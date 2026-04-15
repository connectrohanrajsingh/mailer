<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Webklex\IMAP\Facades\Client;
use App\Models\FetchedEmail;
use Carbon\Carbon;

class TestEmailFetch extends Command
{
    protected $signature = 'emails:test';
    protected $description = 'Test IMAP fetching with 2 emails';

    public function handle()
    {
        $this->info("Connecting to IMAP...");

        $client     = Client::account('gmail');
        $folderName = config('imap.accounts.gmail.folder');


        try {
            $client->connect();
        }
        catch (\Throwable $e) {
            $this->error("Connection failed: " . $e->getMessage());
            return 1;
        }

        if (!$client->isConnected()) {
            $this->error("IMAP not connected");
            return 1;
        }

        $folder = $client->getFolder( $folderName);

        // $message = $folder->query()->since('2026-04-09')->before('2026-04-10');
        // $this->info("Fetched emails count" . $message->count());

        // $message = $folder->query()->setFetchOrder("asc")->whereUid(375430)->get();


        $range = '375431:375631';

        // $message = $folder->query()->setUid($range)->get();
        $message = $folder->overview("375430:375431");


        foreach ($message as $uid => $headers) {

            $this->info("UID: $uid");

            $this->info("Subject: " . $headers['subject']);
            $this->info("From: " . $headers['from']);
            $this->info("Message ID: " . $headers['message_id']);
            $this->info("Date: " . $headers['date']);
        }

        $this->info("Done ✅");
    }
}