import { parse } from '@vue/compiler-sfc'
import fs from 'fs'
const f = 'resources/js/Pages/Candidate/Grimoire.vue'
const src = fs.readFileSync(f, 'utf8')
const { descriptor, errors } = parse(src, { filename: f })
if (errors.length) { console.log('ERREURS:', errors.map(e=>e.message)); process.exit(1) }
console.log('OK — blocs:', ['script','template','styles'].map(k=>{
  const v = descriptor[k]; return Array.isArray(v)?`${k}:${v.length}`:(v?k:`no-${k}`)
}).join(', '))
console.log('lignes:', src.split('\n').length)
