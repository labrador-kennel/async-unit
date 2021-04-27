<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

interface PreRunSummary {

    public function getTestSuiteCount() : int;

    public function getTotalTestCaseCount() : int;

    public function getTotalTestCount() : int;

}