<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('emails:overview')->everyTwoMinutes()->withoutOverlapping();
Schedule::command('emails:fetch')->everyTenMinutes()->withoutOverlapping();