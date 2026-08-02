<?php

declare(strict_types=1); // 厳密な型チェック

/**
 * 最大流用・残余グラフの辺構造体クラス
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
 * 最大流最小カット定理を利用した最小カットソルバー
 */
class MinCutSolver
{
    /** @var array<int, array<int, Edge>> 残余グラフ */
    private array $graph = [];

    /** @var array<int, bool> DFS用の訪問済みフラグ */
    private array $used = [];

    private int $source;
    private int $sink;

    public function __construct(private int $nodeCount)
    {
        // 仮想のソース S = 0、シンク T = nodeCount + 1 を定義
        $this->source = 0;
        $this->sink = $nodeCount + 1;
    }

    /**
     * 残余グラフに有向辺を追加する
     */
    public function addEdge(int $from, int $to, int $capacity): void
    {
        $fromIdx = count($this->graph[$from] ?? []);
        $toIdx = count($this->graph[$to] ?? []);

        $this->graph[$from][] = new Edge($to, $capacity, $toIdx);
        $this->graph[$to][] = new Edge($from, 0, $fromIdx);
    }

    /**
     * ノード i の背景コスト A_i, 前景コスト B_i を登録
     */
    public function addPixelCost(int $node, int $backgroundCost, int $foregroundCost): void
    {
        // S -> node に 前景コスト B_i の容量
        $this->addEdge($this->source, $node, $foregroundCost);
        // node -> T に 背景コスト A_i の容量
        $this->addEdge($node, $this->sink, $backgroundCost);
    }

    /**
     * ノード u と ノード v の隣接違和感ペナルティ C を登録 (双方向)
     */
    public function addAdjacencyPenalty(int $u, int $v, int $penaltyCost): void
    {
        $this->addEdge($u, $v, $penaltyCost);
        $this->addEdge($v, $u, $penaltyCost);
    }

    /**
     * DFS で増加パスを探索 (Ford-Fulkerson法)
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
                    $this->graph[$v][$i]->capacity -= $pushed;
                    $this->graph[$edge->to][$edge->rev]->capacity += $pushed;

                    return $pushed;
                }
            }
        }

        return 0;
    }

    /**
     * 最小カットの容量（＝最大流）を算出する
     */
    public function solveMinCut(): int
    {
        $totalNodes = $this->sink + 1;
        $maxFlow = 0;

        while (true) {
            $this->used = array_fill(0, $totalNodes, false);
            $pushed = $this->dfs($this->source, $this->sink, PHP_INT_MAX);

            if ($pushed === 0) {
                break;
            }

            $maxFlow += $pushed;
        }

        return $maxFlow;
    }
}

// --------------------------------------------------
// 1. 標準入力の読み込みと グラフ構築
// --------------------------------------------------
$firstLine = fgets(STDIN);
if ($firstLine === false) {
    exit;
}

[$nStr, $mStr] = explode(' ', trim($firstLine));
$nodeCount = (int) $nStr;
$edgeCount = (int) $mStr;

$solver = new MinCutSolver($nodeCount);

// 各ノードのコスト読み込み
for ($i = 1; $i <= $nodeCount; $i++) {
    $line = fgets(STDIN);
    if ($line === false) {
        break;
    }

    [$bgCost, $fgCost] = array_map('intval', explode(' ', trim($line)));
    $solver->addPixelCost($i, $bgCost, $fgCost);
}

// 隣接ペナルティの読み込み
for ($j = 0; $j < $edgeCount; $j++) {
    $pLine = fgets(STDIN);
    if ($pLine === false) {
        break;
    }

    [$u, $v, $penalty] = array_map('intval', explode(' ', trim($pLine)));
    $solver->addAdjacencyPenalty($u, $v, $penalty);
}

// --------------------------------------------------
// 2. 処理実行と出力
// --------------------------------------------------
$minCost = $solver->solveMinCut();

echo $minCost . "\n";