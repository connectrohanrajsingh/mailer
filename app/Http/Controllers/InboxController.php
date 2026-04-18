<?php

namespace App\Http\Controllers;

use App\Models\FetchedEmailOverview;
use App\Services\CachedData;
use DB;
use Illuminate\Http\Request;

class InboxController extends Controller
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

    public function details($emailId)
    {
        $email = FetchedEmailOverview::find($emailId);
        return 0;
    }



}
