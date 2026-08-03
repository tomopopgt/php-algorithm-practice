import { readFileSync } from 'fs';

class PrefixSum {
  constructor(array) {
    const n = array.length;
    this.prefix = new Array(n + 1).fill(0);
    for (let i = 0; i < n; i++) {
      this.prefix[i + 1] = this.prefix[i] + array[i];
    }
  }

  query(left, right) {
    return this.prefix[right] - this.prefix[left - 1];
  }
}

function main() {
  const input = readFileSync(0, 'utf-8').trim().split('\n');
  if (input.length === 0 || input[0] === '') return;

  const [n, q] = input[0].trim().split(/\s+/).map(Number);
  const array = input[1].trim().split(/\s+/).map(Number);
  const prefixSum = new PrefixSum(array);

  const results = [];
  for (let i = 0; i < q; i++) {
    const lineIndex = 2 + i;
    if (!input[lineIndex]) break;
    const [left, right] = input[lineIndex].trim().split(/\s+/).map(Number);
    results.push(prefixSum.query(left, right));
  }

  console.log(results.join('\n'));
}

main();