<?php

declare(strict_types=1); // 厳密な型チェック

/**
 * 予約スケジュールのリストを受け取り、時間の重複があるか判定する
 *
 * @param array<int, array{start: int, end: int}> $schedules
 * @return bool 重複があれば true、なければ false
 */
function hasScheduleConflict(array $schedules): bool
{
    // 1. 開始時刻 (start) の昇順でソートする (計算量: O(N log N))
    usort($schedules, function (array $a, array $b): int {
        return $a['start'] <=> $b['start'];
    });

    $count = count($schedules);
    
    // 2. 隣り合う予約の「前の終了時刻」と「次の開始時刻」を比較
    for ($i = 0; $i < $count - 1; $i++) {
        $currentEnd = $schedules[$i]['end'];
        $nextStart = $schedules[$i + 1]['start'];

        // 次の開始時刻が前の終了時刻より前であれば、重複（コンフリクト）が発生
        if ($nextStart < $currentEnd) {
            return true;
        }
    }

    return false;
}

// --------------------------------------------------
// 1. 標準入力の読み込み
// --------------------------------------------------
$line1 = fgets(STDIN);
if ($line1 === false) {
    exit;
}

$n = (int) trim($line1);
$schedules = [];

for ($i = 0; $i < $n; $i++) {
    $line = fgets(STDIN);
    if ($line === false) {
        break;
    }

    [$startStr, $endStr] = explode(' ', trim($line));
    $schedules[] = [
        'start' => (int) $startStr,
        'end' => (int) $endStr,
    ];
}

// --------------------------------------------------
// 2. 処理実行と出力
// --------------------------------------------------
$hasConflict = hasScheduleConflict($schedules);

echo ($hasConflict ? 'NG' : 'OK') . "\n";