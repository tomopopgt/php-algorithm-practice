<?php

declare(strict_types=1); // 厳密な型チェック

/**
 * 2-SAT (2-Satisfiability) 判定クラス
 * 強連結成分分解 (SCC / Kosaraju法) を内包
 */
class TwoSAT
{
    private int $vars;
    private int $nodes;

    /** @var array<int, array<int>> 正方向グラフ */
    private array $graph = [];

    /** @var array<int, array<int>> 逆方向グラフ */
    private array $reversedGraph = [];

    /** @var array<int, bool> DFS用訪問フラグ */
    private array $visited = [];

    /** @var array<int> 帰還順 */
    private array $order = [];

    public function __construct(int $vars)
    {
        $this->vars = $vars;
        // 各変数 i について、x_i (1〜vars) と ¬x_i (vars+1〜2*vars) の 2 頂点を用意
        $this->nodes = $vars * 2;
    }

    /**
     * 変数番号（負数は否定）をグラフのノードID（1〜2*vars）に変換
     */
    private function getNode(int $v): int
    {
        return $v > 0 ? $v : abs($v) + $this->vars;
    }

    /**
     * 変数番号の否定に対応するノードIDを取得
     */
    private function getNegatedNode(int $v): int
    {
        return $v > 0 ? $v + $this->vars : abs($v);
    }

    /**
     * 条件 (a OR b) を追加
     * (a OR b) <=> (¬a => b) AND (¬b => a)
     */
    public function addClause(int $a, int $b): void
    {
        $nodeA = $this->getNode($a);
        $negA = $this->getNegatedNode($a);
        $nodeB = $this->getNode($b);
        $negB = $this->getNegatedNode($b);

        // ¬a => b
        $this->graph[$negA][] = $nodeB;
        $this->reversedGraph[$nodeB][] = $negA;

        // ¬b => a
        $this->graph[$negB][] = $nodeA;
        $this->reversedGraph[$nodeA][] = $negB;
    }

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
     * @param array<int> $component
     */
    private function dfs2(int $v, int $compId, array &$compMap): void
    {
        $this->visited[$v] = true;
        $compMap[$v] = $compId;

        if (isset($this->reversedGraph[$v])) {
            foreach ($this->reversedGraph[$v] as $next) {
                if (!($this->visited[$next] ?? false)) {
                    $this->dfs2($next, $compId, $compMap);
                }
            }
        }
    }

    /**
     * 2-SAT が充足可能か判定する (計算量: O(N + M))
     */
    public function isSatisfiable(): bool
    {
        // 1. 正方向DFS
        $this->visited = array_fill(1, $this->nodes, false);
        $this->order = [];

        for ($i = 1; $i <= $this->nodes; $i++) {
            if (!$this->visited[$i]) {
                $this->dfs1($i);
            }
        }

        // 2. 逆方向DFS (SCCのグループ化)
        $this->visited = array_fill(1, $this->nodes, false);
        $compMap = array_fill(1, $this->nodes, 0);
        $compId = 0;

        for ($i = count($this->order) - 1; $i >= 0; $i--) {
            $v = $this->order[$i];
            if (!$this->visited[$v]) {
                $compId++;
                $this->dfs2($v, $compId, $compMap);
            }
        }

        // 3. 各変数 i について、x_i と ¬x_i が同じSCCグループに属していれば矛盾 (UNSAT)
        for ($i = 1; $i <= $this->vars; $i++) {
            if ($compMap[$i] === $compMap[$i + $this->vars]) {
                return false;
            }
        }

        return true;
    }
}

// --------------------------------------------------
// 1. 標準入力の読み込み
// --------------------------------------------------
$firstLine = fgets(STDIN);
if ($firstLine === false) {
    exit;
}

[$nStr, $mStr] = explode(' ', trim($firstLine));
$n = (int) $nStr;
$m = (int) $mStr;

$sat = new TwoSAT($n);

for ($i = 0; $i < $m; $i++) {
    $line = fgets(STDIN);
    if ($line === false) {
        break;
    }

    [$a, $b] = array_map('intval', explode(' ', trim($line)));
    $sat->addClause($a, $b);
}

// --------------------------------------------------
// 2. 処理実行と出力
// --------------------------------------------------
echo ($sat->isSatisfiable() ? 'YES' : 'NO') . "\n";