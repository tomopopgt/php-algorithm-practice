<?php

declare(strict_types=1); // 厳密な型チェック

/**
 * Manacherのアルゴリズムを用いて最長回文の長さを求める (計算量: O(N))
 *
 * @param string $s 対象の文字列
 * @return int 最長回文の長さ
 */
function getLongestPalindromeLength(string $s): int
{
    $n = strlen($s);
    if ($n === 0) {
        return 0;
    }

    // 奇数長・偶数長を統一処理するため、文字間にダミー文字 '#' を挿入
    $t = '#' . implode('#', str_split($s)) . '#';
    $len = strlen($t);

    /** @var array<int> 各インデックスを中心とする回文の半径 */
    $r = array_fill(0, $len, 0);

    $c = 0;         // 現在最も右に伸びている回文の中心インデックス
    $rBoundary = 0; // 現在最も右に伸びている回文の右端インデックス

    for ($i = 0; $i < $len; $i++) {
        if ($i < $rBoundary) {
            // 中心 $c に関する $i の対称点 $iMirror の計算結果を利用
            $iMirror = 2 * $c - $i;
            $r[$i] = min($rBoundary - $i, $r[$iMirror]);
        } else {
            $r[$i] = 0;
        }

        // 中心 $i$ から回文を左右に一文字ずつ伸ばす
        while (
            $i - $r[$i] - 1 >= 0 &&
            $i + $r[$i] + 1 < $len &&
            $t[$i - $r[$i] - 1] === $t[$i + $r[$i] + 1]
        ) {
            $r[$i]++;
        }

        // 右端の位置が更新されたら、中心と右端を更新
        if ($i + $r[$i] > $rBoundary) {
            $c = $i;
            $rBoundary = $i + $r[$i];
        }
    }

    // ダミー文字を挟んだ文字列での「最大半径 max($r)」が、元の文字列での「最長回文長」と一致する
    return max($r);
}

// --------------------------------------------------
// 1. 標準入力の読み込み
// --------------------------------------------------
$line = fgets(STDIN);
$text = ($line !== false) ? trim($line) : '';

// --------------------------------------------------
// 2. 処理実行と出力
// --------------------------------------------------
$result = getLongestPalindromeLength($text);

echo $result . "\n";