# tinyM - Model
A PHP framework with Model.

This is a simple PHP framework inspired by Laravel. Now we offer some Classes in our work, here list some usages of example. Hope these help you get an general idea of this simple framework.

## Introduction

After configurations are all done in your database, you can start create "Model" for it. Each database table has a corresponding model which is used to interact with that table. Models allow you to query for data in your tables, as well as insert new records into the table.

#### Database Connection

You have to setup configuraions in `App/Eloquent/DB.php` first, to connect to your own database.

``` php
<?php

Class DB
{
	//
    public static function connect() {
    	// setup here
        self::$connection = mysqli_connect('host', 'username', 'password', 'database');
        // …
    }
    
    // …
}
```

## Definine

To create use a new Model Class `Student`, just add a new file called `Student.php` under folder `App/`.

Inside Example.php

``` php
<?php

namespace App;
use App\Eloquent\Model;

require_once 'Eloquent/Model.php';

Class Example extends Model
{
	/**
     * The table associated with the model.
     * @var string
     */
	protected $table = 'tablename';
    
	protected $primaryKey = 'id';
	protected $fillable = ['column1', 'column2'];
}
```

## Usages

before using any of Models under folder `App\`, you need to include these files by an `autoload.php` located in folder `vender/`, and then `use` with their correct paths.

``` php
<?php

include_once __DIR__ . "/../../vender/autoload.php";
use App\Student;
```

### Retrieving Models

To retrieve all data in database.

``` php
$students = Student::all()->get();
```

Or find by primary key.

``` php
$students = Student::find(1)->get();
```

#### Adding Additional Constraints

Alternatively, you may want some specific conditions to limit the results.

``` php
$students = Student::select('id', 'name', 'major')
	->where('grade', '>', 2)
	->orderBy('name', 'desc')
    ->get();
```

#### Using Result Datasets

The method `get()` returns as an Array of all Object datasets.

``` php
Array
(
    [0] => stdClass Object
        (
            [id] => 1
            [column1] => 'value1'
            [column2] => 'value2'
        }
	[1] => stdClass Object
        (
            [id] => 2
            [column1] => 'value1'
            [column2] => 'value2'
        }
)
```

Or the Object if there is only 1 dataset.

``` php
stdClass Object
(
    [id] => 1
    [column1] => 'value1'
    [column2] => 'value2'
}
```

[Details](/App/Eloquent) about usage of Class `Model`.