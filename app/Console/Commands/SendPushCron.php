<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\PushNotificationQueue;
use App\Traits\PushNotificationTrait;

class SendPushCron extends Command
{
    use PushNotificationTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send-push:cron';

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
            // Log::info('Send Push Cron Started on ' . Carbon::now()->format('Y-m-d') . ' ' . Carbon::now()->format('H:i'));
            $pushQueue = PushNotificationQueue::take(30)->get();
            if($pushQueue){
                foreach ($pushQueue as $push) {
                    $this->sendPushNotificationQueue($push);
                }
            }
        } catch (\Exception $e) {
            Log::error('Send Push Error ' . $e->getMessage());
        }
    }
}


