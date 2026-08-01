<?php

declare(strict_types=1); // 厳密な型チェック

/**
 * グリッドマップの空きマスに対して、上下左右に隣接する爆弾の数をカウントして置換する
 *
 * @param array<int, string> $grid マップの各行を表す文字列配列
 * @param int $height 縦のサイズ H
 * @param int $width 横のサイズ W
 * @return array<int, string> 変換後のマップ
 */
function processBombGrid(array $grid, int $height, int $width): array
{
    // 上下左右の移動量を表す方向ベクター [dy, dx]
    $directions = [
        [-1, 0], // 上
        [1, 0],  // 下
        [0, -1], // 左
        [0, 1],  // 右
    ];

    $resultGrid = [];

    for ($y = 0; $y < $height; $y++) {
        $rowChars = str_split($grid[$y]);
        $newRow = '';

        for ($x = 0; $x < $width; $x++) {
            // すでに爆弾マス（#）の場合はそのまま追加してスキップ
            if ($rowChars[$x] === '#') {
                $newRow .= '#';
                continue;
            }

            // 空きマス（.）の場合は上下左右の爆弾数をカウント
            $bombCount = 0;
            foreach ($directions as [$dy, $dx]) {
                $ny = $y + $dy; // 移動後の y 座標
                $nx = $x + $dx; // 移動後の x 座標

                // グリッドの範囲内（マップの外に出ていないか）をチェック
                if ($ny >= 0 && $ny < $height && $nx >= 0 && $nx < $width) {
                    if ($grid[$ny][$nx] === '#') {
                        $bombCount++;
                    }
                }
            }

            $newRow .= (string) $bombCount;
        }

        $resultGrid[] = $newRow;
    }

    return $resultGrid;
}

// --------------------------------------------------
// 1. 標準入力の読み込み
// --------------------------------------------------
$firstLine = fgets(STDIN);
if ($firstLine === false) {
    exit;
}

[$heightStr, $widthStr] = explode(' ', trim($firstLine));
$height = (int) $heightStr;
$width = (int) $widthStr;

$grid = [];
for ($i = 0; $i < $height; $i++) {
    $line = fgets(STDIN);
    if ($line === false) {
        break;
    }
    $grid[] = trim($line);
}

// --------------------------------------------------
// 2. 処理実行と出力
// --------------------------------------------------
$processedGrid = processBombGrid($grid, $height, $width);

foreach ($processedGrid as $row) {
    echo $row . "\n";
}