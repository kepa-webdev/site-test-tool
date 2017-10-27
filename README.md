# Site tester tool

This is a lightweight script that uses [Mink](http://mink.behat.org/en/latest/) 
for running simple tests to ensure web pages work as expected.

This is more like an exercise of how to use Mink.

It's designed to be used within a [Drupal project](https://github.com/drupal-composer/drupal-project)
but can of course run standalone.

Tests are defined in config.inc as follows:
``` 
$tests = [
  [
    'page' => 'https://foo/bar/baz', // required
    'auth' => user/pass, // optional, or set to false to reset
    'response_code' => 200, // optional, if not present, 200 used as default
    'search' => '//*[@id="some-element-id"]/option[2]', // optional
    'search_type' => 'xpath', // required if 'search' is present
    'expected' => 'if (strlen($got) > 5) return TRUE;', // required if 'search' is present
    'expected_type' => 'code', // optional, if present and value is 'code', 'expected' will be eval()'d,
                               // otherwise '!==' comparison will be done
  ],
];
``` 

So it's possible to test only response code (default 200).
If we've gotten a response code other than expected, content test for that
particular test is skipped.

You can specify, for example, a string in 'expected' field, but also a code
block, if 'expected_type' is set to 'code'.

## Installation

When using standalone, run:
```
git clone https://github.com/kepa-webdev/site-test-tool
composer install
```

If you like to have it in Drupal project, go to the project 
root and call:
```
composer require kepa-webdev/site-test-tool:* behat/mink behat/mink-goutte-driver
```

Unfortunately composer might not necessarily get the dependencies so you might 
need to require them explicitly. And of course, you need to add something like this
to your `repositories`:

```
{
    "type": "package",
    "package": {
        "name": "kepa-webdev/site-test-tool",
        "type": "php-library",
        "version": "<version>",
        "source": {
            "url": "<your local git path>",
            "type": "git",
            "reference": "origin/<branch containing your site conf>"
        }
    }
},
```
