<?php

declare(strict_types=1);

use theodorejb\PackageAnalyzer\Analyzer;

require 'vendor/autoload.php';

$lexer = new PhpParser\Lexer\Emulative([
    'usedAttributes' => [
        'comments', 'startLine', 'endLine',
        'startFilePos', 'endFilePos',
    ]
]);

$parser = new PhpParser\Parser\Php7($lexer);

$visitor = new class extends PhpParser\NodeVisitorAbstract {
    public $path = null;
    public $code = null;

    public function enterNode(PhpParser\Node $node) {
        foreach ($node->getComments() as $comment) {
            if (substr($comment->getText(), 0, 2) === '#[') {
                echo $this->path . "\n";
                echo $comment->getText() . "\n";
            }
        }
    }
};

$traverser = new PhpParser\NodeTraverser;
$traverser->addVisitor($visitor);
$analyzer = new Analyzer();
$files = $analyzer->getPhpFiles(__DIR__ . '/extracted');

$i = 0;
foreach ($files as $path) {
    if (++$i % 1000 === 0) {
        echo $i . "\n";
    }

    $code = file_get_contents($path);

    try {
        $stmts = $parser->parse($code);
        $visitor->path = $path;
        $visitor->code = $code;
        $traverser->traverse($stmts);
    } catch (PhpParser\Error $e) {
        echo $path . "\n";
        echo "Parse error: " . $e->getMessage() . "\n";
    }
}
