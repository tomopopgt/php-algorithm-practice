<?php

declare(strict_types=1); // 厳密な型チェック

/**
 * 重み付き確率抽選を O(1) で処理する Alias Method クラス
 */
class AliasMethod
{
    /** @var array<float> 自身のアイテムが当選する確率の閾値 */
    private array $prob = [];

    /** @var array<int> 閾値を超えた場合に置き換わるエイリアスアイテムのインデックス */
    private array $alias = [];

    private int $n;

    /**
     * @param array<int|float> $weights 各アイテムの重み配列
     */
    public function __construct(array $weights)
    {
        $this->n = count($weights);
        $sum = array_sum($weights);

        $this->prob = array_fill(0, $this->n, 0.0);
        $this->alias = array_fill(0, $this->n, 0);

        $scaledProb = [];
        $small = [];
        $large = [];

        // 平均確率を 1.0 としたときの相対確率にスケール変換
        for ($i = 0; $i < $this->n; $i++) {
            $p = ($weights[$i] / $sum) * $this->n;
            $scaledProb[$i] = $p;
            if ($p < 1.0) {
                $small[] = $i;
            } else {
                $large[] = $i;
            }
        }

        // Small と Large をペアリングして Alias Table を構築 (計算量: O(N))
        while (!empty($small) && !empty($large)) {
            $s = array_pop($small);
            $l = array_pop($large);

            $this->prob[$s] = $scaledProb[$s];
            $this->alias[$s] = $l;

            // Large から不足分 (1.0 - scaledProb[s]) を削って分け与える
            $scaledProb[$l] = ($scaledProb[$l] + $scaledProb[$s]) - 1.0;

            if ($scaledProb[$l] < 1.0 - 1e-9) {
                $small[] = $l;
            } else {
                $large[] = $l;
            }
        }

        // 余った要素の確率を 1.0 に設定
        while (!empty($large)) {
            $l = array_pop($large);
            $this->prob[$l] = 1.0;
            $this->alias[$l] = $l;
        }

        while (!empty($small)) {
            $s = array_pop($small);
            $this->prob[$s] = 1.0;
            $this->alias[$s] = $s;
        }
    }

    /**
     * 判定値をもとに O(1) で当選アイテム番号 (1-indexed) を割り出す
     */
    public function sample(int $idx, float $u): int
    {
        if ($u < $this->prob[$idx]) {
            return $idx + 1;
        }
        return $this->alias[$idx] + 1;
    }
}

// --------------------------------------------------
// 1. 標準入力の読み込みと Alias Table 構築
// --------------------------------------------------
$firstLine = fgets(STDIN);
if ($firstLine === false) {
    exit;
}

[$nStr, $qStr] = explode(' ', trim($firstLine));
$n = (int) $nStr;
$q = (int) $qStr;

$secondLine = fgets(STDIN);
if ($secondLine === false) {
    exit;
}

$weights = array_map('floatval', explode(' ', trim($secondLine)));
$aliasMethod = new AliasMethod($weights);

// --------------------------------------------------
// 2. クエリ処理と出力 (計算量: O(1) × Q)
// --------------------------------------------------
for ($k = 0; $k < $q; $k++) {
    $qLine = fgets(STDIN);
    if ($qLine === false) {
        break;
    }

    [$iStr, $uStr] = explode(' ', trim($qLine));
    $idx = (int) $iStr;
    $u = (float) $uStr;

    echo $aliasMethod->sample($idx, $u) . "\n";
}