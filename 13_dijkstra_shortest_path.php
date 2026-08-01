<?php

declare(strict_types=1); // 厳密な型チェック

/**
 * 最小ヒープ（コストが一番小さい要素を先頭にするキュー）クラス
 * PHP標準の SplPriorityQueue を拡張
 */
class MinPriorityQueue extends SplPriorityQueue
{
    /**
     * 比較メソッドをオーバーライドして昇順（最小値優先）に変更
     *
     * @param mixed $priority1
     * @param mixed $priority2
     * @return int
     */
    public function compare(mixed $priority1, mixed $priority2): int
    {
        return $priority2 <=> $priority1;
    }
}

/**
 * ダイクストラ法を用いて街 1 から街 N までの最短移動コストを求める
 *
 * @param int $nodes 街の数 N
 * @param array<int, array<array{to: int, cost: int}>> $graph 隣接リスト
 * @return int 最小コスト（到達不能なら -1）
 */
function findShortestCostDijkstra(int $nodes, array $graph): int
{
    $inf = PHP_INT_MAX;
    // 各街への最短コスト配列（無限大で初期化）
    $dist = array_fill(1, $nodes, $inf);
    $dist[1] = 0; // スタート地点のコストは 0

    $pq = new MinPriorityQueue();
    // [街番号, コスト] を挿入 (優先度はコスト)
    $pq->insert(1, 0);

    while (!$pq->isEmpty()) {
        /** @var int $currentNode */
        $currentNode = $pq->extract();
        $currentCost = $dist[$currentNode];

        // 目的地 (街 N) に到達した時点で最短確定
        if ($currentNode === $nodes) {
            return $currentCost;
        }

        if (!isset($graph[$currentNode])) {
            continue;
        }

        // 隣接する街へ移動を試みる
        foreach ($graph[$currentNode] as $edge) {
            $nextNode = $edge['to'];
            $newCost = $currentCost + $edge['cost'];

            // より少ないコストで到達できるルートが見つかった場合
            if ($newCost < $dist[$nextNode]) {
                $dist[$nextNode] = $newCost;
                $pq->insert($nextNode, $newCost);
            }
        }
    }

    return $dist[$nodes] === $inf ? -1 : $dist[$nodes];
}

// --------------------------------------------------
// 1. 標準入力の読み込みとグラフ構造の構築
// --------------------------------------------------
$firstLine = fgets(STDIN);
if ($firstLine === false) {
    exit;
}

[$nStr, $mStr] = explode(' ', trim($firstLine));
$nodes = (int) $nStr;
$edges = (int) $mStr;

$graph = [];
for ($i = 0; $i < $edges; $i++) {
    $line = fgets(STDIN);
    if ($line === false) {
        break;
    }

    [$fromStr, $toStr, $costStr] = explode(' ', trim($line));
    $from = (int) $fromStr;
    $to = (int) $toStr;
    $cost = (int) $costStr;

    $graph[$from][] = [
        'to' => $to,
        'cost' => $cost,
    ];
}

// --------------------------------------------------
// 2. 処理実行と出力
// --------------------------------------------------
$shortestCost = findShortestCostDijkstra($nodes, $graph);

echo $shortestCost . "\n";