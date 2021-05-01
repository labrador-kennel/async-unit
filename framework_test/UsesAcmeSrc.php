<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

trait UsesAcmeSrc {

    private function path(string $path) : string {
        return dirname(__DIR__) . '/acme_src/' . $path;
    }

    private function implicitDefaultTestSuitePath(string $path) : string {
        return $this->path('ImplicitDefaultTestSuite/' . $path);
    }

    private function explicitTestsuitePath(string $path) : string {
        return $this->path('ExplicitTestSuite/' . $path);
    }

}