# COVID-19 Tracking API

This is a Laravel-powered API source for tracking COVID-19 cases in a region. It is currently used by the COVID-19 Tracker Canada project.

See it in action at https://api.covid19tracker.ca

## Getting Started

### Prerequisites

A hosting environment configured for Laravel 7.x should have no issues. Please see Laravel's [Server Requirements](https://laravel.com/docs/7.x/installation#server-requirements) for up-to-date information.

#### MySQL Database

Data is stored in a MySQL database. You will need to create one for this project to use.

#### Homestead

[Laravel Homestead](https://laravel.com/docs/7.x/homestead) is also supported out of box for local development.

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
Modify the .env with the proper database credentials.

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

## Data Management

[C19T-Manager](https://github.com/andrewthong/covid19tracker-manage) is a companion app to facilitate reporting. There are plans to migrate other functionality here including processing reports.

## Processing Reports

Processing the reports is done through the CLI. Run the following command in the project root:

```
php artisan report:process
```

The prompt should be straightforward. Out-of-box, the processing function:

* Sums the daily **case** and **fatality** totals for each province
* Calculates the day-to-day change in **tests**, **hospitalizations**, **criticals** (intensive care), **recoveries** and **vaccinations** from the `reports`.
* Stores this summarized data, which is used by the reports API

## Built With

* [Laravel](https://laravel.com/) - The web framework used

## Authors

* **Andrew Thong** - *Initial work* - [GitHub](https://github.com/andrewthong)
* **Noah Little** - *Concept, data consultation*

See also: COVID19Tracker.ca [acknowledgements](https://covid19tracker.ca/acknowledgements.html).

## License

This project is licensed under the MIT License.

## Acknowledgments

* [COVID-19 Tracker Canada](https://covid19tracker.ca) - the tracker that inspired this project
* [Adminer](https://www.adminer.org) - convenient single-line database administration
* [DigitalOcean Hub for Good](https://www.digitalocean.com/community/pages/covid-19) - infrastructure

