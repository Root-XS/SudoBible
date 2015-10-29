# SudoBible
Open source Bible API.

## Installation & Setup

Instantiate SudoBible with your database credentials, and optional translation
preference (default translation is the [World English Bible](http://ebible.org/),
a modern, public-domain, English translation):

```php
$oBible = new \RootXS\SudoBible([
	'db_host' => 'localhost',
	'db_user' => 'my_user',
	'db_pass' => 'my_super_secure_password',
	'db_name' => 'my_db',
	'translation' => 'ASV',
]);
```

Call the following method once when first setting up, then delete this code
once your tables have been created:

```php
$oBible->install();
```

Leaving this code in your application won't break anything. It will just cause
your application to make several superfluous database queries each time it runs.
Therefore, it is recommended to run this once and then remove it from your code.

Alternatively, you can manually run the queries found in the `queries` directory.
Find the sub-directory for your database type (e.g., `mysql`) and first run the
`create` scripts, in order. Then run the `insert` scripts.

## Basic usage

Get a single verse:
```php
$oPassage = $oBible->verse('John', 3, 16);
```

Get an entire chapter:
```php
$oPassage = $oBible->chapter('John', 3);
```

Get a passage (supply beginning & end verses):
```php
$oPassage = $oBible->ref('John', 3, 16, 17); // John 3:16-17
$oPassage = $oBible->ref('Hebrews', 5, 11, 6, 2); // Hebrews 5:11-6:2
```

## The SudoBiblePassage Object
`verse()`, `chapter()`, and `ref()` each return a `SudoBiblePassage` object
which contains the requested passage and provides utilities for manipulating it.

### Printing the passage
The `SudoBiblePassage` object employs the `__toString()` magic method, allowing
you to simply `echo` or `print` the object.
```php
echo $oBible->verse('John', 3, 16);
```
*Output:*
> For God so loved the world, that he gave his one and only Son, that whoever
> believes in him should not perish, but have eternal life. (John 3:16)

### Styling the passage

The `SudoBiblePassage` object provides a few methods for styling the string output.
```php
$oPassage = $oBible->ref('John', 3, 16, 17)
	->numberVerses() // adds verse numbers to the passage string
	->useHTML(); // adds some HTML styling to the passage string
echo $oPassage;
```
*Output:*
> <sup>16</sup> For God so loved the world, that he gave his one and only Son,
> that whoever believes in him should not perish, but have eternal life.
> <sup>17</sup> For God didn't send his Son into the world to judge the world,
> but that the world should be saved through him. <i>(John 3:16-17)</i>

All of the styling methods accept a boolean parameter, so you can switch
the styling off if it was previously turned on:
```php
$oPassage->numberVerses(false);
```
The boolean flag defaults to `true`, so if you just want to turn the feature "on,"
no parameter is necessary.

### Navigating the passage

The `SudoBiblePassage` object also provides a mechanism for proceding beyond the chosen passage:
```php
$oPassage = $oBible->verse('John', 3, 16);
for ($i=0; $i<2; $i++) {
	echo $oPassage;
	$oPassage = $oPassage->nextVerse();
}
```

## Deleting or refreshing the database

To delete the `sudo_bible_*` tables, run this once in your PHP code:

```php
$oBible->uninstall();
```
Or, you can manually run the scripts found in `queries/{DB_TYPE}/drop`.

To refresh the tables (after an update to the SudoBible repo, for example), you
can run this in your PHP:

```php
$oBible->reinstall();
```

This simply combines `uninstall()` with `install()`. You may also manually run
the query scripts in `drop`, `create`, and `insert`, in that order.
