# AsyncUnit

[![Unit Tests](https://github.com/labrador-kennel/async-testing/actions/workflows/php.yml/badge.svg)](https://github.com/labrador-kennel/async-testing/actions/workflows/php.yml)

A testing framework, with a focus on integration testing, that treats Amp's Loop as a first-class citizen!

## Installation

```
composer require --dev cspray/labrador-async-unit
```

## Hello, AsyncUnit

Although AsyncUnit can satisfy the needs of most unit and integration tests it was really designed for a specific type of 
test which can be challenging to run properly even in synchronous contexts. The "canonical" AsyncUnit test example 
is below and demonstrates the core functionality of the framework.

```php
<?php

namespace Acme\MyApp;

use Cspray\Labrador\AsyncUnit\Attribute\AfterAll;
use Cspray\Labrador\AsyncUnit\Attribute\BeforeAll;
use Cspray\Labrador\AsyncUnit\Attribute\BeforeEachTest;
use Cspray\Labrador\AsyncUnit\Attribute\AfterEachTest;
use Cspray\Labrador\AsyncUnit\Attribute\BeforeEach;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\Attribute\TestSuite as UseTestSuite;
use Cspray\Labrador\AsyncUnit\TestCase;
use Cspray\Labrador\AsyncUnit\TestSuite;
use Amp\Success;
use Amp\Delayed;
use function Amp\Postgres\pool;

class DatabaseTestSuite extends TestSuite {

    #[BeforeAll]
    public function connectToDatabase() {
        // In test situations we want to make sure we're dealing with the same connection so we can properly clean up data
        $pool = pool(connectionConfig(), maxConnections: 1, resetConnections: false);
        $this->set('pool', $pool);
    }
    
    #[BeforeEachTest]
    public function startTransaction() {
        yield $this->get('pool')->query('START TRANSACTION');
    }
    
    #[AfterEachTest]
    public function rollback() {
        yield $this->get('pool')->query('ROLLBACK');
    }
    
    #[AfterAll]
    public function closeDatabase() {
        $this->get('pool')->close();
    }
    
}

// The name of this class doesn't matter... you only need to ensure you extend TestCase
#[UseTestSuite(DatabaseTestSuite::class)]
class MyDatabaseTestCase extends TestCase {

    // Again, none of the method names matter... just make sure you're annotating with the correct Attribute
    #[BeforeEach]
    public function loadFixture() {
        yield someMethodThatLoadsData($this->testSuite()->get('pool'));
    }
    
    #[Test]
    public function ensureSomethingHappens() {
        yield new Delayed(100); // just to show you we're on the loop
        // These values could be retrieved from the database
        $this->assert()->stringEquals('foo', 'foo');
    }
    
    #[Test]
    public function ensureSomethingAsyncHappens() {
        yield new Delayed(100);
        yield $this->asyncAssert()->stringEquals('foo', new Success('foo'));
    }
    
    #[Test]
    public function makeSureYouAssertSomething() {
        // a failed test because you didn't assert anything!
    }
    
}
    
class MyNormalTestCase extends TestCase {
    
    #[Test]
    public function ensurePoolNotAvailable() {
        $this->assert()->isNull($this->testSuite()->get('pool'));
    }
    
}
```

I hope you were able to see as much neatness in the above testing example as I do! If you're interested in seeing more 
examples there are two places to find them; the `examples/` and `acme_src/` directories. Otherwise, please check out the 
rest of this README for how to get started with the project.

## Documentation

If you're a user wanting to learn how to use the library and write better asynchronous tests this is the place for you. 
We walk you through everything you need to do get started, teach you about all the important concepts to know, and 
list out the assertions available. You should definitely check this area out if you're new to the framework!

You can find documentation online at [docs.labrador-kennel.io/asyncunit](https://docs.labrador-kennel.io/asyncunit).

## Discussion

Have an idea for the framework? Wondering how something works and have a question? Wanna interact with the maintainers? 
You're in the right place! This is the "social" part of AsyncUnit... [go add to the Discussion!](https://github.com/labrador-kennel/async-unit/discussions)

## Roadmap

AsyncUnit has a fairly well-defined roadmap leading to a stable API and a 1.0 release. Our Roadmap is not dated because 
the framework is currently maintained and implemented by 1 person in their free time. Instead, we have a series of 0.x 
releases with functionality that should enable increasingly complex tests until our canonical example can be executed.
Check out [the Projects](https://github.com/labrador-kennel/async-unit/projects) to see what's in store for AsyncUnit!