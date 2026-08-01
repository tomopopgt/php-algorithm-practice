<?php

declare(strict_types=1); // 厳密な型チェック

/**
 * Kahnのアルゴリズムを用いてタスクの依存関係を解き、トポロジカルソートする
 *
 * @param int $taskCount タスク数 N
 * @param array<int, array<int>> $graph 依存の向かう先 (B -> A)
 * @param array<int, int> $inDegree 各タスクの未解消依存数
 * @return array<int> 実行順序（循環依存があれば空配列）
 */
function solveTaskOrder(int $taskCount, array $graph, array $inDegree): array
{
    /** @var SplQueue<int> $queue */
    $queue = new SplQueue();

    // 依存関係のないタスク (入次数が 0) をキューに投入
    for ($i = 1; $i <= $taskCount; $i++) {
        if (($inDegree[$i] ?? 0) === 0) {
            $queue->enqueue($i);
        }
    }

    $result = [];

    while (!$queue->isEmpty()) {
        /** @var int $current */
        $current = $queue->dequeue();
        $result[] = $current;

        // 現在のタスクに依存していた後続タスクの依存数を減らす
        if (isset($graph[$current])) {
            foreach ($graph[$current] as $nextTask) {
                $inDegree[$nextTask]--;

                // 依存がすべて解消されたらキューに追加
                if ($inDegree[$nextTask] === 0) {
                    $queue->enqueue($nextTask);
                }
            }
        }
    }

    // 処理できたタスク数が全タスク数と一致しない場合は循環依存（エラー）
    if (count($result) !== $taskCount) {
        return [];
    }

    return $result;
}

// --------------------------------------------------
// 1. 標準入力の読み込みとグラフ構造の構築
// --------------------------------------------------
$firstLine = fgets(STDIN);
if ($firstLine === false) {
    exit;
}

[$nStr, $mStr] = explode(' ', trim($firstLine));
$taskCount = (int) $nStr;
$relationCount = (int) $mStr;

$graph = [];
$inDegree = array_fill(1, $taskCount, 0);

for ($i = 0; $i < $relationCount; $i++) {
    $line = fgets(STDIN);
    if ($line === false) {
        break;
    }

    // taskA を行う前に taskB が必要 (B -> A)
    [$taskAStr, $taskBStr] = explode(' ', trim($line));
    $taskA = (int) $taskAStr;
    $taskB = (int) $taskBStr;

    $graph[$taskB][] = $taskA;
    $inDegree[$taskA]++;
}

// --------------------------------------------------
// 2. 処理実行と出力
// --------------------------------------------------
$order = solveTaskOrder($taskCount, $graph, $inDegree);

if (empty($order)) {
    echo "-1\n";
} else {
    echo implode(' ', $order) . "\n";
}