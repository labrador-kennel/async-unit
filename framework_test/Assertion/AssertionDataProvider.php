<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

trait AssertionDataProvider {

    public function nonIntProvider() : array {
        return $this->getValuesExceptType('integer');
    }

    public function nonStringProvider() : array {
        return $this->getValuesExceptType('string');
    }

    public function nonFloatProvider() : array {
        return $this->getValuesExceptType('double');
    }

    public function nonNullProvider() : array {
        return $this->getValuesExceptType('NULL');
    }

    public function nonBoolProvider() : array {
        return $this->getValuesExceptType('boolean');
    }

    public function nonArrayProvider() : array {
        return $this->getValuesExceptType('array');
    }

    private function getValuesExceptType(string $type) : array {
        $testValues = [
            'integer value' => [1234, 'integer'],
            'double value' => [9876.54, 'double'],
            'boolean value' => [true, 'boolean'],
            'array value' => [[], 'array'],
            'object value' => [new \stdClass(), 'object'],
            'resource value' => [STDOUT, 'resource'],
            'null value' => [null, 'NULL'],
            'string value' => ['async unit', 'string']
        ];
        $filteredValues = [];
        foreach ($testValues as $dataSetLabel => $valueAndType) {
            if ($valueAndType[1] === $type) {
                continue;
            }
            $filteredValues[$dataSetLabel] = $valueAndType;
        }
        return $filteredValues;
    }
}