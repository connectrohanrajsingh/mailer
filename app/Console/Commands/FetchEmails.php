<?php

namespace App\Console\Commands;

use App\Models\FetchedEmailAddresses;
use App\Models\FetchedEmailAttachment;
use App\Models\FetchedEmailBody;
use App\Models\FetchedEmailOverview;
use App\Services\MailClient;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FetchEmails extends Command
{
    protected $signature = 'emails:fetch';
    protected $description = 'Fetch emails from IMAP and process them safely';

    protected string $imapFolder;
    protected int $limit;

    public function __construct()
    {
        parent::__construct();
        $this->imapFolder = config('imap.accounts.imap.folder');
        $this->limit      = config('imap.accounts.imap.mails_per_fetch');
    }

    public function handle()
    {
        ini_set('memory_limit', '1G');

        $this->info("Fetching pending email overviews...");

        $overviews = FetchedEmailOverview::where('processed', FALSE)
            ->whereNull('sender_email')
            ->orderBy('id')
            ->limit($this->limit)
            ->get();

        if ($overviews->isEmpty()) {
            $this->info("No pending emails.");
            return 0;
        }

        $client = (new MailClient())->connect();
        $folder = $client->getFolder($this->imapFolder);

        if (!$folder) {
            $this->error("IMAP folder not found.");
            return 1;
        }

        foreach ($overviews as $overview) {
            $this->processOverview($overview, $folder, $client);
        }

        return 0;
    }

    private function processOverview($overview, $folder, $client)
    {
        try {
            $this->info("Processing UID: {$overview->uid}");

            $emails = $folder->query()->whereUid($overview->uid)->get();

            if ($emails->isEmpty()) {
                $this->markFailed($overview, 'not found');
                return;
            }

            foreach ($emails as $email) {
                $this->storeEmail($overview, $email);
            }

        }
        catch (\Throwable $e) {
            $this->markFailed($overview, 'imap_error');
            \Log::error("IMAP error", ['uid' => $overview->uid, 'error' => $e->getMessage()]);

            $client->disconnect();
            $client->connect();
        }
    }

    private function storeEmail(FetchedEmailOverview $overview, $email)
    {
        DB::beginTransaction();

        try {
            $date = $email->getDate();
            $date = $date ? Carbon::parse($date) : now();
            $date = $date->isFuture() ? now() : $date;

            $bodyText = $email->getTextBody();
            $bodyHtml = $email->getHTMLBody();

            // // HTML → TEXT fallback
            // if (empty($bodyText) && !empty($bodyHtml)) {
            //     $bodyText = html_entity_decode(strip_tags($bodyHtml));
            // }

            // // TEXT → HTML fallback
            // if (empty($bodyHtml) && !empty($bodyText)) {
            //     $bodyHtml = nl2br(e($bodyText));
            // }


            // if (empty($bodyText) && empty($bodyHtml)) {
            //     $bodyText = '[EMPTY BODY]';
            // }

            FetchedEmailBody::create([
                'email_id'  => $overview->id,
                'body_text' => $bodyText,
                'body_html' => $bodyHtml,
                'flags'     => json_encode($email->getFlags()),
            ]);

            $this->saveAttachments($overview, $email);
            $this->saveRecipients($overview, $email);

            $from        = $email->getFrom();
            $senderName  = $from[0]->personal ?? null;
            $senderEmail = $from[0]->mail ?? null;

            $overview->update([
                'subject'      => Str::limit($email->getSubject(), 250),
                'processed'    => TRUE,
                'sender_name'  => $senderName,
                'sender_email' => $senderEmail,
                'priority'     => $email->getPriority(),
                'size'         => $email->getSize(),
                'in_reply_to'  => $email->getInReplyTo(),
                'processed_at' => now(),
                'status'       => 'done'
            ]);

            DB::commit();

        }
        catch (\Throwable $e) {
            DB::rollBack();
            $this->markFailed($overview, 'save_error');
            \Log::error("Save failed", ['uid' => $overview->uid, 'error' => $e->getMessage()
            ]);
        }
    }
    private function saveRecipients(FetchedEmailOverview $overview, $email)
    {
        foreach (['to', 'cc', 'bcc', 'reply_to'] as $type) {
            $method = 'get' . ucfirst($type);

            foreach ($email->$method() ?? [] as $e) {
                FetchedEmailAddresses::updateOrCreate(
                    [
                        'email_id' => $overview->id,
                        'email'    => $e->mail ?? null,
                        'type'     => $type,
                    ],
                    [
                        'name' => $e->personal ?? null
                    ]
                );
            }
        }
    }

    private function saveAttachments(FetchedEmailOverview $overview, $email)
    {
        $disk = Storage::disk('local');
        $path = Str::lower("attachments/{$this->imapFolder}/{$overview->id}");

        $disk->makeDirectory($path);

        $count = 0;

        foreach ($email->getAttachments() as $attachment) {

            $content = $attachment->getContent();
            if (!$content) {
                continue;
            }

            $filename = $attachment->getName() ?: uniqid('att_');
            $filePath = "$path/$filename";

            $uuidName  = str_replace('-', '', Str::uuid()->toString());
            $extension = Str::lower(pathinfo($filename, PATHINFO_EXTENSION));
            if (!empty($extension)) {
                $uuidName .= ".$extension";
            }

            if ($disk->put($filePath, $content)) {

                FetchedEmailAttachment::updateOrCreate(
                    ['checksum' => md5($content)],
                    [
                        'email_id'     => $overview->id,
                        'email'        => $email->getFrom()[0]->mail ?? '',
                        'name'         => $filename,
                        'name_uuid'    => $uuidName,
                        'mime_type'    => $attachment->getMimeType(),
                        'size'         => $attachment->getSize(),
                        'disposition'  => $attachment->getDisposition() ?? 'attachment',
                        'inline'       => $attachment->getDisposition() === 'inline',
                        'content_id'   => $attachment->getContentId(),
                        'storage_disk' => 'local',
                        'storage_path' => $path,
                    ]
                );

                $count++;
            }
        }

        $overview->update(['have_attachments' => $count]);
    }

    private function markFailed(FetchedEmailOverview $overview, string $reason)
    {
        $overview->update(['processed' => TRUE, 'processed_at' => now(), 'status' => $reason]);
    }
}