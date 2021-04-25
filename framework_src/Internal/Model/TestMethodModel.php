<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Internal\Model;

/**
 * @internal
 */
class TestMethodModel {

    use MethodModelTrait;

    private ?string $dataProvider = null;

    public function getDataProvider() : ?string {
        return $this->dataProvider;
    }

    public function setDataProvider(string $dataProvider) : void {
        $this->dataProvider = $dataProvider;
    }

}