<?php

namespace App\Jobs;

use App\Models\LoggerFail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LoggerFailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $log;

    public function __construct($log)
    {
        $this->log = $log;
    }

    public function handle()
    {
        LoggerFail::create([
            'log' => $this->log
        ]);
    }
}


