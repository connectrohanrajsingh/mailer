<?php

namespace App\Http\Controllers;

use App\Jobs\SendEmailJob;
use App\Models\SentEmail;
use App\Models\SentEmailAttachment;
use App\Services\ApplyFilters;
use App\Services\CachedData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OutboxController extends Controller
{
    public function index(Request $request)
    {
        $filterOptions   = CachedData::getJson("static/outbox", 'filter-options');
        $filterCondition = CachedData::getJson("static/outbox", 'filter-conditions');

        if (!$filterOptions) {
            abort(500, 'Filter option not found');
        }

        if (!$filterCondition) {
            abort(500, 'Filter criteria not found');
        }

        $queryBuilder = SentEmail::select(['id', 'to_emails', 'to_name', 'subject', 'created_at', 'sent_at'])
            ->withCount('attachments')
            ->orderBy("created_at", "desc");

        app(ApplyFilters::class)->applyFilters($request, $queryBuilder, $filterOptions, $filterCondition);
        $emails = $queryBuilder->paginate(10)->withQueryString();

        $emails->map(function ($email) {
            $email->to_emails = implode(", ", $email->to_emails);
        });


        $context = [
            'emails'          => $emails,
            'filterOptions'   => $filterOptions,
            'filterCondition' => $filterCondition
        ];

        return view('outbox.index', $context);
    }

    public function filter(Request $request)
    {
        $validated = $request->validate(
            [
                'search_value'   => ['required', 'string'],
                'fetch_option'   => ['required', 'string'],
                'fetch_criteria' => ['required', 'string'],
            ],
            [
                'search_value.required'   => 'Search value is required',
                'fetch_option.required'   => 'Filter option is required',
                'fetch_criteria.required' => 'Filter criteria is required',
            ]
        );
        return redirect()->route('outbox.index', $validated);
    }


    public function compose($emailId = null)
    {
        return view("outbox.compose");
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

    public function show($emailId)
    {
        $email   = SentEmail::with(['attachments'])->findOrFail($emailId);
        $context = ['email' => $email];
        return view('outbox.details', $context);
    }

}