<?php

declare(strict_types=1); // 厳密な型チェック

/**
 * バックスペース文字 (#) を考慮して最終的な文字列を構築する（スタック構造の活用）
 *
 * @param string $input 入力文字列
 * @return string 処理後の文字列
 */
function processBackspaceString(string $input): string
{
    /** @var array<int, string> $stack */
    $stack = [];
    $length = strlen($input);

    for ($i = 0; $i < $length; $i++) {
        $char = $input[$i];

        if ($char === '#') {
            // スタックが空でない場合のみ、末尾の1文字を消去 (Pop)
            if (!empty($stack)) {
                array_pop($stack);
            }
        } else {
            // 通常の文字ならスタックの末尾に追加 (Push)
            array_push($stack, $char);
        }
    }

    // スタック内の文字を結合して1つの文字列に戻す
    return implode('', $stack);
}

// --------------------------------------------------
// 1. 標準入力の読み込み
// --------------------------------------------------
$line = fgets(STDIN);
if ($line === false) {
    exit;
}

$inputString = trim($line);

// --------------------------------------------------
// 2. 処理実行と出力
// --------------------------------------------------
$result = processBackspaceString($inputString);

echo $result . "\n";