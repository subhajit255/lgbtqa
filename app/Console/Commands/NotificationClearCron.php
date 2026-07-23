<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class NotificationClearCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification-clear:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            Log::info('Notification Clear Cron Started on ' . Carbon::now()->format('Y-m-d') . ' ' . Carbon::now()->format('H:i'));
            $thirtyDaysAgo = now()->subDays(30);
            $notificationIds = Notification::where('created_at', '<', $thirtyDaysAgo)->pluck('id');
            if(!empty($notificationIds)){
                Notification::whereIn('id', $notificationIds)->forceDelete();
            }
        } catch (\Exception $e) {
            Log::error('Delete Notification Error ' . $e->getMessage());
        }
    }
}


