<?php

namespace App\Services;

use Webklex\IMAP\Facades\Client;
use Webklex\PHPIMAP\Client as ImapClient;

class MailHelper
{
    public static function makeIdHashed(string $messageId)
    {
        $normalized = strtolower(trim($messageId));
        $hash       = hash('sha256', $normalized);
        return $hash;
    }
}
