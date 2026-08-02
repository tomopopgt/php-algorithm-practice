<?php

declare(strict_types=1); // 厳密な型チェック

/**
 * ネットワークフローの有向辺を表す構造体クラス (PHP 8.0+ プロパティプロモーション)
 */
class Edge
{
    public function __construct(
        public int $to,       // 行き先ノード
        public int $capacity, // 残余容量
        public int $rev       // 逆辺のインデックス
    ) {}
}

/**
 * Ford-Fulkerson法による最大流計算クラス
 */
class MaxFlow
{
    /** @var array<int, array<int, Edge>> 残余グラフの隣接リスト */
    private array $graph = [];

    /** @var array<int, bool> DFS用の訪問済みフラグ */
    private array $used = [];

    public function __construct(private int $nodeCount)
    {
    }

    /**
     * 有向辺を追加し、同時に容量 0 の「逆辺」を構築する
     */
    public function addEdge(int $from, int $to, int $capacity): void
    {
        $fromIdx = count($this->graph[$from] ?? []);
        $toIdx = count($this->graph[$to] ?? []);

        // 正方向の辺を追加
        $this->graph[$from][] = new Edge($to, $capacity, $toIdx);
        // 逆方向の辺を追加 (初期容量 0)
        $this->graph[$to][] = new Edge($from, 0, $fromIdx);
    }

    /**
     * 深さ優先探索 (DFS) で流せる増加パスを探す
     *
     * @param int $v 現在のノード
     * @param int $sink 目的ノード
     * @param int $flow 現在通過可能な最小容量
     * @return int 実際に流せた流量
     */
    private function dfs(int $v, int $sink, int $flow): int
    {
        if ($v === $sink) {
            return $flow;
        }

        $this->used[$v] = true;

        if (!isset($this->graph[$v])) {
            return 0;
        }

        foreach ($this->graph[$v] as $i => $edge) {
            if (!$this->used[$edge->to] && $edge->capacity > 0) {
                $pushed = $this->dfs($edge->to, $sink, min($flow, $edge->capacity));

                if ($pushed > 0) {
                    // 実際に流れた分、正方向の容量を減らし、逆方向の容量を増やす
                    $this->graph[$v][$i]->capacity -= $pushed;
                    $this->graph[$edge->to][$edge->rev]->capacity += $pushed;

                    return $pushed;
                }
            }
        }

        return 0;
    }

    /**
     * ソースからシンクへの最大流量を算出する
     */
    public function getStreamMaxFlow(int $source, int $sink): int
    {
        $maxFlow = 0;

        while (true) {
            $this->used = array_fill(1, $this->nodeCount, false);
            $pushed = $this->dfs($source, $sink, PHP_INT_MAX);

            // 増加パスが見つからなくなったら探索終了
            if ($pushed === 0) {
                break;
            }

            $maxFlow += $pushed;
        }

        return $maxFlow;
    }
}

// --------------------------------------------------
// 1. 標準入力の読み込みと MaxFlow の構築
// --------------------------------------------------
$firstLine = fgets(STDIN);
if ($firstLine === false) {
    exit;
}

[$nStr, $mStr] = explode(' ', trim($firstLine));
$nodeCount = (int) $nStr;
$edgeCount = (int) $mStr;

$maxFlowSolver = new MaxFlow($nodeCount);

for ($i = 0; $i < $edgeCount; $i++) {
    $line = fgets(STDIN);
    if ($line === false) {
        break;
    }

    [$u, $v, $cap] = array_map('intval', explode(' ', trim($line)));
    $maxFlowSolver->addEdge($u, $v, $cap);
}

// --------------------------------------------------
// 2. 処理実行と出力 (ソース: 1, シンク: N)
// --------------------------------------------------
$result = $maxFlowSolver->getStreamMaxFlow(1, $nodeCount);

echo $result . "\n";