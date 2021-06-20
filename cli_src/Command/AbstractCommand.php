<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnitCli\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

abstract class AbstractCommand extends Command {

    public function __construct() {
        parent::__construct($this->getCommandName());
    }

    protected function confirm(InputInterface $input, OutputInterface $output, string $question, bool $default = false) : bool {
        stream_set_blocking(STDIN, true);
        $answerGuide = $default ? '(Y/n)' : '(y/N)';
        $confirmationQuestion = new ConfirmationQuestion(sprintf("%s %s ", $question, $answerGuide), $default);
        $answer = $this->getHelper('question')->ask($input, $output, $confirmationQuestion);
        stream_set_blocking(STDIN, false);
        return $answer;
    }

    abstract protected function getCommandName() : string;

}