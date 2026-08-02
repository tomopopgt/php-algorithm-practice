<?php

declare(strict_types=1); // 厳密な型チェック

const MOD = 1000000007;

/**
 * 2x2 行列同士の掛け算 (mod MOD)
 *
 * @param array<array<int>> $a
 * @param array<array<int>> $b
 * @return array<array<int>>
 */
function multiplyMatrix(array $a, array $b): array
{
    $c = [[0, 0], [0, 0]];
    for ($i = 0; $i < 2; $i++) {
        for ($k = 0; $k < 2; $k++) {
            for ($j = 0; $j < 2; $j++) {
                $c[$i][$j] = ($c[$i][$j] + $a[$i][$k] * $b[$k][$j]) % MOD;
            }
        }
    }
    return $c;
}

/**
 * 行列の N 冪乗を計算する (繰り返し二乗法: O(log N))
 *
 * @param array<array<int>> $a 2x2の正方行列
 * @param int $n 冪乗数
 * @return array<array<int>>
 */
function powerMatrix(array $a, int $n): array
{
    // 単位行列 E で初期化
    $result = [[1, 0], [0, 1]];
    $base = $a;

    while ($n > 0) {
        // N の最下位ビットが 1 なら結果に行列を掛ける
        if (($n & 1) === 1) {
            $result = multiplyMatrix($result, $base);
        }
        // 行列を 2 乗して進める
        $base = multiplyMatrix($base, $base);
        $n >>= 1;
    }

    return $result;
}

/**
 * N 番目のフィボナッチ数 (F_N) mod 1,000,000,007 を算出する
 */
function getFibonacci(int $n): int
{
    if ($n === 0) {
        return 0;
    }

    // 状態遷移行列 [[1, 1], [1, 0]]
    $transitionMatrix = [[1, 1], [1, 0]];
    $powered = powerMatrix($transitionMatrix, $n);

    // [F_{N+1}, F_N]^T のうち F_N に対応する成分は [1][0]
    return $powered[1][0];
}

// --------------------------------------------------
// 1. 標準入力の読み込み
// --------------------------------------------------
$line = fgets(STDIN);
if ($line === false) {
    exit;
}

$n = (int) trim($line);

// --------------------------------------------------
// 2. 処理実行と出力
// --------------------------------------------------
$result = getFibonacci($n);

echo $result . "\n";