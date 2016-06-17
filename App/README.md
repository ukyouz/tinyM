# tinyM
A PHP framework with Model.

This is a simple PHP framework inspired by Laravel. Now we offer some Classes in our work, here list some usages of example. Hope these help you get an general idea of this simple framework.

## App\

To create use a new Class `Model`, here are some instructions.

### Create Example.php

inside Example.php

```php
<?php
namespace App;
use App\Eloquent\Model;

require_once 'Eloquent/Model.php';

Class Example extends Model
{
	protected $table = 'tablename';
	protected $primaryKey = 'id';
	protected $fillable = ['column1', 'column2'];
}
```

[Details](/ukyouz/tinyM/tree/master/App/Eloquent) about usage of Class `Model`.