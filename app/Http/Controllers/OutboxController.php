<?php

namespace App\Http\Controllers;

use App\Models\SentEmail;
use App\Services\ApplyFilters;
use App\Services\CachedData;
use Illuminate\Http\Request;


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

  
    public function show($emailId)
    {
        $email   = SentEmail::with(['attachments'])->findOrFail($emailId);
        $context = ['email' => $email];
        return view('outbox.details', $context);
    }

}