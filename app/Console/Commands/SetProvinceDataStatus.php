<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Common;
use App\Province;

class SetProvinceDataStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'province:data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set the data status for provinces';

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
        $curr_env = config('app.env');

        $this->line('');

        $this->line("     ___  ___  ____ _   _______  _______________");
        $this->line("    / _ \/ _ \/ __ \ | / /  _/ |/ / ___/ __/ __/");
        $this->line("   / ___/ , _/ /_/ / |/ // //    / /__/ _/_\ \  ");
        $this->line("  /_/  /_/|_|\____/|___/___/_/|_/\___/___/___/  ");

        $this->line('');

        $this->line(' # <fg=black;bg=white>Provinces data status setter</>');
        $this->line(' # COVID-19 Tracker API Database v1.0 #');
        // $this->line(' # github.com/andrewthong/covid19tracker-api');
        $this->line('');
        $this->line(" # Environment: <fg=yellow>${curr_env}</>");

        //load provinces
        $province_codes = Common::getProvinceCodes();

        //move to common if needed later
        $status_arr = [
            'Reported',
            'In progress',
            'Waiting for report',
            'No report expected today',
        ];

        //prep status choices
        $status_choices = $status_arr;
        array_unshift( $status_choices, "");
        unset( $status_choices[0] );
        $status_choices['0'] = '{custom status}';

        //prep province choices
        $province_choices = $province_codes;
        sort( $province_choices );
        // array_unshift( $province_choices, "ALL" );

        //request parameters
        $the_status = $this->choice( 'Data status to set', $status_choices, 1 );

        if( $the_status === '{custom status}' ) {
            $the_status = $this->ask( 'Please enter a custom status (32 characters max)' );
        }

        $the_province = $this->choice( 'Set for all Provinces or specify', [
            '1' => 'All Provinces',
            '2' => 'Specify'
        ], 1 );

        $in_provinces = [];
        if( $the_province === 'Specify' ) {
            // request user to specify
            $this->line( "Provinces: ".implode( ' ', $province_choices ) );
            $provinces = $this->ask( 'Please specify which Province(s) (separate multiple with space)' );
            $provinces = explode( ' ', $provinces );
            $in_provinces = array_intersect( $provinces, $province_codes );
        } else {
            // all provinces
            $in_provinces = $province_codes;
        }

        // perform update
        if( count($in_provinces) > 0 ) {
            Province::whereIn( 'code', $in_provinces )
                ->update( [
                    'data_status' => $the_status
                ] );
        }

        // wrap-up
        $p = implode( ' ', $in_provinces );

        $this->line(' * * *');
        $this->line('');
        $this->line(" <fg=green;bg=black>Province data status set.</>");
        $this->line(" <fg=cyan>{$p}</> set to <fg=cyan>{$the_status}</>");
        $this->line('');
        $this->line(' Have a nice day ツ');
        $this->line('');

    }
}
