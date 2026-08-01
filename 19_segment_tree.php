<?php

declare(strict_types=1); // 厳密な型チェック

/**
 * 区間最小値クエリ (RMQ) を O(log N) で処理するセグメント木クラス
 */
class SegmentTree
{
    private int $n = 1;

    /** @var array<int, int> 木構造を表す配列 */
    private array $tree;

    private int $inf = PHP_INT_MAX;

    /**
     * @param array<int> $initialValues 初期配列データ
     */
    public function __construct(array $initialValues)
    {
        $size = count($initialValues);
        
        // 要素数以上の最小の 2 の累乗数を計算 (完全二分木のサイズ確保)
        while ($this->n < $size) {
            $this->n *= 2;
        }

        // ノード数は 2 * n - 1 (1-indexed で管理するため 2 * n)
        $this->tree = array_fill(1, 2 * $this->n, $this->inf);

        // 葉ノード（最下層）に初期値をセット
        for ($i = 0; $i < $size; $i++) {
            $this->tree[$this->n + $i] = $initialValues[$i];
        }

        // 下から上に向かって親ノードの最小値を計算 (ボトムアップ構築)
        for ($i = $this->n - 1; $i >= 1; $i--) {
            $this->tree[$i] = min($this->tree[2 * $i], $this->tree[2 * $i + 1]);
        }
    }

    /**
     * インデックス i (1-indexed) の値を val に更新する (計算量: O(log N))
     */
    public function update(int $i, int $val): void
    {
        // 葉ノードの位置へ移動 (0-indexed に変換して加算)
        $idx = $this->n + ($i - 1);
        $this->tree[$idx] = $val;

        // 親に向かって登りながら値を更新
        while ($idx > 1) {
            $idx = intdiv($idx, 2); // 親ノードのインデックス
            $this->tree[$idx] = min($this->tree[2 * $idx], $this->tree[2 * $idx + 1]);
        }
    }

    /**
     * 指定区間 [left, right] (1-indexed) の最小値を求める (計算量: O(log N))
     */
    public function query(int $left, int $right): int
    {
        return $this->queryRecursive($left, $right, 1, 1, $this->n);
    }

    /**
     * 再帰的に区間最小値を探索するヘルパー関数
     *
     * @param int $ql 求めたい区間の左端
     * @param int $qr 求めたい区間の右端
     * @param int $k  現在のノードインデックス
     * @param int $l  現在のノードがカバーする区間の左端
     * @param int $r  現在のノードがカバーする区間の右端
     */
    private function queryRecursive(int $ql, int $qr, int $k, int $l, int $r): int
    {
        // 求めたい区間と現在のノードの範囲が全く交差しない場合
        if ($r < $ql || $qr < $l) {
            return $this->inf;
        }

        // 現在のノードの範囲が求めたい区間に完全に包摂される場合
        if ($ql <= $l && $r <= $qr) {
            return $this->tree[$k];
        }

        // 一部だけ重なる場合は左右の子ノードに分割して再帰問い合わせ
        $mid = intdiv($l + $r, 2);
        $vl = $this->queryRecursive($ql, $qr, 2 * $k, $l, $mid);
        $vr = $this->queryRecursive($ql, $qr, 2 * $k + 1, $mid + 1, $r);

        return min($vl, $vr);
    }
}

// --------------------------------------------------
// 1. 標準入力の読み込みと SegmentTree の構築
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

$segTree = new SegmentTree($initialValues);

// --------------------------------------------------
// 2. クエリの処理と出力
// --------------------------------------------------
for ($k = 0; $k < $q; $k++) {
    $qLine = fgets(STDIN);
    if ($qLine === false) {
        break;
    }

    $params = array_map('intval', explode(' ', trim($qLine)));
    $type = $params[0];

    if ($type === 1) {
        // 更新クエリ: 1 i x
        [, $i, $x] = $params;
        $segTree->update($i, $x);
    } elseif ($type === 2) {
        // 最小値クエリ: 2 l r
        [, $left, $right] = $params;
        echo $segTree->query($left, $right) . "\n";
    }
}