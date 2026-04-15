<?php

namespace App\Console\Commands;

use App\Models\FetchEmailTracker;
use App\Services\SaveEmailWebklex;
use Illuminate\Console\Command;
use Webklex\IMAP\Facades\Client;
use App\Models\FetchedEmail;
use Exception;
use Webklex\PHPIMAP\Exceptions\ConnectionFailedException;

class FetchEmails extends Command
{
    protected $signature = 'emails:fetch {--folder=Inbox : Folder to read from}';
    protected $description = 'Fetch latest emails from IMAP every few minutes';

    public function handle()
    {
        ini_set('memory_limit', '8G');
        try {
            $client = Client::account('imap');
            $client->connect();

            if (!$client->isConnected()) {
                throw new Exception("IMAP is not connected.");
            }

            $selectdFolder = $this->option('folder') ?? 'INBOX';
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

        $mailsPerPage = 20;
        $dateObject   = now();
        $currentDate  = $dateObject->copy()->format('Y-m-d');
        $endDate      = $dateObject->copy()->addDays(1)->format('Y-m-d');

        $query      = $folder->query()->setFetchOrder('asc')->since($currentDate)->before($endDate);
        $totalMails = $query->count();

        $totalPages = ceil($totalMails / $mailsPerPage);

        $tracker = FetchEmailTracker::where('fetch_date', $currentDate)->whereIn('status', ['PENDING', 'PROCESSING'])->first();

        if (!$tracker) {
            $tracker = FetchEmailTracker::create([
                'fetch_date'      => $currentDate,
                'total_mails'     => $totalMails,
                'processed_mails' => 0,
                'total_page'      => $totalPages,
                'processed_pages' => 0,
                'status'          => 'PROCESSING'
            ]);
        }

        if ($totalMails != $tracker->total_mails) {
            $tracker->update(['total_mails' => $totalMails, 'total_page' => $totalPages]);
        }

        if ($tracker->total_mails == $tracker->processed_mails) {
            $this->info("No new mails found");
            return;
        }

        $page = $tracker->processed_pages + 1;

        $this->info("IMAP Connected, Fetching mails from: $selectdFolder");
        $this->info("Total Emails Found : $totalMails");
        $this->info("Fetching emails for: $currentDate");
        $this->info("Emails per page: $mailsPerPage");
        $this->info("Fetch emails started at " . now()->format('Y-m-d H:i:s'));
        $this->info("Current page: $page");

        $page = ($page > $totalPages) ? $totalPages : $page;

        $emails = $query->limit($mailsPerPage, $page)->get();

        if ($emails->isEmpty()) {
            $this->warn("No emails found, returning.");
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

        if ($processed == $mailsPerPage) {
            $tracker->increment('processed_pages', 1);
        }
        $bar->finish();
    }
}