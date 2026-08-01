<?php

declare(strict_types=1); // 厳密な型チェック

/**
 * 指定された金額を達成するために必要な最小のコイン枚数を動的計画法（DP）で求める
 *
 * @param int $target 目標金額 V
 * @param array<int> $coins コインの種類リスト
 * @return int 最小枚数（達成不可なら -1）
 */
function minCoinsToPay(int $target, array $coins): int
{
    // dp[i] は「金額 i を作るのに必要な最小枚数」を表す
    // 初期値として十分大きな数をセット
    $inf = PHP_INT_MAX - 1;
    $dp = array_fill(0, $target + 1, $inf);

    // 金額 0 を作るのに必要な枚数は 0
    $dp[0] = 0;

    // DPテーブルの更新 (1 から target まで順に決定)
    for ($i = 1; $i <= $target; $i++) {
        foreach ($coins as $coin) {
            if ($i - $coin >= 0 && $dp[$i - $coin] !== $inf) {
                $dp[$i] = min($dp[$i], $dp[$i - $coin] + 1);
            }
        }
    }

    return $dp[$target] === $inf ? -1 : $dp[$target];
}

// --------------------------------------------------
// 1. 標準入力の読み込み
// --------------------------------------------------
$firstLine = fgets(STDIN);
if ($firstLine === false) {
    exit;
}

[$targetStr, $nStr] = explode(' ', trim($firstLine));
$target = (int) $targetStr;

$secondLine = fgets(STDIN);
if ($secondLine === false) {
    exit;
}

$coins = array_map('intval', explode(' ', trim($secondLine)));

// --------------------------------------------------
// 2. 処理実行と出力
// --------------------------------------------------
$result = minCoinsToPay($target, $coins);

echo $result . "\n";