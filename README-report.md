# V2 Report System

Initial work to make reporting tables modular and not have to repeat processing scripts.

## Quick Setup

### Migration
```
php make:migration create_{new_report_name}_table
```
#### Required columns
```
$table->id();
$table->date('date');
$table->string('province', 8);
```
Columns that will be processed should be prefixed with `changed_` or `total_`

### Model
```
php make:model {NewReportName}
```
*or make a copy of VaccineReport.php as you will need to modify the attrs() and reportAttrs() accordingly.

### Console
Add new entry to `$supported_tables` in `app/Console/Command/FillReports.php`

### Other
Add new entry for `availableReports` in `app/Common.php`

Make any adjustments to `getReports` and `saveReports` in `app/Http/Controllers/ManageController.php` for C19T-manage support.
