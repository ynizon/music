<?php

namespace App\Observers;

use App\Actions\Cron;
use App\Models\Spotdl;

class SpotdlObserver
{
    /**
     * Handle the Spot "created" event.
     */
    public function created(Spotdl $spot): void
    {
        $cron = new Cron();
        $cron->makeCronFile();
    }

    /**
     * Handle the Spot "updated" event.
     */
    public function updated(Spotdl $spot): void
    {
        $cron = new Cron();
        $cron->makeCronFile();
    }

    /**
     * Handle the Spot "deleted" event.
     */
    public function deleted(Spotdl $spot): void
    {
        $cron = new Cron();
        $cron->makeCronFile();
    }

    /**
     * Handle the Spot "restored" event.
     */
    public function restored(Spotdl $spot): void
    {
        //
    }

    /**
     * Handle the Spot "force deleted" event.
     */
    public function forceDeleted(Spotdl $spot): void
    {
        //
    }
}
