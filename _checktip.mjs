import { parse, compileScript, compileTemplate } from '@vue/compiler-sfc'
import fs from 'fs'
const files = [
  'resources/js/Components/DailyTipCard.vue',
  'resources/js/Pages/Library/Index.vue',
  'plugins/praxiboost/resources/js/Pages/PraxiBoostIndex.vue',
]
let bad = 0
for (const f of files) {
  const src = fs.readFileSync(f, 'utf8')
  const { descriptor, errors } = parse(src, { filename: f })
  if (errors.length) { console.log('PARSE ERR', f, errors.map(e=>e.message)); bad++; continue }
  try {
    const id = 'x'+Math.random().toString(36).slice(2)
    compileScript(descriptor, { id })
    if (descriptor.template) {
      const r = compileTemplate({ source: descriptor.template.content, filename: f, id })
      if (r.errors.length) { console.log('TPL ERR', f, r.errors); bad++; continue }
    }
    console.log('OK', f)
  } catch (e) { console.log('COMPILE ERR', f, e.message); bad++ }
}
process.exit(bad?1:0)
