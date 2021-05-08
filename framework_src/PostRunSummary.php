<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

interface PostRunSummary {

    public function getTotalTestCount() : int;

    public function getAssertionCount() : int;

    public function getAsyncAssertionCount() : int;

    public function getPassedTestCount() : int;

    public function getFailedTestCount() : int;

    public function getDisabledTestCount() : int;

}