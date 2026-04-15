<?php

namespace App\Console\Commands;

use App\Models\FetchedEmailTracker;
use App\Services\SaveEmailWebklex;
use Illuminate\Console\Command;
use Webklex\IMAP\Facades\Client;
use App\Models\FetchedEmail;
use Exception;
use Webklex\PHPIMAP\Exceptions\ConnectionFailedException;

class FetchEmailsOverview extends Command
{
    protected $signature = 'emails:fetch';
    protected $description = 'Fetch latest emails from IMAP every few minutes';

    protected $firstFetch;
    protected $imapFolder;
    protected $mailsPerPage;
    protected $currentDateObject;
    protected $fallbackDateObject;

    public function __construct()
    {
        parent::__construct();

        $this->firstFetch = FALSE;

        $this->imapFolder   = config('imap.accounts.gmail.folder');
        $this->mailsPerPage = config('imap.accounts.gmail.mails_per_page');

        $this->currentDateObject  = now();
        $this->fallbackDateObject = config('imap.accounts.gmail.start_date');
    }

    private function clientConnection()
    {
        try {
            $client = Client::account('gmail');
            $client->connect();

            if (!$client->isConnected()) {
                throw new Exception("IMAP is not connected.");
            }

            $folder = $client->getFolder($this->imapFolder);
            if (!$folder) {
                throw new Exception("IMAP folder {$this->imapFolder} does not exist or is inaccessible.");
            }
        }
        catch (ConnectionFailedException $e) {
            $this->error("IMAP connection failed: " . $e->getMessage());
            return null;

        }
        catch (Exception $e) {
            $this->error("Error: " . $e->getMessage());
            return null;
        }
        return $folder;
    }

    private function getTracker()
    {
        // if script ran for the very first time
        $trackerId = FetchedEmailTracker::max('id');
        if (!$trackerId) {
            $this->currentDateObject = $this->fallbackDateObject;
            $this->firstFetch        = TRUE;
        }

        $fetchDate = $this->currentDateObject->copy()->format('Y-m-d');
        $tracker   = FetchedEmailTracker::firstOrCreate(['folder' => $this->imapFolder, 'fetch_date' => $fetchDate]);
        return $tracker;
    }


    private function inititaliseTracker($tracker)
    {
        if ($this->firstFetch) {
        }

        $tracker->refresh();
        return $tracker;
    }





    public function handle()
    {
        $tracker = $this->getTracker();
        if (empty($tracker)) {
            $this->info("Tracker not found");
            return;
        }

        $clientFolder = $this->clientConnection();
        if (empty($clientFolder)) {
            $this->info("Client folder is not connected");
            return;
        }

        $startDate = $this->currentDateObject->copy()->format('Y-m-d');
        $endDate   = $this->currentDateObject->copy()->addDays(1)->format('Y-m-d');

        $query = $clientFolder->query()->setFetchOrder('asc')->since($startDate)->before($endDate);

        $totalMails = $query->count();
        $totalPages = ceil($totalMails / $this->mailsPerPage);

        if ($totalMails != $tracker->total_emails) {
            $tracker->update(['total_emails' => $totalMails, 'total_page' => $totalPages]);
        }

        if ($tracker->total_emails == $tracker->processed_emails) {
            $this->info("No new mails found");
            return;
        }

        $page = $tracker->processed_pages + 1;

        $this->info("IMAP Connected, Fetching mails");
        $this->info("Total Emails Found : $totalMails");
        $this->info("Fetching emails for: $startDate");
        $this->info("Emails per page: {$this->mailsPerPage}");
        $this->info("Fetch emails started at " . now()->format('Y-m-d H:i:s'));
        $this->info("Current page: $page");

        // to get the same page emails and try to find new one 
        $page = ($page > $totalPages) ? $totalPages : $page;

        $emails = $query->limit($this->mailsPerPage, $page)->get();

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
            $tracker->increment('processed_emails', $savedMails);
        }

        if ($processed == $mailsPerPage) {
            $tracker->increment('processed_pages', 1);
        }
        $bar->finish();
    }





}