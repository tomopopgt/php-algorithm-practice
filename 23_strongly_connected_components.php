<?php

declare(strict_types=1); // 厳密な型チェック

/**
 * Kosarajuのアルゴリズムを用いた強連結成分分解 (SCC) クラス
 */
class StronglyConnectedComponents
{
    /** @var array<int, array<int>> 正方向の隣接リスト */
    private array $graph = [];

    /** @var array<int, array<int>> 逆方向の隣接リスト */
    private array $reversedGraph = [];

    /** @var array<int, bool> DFS用の訪問済みフラグ */
    private array $visited = [];

    /** @var array<int> 帰還順 (post-order) の記録 */
    private array $order = [];

    public function __construct(private int $nodeCount)
    {
    }

    /**
     * 有向辺 (from -> to) を追加する
     */
    public function addEdge(int $from, int $to): void
    {
        $this->graph[$from][] = $to;
        $this->reversedGraph[$to][] = $from; // 逆向きのグラフも保持
    }

    /**
     * 1回目のDFS: 探索が終わったノードから順に order に追加する
     */
    private function dfs1(int $v): void
    {
        $this->visited[$v] = true;

        if (isset($this->graph[$v])) {
            foreach ($this->graph[$v] as $next) {
                if (!($this->visited[$next] ?? false)) {
                    $this->dfs1($next);
                }
            }
        }

        $this->order[] = $v;
    }

    /**
     * 2回目のDFS: 逆グラフ上で到達できるノードを同じグループとして収集する
     *
     * @param int $v
     * @param array<int> $component
     */
    private function dfs2(int $v, array &$component): void
    {
        $this->visited[$v] = true;
        $component[] = $v;

        if (isset($this->reversedGraph[$v])) {
            foreach ($this->reversedGraph[$v] as $next) {
                if (!($this->visited[$next] ?? false)) {
                    $this->dfs2($next, $component);
                }
            }
        }
    }

    /**
     * 強連結成分分解を実行し、トポロジカル順にグループ化された配列を返す
     *
     * @return array<array<int>> 強連結成分のリスト
     */
    public function getSCC(): array
    {
        // 1. 正方向グラフで帰還順を取得
        $this->visited = array_fill(1, $this->nodeCount, false);
        $this->order = [];

        for ($i = 1; $i <= $this->nodeCount; $i++) {
            if (!$this->visited[$i]) {
                $this->dfs1($i);
            }
        }

        // 2. 逆方向グラフで帰還順の遅い（一番最後に終わった）ノードからDFS
        $this->visited = array_fill(1, $this->nodeCount, false);
        $sccList = [];

        for ($i = count($this->order) - 1; $i >= 0; $i--) {
            $v = $this->order[$i];
            if (!$this->visited[$v]) {
                $component = [];
                $this->dfs2($v, $component);
                sort($component); // グループ内を見やすくソート
                $sccList[] = $component;
            }
        }

        return $sccList;
    }
}

// --------------------------------------------------
// 1. 標準入力の読み込みとグラフ構築
// --------------------------------------------------
$firstLine = fgets(STDIN);
if ($firstLine === false) {
    exit;
}

[$nStr, $mStr] = explode(' ', trim($firstLine));
$nodeCount = (int) $nStr;
$edgeCount = (int) $mStr;

$scc = new StronglyConnectedComponents($nodeCount);

for ($i = 0; $i < $edgeCount; $i++) {
    $line = fgets(STDIN);
    if ($line === false) {
        break;
    }

    [$u, $v] = array_map('intval', explode(' ', trim($line)));
    $scc->addEdge($u, $v);
}

// --------------------------------------------------
// 2. 処理実行と出力
// --------------------------------------------------
$components = $scc->getSCC();

echo count($components) . "\n";
foreach ($components as $group) {
    echo implode(' ', $group) . "\n";
}