<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\CronDonateNotificationController;
use Illuminate\Console\Command;

class ChkPostDonateStatus4 extends Command
{
    protected $CronDonateNotificationController;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'donate:chkstatus4';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check post donate status 4 using cron job.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(CronDonateNotificationController $CronDonateNotificationController)
    {
        $this->CronDonateNotificationController = $CronDonateNotificationController;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->CronDonateNotificationController->index();
    }
}
