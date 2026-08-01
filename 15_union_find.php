<?php

declare(strict_types=1); // 厳密な型チェック

/**
 * 素集合データ構造 (Union-Find)
 */
class UnionFind
{
    /** @var array<int, int> 親ノードのインデックス */
    private array $parent = [];

    /** @var array<int, int> 木のサイズ（グループの要素数） */
    private array $size = [];

    public function __construct(int $n)
    {
        for ($i = 1; $i <= $n; $i++) {
            $this->parent[$i] = $i; // 初期状態では自分が親
            $this->size[$i] = 1;
        }
    }

    /**
     * 要素 x の属するグループの根（代表者）を求める（経路圧縮付き）
     */
    public function find(int $x): int
    {
        if ($this->parent[$x] === $x) {
            return $x;
        }

        // 経路圧縮：再帰的に親を根に直接繋ぎ直す
        return $this->parent[$x] = $this->find($this->parent[$x]);
    }

    /**
     * 要素 x と要素 y の属するグループを結合する（Union by Size）
     */
    public function unite(int $x, int $y): bool
    {
        $rootX = $this->find($x);
        $rootY = $this->find($y);

        if ($rootX === $rootY) {
            return false; // すでに同じグループ
        }

        // 小さい方の木が大きい方の木の下にぶら下がるように結合
        if ($this->size[$rootX] < $this->size[$rootY]) {
            $this->parent[$rootX] = $rootY;
            $this->size[$rootY] += $this->size[$rootX];
        } else {
            $this->parent[$rootY] = $rootX;
            $this->size[$rootX] += $this->size[$rootY];
        }

        return true;
    }

    /**
     * 要素 x と要素 y が同じグループか判定する
     */
    public function isSame(int $x, int $y): bool
    {
        return $this->find($x) === $this->find($y);
    }
}

// --------------------------------------------------
// 1. 標準入力の読み込み
// --------------------------------------------------
$firstLine = fgets(STDIN);
if ($firstLine === false) {
    exit;
}

[$nStr, $mStr, $qStr] = explode(' ', trim($firstLine));
$n = (int) $nStr;
$m = (int) $mStr;
$q = (int) $qStr;

$uf = new UnionFind($n);

// 友達関係の登録 (Union)
for ($i = 0; $i < $m; $i++) {
    $line = fgets(STDIN);
    if ($line === false) {
        break;
    }

    [$u, $v] = array_map('intval', explode(' ', trim($line)));
    $uf->unite($u, $v);
}

// --------------------------------------------------
// 2. クエリの処理と出力 (Find)
// --------------------------------------------------
for ($i = 0; $i < $q; $i++) {
    $qLine = fgets(STDIN);
    if ($qLine === false) {
        break;
    }

    [$a, $b] = array_map('intval', explode(' ', trim($qLine)));

    echo ($uf->isSame($a, $b) ? 'YES' : 'NO') . "\n";
}