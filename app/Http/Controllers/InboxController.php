<?php

namespace App\Http\Controllers;

use App\Models\FetchedEmailOverview;
use App\Services\ApplyFilters;
use App\Services\CachedData;
use Illuminate\Http\Request;

class InboxController extends Controller
{
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
        app(ApplyFilters::class)->applyFilters($request, $queryBuilder, $filterOptions, $filterCondition);
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

    public function show($emailId)
    {
        $email = FetchedEmailOverview::with(['body', 'attachments', 'addresses'])->findOrFail($emailId);

        $html = $email->body?->body_html;

        if ($html) {
            foreach ($email->attachments as $attachment) {

                if ($attachment->inline && $attachment->content_id) {
                    $cid = trim($attachment->content_id, '<>');
                    $url = $attachment->getUrl();

                    $html = str_replace("cid:$cid", $url, $html);
                }
            }
        }

        $email->rendered_body = $html;

        $context = ['email' => $email];
        return view('inbox.details', $context);
    }
}
