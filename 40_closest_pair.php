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
 * 2点間のユークリッド距離の2乗を計算する
 */
function distSq(Point $a, Point $b): int
{
    $dx = $a->x - $b->x;
    $dy = $a->y - $b->y;
    return $dx * $dx + $dy * $dy;
}

/**
 * 分割統治法を用いた最近点対問題 Solver
 */
class ClosestPairSolver
{
    /**
     * @param array<Point> $points 点の配列
     * @return int 最小距離の2乗
     */
    public function solve(array $points): int
    {
        $n = count($points);
        if ($n <= 1) {
            return 0;
        }

        // x 座標昇順、y 座標昇順でソートした配列を用意
        $ptsX = $points;
        usort($ptsX, fn(Point $a, Point $b) => $a->x !== $b->x ? $a->x <=> $b->x : $a->y <=> $b->y);

        $ptsY = $points;
        usort($ptsY, fn(Point $a, Point $b) => $a->y !== $b->y ? $a->y <=> $b->y : $a->x <=> $b->x);

        return $this->closestPair($ptsX, $ptsY);
    }

    /**
     * 再帰的な分割統治クエリ (計算量: O(N log N))
     *
     * @param array<Point> $ptsX x 座標ソート済みの点リスト
     * @param array<Point> $ptsY y 座標ソート済みの点リスト
     */
    private function closestPair(array $ptsX, array $ptsY): int
    {
        $n = count($ptsX);

        // 要素数が 3 以下の場合は全探索で基底処理
        if ($n <= 3) {
            $minD = PHP_INT_MAX;
            for ($i = 0; $i < $n; $i++) {
                for ($j = $i + 1; $j < $n; $j++) {
                    $minD = min($minD, distSq($ptsX[$i], $ptsX[$j]));
                }
            }
            return $minD;
        }

        // 1. 中央の x 座標で左右に二分 (Divide)
        $mid = intdiv($n, 2);
        $midX = $ptsX[$mid]->x;

        $leftX = array_slice($ptsX, 0, $mid);
        $rightX = array_slice($ptsX, $mid);

        // SplObjectStorage を用いて高速に左右グループ分け
        $leftSet = new SplObjectStorage();
        foreach ($leftX as $p) {
            $leftSet->attach($p);
        }

        $leftY = [];
        $rightY = [];
        foreach ($ptsY as $p) {
            if ($leftSet->contains($p)) {
                $leftY[] = $p;
            } else {
                $rightY[] = $p;
            }
        }

        // 2. 左右の領域を再帰的に解く
        $d1 = $this->closestPair($leftX, $leftY);
        $d2 = $this->closestPair($rightX, $rightY);
        $d = min($d1, $d2);

        // 3. 境界（x = midX）から距離 d 未満の点を y 座標順に抽出して統合比較 (Combine)
        $strip = [];
        foreach ($ptsY as $p) {
            $dx = $p->x - $midX;
            if ($dx * $dx < $d) {
                $strip[] = $p;
            }
        }

        $stripLen = count($strip);
        for ($i = 0; $i < $stripLen; $i++) {
            for ($j = $i + 1; $j < $stripLen; $j++) {
                $dy = $strip[$j]->y - $strip[$i]->y;
                if ($dy * $dy >= $d) {
                    break; // y 座標差の2乗が d 以上になればこれ以降の比較は不要
                }
                $d = min($d, distSq($strip[$i], $strip[$j]));
            }
        }

        return $d;
    }
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
// 2. 処理実行と出力 (計算量: O(N log N))
// --------------------------------------------------
$solver = new ClosestPairSolver();
$result = $solver->solve($points);

echo $result . "\n";