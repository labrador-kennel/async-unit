<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Model;

final class TestModel {

    use MethodModelTrait;

    private ?string $dataProvider = null;
    private bool $isDisabled = false;

    public function isDisabled() : bool {
        return $this->isDisabled;
    }

    public function markDisabled() : void {
        $this->isDisabled = true;
    }

    public function getDataProvider() : ?string {
        return $this->dataProvider;
    }

    public function setDataProvider(string $dataProvider) : void {
        $this->dataProvider = $dataProvider;
    }

}