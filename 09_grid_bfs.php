<?php

declare(strict_types=1); // 厳密な型チェック

/**
 * 2次元グリッド上のスタートからゴールまでの最短歩数を求める (BFS: 幅優先探索)
 *
 * @param array<int, string> $grid 迷路マップ
 * @param int $height 縦のサイズ H
 * @param int $width 横のサイズ W
 * @return int 最短歩数（到達不可なら -1）
 */
function findShortestPath(array $grid, int $height, int $width): int
{
    $start = null;
    $goal = null;

    // 1. スタート(S) と ゴール(G) の座標を特定
    for ($y = 0; $y < $height; $y++) {
        for ($x = 0; $x < $width; $x++) {
            if ($grid[$y][$x] === 'S') {
                $start = [$y, $x];
            } elseif ($grid[$y][$x] === 'G') {
                $goal = [$y, $x];
            }
        }
    }

    if ($start === null || $goal === null) {
        return -1;
    }

    // 上下左右の移動量ベクター
    $directions = [
        [-1, 0], [1, 0], [0, -1], [0, 1]
    ];

    // 最短距離を記録する配列（未訪問の場所は -1 で初期化）
    $dist = array_fill(0, $height, array_fill(0, $width, -1));

    // PHP標準ライブラリの「キュー (SplQueue)」を利用
    /** @var SplQueue<array{0: int, 1: int}> $queue */
    $queue = new SplQueue();

    // スタート地点の設定
    [$sy, $sx] = $start;
    $dist[$sy][$sx] = 0;
    $queue->enqueue([$sy, $sx]); // キューに追加 (Push)

    // キューが空になるまで探索（幅優先探索）
    while (!$queue->isEmpty()) {
        /** @var array{0: int, 1: int} $current */
        $current = $queue->dequeue(); // 先頭から取り出し (Pop)
        [$y, $x] = $current;

        // ゴールに到達したら、その時点の歩数を即座に返す
        if ($y === $goal[0] && $x === $goal[1]) {
            return $dist[$y][$x];
        }

        // 上下左右の隣接マスを調べる
        foreach ($directions as [$dy, $dx]) {
            $ny = $y + $dy;
            $nx = $x + $dx;

            // 境界チェック（マップの外に出ていないか）
            if ($ny < 0 || $ny >= $height || $nx < 0 || $nx >= $width) {
                continue;
            }

            // 壁 (#) または 訪問済み (dist != -1) の場合はスキップ
            if ($grid[$ny][$nx] === '#' || $dist[$ny][$nx] !== -1) {
                continue;
            }

            // 歩数を更新してキューに追加
            $dist[$ny][$nx] = $dist[$y][$x] + 1;
            $queue->enqueue([$ny, $nx]);
        }
    }

    return -1; // ゴールに到達できない場合
}

// --------------------------------------------------
// 1. 標準入力の読み込み
// --------------------------------------------------
$firstLine = fgets(STDIN);
if ($firstLine === false) {
    exit;
}

[$heightStr, $widthStr] = explode(' ', trim($firstLine));
$height = (int) $heightStr;
$width = (int) $widthStr;

$grid = [];
for ($i = 0; $i < $height; $i++) {
    $line = fgets(STDIN);
    if ($line === false) {
        break;
    }
    $grid[] = trim($line);
}

// --------------------------------------------------
// 2. 処理実行と出力
// --------------------------------------------------
$shortestSteps = findShortestPath($grid, $height, $width);

echo $shortestSteps . "\n";