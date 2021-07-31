<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Statistics;

/**
 */
interface AggregateSummary {

    public function getTestSuiteNames() : array;

    public function getTotalTestSuiteCount() : int;

    public function getTotalTestCaseCount() : int;

    public function getTotalTestCount() : int;

}