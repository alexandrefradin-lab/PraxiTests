import { parse } from '@vue/compiler-sfc'
import fs from 'fs'
for (const f of [
  'resources/js/Pages/Library/Show.vue',           // untouched control
  'resources/js/Pages/Library/Index.vue',          // edited
]) {
  const src = fs.readFileSync(f, 'utf8')
  const { errors } = parse(src, { filename: f })
  console.log(f, '=>', errors.length ? errors.map(e=>e.message) : 'OK', '| bytes:', src.length, '| lastchar:', JSON.stringify(src.slice(-12)))
}
