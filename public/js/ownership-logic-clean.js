const entities = DATA.entities, edges = DATA.edges, changes = DATA.changes, audits = DATA.audits, investorSummaries = DATA.investorSummaries, stats = DATA.stats;
const entityArr = Object.values(entities);
let selectedKey = (entities['E:BRPT'] ? 'E:BRPT' : (stats.defaultKey || Object.keys(entities)[0]));
var NET=(window.__NET=window.__NET||{k:1,tx:0,ty:0,nodes:[],links:[],wired:false,pointers:new Map(),raf:null});
let minPct = 1, depth = 2, directionMode = 'both';
const outEdges = {}, inEdges = {}, changeByFrom = {}, changeByTo = {};
for (const e of edges){ (outEdges[e.from] ||= []).push(e); (inEdges[e.to] ||= []).push(e); }
for (const c of changes){ (changeByFrom[c.from] ||= []).push(c); (changeByTo[c.to] ||= []).push(c); }
for (const k in outEdges) outEdges[k].sort((a,b)=>b.pct-a.pct);
for (const k in inEdges) inEdges[k].sort((a,b)=>b.pct-a.pct);
function fmtPct(x){return (x==null||isNaN(x))?'-':`${(+x).toFixed(2)}%`}
function fmtBps(x){return (x==null||isNaN(x))?'-':`${x>0?'+':''}${(+x).toFixed(2)} ppt`}
function fmtShares(n){ n=+n||0; const s=n<0?'-':''; n=Math.abs(n); if(n>=1e12)return s+(n/1e12).toFixed(2)+'T'; if(n>=1e9)return s+(n/1e9).toFixed(2)+'B'; if(n>=1e6)return s+(n/1e6).toFixed(2)+'M'; if(n>=1e3)return s+(n/1e3).toFixed(1)+'K'; return s+Math.round(n).toLocaleString('id-ID') }
function ent(k){return entities[k] || {key:k,label:k,kind:'unknown'}}
function shortLabel(s,n=24){ s=s||''; return s.length>n?s.slice(0,n-1)+'…':s }
function edgeClass(e){return e.direction==='BUY'?'buy':e.direction==='SELL'?'sell':e.direction==='NEW'?'new':e.direction==='EXIT'?'exit':'unchanged'}
function dirTag(d){const cls=d==='BUY'?'buy':d==='SELL'?'sell':d==='NEW'?'new':d==='EXIT'?'exit':''; return `<span class="tag ${cls}">${d}</span>`}
function setSelected(k){ if(!entities[k]) return; selectedKey=k; renderAll(); }
function initSearch(){
  const dl=document.getElementById('entityList'); dl.innerHTML = entityArr.map(e=>`<option value="${(e.ticker?e.ticker+' - ':'')+e.label.replace(/"/g,'&quot;')}"></option>`).join('');
  document.getElementById('goSearch').onclick=()=>{
    const q=document.getElementById('globalSearch').value.trim().toUpperCase();
    if(!q) return;
    const hit = entityArr.find(e=>e.ticker===q) || entityArr.find(e=>(e.label||'').toUpperCase().includes(q)) || entityArr.find(e=>(e.norm||'').includes(q.replace(/[^A-Z0-9]+/g,' ')));
    if(hit) setSelected(hit.key);
  };
  document.getElementById('globalSearch').addEventListener('keydown',e=>{if(e.key==='Enter')document.getElementById('goSearch').click()});
}
function initControls(){
  document.getElementById('sideDate').textContent=stats.latestDate;
  document.getElementById('sidePrev').textContent=stats.prevDate;
  document.getElementById('sideIssuers').textContent=stats.issuersLatest;
  document.getElementById('depthSel').onchange=e=>{depth=+e.target.value;renderAll()};
  document.getElementById('minPctSel').onchange=e=>{minPct=+e.target.value;renderAll()};
  document.getElementById('dirSel').onchange=e=>{directionMode=e.target.value;renderAll()};
  document.getElementById('resetBtn').onclick=()=>setSelected('E:BRPT');
  document.getElementById('fitBtn').onclick=()=>renderNetwork();
  document.getElementById('exportCsv').onclick=exportChanges;
  document.querySelectorAll('.tabbar button').forEach(btn=>btn.onclick=()=>{document.querySelectorAll('.tabbar button').forEach(b=>b.classList.remove('on'));btn.classList.add('on');document.querySelectorAll('.tabpane').forEach(p=>p.classList.remove('on'));document.getElementById(btn.dataset.tab).classList.add('on')});
  const chips=['E:BREN','E:BRPT','I:PRAJOGO PANGESTU','E:TPIA','E:CDIA','E:ADRO','I:LO KHENG HONG','E:BBRI'];
  document.getElementById('quickChips').innerHTML=chips.filter(k=>entities[k]).map(k=>`<span class="chip" data-key="${k}">${entities[k].ticker||shortLabel(entities[k].label,18)}</span>`).join('');
  document.querySelectorAll('.chip[data-key]').forEach(c=>c.onclick=()=>setSelected(c.dataset.key));
}
function renderKpis(){
  const e=ent(selectedKey); document.getElementById('selectedDisplay').value=(e.ticker?e.ticker+' - ':'')+e.label;
  document.querySelectorAll('.chip[data-key]').forEach(c=>c.classList.toggle('on',c.dataset.key===selectedKey));
  const audit=audits[selectedKey], inv=investorSummaries[selectedKey];
  const ownerCount=(inEdges[selectedKey]||[]).length, holdCount=(outEdges[selectedKey]||[]).length;
  const relatedChanges=(changes.filter(c=>c.from===selectedKey||c.to===selectedKey));
  const buy=relatedChanges.filter(c=>c.deltaShares>0).reduce((a,c)=>a+c.deltaShares,0);
  const sell=-relatedChanges.filter(c=>c.deltaShares<0).reduce((a,c)=>a+c.deltaShares,0);
  const kpis=[];
  if(e.kind==='issuer'){
    kpis.push(['Audit confidence', audit?audit.confidence+' / 100':'-', audit?'Issuer-level data quality':'No issuer audit']);
    kpis.push(['Control concentration', audit?fmtPct(audit.top1):'-', audit?audit.controlLabel:'']);
    kpis.push(['Residual proxy', audit?fmtPct(audit.residual):'-', '100% - reported coverage']);
    kpis.push(['Nakamoto-50', audit&&audit.nakamoto50?audit.nakamoto50+' holders':'-', 'Minimum holders to reach 50%']);
    kpis.push(['Upstream owners', ownerCount, 'Direct owners in latest file']);
    kpis.push(['Downstream holdings', holdCount, 'Issuer as investor elsewhere']);
  } else {
    kpis.push(['Direct holdings', holdCount, 'Issuers held in latest file']);
    kpis.push(['Ownership points', inv?fmtPct(inv.ownershipPoints):'-', 'Sum of direct reported stakes']);
    kpis.push(['Largest position', inv?`${inv.largestTicker} ${fmtPct(inv.largestPct)}`:'-', inv?inv.largestIssuer:'']);
    kpis.push(['Bought / increased', fmtShares(buy), 'Latest vs previous']);
    kpis.push(['Sold / reduced', fmtShares(sell), 'Latest vs previous']);
    kpis.push(['Upstream owners', ownerCount, 'Owners if this is listed/mapped']);
  }
  document.getElementById('kpiGrid').innerHTML=kpis.map(([l,n,nt])=>`<div class="kpi"><div class="label">${l}</div><div class="num">${n}</div><div class="note">${nt}</div></div>`).join('');
}
function filteredEdges(list){return (list||[]).filter(e=>e.pct>=minPct)}
function collectSubgraph(){
  const nodes=new Map(); const eds=[]; const level={};
  nodes.set(selectedKey, ent(selectedKey)); level[selectedKey]=0;
  const q=[{key:selectedKey,lvl:0,d:0}]; const seen=new Set([selectedKey+':0']);
  while(q.length){
    const cur=q.shift();
    if(cur.d>=depth) continue;
    let candidates=[];
    // Foto-style: pusat memunculkan owner (atas) + holdings (bawah). Node lain hanya lanjut ke ownernya (atas), supaya holding-lain milik owner tidak ikut.
    const expandDown = (directionMode!=='up') && (cur.d===0);
    if(directionMode!=='down') candidates = candidates.concat(filteredEdges(inEdges[cur.key]).slice(0,8).map(e=>({edge:e,next:e.from,lvl:cur.lvl-1})));
    if(expandDown) candidates = candidates.concat(filteredEdges(outEdges[cur.key]).slice(0,8).map(e=>({edge:e,next:e.to,lvl:cur.lvl+1})));
    for(const x of candidates){
      const key=x.next; nodes.set(key, ent(key)); eds.push(x.edge);
      if(level[key]===undefined || Math.abs(x.lvl)<Math.abs(level[key])) level[key]=x.lvl;
      const state=key+':'+x.lvl;
      if(!seen.has(state)){seen.add(state); q.push({key,lvl:x.lvl,d:cur.d+1})}
    }
  }
  return {nodes:[...nodes.values()], edges:[...new Map(eds.map(e=>[e.from+'>'+e.to,e])).values()], level};
}
function deoverlapLabels(nodes,cx,cy){
  function box(n){ let t=(n.role==='center'?(n.label||''):(n.ticker||n.label||'')); if(t.length>16)t=t.slice(0,16);
    const w=Math.max(n.r*2+6, t.length*7.0+12); const lines=1+((n.pct!=null&&n.role!=='center')?1:0); const h=n.r*2+14+lines*14; return {w,h}; }
  const B=nodes.map(box);
  for(let it=0; it<170; it++){
    for(let i=0;i<nodes.length;i++) for(let j=i+1;j<nodes.length;j++){
      const a=nodes[i],b=nodes[j],ba=B[i],bb=B[j];
      const acy=a.y+(ba.h/2-a.r), bcy=b.y+(bb.h/2-b.r);
      const dx=b.x-a.x, dy=bcy-acy;
      const ox=(ba.w+bb.w)/2-Math.abs(dx), oy=(ba.h+bb.h)/2-Math.abs(dy);
      if(ox>0&&oy>0){
        if(ox<oy){ const s=(dx<0?-1:1)*ox*0.25; if(a.fx==null)a.x-=s; if(b.fx==null)b.x+=s; }
        else { const s=(dy<0?-1:1)*oy*0.16; if(a.fy==null)a.y-=s; if(b.fy==null)b.y+=s; }
      }
    }
    for(const n of nodes){ if(n.fx==null) n.x+=(cx-n.x)*0.004; if(n.fy==null) n.y+=(cy-n.y)*0.004; }
  }
}
function renderNetwork(){
  const svg=document.getElementById('networkSvg');
  const sub=collectSubgraph(); const eds=sub.edges, level=sub.level;
  const W=svg.clientWidth||900, H=svg.clientHeight||560;
  svg.setAttribute('viewBox',`0 0 ${W} ${H}`);
  const cx=W/2, cy=H/2;
  const PURPLE='#9b6cff', RED='#ff5c67', TEAL='#1fb8a6', BRAND='#b07cff';
  function nodeLF(key){ for(const e of eds){ if(e.from===key) return e.local_foreign; } return null; }
  function pctInto(key){ let best=null; for(const e of eds){ if(e.to===key||e.from===key){ if(best==null||e.pct>best) best=e.pct; } } return best; }
  const nodes=[], idx={};
  sub.nodes.forEach(n=>{
    const key=n.key, lvl=level[key]||0, isSel=key===selectedKey, lf=nodeLF(key), isIssuer=(n.kind==='issuer'), held=lvl>0;
    let role,color,stroke,r,pct=null;
    if(isSel){ role='center'; color=BRAND; stroke='#e2d2ff'; r=34; }
    else if(held && isIssuer){ role='holding'; color='#13413a'; stroke=TEAL; r=22; pct=pctInto(key); }
    else { role='owner'; pct=pctInto(key);
      if(lf==='F'){ color='#46161c'; stroke=RED; } else { color='#291a4d'; stroke=PURPLE; }
      r=Math.max(9, Math.min(22, 9+Math.sqrt(pct||1)*2.2)); }
    const nd={key,label:n.label,ticker:n.ticker,kind:n.kind,lf,role,color,stroke,r,pct,lvl,
      x:cx+(Math.random()-0.5)*70, y:cy+lvl*120+(Math.random()-0.5)*40, vx:0,vy:0,fx:null,fy:null};
    if(isSel){ nd.fx=cx; nd.fy=cy; }
    idx[key]=nd; nodes.push(nd);
  });
  const links=eds.filter(e=>idx[e.from]&&idx[e.to]).map(e=>({s:idx[e.from],t:idx[e.to],pct:e.pct,dir:e.direction}));
  const linkDist=l=>(l.s.role==='center'||l.t.role==='center')?175:135;
  function tick(){
    for(let i=0;i<nodes.length;i++)for(let j=i+1;j<nodes.length;j++){
      const a=nodes[i],b=nodes[j]; let dx=b.x-a.x,dy=b.y-a.y,d2=dx*dx+dy*dy||0.01,d=Math.sqrt(d2);
      const fo=4400/d2, fx=dx/d*fo, fy=dy/d*fo; a.vx-=fx;a.vy-=fy;b.vx+=fx;b.vy+=fy;
      const md=a.r+b.r+32; if(d<md){ const p=(md-d)/d*0.5; a.vx-=dx*p;a.vy-=dy*p;b.vx+=dx*p;b.vy+=dy*p; } }
    for(const l of links){ const a=l.s,b=l.t; let dx=b.x-a.x,dy=b.y-a.y,d=Math.sqrt(dx*dx+dy*dy)||0.01; const k=(d-linkDist(l))/d*0.08,fx=dx*k,fy=dy*k; a.vx+=fx;a.vy+=fy;b.vx-=fx;b.vy-=fy; }
    for(const n of nodes){ n.vx+=(cx-n.x)*0.006; n.vy+=((cy+(n.lvl||0)*125)-n.y)*0.02; }
    for(const n of nodes){ if(n.fx!=null){n.x=n.fx;n.vx=0}else{n.vx*=0.84;n.x+=n.vx} if(n.fy!=null){n.y=n.fy;n.vy=0}else{n.vy*=0.84;n.y+=n.vy} }
  }
  for(let i=0;i<460;i++) tick();
  deoverlapLabels(nodes,cx,cy);
  NET.nodes=nodes; NET.links=links; NET.tick=tick; NET.W=W; NET.H=H; NET.cx=cx; NET.cy=cy;
  nodes.forEach(n=>{ n._main = n.role==='center'?shortLabel(n.label,22):(n.role==='holding'?(n.ticker||shortLabel(n.label,12)):shortLabel(n.ticker||n.label,15)); n._pct=(n.pct!=null&&n.role!=='center')?n.pct.toFixed(2)+'%':''; n.lox=0; n.loy=n.r+16; });
  layoutLabels(); fitNetwork(); drawNetwork(); wireNetwork(svg);
  const lm=document.getElementById('netLegendMeta'); if(lm) lm.textContent=nodes.length+' nodes · '+links.length+' links';
  const meta=document.getElementById('netMeta'); if(meta) meta.textContent=nodes.length+' nodes · '+links.length+' links · depth '+depth;
}
function applyTransform(){ const g=document.getElementById('netRoot'); if(g) g.setAttribute('transform',`translate(${NET.tx},${NET.ty}) scale(${NET.k})`); }
function fitNetwork(){ const ns=NET.nodes; if(!ns||!ns.length)return; let a=1e9,b=1e9,c=-1e9,d=-1e9;
  for(const n of ns){ a=Math.min(a,n.x-n.r-30); b=Math.min(b,n.y-n.r-34); c=Math.max(c,n.x+n.r+30); d=Math.max(d,n.y+n.r+30);
    if(n._main){ const w=((n._main||'').length)*3.7+12, lx=n.x+(n.lox||0), ly=n.y+(n.loy!=null?n.loy:n.r+16); a=Math.min(a,lx-w); b=Math.min(b,ly-18); c=Math.max(c,lx+w); d=Math.max(d,ly+(n._pct?24:8)); } }
  const bw=c-a,bh=d-b,k=Math.min(NET.W/bw,NET.H/bh,1.5); NET.k=k; NET.tx=(NET.W-k*(a+c))/2; NET.ty=(NET.H-k*(b+d))/2; applyTransform(); }
function labelW(n){ const tl=(n._main||'').length, pl=(n._pct||'').length; return Math.max(tl,pl)*6.6+10; }
function labelBox(n){ const w=labelW(n), h=(n._pct?28:16); const cx=n.x+(n.lox||0), cy=n.y+(n.loy!=null?n.loy:n.r+16)+(n._pct?5:-2); return {w,h,cx,cy}; }
function layoutLabels(){ const ns=NET.nodes; if(!ns) return;
  for(const n of ns){ if(n.lox==null) n.lox=0; if(n.loy==null) n.loy=n.r+16; }
  for(let it=0; it<90; it++){
    for(let i=0;i<ns.length;i++){ const a=ns[i]; const A=labelBox(a);
      a.lox += (0 - a.lox)*0.05; a.loy += ((a.r+16) - a.loy)*0.05;
      for(let j=0;j<ns.length;j++){ if(i===j) continue; const b=ns[j]; const B=labelBox(b);
        const dx=A.cx-B.cx, dy=A.cy-B.cy; const ox=(A.w+B.w)/2-Math.abs(dx), oy=(A.h+B.h)/2-Math.abs(dy);
        if(ox>0&&oy>0){ if(oy<=ox){ a.loy += (dy>=0?1:-1)*oy*0.5; } else { a.lox += (dx>=0?1:-1)*ox*0.5; } } }
      for(const m of ns){ if(m===a) continue; const dx=A.cx-m.x, dy=A.cy-m.y, dd=Math.hypot(dx,dy)||0.01, min=m.r+A.h/2+6; if(dd<min){ const p=(min-dd)/dd; a.lox+=dx*p*0.5; a.loy+=dy*p*0.5; } }
      a.lox=Math.max(-110,Math.min(110,a.lox)); a.loy=Math.max(-(a.r+40),Math.min(a.r+60,a.loy));
    }
  }
}
function paintNetwork(){ const svg=document.getElementById('networkSvg');
  const linkSvg=NET.links.map((l,i)=>{ const cls=l.dir==='NEW'?'lnew':l.dir==='EXIT'?'lexit':l.dir==='BUY'?'lbuy':l.dir==='SELL'?'lsell':''; return `<line class="netlink ${cls}" data-i="${i}"></line>`; }).join('');
  const nodeSvg=NET.nodes.map(n=>{ const inside=(n.role==='holding')?`<text class="ini" y="4">${(n.ticker||'').slice(0,4)}</text>`:''; return `<g class="netnode ${n.role}${n.role==='center'?' isCenter':''}" data-key="${n.key}"><circle r="${n.r}" fill="${n.color}" stroke="${n.stroke}" stroke-width="${n.role==='center'?3:2}"></circle>${inside}</g>`; }).join('');
  const labSvg=NET.nodes.map(n=>{ const pctEl=n._pct?`<text class="pctlbl" y="13">${n._pct}</text>`:''; return `<g class="nlabel ${n.role}" data-key="${n.key}"><line class="nlead"></line><text class="ttl" y="0">${n._main||''}</text>${pctEl}</g>`; }).join('');
  svg.innerHTML=`<g id="netRoot"><g class="netlinks">${linkSvg}</g><g class="netnodes">${nodeSvg}</g><g class="netlabels">${labSvg}</g></g>`;
  NET.linkEls=[...svg.querySelectorAll('.netlink')]; NET.nodeEls={}; NET.labEls={}; NET.adj={};
  svg.querySelectorAll('.netnode').forEach(g=>{ NET.nodeEls[g.dataset.key]=g; });
  svg.querySelectorAll('.nlabel').forEach(g=>{ NET.labEls[g.dataset.key]=g; });
  NET.links.forEach(l=>{ (NET.adj[l.s.key]||(NET.adj[l.s.key]=new Set())).add(l.t.key); (NET.adj[l.t.key]||(NET.adj[l.t.key]=new Set())).add(l.s.key); });
  syncPositions(); applyTransform();
}
function syncPositions(){ if(!NET.linkEls) return;
  for(let i=0;i<NET.links.length;i++){ const l=NET.links[i],e=NET.linkEls[i]; if(e){ e.setAttribute('x1',l.s.x);e.setAttribute('y1',l.s.y);e.setAttribute('x2',l.t.x);e.setAttribute('y2',l.t.y);} }
  for(const n of NET.nodes){ const g=NET.nodeEls[n.key]; if(g) g.setAttribute('transform',`translate(${n.x},${n.y})`);
    const lg=NET.labEls?NET.labEls[n.key]:null; if(lg){ const lx=n.x+(n.lox||0), ly=n.y+(n.loy!=null?n.loy:n.r+16); lg.setAttribute('transform',`translate(${lx},${ly})`);
      const ld=lg.querySelector('.nlead'); if(ld){ const far=Math.abs(n.lox||0)>16||(n.loy||0)<n.r+6; if(far){ ld.setAttribute('x1',n.x-lx); ld.setAttribute('y1',n.y-ly); ld.setAttribute('x2',0); ld.setAttribute('y2',-5); ld.style.display=''; } else ld.style.display='none'; } } }
}
function drawNetwork(){ paintNetwork(); }
function relax(frames){ if(NET.raf) cancelAnimationFrame(NET.raf); let c=0; const step=()=>{ for(let i=0;i<2;i++) NET.tick(); syncPositions(); if(++c<frames){NET.raf=requestAnimationFrame(step);}else{NET.raf=null;} }; NET.raf=requestAnimationFrame(step); }
function reheat(){ relax(45); }
function roleText(n){ return n.role==='center'?'Subjek':n.role==='holding'?'Holding (dimiliki subjek)':(n.lf==='F'?'Pemilik · Asing':'Pemilik · Domestik'); }
function tipHtml(n){ return `<b>${n.label||n.key}</b>${n.ticker?` · ${n.ticker}`:''}<br><span class="rt">${roleText(n)}</span>${n.pct!=null?` · <b>${n.pct.toFixed(2)}%</b>`:''}`; }
function netHighlight(key){ const adj=NET.adj[key]||new Set();
  for(const k in NET.nodeEls){ const on=(k===key||adj.has(k)); NET.nodeEls[k].classList.toggle('dim',!on); if(NET.labEls[k]) NET.labEls[k].classList.toggle('dim',!on); }
  for(let i=0;i<NET.linkEls.length;i++){ const l=NET.links[i],on=(l.s.key===key||l.t.key===key); NET.linkEls[i].classList.toggle('dim',!on); NET.linkEls[i].classList.toggle('hot',on); }
  const g=NET.nodeEls[key]; if(g) g.classList.add('hot'); }
function netClearHL(){ for(const k in NET.nodeEls){ NET.nodeEls[k].classList.remove('dim','hot'); if(NET.labEls[k]) NET.labEls[k].classList.remove('dim'); } for(const e of (NET.linkEls||[])) e.classList.remove('dim','hot'); }
function wireNetwork(svg){ if(NET.wired) return; NET.wired=true;
  const wrap=svg.closest('.networkWrap')||svg.parentElement;
  let tip=document.getElementById('netTip'); if(!tip){ tip=document.createElement('div'); tip.id='netTip'; wrap.appendChild(tip); }
  const rectPt=ev=>{ const r=svg.getBoundingClientRect(); return {x:ev.clientX-r.left,y:ev.clientY-r.top}; };
  const toGraph=p=>({x:(p.x-NET.tx)/NET.k,y:(p.y-NET.ty)/NET.k});
  let mode=null,moved=0,dragNode=null,last=null,downKey=null;
  svg.addEventListener('pointerdown',ev=>{ try{svg.setPointerCapture(ev.pointerId);}catch(e){} NET.pointers.set(ev.pointerId,rectPt(ev)); tip.style.display='none';
    if(NET.pointers.size===2){ mode='pinch'; const ps=[...NET.pointers.values()]; NET.pinch={d:Math.hypot(ps[0].x-ps[1].x,ps[0].y-ps[1].y),k:NET.k,cx:(ps[0].x+ps[1].x)/2,cy:(ps[0].y+ps[1].y)/2,tx:NET.tx,ty:NET.ty}; return; }
    const g=ev.target.closest('.netnode'); moved=0; last=rectPt(ev);
    if(g){ mode='node'; downKey=g.dataset.key; dragNode=NET.nodes.find(n=>n.key===downKey); } else { mode='pan'; } wrap.style.cursor='grabbing'; });
  svg.addEventListener('pointermove',ev=>{ if(!NET.pointers.has(ev.pointerId)) return; NET.pointers.set(ev.pointerId,rectPt(ev));
    if(mode==='pinch'&&NET.pointers.size>=2){ const ps=[...NET.pointers.values()],d=Math.hypot(ps[0].x-ps[1].x,ps[0].y-ps[1].y),r=d/NET.pinch.d; let nk=Math.max(0.35,Math.min(3.2,NET.pinch.k*r)); NET.k=nk; NET.tx=NET.pinch.cx-(NET.pinch.cx-NET.pinch.tx)*(nk/NET.pinch.k); NET.ty=NET.pinch.cy-(NET.pinch.cy-NET.pinch.ty)*(nk/NET.pinch.k); applyTransform(); return; }
    const p=rectPt(ev),dx=p.x-last.x,dy=p.y-last.y; moved+=Math.abs(dx)+Math.abs(dy); last=p;
    if(mode==='pan'){ NET.tx+=dx; NET.ty+=dy; applyTransform(); }
    else if(mode==='node'&&dragNode){ const gp=toGraph(p); dragNode.x=gp.x; dragNode.y=gp.y; dragNode.fx=gp.x; dragNode.fy=gp.y; syncPositions(); } });
  const up=ev=>{ NET.pointers.delete(ev.pointerId); wrap.style.cursor='grab';
    if(mode==='node'&&dragNode){ if(moved<6){ netClearHL(); setSelected(downKey); } else { layoutLabels(); syncPositions(); } }
    mode=null; dragNode=null; };
  svg.addEventListener('pointerup',up); svg.addEventListener('pointercancel',up); svg.addEventListener('lostpointercapture',up);
  svg.addEventListener('wheel',ev=>{ ev.preventDefault(); const p=rectPt(ev),f=ev.deltaY<0?1.1:1/1.1; let nk=Math.max(0.35,Math.min(3.2,NET.k*f)); NET.tx=p.x-(p.x-NET.tx)*(nk/NET.k); NET.ty=p.y-(p.y-NET.ty)*(nk/NET.k); NET.k=nk; applyTransform(); },{passive:false});
  svg.addEventListener('mousemove',ev=>{ if(mode){ tip.style.display='none'; return; } const g=ev.target.closest('.netnode'); if(g){ const n=NET.nodes.find(x=>x.key===g.dataset.key); if(n){ netHighlight(n.key); const wr=wrap.getBoundingClientRect(); tip.innerHTML=tipHtml(n); tip.style.display='block'; let lx=ev.clientX-wr.left+14; tip.style.left=Math.min(lx,wrap.clientWidth-tip.offsetWidth-8)+'px'; tip.style.top=(ev.clientY-wr.top+12)+'px'; } } else { tip.style.display='none'; netClearHL(); } });
  svg.addEventListener('mouseleave',()=>{ tip.style.display='none'; netClearHL(); });
  const zb=(id,f)=>{const el=document.getElementById(id); if(el) el.onclick=()=>{ const X=NET.W/2,Y=NET.H/2; let nk=Math.max(0.35,Math.min(3.2,NET.k*f)); NET.tx=X-(X-NET.tx)*(nk/NET.k); NET.ty=Y-(Y-NET.ty)*(nk/NET.k); NET.k=nk; applyTransform(); };};
  zb('netZoomIn',1.2); zb('netZoomOut',1/1.2);
  const fb=document.getElementById('netFit'); if(fb) fb.onclick=()=>fitNetwork();
}
function ownershipIntel(){
  const e=ent(selectedKey), audit=audits[selectedKey];
  const owners=filteredEdges(inEdges[selectedKey]).slice().sort((a,b)=>b.pct-a.pct);
  const flags=[]; const top=owners[0];
  if(top){ const hi=top.pct>=50; flags.push({t:`${hi?'Pengendali tunggal':'Top owner'} ${fmtPct(top.pct)}`,tone:hi?'warn':'mut'}); }
  if(audit&&audit.hhi!=null){ flags.push({t:`HHI ${audit.hhi}`,tone:audit.hhi>2500?'bad':audit.hhi>1500?'warn':'good'}); }
  if(audit&&audit.residual!=null){ flags.push({t:`Residual ${fmtPct(audit.residual)}`,tone:audit.residual>=25?'bad':audit.residual>=12?'warn':'good'}); }
  if(audit&&audit.floatProxy!=null){ flags.push({t:`Float ${fmtPct(audit.floatProxy)}`,tone:audit.floatProxy<12?'warn':'good'}); }
  let f=0,l=0; for(const o of owners){ if(o.local_foreign==='F') f+=o.pct; else if(o.local_foreign==='L') l+=o.pct; }
  let fs=null; if(f+l>0){ fs=f/(f+l)*100; flags.push({t:`Asing ${fs.toFixed(0)}%`,tone:fs>=40?'warn':'mut'}); }
  const chs=(changeByTo[selectedKey]||[]); let net=0; for(const c of chs) net+=(c.deltaShares||0);
  if(chs.length){ flags.push({t:`${net>=0?'Net akumulasi':'Net distribusi'} ${fmtShares(Math.abs(net))}`,tone:net>=0?'good':'warn'}); }
  const parts=[];
  if(top) parts.push(`${e.ticker||e.label} dikuasai <b>${top.investor}</b> (${fmtPct(top.pct)})`);
  if(audit&&audit.controlLabel) parts.push(audit.controlLabel.toLowerCase());
  if(fs!=null) parts.push(`porsi asing ~${fs.toFixed(0)}% dari kepemilikan terpetakan`);
  if(audit&&audit.residual!=null) parts.push(`residual proxy ${fmtPct(audit.residual)} belum terungkap`);
  const narrative=parts.join('; ')+'.';
  let group=null;
  if(top){ const ck=top.from; const holds=(outEdges[ck]||[]).filter(x=>x.pct>=5).slice().sort((a,b)=>b.pct-a.pct);
    const members=holds.map(x=>({key:x.to,ticker:x.ticker,issuer:x.issuer,pct:x.pct})).filter(m=>m.key!==selectedKey);
    if(members.length) group={controller:top.investor,members:members.slice(0,8)}; }
  return {flags,narrative,group};
}
function renderInsightCards(){
  const e=ent(selectedKey); const audit=audits[selectedKey]; const owners=filteredEdges(inEdges[selectedKey]); const holdings=filteredEdges(outEdges[selectedKey]);
  const intel=ownershipIntel(); const tc={good:'fgood',warn:'fwarn',bad:'fbad',mut:'fmut'};
  const flagHtml=intel.flags.map(f=>`<span class="iflag ${tc[f.tone]||'fmut'}">${f.t}</span>`).join('');
  let html=`<div class="readCard intel"><h4>\u229b Intelligence Read</h4><p>${intel.narrative}</p><div class="iflags">${flagHtml}</div></div>`;
  if(intel.group){ const mem=intel.group.members.map(m=>`<span class="gchip" onclick="setSelected('${m.key}')">${m.ticker||shortLabel(m.issuer,10)} <b>${fmtPct(m.pct)}</b></span>`).join(''); html+=`<div class="readCard"><h4>\u25f7 Grup / Afiliasi \u2014 ${intel.group.controller}</h4><p class="muted">Entitas lain yang dimiliki pengendali sama (\u22655%) \u2014 klik untuk telusuri:</p><div class="gchips">${mem}</div></div>`; }
  const cards=[];
  if(e.kind==='issuer'){ const top=owners[0];
    cards.push(['Control Chain', top?`${top.investor} controls ${fmtPct(top.pct)} of ${e.ticker}. ${top.from.startsWith('E:')?'Holder ini emiten tercatat \u2014 klik untuk lanjutkan rantai.':'Holder ini tidak terpetakan sebagai emiten tercatat.'}`:'Tidak ada pemilik di atas filter.']);
    if(audit) cards.push(['Issuer Audit', `${audit.controlLabel}. HHI ${audit.hhi}, Nakamoto-50 ${audit.nakamoto50||'-'}, residual ${fmtPct(audit.residual)}, float ${fmtPct(audit.floatProxy)}.`]);
    const ch=(changeByTo[selectedKey]||[]).slice().sort((a,b)=>Math.abs(b.deltaShares)-Math.abs(a.deltaShares))[0];
    cards.push(['Largest Ownership Change', ch?`${ch.investor}: ${ch.direction} ${fmtShares(ch.deltaShares)} (${fmtBps(ch.deltaPct)}).`:'Tidak ada perubahan material.']);
  } else {
    cards.push(['Investor Footprint', `${e.label} muncul di ${holdings.length} kepemilikan langsung di atas filter.`]);
    const ch=(changeByFrom[selectedKey]||[]).slice().sort((a,b)=>Math.abs(b.deltaShares)-Math.abs(a.deltaShares))[0];
    cards.push(['Largest Position Change', ch?`${ch.ticker}: ${ch.direction} ${fmtShares(ch.deltaShares)} (${fmtBps(ch.deltaPct)}).`:'Tidak ada perubahan.']);
    if(owners.length) cards.push(['Mapped Upstream', `Entitas ini juga emiten tercatat dengan ${owners.length} pemilik langsung.`]);
  }
  html+=cards.map(([h,p])=>`<div class="readCard"><h4>${h}</h4><p>${p}</p></div>`).join('');
  document.getElementById('insightCards').innerHTML=html;
}
function tableHtml(headers, rows){
  return `<thead><tr>${headers.map(h=>`<th>${h}</th>`).join('')}</tr></thead><tbody>${rows.join('')||`<tr><td colspan="${headers.length}" class="muted">No data under current filter.</td></tr>`}</tbody>`;
}
function renderTables(){
  const owners=filteredEdges(inEdges[selectedKey]).sort((a,b)=>b.pct-a.pct);
  const holdings=filteredEdges(outEdges[selectedKey]).sort((a,b)=>b.pct-a.pct);
  document.getElementById('ownersMeta').textContent=`${owners.length} links`;
  document.getElementById('holdingsMeta').textContent=`${holdings.length} links`;
  document.getElementById('ownersTable').innerHTML=tableHtml(['Holder','Type','Stake','Shares','Change','Action'], owners.map(e=>`<tr><td><span class="linkish" onclick="setSelected('${e.from}')">${e.investor}</span></td><td>${e.classification||'-'} · ${e.local_foreign||'-'}</td><td>${fmtPct(e.pct)}</td><td>${fmtShares(e.shares)}</td><td>${dirTag(e.direction)} ${fmtShares(e.delta_shares)} <span class="muted">${fmtBps(e.delta_pct)}</span></td><td><button class="btn" onclick="setSelected('${e.from}')">Open</button></td></tr>`));
  document.getElementById('holdingsTable').innerHTML=tableHtml(['Issuer','Stake','Shares','Change','Action'], holdings.map(e=>`<tr><td><span class="linkish" onclick="setSelected('${e.to}')">${e.ticker} · ${e.issuer}</span></td><td>${fmtPct(e.pct)}</td><td>${fmtShares(e.shares)}</td><td>${dirTag(e.direction)} ${fmtShares(e.delta_shares)} <span class="muted">${fmtBps(e.delta_pct)}</span></td><td><button class="btn" onclick="setSelected('${e.to}')">Open</button></td></tr>`));
}
function relatedChanges(){return changes.filter(c=>c.from===selectedKey||c.to===selectedKey).sort((a,b)=>Math.abs(b.deltaShares)-Math.abs(a.deltaShares));}
function renderChanges(){
  const rel=relatedChanges();
  const buys=rel.filter(c=>c.deltaShares>0).slice(0,12); const sells=rel.filter(c=>c.deltaShares<0).slice(0,12);
  document.getElementById('buyTable').innerHTML=tableHtml(['Investor','Issuer','Buy / Increase','Pct Δ'], buys.map(c=>`<tr><td>${c.investor}</td><td><span class="linkish" onclick="setSelected('${c.to}')">${c.ticker}</span></td><td class="green">+${fmtShares(c.deltaShares)}</td><td>${fmtBps(c.deltaPct)}</td></tr>`));
  document.getElementById('sellTable').innerHTML=tableHtml(['Investor','Issuer','Sell / Decrease','Pct Δ'], sells.map(c=>`<tr><td>${c.investor}</td><td><span class="linkish" onclick="setSelected('${c.to}')">${c.ticker}</span></td><td class="red">${fmtShares(c.deltaShares)}</td><td>${fmtBps(c.deltaPct)}</td></tr>`));
  document.getElementById('changeMeta').textContent=`${rel.length} changed links`;
  document.getElementById('changeTable').innerHTML=tableHtml(['Direction','Investor','Issuer','Previous','Latest','Shares Δ','Pct Δ'], rel.map(c=>`<tr><td>${dirTag(c.direction)}</td><td><span class="linkish" onclick="setSelected('${c.from}')">${c.investor}</span></td><td><span class="linkish" onclick="setSelected('${c.to}')">${c.ticker} · ${c.issuer}</span></td><td>${fmtShares(c.prevShares)} · ${fmtPct(c.prevPct)}</td><td>${fmtShares(c.latestShares)} · ${fmtPct(c.latestPct)}</td><td class="${c.deltaShares>=0?'green':'red'}">${c.deltaShares>0?'+':''}${fmtShares(c.deltaShares)}</td><td>${fmtBps(c.deltaPct)}</td></tr>`));
}
function renderAudit(){
  const e=ent(selectedKey); const audit=audits[selectedKey];
  if(!audit){
    const inv=investorSummaries[selectedKey];
    document.getElementById('auditBody').innerHTML=`<div class="readCard"><h4>${e.label}</h4><p>This is an investor / non-issuer node. Issuer-level concentration audit is not applicable. Use the holdings table and proxy explorer to audit its direct and indirect exposure.</p></div>`;
    return;
  }
  const rows=[['Reported coverage',fmtPct(audit.coverage),'Sum of disclosed holders in the file'],['Residual proxy',fmtPct(audit.residual),'100% minus reported coverage'],['Float proxy',fmtPct(audit.floatProxy),'Residual + small financial/institutional holders + small individuals'],['Top 1 / Top 3 / Top 5',`${fmtPct(audit.top1)} / ${fmtPct(audit.top3)} / ${fmtPct(audit.top5)}`,'Control concentration'],['HHI',audit.hhi,'Higher = more concentrated'],['Nakamoto-50',audit.nakamoto50||'-','Minimum holders required to cross 50%'],['Foreign exposure',fmtPct(audit.foreignPct),'Foreign-classified holder points'],['Scrip ratio',fmtPct(audit.scripRatio),'Scrip holdings / reported holdings'],['Listed-holder exposure',fmtPct(audit.listedHolderPct),'Holders mapped to listed issuers'],['Audit confidence',audit.confidence+' / 100','Analytical confidence score']];
  document.getElementById('auditBody').innerHTML=`<div class="twoCols"><div class="readCard"><h4>${audit.ticker} · ${audit.issuer}</h4><p>Audit status: <b>${audit.controlLabel}</b>. Network risk: <b class="${audit.riskLabel==='High'?'red':audit.riskLabel==='Medium'?'yellow':'green'}">${audit.riskLabel}</b> (${audit.riskScore}/100). Confidence ${audit.confidence}/100.</p></div><div class="readCard"><h4>Proxy interpretation</h4><p>Residual proxy is not official free float. If residual is high, many holders are outside the disclosed table. If scrip ratio is high, ownership classification may be less complete because scrip data comes from eBAE.</p></div></div><div class="tableWrap" style="margin-top:14px"><table class="tbl">${tableHtml(['Metric','Value','Meaning'], rows.map(r=>`<tr><td>${r[0]}</td><td><b>${r[1]}</b></td><td class="muted">${r[2]}</td></tr>`))}</table></div>`;
}
function dfsUp(key, maxDepth=4){
  const paths=[];
  function walk(cur, path, eff, d){
    const incoming=filteredEdges(inEdges[cur]||[]);
    if(d>=maxDepth || incoming.length===0){ if(path.length) paths.push({path:[...path], eff}); return; }
    for(const e of incoming){
      if(path.some(p=>p.key===e.from)) continue;
      const step={key:e.from,label:ent(e.from).ticker||ent(e.from).label,pct:e.pct,edge:e};
      walk(e.from, [step,...path], eff*(e.pct/100), d+1);
    }
  }
  walk(key, [], 1, 0); return paths.sort((a,b)=>b.eff-a.eff).slice(0,30);
}
function dfsDown(key,maxDepth=4){
  const paths=[];
  function walk(cur,path,eff,d){
    const outgoing=filteredEdges(outEdges[cur]||[]);
    if(d>=maxDepth || outgoing.length===0){ if(path.length) paths.push({path:[...path], eff}); return; }
    for(const e of outgoing){
      if(path.some(p=>p.key===e.to)) continue;
      const step={key:e.to,label:ent(e.to).ticker||ent(e.to).label,pct:e.pct,edge:e};
      walk(e.to,[...path,step],eff*(e.pct/100),d+1);
    }
  }
  walk(key,[],1,0); return paths.sort((a,b)=>b.eff-a.eff).slice(0,30);
}
function renderPaths(){
  const up=dfsUp(selectedKey, depth), down=dfsDown(selectedKey, depth);
  const label=ent(selectedKey).ticker||shortLabel(ent(selectedKey).label,16);
  document.getElementById('upstreamPaths').innerHTML=up.length?up.map(p=>`<div class="pathItem"><div><div class="path">${p.path.map(x=>`<span class="linkish" onclick="setSelected('${x.key}')">${x.label}</span> <span class="muted">(${fmtPct(x.pct)})</span>`).join(' → ')} → <b>${label}</b></div><div class="muted small">Effective path stake</div></div><div class="eff">${fmtPct(p.eff*100)}</div></div>`).join(''):'<p class="muted">No upstream path under current depth/filter.</p>';
  document.getElementById('downstreamPaths').innerHTML=down.length?down.map(p=>`<div class="pathItem"><div><div class="path"><b>${label}</b> → ${p.path.map(x=>`<span class="linkish" onclick="setSelected('${x.key}')">${x.label}</span> <span class="muted">(${fmtPct(x.pct)})</span>`).join(' → ')}</div><div class="muted small">Effective path stake</div></div><div class="eff">${fmtPct(p.eff*100)}</div></div>`).join(''):'<p class="muted">No downstream path under current depth/filter.</p>';
}
function exportChanges(){
  const rel=relatedChanges();
  const header=['direction','investor','ticker','issuer','previous_shares','latest_shares','delta_shares','previous_pct','latest_pct','delta_pct'];
  const rows=rel.map(c=>[c.direction,c.investor,c.ticker,c.issuer,c.prevShares,c.latestShares,c.deltaShares,c.prevPct,c.latestPct,c.deltaPct]);
  const csv=[header,...rows].map(r=>r.map(v=>`"${String(v).replace(/"/g,'""')}"`).join(',')).join('\n');
  const blob=new Blob([csv],{type:'text/csv'}); const a=document.createElement('a'); a.href=URL.createObjectURL(blob); a.download='avenir_ownership_changes_selected.csv'; a.click();
}
function renderAll(){renderKpis(); renderNetwork(); renderInsightCards(); renderTables(); renderChanges(); renderAudit(); renderPaths();}
initSearch(); initControls(); renderAll();
