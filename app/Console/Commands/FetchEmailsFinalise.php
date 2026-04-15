<?php

namespace App\Console\Commands;

use App\Models\FetchEmailBatchTracker;
use App\Models\FetchEmailTracker;
use Illuminate\Console\Command;
use Webklex\IMAP\Facades\Client;
use App\Models\FetchedEmail;
use Carbon\Carbon;
use Webklex\PHPIMAP\Exceptions\ConnectionFailedException;
use App\Services\SaveEmailWebklex;
use Exception;

class FetchEmailsFinalise extends Command
{
    protected $signature = 'emails:finalise {--folder=Inbox : Folder to read from}';
    protected $description = 'Fetch latest emails from IMAP at 3 AM every morning';

    public function handle()
    {
        ini_set('memory_limit', '8G');

        $this->info("Checking pending mails");
        $finaliseResponse = $this->finaliseBatchCheck();
        $missingResponse  = $this->finaliseMissingCheck();

        if ($finaliseResponse['status'] == 'success' && $missingResponse['status'] == 'success') {
            $this->info("No pending work to do");
            return;
        }

        try {
            $client = Client::account('imap');
            $client->connect();

            if (!$client->isConnected()) {
                throw new Exception("IMAP is not connected.");
            }

            $selectdFolder = 'INBOX';
            $folder        = $client->getFolder($selectdFolder);
            if (!$folder) {
                throw new Exception("IMAP Folder $selectdFolder does not exist or is inaccessible.");
            }
        }
        catch (ConnectionFailedException $e) {
            $this->error("IMAP Connection Failed: " . $e->getMessage());
            return;
        }
        catch (Exception $e) {
            $this->error("Error: " . $e->getMessage());
            return;
        }

        if ($finaliseResponse['status'] == 'due') {
            $this->info("Finalising batch mails");
            $this->finaliseBatch($folder, $finaliseResponse['tracker']);
        }

        if ($missingResponse['status'] == 'due') {
            $this->info("Finalising missing mails");
            $this->finaliseMissing($client, $folder, $missingResponse['tracker'], $missingResponse['uids']);
        }
    }

    private function finaliseBatch($folder, $tracker)
    {

        $dateObject  = Carbon::parse($tracker->fetch_date);
        $currentDate = $dateObject->format('Y-m-d');
        $endDate     = $dateObject->addDays(1)->format('Y-m-d');

        $mailsPerPage = 20;

        $query = $folder->query()->since($currentDate)->before($endDate);
        $count = $query->count();

        $this->info("Total Emails Found : $count");
        $this->info("Fetching emails for: $currentDate");
        $this->info("Emails per page: $mailsPerPage");

        $totalPages = ceil($count / $mailsPerPage);

        if ($count != $tracker->total_mails) {
            $tracker->update(['total_mails' => $count, 'total_page' => $totalPages]);
        }

        $this->info("Rechecking last page for late-arriving emails...");
        $page = $tracker->processed_pages + 1;
        $page = ($page > $totalPages) ? $totalPages : $page;

        $emails = $query->limit($mailsPerPage, $page)->get();

        if ($emails->isEmpty()) {
            $tracker->update(['status' => 'COMPLETE', 'processed_pages' => $page]);
            return;
        }

        $bar = $this->output->createProgressBar($emails->count());
        $bar->start();

        $savedMails = 0;
        $processed  = 0;
        foreach ($emails as $email) {
            // Skip duplicates
            if (FetchedEmail::where('message_id', $email->getMessageId())->exists()) {
                $this->info("Mail with id {$email->getMessageId()} already exists, skipping");
                $bar->advance();
                $processed++;
                continue;
            }

            $response = app(SaveEmailWebklex::class)->saveEmail($email);
            if ($response['status'] == 'success') {
                $savedMails++;
            }

            $processed++;
            $bar->advance();
        }

        if ($savedMails > 0) {
            $tracker->increment('processed_mails', $savedMails);
            FetchEmailBatchTracker::create([
                'fetch_email_tracker_id' => $tracker->id,
                'page'                   => $page,
                'mail_processed'         => $savedMails
            ]);
        }

        $tracker->increment('processed_pages', 1);

        $tracker->refresh();

        if ($tracker->processed_pages == $tracker->total_page || $tracker->processed_mails == $tracker->total_mails) {
            $tracker->update(['status' => 'COMPLETE']);
            $bar->finish();
            return;
        }

        $bar->finish();
        return;
    }

    private function finaliseBatchCheck()
    {
        $returnResponse = ['status' => 'success', 'message' => 'Already completed', 'tracker' => []];

        $tracker = FetchEmailTracker::whereNot('status', 'COMPLETE')
            ->where('fetch_date', '<', now()->format('Y-m-d'))
            ->orderBy('id', 'asc')
            ->first();

        if (!$tracker) {
            $this->info("No tracker found");
            return $returnResponse;
        }

        if ($tracker->total_mails == $tracker->processed_mails) {
            $tracker->update(['status' => 'COMPLETE', 'processed_pages' => $tracker->total_page]);
            $this->info("Processed mails are equal to total mails");
            return $returnResponse;
        }

        if ($tracker->total_page == $tracker->processed_pages) {
            $tracker->update(['status' => 'COMPLETE']);
            $this->info("Processed pages are equal to total pages");
            return $returnResponse;
        }

        $returnResponse['status']  = 'due';
        $returnResponse['tracker'] = $tracker;

        return $returnResponse;
    }

    private function finaliseMissing($client, $folder, $tracker, $uids)
    {
        $this->info("Total uid found to fetch: " . count($uids));

        foreach ($uids as $uid) {
            try {
                $this->info("Fetching mail for uid $uid");

                $emails = $folder->query()->whereUid($uid)->get();

                if ($emails->isEmpty()) {
                    $this->info("No email found for uid $uid");
                    $tracker->update(['uid_checked' => $uid]);
                    continue;
                }

                foreach ($emails as $email) {

                    $existing = FetchedEmail::where('message_id', $email->getMessageId())->first();
                    if ($existing) {
                        if (empty($existing->uid)) {
                            $existing->update(['uid' => $uid]);
                            $this->info("UID updated for existing mail");
                        }
                        else {
                            $this->info("Mail already exists, skipping");
                        }
                        $processed++;
                        continue;
                    }
                    app(SaveEmailWebklex::class)->saveEmail($email);
                }

                $tracker->update(['uid_checked' => $uid]);
                $this->info("Tracker updated with uid: $uid");
            }
            catch (\Throwable $e) {
                \Log::error("IMAP fetch failed", ['uid' => $uid, 'error' => $e->getMessage()]);
                $client->disconnect();
                $client->connect();
                $folder = $client->getFolder('INBOX');
                continue;
            }
        }

        $this->info("No uid remaining to process");
        return;
    }

    private function finaliseMissingCheck()
    {
        $returnResponse = ['status' => 'success', 'message' => 'Already completed', 'uids' => []];

        $tracker = FetchEmailTracker::where('fetch_date', '=', now()->subDays(1)->format('Y-m-d'))->first();
        if (!$tracker) {
            $this->info("Tracker not found to get missing uids");
            return $returnResponse;
        }

        if (!empty($tracker->uid_checked)) {
            $this->info("Already Completed Missing uids for yesterday");
            return $returnResponse;
        }

        $minUid = FetchEmailTracker::max('uid_checked') + 1;
        if (!$minUid) {
            $this->info("No min uid found");
            return $returnResponse;
        }

        $maxUid = $minUid + 2000;

        $min = (int) $minUid;
        $max = (int) $maxUid;

        $allUids = FetchedEmail::select(['uid'])->whereBetween('uid', [$min, $max])->pluck('uid')->flip();

        if ($allUids->isEmpty()) {
            $this->info("No UIDs found in DB between $min and $max");
            $tracker->update(['uid_checked' => $max]);
            return $returnResponse;
        }

        $max = (int) $allUids->keys()->max();

        $missing = [];

        for ($i = $min; $i <= $max; $i++) {
            if (!$allUids->has($i)) {
                $missing[] = $i;
            }

            if (count($missing) >= 100) {
                break;
            }
        }

        if (empty($missing)) {
            $this->info("No missing uids found between $min and $max");
            $tracker->update(['uid_checked' => $max]);
            return $returnResponse;
        }

        $returnResponse['status']  = 'due';
        $returnResponse['tracker'] = $tracker;
        $returnResponse['uids']    = $missing;

        return $returnResponse;
    }
}
