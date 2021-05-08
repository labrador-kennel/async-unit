<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

/**
 * Represents some information about the results of an Assertion or AsyncAssertion.
 *
 * @package Cspray\Labrador\AsyncUnit
 */
interface AssertionMessage {

    /**
     * Provide information for when the Assertion or AsyncAssertion has been processed without the not() modifier.
     *
     * @return string
     */
    public function toString() : string;

    /**
     * Provide information for when the Assertion or AsyncAssertion has been processed with the not() modifier.
     *
     * @return string
     */
    public function toNotString() : string;

}