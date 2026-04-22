<?php

namespace App\Jobs;

use App\Mail\SendEmail;
use App\Models\SentEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendEmailJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    private $emailId;
    public function __construct($emailId)
    {
        $this->emailId = $emailId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $email = SentEmail::where(['id' => $this->emailId, 'status' => 'QUEUED'])->whereNull('sent_at')->first();
        if (!$email) {
            return;
        }

        $email->update(['status' => 'PROCESSING']);

        $updateEmail = [
            'from_email' => config('mail.from.address'),
            'from_name'  => config('mail.from.name'),
        ];

        try {
            Mail::to($email->to_emails)
                ->cc($email->cc_emails)
                ->bcc($email->bcc_emails)
                ->send(new SendEmail($email));

            $updateEmail['status']  = 'SENT';
            $updateEmail['remark']  = 'Email sent successfully';
            $updateEmail['sent_at'] = now();

        }
        catch (\Throwable $e) {
            $updateEmail['status'] = 'FAILED';
            $updateEmail['remark'] = $e->getMessage();
        }

        $email->update($updateEmail);
    }
}
