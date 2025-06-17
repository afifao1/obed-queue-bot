<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use SergiX44\Nutgram\Nutgram;

class SendLunchReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $chatId;

    public function __construct($chatId)
    {
        $this->chatId = $chatId;
    }

    public function handle()
    {
        $bot = app(Nutgram::class);
        $bot->sendMessage($this->chatId, '⏳ Obed tugashiga 5 daqiqa qoldi!');
    }
}

