# COVID-19 Tracking API

This is a Laravel-powered API source for tracking COVID-19 cases in a region. It is currently used by the COVID-19 Tracker Canada project.

See it in action at https://api.covid19tracker.ca

## Getting Started

### Prerequisites

A hosting environment configured for Laravel 8.x should have no issues. Please see Laravel's [Server Requirements](https://laravel.com/docs/8.x/installation#server-requirements) for up-to-date information.

#### MySQL Database

Data is stored in a MySQL database. You will need to create one for this project to use.

#### Cache

A few routes use [caching](https://laravel.com/docs/8.x/cache) and has been tested with `file` and `redis` cache drivers.

#### Homestead

[Laravel Homestead](https://laravel.com/docs/8.x/homestead) is also supported out of box for local development.

> **Important** In order to use the CSV Seeders, you must have PHP 7.4 installed. Most deployments typically install version 7.2.

### Installing

#### 1. Clone project
```
git clone git@github.com:andrewthong/covid19tracker-api.git
cd covid19tracker-api
```

#### 2. Install Dependencies
```
composer install
```

#### 3. Configure Environment
```
cp .env.example .env
```
Update the .env with database credentials.

#### 4. Generate Key
```
php artisan key:generate
```

#### 5. Run Migrations
```
php artisan migrate
```

#### 6. Data Management Support

Required if you plan to run the [C19T-Manager](https://github.com/andrewthong/covid19tracker-manage) companion as well.

```
php artisan passport:install
```

From the resulting output, copy personal access to `PERSONAL_CLIENT_SECRET` and password grant to `PASSWORD_CLIENT_SECRET` in your .env file.

Update `MANAGE_URL` in your .env file with the URL where C19T-Manager is deployed.

Then create your first admin user:

```
php artisan tinker
$user = new \App\User
$user->name = 'Fname';
$user->email = 'fnamelname@example.net';
$user->password = Hash::make('some_password_123');
$user->assignRole('admin');
$user->save();
```

Roles are currently simplified to admin and editor. This project uses [laravel-permission](https://github.com/spatie/laravel-permission) to manage roles.

## Deployment

You can now launch. Instructions vary depending on your setup (LEMP server, Homestead...). When ready, browse to the URL as configured and you should see the API documentation.

## Seeding Data

The database seeders are specific to Canada, but can be tweaked for another region as needed.

### Provinces and Territories

Running this seed inserts all 13 provinces and territories of Canada.

```
php artisan db:seed --class=ProvinceSeeder
```

### Health Regions

Running this seed inserts health regions in Canada.

```
php artisan db:seed --class=HealthRegionsSeeder
```

### Reports

This is a sample of report data. Be sure to adjust the province to match the province data accordingly.

```
php artisan db:seed --class=ReportSeeder
```

### Cases and Fatalities

These do not have a sample CSV, but the seeder classes are available.

```
php artisan db:seed --class=CaseSeeder
php artisan db:seed --class=FatalitySeeder
```

### Postal Districts

Used to determine province from rapid test submissions.

```
php artisan db:seed --class=PostalDistrictSeeder
```

## Data Management

[C19T-Manager](https://github.com/andrewthong/covid19tracker-manage) is a companion app to facilitate reporting.

The URL where C19T-Manager is hosted will need to be whitelisted for CORS. Update `MANAGE_URL` in `.env` accordingly. Use a comma for multiple URLs.

```
MANAGE_URL=http://localhost:1234,http://localhost:5678
```

## Open Submissions

Currently only rapid tests are supported.

All open submissions can use [reCAPTCHA](https://www.google.com/recaptcha/about/) to minimize abuse. To enable this, sign-up and add your secret key to the `.env` for `RECAPTCHA_SECRET_KEY`.

### Rapid Tests

```
POST /collect/rapid-test
```

Example payload:
```
{
  "test_result": "negative",
  "test_date": "2022-01-23",
  "postal_code": "V3A",
  "age": "20-29",
  "g-recaptcha-response": "{if enabled}"
}
```

## Processing Reports

Configure cron to run [Task Scheduling](https://laravel.com/docs/8.x/scheduling#running-the-scheduler) for Laravel and report changes done via the C19T-Manager will trigger processing automatically.

### Manual Processing

Processing reports is accessible through the CLI. Not necessary if you have configured the task scheduler. This is needed if not using the C19T-Manager and updating report tables directly.

```
php artisan report:process
```

The prompt should be straightforward. Out-of-box, the processing function:

* Sums the daily **case** and **fatality** totals for each province
* Calculates the day-to-day change in **tests**, **hospitalizations**, **criticals** (intensive care), **recoveries** and **vaccinations** from the `reports` table.
* Stores this summarized data, which is used by the reports API

#### Health Region (HR) Reports

Support for health regions was added in late 2020. Health regions have a separate reports table (`hr_reports`) and can be processed from the CLI as well:

```
php artisan report:processhr
```

* unlike standard reports, health region reports calculates **case** and **fatality** from the `hr_reports` table.

## Caching

A few select routes have caching enabled to minimize database load and speed performance of the API.

The cache is flushed when a report processing operation finishes. The cache can also be manually flushed from the C19T-Manager companion app.

## Tests

### Configure testing env

By default, tests will use the APP_ENV defined in `phpunit.xml`. Create `.env.testing` with the following template and configure it as needed:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=test
DB_USERNAME=test
DB_PASSWORD=secret
# used for partner-specific route
PARTNER01=
```

### Running tests

```
php artisan test
```

## Built With

* [Laravel](https://laravel.com/) - The web framework used

## Authors

* **Andrew Thong** - *Initial work*
* **Noah Little** - *Concept, data consultation*

See also: [COVID19Tracker.ca acknowledgements](https://covid19tracker.ca/acknowledgements.html).

## License

This project is licensed under the MIT License.

## Acknowledgments

* [COVID-19 Tracker Canada](https://covid19tracker.ca) - the tracker that inspired this project
* [Adminer](https://www.adminer.org) - convenient single-line database administration
* [DigitalOcean Hub for Good](https://www.digitalocean.com/community/pages/covid-19) - infrastructure
