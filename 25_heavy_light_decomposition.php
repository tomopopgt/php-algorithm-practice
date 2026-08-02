<?php

declare(strict_types=1); // 厳密な型チェック

/**
 * 区間加算・区間和クエリを O(log N) で処理する遅延評価セグメント木クラス
 */
class LazySegmentTree
{
    private int $n = 1;

    /** @var array<int, int> */
    private array $tree;

    /** @var array<int, int> */
    private array $lazy;

    public function __construct(int $size)
    {
        while ($this->n < $size) {
            $this->n *= 2;
        }
        $this->tree = array_fill(1, 2 * $this->n, 0);
        $this->lazy = array_fill(1, 2 * $this->n, 0);
    }

    /**
     * 遅延値を子ノードへ伝搬（評価）する
     */
    private function eval(int $k, int $l, int $r): void
    {
        if ($this->lazy[$k] !== 0) {
            $this->tree[$k] += $this->lazy[$k] * ($r - $l + 1);
            if ($k < $this->n) {
                $this->lazy[2 * $k] += $this->lazy[$k];
                $this->lazy[2 * $k + 1] += $this->lazy[$k];
            }
            $this->lazy[$k] = 0;
        }
    }

    /**
     * 区間 [a, b] に x を加算する (1-indexed)
     */
    public function add(int $a, int $b, int $x, int $k = 1, int $l = 1, ?int $r = null): void
    {
        $r ??= $this->n;
        $this->eval($k, $l, $r);

        if ($a <= $l && $r <= $b) {
            $this->lazy[$k] += $x;
            $this->eval($k, $l, $r);
        } elseif ($a <= $r && $l <= $b) {
            $mid = intdiv($l + $r, 2);
            $this->add($a, $b, $x, 2 * $k, $l, $mid);
            $this->add($a, $b, $x, 2 * $k + 1, $mid + 1, $r);
            $this->tree[$k] = $this->tree[2 * $k] + $this->tree[2 * $k + 1];
        }
    }

    /**
     * 区間 [a, b] の合計値を求める (1-indexed)
     */
    public function query(int $a, int $b, int $k = 1, int $l = 1, ?int $r = null): int
    {
        $r ??= $this->n;
        $this->eval($k, $l, $r);

        if ($r < $a || $b < $l) {
            return 0;
        }
        if ($a <= $l && $r <= $b) {
            return $this->tree[$k];
        }

        $mid = intdiv($l + $r, 2);
        $vl = $this->query($a, $b, 2 * $k, $l, $mid);
        $vr = $this->query($a, $b, 2 * $k + 1, $mid + 1, $r);

        return $vl + $vr;
    }
}

/**
 * Heavy-Light Decomposition (HLD) クラス
 */
class HeavyLightDecomposition
{
    /** @var array<int, array<int>> 無向グラフの隣接リスト */
    private array $graph = [];

    /** @var array<int, int> 直近の親 */
    private array $parent = [];

    /** @var array<int, int> 各頂点の深さ */
    private array $depth = [];

    /** @var array<int, int> 部分木のサイズ */
    private array $size = [];

    /** @var array<int, int> 重い辺で繋がる子ノード (Heavy child) */
    private array $heavy = [];

    /** @var array<int, int> 属するパスの先頭頂点 (Head) */
    private array $head = [];

    /** @var array<int, int> セグメント木上のインデックス (In-time) */
    private array $inTime = [];

    private int $time = 0;
    private LazySegmentTree $segTree;

    public function __construct(private int $nodeCount)
    {
        $this->parent = array_fill(1, $nodeCount, 0);
        $this->depth = array_fill(1, $nodeCount, 0);
        $this->size = array_fill(1, $nodeCount, 0);
        $this->heavy = array_fill(1, $nodeCount, 0);
        $this->head = array_fill(1, $nodeCount, 0);
        $this->inTime = array_fill(1, $nodeCount, 0);
        $this->segTree = new LazySegmentTree($nodeCount);
    }

    public function addEdge(int $u, int $v): void
    {
        $this->graph[$u][] = $v;
        $this->graph[$v][] = $u;
    }

    /**
     * 1段階目: 部分木サイズと Heavy child の特定
     */
    private function dfs1(int $u, int $p): void
    {
        $this->parent[$u] = $p;
        $this->depth[$u] = $this->depth[$p] + 1;
        $this->size[$u] = 1;
        $maxChildSize = 0;

        if (isset($this->graph[$u])) {
            foreach ($this->graph[$u] as $v) {
                if ($v !== $p) {
                    $this->dfs1($v, $u);
                    $this->size[$u] += $this->size[$v];
                    if ($this->size[$v] > $maxChildSize) {
                        $maxChildSize = $this->size[$v];
                        $this->heavy[$u] = $v;
                    }
                }
            }
        }
    }

    /**
     * 2段階目: パス（列）への分解とインデックス割り当て
     */
    private function dfs2(int $u, int $h): void
    {
        $this->head[$u] = $h;
        $this->time++;
        $this->inTime[$u] = $this->time;

        // Heavy child を優先して同じパスとして掘り下げる
        if ($this->heavy[$u] !== 0) {
            $this->dfs2($this->heavy[$u], $h);
        }

        if (isset($this->graph[$u])) {
            foreach ($this->graph[$u] as $v) {
                if ($v !== $this->parent[$u] && $v !== $this->heavy[$u]) {
                    // Light child は新しいパスの Head としてスタート
                    $this->dfs2($v, $v);
                }
            }
        }
    }

    /**
     * HLDの初期構築を行う
     */
    public function build(int $root = 1): void
    {
        $this->dfs1($root, 0);
        $this->dfs2($root, $root);
    }

    /**
     * 頂点 u から 頂点 v までのパス上の全頂点に x を加算する (計算量: O(log^2 N))
     */
    public function addPath(int $u, int $v, int $x): void
    {
        while ($this->head[$u] !== $this->head[$v]) {
            if ($this->depth[$this->head[$u]] > $this->depth[$this->head[$v]]) {
                [$u, $v] = [$v, $u];
            }
            $this->segTree->add($this->inTime[$this->head[$v]], $this->inTime[$v], $x);
            $v = $this->parent[$this->head[$v]];
        }

        if ($this->depth[$u] > $this->depth[$v]) {
            [$u, $v] = [$v, $u];
        }
        $this->segTree->add($this->inTime[$u], $this->inTime[$v], $x);
    }

    /**
     * 頂点 u から 頂点 v までのパス上の全頂点の合計値を求める (計算量: O(log^2 N))
     */
    public function queryPath(int $u, int $v): int
    {
        $total = 0;

        while ($this->head[$u] !== $this->head[$v]) {
            if ($this->depth[$this->head[$u]] > $this->depth[$this->head[$v]]) {
                [$u, $v] = [$v, $u];
            }
            $total += $this->segTree->query($this->inTime[$this->head[$v]], $this->inTime[$v]);
            $v = $this->parent[$this->head[$v]];
        }

        if ($this->depth[$u] > $this->depth[$v]) {
            [$u, $v] = [$v, $u];
        }
        $total += $this->segTree->query($this->inTime[$u], $this->inTime[$v]);

        return $total;
    }
}

// --------------------------------------------------
// 1. 標準入力の読み込みと HLD 構築
// --------------------------------------------------
$firstLine = fgets(STDIN);
if ($firstLine === false) {
    exit;
}

[$nStr, $qStr] = explode(' ', trim($firstLine));
$nodeCount = (int) $nStr;
$queryCount = (int) $qStr;

$hld = new HeavyLightDecomposition($nodeCount);

for ($i = 0; $i < $nodeCount - 1; $i++) {
    $line = fgets(STDIN);
    if ($line === false) {
        break;
    }

    [$u, $v] = array_map('intval', explode(' ', trim($line)));
    $hld->addEdge($u, $v);
}

// HLDの事前構築
$hld->build(1);

// --------------------------------------------------
// 2. クエリ処理と出力
// --------------------------------------------------
for ($k = 0; $k < $queryCount; $k++) {
    $qLine = fgets(STDIN);
    if ($qLine === false) {
        break;
    }

    $params = array_map('intval', explode(' ', trim($qLine)));
    $type = $params[0];

    if ($type === 1) {
        // パス加算クエリ: 1 u v x
        [, $u, $v, $x] = $params;
        $hld->addPath($u, $v, $x);
    } elseif ($type === 2) {
        // パス合計クエリ: 2 u v
        [, $u, $v] = $params;
        echo $hld->queryPath($u, $v) . "\n";
    }
}