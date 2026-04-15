<?php

namespace App\Console\Commands;

use App\Jobs\JobSendEmail;
use Illuminate\Console\Command;

class SendEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:send';
    protected $description = 'Dispatch email sending job';

    /**
     * The console command description.
     *
     * @var string
     */

    /**
     * Execute the console command.
     */
    public function handle()
    {
        JobSendEmail::dispatch();
        $this->info('Email job dispatched successfully!');
    }
}
