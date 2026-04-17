<?php

namespace App\Http\Controllers;

use App\Models\FetchedEmailOverview;
use Illuminate\Http\Request;

class InboxController extends Controller
{
    public function index(Request $request)
    {
        $emails  = FetchedEmailOverview::orderBy("created_at", "desc")->paginate(10);
        $context = ['emails' => $emails];
        return view('inbox.index', $context);
    }
}
