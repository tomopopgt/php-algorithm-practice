<?php

declare(strict_types=1); // 厳密な型チェック

/**
 * 素集合データ構造 (Union-Find)
 */
class UnionFind
{
    /** @var array<int, int> */
    private array $parent = [];

    /** @var array<int, int> */
    private array $size = [];

    public function __construct(int $n)
    {
        for ($i = 1; $i <= $n; $i++) {
            $this->parent[$i] = $i;
            $this->size[$i] = 1;
        }
    }

    public function find(int $x): int
    {
        if ($this->parent[$x] === $x) {
            return $x;
        }
        return $this->parent[$x] = $this->find($this->parent[$x]);
    }

    public function unite(int $x, int $y): bool
    {
        $rootX = $this->find($x);
        $rootY = $this->find($y);

        if ($rootX === $rootY) {
            return false; // 閉路ができるため結合しない
        }

        if ($this->size[$rootX] < $this->size[$rootY]) {
            $this->parent[$rootX] = $rootY;
            $this->size[$rootY] += $this->size[$rootX];
        } else {
            $this->parent[$rootY] = $rootX;
            $this->size[$rootX] += $this->size[$rootY];
        }

        return true;
    }
}

/**
 * クラスカル法を用いて最小全域木（MST）のコスト合計を求める
 *
 * @param int $nodeCount サーバー数 N
 * @param array<int, array{u: int, v: int, cost: int}> $edges 辺の情報リスト
 * @return int 最小合計コスト
 */
function findMinimumSpanningTreeCost(int $nodeCount, array $edges): int
{
    // 1. コストの昇順（安い順）で辺をソート (計算量: O(M log M))
    usort($edges, function (array $a, array $b): int {
        return $a['cost'] <=> $b['cost'];
    });

    $uf = new UnionFind($nodeCount);
    $totalCost = 0;
    $edgesCount = 0;

    // 2. コストが安い辺から順に評価してグラフに追加
    foreach ($edges as $edge) {
        $u = $edge['u'];
        $v = $edge['v'];
        $cost = $edge['cost'];

        // u と v がまだ繋がっていない（閉路を作らない）場合のみ採用
        if ($uf->unite($u, $v)) {
            $totalCost += $cost;
            $edgesCount++;

            // N 個の頂点をつなぐ辺の数は必ず N - 1 本になる
            if ($edgesCount === $nodeCount - 1) {
                break;
            }
        }
    }

    return $totalCost;
}

// --------------------------------------------------
// 1. 標準入力の読み込み
// --------------------------------------------------
$firstLine = fgets(STDIN);
if ($firstLine === false) {
    exit;
}

[$nStr, $mStr] = explode(' ', trim($firstLine));
$nodeCount = (int) $nStr;
$edgeCount = (int) $mStr;

$edges = [];
for ($i = 0; $i < $edgeCount; $i++) {
    $line = fgets(STDIN);
    if ($line === false) {
        break;
    }

    [$u, $v, $cost] = array_map('intval', explode(' ', trim($line)));
    $edges[] = [
        'u' => $u,
        'v' => $v,
        'cost' => $cost,
    ];
}

// --------------------------------------------------
// 2. 処理実行と出力
// --------------------------------------------------
$minCost = findMinimumSpanningTreeCost($nodeCount, $edges);

echo $minCost . "\n";