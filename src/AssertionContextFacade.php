<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncTesting;

/**
 * An object created for each #[Test] that provides a single access to the Assertion and AsyncAssertion contexts.
 *
 *
 */
final class AssertionContextFacade {

    public function __construct(
        private AssertionContext $assertionContext,
        private AsyncAssertionContext $asyncAssertionContext
    ) {}

    public function assert() : AssertionContext {

    }

    public function asyncAssert() : AsyncAssertionContext {

    }

    /**
     * Returns the total count of Assertion and AsyncAssertion that were executed.
     *
     * @return int
     */
    public function getTotalAssertionCount() : int {

    }

    /**
     * Returns the total count of ONLY Assertion that were executed.
     *
     * @return int
     */
    public function getAssertionCount() : int {

    }

    /**
     * Returns the total count of ONLY AsyncAssertion that were executed.
     *
     * @return int
     */
    public function getAsyncAssertionCount() : int {

    }

}