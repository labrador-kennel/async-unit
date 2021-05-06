# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## Unreleased

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