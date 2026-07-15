
<template>
  <Head title="Ownership Intelligence" />
  <div v-if="loading" class="min-h-screen bg-[#070b0a] flex items-center justify-center text-emerald-500 font-bold text-xl">
     Memuat Data Ownership Graph...
  </div>
  <div v-else-if="!dataUrl" class="min-h-screen bg-[#070b0a] flex items-center justify-center text-gray-400 font-bold text-xl">
     Belum ada data Ownership Snapshot yang diunggah.
  </div>
  <div class="app">

<aside class="side">
  <div class="brand"><div class="logo">A</div><div><h1>AVENIR</h1><p>RESEARCH</p></div></div>
  <div class="navTitle">Ownership Intelligence</div>
  <nav class="nav">
    <a class="active" data-nav="networkPane"><span class="ic">◎</span> Linked Network</a>
    <a data-nav="changesPane"><span class="ic">↕</span> Change Monitor</a>
    <a data-nav="groupPane"><span class="ic">◈</span> Conglomerate Groups</a>
    <a data-nav="govPane"><span class="ic">🏛</span> Government Layer</a>
    <a data-nav="instPane"><span class="ic">▤</span> Institutional</a>
    <a data-nav="shadowPane"><span class="ic">◇</span> Shadow Network</a>
    <a data-nav="auditPane"><span class="ic">◌</span> Issuer Audit</a>
    <a data-nav="proxyPane"><span class="ic">⛓</span> Proxy Explorer</a>
    <a data-nav="methodPane"><span class="ic">ⓘ</span> Methodology</a>
  </nav>
  <div class="dataCard">
    <div class="k">Latest source date</div>
    <div class="v" id="sideDate">-</div>
    <div class="k" style="margin-top:12px">Coverage</div>
    <div class="v"><span id="sideIssuers">0</span> issuers</div>
    <div class="k" style="margin-top:12px">Government-held issuers</div>
    <div class="v"><span id="sideGov">0</span> issuers</div>
  </div>
  <div class="dataCard info">
    <div class="k">Proxy note</div>
    <p>Residual and float metrics are proxies from disclosed holders, not official free-float. Scrip and unclassified accounts reduce confidence.</p>
  </div>
</aside>
<main class="main">
  <div class="topbar">
    <div class="tabs"><span>Beranda</span><span class="on">Research</span><span>Market</span><span>Langganan</span></div>
    <div class="search"><input id="globalSearch" list="entityList" placeholder="Cari emiten, investor, atau grup..."/><datalist id="entityList"></datalist><button id="goSearch">Cari</button></div>
  </div>
  <section class="header">
    <div>
      <div class="eyebrow">Issuer-level audit · connected investor graph · government layer</div>
      <div class="title">Ownership Intelligence</div>
      <div class="sub">Pemetaan kepemilikan lintas emiten: siapa memiliki siapa, perubahan saham, kepemilikan pemerintah, domisili &amp; klasifikasi investor, proxy UBO, dan audit konsentrasi per issuer.</div>
    </div>
    <div class="actions"><button class="btn" id="exportCsv">Export Changes</button><button class="btn primary" id="resetBtn">Reset</button></div>
  </section>

  <section class="selectorPanel">
    <div class="selectorGrid">
      <div class="field"><label>Selected entity</label><input id="selectedDisplay" readonly value="-"/></div>
      <div class="field"><label>Network depth</label><select id="depthSel"><option value="1">1-hop</option><option value="2" selected>2-hop</option><option value="3">3-hop</option><option value="4">4-hop</option></select></div>
      <div class="field"><label>Minimum stake</label><select id="minPctSel"><option value="0">All</option><option selected value="1">&ge; 1%</option><option value="3">&ge; 3%</option><option value="5">&ge; 5%</option><option value="10">&ge; 10%</option></select></div>
      <div class="field"><label>Direction</label><select id="dirSel"><option value="both">Owners + holdings</option><option value="up">Owners only</option><option value="down">Holdings only</option></select></div>
    </div>
    <div class="chips" id="quickChips"></div>
  </section>

  <section class="gridKpi" id="kpiGrid"></section>

  <section class="panel">
    <div class="tabbar">
      <button class="on" data-tab="networkPane">Network</button>
      <button data-tab="changesPane">Change Monitor</button>
      <button data-tab="groupPane">Groups</button>
      <button data-tab="govPane">Government Layer</button>
      <button data-tab="instPane">Institutional</button>
      <button data-tab="shadowPane">Shadow Network</button>
      <button data-tab="auditPane">Issuer Audit</button>
      <button data-tab="proxyPane">Proxy Explorer</button>
      <button data-tab="methodPane">Methodology</button>
    </div>

    <div class="tabpane on" id="networkPane">
      <div class="layout">
        <div>
          <div class="panelHead"><h3>Investor Network</h3><div><span class="muted small" id="netMeta"></span> <button class="btn" id="fitBtn" style="margin-left:8px">Re-center</button></div></div>
          <div class="networkWrap">
  <div class="nethint">Panah menunjuk dari <b>pemilik → yang dimiliki</b>. Hover node untuk lihat % kepemilikan · scroll = zoom · drag area kosong = geser · drag bulatan = pindah · klik = fokus</div>
  <div class="netctrls"><div class="netbtn" id="netZoomIn">+</div><div class="netbtn" id="netZoomOut">−</div><div class="netbtn" id="netFit" title="Fit ke tengah">⤢</div></div>
  <div class="netlegend">
    <span class="lg"><span class="dot" style="background:#40e07a"></span>Domestic</span>
    <span class="lg"><span class="dot" style="background:#ff5c67"></span>Foreign</span>
    <span class="lg"><span class="dot" style="background:#9b6cff"></span>Emiten/holdco</span>
    <span class="lg"><span class="dot" style="background:#f6c247"></span>Government</span>
    <span class="lg" style="border-left:1px solid var(--line);padding-left:10px">→ = memiliki</span>
  </div>
  <svg class="svgnet" id="networkSvg"></svg>
  <div id="netTip"></div>
</div>
        </div>
      </div>
      <div class="netInsightRow">
        <div class="cards" id="insightCards"></div>
        <div class="cards" id="insightCards2"></div>
      </div>
      <div class="twoCols" style="padding:16px 18px 18px">
        <div class="panel" style="box-shadow:none"><div class="panelHead"><h3>Owners of selected issuer</h3><span class="muted small" id="ownersMeta"></span></div><div class="tableWrap"><table class="tbl" id="ownersTable"></table></div></div>
        <div class="panel" style="box-shadow:none"><div class="panelHead"><h3>Holdings by selected entity</h3><span class="muted small" id="holdingsMeta"></span></div><div class="tableWrap"><table class="tbl" id="holdingsTable"></table></div></div>
      </div>
    </div>

    <div class="tabpane" id="changesPane">
      <div class="panelBody" id="changesBody"></div>
    </div>

    <div class="tabpane" id="groupPane">
      <div class="panelBody" id="groupBody"></div>
    </div>

    <div class="tabpane" id="govPane">
      <div class="panelBody" id="govBody"></div>
    </div>

    <div class="tabpane" id="instPane">
      <div class="panelBody" id="instBody"></div>
    </div>

    <div class="tabpane" id="shadowPane">
      <div class="panelBody" id="shadowBody"></div>
    </div>

    <div class="tabpane" id="auditPane">
      <div class="panelBody" id="auditBody"></div>
    </div>

    <div class="tabpane" id="proxyPane">
      <div class="panelBody">
        <div class="twoCols">
          <div class="panel" style="box-shadow:none"><div class="panelHead"><h3>Upstream control paths</h3><span class="muted small">Who ultimately owns this?</span></div><div class="panelBody" id="upstreamPaths"></div></div>
          <div class="panel" style="box-shadow:none"><div class="panelHead"><h3>Downstream exposure</h3><span class="muted small">What does this entity own?</span></div><div class="panelBody" id="downstreamPaths"></div></div>
        </div>
      </div>
    </div>

    <div class="tabpane" id="methodPane">
      <div class="panelBody">
        <div class="methodGrid">
          <div class="readCard"><h4>1. Link mapping</h4><p>Investor names are normalized and matched against listed issuer names, so the graph can continue across chains such as BREN → BRPT → PRAJOGO PANGESTU.</p></div>
          <div class="readCard"><h4>2. Ownership change</h4><p>Movement classifies each holder as increase, decrease, new, or exit versus the prior filing. Magnitude is the reported percentage weight of the move.</p></div>
          <div class="readCard"><h4>3. Effective proxy</h4><p class="formula">Effective path = stake_1 × stake_2 × … × stake_n<br/>e.g. Prajogo → BRPT → BREN = Prajogo% in BRPT × BRPT% in BREN.</p></div>
          <div class="readCard"><h4>4. Issuer audit metrics</h4><p class="formula">HHI = Σ(pct/100)² × 10,000<br/>Nakamoto-50 = holders needed to reach 50%<br/>Residual = 100% − disclosed coverage<br/>Gov% = disclosed government / SOE stake</p></div>
          <div class="readCard"><h4>5. Government layer</h4><p>Holders flagged as State-Owned Enterprises, Sovereign Wealth Funds, government pension programs, or direct government entities are separated into a dedicated layer for state-exposure analysis.</p></div>
          <div class="readCard"><h4>6. Data caveats</h4><p>Scripless data comes from SRE in C-BEST; scrip data from eBAE. Local/foreign and domicile may be unavailable for some scrip accounts, and multiple-voting-share percentages reflect ownership, not voting rights.</p></div>
        </div>
      </div>
    </div>
  </section>
  <div class="footerNote">Source: KSEI/BEI ownership disclosure data. Avenir calculations are analytical proxies and should be verified against issuer filings and official free-float data before investment use.</div>
</main>

</div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import * as d3 from 'd3';

const props = defineProps({
    dataUrl: String,
    manualInputs: Object
});

const loading = ref(true);

onMounted(async () => {
    if (!props.dataUrl) {
        loading.value = false;
        return;
    }
    
    try {
        const response = await fetch(props.dataUrl);
        const DATA = await response.json();
        window.manualInputs = props.manualInputs || {};
        
        loading.value = false;
        
        // Wait for DOM to update after v-if changes
        setTimeout(() => {
            initVanillaLogic(DATA);
        }, 100);
        
    } catch (e) {
        console.error(e);
        loading.value = false;
    }
});

function initVanillaLogic(DATA) {
    const entities = DATA.entities || {};
    const edges = DATA.edges || [];
    const changes = DATA.changes || [];
    const audits = DATA.audits || {};
    const investorSummaries = DATA.investorSummaries || {};
    const stats = DATA.stats || {};
    const govHoldings = DATA.govHoldings || [];
    const govByIssuer = DATA.govByIssuer || {};
    const groups = DATA.groups || {};
    const entityGroup = DATA.entityGroup || {};
    const institutions = DATA.institutions || {top:[],flow:{buy:[],sell:[]},classes:[]};
    const shadow = DATA.shadow || {sharedHolders:[],connectorCount:0};
    const affiliations = DATA.affiliations || {};
    const entityArr = Object.values(entities);

    


let selectedKey = (entities['E:BREN'] ? 'E:BREN' : (stats.defaultKey || Object.keys(entities)[0]));
let minPct = 1, depth = 2, directionMode = 'both';
const outEdges = {}, inEdges = {};
for (const e of edges){ (outEdges[e.from] ||= []).push(e); (inEdges[e.to] ||= []).push(e); }
for (const k in outEdges) outEdges[k].sort((a,b)=>b.pct-a.pct);
for (const k in inEdges) inEdges[k].sort((a,b)=>b.pct-a.pct);

function fmtPct(x){return (x==null||isNaN(x))?'-':`${(+x).toFixed(2)}%`}
function fmtShares(n){ n=+n||0; const s=n<0?'-':''; n=Math.abs(n); if(n>=1e12)return s+(n/1e12).toFixed(2)+'T'; if(n>=1e9)return s+(n/1e9).toFixed(2)+'B'; if(n>=1e6)return s+(n/1e6).toFixed(2)+'M'; if(n>=1e3)return s+(n/1e3).toFixed(1)+'K'; return s+Math.round(n).toLocaleString('id-ID') }
function ent(k){return entities[k] || {key:k,label:k,kind:'unknown'}}
function shortLabel(s,n=24){ s=s||''; return s.length>n?s.slice(0,n-1)+'…':s }
function dirTag(d){const cls=d==='BUY'?'buy':d==='SELL'?'sell':d==='NEW'?'new':d==='EXIT'?'exit':''; return `<span class="tag ${cls}">${d}</span>`}
function lfTag(lf){ if(lf==='F')return '<span class="tag frn">Foreign</span>'; if(lf==='L')return '<span class="tag loc">Local</span>'; return '<span class="tag">—</span>'; }
function setSelected(k){ if(!entities[k]) return; selectedKey=k; renderAll(); }
function tableHtml(cols,rowsArr){ return `<thead><tr>${cols.map(c=>`<th>${c}</th>`).join('')}</tr></thead><tbody>${rowsArr.join('')}</tbody>`; }
function filteredEdges(arr){ return (arr||[]).filter(e=>e.pct>=minPct); }

function initSearch(){
  const dl=document.getElementById('entityList');
  dl.innerHTML = entityArr.slice(0,4000).map(e=>`<option value="${(e.ticker?e.ticker+' - ':'')+(e.label||'').replace(/"/g,'&quot;')}"></option>`).join('');
  document.getElementById('goSearch').onclick=()=>{
    const q=document.getElementById('globalSearch').value.trim().toUpperCase();
    if(!q) return;
    const hit = entityArr.find(e=>e.ticker===q) || entityArr.find(e=>(e.label||'').toUpperCase().includes(q)) || entityArr.find(e=>(e.norm||'').includes(q.replace(/[^A-Z0-9]+/g,' ')));
    if(hit) setSelected(hit.key);
  };
  document.getElementById('globalSearch').addEventListener('keydown',e=>{if(e.key==='Enter')document.getElementById('goSearch').click()});
}
function switchTab(tab){
  document.querySelectorAll('.tabbar button').forEach(b=>b.classList.toggle('on',b.dataset.tab===tab));
  document.querySelectorAll('.tabpane').forEach(p=>p.classList.toggle('on',p.id===tab));
  document.querySelectorAll('.nav a').forEach(a=>a.classList.toggle('active',a.dataset.nav===tab));
  if(tab==='networkPane') renderNetwork(true);
}
function initControls(){
  document.getElementById('sideDate').textContent=stats.latestDate;
  document.getElementById('sideIssuers').textContent=stats.issuersLatest;
  document.getElementById('sideGov').textContent=stats.govIssuers||0;
  document.getElementById('depthSel').onchange=e=>{depth=+e.target.value;renderAll()};
  document.getElementById('minPctSel').onchange=e=>{minPct=+e.target.value;renderAll()};
  document.getElementById('dirSel').onchange=e=>{directionMode=e.target.value;renderAll()};
  document.getElementById('resetBtn').onclick=()=>setSelected(stats.defaultKey);
  document.getElementById('fitBtn').onclick=()=>renderNetwork(true);
  document.getElementById('exportCsv').onclick=exportChanges;
  document.querySelectorAll('.tabbar button').forEach(btn=>btn.onclick=()=>switchTab(btn.dataset.tab));
  document.querySelectorAll('.nav a[data-nav]').forEach(a=>a.onclick=()=>switchTab(a.dataset.nav));
  const chips=['E:BREN','E:BRPT','E:BBCA','E:TPIA','E:ADRO','E:BBRI','E:TLKM','E:ASII'];
  document.getElementById('quickChips').innerHTML=chips.filter(k=>entities[k]).map(k=>`<span class="chip" data-key="${k}">${entities[k].ticker||shortLabel(entities[k].label,18)}</span>`).join('');
  document.querySelectorAll('.chip[data-key]').forEach(c=>c.onclick=()=>setSelected(c.dataset.key));
}

function renderKpis(){
  const e=ent(selectedKey);
  document.getElementById('selectedDisplay').value=(e.ticker?e.ticker+' - ':'')+e.label;
  document.querySelectorAll('.chip[data-key]').forEach(c=>c.classList.toggle('on',c.dataset.key===selectedKey));
  const audit=audits[selectedKey], inv=investorSummaries[selectedKey];
  const ownerCount=(inEdges[selectedKey]||[]).length, holdCount=(outEdges[selectedKey]||[]).length;
  const rel=changes.filter(c=>c.from===selectedKey||c.to===selectedKey);
  const buy=rel.filter(c=>c.direction==='BUY'||c.direction==='NEW').length;
  const sell=rel.filter(c=>c.direction==='SELL'||c.direction==='EXIT').length;
  const kpis=[];
  if(e.kind==='issuer'){
    kpis.push(['Audit confidence', audit?audit.confidence+' / 100':'-', audit?'Issuer-level data quality':'No audit']);
    kpis.push(['Control concentration', audit?fmtPct(audit.top1):'-', audit?audit.controlLabel:'']);
    kpis.push(['Government stake', audit?fmtPct(audit.govPct):'-', 'Disclosed SOE / gov holders','amber']);
    kpis.push(['Residual proxy', audit?fmtPct(audit.residual):'-', '100% − disclosed coverage']);
    kpis.push(['Foreign holders', audit?fmtPct(audit.foreignPct):'-', 'Disclosed foreign stake','blue']);
    kpis.push(['Upstream owners', ownerCount, 'Direct disclosed owners']);
  } else {
    kpis.push(['Direct holdings', holdCount, 'Issuers held']);
    kpis.push(['Ownership points', inv?fmtPct(inv.ownershipPoints):'-', 'Sum of disclosed stakes']);
    kpis.push(['Largest position', inv?`${inv.largestTicker} ${fmtPct(inv.largestPct)}`:'-', inv?shortLabel(inv.largestIssuer,22):'']);
    kpis.push(['Increases', buy, 'Moves up / new','green']);
    kpis.push(['Decreases', sell, 'Moves down / exit','red']);
    kpis.push(['Profile', inv&&inv.foreign?'Foreign':'Local', (inv&&inv.classifications&&inv.classifications[0])||'—', inv&&inv.foreign?'blue':'green']);
  }
  document.getElementById('kpiGrid').innerHTML=kpis.map(k=>`<div class="kpi"><div class="label">${k[0]}</div><div class="num ${k[3]||''}">${k[1]}</div><div class="note">${k[2]||''}</div></div>`).join('');
}

/* ============ NETWORK (force-directed, draggable nodes) ============ */
var NET=(window.__NET=window.__NET||{k:1,tx:0,ty:0,wired:false,nodes:[],links:[],center:null,dragNode:null,raf:null,idx:{},els:null});

function buildGraph(){
  const nodes=new Map(), links=[];
  const center=selectedKey; nodes.set(center,{key:center,depth:0});
  let frontier=[center];
  for(let d=1; d<=depth; d++){
    const next=[];
    for(const k of frontier){
      if(directionMode!=='down') for(const e of filteredEdges(inEdges[k])){ if(!nodes.has(e.from))nodes.set(e.from,{key:e.from,depth:d}); links.push(e); next.push(e.from); }
      if(directionMode!=='up') for(const e of filteredEdges(outEdges[k])){ if(!nodes.has(e.to))nodes.set(e.to,{key:e.to,depth:d}); links.push(e); next.push(e.to); }
    }
    frontier=next;
  }
  const arr=[...nodes.values()]; const cap=90;
  let keep=new Set(arr.slice(0,cap).map(n=>n.key)); keep.add(center);
  const L=links.filter(e=>keep.has(e.from)&&keep.has(e.to));
  const nn=[...keep].map(k=>({key:k,depth:nodes.get(k)?nodes.get(k).depth:1}));
  return {nodes:nn,links:L,center};
}
function nodeClass(k,center){
  if(k===center) return 'nodeSelected';
  const e=ent(k);
  if(e.kind==='issuer') return 'nodeIssuer';
  const gov=(outEdges[k]||[]).some(x=>x.is_government)||(inEdges[k]||[]).some(x=>x.is_government && x.from===k);
  if(gov) return 'nodeGov';
  const frn=(outEdges[k]||[]).some(x=>x.local_foreign==='F');
  return frn?'nodeForeign':'nodeLocal';
}
function nodeRadius(k,center){ return k===center?22:(ent(k).kind==='issuer'?15:12); }

function renderNetwork(relayout){
  const svg=document.getElementById('networkSvg'); if(!svg) return;
  const g=buildGraph();
  const W=svg.clientWidth||800, H=svg.clientHeight||600;
  const cx=W/2, cy=H/2;
  const prev={}; (NET.nodes||[]).forEach(n=>prev[n.key]={x:n.x,y:n.y});
  // group nodes by depth for even angular seeding
  const byDepth={}; g.nodes.forEach(n=>{(byDepth[n.depth]||=[]).push(n);});
  const seedPos={};
  Object.keys(byDepth).forEach(dp=>{
    const list=byDepth[dp]; const d=+dp;
    const R=d===0?0:Math.min(W,H)*0.17*d+40;
    list.forEach((n,i)=>{
      const ang=(i/Math.max(1,list.length))*Math.PI*2 + d*0.6;
      seedPos[n.key]={x:cx+Math.cos(ang)*R, y:cy+Math.sin(ang)*R};
    });
  });
  const nodes=g.nodes.map(n=>{
    const p=(!relayout && prev[n.key])?prev[n.key]:null;
    const s=seedPos[n.key]||{x:cx,y:cy};
    return {key:n.key,depth:n.depth,
      x:p?p.x:(n.key===g.center?cx:s.x),
      y:p?p.y:(n.key===g.center?cy:s.y),
      vx:0,vy:0,fx:n.key===g.center?cx:null,fy:n.key===g.center?cy:null};
  });
  const idx={}; nodes.forEach((n,i)=>idx[n.key]=i);
  const links=g.links.filter(e=>idx[e.from]!=null&&idx[e.to]!=null)
    .map(e=>({from:e.from,to:e.to,dir:e.direction,pct:e.pct}));
  NET.nodes=nodes; NET.links=links; NET.center=g.center; NET.idx=idx; NET.W=W; NET.H=H;
  document.getElementById('netMeta').textContent=`${nodes.length} nodes · ${links.length} links`;
  drawNet(svg);
  startSim(true);
  wireNet(svg);
}

function startSim(reheat){
  if(NET.raf) cancelAnimationFrame(NET.raf);
  NET.alpha = reheat ? 1 : Math.max(NET.alpha||0, 0.4);
  const nodes=NET.nodes, links=NET.links, idx=NET.idx, W=NET.W, H=NET.H, cx=W/2, cy=H/2;
  function tick(){
    NET.alpha*=0.985;
    for(let i=0;i<nodes.length;i++){
      const a=nodes[i];
      for(let j=i+1;j<nodes.length;j++){
        const b=nodes[j];
        let dx=a.x-b.x, dy=a.y-b.y; let d2=dx*dx+dy*dy||0.01; let d=Math.sqrt(d2);
        const rep=3400/d2; const fx=dx/d*rep, fy=dy/d*rep;
        a.vx+=fx; a.vy+=fy; b.vx-=fx; b.vy-=fy;
      }
    }
    for(const l of links){
      const a=nodes[idx[l.from]], b=nodes[idx[l.to]]; if(!a||!b)continue;
      let dx=b.x-a.x, dy=b.y-a.y; let d=Math.sqrt(dx*dx+dy*dy)||0.01;
      const target=130; const k=(d-target)*0.02;
      const fx=dx/d*k, fy=dy/d*k;
      a.vx+=fx; a.vy+=fy; b.vx-=fx; b.vy-=fy;
    }
    for(const n of nodes){
      n.vx+=(cx-n.x)*0.002; n.vy+=(cy-n.y)*0.002;
      if(n.fx!=null){n.x=n.fx;n.y=n.fy;n.vx=0;n.vy=0;continue;}
      n.vx*=0.86; n.vy*=0.86;
      n.x+=n.vx*NET.alpha*1.6; n.y+=n.vy*NET.alpha*1.6;
    }
    positionNet();
    if(NET.alpha>0.02 || NET.dragNode){ NET.raf=requestAnimationFrame(tick); }
    else { NET.raf=null; }
  }
  NET.raf=requestAnimationFrame(tick);
}

function drawNet(svg){
  const {nodes,links,center}=NET;
  const defs=`<defs>
    <marker id="arrowOwn" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="7" markerHeight="7" orient="auto-start-reverse"><path d="M0,0 L10,5 L0,10 z" fill="#5a6b64"/></marker>
    <marker id="arrowHot" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="8" markerHeight="8" orient="auto-start-reverse"><path d="M0,0 L10,5 L0,10 z" fill="#46e07a"/></marker>
  </defs>`;
  const linkStr=links.map((l,i)=>{
    let cls='netlink';
    if(l.dir==='NEW')cls+=' lnew'; else if(l.dir==='EXIT')cls+=' lexit';
    else if(l.dir==='BUY')cls+=' lbuy'; else if(l.dir==='SELL')cls+=' lsell';
    // from = owner, to = owned. Arrow points owner -> owned.
    return `<path class="${cls}" data-a="${l.from}" data-b="${l.to}" data-pct="${l.pct||''}" marker-end="url(#arrowOwn)"/>`;
  }).join('');
  const elabelStr=links.map((l,i)=>`<text class="elabel" data-i="${i}" data-a="${l.from}" data-b="${l.to}">${l.pct?fmtPct(l.pct):''}</text>`).join('');
  const nodeStr=nodes.map(n=>{
    const r=nodeRadius(n.key,center); const cls=nodeClass(n.key,center);
    const e=ent(n.key); const ini=(e.ticker||(e.label||'?')).slice(0,2).toUpperCase();
    return `<g class="netnode" data-key="${n.key}"><circle r="${r}" class="${cls}" stroke-width="2"/><text class="ini">${ini}</text></g>`;
  }).join('');
  const labelStr=nodes.map(n=>{
    const e=ent(n.key); const isC=n.key===center; const lbl=e.ticker||shortLabel(e.label,14);
    return `<g class="nlabel ${isC?'center':''}" data-key="${n.key}"><text class="ttl">${lbl}</text></g>`;
  }).join('');
  svg.innerHTML=`${defs}<g id="netZoomG" transform="translate(${NET.tx},${NET.ty}) scale(${NET.k})"><g class="netlinks">${linkStr}</g><g class="netelabels">${elabelStr}</g><g class="netnodes">${nodeStr}</g><g class="netlabels">${labelStr}</g></g>`;
  NET.els={
    links:[...svg.querySelectorAll('.netlink')],
    elabels:[...svg.querySelectorAll('.elabel')],
    nodes:[...svg.querySelectorAll('.netnode')],
    labels:[...svg.querySelectorAll('.nlabel')],
  };
  positionNet();
}
function positionNet(){
  const {nodes,idx,els,center}=NET; if(!els)return;
  for(const p of els.links){
    const a=nodes[idx[p.dataset.a]], b=nodes[idx[p.dataset.b]]; if(!a||!b)continue;
    // shorten the line so arrowhead sits at edge of target node, not center
    const rB=nodeRadius(p.dataset.b,center)+7;
    const dx=b.x-a.x, dy=b.y-a.y; const d=Math.sqrt(dx*dx+dy*dy)||1;
    const bx=b.x-dx/d*rB, by=b.y-dy/d*rB;
    const mx=(a.x+bx)/2, my=(a.y+by)/2-18;
    p.setAttribute('d',`M${a.x.toFixed(1)},${a.y.toFixed(1)} Q${mx.toFixed(1)},${my.toFixed(1)} ${bx.toFixed(1)},${by.toFixed(1)}`);
  }
  if(els.elabels) for(const t of els.elabels){
    const a=nodes[idx[t.dataset.a]], b=nodes[idx[t.dataset.b]]; if(!a||!b)continue;
    t.setAttribute('x',((a.x+b.x)/2).toFixed(1)); t.setAttribute('y',((a.y+b.y)/2-20).toFixed(1));
  }
  for(const gEl of els.nodes){ const n=nodes[idx[gEl.dataset.key]]; if(n)gEl.setAttribute('transform',`translate(${n.x.toFixed(1)},${n.y.toFixed(1)})`); }
  for(const lEl of els.labels){ const n=nodes[idx[lEl.dataset.key]]; if(!n)continue;
    const r=nodeRadius(n.key,center); const t=lEl.querySelector('.ttl');
    t.setAttribute('x',n.x.toFixed(1)); t.setAttribute('y',(n.y+r+13).toFixed(1)); }
}
function screenToGraph(svg,clientX,clientY){
  const rect=svg.getBoundingClientRect();
  return { x:(clientX-rect.left-NET.tx)/NET.k, y:(clientY-rect.top-NET.ty)/NET.k };
}
function wireNet(svg){
  const tip=document.getElementById('netTip');
  const {idx,nodes}=NET;
  NET.els.nodes.forEach(node=>{
    const key=node.dataset.key;
    let moved=false, startX=0, startY=0;
    node.onmouseenter=()=>{
      if(NET.dragNode)return;
      const e=ent(key);
      const owners=(inEdges[key]||[]).length, holds=(outEdges[key]||[]).length;
      tip.innerHTML=`<b>${e.ticker?e.ticker+' · ':''}${shortLabel(e.label,30)}</b><br/><span class="rt">${e.kind==='issuer'?'Emiten':'Investor'} · ◄ dimiliki oleh ${owners} · memiliki ► ${holds}</span>`;
      tip.style.display='block';
      svg.querySelectorAll('.netnode,.netlink,.nlabel,.elabel').forEach(el=>el.classList.add('dim'));
      svg.querySelectorAll('.elabel').forEach(el=>el.classList.remove('show'));
      node.classList.remove('dim'); node.classList.add('hot');
      svg.querySelectorAll(`.nlabel[data-key="${CSS.escape(key)}"]`).forEach(el=>el.classList.remove('dim'));
      NET.els.links.forEach((l,i)=>{ if(l.dataset.a===key||l.dataset.b===key){ l.classList.remove('dim'); l.classList.add('hot');
        const other=l.dataset.a===key?l.dataset.b:l.dataset.a;
        svg.querySelectorAll(`.netnode[data-key="${CSS.escape(other)}"],.nlabel[data-key="${CSS.escape(other)}"]`).forEach(el=>el.classList.remove('dim'));
      }});
      if(NET.els.elabels) NET.els.elabels.forEach(t=>{ if(t.dataset.a===key||t.dataset.b===key){ t.classList.remove('dim'); t.classList.add('show'); } });
    };
    node.onmousemove=(ev)=>{ const rect=svg.getBoundingClientRect(); tip.style.left=(ev.clientX-rect.left+14)+'px'; tip.style.top=(ev.clientY-rect.top+14)+'px'; };
    node.onmouseleave=()=>{ if(NET.dragNode)return; tip.style.display='none'; svg.querySelectorAll('.netnode,.netlink,.nlabel,.elabel').forEach(el=>el.classList.remove('dim','hot','show')); };
    node.addEventListener('mousedown',(ev)=>{
      ev.stopPropagation(); ev.preventDefault();
      const n=nodes[idx[key]]; if(!n)return;
      NET.dragNode=n; moved=false; startX=ev.clientX; startY=ev.clientY;
      node.classList.add('grabbing'); tip.style.display='none';
      startSim(false);
      const onMove=(e2)=>{
        const p=screenToGraph(svg,e2.clientX,e2.clientY);
        n.fx=p.x; n.fy=p.y; n.x=p.x; n.y=p.y; n.vx=0; n.vy=0;
        if(Math.abs(e2.clientX-startX)>3||Math.abs(e2.clientY-startY)>3) moved=true;
        positionNet();
      };
      const onUp=()=>{
        window.removeEventListener('mousemove',onMove);
        window.removeEventListener('mouseup',onUp);
        node.classList.remove('grabbing');
        if(key!==NET.center){ n.fx=null; n.fy=null; }
        NET.dragNode=null;
        if(!moved){ setSelected(key); }
        startSim(false);
      };
      window.addEventListener('mousemove',onMove);
      window.addEventListener('mouseup',onUp);
    });
    node.addEventListener('touchstart',(ev)=>{
      const t=ev.touches[0]; const n=nodes[idx[key]]; if(!n)return;
      ev.stopPropagation();
      NET.dragNode=n; moved=false; startX=t.clientX; startY=t.clientY; startSim(false);
      const onMove=(e2)=>{ const tt=e2.touches[0]; const p=screenToGraph(svg,tt.clientX,tt.clientY);
        n.fx=p.x;n.fy=p.y;n.x=p.x;n.y=p.y;n.vx=0;n.vy=0;
        if(Math.abs(tt.clientX-startX)>4||Math.abs(tt.clientY-startY)>4)moved=true; positionNet(); e2.preventDefault(); };
      const onEnd=()=>{ document.removeEventListener('touchmove',onMove); document.removeEventListener('touchend',onEnd);
        if(key!==NET.center){n.fx=null;n.fy=null;} NET.dragNode=null; if(!moved)setSelected(key); startSim(false); };
      document.addEventListener('touchmove',onMove,{passive:false}); document.addEventListener('touchend',onEnd);
    },{passive:true});
  });
  if(!NET.wired){
    NET.wired=true;
    const wrap=svg.parentElement;
    let panning=false,sx=0,sy=0,ox=0,oy=0;
    wrap.addEventListener('wheel',ev=>{ ev.preventDefault();
      const rect=svg.getBoundingClientRect(); const mx=ev.clientX-rect.left, my=ev.clientY-rect.top;
      const d=ev.deltaY<0?1.12:0.89; const nk=Math.max(0.25,Math.min(5,NET.k*d));
      NET.tx=mx-(mx-NET.tx)*(nk/NET.k); NET.ty=my-(my-NET.ty)*(nk/NET.k); NET.k=nk; applyZoom();
    },{passive:false});
    // pan when pressing on empty canvas (not on a node)
    const startPan=(cx,cy)=>{ panning=true; sx=cx; sy=cy; ox=NET.tx; oy=NET.ty; wrap.style.cursor='grabbing'; };
    wrap.addEventListener('mousedown',ev=>{
      if(ev.target.closest('.netnode')) return;   // node drag handles itself
      startPan(ev.clientX,ev.clientY);
      ev.preventDefault();
    });
    window.addEventListener('mousemove',ev=>{ if(!panning)return; NET.tx=ox+(ev.clientX-sx); NET.ty=oy+(ev.clientY-sy); applyZoom(); });
    window.addEventListener('mouseup',()=>{ if(panning){panning=false; wrap.style.cursor='grab';} });
    // touch pan on empty canvas
    wrap.addEventListener('touchstart',ev=>{
      if(ev.target.closest('.netnode')) return;
      if(ev.touches.length!==1) return;
      startPan(ev.touches[0].clientX, ev.touches[0].clientY);
    },{passive:true});
    wrap.addEventListener('touchmove',ev=>{ if(!panning||ev.touches.length!==1)return;
      NET.tx=ox+(ev.touches[0].clientX-sx); NET.ty=oy+(ev.touches[0].clientY-sy); applyZoom(); ev.preventDefault();
    },{passive:false});
    wrap.addEventListener('touchend',()=>{ panning=false; });
    document.getElementById('netZoomIn').onclick=()=>{NET.k=Math.min(5,NET.k*1.2);applyZoom();};
    document.getElementById('netZoomOut').onclick=()=>{NET.k=Math.max(0.25,NET.k*0.83);applyZoom();};
    document.getElementById('netFit').onclick=()=>{NET.k=1;NET.tx=0;NET.ty=0;applyZoom();};
  }
}
function applyZoom(){ const gz=document.getElementById('netZoomG'); if(gz)gz.setAttribute('transform',`translate(${NET.tx},${NET.ty}) scale(${NET.k})`); }

/* ============ INSIGHT CARDS (controller intelligence) ============ */
const CTRL_TYPE_LABEL={via_holding:'Kendali via holding',direct:'Pengendali langsung (individu)',
  direct_corp:'Pengendali langsung (korporasi)',family:'Kendali keluarga',government:'Kendali pemerintah'};
function controllerCard(audit){
  const c=audit.controller; if(!c) return '';
  const tcls='ct-'+c.type;
  const tlabel=CTRL_TYPE_LABEL[c.type]||'Pengendali';
  let nameHtml = c.holderKey&&entities[c.holderKey]
    ? `<span class="linkish" onclick="setSelected('${c.holderKey}')">${c.name}</span>`
    : c.name;
  let explain='';
  if(c.type==='via_holding') explain=`Dikendalikan lewat perusahaan induk yang tercatat. Untuk pemilik akhir (UBO), telusuri rantai di bawah.`;
  else if(c.type==='family') explain=`Blok keluarga dengan marga sama menguasai gabungan ${fmtPct(c.pct)}. ${c.detail?('Anggota: '+c.detail):''}`;
  else if(c.type==='direct') explain=`Individu memegang saham secara langsung sebagai pemegang saham pengendali.`;
  else if(c.type==='direct_corp') explain=`Korporasi/lembaga memegang langsung sebagai pemegang saham terbesar.`;
  else if(c.type==='government') explain=`Negara/BUMN merupakan pemegang saham dominan.`;
  const grp=c.group?`<span class="strengthPill" style="border-color:rgba(53,208,111,.4);color:#6dff9d;cursor:pointer" onclick="focusGroup('${c.group.replace(/'/g,"\\'")}')">◈ ${c.group}</span>`:'';
  return `<div class="ctrlCard">
    <div class="ctrlHead"><span class="ctrlType ${tcls}">${tlabel}</span><span class="strengthPill">${c.strength}</span>${grp}</div>
    <div class="ctrlName">${nameHtml}</div>
    <div class="ctrlHead" style="margin-top:6px"><span class="ctrlPct">${fmtPct(c.pct)}</span><span class="ctrlMeta">stake pengendali</span></div>
    <div class="ctrlMeta" style="margin-top:8px">${explain}</div>
    ${uboChainHtml(audit)}
  </div>`;
}
const STYPE_LABEL={offshore:'offshore holdco',holdco:'holdco privat',nominee:'nominee/kustodian',issuer:'emiten',state:'BUMN/negara',direct:''};
function uboChainHtml(audit){
  const ch=audit.uboChain||[]; if(ch.length<1) return '';
  const iss=`<div class="uboBox top" onclick="setSelected('${selectedKey}')"><div class="un">${audit.ticker||shortLabel(audit.issuer,16)}</div><div class="up">emiten</div></div>`;
  const steps=ch.map((s,i)=>{
    const isLast=i===ch.length-1;
    let cls='uboBox';
    if(isLast) cls+= (s.stype==='offshore'||s.stype==='holdco'||s.stype==='nominee')?' shadow':' ubo';
    const tag=s.stype&&STYPE_LABEL[s.stype]?`<div class="uboTag">${STYPE_LABEL[s.stype]}${s.domicile&&s.stype==='offshore'?' · '+s.domicile:''}</div>`:'';
    const box=`<div class="${cls}" onclick="setSelected('${s.key}')"><div class="un">${shortLabel(s.name,20)}</div><div class="up">${fmtPct(s.pct)}${isLast&&!audit.uboOpaque?' · UBO':''}</div>${tag}</div>`;
    return `<span class="uboArrow">◄</span>`+box;
  }).join('');
  const finalName=ch[ch.length-1]?ch[ch.length-1].name:'';
  let eff='';
  if(audit.uboOpaque){
    eff=`<div class="uboWarn">⚠ Pemilik akhir <b>tidak transparan</b> — rantai berhenti di ${STYPE_LABEL[ch[ch.length-1].stype]||'entitas privat'} <b>${shortLabel(finalName,26)}</b>${ch[ch.length-1].domicile?(' ('+ch[ch.length-1].domicile+')'):''}. Kepemilikan sebenarnya kemungkinan lewat struktur bayangan; cek afiliasi di bawah untuk keterkaitan grup.</div>`;
  } else if(audit.uboEffective!=null){
    eff=`<div class="uboFinal">Perkiraan kendali efektif <b>${finalName}</b> ≈ <b>${fmtPct(audit.uboEffective)}</b> (perkalian rantai)</div>`;
  }
  return `<div class="gsub">Rantai kendali → pemilik akhir</div><div class="uboChain"><div class="uboStep">${iss}${steps}</div></div>${eff}`;
}
function affiliationCard(audit){
  const aff=audit.topAffiliations||[]; if(!aff.length) return '';
  const rows=aff.slice(0,6).map(a=>`<div class="affRow"><div><span class="linkish" onclick="setSelected('${a.key}')">${a.ticker}</span> <span class="muted small">${shortLabel(a.issuer,20)}</span></div><div class="affVia" title="${a.sharedHolders.join(', ')}">${a.sharedCount} pemegang sama</div></div>`).join('');
  return `<div class="readCard affil"><h4>Keterkaitan tersembunyi (pemegang saham bersama)</h4>
    <div class="segLabel">Emiten yang berbagi pemegang saham dengan ${audit.ticker} — indikasi afiliasi/grup bayangan</div>
    <div class="affList">${rows}</div></div>`;
}
function familyCard(audit){
  const fams=(audit.families||[]).filter(f=>f.totalPct>=1); if(!fams.length) return '';
  return fams.slice(0,2).map(f=>`<div class="famCard"><div class="famHead"><h4>Keluarga ${f.surname}</h4><span class="ft">${fmtPct(f.totalPct)}</span></div>
    <div class="segLabel">${f.members.length} anggota keluarga terdeteksi memegang saham</div>
    <div class="famMembers">${f.members.slice(0,8).map(m=>`<span class="famM">${shortLabel(m.name,22)} <b>${fmtPct(m.pct)}</b></span>`).join('')}</div></div>`).join('');
}
function renderInsightCards(){
  const host=document.getElementById('insightCards');
  const host2=document.getElementById('insightCards2');
  const e=ent(selectedKey); const audit=audits[selectedKey];
  let html='', html2='';
  if(e.kind==='issuer' && audit){
    html+=controllerCard(audit);
    if(audit.controller&&audit.controller.type!=='family') html+=familyCard(audit);
    const flags=[];
    flags.push(`<span class="iflag ${audit.confidence>=80?'fgood':audit.confidence>=60?'fwarn':'fbad'}">Confidence ${audit.confidence}</span>`);
    flags.push(`<span class="iflag ${audit.riskLabel==='Low'?'fgood':audit.riskLabel==='Medium'?'fwarn':'fbad'}">Concentration risk ${audit.riskLabel}</span>`);
    if(audit.foreignPct>0) flags.push(`<span class="iflag finfo">Foreign ${fmtPct(audit.foreignPct)}</span>`);
    if(audit.instPct>0) flags.push(`<span class="iflag finfo">Institusi ${fmtPct(audit.instPct)}</span>`);
    if(audit.scripRatio>0.1) flags.push(`<span class="iflag fwarn">Scrip ${(audit.scripRatio*100).toFixed(0)}%</span>`);
    html2+=`<div class="readCard"><h4>Struktur konsentrasi</h4><p>Top holder ${fmtPct(audit.top1)}, top-3 ${fmtPct(audit.top3)}, top-5 ${fmtPct(audit.top5)}. Nakamoto-50 = ${audit.nakamoto50} pemegang. Residual proxy ${fmtPct(audit.residual)}.</p><div class="iflags">${flags.join('')}</div></div>`;
    if(audit.instPct>0){
      html2+=`<div class="readCard" style="border-color:rgba(58,160,255,.3)"><h4>Kepemilikan institusional</h4><p>${audit.instHolders} pemegang institusi (reksadana, asuransi, dana pensiun, sekuritas, bank), gabungan <b>${fmtPct(audit.instPct)}</b> dari saham terdisclosure.</p></div>`;
    }
    html2+=affiliationCard(audit);
    if(audit.suspectedGroup){
      html2+=`<div class="readCard" style="border-color:rgba(224,151,62,.4);background:linear-gradient(180deg,rgba(224,151,62,.07),transparent)"><h4>⚠ Diduga terafiliasi grup</h4><p>Pengendali resmi opaque, tapi <b>${audit.suspectedGroup}</b> ikut memegang saham lewat ${shortLabel(audit.suspectedVia||'',30)}. Kemungkinan afiliasi grup lewat struktur bayangan.</p><div class="gchips" style="margin-top:8px"><span class="gchip" onclick="focusGroup('${audit.suspectedGroup.replace(/'/g,"\\'")}')">◈ Lihat ${audit.suspectedGroup}</span></div></div>`;
    }
    const gh=govByIssuer[selectedKey]||[];
    if(gh.length){
      html2+=`<div class="readCard gov"><h4>Eksposur pemerintah</h4><p>${gh.length} pemegang terkait negara, gabungan ${fmtPct(audit.govPct)}.</p><div class="afmembers">${gh.slice(0,4).map(g=>`<div class="afm"><span>${shortLabel(g.investor,26)}</span><b>${fmtPct(g.pct)}</b></div>`).join('')}</div></div>`;
    }
  } else {
    const inv=investorSummaries[selectedKey];
    if(inv){
      const flags=[];
      flags.push(`<span class="iflag ${inv.foreign?'finfo':'fgood'}">${inv.foreign?'Foreign':'Local'}</span>`);
      (inv.classifications||[]).slice(0,3).forEach(c=>flags.push(`<span class="iflag fmut">${c}</span>`));
      const grp=entityGroup[selectedKey];
      html+=`<div class="ctrlCard"><div class="ctrlHead"><span class="ctrlType ct-direct">Investor</span>${grp?`<span class="strengthPill" style="color:#6dff9d;cursor:pointer" onclick="focusGroup('${grp.replace(/'/g,"\\'")}')">◈ ${grp}</span>`:''}</div>
        <div class="ctrlName">${shortLabel(e.label,30)}</div>
        <div class="ctrlMeta" style="margin-top:6px">Memegang <b>${inv.holdingCount}</b> emiten · total ${fmtPct(inv.ownershipPoints)} · terbesar <b>${inv.largestTicker}</b> ${fmtPct(inv.largestPct)}</div>
        <div class="iflags">${flags.join('')}</div></div>`;
      if(inv.domiciles&&inv.domiciles.length) html2+=`<div class="readCard"><h4>Domisili</h4><p>${inv.domiciles.slice(0,5).join(', ')}</p></div>`;
      const controls=Object.values(audits).filter(a=>a.controller&&a.controller.holderKey===selectedKey);
      if(controls.length) html2+=`<div class="readCard"><h4>Mengendalikan emiten</h4><div class="gchips">${controls.slice(0,10).map(a=>`<span class="gchip" onclick="setSelected('E:${a.ticker}')">${a.ticker} <b>${fmtPct(a.controller.pct)}</b></span>`).join('')}</div></div>`;
    }
  }
  const nb=(inEdges[selectedKey]||[]).concat(outEdges[selectedKey]||[]).filter(x=>x.pct>=minPct).slice(0,10);
  if(nb.length){
    html2+=`<div class="readCard"><h4>Lompat ke entitas terkait</h4><div class="gchips">${nb.map(x=>{const other=x.from===selectedKey?x.to:x.from;const en=ent(other);return `<span class="gchip" onclick="setSelected('${other}')">${en.ticker||shortLabel(en.label,16)} <b>${fmtPct(x.pct)}</b></span>`;}).join('')}</div></div>`;
  }
  host.innerHTML=html||'<div class="readCard"><p class="muted">Tidak ada insight untuk entitas ini pada filter saat ini.</p></div>';
  host2.innerHTML=html2;
}
function focusGroup(label){ switchTab('groupPane'); setTimeout(()=>{const el=document.getElementById('grp-'+cssId(label)); if(el){el.scrollIntoView({behavior:'smooth',block:'center'});el.style.borderColor='var(--green)';setTimeout(()=>el.style.borderColor='',1500);}},60); }
function cssId(s){ return s.replace(/[^a-zA-Z0-9]/g,'-'); }

/* ============ TABLES ============ */
function renderTables(){
  const owners=filteredEdges(inEdges[selectedKey]);
  const holdings=filteredEdges(outEdges[selectedKey]);
  document.getElementById('ownersMeta').textContent=`${owners.length} owners`;
  document.getElementById('holdingsMeta').textContent=`${holdings.length} holdings`;
  document.getElementById('ownersTable').innerHTML=tableHtml(
    ['Investor','Stake','Shares','Type','Origin','Move'],
    owners.length?owners.map(e=>`<tr><td><span class="linkish" onclick="setSelected('${e.from}')">${shortLabel(e.investor,30)}</span></td><td><b>${fmtPct(e.pct)}</b></td><td>${fmtShares(e.shares)}</td><td>${e.classification||'—'}</td><td>${lfTag(e.local_foreign)}${e.is_government?' <span class="tag gov">Gov</span>':''}</td><td>${e.direction!=='UNCHANGED'?dirTag(e.direction):'<span class="muted">—</span>'}</td></tr>`):['<tr><td colspan="6" class="muted">No owners disclosed under current filter.</td></tr>']
  );
  document.getElementById('holdingsTable').innerHTML=tableHtml(
    ['Issuer','Stake','Shares','Origin','Move'],
    holdings.length?holdings.map(e=>`<tr><td><span class="linkish" onclick="setSelected('${e.to}')">${e.ticker||shortLabel(e.issuer,22)}</span></td><td><b>${fmtPct(e.pct)}</b></td><td>${fmtShares(e.shares)}</td><td>${lfTag(e.local_foreign)}</td><td>${e.direction!=='UNCHANGED'?dirTag(e.direction):'<span class="muted">—</span>'}</td></tr>`):['<tr><td colspan="5" class="muted">This entity is not recorded holding other issuers.</td></tr>']
  );
}

/* ============ CHANGES ============ */
function relatedChanges(){ return changes.filter(c=>c.from===selectedKey||c.to===selectedKey); }
let chgFilter={dir:'all', q:''};
function chgDirGroup(d){ return (d==='BUY'||d==='NEW')?'up':'down'; }
function renderChanges(){
  const host=document.getElementById('changesBody');
  const s=stats;
  const hero=`<div class="chgHero">
    <div class="chgStat up"><div class="cl">Penambahan (increase)</div><div class="cv">${s.moveIncrease}</div><div class="cn">investor menambah posisi</div></div>
    <div class="chgStat new"><div class="cl">Masuk baru (new)</div><div class="cv">${s.moveNew}</div><div class="cn">pemegang saham baru</div></div>
    <div class="chgStat down"><div class="cl">Pengurangan (decrease)</div><div class="cv">${s.moveDecrease}</div><div class="cn">investor mengurangi posisi</div></div>
    <div class="chgStat exit"><div class="cl">Keluar (exit)</div><div class="cv">${s.moveExit}</div><div class="cn">pemegang saham keluar</div></div>
  </div>`;
  const bar=`<div class="filterBar">
    <input id="chgSearch" placeholder="Cari investor / emiten..." value="${chgFilter.q.replace(/"/g,'&quot;')}"/>
    <span class="fchip ${chgFilter.dir==='all'?'on':''}" data-dir="all">Semua (${changes.length})</span>
    <span class="fchip up ${chgFilter.dir==='up'?'on':''}" data-dir="up">▲ Naik/Baru</span>
    <span class="fchip down ${chgFilter.dir==='down'?'on':''}" data-dir="down">▼ Turun/Keluar</span>
    <span class="fchip new ${chgFilter.dir==='new'?'on':''}" data-dir="new">Baru</span>
    <span class="fchip exit ${chgFilter.dir==='exit'?'on':''}" data-dir="exit">Keluar</span>
    <span class="fchip ${chgFilter.dir==='gov'?'on':''}" data-dir="gov">Pemerintah</span>
    <span class="fchip ${chgFilter.dir==='foreign'?'on':''}" data-dir="foreign">Asing</span>
  </div>`;
  // apply filter
  let list=changes.slice();
  const q=chgFilter.q.trim().toUpperCase();
  if(q) list=list.filter(c=>(c.investor||'').toUpperCase().includes(q)||(c.ticker||'').toUpperCase().includes(q)||(c.issuer||'').toUpperCase().includes(q));
  if(chgFilter.dir==='up') list=list.filter(c=>c.direction==='BUY'||c.direction==='NEW');
  else if(chgFilter.dir==='down') list=list.filter(c=>c.direction==='SELL'||c.direction==='EXIT');
  else if(chgFilter.dir==='new') list=list.filter(c=>c.direction==='NEW');
  else if(chgFilter.dir==='exit') list=list.filter(c=>c.direction==='EXIT');
  else if(chgFilter.dir==='gov') list=list.filter(c=>c.isGovernment);
  else if(chgFilter.dir==='foreign') list=list.filter(c=>c.localForeign==='F');
  list.sort((a,b)=>Math.abs(b.deltaPct||b.magnitude)-Math.abs(a.deltaPct||a.magnitude));
  const shown=list.slice(0,300);
  const rows=shown.map(c=>{
    const grp=chgDirGroup(c.direction);
    const up=grp==='up';
    const arrow=up?'▲':'▼';
    const col=up?'var(--green)':'var(--red)';
    const dShares=Math.abs(c.deltaShares||0);
    const sharesTxt=(up?'+':'−')+fmtShares(dShares);
    const dpct=c.deltaPct!=null?c.deltaPct:(up?c.magnitude:-c.magnitude);
    const dpctTxt=(dpct>0?'+':'')+fmtPct(dpct);
    const afterTxt=c.latestPct!=null?fmtPct(c.latestPct):(c.direction==='EXIT'?'0% (keluar)':'—');
    const actionWord={BUY:'menambah',NEW:'masuk baru',SELL:'mengurangi',EXIT:'keluar'}[c.direction]||'';
    return `<tr>
      <td><span class="linkish" onclick="setSelected('${c.from}')">${shortLabel(c.investor,30)}</span><div class="miniShares">${actionWord}</div></td>
      <td><span class="linkish" onclick="setSelected('${c.to}')">${c.ticker}</span> <span class="muted">${shortLabel(c.issuer,20)}</span></td>
      <td>${dirTag(c.direction)}</td>
      <td style="color:${col};font-weight:750">${sharesTxt} <span class="miniShares">lembar</span></td>
      <td style="color:${col};font-weight:750">${dpctTxt}</td>
      <td><b>${afterTxt}</b></td>
      <td>${lfTag(c.localForeign)}${c.isGovernment?' <span class="tag gov">Gov</span>':''}</td>
      <td>${c.classification||'—'}</td></tr>`;
  }).join('');
  const table=`<div class="panel" style="box-shadow:none"><div class="panelHead"><h3>Perubahan kepemilikan</h3><span class="muted small">${list.length} hasil${list.length>300?' · menampilkan 300 teratas per besar perubahan':''}</span></div>
    <div class="tableWrap"><table class="tbl">${tableHtml(['Investor','Emiten','Aksi','Saham berubah','Δ Persentase','% Sesudah','Origin','Klasifikasi'],
      shown.length?[rows]:['<tr><td colspan="8" class="muted">Tidak ada perubahan cocok filter.</td></tr>'])}</table></div></div>`;
  host.innerHTML=hero+bar+table;
  // wire filter
  const si=document.getElementById('chgSearch');
  si.oninput=e=>{ chgFilter.q=e.target.value; const pos=e.target.selectionStart; renderChanges();
    const ns=document.getElementById('chgSearch'); if(ns){ns.focus(); ns.setSelectionRange(pos,pos);} };
  host.querySelectorAll('.fchip[data-dir]').forEach(ch=>ch.onclick=()=>{chgFilter.dir=ch.dataset.dir; renderChanges();});
}

/* ============ INSTITUTIONAL LAYER ============ */
let instFilter='all';
function renderInstitutions(){
  const host=document.getElementById('instBody');
  const top=institutions.top||[];
  const flow=institutions.flow||{buy:[],sell:[]};
  // class distribution
  const byCls={};
  top.forEach(t=>byCls[t.cls||'Lainnya']=(byCls[t.cls||'Lainnya']||0)+1);
  const foreignCount=top.filter(t=>t.foreign).length;
  const totalIssuersHeld=new Set();
  top.forEach(t=>(t.topIssuers||[]).forEach(i=>totalIssuersHeld.add(i.ticker)));
  const hero=`<div class="instHero">
    <div class="govStat" style="border-color:rgba(58,160,255,.3);background:linear-gradient(180deg,rgba(58,160,255,.08),transparent)"><div class="gl" style="color:#7bb6ff">Institusi terlacak</div><div class="gv">${top.length}</div><div class="gn">pemegang institusional teratas</div></div>
    <div class="govStat" style="border-color:rgba(53,208,111,.3);background:linear-gradient(180deg,rgba(53,208,111,.08),transparent)"><div class="gl" style="color:#5fe08d">Beli/tambah institusi</div><div class="gv">${flow.buy.length}</div><div class="gn">aksi akumulasi institusional</div></div>
    <div class="govStat" style="border-color:rgba(255,92,103,.3);background:linear-gradient(180deg,rgba(255,92,103,.08),transparent)"><div class="gl" style="color:#ff8087">Jual/kurang institusi</div><div class="gv">${flow.sell.length}</div><div class="gn">aksi distribusi institusional</div></div>
    <div class="govStat" style="border-color:rgba(155,108,255,.3);background:linear-gradient(180deg,rgba(155,108,255,.08),transparent)"><div class="gl" style="color:#b699ff">Institusi asing</div><div class="gv">${foreignCount}</div><div class="gn">dari ${top.length} institusi teratas</div></div>
  </div>`;
  // filter chips by class
  const classes=Object.keys(byCls).sort((a,b)=>byCls[b]-byCls[a]);
  const chips=`<div class="filterBar"><span class="fchip ${instFilter==='all'?'on':''}" data-cls="all">Semua (${top.length})</span>`+
    classes.map(c=>`<span class="fchip ${instFilter===c?'on':''}" data-cls="${c}">${c} (${byCls[c]})</span>`).join('')+`</div>`;
  const flist=instFilter==='all'?top:top.filter(t=>t.cls===instFilter);
  const cards=flist.slice(0,48).map(t=>{
    const ini=(t.label||'?').replace(/[^A-Za-z ]/g,'').split(' ').filter(Boolean).slice(0,2).map(w=>w[0]).join('').toUpperCase()||'IN';
    const pills=(t.topIssuers||[]).slice(0,5).map(i=>`<span class="ip" onclick="setSelected('${i.key}')">${i.ticker} ${fmtPct(i.pct)}</span>`).join('');
    return `<div class="instCard">
      <div class="instTop"><div class="instBadge ${t.foreign?'frn':''}">${ini}</div>
        <div><div class="instName" onclick="setSelected('${t.key}')">${shortLabel(t.label,28)}</div><div class="instMeta">${t.cls||'Institusi'}${t.foreign?' · Foreign':' · Local'}</div></div></div>
      <div class="instFig"><span class="f"><b>${t.holdingCount}</b> emiten</span><span class="f"><b>${fmtPct(t.pts)}</b> total pts</span><span class="f">terbesar <b>${t.largestTicker}</b></span></div>
      <div class="instPills">${pills}</div></div>`;
  }).join('');
  const grid=`<div class="panel" style="box-shadow:none"><div class="panelHead"><h3>Pemegang institusional teratas</h3><span class="muted small">${flist.length} institusi · urut per jumlah emiten</span></div>
    <div class="panelBody"><div class="instGrid">${cards}</div></div></div>`;
  // flow tables
  const buyRows=flow.buy.slice(0,20).map(c=>`<tr><td>${shortLabel(c.investor,26)}</td><td><span class="linkish" onclick="setSelected('E:${c.ticker}')">${c.ticker}</span></td><td>${dirTag(c.direction)}</td><td><b>${fmtPct(c.magnitude)}</b></td><td>${c.cls}</td></tr>`).join('');
  const sellRows=flow.sell.slice(0,20).map(c=>`<tr><td>${shortLabel(c.investor,26)}</td><td><span class="linkish" onclick="setSelected('E:${c.ticker}')">${c.ticker}</span></td><td>${dirTag(c.direction)}</td><td><b>${fmtPct(c.magnitude)}</b></td><td>${c.cls}</td></tr>`).join('');
  const flowTables=`<div class="twoCols" style="margin-top:16px">
    <div class="panel" style="box-shadow:none"><div class="panelHead"><h3>Akumulasi institusi</h3><span class="muted small">Top beli/tambah</span></div><div class="tableWrap"><table class="tbl">${tableHtml(['Institusi','Emiten','Arah','Mag','Kelas'], buyRows?[buyRows]:['<tr><td colspan=5 class=muted>—</td></tr>'])}</table></div></div>
    <div class="panel" style="box-shadow:none"><div class="panelHead"><h3>Distribusi institusi</h3><span class="muted small">Top jual/kurang</span></div><div class="tableWrap"><table class="tbl">${tableHtml(['Institusi','Emiten','Arah','Mag','Kelas'], sellRows?[sellRows]:['<tr><td colspan=5 class=muted>—</td></tr>'])}</table></div></div>
  </div>`;
  host.innerHTML=hero+chips+grid+flowTables;
  host.querySelectorAll('.fchip[data-cls]').forEach(ch=>ch.onclick=()=>{instFilter=ch.dataset.cls; renderInstitutions();});
}

/* ============ SHADOW NETWORK (shared holders across issuers) ============ */
let shadowFilter='';
function renderShadow(){
  const host=document.getElementById('shadowBody');
  const sh=shadow.sharedHolders||[];
  const intro=`<div class="readCard affil" style="margin-bottom:14px"><h4>Jaringan Kepemilikan Bayangan</h4>
    <p>${shadow.connectorCount} pemegang saham muncul di lebih dari satu emiten — "jaringan penghubung" yang mengungkap afiliasi tersembunyi antar emiten, meski tak ada rantai kepemilikan langsung. Ini cara melacak grup yang kepemilikannya terpecah/bayangan: bila beberapa emiten berbagi pemegang saham yang sama, kemungkinan ada keterkaitan pengendali.</p></div>`;
  const bar=`<div class="filterBar"><input id="shadowSearch" placeholder="Cari nama pemegang saham penghubung..." value="${shadowFilter.replace(/"/g,'&quot;')}"/></div>`;
  let list=sh;
  const q=shadowFilter.trim().toUpperCase();
  if(q) list=list.filter(s=>(s.holder||'').toUpperCase().includes(q)||(s.issuers||[]).some(i=>(i.ticker||'').toUpperCase().includes(q)));
  const cards=list.slice(0,60).map(s=>{
    const pills=s.issuers.slice(0,12).map(i=>`<span class="gpill" onclick="setSelected('${i.key}')">${i.ticker}<span class="pp">${fmtPct(i.pct)}</span></span>`).join('');
    return `<div class="groupCard">
      <div class="groupCardHead" style="background:linear-gradient(90deg,rgba(246,194,71,.1),transparent)">
        <h3>${shortLabel(s.holder,40)}</h3>
        <div class="gc">${s.count} emiten terhubung · ${s.cls||'—'}${s.isGov?' · Pemerintah':''}</div></div>
      <div class="groupCardBody"><div class="gsub">Emiten yang dipegang bersama</div><div class="gpills">${pills}${s.issuers.length>12?`<span class="gpill" style="cursor:default;color:var(--muted2)">+${s.issuers.length-12}</span>`:''}</div></div></div>`;
  }).join('');
  host.innerHTML=intro+bar+`<div class="groupGrid">${cards||'<p class="muted">Tidak ada penghubung cocok.</p>'}</div>`;
  const si=document.getElementById('shadowSearch');
  si.oninput=e=>{ shadowFilter=e.target.value; const pos=e.target.selectionStart; renderShadow();
    const ns=document.getElementById('shadowSearch'); if(ns){ns.focus(); ns.setSelectionRange(pos,pos);} };
}

/* ============ CONGLOMERATE GROUPS ============ */
function renderGroups(){
  const host=document.getElementById('groupBody');
  const arr=Object.values(groups).sort((a,b)=>(b.reachIssuers||b.issuerCount)-(a.reachIssuers||a.issuerCount));
  if(!arr.length){ host.innerHTML='<div class="readCard"><p class="muted">Tidak ada grup konglomerat terdeteksi.</p></div>'; return; }
  const intro=`<div class="readCard intel" style="margin-bottom:14px"><h4>Peta Grup Konglomerat</h4><p>${arr.length} grup dibangun dari <b>kepemilikan saham aktual</b> oleh entitas pengendali spesifik. Emiten <b>terkendali</b> = anchor memegang ≥20%; <b>diduga terafiliasi</b> = pengendali resmi opaque (offshore/holdco) tapi anchor grup ikut memegang saham (mis. BUMI); <b>minor</b> = 1–20%. Kepemilikan lewat <b>bank/sekuritas dikecualikan</b> karena berpotensi saham repo/titipan nasabah, bukan kendali grup.</p></div>`;
  const cards=arr.map(g=>{
    const ctrl=g.issuers.slice(0,16).map(i=>`<span class="gpill" onclick="setSelected('${i.key}')">${i.ticker}<span class="pp">${fmtPct(i.controllerPct)}</span></span>`).join('');
    const suspList=(g.suspected||[]);
    const susp=suspList.slice(0,12).map(h=>`<span class="gpill suspect" onclick="setSelected('${h.key}')" title="Pengendali resmi opaque; ${h.holder} ikut memegang ${fmtPct(h.pct)}">${h.ticker}<span class="pp" style="color:#ffb060">${fmtPct(h.pct)}</span></span>`).join('');
    const minorList=(g.holdings||[]).filter(h=>!h.controlling && !h.suspected);
    const minor=minorList.slice(0,20).map(h=>`<span class="gpill" style="border-style:dotted" onclick="setSelected('${h.key}')" title="via ${h.holder}">${h.ticker}<span class="pp" style="color:#8fb8ff">${fmtPct(h.pct)}</span></span>`).join('');
    const anchors=g.anchors.filter(a=>a.kind==='investor').slice(0,6).map(a=>`<span class="gpill anchor" onclick="setSelected('${a.key}')">${shortLabel(a.label,20)}</span>`).join('');
    return `<div class="groupCard" id="grp-${cssId(g.label)}">
      <div class="groupCardHead"><h3>${g.label}</h3><div class="gc">${g.issuerCount} terkendali (≥20%)${suspList.length?` · ${suspList.length} diduga terafiliasi`:''} · ${minorList.length} minor</div></div>
      <div class="groupCardBody">
        <div class="gsub">Emiten terkendali (≥20%)</div><div class="gpills">${ctrl||'<span class="muted small">—</span>'}</div>
        ${susp?`<div class="gsub" style="color:#e0973e">⚠ Diduga terafiliasi (pengendali resmi opaque)</div><div class="gpills">${susp}</div>`:''}
        ${minor?`<div class="gsub">Kepemilikan minor (1–20%)</div><div class="gpills">${minor}${minorList.length>20?`<span class="gpill" style="cursor:default;color:var(--muted2)">+${minorList.length-20} lagi</span>`:''}</div>`:''}
        ${anchors?`<div class="gsub">Induk / tokoh pengendali</div><div class="gpills">${anchors}</div>`:''}
      </div></div>`;
  }).join('');
  host.innerHTML=intro+`<div class="groupGrid">${cards}</div>`;
}

/* ============ GOVERNMENT / STATE OWNERSHIP ANALYSIS ============ */
function renderGov(){
  const host=document.getElementById('govBody');
  // aggregate by issuer: total state stake, and classify control depth
  const byIssuer={};
  govHoldings.forEach(g=>{
    const k='E:'+g.ticker;
    (byIssuer[k]=byIssuer[k]||{ticker:g.ticker,issuer:g.issuer,pct:0,holders:[],cls:new Set()});
    byIssuer[k].pct+=g.pct; byIssuer[k].holders.push(g); byIssuer[k].cls.add(g.classification||'—');
  });
  const issuerArr=Object.values(byIssuer).map(x=>({...x,pct:Math.round(x.pct*100)/100}));
  // control depth: state majority / state significant / state minority
  const majority=issuerArr.filter(x=>x.pct>=50);
  const significant=issuerArr.filter(x=>x.pct>=20&&x.pct<50);
  const minority=issuerArr.filter(x=>x.pct<20);
  // by classification aggregated by VALUE (sum of pct) not count
  const clsAgg={};
  govHoldings.forEach(g=>{ const c=g.classification||'Lainnya'; (clsAgg[c]=clsAgg[c]||{count:0,pts:0,issuers:new Set()}); clsAgg[c].count++; clsAgg[c].pts+=g.pct; clsAgg[c].issuers.add(g.ticker); });
  const clsEntries=Object.entries(clsAgg).sort((a,b)=>b[1].pts-a[1].pts);
  const palette=['#f6c247','#35d06f','#3aa0ff','#9b6cff','#21d4c4','#ff5c67','#e8894a','#8fa09a','#c77dff','#5ad1c4','#d98cff'];
  // entity aggregation
  const byInv={};
  govHoldings.forEach(g=>{ byInv[g.investor]=(byInv[g.investor]||{count:0,pts:0,cls:g.classification,foreign:g.local_foreign==='F',dom:g.domicile}); byInv[g.investor].count++; byInv[g.investor].pts+=g.pct; });
  const topInv=Object.entries(byInv).sort((a,b)=>b[1].pts-a[1].pts).slice(0,8);

  const hero=`<div class="readCard intel" style="margin-bottom:16px;border-color:rgba(246,194,71,.35)"><h4>Analisis Kepemilikan Negara</h4><p>Memetakan seberapa dalam negara mengendalikan emiten — bukan sekadar mendata. Negara hadir di <b>${issuerArr.length}</b> emiten: <b>${majority.length}</b> dikendalikan mayoritas (≥50%), <b>${significant.length}</b> kepemilikan signifikan (20–50%), <b>${minority.length}</b> minoritas (&lt;20%). Sumber: entitas berflag pemerintah (BUMN, SWF, dana pensiun negara).</p></div>`;

  const hero2=`<div class="govHero">
    <div class="govStat"><div class="gl">Kendali mayoritas negara</div><div class="gv">${majority.length}</div><div class="gn">emiten ≥50% milik negara</div></div>
    <div class="govStat"><div class="gl">Kepemilikan signifikan</div><div class="gv">${significant.length}</div><div class="gn">20–50% (blokir/pengaruh)</div></div>
    <div class="govStat"><div class="gl">Entitas negara unik</div><div class="gv">${Object.keys(byInv).length}</div><div class="gn">BUMN, SWF, dana pensiun</div></div>
    <div class="govStat"><div class="gl">SWF asing</div><div class="gv">${Object.values(byInv).filter(v=>v.foreign).length}</div><div class="gn">sovereign fund luar negeri</div></div>
  </div>`;

  // classification bar by VALUE
  const totalPts=clsEntries.reduce((a,c)=>a+c[1].pts,0)||1;
  const barSeg=clsEntries.map((c,i)=>`<span style="width:${(c[1].pts/totalPts*100).toFixed(1)}%;background:${palette[i%palette.length]}" title="${c[0]}: ${fmtPct(c[1].pts)} across ${c[1].issuers.size} emiten"></span>`).join('');
  const barLeg=clsEntries.map((c,i)=>`<span class="li"><span class="sw" style="background:${palette[i%palette.length]}"></span>${c[0]} · ${c[1].issuers.size} emiten</span>`).join('');

  const entRows=topInv.map(t=>{
    const ini=(t[0]||'?').replace(/[^A-Za-z ]/g,'').split(' ').filter(Boolean).slice(0,2).map(w=>w[0]).join('').toUpperCase()||'GV';
    return `<div class="govEntity"><div class="govAvatar">${ini}</div>
      <div class="ge"><div class="gname">${shortLabel(t[0],32)}</div><div class="gmeta">${t[1].cls||'Entitas negara'}${t[1].foreign?' · '+(t[1].dom||'Foreign'):''}</div></div>
      <div class="gright"><div class="gbig">${fmtPct(t[1].pts)}</div><div class="gsmall">${t[1].count} emiten</div></div></div>`;
  }).join('');

  const split=`<div class="govSplit">
    <div class="panel" style="box-shadow:none"><div class="panelHead"><h3>Bobot per tipe entitas negara</h3><span class="muted small">Berdasar total stake, bukan jumlah</span></div>
      <div class="panelBody"><div class="mbar" style="height:12px">${barSeg}</div><div class="blegend">${barLeg}</div></div></div>
    <div class="panel" style="box-shadow:none"><div class="panelHead"><h3>Entitas negara terbesar</h3><span class="muted small">Per total stake</span></div>
      <div class="panelBody" style="padding-top:12px">${entRows}</div></div>
  </div>`;

  // control-depth table (aggregated per issuer)
  const depthTable=(list,title,note)=>`<div class="panel" style="margin-top:16px;box-shadow:none"><div class="panelHead"><h3>${title}</h3><span class="muted small">${note}</span></div>
    <div class="tableWrap"><table class="tbl">${tableHtml(['Emiten','Total stake negara','Pemegang negara','Klasifikasi'],
      list.length?list.sort((a,b)=>b.pct-a.pct).slice(0,15).map(x=>`<tr>
        <td><span class="linkish" onclick="setSelected('E:${x.ticker}')">${x.ticker}</span> <span class="muted">${shortLabel(x.issuer,26)}</span></td>
        <td><b>${fmtPct(x.pct)}</b></td>
        <td>${x.holders.length}: ${shortLabel(x.holders.sort((a,b)=>b.pct-a.pct)[0].investor,26)}${x.holders.length>1?` +${x.holders.length-1}`:''}</td>
        <td>${[...x.cls].slice(0,2).join(', ')}</td></tr>`):['<tr><td colspan=4 class=muted>—</td></tr>'])}</table></div></div>`;

  host.innerHTML=hero+hero2+split
    +depthTable(majority,'Emiten dikendalikan mayoritas negara (≥50%)','BUMN inti / kendali penuh')
    +depthTable(significant,'Kepemilikan negara signifikan (20–50%)','Pengaruh besar / hak veto');
}
function donutSvg(segs,total){
  const R=54,SW=22,C=2*Math.PI*R, cx=66,cy=66; let off=0;
  const arcs=segs.map(s=>{
    const frac=s.value/total; const len=frac*C;
    const el=`<circle cx="${cx}" cy="${cy}" r="${R}" fill="none" stroke="${s.color}" stroke-width="${SW}" stroke-dasharray="${len.toFixed(2)} ${(C-len).toFixed(2)}" stroke-dashoffset="${(-off).toFixed(2)}" transform="rotate(-90 ${cx} ${cy})"/>`;
    off+=len; return el;
  }).join('');
  const leg=segs.map(s=>`<div class="dl"><span class="dsw" style="background:${s.color}"></span>${s.label}<span class="dpc">${s.value}</span></div>`).join('');
  return `<svg class="donutSvg" width="132" height="132" viewBox="0 0 132 132">${arcs}<text x="66" y="61" text-anchor="middle" fill="#e7efe9" font-size="22" font-weight="800">${total}</text><text x="66" y="79" text-anchor="middle" fill="#8fa09a" font-size="10">posisi</text></svg><div class="donutLeg">${leg}</div>`;
}

/* ============ AUDIT ============ */
function renderAudit(){
  const audit=audits[selectedKey];
  const host=document.getElementById('auditBody');
  if(!audit){ host.innerHTML='<div class="readCard"><p class="muted">Issuer audit is only available for listed issuers. Select an issuer (ticker) to view its concentration and quality metrics.</p></div>'; return; }
  const rows=[
    ['Disclosed holders', audit.holders, 'Rows in latest file'],
    ['Coverage', fmtPct(audit.coverage), 'Sum of disclosed stakes'],
    ['Residual proxy', fmtPct(audit.residual), '100% − coverage'],
    ['Top-1 / Top-3 / Top-5', `${fmtPct(audit.top1)} / ${fmtPct(audit.top3)} / ${fmtPct(audit.top5)}`, 'Concentration'],
    ['HHI', audit.hhi, 'Herfindahl index'],
    ['Nakamoto-50', audit.nakamoto50+' holders', 'Holders to reach 50%'],
    ['Government stake', fmtPct(audit.govPct), 'Disclosed SOE / gov'],
    ['Foreign / Local / Unknown', `${fmtPct(audit.foreignPct)} / ${fmtPct(audit.localPct)} / ${fmtPct(audit.unknownPct)}`, 'Origin split'],
    ['Scrip ratio', (audit.scripRatio*100).toFixed(1)+'%', 'Scrip / total shares'],
    ['Float proxy', fmtPct(audit.floatProxy), 'Residual + small holders'],
  ];
  const palette={Foreign:'#3aa0ff',Local:'#35d06f',Unknown:'#8fa09a'};
  const tot=Math.max(1,audit.foreignPct+audit.localPct+audit.unknownPct);
  const bar=`<span style="width:${audit.localPct/tot*100}%;background:${palette.Local}"></span><span style="width:${audit.foreignPct/tot*100}%;background:${palette.Foreign}"></span><span style="width:${audit.unknownPct/tot*100}%;background:${palette.Unknown}"></span>`;
  const ctrlBlock=audit.controller?`<div style="margin-bottom:16px">${controllerCard(audit)}</div>`:'';
  const famBlock=(audit.families&&audit.families.length)?`<div style="margin-bottom:16px">${familyCard(audit)}</div>`:'';
  const affBlock=(audit.topAffiliations&&audit.topAffiliations.length)?`<div style="margin-bottom:16px">${affiliationCard(audit)}</div>`:'';
  host.innerHTML=ctrlBlock+famBlock+affBlock+`<div class="twoCols"><div class="readCard intel"><h4>${audit.ticker} · ${shortLabel(audit.issuer,30)}</h4><p>Control: <b>${audit.controlLabel}</b>. Network risk <b class="${audit.riskLabel==='High'?'red':audit.riskLabel==='Medium'?'amber':'green'}">${audit.riskLabel}</b> (${audit.riskScore}/100). Confidence ${audit.confidence}/100.</p><div class="mbar" style="margin-top:10px">${bar}</div><div class="blegend"><span class="li"><span class="sw" style="background:${palette.Local}"></span>Local ${fmtPct(audit.localPct)}</span><span class="li"><span class="sw" style="background:${palette.Foreign}"></span>Foreign ${fmtPct(audit.foreignPct)}</span><span class="li"><span class="sw" style="background:${palette.Unknown}"></span>Unknown ${fmtPct(audit.unknownPct)}</span></div></div><div class="readCard"><h4>Proxy interpretation</h4><p>Residual proxy is not official free float. High residual means many holders sit outside the disclosed table. A high scrip ratio lowers classification completeness because scrip data comes from eBAE.</p></div></div><div class="tableWrap" style="margin-top:16px"><table class="tbl">${tableHtml(['Metric','Value','Meaning'], rows.map(r=>`<tr><td>${r[0]}</td><td><b>${r[1]}</b></td><td class="muted">${r[2]}</td></tr>`))}</table></div>`;
}

/* ============ PROXY PATHS ============ */
function dfsUp(key, maxDepth=4){
  const paths=[];
  function walk(cur, path, eff, d){
    const incoming=filteredEdges(inEdges[cur]||[]);
    if(d>=maxDepth || incoming.length===0){ if(path.length) paths.push({path:[...path], eff}); return; }
    for(const e of incoming){ if(path.some(p=>p.key===e.from)) continue;
      const step={key:e.from,label:ent(e.from).ticker||ent(e.from).label,pct:e.pct};
      walk(e.from, [step,...path], eff*(e.pct/100), d+1); }
  }
  walk(key, [], 1, 0); return paths.sort((a,b)=>b.eff-a.eff).slice(0,25);
}
function dfsDown(key,maxDepth=4){
  const paths=[];
  function walk(cur,path,eff,d){
    const outgoing=filteredEdges(outEdges[cur]||[]);
    if(d>=maxDepth || outgoing.length===0){ if(path.length) paths.push({path:[...path], eff}); return; }
    for(const e of outgoing){ if(path.some(p=>p.key===e.to)) continue;
      const step={key:e.to,label:ent(e.to).ticker||ent(e.to).label,pct:e.pct};
      walk(e.to,[...path,step],eff*(e.pct/100),d+1); }
  }
  walk(key,[],1,0); return paths.sort((a,b)=>b.eff-a.eff).slice(0,25);
}
function renderPaths(){
  const up=dfsUp(selectedKey, depth), down=dfsDown(selectedKey, depth);
  const label=ent(selectedKey).ticker||shortLabel(ent(selectedKey).label,16);
  document.getElementById('upstreamPaths').innerHTML=up.length?up.map(p=>`<div class="pathItem"><div><div class="path">${p.path.map(x=>`<span class="linkish" onclick="setSelected('${x.key}')">${x.label}</span> <span class="muted">(${fmtPct(x.pct)})</span>`).join(' → ')} → <b>${label}</b></div><div class="muted small">Effective path stake</div></div><div class="eff">${fmtPct(p.eff*100)}</div></div>`).join(''):'<p class="muted">No upstream path under current depth/filter.</p>';
  document.getElementById('downstreamPaths').innerHTML=down.length?down.map(p=>`<div class="pathItem"><div><div class="path"><b>${label}</b> → ${p.path.map(x=>`<span class="linkish" onclick="setSelected('${x.key}')">${x.label}</span> <span class="muted">(${fmtPct(x.pct)})</span>`).join(' → ')}</div><div class="muted small">Effective path stake</div></div><div class="eff">${fmtPct(p.eff*100)}</div></div>`).join(''):'<p class="muted">No downstream path under current depth/filter.</p>';
}
function exportChanges(){
  const rel=relatedChanges();
  const header=['direction','investor','ticker','issuer','latest_pct','delta_shares','magnitude','origin','government'];
  const rowsArr=rel.map(c=>[c.direction,c.investor,c.ticker,c.issuer,c.latestPct,c.deltaShares,c.magnitude,c.localForeign,c.isGovernment]);
  const csv=[header,...rowsArr].map(r=>r.map(v=>`"${String(v==null?'':v).replace(/"/g,'""')}"`).join(',')).join('\n');
  const blob=new Blob([csv],{type:'text/csv'}); const a=document.createElement('a'); a.href=URL.createObjectURL(blob); a.download='avenir_changes_selected.csv'; a.click();
}

function renderAll(){renderKpis(); renderNetwork(true); renderInsightCards(); renderTables(); renderChanges(); renderGroups(); renderGov(); renderInstitutions(); renderShadow(); renderAudit(); renderPaths();}
initSearch(); initControls(); renderAll();
window.setSelected=setSelected; window.switchTab=switchTab; window.focusGroup=focusGroup;

    
    initSearch();
    initControls();
    renderKpis();
    switchTab('networkPane');


    const originalSetSelected = window.setSelected || (typeof setSelected === 'function' ? setSelected : null);
    if (originalSetSelected) {
        window.setSelected = function(k) {
            originalSetSelected(k);
            
            // Look for proxyPane or auditPane and inject our manual images!
            const proxyBody = document.getElementById('upstreamPaths');
            const manualInputs = window.manualInputs || {};
            
            const ent = DATA.entities && DATA.entities[k] ? DATA.entities[k] : { ticker: '' };
            const ticker = ent.ticker || k.replace('E:', '');
            
            if (proxyBody) {
                // Check if there is manual image
                const manual = manualInputs[ticker];
                let manualHtml = '';
                
                if (manual && manual.ubo_image_path) {
                    manualHtml += `<div class="panel" style="box-shadow:none; margin-top:16px;">
                        <div class="panelHead">
                            <h3 class="text-amber-500">Official UBO Structure (Annual Report)</h3>
                        </div>
                        <div class="panelBody">
                            <img src="/storage/${manual.ubo_image_path}" alt="UBO Structure" style="max-width:100%; border-radius: 8px; border: 1px solid #2a3a33;" />
                        </div>
                    </div>`;
                }
                
                if (manual && manual.shareholder_image_path) {
                    manualHtml += `<div class="panel" style="box-shadow:none; margin-top:16px;">
                        <div class="panelHead">
                            <h3 class="text-amber-500">Official Shareholder Composition (Annual Report)</h3>
                        </div>
                        <div class="panelBody">
                            <img src="/storage/${manual.shareholder_image_path}" alt="Shareholders" style="max-width:100%; border-radius: 8px; border: 1px solid #2a3a33;" />
                        </div>
                    </div>`;
                }
                
                // If there's an existing manual container, update it, else append
                let container = document.getElementById('manual-ubo-container');
                if (!container) {
                    container = document.createElement('div');
                    container.id = 'manual-ubo-container';
                    // Let's insert it before the upstreamPaths or inside proxyPane
                    const proxyPane = document.getElementById('proxyPane');
                    if(proxyPane) {
                        const pb = proxyPane.querySelector('.panelBody');
                        if (pb) pb.appendChild(container);
                    }
                }
                container.innerHTML = manualHtml;
            }
        };
        
        // Trigger for initial selection
        if (selectedKey) {
            window.setSelected(selectedKey);
        }
    }

}
</script>

<style>

:root{
  --bg:#070b0a; --bg2:#0a1110; --panel:#101816; --panel2:#0d1413;
  --line:#23322d; --line2:#1a2622;
  --text:#e7efe9; --text2:#cdd8d4; --muted:#8fa09a; --muted2:#6f827b;
  --green:#35d06f; --green2:#1ca95a; --greenSoft:rgba(53,208,111,.12);
  --brand:#35d06f; --brandSoft:rgba(53,208,111,.14);
  --red:#ff5c67; --redSoft:rgba(255,92,103,.12);
  --amber:#f6c247; --amberSoft:rgba(246,194,71,.13);
  --violet:#9b6cff; --violetSoft:rgba(155,108,255,.14);
  --teal:#21d4c4; --tealSoft:rgba(33,212,196,.12); --cyan:#3aa0ff; --blue:#3aa0ff;
  --shadow:0 20px 60px rgba(0,0,0,.35);
  --shadowSm:0 8px 24px rgba(0,0,0,.28);
  --radius:16px; --radiusSm:11px;
}
*{box-sizing:border-box}
html,body{max-width:100%}
body{margin:0;
  background:radial-gradient(1400px 700px at 60% -20%,#173023 0%,#08100d 35%,#050706 100%);
  color:var(--text);
  font-family:"Inter",ui-sans-serif,system-ui,-apple-system,"Segoe UI",Roboto,Arial,sans-serif;
  font-size:14px;line-height:1.5;-webkit-font-smoothing:antialiased}
.app{display:grid;grid-template-columns:256px 1fr;min-height:100vh}

/* Sidebar */
.side{border-right:1px solid var(--line);background:rgba(7,10,9,.86);backdrop-filter:blur(12px);
  padding:22px 16px;position:sticky;top:0;height:100vh;overflow:auto}
.brand{display:flex;align-items:center;gap:11px;margin-bottom:26px;padding:0 6px}
.logo{width:40px;height:40px;border-radius:12px;
  background:linear-gradient(135deg,#46ef82,#0d7e3f);display:grid;place-items:center;
  color:#001b0b;font-weight:900;font-size:22px}
.brand h1{font-size:16px;letter-spacing:.16em;margin:0;font-weight:800}
.brand p{margin:0;color:var(--muted);font-size:10px;letter-spacing:.2em;font-weight:600}
.navTitle{color:var(--muted2);font-size:10.5px;text-transform:uppercase;
  letter-spacing:.13em;margin:18px 10px 8px;font-weight:700}
.nav a{display:flex;align-items:center;gap:11px;padding:10px 12px;border-radius:11px;
  color:var(--text2);text-decoration:none;margin:3px 0;font-weight:550;font-size:13.5px;
  cursor:pointer;transition:background .12s,color .12s;border:1px solid transparent}
.nav a:hover{background:var(--panel2)}
.nav a.active{background:linear-gradient(90deg,rgba(53,208,111,.22),rgba(53,208,111,.06));
  color:#6dff9d;border:1px solid rgba(53,208,111,.22);font-weight:650}
.nav a .ic{width:18px;text-align:center;font-size:15px;opacity:.9}
.dataCard{margin-top:20px;border:1px solid var(--line);
  background:linear-gradient(180deg,rgba(18,29,25,.92),rgba(8,13,12,.9));
  border-radius:14px;padding:15px}
.dataCard .k{font-size:11.5px;color:var(--muted);font-weight:550}
.dataCard .v{font-size:16px;font-weight:700;margin-top:3px}
.dataCard.info{border-color:rgba(53,208,111,.25)}
.dataCard.info .k{color:#6dff9d;font-weight:700}
.dataCard.info p{margin:6px 0 0;font-size:11.5px;color:var(--text2);line-height:1.5}

/* Main */
.main{padding:22px 28px 40px;min-width:0}
.topbar{display:flex;align-items:center;gap:16px;margin-bottom:16px}
.topbar .tabs{display:flex;gap:22px}
.topbar .tabs span{color:var(--muted);font-size:13.5px;font-weight:550;cursor:default}
.topbar .tabs .on{color:var(--green);font-weight:700;border-bottom:2px solid var(--green);padding-bottom:6px}
.search{margin-left:auto;position:relative}
.search input{width:340px;background:#0c1211;border:1px solid var(--line);
  border-radius:11px;color:var(--text);padding:11px 44px 11px 14px;font-size:13.5px;
  transition:border-color .12s,box-shadow .12s}
.search input:focus{outline:none;border-color:var(--green2);box-shadow:0 0 0 3px var(--greenSoft)}
.search input::placeholder{color:var(--muted2)}
.search button{position:absolute;right:6px;top:6px;border:0;background:#18231f;
  color:var(--green);border-radius:8px;padding:7px 12px;font-weight:600;font-size:12.5px;cursor:pointer}
.search button:hover{background:#1f2f28}

.header{display:flex;align-items:flex-end;justify-content:space-between;margin:6px 0 18px;gap:16px;flex-wrap:wrap}
.eyebrow{color:var(--green);font-size:12px;font-weight:700;letter-spacing:.06em;text-transform:uppercase}
.title{font-size:30px;line-height:1.1;margin:5px 0;font-weight:800;letter-spacing:-.02em}
.sub{color:var(--muted);font-size:14px;max-width:760px}
.actions{display:flex;gap:9px}
.btn{background:#111816;border:1px solid var(--line);color:var(--text2);
  border-radius:10px;padding:9px 14px;cursor:pointer;font-weight:600;font-size:13px;
  transition:background .12s,border-color .12s}
.btn:hover{background:#16201c;border-color:var(--green2)}
.btn.primary{background:linear-gradient(135deg,#1ea95b,#42e579);color:#001b0a;border:0;font-weight:800}
.btn.primary:hover{filter:brightness(1.06)}

/* Selector */
.selectorPanel{border:1px solid var(--line);
  background:linear-gradient(180deg,rgba(18,28,25,.86),rgba(8,13,12,.88));
  border-radius:var(--radius);padding:16px;box-shadow:var(--shadow);margin-bottom:16px}
.selectorGrid{display:grid;grid-template-columns:1fr 170px 150px 170px;gap:12px;align-items:end}
.field label{display:block;color:var(--muted);font-size:11.5px;margin-bottom:6px;font-weight:600}
.field input,.field select{width:100%;background:#0a100f;border:1px solid #26352f;
  border-radius:10px;color:var(--text);padding:10px 12px;font-size:13.5px;font-family:inherit;
  transition:border-color .12s,box-shadow .12s}
.field input:focus,.field select:focus{outline:none;border-color:var(--green2);box-shadow:0 0 0 3px var(--greenSoft)}
.field input[readonly]{background:#0c1312;font-weight:650;color:var(--text)}
.chips{display:flex;gap:8px;flex-wrap:wrap;margin-top:12px}
.chip{background:#111a18;border:1px solid var(--line);padding:7px 12px;border-radius:999px;
  color:var(--text2);cursor:pointer;font-size:12.5px;font-weight:600;transition:all .12s}
.chip:hover{border-color:var(--green2);color:#6dff9d}
.chip.on{border-color:rgba(53,208,111,.6);background:var(--greenSoft);color:#6dff9d}

/* KPIs */
.gridKpi{display:grid;grid-template-columns:repeat(6,1fr);gap:12px;margin:16px 0}
.kpi{border:1px solid var(--line);border-radius:14px;background:rgba(13,20,18,.84);padding:15px;min-height:96px}
.kpi .label{color:var(--muted);font-size:11.5px;font-weight:600}
.kpi .num{font-size:23px;font-weight:850;margin-top:9px;letter-spacing:-.02em}
.kpi .note{color:var(--muted2);font-size:11.5px;margin-top:5px}
.green{color:var(--green)} .red{color:var(--red)} .brandc{color:var(--green)}
.amber{color:var(--amber)} .violet{color:var(--violet)} .teal{color:var(--teal)} .blue{color:var(--blue)}

/* Panels */
.panel{border:1px solid var(--line);
  background:linear-gradient(180deg,rgba(16,24,22,.9),rgba(9,14,13,.92));
  border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden}
.panelHead{display:flex;align-items:center;justify-content:space-between;padding:15px 18px;
  border-bottom:1px solid rgba(35,50,45,.7);gap:12px;flex-wrap:wrap}
.panelHead h3{margin:0;font-size:15.5px;font-weight:700;letter-spacing:-.01em}
.panelBody{padding:16px 18px}
.muted{color:var(--muted)} .small{font-size:12px}

.layout{display:block}
.netInsightRow{display:grid;grid-template-columns:1fr;gap:16px;padding:16px 18px 0}
@media(min-width:1200px){.netInsightRow{grid-template-columns:1fr 1fr}}

/* Network */
.networkWrap{height:600px;position:relative;border-radius:0;overflow:hidden;touch-action:none;
  cursor:grab;
  background:radial-gradient(640px 380px at 50% 44%,rgba(123,92,255,.10),transparent 70%),var(--panel2)}
.networkWrap:active{cursor:grabbing}
.svgnet{width:100%;height:100%;display:block;user-select:none}
.netlink{stroke:#3c4d46;stroke-width:1.5;opacity:.55;fill:none}
.netlink.lnew{stroke:var(--teal);opacity:.85}
.netlink.lexit{stroke:var(--amber);opacity:.85;stroke-dasharray:4 3}
.netlink.lbuy{stroke:var(--green);opacity:.8}
.netlink.lsell{stroke:var(--red);opacity:.78}
.netlink.dim{opacity:.04}
.netlink.hot{stroke:#46e07a;stroke-opacity:1;stroke-width:2.4;marker-end:url(#arrowHot)}
.netelabels .elabel{font-size:10px;font-weight:800;fill:#bfe8cf;text-anchor:middle;opacity:0;
  paint-order:stroke;stroke:#04120b;stroke-width:3.5px;transition:opacity .1s;pointer-events:none}
.netelabels .elabel.show{opacity:1}
.netnode{cursor:grab}
.netnode.grabbing{cursor:grabbing}
.netnode circle{filter:drop-shadow(0 5px 12px rgba(0,0,0,.5));transition:stroke-width .1s}
.netnode:hover circle{stroke-width:3.5px}
.netnode.dim{opacity:.16;transition:opacity .12s}
.netnode.hot circle{stroke-width:3.5px}
.netnode .ini{font-size:11px;font-weight:800;fill:#fff;text-anchor:middle;dominant-baseline:middle;
  paint-order:stroke;stroke:rgba(0,0,0,.3);stroke-width:2px}
.nodeLocal{fill:#155b34;stroke:#40e07a}
.nodeForeign{fill:#5a1620;stroke:#ff5c67}
.nodeIssuer{fill:#392261;stroke:#9b6cff}
.nodeGov{fill:#5a4410;stroke:#f6c247}
.nodeSelected{fill:#113f25;stroke:#77ff9d}
.netlabels{pointer-events:none}
.nlabel .ttl{font-size:11.5px;font-weight:700;fill:#eaf4ee;text-anchor:middle;
  paint-order:stroke;stroke:#05100b;stroke-width:3.5px}
.nlabel.center .ttl{font-size:13px;fill:#fff}
.nlabel.dim{opacity:.14;transition:opacity .12s}
.netctrls{position:absolute;right:14px;top:14px;display:flex;flex-direction:column;gap:7px;z-index:6}
.netbtn{width:34px;height:34px;border-radius:9px;background:rgba(13,20,19,.92);border:1px solid var(--line);
  color:var(--text);font-size:17px;font-weight:700;display:flex;align-items:center;
  justify-content:center;cursor:pointer;backdrop-filter:blur(8px)}
.netbtn:hover{border-color:var(--green2);color:#fff}
.netlegend{position:absolute;left:14px;bottom:14px;display:flex;gap:14px;align-items:center;
  background:rgba(13,20,19,.9);border:1px solid var(--line);border-radius:10px;padding:8px 13px;
  font-size:11.5px;color:var(--text);backdrop-filter:blur(8px);z-index:6;flex-wrap:wrap;font-weight:550}
.netlegend .lg{display:flex;align-items:center;gap:6px}
.netlegend .dot{width:9px;height:9px;border-radius:50%}
.nethint{position:absolute;left:14px;top:14px;font-size:10.5px;color:var(--muted);
  background:rgba(13,20,19,.82);padding:6px 11px;border-radius:9px;border:1px solid var(--line);
  z-index:6;max-width:62%;backdrop-filter:blur(8px)}
#netTip{position:absolute;pointer-events:none;background:#0c1411;border:1px solid #25322b;
  border-radius:10px;padding:9px 12px;font-size:12px;line-height:1.5;color:#e8efe9;
  box-shadow:0 12px 30px rgba(0,0,0,.6);z-index:40;display:none;max-width:250px}
#netTip b{color:#fff;font-weight:700}
#netTip .rt{color:#8fa0b3;font-size:11px}

/* Insight cards */
.cards{display:grid;gap:11px}
.readCard{border:1px solid var(--line);background:#0b1210;border-radius:13px;padding:14px}
.readCard h4{margin:0 0 6px;font-size:14px;font-weight:700}
.readCard p{margin:0;color:var(--text2);font-size:13px;line-height:1.5}
.readCard.intel{border-color:rgba(123,92,255,.35);
  background:linear-gradient(180deg,rgba(123,92,255,.07),transparent)}
.readCard.affil,.readCard.gov{border-color:rgba(246,194,71,.4);
  background:linear-gradient(180deg,rgba(246,194,71,.07),transparent)}
.iflags,.gchips{display:flex;flex-wrap:wrap;gap:6px;margin-top:9px}
.iflag{font-size:10.5px;font-weight:700;padding:3px 9px;border-radius:7px;white-space:nowrap;border:1px solid transparent}
.fgood{background:var(--greenSoft);color:#46c46e}
.fwarn{background:var(--amberSoft);color:#d99b3e}
.fbad{background:var(--redSoft);color:#ff6b6b}
.fmut{background:#161c1a;color:#9aa6a0;border-color:#283029}
.finfo{background:rgba(58,160,255,.13);color:#3aa0ff}
.gchip{font-size:11.5px;background:#16201c;border:1px solid #28332c;border-radius:8px;
  padding:5px 10px;cursor:pointer;color:var(--text2);font-weight:550}
.gchip:hover{border-color:var(--green2);color:#6dff9d}
.gchip b{color:var(--green)}
.afmembers{display:flex;flex-direction:column;gap:5px;margin-top:8px}
.afm{display:flex;justify-content:space-between;gap:10px;font-size:12px;padding:6px 10px;
  background:#16201c;border:1px solid #28332c;border-radius:8px}
.afm b{color:var(--amber)}

/* Tables */
.tableWrap{overflow:auto}
.tbl{width:100%;border-collapse:collapse}
.tbl th,.tbl td{padding:10px 12px;border-bottom:1px solid rgba(35,50,45,.7);text-align:left;white-space:nowrap}
.tbl th{color:var(--muted);font-size:11.5px;font-weight:650;background:#0b1210;
  position:sticky;top:0;letter-spacing:.02em}
.tbl td{font-size:13px;color:var(--text2)}
.tbl tr:hover td{background:rgba(53,208,111,.04)}
.tbl tr:last-child td{border-bottom:none}
.tag{border:1px solid var(--line);background:#111917;border-radius:999px;padding:3px 9px;
  font-size:11px;color:var(--text2);font-weight:600}
.tag.buy{color:var(--green);border-color:rgba(53,208,111,.35);background:var(--greenSoft)}
.tag.sell{color:var(--red);border-color:rgba(255,92,103,.35);background:var(--redSoft)}
.tag.new{color:var(--teal);border-color:rgba(33,212,196,.35);background:var(--tealSoft)}
.tag.exit{color:var(--amber);border-color:rgba(246,194,71,.35);background:var(--amberSoft)}
.tag.gov{color:var(--amber);border-color:rgba(246,194,71,.35);background:var(--amberSoft)}
.tag.frn{color:var(--red);border-color:rgba(255,92,103,.35);background:var(--redSoft)}
.tag.loc{color:var(--green);border-color:rgba(53,208,111,.35);background:var(--greenSoft)}

/* Tabbar */
.tabbar{display:flex;gap:4px;border-bottom:1px solid var(--line);padding:0 12px;background:#09100e;overflow-x:auto}
.tabbar button{background:transparent;border:0;color:var(--muted);padding:14px 14px;cursor:pointer;
  border-bottom:2.5px solid transparent;font-weight:600;font-size:13.5px;white-space:nowrap;font-family:inherit}
.tabbar button:hover{color:var(--text2)}
.tabbar button.on{color:var(--green);border-bottom-color:var(--green)}
.tabpane{display:none}
.tabpane.on{display:block}

.twoCols{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.methodGrid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px}
.formula{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;background:#070d0b;
  border:1px solid var(--line);border-radius:10px;padding:12px;color:var(--text2);line-height:1.55;font-size:12px}
.pathItem{display:flex;justify-content:space-between;gap:12px;padding:11px 0;border-bottom:1px solid rgba(35,50,45,.6)}
.pathItem:last-child{border-bottom:none}
.pathItem .path{color:var(--text2)}
.pathItem .eff{font-weight:800;color:var(--green)}
.linkish{color:var(--green);cursor:pointer;text-decoration:none;font-weight:600}
.linkish:hover{text-decoration:underline}
.footerNote{margin-top:16px;color:var(--muted2);font-size:12px;line-height:1.5}

.mbar{height:8px;border-radius:6px;background:#0b1210;overflow:hidden;display:flex}
.mbar span{height:100%}
.blegend{display:flex;flex-wrap:wrap;gap:10px;margin-top:9px;font-size:11.5px;color:var(--text2)}
.blegend .li{display:flex;align-items:center;gap:5px}
.blegend .sw{width:9px;height:9px;border-radius:3px}
.statRow{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid rgba(35,50,45,.6);font-size:13px}
.statRow:last-child{border-bottom:none}
.statRow b{font-weight:700;color:var(--text)}

/* Controller / UBO intelligence */
.ctrlCard{border:1px solid rgba(53,208,111,.35);border-radius:14px;padding:15px;
  background:linear-gradient(180deg,rgba(53,208,111,.09),rgba(9,14,13,.6))}
.ctrlHead{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:4px}
.ctrlType{font-size:10px;font-weight:800;letter-spacing:.04em;text-transform:uppercase;
  padding:4px 9px;border-radius:7px;white-space:nowrap}
.ct-via_holding{background:var(--violetSoft);color:#b699ff;border:1px solid rgba(155,108,255,.4)}
.ct-direct{background:var(--greenSoft);color:#5fe08d;border:1px solid rgba(53,208,111,.4)}
.ct-direct_corp{background:rgba(58,160,255,.13);color:#6bb6ff;border:1px solid rgba(58,160,255,.4)}
.ct-family{background:var(--amberSoft);color:#e6b24e;border:1px solid rgba(246,194,71,.4)}
.ct-government{background:rgba(246,194,71,.16);color:#f6c247;border:1px solid rgba(246,194,71,.5)}
.ctrlName{font-size:17px;font-weight:800;line-height:1.2}
.ctrlName .linkish{font-size:17px}
.ctrlMeta{color:var(--muted);font-size:12.5px;margin-top:3px}
.ctrlPct{font-size:26px;font-weight:850;color:var(--green);letter-spacing:-.02em}
.strengthPill{font-size:11px;font-weight:700;color:var(--text2);background:#16201c;
  border:1px solid #28332c;border-radius:999px;padding:3px 10px}

/* UBO chain visual */
.uboChain{display:flex;align-items:stretch;gap:0;flex-wrap:wrap;margin-top:12px}
.uboStep{display:flex;align-items:center;gap:0}
.uboBox{background:#0c1512;border:1px solid #2a3a33;border-radius:10px;padding:8px 12px;
  cursor:pointer;transition:border-color .12s,background .12s;min-width:0}
.uboBox:hover{border-color:var(--green2);background:#10201a}
.uboBox .un{font-size:12.5px;font-weight:700;color:var(--text);white-space:nowrap;max-width:150px;overflow:hidden;text-overflow:ellipsis}
.uboBox .up{font-size:11px;color:var(--green);font-weight:700}
.uboBox.top{border-color:rgba(155,108,255,.5)}
.uboBox.ubo{border-color:rgba(246,194,71,.55);background:linear-gradient(180deg,rgba(246,194,71,.08),#0c1512)}
.uboBox.shadow{border-color:rgba(255,92,103,.55);background:linear-gradient(180deg,rgba(255,92,103,.1),#12090b)}
.uboTag{font-size:9.5px;color:var(--muted);margin-top:2px;text-transform:uppercase;letter-spacing:.03em;font-weight:700}
.uboBox.shadow .uboTag{color:#ff8087}
.uboWarn{margin-top:9px;font-size:12.5px;color:#ffb3b8;background:rgba(255,92,103,.09);
  border:1px solid rgba(255,92,103,.3);border-radius:10px;padding:9px 12px;line-height:1.5}
.uboWarn b{color:#fff}
.affList{display:flex;flex-direction:column;gap:6px;margin-top:8px}
.affRow{display:flex;justify-content:space-between;align-items:center;gap:10px;font-size:12.5px;
  padding:7px 10px;background:#161c1a;border:1px solid #283029;border-radius:8px}
.affVia{font-size:11px;color:var(--amber);font-weight:700;white-space:nowrap;cursor:help}
.uboArrow{color:var(--muted);font-size:16px;padding:0 8px;font-weight:700;align-self:center}
.uboFinal{margin-top:9px;font-size:12.5px;color:var(--text2)}
.uboFinal b{color:var(--amber)}

/* Family bloc */
.famCard{border:1px solid rgba(246,194,71,.35);border-radius:13px;padding:13px;
  background:linear-gradient(180deg,rgba(246,194,71,.07),transparent)}
.famHead{display:flex;justify-content:space-between;align-items:baseline;gap:10px}
.famHead h4{margin:0;font-size:14px}
.famHead .ft{font-size:16px;font-weight:850;color:var(--amber)}
.famMembers{display:flex;flex-wrap:wrap;gap:6px;margin-top:9px}
.famM{font-size:11.5px;background:#181e19;border:1px solid #2c352d;border-radius:8px;
  padding:4px 9px;color:var(--text2);font-weight:550}
.famM b{color:var(--amber);font-weight:750}

/* Group cards */
.groupGrid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:14px}
.groupCard{border:1px solid var(--line);border-radius:15px;overflow:hidden;
  background:linear-gradient(180deg,rgba(16,24,22,.9),rgba(9,14,13,.92));transition:border-color .12s,transform .12s}
.groupCard:hover{border-color:var(--green2);transform:translateY(-2px)}
.groupCardHead{padding:14px 15px;border-bottom:1px solid rgba(35,50,45,.7);
  background:linear-gradient(90deg,rgba(53,208,111,.1),transparent)}
.groupCardHead h3{margin:0;font-size:15px;font-weight:800;line-height:1.25}
.groupCardHead .gc{color:var(--muted);font-size:11.5px;margin-top:3px;font-weight:600}
.groupCardBody{padding:12px 15px}
.gpills{display:flex;flex-wrap:wrap;gap:6px}
.gpill{font-size:11.5px;font-weight:700;background:#111a17;border:1px solid #28332c;
  border-radius:8px;padding:5px 10px;cursor:pointer;color:var(--text2);transition:all .12s;white-space:nowrap}
.gpill:hover{border-color:var(--green2);color:#6dff9d;background:var(--greenSoft)}
.gpill .pp{color:var(--green);font-weight:800;margin-left:4px}
.gpill.anchor{border-style:dashed;color:var(--amber)}
.gpill.anchor:hover{border-color:var(--amber);color:#f6c247}
.gpill.suspect{border-color:rgba(224,151,62,.55);background:rgba(224,151,62,.08)}
.gpill.suspect:hover{border-color:#e0973e;color:#ffb060}
.gsub{font-size:11px;color:var(--muted2);text-transform:uppercase;letter-spacing:.08em;
  font-weight:700;margin:12px 0 7px}

/* Government redesign */
.govHero{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:16px}
.govStat{border:1px solid rgba(246,194,71,.3);border-radius:14px;padding:15px;
  background:linear-gradient(180deg,rgba(246,194,71,.08),rgba(9,14,13,.5))}
.govStat .gl{font-size:11.5px;color:#d9b45a;font-weight:650}
.govStat .gv{font-size:24px;font-weight:850;margin-top:6px;color:var(--text)}
.govStat .gn{font-size:11px;color:var(--muted2);margin-top:4px}
.govSplit{display:grid;grid-template-columns:1.2fr 1fr;gap:16px;margin-bottom:16px}
.govEntity{display:flex;align-items:center;gap:12px;padding:11px 13px;border:1px solid var(--line);
  border-radius:12px;background:#0b1210;margin-bottom:9px;transition:border-color .12s}
.govEntity:hover{border-color:rgba(246,194,71,.45)}
.govAvatar{width:40px;height:40px;border-radius:10px;flex-shrink:0;display:grid;place-items:center;
  font-weight:850;font-size:14px;color:#1a1400;background:linear-gradient(135deg,#f8d267,#d99a2b)}
.govEntity .ge{flex:1;min-width:0}
.govEntity .gname{font-size:13.5px;font-weight:700;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.govEntity .gmeta{font-size:11.5px;color:var(--muted);margin-top:2px}
.govEntity .gright{text-align:right;flex-shrink:0}
.govEntity .gbig{font-size:16px;font-weight:800;color:var(--amber)}
.govEntity .gsmall{font-size:11px;color:var(--muted2)}
.donut{display:flex;align-items:center;gap:16px}
.donutSvg{flex-shrink:0}
.donutLeg{display:flex;flex-direction:column;gap:6px;font-size:12px}
.donutLeg .dl{display:flex;align-items:center;gap:8px;color:var(--text2)}
.donutLeg .dsw{width:11px;height:11px;border-radius:3px;flex-shrink:0}
.donutLeg .dpc{margin-left:auto;font-weight:700;color:var(--text)}
.segLabel{font-size:12px;color:var(--muted);margin:2px 0 8px}

/* Change dashboard */
.chgHero{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:16px}
.chgStat{border:1px solid var(--line);border-radius:14px;padding:15px;position:relative;overflow:hidden}
.chgStat.up{border-color:rgba(53,208,111,.35);background:linear-gradient(180deg,rgba(53,208,111,.08),transparent)}
.chgStat.down{border-color:rgba(255,92,103,.35);background:linear-gradient(180deg,rgba(255,92,103,.08),transparent)}
.chgStat.new{border-color:rgba(33,212,196,.35);background:linear-gradient(180deg,rgba(33,212,196,.08),transparent)}
.chgStat.exit{border-color:rgba(246,194,71,.35);background:linear-gradient(180deg,rgba(246,194,71,.08),transparent)}
.chgStat .cl{font-size:12px;color:var(--muted);font-weight:650}
.chgStat .cv{font-size:26px;font-weight:850;margin-top:6px}
.chgStat.up .cv{color:var(--green)} .chgStat.down .cv{color:var(--red)}
.chgStat.new .cv{color:var(--teal)} .chgStat.exit .cv{color:var(--amber)}
.chgStat .cn{font-size:11px;color:var(--muted2);margin-top:3px}
.filterBar{display:flex;gap:8px;flex-wrap:wrap;align-items:center;margin-bottom:14px}
.filterBar input{background:#0a100f;border:1px solid #26352f;border-radius:9px;color:var(--text);
  padding:9px 12px;font-size:13px;min-width:200px;font-family:inherit}
.filterBar input:focus{outline:none;border-color:var(--green2);box-shadow:0 0 0 3px var(--greenSoft)}
.fchip{background:#111a18;border:1px solid var(--line);padding:7px 13px;border-radius:999px;
  color:var(--text2);cursor:pointer;font-size:12.5px;font-weight:600;transition:all .12s}
.fchip:hover{border-color:var(--green2)}
.fchip.on{border-color:rgba(53,208,111,.6);background:var(--greenSoft);color:#6dff9d}
.fchip.up.on{border-color:rgba(53,208,111,.6);background:var(--greenSoft);color:#6dff9d}
.fchip.down.on{border-color:rgba(255,92,103,.6);background:var(--redSoft);color:#ff8087}
.fchip.new.on{border-color:rgba(33,212,196,.6);background:var(--tealSoft);color:#4fe0d2}
.fchip.exit.on{border-color:rgba(246,194,71,.6);background:var(--amberSoft);color:#e6b24e}
.miniShares{color:var(--muted2);font-size:11px}
.dirCell{display:inline-flex;align-items:center;gap:5px}
.dirArrow{font-weight:900}
.up .dirArrow,.aUp{color:var(--green)} .down .dirArrow,.aDown{color:var(--red)}

/* Institutional */
.instHero{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:16px}
.instCard{border:1px solid var(--line);border-radius:13px;padding:12px 14px;background:#0b1210;
  display:flex;flex-direction:column;gap:6px;transition:border-color .12s}
.instCard:hover{border-color:var(--green2)}
.instTop{display:flex;align-items:center;gap:10px}
.instBadge{width:34px;height:34px;border-radius:9px;flex-shrink:0;display:grid;place-items:center;
  font-weight:800;font-size:12px;color:#04120b;background:linear-gradient(135deg,#5fe08d,#1ca95a)}
.instBadge.frn{background:linear-gradient(135deg,#6bb6ff,#3a7bd5);color:#02121f}
.instName{font-size:13px;font-weight:700;line-height:1.2;cursor:pointer}
.instName:hover{color:#6dff9d}
.instMeta{font-size:11px;color:var(--muted)}
.instFig{display:flex;gap:14px;margin-top:2px}
.instFig .f{font-size:11.5px;color:var(--text2)}
.instFig .f b{color:var(--text);font-weight:750}
.instPills{display:flex;flex-wrap:wrap;gap:5px;margin-top:4px}
.instPills .ip{font-size:10.5px;background:#161f1b;border:1px solid #28332c;border-radius:6px;
  padding:2px 7px;color:var(--muted);cursor:pointer}
.instPills .ip:hover{border-color:var(--green2);color:#6dff9d}
.instGrid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:12px}

@media (max-width:1080px){
  .app{grid-template-columns:1fr}
  .side{position:relative;height:auto;border-right:none;border-bottom:1px solid var(--line);
    padding:12px 16px;display:flex;align-items:center;gap:12px;overflow:visible}
  .side .navTitle,.side .nav,.dataCard{display:none!important}
  .brand{margin-bottom:0}
  .main{padding:16px 16px 30px}
  .layout,.twoCols,.selectorGrid,.methodGrid{grid-template-columns:1fr}
  .gridKpi{grid-template-columns:repeat(2,1fr)}
  .search input{width:100%}
  .topbar{flex-wrap:wrap}
  .search{margin-left:0;width:100%}
  .title{font-size:24px}
  .networkWrap{height:480px;touch-action:pan-y}
  .nethint{display:none}
}
@media (max-width:480px){
  .title{font-size:21px}
  .kpi .num{font-size:19px}
  .networkWrap{height:400px}
  .netlegend{font-size:10px;gap:8px;max-width:76%}
}


/* Make sure modal doesn't conflict with layout */
.app {
    min-height: 100vh;
}
</style>
