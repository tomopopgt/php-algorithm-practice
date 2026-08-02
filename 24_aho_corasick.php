<?php

declare(strict_types=1); // 厳密な型チェック

/**
 * Aho-Corasick オートマトンのノードクラス
 */
class AhoCorasickNode
{
    /** @var array<string, int> 子ノードへの遷移マップ (文字 => ノードインデックス) */
    public array $children = [];

    /** @var int 照合失敗時にジャンプする失敗リンク (Failure Link) のインデックス */
    public int $fail = 0;

    /** @var array<string> このノードに到達した時点で一致判定となるキーワード一覧 */
    public array $output = [];
}

/**
 * Aho-Corasick 多重パターン文字列検索クラス
 */
class AhoCorasick
{
    /** @var array<int, AhoCorasickNode> オートマトンの全ノード配列 */
    private array $nodes = [];

    public function __construct()
    {
        // 根ノード (Index 0) を初期化
        $this->nodes[] = new AhoCorasickNode();
    }

    /**
     * 1. 検索対象キーワードを Trie に登録する (計算量: O(L))
     */
    public function addPattern(string $pattern): void
    {
        $curr = 0;
        $length = strlen($pattern);

        for ($i = 0; $i < $length; $i++) {
            $char = $pattern[$i];

            if (!isset($this->nodes[$curr]->children[$char])) {
                $newNodeIdx = count($this->nodes);
                $this->nodes[$curr]->children[$char] = $newNodeIdx;
                $this->nodes[] = new AhoCorasickNode();
            }

            $curr = $this->nodes[$curr]->children[$char];
        }

        // 単語の終端ノードにヒット情報を記録
        $this->nodes[$curr]->output[] = $pattern;
    }

    /**
     * 2. 幅優先探索 (BFS) で失敗リンク (Failure Link) と出力遷移を構築する (計算量: O(\sum L))
     */
    public function buildFailLinks(): void
    {
        /** @var SplQueue<int> $queue */
        $queue = new SplQueue();

        // 根ノード (0) の直下の子ノードの失敗リンクはすべて 0 に設定してキューに追加
        foreach ($this->nodes[0]->children as $char => $childIdx) {
            $this->nodes[$childIdx]->fail = 0;
            $queue->enqueue($childIdx);
        }

        while (!$queue->isEmpty()) {
            /** @var int $curr */
            $curr = $queue->dequeue();

            foreach ($this->nodes[$curr]->children as $char => $childIdx) {
                $failState = $this->nodes[$curr]->fail;

                // 失敗リンクを辿り、同じ文字遷移が存在する場所を探す
                while ($failState > 0 && !isset($this->nodes[$failState]->children[$char])) {
                    $failState = $this->nodes[$failState]->fail;
                }

                if (isset($this->nodes[$failState]->children[$char])) {
                    $failState = $this->nodes[$failState]->children[$char];
                }

                $this->nodes[$childIdx]->fail = $failState;

                // 失敗先のノードが持つ一致パターン情報もすべて引き継ぐ
                foreach ($this->nodes[$failState]->output as $out) {
                    $this->nodes[$childIdx]->output[] = $out;
                }

                $queue->enqueue($childIdx);
            }
        }
    }

    /**
     * 3. テキスト T を1回走査して全パターンの一致回数を求める (計算量: O(|T|))
     */
    public function search(string $text): int
    {
        $curr = 0;
        $matchCount = 0;
        $length = strlen($text);

        for ($i = 0; $i < $length; $i++) {
            $char = $text[$i];

            // 現在のノードから char で遷移できない場合、失敗リンクを戻る
            while ($curr > 0 && !isset($this->nodes[$curr]->children[$char])) {
                $curr = $this->nodes[$curr]->fail;
            }

            if (isset($this->nodes[$curr]->children[$char])) {
                $curr = $this->nodes[$curr]->children[$char];
            }

            // この状態に到達した時点でマッチしている全キーワードの数を加算
            $matchCount += count($this->nodes[$curr]->output);
        }

        return $matchCount;
    }
}

// --------------------------------------------------
// 1. 標準入力の読み込みと Aho-Corasick 構築
// --------------------------------------------------
$firstLine = fgets(STDIN);
if ($firstLine === false) {
    exit;
}

$k = (int) trim($firstLine);
$ac = new AhoCorasick();

for ($i = 0; $i < $k; $i++) {
    $line = fgets(STDIN);
    if ($line === false) {
        break;
    }
    $ac->addPattern(trim($line));
}

// 失敗リンクの構築
$ac->buildFailLinks();

// 検索対象テキストの読み込み
$textLine = fgets(STDIN);
$text = ($textLine !== false) ? trim($textLine) : '';

// --------------------------------------------------
// 2. 処理実行と出力
// --------------------------------------------------
$totalMatches = $ac->search($text);

echo $totalMatches . "\n";