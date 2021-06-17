<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

/**
 * Represents an implementation that knows how to create a mock from a 3rd-party library and facilitate coordinating with
 * the AsyncUnit framework runner to port any 3rd-party specific exceptions or APIs into appropriate AsyncUnit counterparts.
 */
interface MockBridge {

    /**
     * Perform whatever setup functionality that might be required for the 3rd party implementation.
     *
     * This method will be called one time for every test.
     */
    public function initialize() : void;

    /**
     * Create a mock object that is a type of $class.
     *
     * @param string $class
     * @return object
     */
    public function createMock(string $class) : object;

    /**
     * Perform whatever validation or verification of expected mock calls are required for the given 3rd-party library.
     */
    public function finalize() : void;

}