<?php

declare(strict_types=1); // 厳密な型チェック

/**
 * 指定された積載量 (capacity) で K 台のトラックに荷物を積み切れるか判定する
 *
 * @param array<int> $weights 荷物の重さリスト
 * @param int $capacity トラックの最大積載量
 * @param int $maxTrucks トラックの台数 K
 * @return bool 全て運べれば true
 */
function canTransport(array $weights, int $capacity, int $maxTrucks): bool
{
    $truckCount = 1;
    $currentWeight = 0;

    foreach ($weights as $weight) {
        if ($currentWeight + $weight > $capacity) {
            // 現在のトラックに乗らない場合は次のトラックへ
            $truckCount++;
            $currentWeight = $weight;

            if ($truckCount > $maxTrucks) {
                return false;
            }
        } else {
            $currentWeight += $weight;
        }
    }

    return true;
}

/**
 * 全ての荷物を K 台のトラックで運ぶ際の、1台あたりの最大積載量の最小値を求める（二分探索）
 *
 * @param array<int> $weights 荷物の重さリスト
 * @param int $maxTrucks トラックの台数 K
 * @return int 最小の最大積載量
 */
function findMinMaxCapacity(array $weights, int $maxTrucks): int
{
    if (empty($weights)) {
        return 0;
    }

    // 積載量の探索範囲を設定
    // low: 単体で最も重い荷物（これ未満だとその荷物1個すら運べない）
    // high: 全ての荷物の合計値（1台で全部運ぶ場合の限界）
    $low = max($weights);
    $high = (int) array_sum($weights);

    $answer = $high;

    while ($low <= $high) {
        $mid = intdiv($low + $high, 2);

        if (canTransport($weights, $mid, $maxTrucks)) {
            // mid で運び切れるなら、さらに小さい積載量が可能か左側（小さい方）を探す
            $answer = $mid;
            $high = $mid - 1;
        } else {
            // mid で運び切れないなら、積載量を増やすため右側（大きい方）を探す
            $low = $mid + 1;
        }
    }

    return $answer;
}

// --------------------------------------------------
// 1. 標準入力の読み込み
// --------------------------------------------------
$firstLine = fgets(STDIN);
if ($firstLine === false) {
    exit;
}

[$nStr, $kStr] = explode(' ', trim($firstLine));
$maxTrucks = (int) $kStr;

$secondLine = fgets(STDIN);
if ($secondLine === false) {
    exit;
}

$weights = array_map('intval', explode(' ', trim($secondLine)));

// --------------------------------------------------
// 2. 処理実行と出力
// --------------------------------------------------
$minCapacity = findMinMaxCapacity($weights, $maxTrucks);

echo $minCapacity . "\n";