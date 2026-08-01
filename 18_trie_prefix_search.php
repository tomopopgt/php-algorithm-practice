<?php

declare(strict_types=1); // 厳密な型チェック

/**
 * Trie木の各ノードを表すクラス
 */
class TrieNode
{
    /** @var array<string, TrieNode> 子ノードのマップ */
    public array $children = [];

    /** @var int このノードを経由する単語の数 */
    public int $prefixCount = 0;

    /** @var bool ここで単語が終了しているかどうか */
    public bool $isEndOfWord = false;
}

/**
 * Trie (プレフィックス木) クラス
 */
class Trie
{
    private TrieNode $root;

    public function __construct()
    {
        $this->root = new TrieNode();
    }

    /**
     * 単語を Trie に挿入する (計算量: O(L) ※ L は単語の長さ)
     */
    public function insert(string $word): void
    {
        $current = $this->root;
        $length = strlen($word);

        for ($i = 0; $i < $length; $i++) {
            $char = $word[$i];

            // 該当する文字の子ノードが存在しなければ新規作成
            if (!isset($current->children[$char])) {
                $current->children[$char] = new TrieNode();
            }

            $current = $current->children[$char];
            $current->prefixCount++; // 通過カウントを増やす
        }

        $current->isEndOfWord = true;
    }

    /**
     * 指定された接頭辞 (prefix) から始まる単語の数を取得する (計算量: O(L))
     */
    public function countPrefix(string $prefix): int
    {
        $current = $this->root;
        $length = strlen($prefix);

        for ($i = 0; $i < $length; $i++) {
            $char = $prefix[$i];

            //途中でノードが存在しなければ 0 個
            if (!isset($current->children[$char])) {
                return 0;
            }

            $current = $current->children[$char];
        }

        return $current->prefixCount;
    }
}

// --------------------------------------------------
// 1. 標準入力の読み込みと Trie の構築
// --------------------------------------------------
$firstLine = fgets(STDIN);
if ($firstLine === false) {
    exit;
}

[$nStr, $qStr] = explode(' ', trim($firstLine));
$n = (int) $nStr;
$q = (int) $qStr;

$trie = new Trie();

// 辞書単語の登録
for ($i = 0; $i < $n; $i++) {
    $line = fgets(STDIN);
    if ($line === false) {
        break;
    }
    $trie->insert(trim($line));
}

// --------------------------------------------------
// 2. 検索クエリの処理と出力
// --------------------------------------------------
for ($k = 0; $k < $q; $k++) {
    $qLine = fgets(STDIN);
    if ($qLine === false) {
        break;
    }

    $prefix = trim($qLine);
    echo $trie->countPrefix($prefix) . "\n";
}