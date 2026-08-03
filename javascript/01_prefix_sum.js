import { readFileSync } from 'fs';

/**
 * 累積和構造体クラス
 */
class PrefixSum {
  /**
   * @param {number[]} array - 元の数値配列 (0-indexed)
   */
  constructor(array) {
    const n = array.length;
    // 1-indexed で扱いやすくするため、要素数 N + 1 の配列を準備
    this.prefix = new Array(n + 1).fill(0);

    for (let i = 0; i < n; i++) {
      this.prefix[i + 1] = this.prefix[i] + array[i];
    }
  }

  /**
   * 区間 [left, right] (1-indexed) の合計値を O(1) で取得
   * @param {number} left
   * @param {number} right
   * @returns {number}
   */
  query(left, right) {
    return this.prefix[right] - this.prefix[left - 1];
  }
}

function main() {
  // 標準入力を一括読み込みして行ごとに分割
  const input = readFileSync(0, 'utf-8').trim().split('\n');
  if (input.length === 0 || input[0] === '') return;

  const [nStr, qStr] = input[0].trim().split(/\s+/);
  const n = Number(nStr);
  const q = Number(qStr);

  const array = input[1].trim().split(/\s+/).map(Number);
  const prefixSum = new PrefixSum(array);

  const results = [];
  for (let i = 0; i < q; i++) {
    const lineIndex = 2 + i;
    if (!input[lineIndex]) break;

    const [left, right] = input[lineIndex].trim().split(/\s+/).map(Number);
    results.push(prefixSum.query(left, right));
  }

  // 出力オーバーヘッドを減らすため一括出力
  console.log(results.join('\n'));
}

main();