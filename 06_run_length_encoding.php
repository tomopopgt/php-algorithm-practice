<?php

declare(strict_types=1); // 厳密な型チェック

/**
 * 文字列をランレングス圧縮（文字＋連続数）する
 *
 * @param string $input 圧縮対象の文字列
 * @return string 圧縮後の文字列
 */
function runLengthEncode(string $input): string
{
    $length = strlen($input);
    if ($length === 0) {
        return '';
    }

    $result = '';
    $currentChar = $input[0];
    $count = 1;

    for ($i = 1; $i < $length; $i++) {
        if ($input[$i] === $currentChar) {
            // 同じ文字が続いている場合はカウントアップ
            $count++;
        } else {
            // 別の文字に切り替わったら、ここまでの結果を結合
            $result .= $currentChar . $count;
            // 新しい文字とカウントをリセット
            $currentChar = $input[$i];
            $count = 1;
        }
    }

    // ループ終了後、最後の文字グループを出力に追加する
    $result .= $currentChar . $count;

    return $result;
}

// --------------------------------------------------
// 1. 標準入力の読み込み
// --------------------------------------------------
$line = fgets(STDIN);
if ($line === false) {
    exit;
}

$inputString = trim($line);

// --------------------------------------------------
// 2. 処理実行と出力
// --------------------------------------------------
$compressed = runLengthEncode($inputString);

echo $compressed . "\n";