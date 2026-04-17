<?php

namespace App\Console\Commands;

use App\Models\FetchedEmailOverview;
use App\Models\FetchedEmailTracker;
use App\Services\MailClient;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Exception;

class FetchEmailsOverview extends Command
{
    protected $signature = 'emails:overview';
    protected $description = 'Fetch latest emails overview from IMAP every few minutes';

    protected $imapFolder;
    protected $maxMailsPerFetch;
    protected $mailsPerFetch;
    protected $fallbackDateObject;

    public function __construct()
    {
        parent::__construct();
        $this->maxMailsPerFetch   = 150;
        $this->imapFolder         = config('imap.accounts.imap.folder');
        $this->fallbackDateObject = config('imap.accounts.imap.start_date');

        $mailsPerFetch       = config('imap.accounts.imap.mails_per_fetch');
        $this->mailsPerFetch = $mailsPerFetch <= $this->maxMailsPerFetch ? $mailsPerFetch : $this->maxMailsPerFetch;
    }

    private function makeMessageIdHashed($messageId)
    {
        $normalized = strtolower(trim($messageId));
        $hash       = hash('sha256', $normalized);
        return $hash;
    }

    private function getTracker($folder)
    {
        $currentDateObject = now();
        $startUid          = 1;

        $trackerId = FetchedEmailTracker::max('id');

        // if script ran for the very first time
        if (!$trackerId) {
            $currentDateObject = Carbon::parse($this->fallbackDateObject);

            $messages = $folder->query()->setFetchOrder("asc")->whereOn($currentDateObject)->limit(1)->get();
            foreach ($messages as $message) {
                $startUid = $message->getUid();
            }
        }
        else {
            $startUid = FetchedEmailTracker::max('last_uid') + 1;
            if ($startUid == 1) {
                $startUid = FetchedEmailTracker::max('start_uid');
            }
        }

        $fetchDate = $currentDateObject->copy()->format('Y-m-d');
        $tracker   = FetchedEmailTracker::firstOrCreate(['folder' => $this->imapFolder, 'fetch_date' => $fetchDate], ['start_uid' => $startUid]);
        return $tracker;
    }

    public function handle()
    {
        $this->info("Connecting to imap.");

        $mailClient = new MailClient();
        $client     = $mailClient->connect();
        $folder     = $mailClient->getFolder($client, $this->imapFolder);

        if (!$folder) {
            $this->error("No folder connection available. Exiting.");
            return 1;
        }

        $this->info("Fetching Tracker.");
        !$tracker = $this->getTracker($folder);
        if (!$tracker) {
            $this->error("No tracker available. Exiting.");
            return 1;
        }

        $startRange = ($tracker->last_uid > 0) ? $tracker->last_uid + 1 : $tracker->start_uid;
        $endRange   = $startRange + $this->mailsPerFetch - 1;
        $fetchRange = "$startRange:$endRange";

        if ($startRange > $endRange) {
            $this->info("Invalid uid fetch range $fetchRange provided");
            return 0;
        }

        $this->info("Fetching mails within uid $fetchRange.");

        $emails      = $folder->overview($fetchRange);
        $totalEmails = count($emails);

        if ($totalEmails == 0) {
            $this->info("No new mails found to sync.");
            $tracker->update(['last_uid' => $endRange]);
            return 0;
        }

        $this->info("$totalEmails mails fetched.");

        $saveOverview = [];
        $currentTime  = now()->format('Y-m-d H:i:s');
        $lastUid      = $startRange;

        $this->info("Started query building.");

        foreach ($emails as $uid => $header) {
            $messageId = (string) $header['message_id'];
            if (empty($messageId)) {
                continue;
            }
            try {
                $parsedDate = Carbon::parse((string) $header['date']);
                $parsedDate = $parsedDate->isFuture() ? $currentTime : $parsedDate;
            }
            catch (Exception $e) {
                $parsedDate = now();
            }

            $saveOverview[] = [
                'folder'            => $this->imapFolder,
                'uid'               => $uid,
                'message_id'        => $messageId,
                'message_id_hashed' => $this->makeMessageIdHashed($messageId),
                'date'              => $parsedDate,
                'created_at'        => $currentTime,
                'updated_at'        => $currentTime
            ];

            $lastUid = $uid;
        }

        $this->info("Saving mails.");

        $bar = $this->output->createProgressBar($totalEmails);
        $bar->start();

        $processedEmails = 0;
        collect($saveOverview)
            ->chunk(50)
            ->each(function ($chunk) use (&$processedEmails, $bar) {
                $processedEmails += FetchedEmailOverview::insertOrIgnore($chunk->toArray());
                $bar->advance();
            });

        $bar->finish();

        $this->info("\n$processedEmails mails saved.");
        $this->info("Updating tracker with last uid $lastUid.");

        $totalEmails = $tracker->total_emails + $totalEmails;
        $savedEmails = $tracker->processed_emails + $processedEmails;

        $tracker->update([
            'last_uid'         => $lastUid,
            'total_emails'     => $totalEmails,
            'processed_emails' => $savedEmails,
        ]);

        $this->info("Sync complete.");
        return 0;
    }
}