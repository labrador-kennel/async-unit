<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Parser;

use Amp\Promise;

interface Parser {

    /**
     * @param array|string $dirs
     * @return Promise<ParserResult>
     */
    public function parse(array|string $dirs) : Promise;

}