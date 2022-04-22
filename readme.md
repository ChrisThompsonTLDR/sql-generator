[![Packagist License](https://poser.pugx.org/froiden/sql-generator/license.png)]()
[![Total Downloads](https://poser.pugx.org/froiden/sql-generator/d/total.png)](https://packagist.org/packages/froiden/sql-generator)


# LARAVEL SQL GENERATOR
Convert Laravel migrations to raw SQL scripts


## Usage

### Step 1: Install Through Composer

```bash
composer require "froiden/sql-generator:dev-master" --dev
```

### Step 2: Now publish the vendor
```bash
php artisan vendor:publish
```


### Step 3: Run command
Then you will need to run these commands in the terminal

```bash
php artisan sql:generate
```

This Will Generate "database.sql" in 'database/sql' directory
If you want change path directory go to 'config/sql_generator.php' change value 'defaultDirectory'
