# Async Testing

A comprehensive unit/integration testing framework that combines PHP8 and Amphp to support writing 
tests with first-class async support.

> This library is still in early development and is largely public at this point to garner feedback 
> and solicit potential ideas or pitfalls. Please open a PR or start a Discussion if you'd like to 
> contribute!

## Installation

```
composer require --dev cspray/labrador-async-testing
```

## What we are (or strive to be)...

- A SOLID, well-tested, comprehensive testing framework with first-class async support.
- A set of explicit, opinionated interfaces and implementations to run tests configured primarily through the use of [Attributes]()
- A static analysis tool to ensure that your tests are coherent, logical, and adhere to the opinions of this framework
- A comprehensive Assertions API with first-class async support.
- The primary test suite used by all Labrador projects.

## What we are not...

- A replacement for PHPUnit. We don't aim to have complete feature parity with PHPUnit. There are aspects of what PHPUnit 
needs to provide that are largely irrelevant to us. You should be using this framework specifically if you have experience 
pain points with the [amphp/phpunit-util]() wrapper. For the majority of use cases this library is probably sufficient.
- A mocking framework. BYOM... Bring Your Own Mocks.

## User Guide

At the current moment there is only limited capacity to run, through not well-defined boilerplate, a test suite that 
looks like the following. The attributes should be explicit in how this test would work.

```php
<?php

use Amp\Promise;use Cspray\Labrador\AsyncTesting\Attribute\AfterAll;
use Cspray\Labrador\AsyncTesting\Attribute\BeforeAll;
use Cspray\Labrador\AsyncTesting\Attribute\BeforeEach;use Cspray\Labrador\AsyncTesting\Attribute\Test;
use Cspray\Labrador\AsyncTesting\TestCase;use function Amp\call;

function whatMakesMeHappyProgramming() : string {
    return "Unit Testing \o/";
}

function whatElseMakesMeHappyProgramming() : Promise {
    return call(function() {
        return "Async & Unit Testing! \o/";
    });
}

class ExampleTestCase extends TestCase {

    #[BeforeAll]
    public static function beforeTestCaseRan() {
    
    }
    
    #[BeforeEach]
    public function beforeEachTest() {
    
    }
    
    #[BeforeEach]
    public function yesYouCanDoThisButBeCarefulDoingThisTooMuch() {    
    }
    
    // There is a corresponding AfterEach that comes with the same warnings
    
    #[AfterAll]
    public static function afterTestCaseRan() {
    
    }

    #[Test]
    public function ensureSomething() {
        $this->assert()->stringEqual("Unit Testing \o/", whatMakesMeHappyProgramming());
    }
    
    #[Test]
    public function ensureSomethingAsync() {
        yield $this->asyncAssert()->stringEqual("Async & Unit Testing! \o/", whatElseMakesMeHappyProgramming());
    }

}
```

The rest of this User Guide details how to use this library to achieve async test nirvana! This guide does not teach 
the basics of unit testing or TDD in general. For that I really recommend you checkout PHPUnit's documentation. This 
guide is specifically targeted to developers with experience unit testing and are having pain points with testing 
asynchronous code in the existing unit testing ecosystem.

### The TestCase

This is the object you'll be extending and interacting with the most and conceptually should feel really familiar to 
you if you've used PHPUnit. The TestCase from the end user's perspective is...

- A collection of _tests_ that are methods annotated with a `#[Test]` Attribute.
- An optional collection of _hooks_ that are methods annotated with a series of Attribute detailing when the method should be invoked
- Access to the Assertions API which we'll document more below
- Additional functionality is also expected to be added to the TestCase as the framework matures

Extending the TestCase binds you to a contract with how you're going to interact with the object. Your tests MUST 
adhere to the following rules.

- You MUST extend TestCase or tests will not run. In fact, an annotated `#[Test]` that does not extend TestCase is a compilation error.
- Your TestCase MUST NOT have a constructor of any kind. We expect specific arguments to be passed to the TestCase. If 
  you need to do some form of setup procedures you should utilize one of the available hooks.
- Your TestCase MUST have at least 1 method annotated with `#[Test]`.  If your TestCase has not `#[Test]` it is a compilation error.

As Protocols are implemented more thoroughly additional checks or rules will be added to the use of this TestCase. Don't 
worry, we intend for this to be fairly transparent and to primarily serve as a way of warning you when you've 
misconfigured your attributes. Let's take a look at Protocols next, while they are mostly theory at this point they will 
be an important part of the framework as the static analyzer matures.

### Protocols

Generally, my designs tend to include a lot of granular interfaces that define boundaries of concerns and default 
implementations of those interfaces. For obvious reasons I can't provide an interface for your tests! But, we do execute 
methods on your TestCase that you implement and sometimes assumptions are made about those method signatures. To properly 
communicate what those assumptions are each scenario that has you placing an Attribute on a TestCase is also defined, 
or will be defined, by a Protocol. So, what is a Protocol?

A Protocol is the method signature that defines any method on a TestCase defined with 1 or more Attributes. For example, 
here's the `TestProtocol` that defines the method signature for methods annotated with `#[Test]`. Remember, that in this 
context the _name_ of the method does not matter and is simply a placeholder. The pieces that are important are your 
expected arguments and return types.

```php

namespace Cspray\Labrador\AsyncTesting\Protocol;

use Amp\Promise;
use Cspray\Labrador\AsyncTesting\Attribute\ProtocolRequiresAttribute;
use Cspray\Labrador\AsyncTesting\Attribute\Protocol;
use Cspray\Labrador\AsyncTesting\Attribute\Test;

#[Protocol]
#[ProtocolRequiresAttribute(Test::class)]
interface TestProtocol {

    public function test() : Promise|Generator|null 

}
```

You would not generally implement this interface but use it as a guide for how to implement your `#[Test]` methods. To 
maximize what you can get out of this framework you should review the defined Protocols and ensure your implementations 
adhere to them. Violations of these Protocols will result in compilation errors as the framework matures.

## Assertions

Assertions play a big part in how a unit testing framework feels and we face a problem where we need to support the 
general use case of asserting values that do not require resolution as well as asserting values that are resolved on the 
Loop. The provided Assertions API is intended to make dealing with this problem explicit, type-safe, and straight-forward 
for consumers. For now, it is the only supported way for making assertions within your TestCase. As the framework matures 
we expect to provide more support for other Assertion libraries. In the time being we need to ensure we provide a coherent 
Assertions API that treats asynchronous code as a first class citizen.

### The Basics

In your `#[Test]` you have access to 2 methods that provide access to an `AssertionContext` and an `AsyncAssertionContext`. 
These methods allow us to keep track of how many assertions your test has made and provides an easy to use API, whether 
you're dealing with values that need to be resolved on the Loop or not. First, let's take a look at using the `AssertionContext`.

```php
$this->assert()->stringEquals("foo", "foo");
```

Pretty simple, huh? There's a third parameter on `stringEquals()` that allows you to customize the error message displayed. 
If the strings are not equal to one another a `TestFailedException` will be thrown and no more assertions will be made. 
This is an asynchronous library though, what do you do if you need to test something that needs to resolve on the Loop? 
Use `asyncAssert()`!

```php

use Amp\Delayed;

function getAsyncFoo() : Generator {
    yield new Delayed(100);
    return "foo";
}

yield $this->asyncAssert()->stringEquals("foo", getAsyncFoo());
```

This example looks pretty similar to the previous one with a couple key differences.

- We `yield` the result of the call to `stringEquals()`
- We pass the `Promise` directly as the `$actual` value and do not resolve it

This test will resolve the Generator on the Loop and then compare the 2 values with one another. When calling the 
`asyncAssert()` API it is expected that the `$actual` parameter will always adhere to the following type union
`Promise|Generator|Coroutine`.

### Assertions list

> This list only includes Assertions that are implemented and tested. More Assertions are on the way!

|Description|`assert()`|`asyncAssert()`|
|---|---|
|Confirm that 2 strings are equal to one another|`stringEquals(string $expected, string $actual, string $message = null)`|`stringEquals(string $expected, Promise|Generator|Coroutine $actual)`|

### Creating your own Assertions

> More details to come as the Assertions API matures!

## Roadmap

To see the planned future for Async Testing please checkout the Projects and Issues created in this repo.

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

