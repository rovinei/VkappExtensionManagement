<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
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
        $n_relesed_ext = 0;

        // Relese extension
        try {
            $n_relesed_ext = DB::table('extensions')
                ->whereNotNull('token')
                ->whereNotNull('last_registered')
                ->where('last_registered', '<=', $date->subDays(3))
                ->sharedLock()
                ->update(['status'=>1, 'token'=>null,' last_registered'=>null]);

            $this->line("[".Carbon::now('Asia/Phnom_Penh')."], Success, Successfully released ".$n_relesed_ext." extensions.");

        } catch (Exception $e) {

            Log::info("Error releasing extension");
            $this->line("[".Carbon::now('Asia/Phnom_Penh')."], Error, Error while releasing extensions.");
        }

    }
}
