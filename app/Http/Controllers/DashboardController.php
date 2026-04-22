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
        $inboxStats = FetchedEmailOverview::selectRaw(
            "COUNT(*) as inboxEmails,
            COUNT(DISTINCT sender_email) as inboxDistinctEmails,
            DATE_FORMAT(MIN(date), '%Y-%m-%d') as minDate,
            DATE_FORMAT(MAX(date), '%Y-%m-%d') as maxDate")
            ->where('processed', 1)
            ->first();

        $inboxAttachments = FetchedEmailAttachment::count();


        // Details overview
        $detailStats = FetchedEmailOverview::from('fetched_email_overviews as fe')
            ->leftJoin('fetched_email_attachments as fea', 'fea.email_id', '=', 'fe.id')
            ->where('fe.processed', 1)
            ->selectRaw("DATE(fe.date) as monthGroup,
                        COUNT(DISTINCT fe.sender_email) as distinctEmail,
                        COUNT(DISTINCT fe.id) as total,
                        COUNT(fea.id) as attachment")
            ->groupByRaw('DATE(fe.date)')
            ->orderByRaw('DATE(fe.date) DESC')
            ->limit(5)
            ->get();


        $latesEmailstats = FetchedEmailOverview::selectRaw('sender_email, COUNT(id) as email_count')
            ->where('processed', 1)
            ->groupBy('sender_email')
            ->having('email_count', '>', 2)
            ->orderByDesc('email_count')
            ->limit(5)
            ->get();

        $max     = $latesEmailstats->max('email_count') ?? 100;
        $divisor = $max / 100;

        $latesEmailstats->map(function ($details) use ($divisor) {
            $details->bar_percentage = $details->email_count / $divisor;
        });

        $context = [
            'inboxStats'       => $inboxStats,
            'inboxAttachments' => $inboxAttachments,
            'detailStats'      => $detailStats,
            'latesEmailstats'  => $latesEmailstats,
        ];

        return view("dashboard.index", $context);
    }
}
