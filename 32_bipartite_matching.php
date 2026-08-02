<?php

declare(strict_types=1); // 厳密な型チェック

/**
 * 増加パス DFS による最大二部マッチング演算クラス
 */
class BipartiteMatching
{
    /** @var array<int, array<int>> 作業者からタスクへの有向隣接リスト */
    private array $graph = [];

    /** @var array<int, int> 各タスクにマッチングされている作業者ID (-1 は未割り当て) */
    private array $matchY = [];

    /** @var array<int, bool> 1回のDFS内の訪問済みフラグ */
    private array $used = [];

    public function __construct(
        private int $sizeX,
        private int $sizeY
    ) {
        // 初期状態では全タスク未割り当て (-1)
        $this->matchY = array_fill(1, $sizeY, -1);
    }

    /**
     * 作業者 u と タスク v の対応関係を追加
     */
    public function addEdge(int $u, int $v): void
    {
        $this->graph[$u][] = $v;
    }

    /**
     * 増加パスを深さ優先探索 (DFS) で探索する
     */
    private function dfs(int $u): bool
    {
        if (!isset($this->graph[$u])) {
            return false;
        }

        foreach ($this->graph[$u] as $v) {
            if ($this->used[$v]) {
                continue;
            }
            $this->used[$v] = true;

            // タスク v が未割り当てか、または v に割り当てられている先客作業者を別タスクへ押し出し移動可能か
            if ($this->matchY[$v] === -1 || $this->dfs($this->matchY[$v])) {
                $this->matchY[$v] = $u;
                return true;
            }
        }

        return false;
    }

    /**
     * 最大マッチング数を算出する (計算量: O(V * E))
     */
    public function solveMaxMatching(): int
    {
        $maxMatching = 0;

        for ($u = 1; $u <= $this->sizeX; $u++) {
            $this->used = array_fill(1, $this->sizeY, false);
            if ($this->dfs($u)) {
                $maxMatching++;
            }
        }

        return $maxMatching;
    }
}

// --------------------------------------------------
// 1. 標準入力の読み込み
// --------------------------------------------------
$firstLine = fgets(STDIN);
if ($firstLine === false) {
    exit;
}

[$xStr, $yStr, $mStr] = explode(' ', trim($firstLine));
$sizeX = (int) $xStr;
$sizeY = (int) $yStr;
$edgeCount = (int) $mStr;

$bm = new BipartiteMatching($sizeX, $sizeY);

for ($i = 0; $i < $edgeCount; $i++) {
    $line = fgets(STDIN);
    if ($line === false) {
        break;
    }

    [$u, $v] = array_map('intval', explode(' ', trim($line)));
    $bm->addEdge($u, $v);
}

// --------------------------------------------------
// 2. 処理実行と出力
// --------------------------------------------------
echo $bm->solveMaxMatching() . "\n";