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

        $folder = $client->getFolder($folderName);


        $this->info("Fetching: " . now());
        // $message = $folder->query()->setFetchOrder("asc")->whereUid(375430)->get();
        // $this->info("Fetched emails count" . $message->count());

        // $messages = $folder->query()
        //     ->setFetchOrder("asc")
        //     ->whereOn('2026-04-14')
        //     ->setFetchOptions(FT_PEEK)
        //     ->limit(1)
        //     ->get();

        // foreach ($messages as $message) {
        //     $this->info("UID: {$message->getUid()}");
        //     $this->info("Subject: {$message->getSubject()}");
        //     $this->info("From: {$message->getFrom()[0]->mail}");
        //     $this->info("Message ID: {$message->getMessageId()}");
        //     $this->info("Dump: " . json_encode($message));


        $message = $folder->overview("364930:364931");
        // return 0;
        foreach ($message as $uid => $headers) {
            $this->info("UID: $uid");
            $this->info("Subject: " . $headers['subject']);
            $this->info("From: " . $headers['from']);
            $this->info("Message ID: " . $headers['message_id']);
            $this->info("Date: " . $headers['date']);
        }
        $this->info(count($message));



        $this->info("Done: " . now());
    }
}