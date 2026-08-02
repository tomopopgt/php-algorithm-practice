<?php

declare(strict_types=1); // 厳密な型チェック

/**
 * ダブリングを用いた最小共通祖先 (LCA) 計算クラス
 */
class LowestCommonAncestor
{
    private int $log;

    /** @var array<int, int> 各ノードの深さ */
    private array $depth = [];

    /** @var array<int, array<int, int>> ダブリングテーブル: parent[k][v] は v の 2^k 個上の親 */
    private array $parent = [];

    /** @var array<int, array<int>> 無向グラフの隣接リスト */
    private array $graph = [];

    public function __construct(private int $nodeCount)
    {
        // 2^log > nodeCount となる最小の log を算出（PHPでは第2引数に底の 2 を指定）
        $this->log = (int) ceil(log($nodeCount, 2)) + 1;
        $this->depth = array_fill(1, $nodeCount, -1);

        for ($k = 0; $k < $this->log; $k++) {
            $this->parent[$k] = array_fill(1, $nodeCount, 0);
        }
    }

    /**
     * ツリーの無向辺を追加する
     */
    public function addEdge(int $u, int $v): void
    {
        $this->graph[$u][] = $v;
        $this->graph[$v][] = $u;
    }

    /**
     * 幅優先探索 (BFS) で深さと 1 個上の親 (2^0) を初期化し、ダブリングテーブルを構築する
     *
     * @param int $root 根ノード (デフォルト: 1)
     */
    public function build(int $root = 1): void
    {
        /** @var SplQueue<int> $queue */
        $queue = new SplQueue();

        $this->depth[$root] = 0;
        $queue->enqueue($root);

        // 1. BFSで各ノードの深さと直近の親 (2^0 個上) を設定
        while (!$queue->isEmpty()) {
            /** @var int $u */
            $u = $queue->dequeue();

            if (!isset($this->graph[$u])) {
                continue;
            }

            foreach ($this->graph[$u] as $v) {
                if ($this->depth[$v] === -1) {
                    $this->depth[$v] = $this->depth[$u] + 1;
                    $this->parent[0][$v] = $u;
                    $queue->enqueue($v);
                }
            }
        }

        // 2. 動的計画法で 2^k 個上の親を埋める (ダブリング構築)
        for ($k = 1; $k < $this->log; $k++) {
            for ($v = 1; $v <= $this->nodeCount; $v++) {
                $p = $this->parent[$k - 1][$v];
                $this->parent[$k][$v] = ($p !== 0) ? $this->parent[$k - 1][$p] : 0;
            }
        }
    }

    /**
     * 頂点 u と 頂点 v の最小共通祖先 (LCA) を求める (計算量: O(log N))
     */
    public function getLCA(int $u, int $v): int
    {
        // 常に u の方が深い位置になるように揃える
        if ($this->depth[$u] < $this->depth[$v]) {
            [$u, $v] = [$v, $u];
        }

        // 1. u と v の深さを揃える (深い方の u を 2^k ずつ引き上げる)
        for ($k = $this->log - 1; $k >= 0; $k--) {
            if ((($this->depth[$u] - $this->depth[$v]) >> $k) & 1) {
                $u = $this->parent[$k][$u];
            }
        }

        if ($u === $v) {
            return $u;
        }

        // 2. u と v が一致する手前まで、同時に 2^k ずつ親へ登る
        for ($k = $this->log - 1; $k >= 0; $k--) {
            if ($this->parent[$k][$u] !== $this->parent[$k][$v]) {
                $u = $this->parent[$k][$u];
                $v = $this->parent[$k][$v];
            }
        }

        // 最後に1つ上の親が最小共通祖先
        return $this->parent[0][$u];
    }
}

// --------------------------------------------------
// 1. 標準入力の読み込みとグラフ構築
// --------------------------------------------------
$firstLine = fgets(STDIN);
if ($firstLine === false) {
    exit;
}

[$nStr, $qStr] = explode(' ', trim($firstLine));
$nodeCount = (int) $nStr;
$queryCount = (int) $qStr;

$lca = new LowestCommonAncestor($nodeCount);

for ($i = 0; $i < $nodeCount - 1; $i++) {
    $line = fgets(STDIN);
    if ($line === false) {
        break;
    }

    [$u, $v] = array_map('intval', explode(' ', trim($line)));
    $lca->addEdge($u, $v);
}

// 前処理 (ダブリングテーブル構築: O(N log N))
$lca->build(1);

// --------------------------------------------------
// 2. クエリの処理と出力 (計算量: O(log N) × Q)
// --------------------------------------------------
for ($k = 0; $k < $queryCount; $k++) {
    $qLine = fgets(STDIN);
    if ($qLine === false) {
        break;
    }

    [$u, $v] = array_map('intval', explode(' ', trim($qLine)));
    echo $lca->getLCA($u, $v) . "\n";
}