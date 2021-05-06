<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnitCli\Unit;

use Cspray\Labrador\AsyncUnitCli\SymfonyConsoleTerminalOutput;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;

class SymfonyConsoleTerminalOutputTest extends TestCase {

    public function testSendOutputToWrite() {
        $output = new BufferedOutput();
        $subject = new SymfonyConsoleTerminalOutput($output);
        $subject->write('This is AsyncUnit!');

        $expected = 'This is AsyncUnit!';
        $this->assertSame($expected, $output->fetch());
    }

    public function testSendOutputToWriteLn() {
        $output = new BufferedOutput();
        $subject = new SymfonyConsoleTerminalOutput($output);
        $subject->writeln('This is AsyncUnit!');

        $expected = 'This is AsyncUnit!' . PHP_EOL;
        $this->assertSame($expected, $output->fetch());
    }

}