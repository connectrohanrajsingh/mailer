<?php

namespace App\Providers;

use App\Models\FetchedEmailOverview;
use App\Models\SentEmail;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layout.*', function ($view) {
            $view->with('mailCounts', [
                'inboxUnread' => FetchedEmailOverview::where('processed', TRUE)->where('seen', FALSE)->count(),
                'inboxTotal'  => FetchedEmailOverview::where('processed', TRUE)->count(),
                'outboxTotal' => SentEmail::count(),
            ]);
        });
    }
}
