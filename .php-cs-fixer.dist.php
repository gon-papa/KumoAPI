<?php

// 修正対象のディレクトリを指定
$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src') 
;

$config = new PhpCsFixer\Config();
return $config->setRules([
        // 最新のPSR-12に準拠したルールセットを適用
        '@PSR12' => true,
        // use文をアルファベット順にソート
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        // 1行の空白行を強制し、複数行の空白行を削除
        'no_extra_blank_lines' => true,
        // 演算子前後のスペースを強制
        'binary_operator_spaces' => true,
    ])
    ->setFinder($finder)
    // キャッシュファイルを作成
    ->setCacheFile(__DIR__ . '/.php-cs-fixer.cache');