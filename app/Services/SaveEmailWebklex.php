<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\FetchedEmail;
use Carbon\Carbon;
use Illuminate\Support\Str;


class SaveEmailWebklex
{
    private function saveRecipients(FetchedEmail $email, $message): void
    {
        foreach (['to', 'cc', 'bcc', 'reply_to'] as $type) {
            foreach ($message->{'get' . ucfirst($type)}() as $r) {
                $email->addresses()->updateOrCreate(
                    ['email' => $r->mail, 'type' => $type],
                    ['name' => $r->personal]
                );
            }
        }
    }

    private function saveAttachments(FetchedEmail $email, $message): void
    {
        $savePath = 'emails/' . $email->id;
        Storage::makeDirectory($savePath);
        $fullPath = Storage::path($savePath);

        if (!is_writable($fullPath)) {
            @chmod($fullPath, 0775);
        }

        foreach ($message->getAttachments() as $attachment) {
            $filename = $attachment->getName() ?: uniqid('att_');
            $filePath = $savePath . '/' . $filename;

            $content = $attachment->getContent();

            if ($content && Storage::put($filePath, $content)) {
                $email->attachments()->updateOrCreate(
                    ['checksum' => md5($content)],
                    [
                        'name'         => $filename,
                        'mime_type'    => $attachment->getMimeType(),
                        'size'         => $attachment->getSize(),
                        'disposition'  => $attachment->getDisposition() ?? 'attachment',
                        'content_id'   => $attachment->getContentId(),
                        'path'         => $filePath,
                        'download_url' => Storage::url($filePath),
                    ]
                );
            }
            else {
                \Log::error("Attachment '{$filename}' not saved!");
            }
        }
    }

    public function saveEmail($email)
    {
        $returnResponse = ['status' => 'success', 'message' => 'mail saved'];
        DB::beginTransaction();
        try {

            $internalDate = $email->getInternalDate();
            $receivedDate = $internalDate ? Carbon::parse($internalDate)->timezone('Asia/Kolkata') : now();

            // preventing future dates 
            $emailDate = $email->getDate();
            $emailDate = Carbon::parse($emailDate);
            $emailDate = $emailDate->isFuture() ? now() : $emailDate;

            $fetchedEmail = FetchedEmail::create([
                'message_id'    => $email->getMessageId(),
                'subject'       => Str::limit($email->getSubject(), 250),
                'sender_name'   => $email->getFrom()[0]->personal ?? null,
                'sender_email'  => $email->getFrom()[0]->mail ?? null,
                'body_text'     => $email->getTextBody(),
                'body_html'     => $email->getHTMLBody(),
                'priority'      => $email->getPriority(),
                'size'          => $email->getSize(),
                'uid'           => $email->getUid(),
                'date'          => $emailDate,
                'received_date' => $receivedDate,
                'flags'         => json_encode($email->getFlags()),
                'in_reply_to'   => $email->getInReplyTo()
            ]);

            // $this->saveRecipients($email, $message);
            $this->saveAttachments($fetchedEmail, $email);
            DB::commit();
        }
        catch (\Throwable $e) {
            DB::rollBack();
            \Log::error("Failed to save email {$email->getSubject()}: {$e->getMessage()}");
            $returnResponse['status']  = 'error';
            $returnResponse['message'] = $e->getMessage();
        }

        return $returnResponse;
    }
}
