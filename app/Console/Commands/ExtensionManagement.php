<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Extension;
use Carbon\Carbon;
use Exception;
use DB;

class ExtensionManagement extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'extension:release';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Release extension that last registered diff {day}';

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
     * @return mixed
     */
    public function handle()
    {

        // Set locale and create datetime
        Carbon::setLocale('km');
        $date = Carbon::now('Asia/Phnom_Penh');

        // Relese extension

        try {
            DB::table('extensions')
                ->whereNotNull('token')
                ->whereNotNull('last_registered')
                ->where('last_registered', '<=', $date->addHours(6))
                ->sharedLock()
                ->update(['token'=>null,'last_registered'=>null]);
        } catch (Exception $e) {

        }

    }
}
