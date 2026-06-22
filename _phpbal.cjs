const fs=require("fs");
for(const f of process.argv.slice(2)){
 const s=fs.readFileSync(f,"utf8");let i=0,n=s.length;
 let d={"(":0,"{":0,"[":0};const pair={")":"(","}":"{","]":"["};let err=null;
 while(i<n){const c=s[i],c2=s[i+1];
  if(c==="/"&&c2==="/"){while(i<n&&s[i]!=="\n")i++;continue}
  if(c==="#"){while(i<n&&s[i]!=="\n")i++;continue}
  if(c==="/"&&c2==="*"){i+=2;while(i<n&&!(s[i]==="*"&&s[i+1]==="/"))i++;i+=2;continue}
  if(c==="'"){i++;while(i<n){if(s[i]==="\\"){i+=2;continue}if(s[i]==="'"){i++;break}i++}continue}
  if(c==='"'){i++;while(i<n){if(s[i]==="\\"){i+=2;continue}if(s[i]==='"'){i++;break}i++}continue}
  if(c==="("||c==="{"||c==="[")d[c]++;
  else if(c===")"||c==="}"||c==="]"){d[pair[c]]--;if(d[pair[c]]<0&&!err)err="extra "+c+" at "+i}
  i++;
 }
 const ok=d["("]===0&&d["{"]===0&&d["["]===0&&!err;
 console.log((ok?"OK   ":"BAD  ")+f,JSON.stringify(d),err||"");
}
