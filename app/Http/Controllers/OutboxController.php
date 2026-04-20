<?php

namespace App\Http\Controllers;

use App\Models\FetchedEmailOverview;
use App\Models\SentEmail;
use App\Models\SentEmailAttachment;
use App\Services\CachedData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OutboxController extends Controller
{
    private function applyFilters(Request $request, $queryBuilder, $filterOptions, $filterCondition)
    {
        $reqOption   = $request->input('fetch_option');
        $reqCriteria = $request->input('fetch_criteria');
        $reqSearch   = $request->input('search_value');

        if (!$reqOption || !$reqCriteria || !$reqSearch) {
            return;
        }

        $filterOptions   = array_column($filterOptions, 'value');
        $filterCondition = array_column($filterCondition, 'value');

        if (!in_array($reqOption, $filterOptions, TRUE) || !in_array($reqCriteria, $filterCondition, TRUE)) {
            \Log::warning('Invalid filter option or criteria', ['options' => $reqOption, 'criteria' => $reqCriteria]);
            return;
        }

        switch ($reqCriteria) {
            case 'exact_match':
                $queryBuilder->where($reqOption, $reqSearch);
                break;

            case 'exists':
                $search = addcslashes($reqSearch, '%_');
                $queryBuilder->where($reqOption, 'LIKE', "%{$search}%");
                break;
        }

        return;
    }

    public function index(Request $request)
    {
        $filterOptions   = CachedData::getJson("static/inbox", 'filter-options');
        $filterCondition = CachedData::getJson("static/inbox", 'filter-conditions');

        if (!$filterOptions) {
            abort(500, 'Filter option not found');
        }

        if (!$filterCondition) {
            abort(500, 'Filter criteria not found');
        }

        $queryBuilder = FetchedEmailOverview::where('processed', TRUE)->orderBy("created_at", "desc");
        $this->applyFilters($request, $queryBuilder, $filterOptions, $filterCondition);
        $emails = $queryBuilder->paginate(10)->withQueryString();

        $context = [
            'emails'          => $emails,
            'filterOptions'   => $filterOptions,
            'filterCondition' => $filterCondition
        ];

        return view('inbox.index', $context);
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
        return redirect()->route('inbox.index', $validated);
    }

    public function send(Request $request)
    {
        $validatedData = $request->validate([
            'to_emails'   => ['required', 'string'],
            'cc_emails'   => ['nullable', 'string'],
            'bcc_emails'  => ['nullable', 'string'],

            'subject'     => ['required', 'string', 'max:800'],
            'text_body'   => ['nullable', 'string'],
            'attachments' => ['nullable', 'array'],
        ]);

        $validatedData['to_emails']  = explode(",", $validatedData['to_emails']) ?? [];
        $validatedData['cc_emails']  = !empty($validatedData['cc_emails']) ? explode(",", $validatedData['bcc_emails']) : [];
        $validatedData['bcc_emails'] = !empty($validatedData['bcc_emails']) ? explode(",", $validatedData['bcc_emails']) : [];

        try {
            $sentEmail = SentEmail::create([
                'to_emails'  => $validatedData['to_emails'],
                'cc_emails'  => $validatedData['cc_emails'],
                'bcc_emails' => $validatedData['bcc_emails'],
                'subject'    => $validatedData['subject'],
                'text_body'  => $validatedData['text_body'],
            ]);

            // Handle actual uploaded files
            if ($request->filled('attachments') && $request->hasFile('attachments')) {

                $disk = Storage::disk('local');
                $path = Str::lower("attachments/outbox/{$sentEmail->id}");
                $disk->makeDirectory($path);

                foreach ($request->file('attachments') as $file) {

                    if ($file->storeAs($path, $uuidName, 'local')) {

                        $uuidName = str_replace('-', '', Str::uuid()->toString());
                        $filename = $file->getClientOriginalName();

                        $extension = Str::lower(pathinfo($filename, PATHINFO_EXTENSION));
                        if (!empty($extension)) {
                            $uuidName .= ".$extension";
                        }

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

            return redirect()->back()->with('success', 'Mail queued to send');
        }
        catch (\Exception $e) {
            \Log::error('Mail Queue Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->with('error', 'Failed to queue the mail');
        }
    }

}