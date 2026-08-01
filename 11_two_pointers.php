<?php

declare(strict_types=1); // 厳密な型チェック

/**
 * 合計が target 以上となる連続部分列の最小長さを求める（尺取り法：Two Pointers）
 *
 * @param array<int> $numbers 正の整数列
 * @param int $target 目標値 S
 * @return int 条件を満たす最小の長さ（存在しない場合は 0）
 */
function minSubarrayLen(array $numbers, int $target): int
{
    $count = count($numbers);
    $minLength = PHP_INT_MAX;
    $currentSum = 0;
    $left = 0;

    // 右端 (right) を1つずつ進めてウィンドウを広げる
    for ($right = 0; $right < $count; $right++) {
        $currentSum += $numbers[$right];

        // 合計が target 以上である間、左端 (left) を進めてウィンドウを極限まで縮める
        while ($currentSum >= $target) {
            $currentLength = $right - $left + 1;
            if ($currentLength < $minLength) {
                $minLength = $currentLength;
            }

            $currentSum -= $numbers[$left];
            $left++;
        }
    }

    return $minLength === PHP_INT_MAX ? 0 : $minLength;
}

// --------------------------------------------------
// 1. 標準入力の読み込み
// --------------------------------------------------
$firstLine = fgets(STDIN);
if ($firstLine === false) {
    exit;
}

[$nStr, $sStr] = explode(' ', trim($firstLine));
$target = (int) $sStr;

$secondLine = fgets(STDIN);
if ($secondLine === false) {
    exit;
}

$numbers = array_map('intval', explode(' ', trim($secondLine)));

// --------------------------------------------------
// 2. 処理実行と出力
// --------------------------------------------------
$result = minSubarrayLen($numbers, $target);

echo $result . "\n";