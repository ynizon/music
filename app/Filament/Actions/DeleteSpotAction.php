<?php

namespace App\Filament\Actions;

use App\Actions\Cron;
use Filament\Actions\DeleteAction;

class DeleteSpotAction extends DeleteAction
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->after(function (): void {
            $cron = new Cron();
            $cron->makeCronFile();
        });
    }
}
