<?php
/**
 * Simple script for running certain web request tests. 
 * 
 * See README.md for more.
 */

if (is_file(__DIR__ . '/vendor/autoload.php')) {
  require __DIR__ . '/vendor/autoload.php';
} elseif (is_file(__DIR__ . '/../../autoload.php')) {
  require __DIR__ . '/../../autoload.php';
} else {
  echo 'Site-test-tool dependencies not found, be sure to run "composer install".' . PHP_EOL;
  exit(1);
}

use \Behat\Mink\Driver\GoutteDriver;
use \Behat\Mink\Session;

require_once __DIR__ . '/config.inc';

/** @var GoutteDriver $driver */
$driver = new GoutteDriver();
/** @var \Behat\Mink\Session $session */
$session = new Session($driver);

$failures = [];
runTests($session, $tests);
$session->stop();
if (!isCli()) {
  header("Content-type: text/plain");
}
if (empty($failures)) {
  printf("%s\n", "OK");
}
else {
  printf("FAILURES: %d\n\n", count($failures));
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
    if (is_null($response)) {
      fail($test['page'], 'some response', 'no response (i.e. no xpath found)');
    }
    else {
      $value = $response->getText();
      test($test['page'], $test['expected'], $value, $test['expected_type']);
    }
  }
}

function fail($page, $expected, $got) {
  global $failures;

  $failures[] = sprintf(
    "Error on page %s, expected '%s', got '%s'.\n",
    $page, $expected, $got
  );
}

function isCli() {
  return php_sapi_name() === 'cli';
}

