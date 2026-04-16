<?php

namespace App\Console\Commands;

use App\Models\FetchedEmailOverview;
use App\Models\FetchedEmailTracker;
use Illuminate\Console\Command;
use Webklex\IMAP\Facades\Client;
use Carbon\Carbon;
use Exception;
use Webklex\PHPIMAP\Exceptions\ConnectionFailedException;

class FetchEmailsOverview extends Command
{
    protected $signature = 'emails:fetchoverview';
    protected $description = 'Fetch latest emails overview from IMAP every few minutes';

    protected $imapFolder;
    protected $mailsPerPageThrottled = 150;
    protected $mailsPerPage;
    protected $fallbackDateObject;
    protected $folder;
    protected FetchedEmailTracker $tracker;

    public function __construct()
    {
        parent::__construct();
        $this->imapFolder         = config('imap.accounts.gmail.folder');
        $this->fallbackDateObject = config('imap.accounts.gmail.start_date');

        $mailsPerPage       = config('imap.accounts.gmail.mails_per_page');
        $this->mailsPerPage = $mailsPerPage < $this->mailsPerPageThrottled ? $mailsPerPage : $this->mailsPerPageThrottled;
    }

    private function clientConnection()
    {
        try {
            $client = Client::account('gmail');
            $client->connect();

            if (!$client->isConnected()) {
                throw new Exception("IMAP is not connected.");
            }

            $this->folder = $client->getFolder($this->imapFolder);
            if (!$this->folder) {
                throw new Exception("IMAP folder {$this->imapFolder} does not exist or is inaccessible.");
            }
        }
        catch (ConnectionFailedException $e) {
            $this->error("IMAP connection failed: " . $e->getMessage());
            return;

        }
        catch (Exception $e) {
            $this->error("Error: " . $e->getMessage());
            return;
        }

        return;
    }

    private function makeMessageIdHashed($messageId)
    {
        $normalized = strtolower(trim($messageId));
        $hash       = hash('sha256', $normalized);
        return $hash;
    }

    private function getTracker()
    {
        $currentDateObject = now();
        $startUid          = 1;

        $trackerId = FetchedEmailTracker::max('id');

        // if script ran for the very first time
        if (!$trackerId) {
            $currentDateObject = Carbon::parse($this->fallbackDateObject);

            $messages = $this->folder->query()->setFetchOrder("asc")->whereOn($currentDateObject)->limit(1)->get();
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

        $fetchDate     = $currentDateObject->copy()->format('Y-m-d');
        $this->tracker = FetchedEmailTracker::firstOrCreate(['folder' => $this->imapFolder, 'fetch_date' => $fetchDate], ['start_uid' => $startUid]);
        return;
    }

    public function handle()
    {
        $this->info("Connecting to imap.");
        $this->clientConnection();
        if (!$this->folder) {
            $this->error("No folder connection available. Exiting.");
            return 1;
        }

        $this->info("Fetching Tracker.");
        $this->getTracker();
        if (!$this->tracker) {
            $this->error("No tracker available. Exiting.");
            return 1;
        }

        $startRange = ($this->tracker->last_uid > 0) ? $this->tracker->last_uid + 1 : $this->tracker->start_uid;
        $endRange   = $startRange + $this->mailsPerPage - 1;

        $this->info("Fetching mails within uid $startRange:$endRange.");

        $emails      = $this->folder->overview("$startRange:$endRange");
        $totalEmails = count($emails);

        if ($totalEmails == 0) {
            $this->info("No new mails found to sync.");
            return 0;
        }

        $this->info("$totalEmails mails fetched.");
        $bar = $this->output->createProgressBar($totalEmails);
        $bar->start();

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
            $bar->advance();
        }

        $this->info("Saving mails.");



        $processedEmails = 0;
        collect($saveOverview)
            ->chunk(50)
            ->each(function ($chunk) use (&$processedEmails) {
                $processedEmails += FetchedEmailOverview::insertOrIgnore($chunk->toArray());
            });

        $this->info("$processedEmails mails saved.");
        $this->info("Updating tracker with last uid $lastUid.");

        $totalEmails = $this->tracker->total_emails + $totalEmails;
        $savedEmails = $this->tracker->processed_emails + $processedEmails;
        $this->tracker->update([
            'last_uid'         => $lastUid,
            'total_emails'     => $totalEmails,
            'processed_emails' => $savedEmails,
        ]);

        $bar->finish();
        $this->info("Sync complete.");
        return 0;
    }
}