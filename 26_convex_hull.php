<?php

declare(strict_types=1); // 厳密な型チェック

/**
 * 2次元座標上の点を表す構造体クラス (PHP 8.0+)
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
 * 
 * @return int 外積の大きさ (> 0: 反時計回り/左折, < 0: 時計回り/右折, 0: 一直線)
 */
function crossProduct(Point $o, Point $a, Point $b): int
{
    return ($a->x - $o->x) * ($b->y - $o->y) - ($a->y - $o->y) * ($b->x - $o->x);
}

/**
 * Andrew's Monotone Chain アルゴリズムを用いて凸包を求める
 * 
 * @param array<Point> $points 座標リスト
 * @return array<Point> 凸包を構成する頂点リスト (反時計回り)
 */
function getConvexHull(array $points): array
{
    $count = count($points);
    if ($count <= 1) {
        return $points;
    }

    // 1. x 昇順、x が同じ場合は y 昇順にソート (計算量: O(N log N))
    usort($points, function (Point $a, Point $b): int {
        if ($a->x !== $b->x) {
            return $a->x <=> $b->x;
        }
        return $a->y <=> $b->y;
    });

    // 2. 下側凸包 (Lower Hull) の構築
    $lower = [];
    foreach ($points as $p) {
        while (count($lower) >= 2) {
            $last1 = $lower[count($lower) - 1];
            $last2 = $lower[count($lower) - 2];

            // 時計回り（右折または一直線）である限り、スタックから直前の点を取り除く
            if (crossProduct($last2, $last1, $p) <= 0) {
                array_pop($lower);
            } else {
                break;
            }
        }
        $lower[] = $p;
    }

    // 3. 上側凸包 (Upper Hull) の構築
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

    // 端点が重複するため、最後の要素をそれぞれ除外して結合
    array_pop($lower);
    array_pop($upper);

    return array_merge($lower, $upper);
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
// 2. 処理実行と出力
// --------------------------------------------------
$hull = getConvexHull($points);

echo count($hull) . "\n";
foreach ($hull as $p) {
    echo "{$p->x} {$p->y}\n";
}