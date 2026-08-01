<?php

declare(strict_types=1); // 厳密な型チェック

/**
 * 元の配列から累積和（1-indexed）テーブルを作成する
 *
 * @param array<int> $values 数値の配列
 * @return array<int> 累積和テーブル ($prefix[i] は先頭 i 個の合計)
 */
function buildPrefixSum(array $values): array
{
    $prefix = [0];
    $currentSum = 0;

    foreach ($values as $val) {
        $currentSum += $val;
        $prefix[] = $currentSum;
    }

    return $prefix;
}

/**
 * 累積和テーブルを使って L 日目から R 日目までの合計を O(1) で算出する
 *
 * @param array<int> $prefix 累積和テーブル
 * @param int $left 開始位置 (1-indexed)
 * @param int $right 終了位置 (1-indexed)
 * @return int 区間の合計値
 */
function queryRangeSum(array $prefix, int $left, int $right): int
{
    // [1 〜 R] の合計から [1 〜 L-1] の合計を引くことで [L 〜 R] の合計が得られる
    return $prefix[$right] - $prefix[$left - 1];
}

// --------------------------------------------------
// 1. 標準入力の読み込み
// --------------------------------------------------
$firstLine = fgets(STDIN);
if ($firstLine === false) {
    exit;
}

[$nStr, $qStr] = explode(' ', trim($firstLine));
$q = (int) $qStr;

$secondLine = fgets(STDIN);
if ($secondLine === false) {
    exit;
}

$sales = array_map('intval', explode(' ', trim($secondLine)));

// 累積和テーブルを構築 (計算量: O(N))
$prefixTable = buildPrefixSum($sales);

// --------------------------------------------------
// 2. クエリの処理と出力 (計算量: O(1) × Q)
// --------------------------------------------------
for ($i = 0; $i < $q; $i++) {
    $qLine = fgets(STDIN);
    if ($qLine === false) {
        break;
    }

    [$left, $right] = array_map('intval', explode(' ', trim($qLine)));
    
    $result = queryRangeSum($prefixTable, $left, $right);
    echo $result . "\n";
}