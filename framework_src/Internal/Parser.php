<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Internal;

use Cspray\Labrador\AsyncUnit\Exception\TestCompilationException;
use Cspray\Labrador\AsyncUnit\Internal\Model\TestCaseModel;
use Cspray\Labrador\AsyncUnit\Internal\Model\TestMethodModel;
use Cspray\Labrador\AsyncUnit\Internal\Model\TestSuiteModel;
use Cspray\Labrador\AsyncUnit\Internal\NodeVisitor\TestCaseVisitor;
use FilesystemIterator;
use Generator;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitor\NodeConnectingVisitor;
use PhpParser\Parser as PhpParser;
use PhpParser\ParserFactory;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * @internal
 */
class Parser {

    private PhpParser $phpParser;
    private NodeTraverser $nodeTraverser;

    public function __construct() {
        $this->phpParser = (new ParserFactory())->create(ParserFactory::ONLY_PHP7);
        $this->nodeTraverser = new NodeTraverser();
    }

    /**
     * @param string|array $dirs
     * @return TestSuiteModel[]
     */
    public function parse(string|array $dirs) : array {
        $testSuiteModel = new TestSuiteModel();
        $dirs = is_string($dirs) ? [$dirs] : $dirs;
        foreach ($this->parseDirs($dirs) as $model) {
            $testSuiteModel->addTestCaseModel($model);
        }

        return [$testSuiteModel];
    }

    private function parseDirs(array $dirs) : Generator {
        foreach ($dirs as $dir) {
            $dirIterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $dir,
                    FilesystemIterator::KEY_AS_PATHNAME |
                    FilesystemIterator::CURRENT_AS_FILEINFO |
                    FilesystemIterator::SKIP_DOTS
                )
            );

            /** @var \SplFileInfo $file */
            foreach ($dirIterator as $file) {
                if ($file->isDir() || $file->getExtension() !== 'php') {
                    continue;
                }

                $statements = $this->phpParser->parse(file_get_contents($file->getRealPath()));
                $nodeConnectingVisitor = new NodeConnectingVisitor();
                $nameResolver = new NameResolver();
                $testCaseVisitor = new TestCaseVisitor();

                $this->nodeTraverser->addVisitor($nodeConnectingVisitor);
                $this->nodeTraverser->addVisitor($nameResolver);
                $this->nodeTraverser->addVisitor($testCaseVisitor);
                $this->nodeTraverser->traverse($statements);

                foreach ($testCaseVisitor->getTestCaseModels() as $testCaseModel) {
                    if (empty($testCaseModel->getTestMethodModels())) {
                        $msg = sprintf('Failure compiling "%s". There were no #[Test] found.', $testCaseModel->getTestCaseClass());
                        throw new TestCompilationException($msg);
                    }
                    yield $testCaseModel;
                }

                unset($statements);
                unset($nodeConnectingVisitor);
                unset($testCaseVisitor);
                unset($testCaseVisitor);
            }
        }









    }

}