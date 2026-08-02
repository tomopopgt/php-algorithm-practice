<?php

declare(strict_types=1); // 厳密な型チェック

/**
 * 2次元平面上の点を表す構造体クラス (PHP 8.0+)
 */
class Point
{
    public function __construct(
        public int $x,
        public int $y
    ) {}
}

/**
 * 2次元ベクトルの外積（クロス積）を計算する
 */
function crossProduct(Point $o, Point $a, Point $b): int
{
    return ($a->x - $o->x) * ($b->y - $o->y) - ($a->y - $o->y) * ($b->x - $o->x);
}

/**
 * 2点間のユークリッド距離の2乗を計算する
 */
function distSq(Point $a, Point $b): int
{
    $dx = $a->x - $b->x;
    $dy = $a->y - $b->y;
    return $dx * $dx + $dy * $dy;
}

/**
 * Andrew's Monotone Chain アルゴリズムで凸包を求める (計算量: O(N log N))
 *
 * @param array<Point> $points
 * @return array<Point> 凸包の頂点リスト (反時計回り)
 */
function getConvexHull(array $points): array
{
    $count = count($points);
    if ($count <= 1) {
        return $points;
    }

    usort($points, function (Point $a, Point $b): int {
        if ($a->x !== $b->x) {
            return $a->x <=> $b->x;
        }
        return $a->y <=> $b->y;
    });

    $lower = [];
    foreach ($points as $p) {
        while (count($lower) >= 2) {
            $last1 = $lower[count($lower) - 1];
            $last2 = $lower[count($lower) - 2];
            if (crossProduct($last2, $last1, $p) <= 0) {
                array_pop($lower);
            } else {
                break;
            }
        }
        $lower[] = $p;
    }

    $upper = [];
    $reversedPoints = array_reverse($points);
    foreach ($reversedPoints as $p) {
        while (count($upper) >= 2) {
            $last1 = $upper[count($upper) - 1];
            $last2 = $upper[count($upper) - 2];
            if (crossProduct($last2, $last1, $p) <= 0) {
                array_pop($upper);
            } else {
                break;
            }
        }
        $upper[] = $p;
    }

    array_pop($lower);
    array_pop($upper);

    return array_merge($lower, $upper);
}

/**
 * 回転のツメ (Rotating Calipers) で最遠点対の距離の2乗を求める (計算量: O(N))
 *
 * @param array<Point> $hull 凸包の頂点リスト
 * @return int 最遠点間の距離の2乗
 */
function solveRotatingCalipers(array $hull): int
{
    $n = count($hull);
    if ($n === 0) {
        return 0;
    }
    if ($n === 1) {
        return 0;
    }
    if ($n === 2) {
        return distSq($hull[0], $hull[1]);
    }

    $maxDistSq = 0;
    $j = 0;

    for ($i = 0; $i < $n; $i++) {
        $nextI = ($i + 1) % $n;

        // 辺 (hull[i], hull[nextI]) に対する対蹠点 j をしゃくとり法で探索
        while (true) {
            $nextJ = ($j + 1) % $n;

            // 三角形の面積（外積の絶対値）を比較
            $areaCurrent = abs(crossProduct($hull[$i], $hull[$nextI], $hull[$j]));
            $areaNext = abs(crossProduct($hull[$i], $hull[$nextI], $hull[$nextJ]));

            if ($areaNext > $areaCurrent) {
                $j = $nextJ;
            } else {
                break;
            }
        }

        $maxDistSq = max($maxDistSq, distSq($hull[$i], $hull[$j]));
        $maxDistSq = max($maxDistSq, distSq($hull[$nextI], $hull[$j]));
    }

    return $maxDistSq;
}

// --------------------------------------------------
// 1. 標準入力の読み込み
// --------------------------------------------------
$firstLine = fgets(STDIN);
if ($firstLine === false) {
    exit;
}

$n = (int) trim($firstLine);
$points = [];

for ($i = 0; $i < $n; $i++) {
    $line = fgets(STDIN);
    if ($line === false) {
        break;
    }

    [$x, $y] = array_map('intval', explode(' ', trim($line)));
    $points[] = new Point($x, $y);
}

// --------------------------------------------------
// 2. 処理実行と出力 (凸包計算 O(N log N) + 回転のツメ O(N))
// --------------------------------------------------
$hull = getConvexHull($points);
$result = solveRotatingCalipers($hull);

echo $result . "\n";