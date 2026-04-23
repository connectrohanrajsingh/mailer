<?php

namespace App\Http\Controllers;

use App\Jobs\SendEmailJob;
use App\Models\FetchedEmailOverview;
use App\Models\SentEmail;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\SentEmailAttachment;
use Illuminate\Support\Facades\Storage;

class ComposeEmailController extends Controller
{
    public function index($emailId = null)
    {
        $email = null;
        if ($emailId) {
            $email = FetchedEmailOverview::select(['id as reply_to', 'subject', 'sender_name', 'sender_email'])->find($emailId);
        }

        return view("compose.index", compact("email"));
    }


    public function store(Request $request)
    {
        $emailRegex = "/^([a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,})(\s*,\s*[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,})*$/";

        $validatedData = $request->validate([
            'to_emails'   => ['required', 'string', "regex:$emailRegex"],
            'cc_emails'   => ['nullable', 'string', "regex:$emailRegex"],
            'bcc_emails'  => ['nullable', 'string', "regex:$emailRegex"],

            'to_name'     => ['nullable', 'string', 'max:120'],
            'subject'     => ['required', 'string', 'max:800'],
            'body'        => ['nullable', 'string'],
            'attachments' => ['nullable', 'array'],

            'reply_to'    => ['nullable', 'numeric'],
        ]);


        $validatedData['to_emails'] = collect(explode(',', $validatedData['to_emails']))
            ->map(fn($e) => trim($e))
            ->filter()
            ->values()
            ->toArray();

        $validatedData['cc_emails'] = !empty($validatedData['cc_emails'])
            ? collect(explode(',', $validatedData['cc_emails']))
                ->map(fn($e) => trim($e))
                ->filter()
                ->values()
                ->toArray()
            : [];

        $validatedData['bcc_emails'] = !empty($validatedData['bcc_emails'])
            ? collect(explode(',', $validatedData['bcc_emails']))
                ->map(fn($e) => trim($e))
                ->filter()
                ->values()
                ->toArray()
            : [];

        try {
            $sentEmail = SentEmail::create([
                'to_emails'  => $validatedData['to_emails'],
                'cc_emails'  => $validatedData['cc_emails'],
                'bcc_emails' => $validatedData['bcc_emails'],
                'subject'    => $validatedData['subject'],
                'to_name'    => $validatedData['to_name'],
                'body'       => $validatedData['body'],
                'reply_to'   => $validatedData['reply_to'] ?? null,
            ]);

            // Handle actual uploaded files
            if ($request->hasFile('attachments')) {

                $disk = Storage::disk('local');
                $path = Str::lower("attachments/outbox/{$sentEmail->id}");
                $disk->makeDirectory($path);

                foreach ($request->file('attachments') as $file) {

                    $filename = $file->getClientOriginalName();
                    $uuidName = str_replace('-', '', Str::uuid()->toString());

                    $extension = Str::lower(pathinfo($filename, PATHINFO_EXTENSION));
                    if (!empty($extension)) {
                        $uuidName .= ".$extension";
                    }

                    if ($file->storeAs($path, $filename, 'local')) {

                        $checksum = hash_file('sha256', $file->getRealPath());

                        SentEmailAttachment::create([
                            'email_id'     => $sentEmail->id,
                            'name'         => $filename,
                            'name_uuid'    => $uuidName,
                            'mime_type'    => $file->getClientMimeType(),
                            'size'         => $file->getSize(),
                            'checksum'     => $checksum,
                            'storage_disk' => 'local',
                            'storage_path' => $path,
                        ]);
                    }
                }
            }

            SendEmailJob::dispatch($sentEmail->id);
            return redirect()->route('outbox.index')->with('success', 'Mail queued to send');
        }
        catch (\Exception $e) {
            \Log::error('Mail Queue Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->with('error', 'Failed to queue the mail')->withInput();
        }
    }

}
