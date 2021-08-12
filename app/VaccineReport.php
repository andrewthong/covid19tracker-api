<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\ModularReport;

use App\Common;

/**
 * v2 report system: modular reporting tables
 * see README-report.md
 */
class VaccineReport extends ModularReport
{
    protected $table = 'vaccine_reports';

    // reporting attributes
    // processed and reference attributes must begin with change_ or total_

    // processed attributes are derived from reference data
    const processed_attrs = [
        'change_adults_vaccinations',
        'change_adults_vaccinated',
        'change_vaccinations_out_of_province',
        'change_vaccinated_out_of_province',
    ];

    // reference attributes are provided by data-entry activities
    const reference_attrs = [
        'total_adults_vaccinations',
        'total_adults_vaccinated',
        'total_vaccinations_out_of_province',
        'total_vaccinated_out_of_province',
    ];

    // other attributes that are not processed
    // Note: id, date, province does not need to be included here
    const other_attrs = [];

}
