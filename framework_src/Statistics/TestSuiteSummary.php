<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Statistics;

interface TestSuiteSummary {

    public function getTestSuiteName() : string;

    public function getTestCaseNames() : array;

    public function getTestCaseCount() : int;

    public function getDisabledTestCaseCount() : int;

    public function getEnabledTestCaseCount() : int;

    public function getTestCount() : int;

    public function getDisabledTestCount() : int;

    public function getEnabledTestCount() : int;

}