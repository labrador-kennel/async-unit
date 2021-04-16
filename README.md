# Async Testing

A PHP8 + Amphp testing framework built from the ground up with asynchronous programming 
in mind.

> This library is still in early development and is largely public at this point to garner feedback 
> and solicit potential ideas or pitfalls. Please open a PR or start a Discussion if you'd like to 
> contribute!

## Installation

```
composer require --dev cspray/labrador-async-testing
```

## Haven't you heard of PHPUnit?

I have! It is a great library, and I love it! If you look through my repositories you'll see that I 
have written a lot of unit and integration tests. In fact, this library is tested with PHPUnit! However, 
I do a lot of asynchronous programming and while the [amphp/phpunit-util]() library provides an adequate 
wrapper around PHPUnit for simple tests I was running into something that required a more comprehensive 
solution.

### The problem

I was writing integration tests that was really putting my applicatino through its paces. Each _test_ 
involved:

- Starting up its own event loop
- Connecting to the database
- Starting up an HTTP server

I quickly wrote up a thin abstraction layer around doing this and found myself chugging away with 
fairly complicated tests that involved some fairly hard things to test properly. I ran into a few snags,
mostly with the database connection pool configuration, but everything seemed to be going good. Then... I 
wrote one test too many.

Each test was interacting with a table on the database and with each one opening its own connection I 
quickly reached a point where my database was pounded and hanging, causing my tests to timeout 
and fail for seemingly no reason. I needed a way to open up a single database connection for the entire 
suite of integration tests. Unfortunately there's no way to easily do that with the current PHPUnit wrapper.
Hence, you see this project.

This library, while inspired in many ways by PHPUnit, is only meant to replace it in the specific scenarios 
laid out above. PHP needs a testing framework with asynchronicity built in as a first-class citizen. If you 
find yourself writing a lot of asynchronous code please give this library a look.

## User Guide

At the current moment there is only limited capacity to run, through not well-defined boilerplate, the following 
test suite. The attributes should be explicit in how this test would work.

```php
<?php

use Cspray\Labrador\AsyncTesting\Attribute\AfterAll;
use Cspray\Labrador\AsyncTesting\Attribute\BeforeAll;
use Cspray\Labrador\AsyncTesting\Attribute\Test;
use Cspray\Labrador\AsyncTesting\TestCase;

class ExampleTestCase implements TestCase {

    #[BeforeAll]
    public static function beforeTestCaseRan() {
    
    }
    
    #[AfterAll]
    public static function afterTestCaseRan() {
    
    }

    #[Test]
    public function ensureSomething() {
        // oops we don't have an assertion api yet!
    }

}
```

Before it is all said and done we want to support everything currently listed in the Roadmap. Specifically we want to 
be able to achieve the following as our first MVP.

### Sharing a database pool

```php
<?php

use Cspray\Labrador\AsyncTesting\TestCase;
use Cspray\Labrador\AsyncTesting\TestSuite;

// Maybe have a #[DefaultSuite] capability if you want to provide 1 TestSuite without marking every TestCase
class DatabaseTestSuite extends AbstractTestSuite implements TestSuite {

    #[BeforeTestCases]
    public function connectToDatabase() {
        $pool = yield getTestConfiguredPool();
        $this->set('dbPool', $pool);
    }

    #[AfterTestCases]
    public function closeDatabaseConnection() {
        $this->get('dbPool')->close();
    }

}

#[ForSuite(DatabaseTestSuite::class)]
class RepoTest extends AbstractTestCase implements TestCase {

    #[BeforeEach]
    public function loadFixture() {
        // yield some promises here to  store some data
        // implement a strategy to make sure database stays cleared out
    }
    
    #[Test]
    public function ensureRepoCount() {
        // $this->testSuite->get('dbPool') is the same $pool from above... shared across all TestCases used in this TestSuite
    }

}

#[ForSuite(DatabaseTestSuite::class)]
class AnotherDbTest extends AbstractTestCase implements TestCase {

    #[BeforeEach]
    public function loadFixture() {
        // yield some promises here to  store some data
        // implement a strategy to make sure database stays cleared out
    }
    
    #[Test]
    public function ensureRepoDeletion() {
        // $this->testSuite->get('dbPool') is the same $pool from above... shared across all TestCases used in this TestSuite
    }
}

// There's no #[ForSuite] attribute here
class NoDbTest extends AbstractTestCase implements TestCase {

    #[Test]
    public function ensureSomething() {
        // throws an exception... when you don't specify an explicit TestSuite you run on
        // an implicit TestSuite that has no state assigned to it
        $this->testSuite->get('dbPool');
    }
}
```

The point of this objective is to share the same database connection across many tests and have the Event Loop be in complete 
control of our test suite running. Testing webserver integrations would also be a good target for this type of architecture.

## Roadmap

- Async Assertion API
- Explicit evented system for detailing what has happened running TestSuites 
- Explicit TestSuite
- Assertion results printer
- Data providers
- Exception expectation
- Randomize test running
- PHPUnit Assertion wrapper (?)
- CLI tool