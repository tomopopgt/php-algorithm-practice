<?php

declare(strict_types=1); // 強い型付けを有効化（Findy評価アップの秘訣！）

/**
 * 与えられた整数に1を加算して返す
 *
 * @param int $number 入力される整数
 * @return int 1を加算した結果
 */
function incrementNumber(int $number): int
{
    return $number + 1;
}

// --------------------------------------------------
// 実行部分（標準入力の受け取りと出力）
// --------------------------------------------------
$input = trim(fgets(STDIN));

if ($input !== '') {
    $n = intval($input);
    // 関数を呼び出して結果を出力
    echo incrementNumber($n) . "\n";
}