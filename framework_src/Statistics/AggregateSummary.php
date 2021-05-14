<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Statistics;

/**
 */
interface AggregateSummary {

    public function getTestSuiteNames() : array;

    public function getTotalTestSuiteCount() : int;

    public function getEnabledTestSuiteCount() : int;

    public function getDisabledTestSuiteCount() : int;

    public function getTotalTestCaseCount() : int;

    public function getEnabledTestCaseCount() : int;

    public function getDisabledTestCaseCount() : int;

    public function getTotalTestCount() : int;

    public function getEnabledTestCount() : int;

    public function getDisabledTestCount() : int;

}