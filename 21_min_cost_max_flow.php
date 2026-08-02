<?php

declare(strict_types=1); // 厳密な型チェック

/**
 * 最小費用流の有向辺構造体クラス (PHP 8.0+)
 */
class MinCostFlowEdge
{
    public function __construct(
        public int $to,       // 行き先ノード
        public int $cap,      // 残余容量
        public int $cost,     // 1単位あたりのコスト
        public int $rev       // 逆辺のインデックス
    ) {}
}

/**
 * 最小ヒープ (優先度付きキュー)
 */
class MinPriorityQueue extends SplPriorityQueue
{
    public function compare(mixed $priority1, mixed $priority2): int
    {
        return $priority2 <=> $priority1;
    }
}

/**
 * Primal-Dual法による最小費用流計算クラス
 */
class MinCostMaxFlow
{
    /** @var array<int, array<int, MinCostFlowEdge>> 残余グラフ */
    private array $graph = [];

    public function __construct(private int $nodeCount) {}

    /**
     * 有向辺の追加 (容量 cap, コスト cost)
     */
    public function addEdge(int $from, int $to, int $cap, int $cost): void
    {
        $fromIdx = count($this->graph[$from] ?? []);
        $toIdx = count($this->graph[$to] ?? []);

        // 正方向の辺 (コスト cost)
        $this->graph[$from][] = new MinCostFlowEdge($to, $cap, $cost, $toIdx);
        // 逆方向の辺 (容量 0, コスト -cost)
        $this->graph[$to][] = new MinCostFlowEdge($from, 0, -$cost, $fromIdx);
    }

    /**
     * ソースからシンクへ流量 requiredFlow を流す最小費用を求める
     */
    public function solveMinCostFlow(int $source, int $sink, int $requiredFlow): int
    {
        $totalCost = 0;
        $h = array_fill(1, $this->nodeCount, 0); // ポテンシャル配列
        $prevNode = array_fill(1, $this->nodeCount, 0);
        $prevEdge = array_fill(1, $this->nodeCount, 0);
        $inf = PHP_INT_MAX;

        while ($requiredFlow > 0) {
            $dist = array_fill(1, $this->nodeCount, $inf);
            $dist[$source] = 0;

            $pq = new MinPriorityQueue();
            // [最短距離, ノード番号] をキューに挿入 (優先度は距離)
            $pq->insert([0, $source], 0);

            while (!$pq->isEmpty()) {
                /** @var array{0: int, 1: int} $item */
                $item = $pq->extract();
                [$d, $v] = $item;

                if ($dist[$v] < $d) {
                    continue;
                }

                if (!isset($this->graph[$v])) {
                    continue;
                }

                foreach ($this->graph[$v] as $i => $e) {
                    // ポテンシャルを用いた最短経路（残余コスト）探索
                    $reducedCost = $e->cost + $h[$v] - $h[$e->to];
                    if ($e->cap > 0 && $dist[$e->to] > $dist[$v] + $reducedCost) {
                        $dist[$e->to] = $dist[$v] + $reducedCost;
                        $prevNode[$e->to] = $v;
                        $prevEdge[$e->to] = $i;
                        $pq->insert([$dist[$e->to], $e->to], $dist[$e->to]);
                    }
                }
            }

            // シンクへ到達不可の場合、要求流量を流し切れない
            if ($dist[$sink] === $inf) {
                return -1;
            }

            // ポテンシャルの更新
            for ($v = 1; $v <= $this->nodeCount; $v++) {
                if ($dist[$v] < $inf) {
                    $h[$v] += $dist[$v];
                }
            }

            // 今回の増加パスで流せる最大流量 d を計算
            $d = $requiredFlow;
            $v = $sink;
            while ($v !== $source) {
                $pNode = $prevNode[$v];
                $pEdge = $prevEdge[$v];
                $d = min($d, $this->graph[$pNode][$pEdge]->cap);
                $v = $pNode;
            }

            $requiredFlow -= $d;
            $totalCost += $d * $h[$sink];

            // 残余グラフの更新
            $v = $sink;
            while ($v !== $source) {
                $pNode = $prevNode[$v];
                $pEdge = $prevEdge[$v];
                $e = $this->graph[$pNode][$pEdge];
                $e->cap -= $d;
                $this->graph[$v][$e->rev]->cap += $d;
                $v = $pNode;
            }
        }

        return $totalCost;
    }
}

// --------------------------------------------------
// 1. 標準入力の読み込みとグラフ構築
// --------------------------------------------------
$firstLine = fgets(STDIN);
if ($firstLine === false) {
    exit;
}

[$nStr, $mStr, $fStr] = explode(' ', trim($firstLine));
$nodeCount = (int) $nStr;
$edgeCount = (int) $mStr;
$requiredFlow = (int) $fStr;

$mcf = new MinCostMaxFlow($nodeCount);

for ($i = 0; $i < $edgeCount; $i++) {
    $line = fgets(STDIN);
    if ($line === false) {
        break;
    }

    [$u, $v, $cap, $cost] = array_map('intval', explode(' ', trim($line)));
    $mcf->addEdge($u, $v, $cap, $cost);
}

// --------------------------------------------------
// 2. 処理実行と出力 (ソース: 1, シンク: N)
// --------------------------------------------------
$result = $mcf->solveMinCostFlow(1, $nodeCount, $requiredFlow);

echo $result . "\n";