<?php

declare(strict_types=1); // 厳密な型チェック

/**
 * 複素数を扱うクラス
 */
class Complex
{
    public function __construct(
        public float $real = 0.0,
        public float $imag = 0.0
    ) {}

    public function add(Complex $other): Complex
    {
        return new Complex($this->real + $other->real, $this->imag + $other->imag);
    }

    public function sub(Complex $other): Complex
    {
        return new Complex($this->real - $other->real, $this->imag - $other->imag);
    }

    public function mul(Complex $other): Complex
    {
        return new Complex(
            $this->real * $other->real - $this->imag * $other->imag,
            $this->real * $other->imag + $this->imag * $other->real
        );
    }
}

/**
 * Cooley-Tukey アルゴリズムによる高速フーリエ変換 (FFT / IFFT)
 *
 * @param array<Complex> $a 変換対象の複素数配列 (要素数は 2 の累乗)
 * @param bool $invert true の場合は逆変換 (IFFT)
 */
function fft(array &$a, bool $invert): void
{
    $n = count($a);
    if ($n <= 1) {
        return;
    }

    // 1. Bit-Reversal (ビット反転置換) による非再帰バタフライ演算の準備
    for ($i = 1, $j = 0; $i < $n; $i++) {
        $bit = $n >> 1;
        for (; ($j & $bit) !== 0; $bit >>= 1) {
            $j ^= $bit;
        }
        $j ^= $bit;

        if ($i < $j) {
            [$a[$i], $a[$j]] = [$a[$j], $a[$i]];
        }
    }

    // 2. バタフライ演算 (計算量: O(N log N))
    for ($len = 2; $len <= $n; $len <<= 1) {
        $ang = 2 * M_PI / $len * ($invert ? -1 : 1);
        $wlen = new Complex(cos($ang), sin($ang));

        for ($i = 0; $i < $n; $i += $len) {
            $w = new Complex(1.0, 0.0);
            $half = intdiv($len, 2);

            for ($j = 0; $j < $half; $j++) {
                $u = $a[$i + $j];
                $v = $a[$i + $j + $half]->mul($w);

                $a[$i + $j] = $u->add($v);
                $a[$i + $j + $half] = $u->sub($v);

                $w = $w->mul($wlen);
            }
        }
    }

    // 3. IFFT の場合は要素数 N で割って正規化
    if ($invert) {
        for ($i = 0; $i < $n; $i++) {
            $a[$i]->real /= $n;
            $a[$i]->imag /= $n;
        }
    }
}

/**
 * 高速フーリエ変換を用いた多項式乗算 (畳み込み / Convolution)
 *
 * @param array<int> $a 多項式 A の係数配列
 * @param array<int> $b 多項式 B の係数配列
 * @return array<int> 積多項式 C の係数配列
 */
function multiplyPolynomials(array $a, array $b): array
{
    $neededSize = count($a) + count($b) - 1;

    // サイズを 2 の累乗に切り上げる
    $n = 1;
    while ($n < $neededSize) {
        $n <<= 1;
    }

    /** @var array<Complex> $fa */
    $fa = [];
    /** @var array<Complex> $fb */
    $fb = [];

    for ($i = 0; $i < $n; $i++) {
        $fa[] = new Complex((float) ($a[$i] ?? 0));
        $fb[] = new Complex((float) ($b[$i] ?? 0));
    }

    // 1. 時間領域 -> 周波数領域 (FFT)
    fft($fa, false);
    fft($fb, false);

    // 2. 周波数領域での点ごとの積 (Point-wise multiplication)
    /** @var array<Complex> $fc */
    $fc = [];
    for ($i = 0; $i < $n; $i++) {
        $fc[] = $fa[$i]->mul($fb[$i]);
    }

    // 3. 周波数領域 -> 時間領域 (IFFT)
    fft($fc, true);

    // 4. 実数部を丸めて整数係数に復元
    $result = [];
    for ($i = 0; $i < $neededSize; $i++) {
        $result[] = (int) round($fc[$i]->real);
    }

    return $result;
}

// --------------------------------------------------
// 1. 標準入力の読み込み
// --------------------------------------------------
$firstLine = fgets(STDIN);
if ($firstLine === false) {
    exit;
}

$n = (int) trim($firstLine);

$secondLine = fgets(STDIN);
$polyA = array_map('intval', explode(' ', trim($secondLine ?? '')));

$thirdLine = fgets(STDIN);
$polyB = array_map('intval', explode(' ', trim($thirdLine ?? '')));

// --------------------------------------------------
// 2. 処理実行と出力 (計算量: O(N log N))
// --------------------------------------------------
$resultPoly = multiplyPolynomials($polyA, $polyB);

echo implode(' ', $resultPoly) . "\n";