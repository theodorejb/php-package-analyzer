<?php

declare(strict_types=1);

use PhpParser\Error as ParserError;
use PhpParser\Lexer\Emulative;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Parser\Php7;
use theodorejb\PackageAnalyzer\Analyzer;

require __DIR__ . '/vendor/autoload.php';

$lexer = new Emulative([
    'usedAttributes' => [
        'comments',
        'startLine',
        'endLine',
        'startFilePos',
        'endFilePos',
    ]
]);

$parser = new Php7($lexer);

$visitor = new class extends NodeVisitorAbstract {
    public $path = null;
    public $code = null;

    public function enterNode(Node $node) {
        foreach ($node->getComments() as $comment) {
            if (substr($comment->getText(), 0, 2) === '#[') {
                echo $this->path . "\n";
                echo $comment->getText() . "\n";
            }
        }
    }
};

$traverser = new NodeTraverser;
$traverser->addVisitor($visitor);
$analyzer = new Analyzer();
$files = $analyzer->getPhpFiles(__DIR__ . '/extracted');

$index = 0;
foreach ($files as $path) {
    if (++$index % 1000 === 0) {
        echo $index . "\n";
    }

    $code = file_get_contents($path);

    try {
        $stmts = $parser->parse($code);
        $visitor->path = $path;
        $visitor->code = $code;
        $traverser->traverse($stmts);
    } catch (ParserError $exception) {
        error_log($path);
        error_log("Parse error: " . $exception->getMessage());
    }
}
