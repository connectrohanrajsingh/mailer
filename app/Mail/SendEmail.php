<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class SendEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    private $email;
    public function __construct($email)
    {
        $this->email = $email;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->email->subject);
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'outbox.template',
            with: ['email' => $this->email]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */

    public function attachments(): array
    {
        $files = [];

        if ($this->email->attachments()->exists()) {
            foreach ($this->email->attachments as $att) {

                $path = Storage::disk($att->storage_disk)->path($att->storage_path);

                if (file_exists($path)) {
                    $files[] = Attachment::fromPath($path)->as($att->name)->withMime($att->mime_type);
                }
            }
        }
        return $files;
    }
}
