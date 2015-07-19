# sudo-bible
Open source Bible API.

## Installation & Setup

...

Instantiate Sudo Bible with your database credentials, and optional translation
preference (default translation is the [World English Bible](http://ebible.org/)):

```php
$oBible = new SudoBible([
	'db_host' => 'localhost',
	'db_user' => 'my_user',
	'db_pass' => 'my_super_secure_password',
	'db_name' => 'my_db',
	'translation' => 'ASV',
]);
```

Call the following method once when first setting up, then delete it once your
tables have been created:

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
$oBible->verse('John', 3, 16);
```

Get an entire chapter:
```php
$oBible->chapter('John', 3);
```

Get a passage (supply beginning & end verses):
```php
$oBible->ref('John', 3, 16, 17); // John 3:16-17
$oBible->ref('Hebrews', 5, 11, 6, 2); // Hebrews 5:11-6:2
```

###Return values
`verse()` returns a single verse, while `chapter()` and `passage()` each return
an array of verses. The structure of a verse is as follows:
```php
[
	'book_name' => 'Revelation',
	'book_abbr' => 'Rev',
	'chapter' => 4,
	'verse' => 8,
]
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
