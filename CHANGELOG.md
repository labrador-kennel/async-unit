# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## 0.1.0 - 2021-04-18

### Added

- Parser to analyze PHP code for configured tests and hooks.
- TestSuiteRunner to execute the parsed tests and hooks
- A TestCase and Assertions API for writing tests
- A CLI tool to execute tests in 1 or more directories
- A Result printer that details what Tests failed and why