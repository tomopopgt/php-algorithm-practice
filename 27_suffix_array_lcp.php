<?php

declare(strict_types=1); // 厳密な型チェック

/**
 * ダブリング法を用いて Suffix Array (接頭辞配列) を構築する
 *
 * @param string $s 対象の文字列
 * @return array<int> 辞書順ソートされた接尾辞の開始インデックス配列
 */
function buildSuffixArray(string $s): array
{
    $n = strlen($s);
    $sa = range(0, $n - 1);
    
    // 各文字の文字コードを初期ランクとする
    $rank = array_map('ord', str_split($s));
    $tmp = array_fill(0, $n, 0);

    // 1, 2, 4, 8... 文字の長さで比較ランクを倍々で更新していく (Doubling)
    for ($k = 1; $k < $n; $k *= 2) {
        $cmp = function (int $i, int $j) use ($rank, $k, $n): int {
            if ($rank[$i] !== $rank[$j]) {
                return $rank[$i] <=> $rank[$j];
            }
            $ri = ($i + $k < $n) ? $rank[$i + $k] : -1;
            $rj = ($j + $k < $n) ? $rank[$j + $k] : -1;
            return $ri <=> $rj;
        };

        usort($sa, $cmp);

        $tmp[$sa[0]] = 0;
        for ($i = 1; $i < $n; $i++) {
            $tmp[$sa[$i]] = $tmp[$sa[$i - 1]] + ($cmp($sa[$i - 1], $sa[$i]) < 0 ? 1 : 0);
        }
        $rank = $tmp;
    }

    return $sa;
}

/**
 * Kasaiのアルゴリズムを用いて LCP (Longest Common Prefix) 配列を構築する (計算量: O(N))
 *
 * @param string $s 対象の文字列
 * @param array<int> $sa Suffix Array
 * @return array<int> 隣り合う接尾辞同士の最長共通接頭辞長配列
 */
function buildLCPArray(string $s, array $sa): array
{
    $n = strlen($s);
    $rank = array_fill(0, $n, 0);

    // Suffix Array の逆引き（インデックス i の接尾辞が SA の何番目にあるか）
    for ($i = 0; $i < $n; $i++) {
        $rank[$sa[$i]] = $i;
    }

    $lcp = array_fill(0, $n - 1, 0);
    $h = 0; // 共通接頭辞の長さ

    for ($i = 0; $i < $n; $i++) {
        if ($rank[$i] > 0) {
            // SA 上で 1 つ前にある接尾辞の開始位置 j
            $j = $sa[$rank[$i] - 1];

            // 共通する文字数をカウント
            while ($i + $h < $n && $j + $h < $n && $s[$i + $h] === $s[$j + $h]) {
                $h++;
            }

            $lcp[$rank[$i] - 1] = $h;

            // 次の文字へ進む際、共通接頭辞長は高々 1 しか減らない性質を利用
            if ($h > 0) {
                $h--;
            }
        }
    }

    return $lcp;
}

// --------------------------------------------------
// 1. 標準入力の読み込み
// --------------------------------------------------
$line = fgets(STDIN);
if ($line === false) {
    exit;
}

$text = trim($line);

if ($text === '') {
    echo "0\n";
    exit;
}

// --------------------------------------------------
// 2. Suffix Array と LCP Array の構築
// --------------------------------------------------
$sa = buildSuffixArray($text);
$lcp = buildLCPArray($text, $sa);

// --------------------------------------------------
// 3. 最長重複部分文字列の長さを算出 (LCP 配列の最大値)
// --------------------------------------------------
$maxLCP = empty($lcp) ? 0 : max($lcp);

echo $maxLCP . "\n";