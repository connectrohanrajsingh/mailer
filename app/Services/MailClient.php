<?php

namespace App\Services;

use Webklex\IMAP\Facades\Client;
use Webklex\PHPIMAP\Client as ImapClient;

class MailClient
{
    public function connect(string $account = 'imap')
    {
        try {
            $client = Client::account($account);
            $client->connect();
            return $client;
        }
        catch (\Throwable $e) {
            \Log::error('IMAP connection failed', ['account' => $account, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function getFolder(ImapClient $client, string $folder)
    {
        try {
            $connection = $client->getFolder($folder);
            if (!$connection) {
                throw new \Exception("Folder '{$folder}' not found or inaccessible.");
            }
            return $connection;
        }
        catch (\Throwable $e) {
            \Log::error('IMAP folder access failed', ['folder' => $folder, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
}
