#!/usr/bin/env php
<?php
/**
 * Script for running certain web request tests. Tests are defined in ./config.inc,
 * config format as follows:
 *
   $tests = [
     [
       'auth' => user/pass, // optional, or set to false to reset
       'page' => 'https://foo/bar/baz', // required
       'response_code' => 200, // optional, if not present, 200 used as default
       'search' => '//*[@id="select-org-type"]/option[2]', // optional
       'search_type' => 'xpath', // required if 'search' is present
       'expected' => 'if (strlen($got) > 5) return TRUE;', // required if 'search' is present
       'expected_type' => 'code', // optional, if present and value is 'code', 'expected' will be eval()'d,
                                  // otherwise '!==' comparison will be done
     ],
   ];
 *
 * So it's possible to test only response code (default 200).
 * If we've gotten a response code other than expected, content test for that
 * particular test is skipped.
 */

if (is_file(__DIR__ . '/vendor/autoload.php')) {
  require __DIR__ . '/vendor/autoload.php';
} elseif (is_file(__DIR__ . '/../../autoload.php')) {
  require __DIR__ . '/../../autoload.php';
} else {
  echo 'Site-test-tool dependencies not found, be sure to run "composer install".' . PHP_EOL;
  exit(1);
}

use Behat\Mink\Driver\GoutteDriver;
use Behat\Mink\Session;

require_once __DIR__ . '/config.inc';

/** @var GoutteDriver $driver */
$driver = new GoutteDriver();
/** @var \Behat\Mink\Session $session */
$session = new Session($driver);

$failures = [];
runTests($session, $tests);
$session->stop();
if (empty($failures)) {
  echo "OK";
}
else {
  echo "FAILURES: " . count($failures);
  echo "\n\n";
  foreach ($failures as $failure) {
    echo $failure;
  }
}

function test($page, $expected, $got, $type = null) {
  if (!empty($type) && $type === 'code') {
    $test_result = eval($expected);
  }
  else {
    $test_result = ($expected === $got);
  }
  if (!$test_result) {
    fail($page, $expected, $got);
    return false;
  }
  return true;
}

function runTests($session, $tests) {
  foreach ($tests as $test) {
    if (empty($test['page'])) {
      // This is a failure in config, ignoring test.
      continue;
    }
    /** @var \Behat\Mink\Session $session */
    if (empty($test['auth'])) {
      $session->setBasicAuth(false);
    }
    else {
      list($user, $password) = preg_split('!/!', $test['auth']);
      $session->setBasicAuth($user, $password);
    }
    $session->visit($test['page']);
    if (empty($test['response_code'])) {
      $expected_response_code = 200;
    }
    else {
      $expected_response_code = $test['response_code'];
    }

    if (!test($test['page'], $expected_response_code, $session->getStatusCode())) {
      continue;
    }

    if (empty($test['search'])) {
      continue;
    }
    $page = $session->getPage();
    /** @var \Behat\Mink\Element\NodeElement $response */
    $response = $page->find($test['search_type'], $test['search']);
    $value = $response->getText();
    test($test['page'], $test['expected'], $value, $test['expected_type']);
  }
}

function fail($page, $expected, $got) {
  global $failures;

  $failures[] = sprintf(
    "Error on page %s, expected '%s', got '%s'.\n",
    $page, $expected, $got
  );
}



