<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Parser;

use Amp\ByteStream\Payload;
use Amp\File\Driver;
use Amp\File\File;
use Amp\Promise;
use Cspray\Labrador\AsyncUnit\ImplicitTestSuite;
use Cspray\Labrador\AsyncUnit\Model\TestSuiteModel;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitor\NodeConnectingVisitor;
use PhpParser\Parser as PhpParser;
use PhpParser\ParserFactory;
use function Amp\call;
use function Amp\File\filesystem;

/**
 * Responsible for iterating over a directory of PHP source code, analyzing it for code annotated with AysncUnit
 * Attributes, and converting them into the appropriate AsyncUnit Model.
 *
 * @package Cspray\Labrador\AsyncUnit
 * @see AsyncUnitModelNodeVisitor
 */
final class StaticAnalysisParser implements Parser {

    use AttributeGroupTraverser;

    private PhpParser $phpParser;
    private NodeTraverser $nodeTraverser;
    private Driver $filesystem;

    public function __construct() {
        $this->phpParser = (new ParserFactory())->create(ParserFactory::ONLY_PHP7);
        $this->nodeTraverser = new NodeTraverser();
        $this->filesystem = filesystem();
    }

    /**
     * @param string|array $dirs
     * @return Promise<ParserResult>
     */
    public function parse(string|array $dirs) : Promise {
        return call(function() use($dirs) {
            $dirs = is_string($dirs) ? [$dirs] : $dirs;

            $collector = new AsyncUnitModelCollector();
            $nodeConnectingVisitor = new NodeConnectingVisitor();
            $nameResolver = new NameResolver();
            $asyncUnitVisitor = new AsyncUnitModelNodeVisitor($collector);

            $this->nodeTraverser->addVisitor($nameResolver);
            $this->nodeTraverser->addVisitor($nodeConnectingVisitor);
            $this->nodeTraverser->addVisitor($asyncUnitVisitor);

            foreach ($dirs as $dir) {
                yield $this->traverseDir($dir);
            }

            if (!$collector->hasDefaultTestSuite()) {
                $collector->attachTestSuite(new TestSuiteModel(ImplicitTestSuite::class, true));
            }
            $collector->finishedCollection();

            return new ParserResult($collector);
        });
    }

    private function traverseDir(string $dir) : Promise {
        return call(function() use($dir) {
            $files = yield $this->filesystem->scandir($dir);

            foreach ($files as $fileOrDir) {
                $fullPath = $dir . '/' . $fileOrDir;
                if (yield $this->filesystem->isdir($fullPath)) {
                    yield $this->traverseDir($fullPath);
                } else {
                    /** @var File $handle */
                    $handle = yield $this->filesystem->open($fullPath, 'r');
                    $contents = yield (new Payload($handle))->buffer();
                    $statements = $this->phpParser->parse($contents);
                    $this->nodeTraverser->traverse($statements);
                    yield $handle->close();
                    unset($handle);
                    unset($contents);
                }
            }
        });
    }

}