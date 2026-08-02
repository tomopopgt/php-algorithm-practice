<?php

declare(strict_types=1); // 厳密な型チェック

/**
 * 直線 y = ax + b を表す構造体クラス (PHP 8.0+)
 */
class Line
{
    public function __construct(
        public int $a,
        public int $b
    ) {}

    public function eval(int $x): int
    {
        return $this->a * $x + $this->b;
    }
}

/**
 * Convex Hull Trick (CHT) クラス
 * 傾き a が単調減少、クエリ x が単調増加の条件で O(N + Q) を実現
 */
class ConvexHullTrick
{
    /** @var array<Line> 直線群を管理するデック */
    private array $lines = [];

    /** @var int デックの先頭ポインタ */
    private int $head = 0;

    /**
     * 直線 l2 が不要（冗長）かどうかを交点の大小関係で判定する
     * 交点 x12 >= 交点 x23 のとき l2 は不要
     */
    private function isRedundant(Line $l1, Line $l2, Line $l3): bool
    {
        // 浮動小数点の誤差を避けるため、分数を両辺に掛け合わせた整数演算で比較
        return ($l2->b - $l1->b) * ($l2->a - $l3->a) >= ($l3->b - $l2->b) * ($l1->a - $l2->a);
    }

    /**
     * 直線 y = ax + b を追加する (計算量: 均して O(1))
     * ※ 追加する直線の傾き a は、直前に追加した直線以下であること
     */
    public function addLine(int $a, int $b): void
    {
        $newLine = new Line($a, $b);

        while (count($this->lines) - $this->head >= 2) {
            $l1 = $this->lines[count($this->lines) - 2];
            $l2 = $this->lines[count($this->lines) - 1];

            if ($this->isRedundant($l1, $l2, $newLine)) {
                array_pop($this->lines);
            } else {
                break;
            }
        }

        $this->lines[] = $newLine;
    }

    /**
     * 最小値 min_i (a_i * x + b_i) を取得する (計算量: 均して O(1))
     * ※ クエリ x は、前回のクエリの値以上であること
     */
    public function query(int $x): int
    {
        while (count($this->lines) - $this->head >= 2) {
            $val1 = $this->lines[$this->head]->eval($x);
            $val2 = $this->lines[$this->head + 1]->eval($x);

            // 次の直線の値の方が小さければ、古い直線は二度と使われないため先頭ポインタを進める
            if ($val1 >= $val2) {
                $this->head++;
            } else {
                break;
            }
        }

        return $this->lines[$this->head]->eval($x);
    }
}

// --------------------------------------------------
// 1. 標準入力の読み込みと CHT への登録
// --------------------------------------------------
$firstLine = fgets(STDIN);
if ($firstLine === false) {
    exit;
}

[$nStr, $qStr] = explode(' ', trim($firstLine));
$n = (int) $nStr;
$q = (int) $qStr;

$cht = new ConvexHullTrick();

for ($i = 0; $i < $n; $i++) {
    $line = fgets(STDIN);
    if ($line === false) {
        break;
    }

    [$a, $b] = array_map('intval', explode(' ', trim($line)));
    $cht->addLine($a, $b);
}

// --------------------------------------------------
// 2. クエリ処理と出力
// --------------------------------------------------
for ($k = 0; $k < $q; $k++) {
    $qLine = fgets(STDIN);
    if ($qLine === false) {
        break;
    }

    $x = (int) trim($qLine);
    echo $cht->query($x) . "\n";
}