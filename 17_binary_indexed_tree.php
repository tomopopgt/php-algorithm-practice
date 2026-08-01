<?php

declare(strict_types=1); // 厳密な型チェック

/**
 * Binary Indexed Tree (Fenwick Tree) クラス
 *
 * 点更新と区間和クエリをともに O(log N) で処理する高度データ構造
 */
class BinaryIndexedTree
{
    private int $size;

    /** @var array<int, int> 1-indexed のツリー構造データ */
    private array $tree;

    public function __construct(int $size)
    {
        $this->size = $size;
        // 1-indexed のため size + 1 で 0 初期化
        $this->tree = array_fill(1, $size, 0);
    }

    /**
     * インデックス i (1-indexed) の要素に val を加算する (計算量: O(log N))
     *
     * @param int $i 対象インデックス
     * @param int $val 加算する値
     */
    public function add(int $i, int $val): void
    {
        // $idx & -$idx は LSB (Least Significant Bit) を抽出するビット演算
        for ($idx = $i; $idx <= $this->size; $idx += ($idx & -$idx)) {
            $this->tree[$idx] += $val;
        }
    }

    /**
     * 先頭 (1) から インデックス i までの累積和を取得する (計算量: O(log N))
     *
     * @param int $i 対象インデックス
     * @return int 1 〜 i の合計
     */
    public function sum(int $i): int
    {
        $total = 0;
        for ($idx = $i; $idx > 0; $idx -= ($idx & -$idx)) {
            $total += $this->tree[$idx];
        }

        return $total;
    }

    /**
     * インデックス left から right までの区間和を取得する (計算量: O(log N))
     *
     * @param int $left 開始インデックス
     * @param int $right 終了インデックス
     * @return int left 〜 right の合計
     */
    public function rangeSum(int $left, int $right): int
    {
        return $this->sum($right) - $this->sum($left - 1);
    }
}

// --------------------------------------------------
// 1. 標準入力の読み込み
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

$initialValues = array_map('intval', explode(' ', trim($secondLine)));

// BITの構築
$bit = new BinaryIndexedTree($n);
for ($i = 0; $i < $n; $i++) {
    // 1-indexed に合わせて初期値を登録
    $bit->add($i + 1, $initialValues[$i]);
}

// --------------------------------------------------
// 2. クエリ処理と出力
// --------------------------------------------------
for ($k = 0; $k < $q; $k++) {
    $qLine = fgets(STDIN);
    if ($qLine === false) {
        break;
    }

    $params = array_map('intval', explode(' ', trim($qLine)));
    $type = $params[0];

    if ($type === 1) {
        // 更新クエリ: 1 i x (i 番目に x を加算)
        [, $i, $x] = $params;
        $bit->add($i, $x);
    } elseif ($type === 2) {
        // 区間和クエリ: 2 l r (l 〜 r の和)
        [, $left, $right] = $params;
        echo $bit->rangeSum($left, $right) . "\n";
    }
}