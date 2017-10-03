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
    protected $signature = 'extension:release {extensions?*} {--A|all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Release extension that last registered was 3 days before, if --all flag specified this will release all extensions.';

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
	$all_flag = $this->option('all');
	$extensions = $this->argument('extensions');

	if($extensions == null && $all_flag != null){
        	// Release all extensions
		try{
			$extensions = Extension::all();
			$bar = $this->output->createProgressBar(count($extensions));
			foreach($extensions as $ext){
				$ext->customer_name = null;
				$ext->token = null;
				$ext->last_registered = null;
				$ext->status = 1;
				$ext->save();
				$bar->advance();
			}
			$bar->finish();
			Log::info("[".Carbon::now('Asia/Phnom_Penh')."], Success, Successfully released all extensions, total count ".strval(count($extensions)));
			$this->line("[".Carbon::now('Asia/Phnom_Penh')."], Success, Successfully released all extensions, total count ".strval(count($extensions)));
		}catch(Exception $e){
			Log::info("Error releasing all extensions");
                        $this->line("[".Carbon::now('Asia/Phnom_Penh')."], Error, Error while releasing all extensions.");
		}
	}else if($extensions !=null && count($extensions) > 0 && $all_flag == null){
		$n_released_ext = 0;
		try{
			$n_relesed_ext = DB::table('extensions')
                        ->whereIn('extension', $extensions)
                        ->sharedLock()
                        ->update(['status'=>1, 'token'=>null, 'last_registered'=>null, 'customer_name'=>null]);
                        Log::info("[".Carbon::now('Asia/Phnom_Penh')."], Success, Successfully released ".$n_relesed_ext." list of extensions.");
                        $this->line("[".Carbon::now('Asia/Phnom_Penh')."], Success, Successfully released ".$n_relesed_ext." list of extensions.");
		}catch(Exception $e){
			Log::info("Error releasing list of extensions");
            $this->line("[".Carbon::now('Asia/Phnom_Penh')."], Error, Error while releasing list of extensions.".$e);
		}
	}else if($extensions == null && $all_flag == null){
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
                	->update(['status'=>1, 'token'=>null, 'last_registered'=>null, 'customer_name'=>null]);
			Log::info("[".Carbon::now('Asia/Phnom_Penh')."], Success, Successfully released ".$n_relesed_ext." extensions.");
            		$this->line("[".Carbon::now('Asia/Phnom_Penh')."], Success, Successfully released ".$n_relesed_ext." extensions.");

        	} catch (Exception $e) {

            		Log::info("Error releasing extension");
            		$this->line("[".Carbon::now('Asia/Phnom_Penh')."], Error, Error while releasing extensions.");
        	}
	}else{
		$this->line("Command line argument and flag wrong syntax");
	}
    }
}
