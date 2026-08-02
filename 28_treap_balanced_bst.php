<?php

declare(strict_types=1); // 厳密な型チェック

/**
 * Treap のノード構造体クラス
 */
class TreapNode
{
    public int $priority;
    public int $size = 1; // このノードを根とする部分木の要素数
    public ?TreapNode $left = null;
    public ?TreapNode $right = null;

    public function __construct(public int $key)
    {
        // 乱数による優先度割り当てで木の平衡を確率的に保証
        $this->priority = random_int(0, PHP_INT_MAX);
    }

    /**
     * 子ノードのサイズから自ノードの部分木サイズを更新する
     */
    public function updateSize(): void
    {
        $this->size = 1 + ($this->left?->size ?? 0) + ($this->right?->size ?? 0);
    }
}

/**
 * 平衡二分探索木 (Treap) クラス
 */
class Treap
{
    private ?TreapNode $root = null;

    /**
     * 右回転 (Right Rotation)
     */
    private function rotateRight(TreapNode $node): TreapNode
    {
        /** @var TreapNode $left */
        $left = $node->left;
        $node->left = $left->right;
        $left->right = $node;

        $node->updateSize();
        $left->updateSize();

        return $left;
    }

    /**
     * 左回転 (Left Rotation)
     */
    private function rotateLeft(TreapNode $node): TreapNode
    {
        /** @var TreapNode $right */
        $right = $node->right;
        $node->right = $right->left;
        $right->left = $node;

        $node->updateSize();
        $right->updateSize();

        return $right;
    }

    /**
     * 要素の挿入 (計算量: O(log N))
     */
    private function insertNode(?TreapNode $node, int $key): TreapNode
    {
        if ($node === null) {
            return new TreapNode($key);
        }

        if ($key < $node->key) {
            $node->left = $this->insertNode($node->left, $key);
            // ヒープ条件が崩れたら右回転で引き上げる
            if ($node->left->priority > $node->priority) {
                $node = $this->rotateRight($node);
            }
        } elseif ($key > $node->key) {
            $node->right = $this->insertNode($node->right, $key);
            // ヒープ条件が崩れたら左回転で引き上げる
            if ($node->right->priority > $node->priority) {
                $node = $this->rotateLeft($node);
            }
        }

        $node->updateSize();
        return $node;
    }

    public function insert(int $key): void
    {
        $this->root = $this->insertNode($this->root, $key);
    }

    /**
     * 要素の削除 (計算量: O(log N))
     */
    private function deleteNode(?TreapNode $node, int $key): ?TreapNode
    {
        if ($node === null) {
            return null;
        }

        if ($key < $node->key) {
            $node->left = $this->deleteNode($node->left, $key);
        } elseif ($key > $node->key) {
            $node->right = $this->deleteNode($node->right, $key);
        } else {
            // 削除対象ノードを発見：葉になるまで回転で押し下げる
            if ($node->left === null && $node->right === null) {
                return null;
            }

            if ($node->left === null) {
                $node = $this->rotateLeft($node);
                $node->left = $this->deleteNode($node->left, $key);
            } elseif ($node->right === null) {
                $node = $this->rotateRight($node);
                $node->right = $this->deleteNode($node->right, $key);
            } else {
                if ($node->left->priority > $node->right->priority) {
                    $node = $this->rotateRight($node);
                    $node->right = $this->deleteNode($node->right, $key);
                } else {
                    $node = $this->rotateLeft($node);
                    $node->left = $this->deleteNode($node->left, $key);
                }
            }
        }

        $node->updateSize();
        return $node;
    }

    public function delete(int $key): void
    {
        $this->root = $this->deleteNode($this->root, $key);
    }

    /**
     * k 番目に小さな値を検索する (1-indexed, 計算量: O(log N))
     */
    private function findKthNode(?TreapNode $node, int $k): int
    {
        if ($node === null) {
            return -1;
        }

        $leftSize = $node->left?->size ?? 0;

        if ($k === $leftSize + 1) {
            return $node->key;
        }

        if ($k <= $leftSize) {
            return $this->findKthNode($node->left, $k);
        }

        return $this->findKthNode($node->right, $k - $leftSize - 1);
    }

    public function findKth(int $k): int
    {
        return $this->findKthNode($this->root, $k);
    }
}

// --------------------------------------------------
// 1. 標準入力の読み込み
// --------------------------------------------------
$firstLine = fgets(STDIN);
if ($firstLine === false) {
    exit;
}

$q = (int) trim($firstLine);
$treap = new Treap();

// --------------------------------------------------
// 2. クエリ処理と出力
// --------------------------------------------------
for ($i = 0; $i < $q; $i++) {
    $line = fgets(STDIN);
    if ($line === false) {
        break;
    }

    $params = array_map('intval', explode(' ', trim($line)));
    $type = $params[0];

    if ($type === 1) {
        // 挿入: 1 x
        $treap->insert($params[1]);
    } elseif ($type === 2) {
        // 削除: 2 x
        $treap->delete($params[1]);
    } elseif ($type === 3) {
        // k 番目の値取得: 3 k
        echo $treap->findKth($params[1]) . "\n";
    }
}