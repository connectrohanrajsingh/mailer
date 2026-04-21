<?php

namespace App\Http\Controllers;

use App\Models\FetchedEmailAttachment;
use App\Models\FetchedEmailOverview;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Overview tabs
        $inboxEmails = FetchedEmailOverview::processed()->count();

        $inboxDistinctEmails = FetchedEmailOverview::processed()
            ->distinct('sender_email')
            ->count('sender_email');

        $inboxDateRange = FetchedEmailOverview::processed()
            ->selectRaw("DATE_FORMAT(MIN(date),'%Y-%m-%d') as min_date,DATE_FORMAT(MAX(date),'%Y-%m-%d') as max_date")
            ->first();

        $inboxAttachments = FetchedEmailAttachment::count();


        // Details overview
        $inboxMonthlyData = FetchedEmailOverview::processed()
            ->selectRaw("DATE_FORMAT(date, '%Y-%m') as month, COUNT(*) as total")
            ->groupByRaw("DATE_FORMAT(date, '%Y-%m')")
            ->orderBy('month', 'DESC')
            ->get();

        $inboxDailyData = FetchedEmailOverview::processed()
            ->selectRaw("DATE(date) as day, COUNT(*) as total")
            ->groupByRaw("DATE(date)")
            ->orderBy('day', 'DESC')
            ->get();


        $context = [
            'inboxEmails'         => $inboxEmails,
            'inboxDistinctEmails' => $inboxDistinctEmails,
            'inboxDateRange'      => $inboxDateRange,
            'inboxAttachments'    => $inboxAttachments,
            'inboxMonthlyData'    => $inboxMonthlyData,
            'inboxDailyData'      => $inboxDailyData,
        ];

        return view("dashboard.index", $context);
    }
}
