<?php

declare(strict_types=1); // 厳密な型チェック

/**
 * Nim (山崩しゲーム) の勝敗を Grundy数 (XOR Sum) を用いて判定する (計算量: O(N))
 *
 * @param array<int> $piles 各山のコイン数
 * @return string "First" (先手勝利) または "Second" (後手勝利)
 */
function solveNim(array $piles): string
{
    $xorSum = 0;

    foreach ($piles as $pile) {
        $xorSum ^= $pile;
    }

    // XOR和が 0 以外のときは先手必勝 (N-position)、0 のときは後手必勝 (P-position)
    return $xorSum !== 0 ? 'First' : 'Second';
}

// --------------------------------------------------
// 1. 標準入力の読み込み
// --------------------------------------------------
$firstLine = fgets(STDIN);
if ($firstLine === false) {
    exit;
}

$n = (int) trim($firstLine);

$secondLine = fgets(STDIN);
if ($secondLine === false) {
    exit;
}

$piles = array_map('intval', explode(' ', trim($secondLine)));

// --------------------------------------------------
// 2. 処理実行と出力
// --------------------------------------------------
$result = solveNim($piles);

echo $result . "\n";