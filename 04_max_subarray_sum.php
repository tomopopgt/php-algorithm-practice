<?php

declare(strict_types=1); // 厳密な型チェック

/**
 * 連続する K 日間の売上合計の最大値を求める（スライディングウィンドウ法）
 *
 * @param array<int> $sales 日ごとの売上リスト
 * @param int $k 連続する日数
 * @return int 最大合計値
 */
function findMaxConsecutiveSum(array $sales, int $k): int
{
    $count = count($sales);
    if ($count < $k || $k <= 0) {
        return 0;
    }

    // 最初の K 日間の合計を初期化
    $currentSum = 0;
    for ($i = 0; $i < $k; $i++) {
        $currentSum += $sales[$i];
    }

    $maxSum = $currentSum;

    // ウィンドウを1日ずつ右にスライドさせる (計算量: O(N))
    for ($i = $k; $i < $count; $i++) {
        // [前日の和] - [押し出された要素] + [新しく入ってきた要素]
        $currentSum = $currentSum - $sales[$i - $k] + $sales[$i];

        if ($currentSum > $maxSum) {
            $maxSum = $currentSum;
        }
    }

    return $maxSum;
}

// --------------------------------------------------
// 1. 標準入力の読み込みとデータ変換
// --------------------------------------------------
$firstLine = fgets(STDIN);
if ($firstLine === false) {
    exit;
}

[$nStr, $kStr] = explode(' ', trim($firstLine));
$k = (int) $kStr;

$secondLine = fgets(STDIN);
if ($secondLine === false) {
    exit;
}

// array_map を使って配列要素を一括で整数化（型安全）
$sales = array_map('intval', explode(' ', trim($secondLine)));

// --------------------------------------------------
// 2. 処理実行と出力
// --------------------------------------------------
$maxSales = findMaxConsecutiveSum($sales, $k);

echo $maxSales . "\n";