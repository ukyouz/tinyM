# tinyM - Http
A PHP framework with Model.

This is a simple PHP framework inspired by Laravel. Now we offer some Classes in our work, here list some usages of example. Hope these help you get an general idea of this simple framework.

## App\Http\Request

The `Request` Class inlcudes all input values of `$_GET` and `$_POST`, and also you can get access to `$_FILES` with Request class.

### Retrieving Input Value

Using a few simple methods, you may access all user input from your App\Http\Request instance. You do not need to worry about the HTTP verb used for the request, as input is accessed in the same way for all verbs.

#### Basic Method Getting Value

``` php
$name = Request::input('name');
// $name = "John"
```

Or set a default value `Sam` for input `name` if the input value doesn't exist.

``` php
$name = Request::input('name', 'Sam');
```

When working on forms with "array" inputs, you may use dot notation to access the arrays:

``` php
$course_name = Request::input('courses.0.name');
```

#### Checking If an Input Value is Present

``` php
if (Request::has('name')) {
	// …
}
```

### Getting Requests Instance

To fetch request instance with all inputs, use `All` method.

``` php
$requests = Request::all();
```

Getting Only Some Of The Request Input

``` php
$requests = Request::only('name', 'grade');

$requests = Request::except('password', 'birthday');
```

### Files

Sometimes you may need access to Files.

#### Retrieving An Uploaded File

Calling the `file` method will return an `UploadedFile` Class instance, so you can do more for your files.

``` php
$homework = Request::file('homework');
```

#### Checking If an File Input is Present

``` php
if (Request::hasFile('homework')) {
	// …
}
```

#### Uploading File to Directory

Once you get a file instance from request, you can upload this file to specific directory in your server by using `move` method.

``` php
$homework->move('/server/path/to/upload/folder/');
```

#### Check if failed Or Not

``` php
if ($homework->failed()) {
	echo $file->error();
}
```

#### Retrieving Files Data

The method `get` returns as an Array of all Files.

``` php
Array
(
    [homework] => stdClass Object
        (
            [name] => uploaded-file-name.pdf
            [type] => application/pdf
            [tmp_name] => C:\wamp\tmp\php7627.tmp
            [error] => 0
            [size] => 127153
        )
    [profile_picture] => stdClass Object
        (
            [name] => uploaded-picture-name.jpg
            [type] => image/png
            [tmp_name] => C:\wamp\tmp\php7628.tmp
            [error] => 0
            [size] => 189077
        )
)
```

The method `first` returns the first File.

``` php
stdClass Object
(
    [name] => uploaded-file-name.pdf
    [type] => application/pdf
    [tmp_name] => C:\wamp\tmp\php7627.tmp
    [error] => 0
    [size] => 127153
)
```