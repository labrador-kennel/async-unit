<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Model;

final class TestModel {

    use MethodModelTrait;
    use CanBeDisabledTrait;

    private ?string $dataProvider = null;

    public function getDataProvider() : ?string {
        return $this->dataProvider;
    }

    public function setDataProvider(string $dataProvider) : void {
        $this->dataProvider = $dataProvider;
    }

}