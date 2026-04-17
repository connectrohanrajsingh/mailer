<?php

namespace App\Http\Controllers;

use App\Models\FetchedEmailOverview;
use DB;
use Illuminate\Http\Request;

class InboxController extends Controller
{
    public function index(Request $request)
    {

        $queryBuilder = FetchedEmailOverview::orderBy("created_at", "desc");

        if ($request->has('sv') && $request->has('fc') && $request->has('fo')) {

            if ($request->input('fc') == 'exact_match') {
                $queryBuilder->where($request->input('fo'), $request->input('sv'));
            }

            if ($request->input('fc') == 'exists') {
                $queryBuilder->where($request->input('fo'), 'LIKE', '%' . $request->input('sv') . '%');
            }
        }

        $emails = $queryBuilder->paginate(10)->withQueryString();;

        $filterOptions = [
            ['value' => 'sender_email', 'label' => 'Sender Email'],
            ['value' => 'sender_name', 'label' => 'Sender Name'],
            ['value' => 'subject', 'label' => 'Subject'],
        ];

        $filterCondition = [
            ['value' => 'exact_match', 'label' => 'Exact Match'],
            ['value' => 'exists', 'label' => 'Exists In'],
        ];

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
                'sv' => ['required', 'string'],
                'fo' => ['required', 'string'],
                'fc' => ['required', 'string'],
            ],
            [
                'sv.required' => 'Search value is required',
                'fo.required' => 'Filter option is required',
                'fc.required' => 'Filter criteria is required',
            ]
        );
        return redirect()->route('inbox.index', $validated);
    }

}
