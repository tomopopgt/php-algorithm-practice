<?php

declare(strict_types=1); // 厳密な型チェック

/**
 * 2次元座標上の点を表す構造体クラス (PHP 8.0+)
 */
class Point2D
{
    public function __construct(
        public int $x,
        public int $y
    ) {}
}

/**
 * KD-Tree のノードクラス
 */
class KDNode
{
    public ?KDNode $left = null;
    public ?KDNode $right = null;

    public function __construct(
        public Point2D $point,
        public int $axis // 0: x軸での分割, 1: y軸での分割
    ) {}
}

/**
 * KD-Tree (K-Dimensional Tree) クラス
 */
class KDTree
{
    private ?KDNode $root = null;

    /**
     * @param array<Point2D> $points
     */
    public function __construct(array $points)
    {
        $this->root = $this->build($points, 0);
    }

    /**
     * 軸を交互に切り替えながら木を再帰的に構築する (計算量: O(N log N))
     *
     * @param array<Point2D> $points
     */
    private function build(array $points, int $depth): ?KDNode
    {
        if (empty($points)) {
            return null;
        }

        // 深さに応じて軸を選択 (2次元の場合は x -> y -> x -> y ...)
        $axis = $depth % 2;

        // 対象軸の座標順にソート
        usort($points, function (Point2D $a, Point2D $b) use ($axis): int {
            return $axis === 0 ? $a->x <=> $b->x : $a->y <=> $b->y;
        });

        // 中央値の要素をノードにし、左右に二分する
        $mid = intdiv(count($points), 2);
        $node = new KDNode($points[$mid], $axis);

        $node->left = $this->build(array_slice($points, 0, $mid), $depth + 1);
        $node->right = $this->build(array_slice($points, $mid + 1), $depth + 1);

        return $node;
    }

    /**
     * 指定矩形領域 [sx, ex] x [sy, ey] 内の点の個数をカウントする
     */
    public function countRange(int $sx, int $ex, int $sy, int $ey): int
    {
        return $this->query($this->root, $sx, $ex, $sy, $ey);
    }

    /**
     * 空間分割を利用した枝刈り探索 (計算量: 平均 O(√N + K))
     */
    private function query(?KDNode $node, int $sx, int $ex, int $sy, int $ey): int
    {
        if ($node === null) {
            return 0;
        }

        $count = 0;
        $x = $node->point->x;
        $y = $node->point->y;

        // 自身の点が指定矩形に含まれるか判定
        if ($sx <= $x && $x <= $ex && $sy <= $y && $y <= $ey) {
            $count++;
        }

        // 分割軸に応じて、探索が必要な子ノードのみに枝刈り分岐
        if ($node->axis === 0) {
            // x 軸での分割
            if ($x >= $sx) {
                $count += $this->query($node->left, $sx, $ex, $sy, $ey);
            }
            if ($x <= $ex) {
                $count += $this->query($node->right, $sx, $ex, $sy, $ey);
            }
        } else {
            // y 軸での分割
            if ($y >= $sy) {
                $count += $this->query($node->left, $sx, $ex, $sy, $ey);
            }
            if ($y <= $ey) {
                $count += $this->query($node->right, $sx, $ex, $sy, $ey);
            }
        }

        return $count;
    }
}

// --------------------------------------------------
// 1. 標準入力の読み込みと KD-Tree の構築
// --------------------------------------------------
$firstLine = fgets(STDIN);
if ($firstLine === false) {
    exit;
}

[$nStr, $qStr] = explode(' ', trim($firstLine));
$n = (int) $nStr;
$q = (int) $qStr;

$points = [];
for ($i = 0; $i < $n; $i++) {
    $line = fgets(STDIN);
    if ($line === false) {
        break;
    }

    [$x, $y] = array_map('intval', explode(' ', trim($line)));
    $points[] = new Point2D($x, $y);
}

// KD-Treeの事前構築
$kdTree = new KDTree($points);

// --------------------------------------------------
// 2. クエリ処理と出力
// --------------------------------------------------
for ($k = 0; $k < $q; $k++) {
    $qLine = fgets(STDIN);
    if ($qLine === false) {
        break;
    }

    [$sx, $sy, $ex, $ey] = array_map('intval', explode(' ', trim($qLine)));
    echo $kdTree->countRange($sx, $ex, $sy, $ey) . "\n";
}