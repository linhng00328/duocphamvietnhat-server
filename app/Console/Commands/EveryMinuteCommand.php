<?php

namespace App\Console\Commands;


use Illuminate\Console\Command;

class EveryMinuteCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:every_minute';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gửi thông báo tới customer';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
    }
}
