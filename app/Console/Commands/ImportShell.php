<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ImportShell extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:shell';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Helper to run python commands through artisan';

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
        // check if env is enabled
        if (env('IMPORT_SHELL_ENABLED') ) {
            $path = env('IMPORT_SHELL_PATH');
            $script = env('IMPORT_SHELL_SCRIPT');
            if ( $path && $script ) {
                $this->info('Running script at ' . $path);
                if ( file_exists($path.$script) ) {
                    // limit to python3 script
                    $output = exec( "cd {$path} && python3 {$script}" );
                    $this->info("> {$output}");
                } else {
                    $this->error('Script does not exist');
                }
            } else {
                $this->error('Path/script is not set');
            }
        }
        return Command::SUCCESS;
    }
}
