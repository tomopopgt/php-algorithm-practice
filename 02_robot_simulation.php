<?php

declare(strict_types=1); // 厳密な型チェック

/**
 * ロボットの移動命令を処理し、最終座標 [x, y] を返す
 *
 * @param array<array{direction: string, distance: int}> $commands
 * @return array{0: int, 1: int} [x, y]
 */
function simulateRobotMovement(array $commands): array
{
    $x = 0;
    $y = 0;

    foreach ($commands as $command) {
        $direction = $command['direction'];
        $distance = $command['distance'];

        // match 式を使った安全で直感的な条件分岐
        match ($direction) {
            'N' => $y += $distance,
            'S' => $y -= $distance,
            'E' => $x += $distance,
            'W' => $x -= $distance,
            default => null, // 想定外の値が来てもエラーにならず安全にスルー
        };
    }

    return [$x, $y];
}

// --------------------------------------------------
// 1. 標準入力の読み込みとデータ整形
// --------------------------------------------------
$line1 = fgets(STDIN);
if ($line1 === false) {
    exit;
}

$n = (int) trim($line1);
$commands = [];

for ($i = 0; $i < $n; $i++) {
    $line = fgets(STDIN);
    if ($line === false) {
        break;
    }

    [$dir, $distStr] = explode(' ', trim($line));
    
    // 型を確定させてから配列に格納する
    $commands[] = [
        'direction' => $dir,
        'distance' => (int) $distStr,
    ];
}

// --------------------------------------------------
// 2. 処理実行と出力
// --------------------------------------------------
[$finalX, $finalY] = simulateRobotMovement($commands);

echo "{$finalX} {$finalY}\n";