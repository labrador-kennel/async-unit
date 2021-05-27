# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## 0.5.0 - 2021-05-??

### Added

- Introduced the `amphp/file` library to handle async I/O on the filesystem.
- Introduced a `StaticAnalysisParser` that uses PHP-Parser and makes significant improvements over the 
previous parser implementation.
- Added the `labrador-kennel/styled-byte-stream` project to support formatted terminal output.
- Adds the ability to expect that no assertions are expected to take place. If a test expects no assertions 
and an assertion is made it results in a failure. This method accounts for both `Assertion` and `AsyncAssertion` types.
- Adds a `Context\TestExpector` that represents the expectations, for example whether an exception is thrown 
or the amount of assertions to expect, that a test can make.
- Adds a `#[Timeout]` Attribute that can be annotated on tests, `TestCase`, or `TestSuite` to cause a TestFailure 
if the test takes longer than the provided number of milliseconds.
- Introduces a comprehensive statistics API that's accessible through the event system to gather information about the 
system both pre and post test processing.

### Changed

- **Breaking Change!** Refactored the `Parser` implementation into an interface to support different types of parsers in the future.
- **Breaking Change!** Updates the `Parser::parse` return type to be a `Promise` and use async I/O.
- **Breaking Change!** The `TestFrameworkApplication` now expects to get a `Parser` implementation and the directories to
parse as a constructor dependency. The `TestFrameworkApplication` is now responsible for initiating parsing.
- **Breaking Change!** Removes the `TestCase::expectException*` methods. A `TestCase::expect()` now returns a `TestExpector`

## 0.4.2 - 2021-05-09

### Changed

- Updates the exception thrown during compilation if a class annotated with an AsyncUnit Attribute cannot be loaded to 
better inform the user what has happened.

### Fixed

- Fixes a bug where the inappropriate directory was used when executing the AsyncUnit executable from `vendor/bin`

## 0.4.1 - 2021-05-09

### Changed

- Updates Labrador Core to 3.2.0

## 0.4.0 - 2021-05-09

### Added

- Added a `#[Disabled]` Attribute that allows for annotating a test, TestCase, or TestSuite to not run. [#63](https://github.com/labrador-kennel/async-unit/pull/63)
- Adds the total time and memory usage to default test output. [#64](https://github.com/labrador-kennel/async-unit/pull/64)
- Ensures that any test that has output is marked as a failure. [#67](https://github.com/labrador-kennel/async-unit/pull/67)
- Randomize all tests so none of them process in any specific order. Depending on the order of the tests is a code smell 
  in your application or testing suite. [#68](https://github.com/labrador-kennel/async-unit/pull/68)
- Adds `assert()->instanceOf`, `assert()->isEmpty`, and `assert()->countEquals` along with their asynchronous counterparts. [#69](https://github.com/labrador-kennel/async-unit/pull/69)
- Adds the ability to expect that an exception is thrown from your test. [#70](https://github.com/labrador-kennel/async-unit/pull/70)
- Adds a `TestResult::getState` method that returns an enum whether test passed, failed, or was disabled.

### Changed

- **Breaking Change!** Renamed `TestInvokedEvent` -> `TestProcessedEvent` to better signify that the test might not have actually 
been invoked if it was disabled.
- **Breaking Change!** Removes the `TestOutput` interface in favor of using the `Amp\ByteStream\OutputStream` interface. 
- **Breaking Change!** Renamed `DefaultTestSuite` -> `ImplicitTestSuite` to not conflict with the attribute of the same name.
- **Breaking Change!** Renamed `#[TestSuite]` -> `#[AttachToTestSuite]` to not conflict with the interface of the same name.
- **Breaking Change!** Refactored the `HookModel` type to be an enum instead of a string.

### Removed

- **Breaking Change!** Removed the `TestResult::isSuccessful` method. With the addition of a disabled boolean the state 
of the test became too complex to manage with booleans.

## 0.3.0 - 2021-05-05

### Added

- Adds initial implementation of an explicit TestSuite. [#50](https://github.com/labrador-kennel/async-unit/pull/50)
- Invoke all existing hooks for an explicit TestSuite. Adds `#[BeforeEachTest]` and `#[AfterEachTest]` hooks for the 
  TestSuite to have access to invoking hooks around each test. [#54](https://github.com/labrador-kennel/async-unit/pull/54)
- Allow for a TestSuite and TestCases associated to it to read and write arbitrary state. [#57](https://github.com/labrador-kennel/async-unit/pull/57)
- Adds a `TestOutput` and `ResultPrinterPlugin` implementations to facilitate creating result output that's not tied to Symfony 
Console. [#58](https://github.com/labrador-kennel/async-unit/pull/58)
- Improved, reusable implementations of a new interface `AssertionMessage` that represents the results of an Assertion. [#58](https://github.com/labrador-kennel/async-unit/pull/58)
- Emit more complete events representing the complete lifecycle of the testing process. [#59](https://github.com/labrador-kennel/async-unit/pull/59)

  
### Changed

- **Breaking Change!** Renamed the CLI namespace to `Cspray\Labrador\AsyncUnitCli` to more clearly separate it from the 
framework itself.
- **Breaking Change!** Renames Event classes to no longer suffix `Event` at the end of the class name.
- **Breaking Change!** Refactor `AssertionResult` to return implementations of  new interface `AssertionMessage` that 
  represents the summary and details of the Assertion.


## 0.2.0 - 2021-04-30

### Added

- Support running tests that are extended from parent test cases. [#31](https://github.com/labrador-kennel/async-unit/pull/31)
- Support running `assert()->not()` and `asyncAssert()->not()` to run the opposite assertion. Works for built-in and custom assertions. [#32](https://github.com/labrador-kennel/async-unit/pull/32)
- Support making your own custom Assertion and running it with `assert()->customAssertion()` and `asyncAssert()->customAssertion()`. [#33](https://github.com/labrador-kennel/async-unit/pull/33)
- Support for running the same `#[Test]` multiple times by using a `#[DataProvider]`. [#34](https://github.com/labrador-kennel/async-unit/pull/34)

### Changed

- **Breaking Change!** Changed the `Assertion::assert` and `AsyncAssertion::assert` methods to no longer take any parameters. This change is 
required to better support custom Assertions. When interacting with a custom Assertion we don't know what's expected,
actual, or a custom error message. Assertions are now expected to pass everything they need to perform their task into 
the constructor, including the actual value being asserted.
  
### Fixes

- Fixes a problem where hooks were erroneously being invoked though the hook does not belong to the TestCase.

## 0.1.0 - 2021-04-18

### Added

- Parser to analyze PHP code for configured tests and hooks.
- TestSuiteRunner to execute the parsed tests and hooks
- A TestCase and Assertions API for writing tests
- A CLI tool to execute tests in 1 or more directories
- A Result printer that details what Tests failed and why
