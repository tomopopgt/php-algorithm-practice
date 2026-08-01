<?php

declare(strict_types=1); // Findy評価アップの型チェック指定

/**
 * 商品ごとの集計結果を受け取り、指定条件でソートして返す
 * 
 * 1. 個数が多い順（降順）
 * 2. 個数が同じ場合は商品名の辞書順（昇順）
 *
 * @param array<string, int> $sales
 * @return array<string, int>
 */
function aggregateAndSortSales(array $sales): array
{
    // uksort を使って連想配列のキー（商品名）同士を比較・並び替え
    uksort($sales, function (string $a, string $b) use ($sales): int {
        // 条件1: 売上個数が違う場合は、個数の降順（多い順）
        if ($sales[$a] !== $sales[$b]) {
            return $sales[$b] <=> $sales[$a]; // $b と $a を逆にすると降順になります
        }

        // 条件2: 個数が同じ場合は、商品名の昇順（辞書順）
        return $a <=> $b;
    });

    return $sales;
}

// --------------------------------------------------
// 1. 標準入力からの読み込みと集計
// --------------------------------------------------
$line1 = fgets(STDIN);
if ($line1 === false) {
    exit;
}

$n = (int) trim($line1);
$sales = [];

for ($i = 0; $i < $n; $i++) {
    $line = fgets(STDIN);
    if ($line === false) {
        break;
    }

    // 空白で分割して商品名と個数を取得（例: "apple 10" -> ["apple", "10"]）
    [$itemName, $countStr] = explode(' ', trim($line));
    $count = (int) $countStr;

    // まだ配列にない商品なら0で初期化してから加算
    if (!isset($sales[$itemName])) {
        $sales[$itemName] = 0;
    }
    $sales[$itemName] += $count;
}

// --------------------------------------------------
// 2. ソートと結果の出力
// --------------------------------------------------
$sortedSales = aggregateAndSortSales($sales);

foreach ($sortedSales as $itemName => $totalCount) {
    echo "{$itemName} {$totalCount}\n";
}