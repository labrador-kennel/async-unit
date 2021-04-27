<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

interface PostRunSummary {

    public function getExecutedTestCount() : int;

    public function getAssertionCount() : int;

    public function getAsyncAssertionCount() : int;

    public function getFailureTestCount() : int;

}