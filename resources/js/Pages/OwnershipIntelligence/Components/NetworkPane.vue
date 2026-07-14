<script setup>
import { onMounted, onUnmounted, ref, watch } from 'vue';
import { 
    selectedKey, depth, directionMode, minPct, NET,
    collectSubgraph, shortLabel, setSelected,
    filteredInEdges, filteredOutEdges, fmtPct, fmtShares, fmtBps, edgeClass, ent, ownershipIntel
} from '../../../Composables/useOwnershipLogic';

const networkSvg = ref(null);
const insightCards = ref(null);
let rafId = null;

function deoverlapLabels(nodes, cx, cy) {
    function box(n) {
        let t = (n.role === 'center' ? (n.label || '') : (n.ticker || n.label || ''));
        if (t.length > 16) t = t.slice(0, 16);
        const w = Math.max(n.r * 2 + 6, t.length * 7.0 + 12);
        const lines = 1 + ((n.pct != null && n.role !== 'center') ? 1 : 0);
        const h = n.r * 2 + 14 + lines * 14;
        return { w, h };
    }
    const B = nodes.map(box);
    for (let it = 0; it < 170; it++) {
        for (let i = 0; i < nodes.length; i++) for (let j = i + 1; j < nodes.length; j++) {
            const a = nodes[i], b = nodes[j], ba = B[i], bb = B[j];
            const acy = a.y + (ba.h / 2 - a.r), bcy = b.y + (bb.h / 2 - b.r);
            const dx = b.x - a.x, dy = bcy - acy;
            const ox = (ba.w + bb.w) / 2 - Math.abs(dx), oy = (ba.h + bb.h) / 2 - Math.abs(dy);
            if (ox > 0 && oy > 0) {
                if (ox < oy) {
                    const s = (dx < 0 ? -1 : 1) * ox * 0.25;
                    if (a.fx == null) a.x -= s;
                    if (b.fx == null) b.x += s;
                } else {
                    const s = (dy < 0 ? -1 : 1) * oy * 0.16;
                    if (a.fy == null) a.y -= s;
                    if (b.fy == null) b.y += s;
                }
            }
        }
        for (const n of nodes) {
            if (n.fx == null) n.x += (cx - n.x) * 0.004;
            if (n.fy == null) n.y += (cy - n.y) * 0.004;
        }
    }
}

function applyTransform() {
    const g = document.getElementById('netRoot');
    if (g) g.setAttribute('transform', `translate(${NET.tx},${NET.ty}) scale(${NET.k})`);
}

function fitNetwork() {
    const ns = NET.nodes;
    if (!ns || !ns.length) return;
    let a = 1e9, b = 1e9, c = -1e9, d = -1e9;
    for (const n of ns) {
        a = Math.min(a, n.x - n.r - 30);
        b = Math.min(b, n.y - n.r - 34);
        c = Math.max(c, n.x + n.r + 30);
        d = Math.max(d, n.y + n.r + 30);
        if (n._main) {
            const w = ((n._main || '').length) * 3.7 + 12;
            const lx = n.x + (n.lox || 0);
            const ly = n.y + (n.loy != null ? n.loy : n.r + 16);
            a = Math.min(a, lx - w);
            b = Math.min(b, ly - 18);
            c = Math.max(c, lx + w);
            d = Math.max(d, ly + (n._pct ? 24 : 8));
        }
    }
    const svgEl = networkSvg.value;
    const W = svgEl ? svgEl.clientWidth || 900 : 900;
    const H = svgEl ? svgEl.clientHeight || 560 : 560;
    const bw = c - a, bh = d - b;
    const k = Math.min(W / bw, H / bh, 1.5);
    NET.k = k;
    NET.tx = (W - k * (a + c)) / 2;
    NET.ty = (H - k * (b + d)) / 2;
    applyTransform();
}

function labelBox(n) {
    const w = Math.max((n._main || '').length, (n._pct || '').length) * 6.6 + 10;
    const h = (n._pct ? 28 : 16);
    const cx = n.x + (n.lox || 0);
    const cy = n.y + (n.loy != null ? n.loy : n.r + 16) + (n._pct ? 5 : -2);
    return { w, h, cx, cy };
}

function layoutLabels() {
    const ns = NET.nodes;
    if (!ns) return;
    for (const n of ns) {
        if (n.lox == null) n.lox = 0;
        if (n.loy == null) n.loy = n.r + 16;
    }
    for (let it = 0; it < 90; it++) {
        for (let i = 0; i < ns.length; i++) {
            const a = ns[i];
            const A = labelBox(a);
            a.lox += (0 - a.lox) * 0.05;
            a.loy += ((a.r + 16) - a.loy) * 0.05;
            for (let j = 0; j < ns.length; j++) {
                if (i === j) continue;
                const b = ns[j];
                const B = labelBox(b);
                const dx = A.cx - B.cx, dy = A.cy - B.cy;
                const ox = (A.w + B.w) / 2 - Math.abs(dx), oy = (A.h + B.h) / 2 - Math.abs(dy);
                if (ox > 0 && oy > 0) {
                    if (oy <= ox) {
                        a.loy += (dy >= 0 ? 1 : -1) * oy * 0.5;
                    } else {
                        a.lox += (dx >= 0 ? 1 : -1) * ox * 0.5;
                    }
                }
            }
            for (const m of ns) {
                if (m === a) continue;
                const dx = A.cx - m.x, dy = A.cy - m.y, dd = Math.hypot(dx, dy) || 0.01;
                const min = m.r + A.h / 2 + 6;
                if (dd < min) {
                    const p = (min - dd) / dd;
                    a.lox += dx * p * 0.5;
                    a.loy += dy * p * 0.5;
                }
            }
            a.lox = Math.max(-110, Math.min(110, a.lox));
            a.loy = Math.max(-(a.r + 40), Math.min(a.r + 60, a.loy));
        }
    }
}

function syncPositions() {
    if (!NET.linkEls) return;
    for (let i = 0; i < NET.links.length; i++) {
        const l = NET.links[i], e = NET.linkEls[i];
        if (e) {
            e.setAttribute('x1', l.s.x); e.setAttribute('y1', l.s.y);
            e.setAttribute('x2', l.t.x); e.setAttribute('y2', l.t.y);
        }
    }
    for (const n of NET.nodes) {
        const g = NET.nodeEls[n.key];
        if (g) g.setAttribute('transform', `translate(${n.x},${n.y})`);
        const lg = NET.labEls ? NET.labEls[n.key] : null;
        if (lg) {
            const lx = n.x + (n.lox || 0), ly = n.y + (n.loy != null ? n.loy : n.r + 16);
            lg.setAttribute('transform', `translate(${lx},${ly})`);
            const ld = lg.querySelector('.nlead');
            if (ld) {
                const far = Math.abs(n.lox || 0) > 16 || (n.loy || 0) < n.r + 6;
                if (far) {
                    ld.setAttribute('x1', n.x - lx); ld.setAttribute('y1', n.y - ly);
                    ld.setAttribute('x2', 0); ld.setAttribute('y2', -5);
                    ld.style.display = '';
                } else {
                    ld.style.display = 'none';
                }
            }
        }
    }
}

function paintNetwork() {
    const svg = networkSvg.value;
    if (!svg) return;
    const linkSvg = NET.links.map((l, i) => {
        const cls = l.dir === 'NEW' ? 'lnew' : l.dir === 'EXIT' ? 'lexit' : l.dir === 'BUY' ? 'lbuy' : l.dir === 'SELL' ? 'lsell' : '';
        return `<line class="netlink ${cls}" data-i="${i}"></line>`;
    }).join('');
    const nodeSvg = NET.nodes.map(n => {
        const inside = (n.role === 'holding') ? `<text class="ini" y="4">${(n.ticker || '').slice(0, 4)}</text>` : '';
        return `<g class="netnode ${n.role}${n.role === 'center' ? ' isCenter' : ''}" data-key="${n.key}"><circle r="${n.r}" fill="${n.color}" stroke="${n.stroke}" stroke-width="${n.role === 'center' ? 3 : 2}"></circle>${inside}</g>`;
    }).join('');
    const labSvg = NET.nodes.map(n => {
        const pctEl = n._pct ? `<text class="pctlbl" y="13">${n._pct}</text>` : '';
        return `<g class="nlabel ${n.role}" data-key="${n.key}"><line class="nlead"></line><text class="ttl" y="0">${n._main || ''}</text>${pctEl}</g>`;
    }).join('');
    svg.innerHTML = `<g id="netRoot"><g class="netlinks">${linkSvg}</g><g class="netnodes">${nodeSvg}</g><g class="netlabels">${labSvg}</g></g>`;
    
    NET.linkEls = [...svg.querySelectorAll('.netlink')];
    NET.nodeEls = {};
    NET.labEls = {};
    NET.adj = {};
    svg.querySelectorAll('.netnode').forEach(g => { NET.nodeEls[g.dataset.key] = g; });
    svg.querySelectorAll('.nlabel').forEach(g => { NET.labEls[g.dataset.key] = g; });
    NET.links.forEach(l => {
        (NET.adj[l.s.key] || (NET.adj[l.s.key] = new Set())).add(l.t.key);
        (NET.adj[l.t.key] || (NET.adj[l.t.key] = new Set())).add(l.s.key);
    });
    syncPositions();
    applyTransform();
}

function renderNetwork() {
    if (!networkSvg.value) return;
    const sub = collectSubgraph();
    const eds = sub.edges, level = sub.level;
    const W = networkSvg.value.clientWidth || 900, H = networkSvg.value.clientHeight || 560;
    networkSvg.value.setAttribute('viewBox', `0 0 ${W} ${H}`);
    const cx = W / 2, cy = H / 2;
    const PURPLE = '#9b6cff', RED = '#ff5c67', TEAL = '#1fb8a6', BRAND = '#b07cff';
    
    function nodeLF(key) { for (const e of eds) { if (e.from === key) return e.local_foreign; } return null; }
    function pctInto(key) { let best = null; for (const e of eds) { if (e.to === key || e.from === key) { if (best == null || e.pct > best) best = e.pct; } } return best; }
    
    const nodes = [], idx = {};
    sub.nodes.forEach(n => {
        const key = n.key, lvl = level[key] || 0, isSel = key === selectedKey.value, lf = nodeLF(key), isIssuer = (n.kind === 'issuer'), held = lvl > 0;
        let role, color, stroke, r, pct = null;
        if (isSel) { role = 'center'; color = BRAND; stroke = '#e2d2ff'; r = 34; }
        else if (held && isIssuer) { role = 'holding'; color = '#13413a'; stroke = TEAL; r = 22; pct = pctInto(key); }
        else {
            role = 'owner'; pct = pctInto(key);
            if (lf === 'F') { color = '#46161c'; stroke = RED; } else { color = '#291a4d'; stroke = PURPLE; }
            r = Math.max(9, Math.min(22, 9 + Math.sqrt(pct || 1) * 2.2));
        }
        const nd = {
            key, label: n.label, ticker: n.ticker, kind: n.kind, lf, role, color, stroke, r, pct, lvl,
            x: cx + (Math.random() - 0.5) * 70, y: cy + lvl * 120 + (Math.random() - 0.5) * 40, vx: 0, vy: 0, fx: null, fy: null
        };
        if (isSel) { nd.fx = cx; nd.fy = cy; }
        idx[key] = nd; nodes.push(nd);
    });
    
    const links = eds.filter(e => idx[e.from] && idx[e.to]).map(e => ({ s: idx[e.from], t: idx[e.to], pct: e.pct, dir: e.direction }));
    const linkDist = l => (l.s.role === 'center' || l.t.role === 'center') ? 175 : 135;
    
    function tick() {
        for (let i = 0; i < nodes.length; i++) for (let j = i + 1; j < nodes.length; j++) {
            const a = nodes[i], b = nodes[j]; let dx = b.x - a.x, dy = b.y - a.y, d2 = dx * dx + dy * dy || 0.01, d = Math.sqrt(d2);
            const fo = 4400 / d2, fx = dx / d * fo, fy = dy / d * fo;
            a.vx -= fx; a.vy -= fy; b.vx += fx; b.vy += fy;
            const md = a.r + b.r + 32;
            if (d < md) { const p = (md - d) / d * 0.5; a.vx -= dx * p; a.vy -= dy * p; b.vx += dx * p; b.vy += dy * p; }
        }
        for (const l of links) {
            const a = l.s, b = l.t; let dx = b.x - a.x, dy = b.y - a.y, d = Math.sqrt(dx * dx + dy * dy) || 0.01;
            const k = (d - linkDist(l)) / d * 0.08, fx = dx * k, fy = dy * k;
            a.vx += fx; a.vy += fy; b.vx -= fx; b.vy -= fy;
        }
        for (const n of nodes) { n.vx += (cx - n.x) * 0.006; n.vy += ((cy + (n.lvl || 0) * 125) - n.y) * 0.02; }
        for (const n of nodes) {
            if (n.fx != null) { n.x = n.fx; n.vx = 0; } else { n.vx *= 0.84; n.x += n.vx; }
            if (n.fy != null) { n.y = n.fy; n.vy = 0; } else { n.vy *= 0.84; n.y += n.vy; }
        }
    }
    
    for (let i = 0; i < 460; i++) tick();
    deoverlapLabels(nodes, cx, cy);
    
    NET.nodes = nodes; NET.links = links; NET.tick = tick; NET.W = W; NET.H = H; NET.cx = cx; NET.cy = cy;
    nodes.forEach(n => {
        n._main = n.role === 'center' ? shortLabel(n.label, 22) : (n.role === 'holding' ? (n.ticker || shortLabel(n.label, 12)) : shortLabel(n.ticker || n.label, 15));
        n._pct = (n.pct != null && n.role !== 'center') ? n.pct.toFixed(2) + '%' : '';
        n.lox = 0; n.loy = n.r + 16;
    });
    
    layoutLabels();
    fitNetwork();
    paintNetwork();
    wireNetwork();
    renderInsightCards();
}

function wireNetwork() {
    if (!networkSvg.value || NET.wired) return;
    const svg = networkSvg.value;
    NET.wired = true;
    const wrap = svg.closest('.networkWrap') || svg.parentElement;
    let tip = document.getElementById('netTip');
    if (!tip) { tip = document.createElement('div'); tip.id = 'netTip'; wrap.appendChild(tip); }
    
    const rectPt = ev => { const r = svg.getBoundingClientRect(); return { x: ev.clientX - r.left, y: ev.clientY - r.top }; };
    const toGraph = p => ({ x: (p.x - NET.tx) / NET.k, y: (p.y - NET.ty) / NET.k });
    
    let mode = null, moved = 0, dragNode = null, last = null, downKey = null;
    
    svg.addEventListener('pointerdown', ev => {
        try { svg.setPointerCapture(ev.pointerId); } catch (e) {}
        NET.pointers.set(ev.pointerId, rectPt(ev)); tip.style.display = 'none';
        if (NET.pointers.size === 2) {
            mode = 'pinch';
            const ps = [...NET.pointers.values()];
            NET.pinch = { d: Math.hypot(ps[0].x - ps[1].x, ps[0].y - ps[1].y), k: NET.k, cx: (ps[0].x + ps[1].x) / 2, cy: (ps[0].y + ps[1].y) / 2, tx: NET.tx, ty: NET.ty };
            return;
        }
        const g = ev.target.closest('.netnode');
        moved = 0; last = rectPt(ev);
        if (g) { mode = 'node'; downKey = g.dataset.key; dragNode = NET.nodes.find(n => n.key === downKey); }
        else { mode = 'pan'; }
        wrap.style.cursor = 'grabbing';
    });
    
    svg.addEventListener('pointermove', ev => {
        if (!NET.pointers.has(ev.pointerId)) return;
        NET.pointers.set(ev.pointerId, rectPt(ev));
        if (mode === 'pinch' && NET.pointers.size >= 2) {
            const ps = [...NET.pointers.values()], d = Math.hypot(ps[0].x - ps[1].x, ps[0].y - ps[1].y), r = d / NET.pinch.d;
            let nk = Math.max(0.35, Math.min(3.2, NET.pinch.k * r));
            NET.k = nk; NET.tx = NET.pinch.cx - (NET.pinch.cx - NET.pinch.tx) * (nk / NET.pinch.k); NET.ty = NET.pinch.cy - (NET.pinch.cy - NET.pinch.ty) * (nk / NET.pinch.k);
            applyTransform(); return;
        }
        const p = rectPt(ev), dx = p.x - last.x, dy = p.y - last.y;
        moved += Math.abs(dx) + Math.abs(dy); last = p;
        if (mode === 'pan') { NET.tx += dx; NET.ty += dy; applyTransform(); }
        else if (mode === 'node' && dragNode) {
            const gp = toGraph(p);
            dragNode.x = gp.x; dragNode.y = gp.y; dragNode.fx = gp.x; dragNode.fy = gp.y;
            syncPositions();
        }
    });
    
    const up = ev => {
        NET.pointers.delete(ev.pointerId); wrap.style.cursor = 'grab';
        if (mode === 'node' && dragNode) {
            if (moved < 6) {
                netClearHL();
                setSelected(downKey);
            } else {
                layoutLabels(); syncPositions();
            }
        }
        mode = null; dragNode = null;
    };
    
    svg.addEventListener('pointerup', up); svg.addEventListener('pointercancel', up); svg.addEventListener('lostpointercapture', up);
    svg.addEventListener('wheel', ev => {
        ev.preventDefault();
        const p = rectPt(ev), f = ev.deltaY < 0 ? 1.1 : 1 / 1.1;
        let nk = Math.max(0.35, Math.min(3.2, NET.k * f));
        NET.tx = p.x - (p.x - NET.tx) * (nk / NET.k); NET.ty = p.y - (p.y - NET.ty) * (nk / NET.k); NET.k = nk;
        applyTransform();
    }, { passive: false });
    
    svg.addEventListener('mousemove', ev => {
        if (mode) { tip.style.display = 'none'; return; }
        const g = ev.target.closest('.netnode');
        if (g) {
            const n = NET.nodes.find(x => x.key === g.dataset.key);
            if (n) {
                netHighlight(n.key);
                const wr = wrap.getBoundingClientRect();
                tip.innerHTML = tipHtml(n);
                tip.style.display = 'block';
                let lx = ev.clientX - wr.left + 14;
                tip.style.left = Math.min(lx, wrap.clientWidth - tip.offsetWidth - 8) + 'px';
                tip.style.top = (ev.clientY - wr.top + 12) + 'px';
            }
        } else {
            tip.style.display = 'none';
            netClearHL();
        }
    });
    svg.addEventListener('mouseleave', () => { tip.style.display = 'none'; netClearHL(); });
}

function roleText(n) {
    return n.role === 'center' ? 'Subjek' : n.role === 'holding' ? 'Holding (dimiliki subjek)' : (n.lf === 'F' ? 'Pemilik · Asing' : 'Pemilik · Domestik');
}
function tipHtml(n) {
    return `<b>${n.label || n.key}</b>${n.ticker ? ` · ${n.ticker}` : ''}<br><span class="rt">${roleText(n)}</span>${n.pct != null ? ` · <b>${n.pct.toFixed(2)}%</b>` : ''}`;
}

function netHighlight(key) {
    const adj = NET.adj[key] || new Set();
    for (const k in NET.nodeEls) {
        const on = (k === key || adj.has(k));
        NET.nodeEls[k].classList.toggle('dim', !on);
        if (NET.labEls[k]) NET.labEls[k].classList.toggle('dim', !on);
    }
    for (let i = 0; i < NET.linkEls.length; i++) {
        const l = NET.links[i], on = (l.s.key === key || l.t.key === key);
        NET.linkEls[i].classList.toggle('dim', !on);
        NET.linkEls[i].classList.toggle('hot', on);
    }
    const g = NET.nodeEls[key]; if (g) g.classList.add('hot');
}
function netClearHL() {
    for (const k in NET.nodeEls) {
        NET.nodeEls[k].classList.remove('dim', 'hot');
        if (NET.labEls[k]) NET.labEls[k].classList.remove('dim');
    }
    for (const e of (NET.linkEls || [])) e.classList.remove('dim', 'hot');
}

function zoom(f) {
    const X = NET.W / 2, Y = NET.H / 2;
    let nk = Math.max(0.35, Math.min(3.2, NET.k * f));
    NET.tx = X - (X - NET.tx) * (nk / NET.k);
    NET.ty = Y - (Y - NET.ty) * (nk / NET.k);
    NET.k = nk;
    applyTransform();
}

function renderInsightCards() {
    const intel = ownershipIntel();
    const tc = { good: 'fgood', warn: 'fwarn', bad: 'fbad', mut: 'fmut' };
    const flagHtml = intel.flags.map(f => `<span class="iflag ${tc[f.tone] || 'fmut'}">${f.t}</span>`).join('');
    let html = `<div class="readCard intel"><h4>⊛ Intelligence Read</h4><p>${intel.narrative}</p><div class="iflags">${flagHtml}</div></div>`;
    
    if (intel.group) {
        const mem = intel.group.members.map(m => `<span class="gchip" data-key="${m.key}">${m.ticker || shortLabel(m.issuer, 10)} <b>${fmtPct(m.pct)}</b></span>`).join('');
        html += `<div class="readCard"><h4>◷ Grup / Afiliasi — ${intel.group.controller}</h4><p class="muted">Entitas lain yang dimiliki pengendali sama (≥5%) — klik untuk telusuri:</p><div class="gchips">${mem}</div></div>`;
    }
    if (intel.affiliation && (intel.affiliation.hidden || intel.affiliation.bloc.members.length >= 2)) {
        const mem = intel.affiliation.bloc.members.map(m => `<span class="gchip" data-key="${m.from}">${m.investor ? shortLabel(m.investor, 15) : shortLabel(m.from, 15)} <b>${fmtPct(m.pct)}</b></span>`).join('');
        const title = intel.affiliation.hidden ? '<span class="iflag fbad">Hidden Control Detected</span>' : 'Blok Afiliasi Terdeteksi';
        const p = intel.affiliation.hidden 
            ? `Total gabungan (${fmtPct(intel.affiliation.bloc.sum)}) melebihi pemegang tunggal terbesar (${fmtPct(intel.affiliation.largest.pct)}). Terdapat indikasi pemecahan kepemilikan di bawah ambang pengendali.`
            : `Ditemukan grup afiliasi dengan total kepemilikan ${fmtPct(intel.affiliation.bloc.sum)}.`;
        
        html += `<div class="readCard intel"><h4>${title}</h4><p class="muted">${p} (Bukti: token "<b>${intel.affiliation.bloc.token}</b>" atau tautan silang)</p><div class="gchips">${mem}</div></div>`;
    }
    if (insightCards.value) {
        insightCards.value.innerHTML = html;
        // Re-attach event listeners to injected HTML
        insightCards.value.querySelectorAll('.gchip').forEach(el => {
            el.onclick = () => setSelected(el.dataset.key);
        });
    }
}

watch([selectedKey, depth, directionMode, minPct], () => {
    renderNetwork();
});

onMounted(() => {
    setTimeout(renderNetwork, 100);
});
onUnmounted(() => {
    NET.wired = false;
});
</script>

<template>
    <div class="layout">
        <div>
            <div class="panelHead">
                <h3>Investor Network</h3>
                <div class="toolbar">
                    <span class="muted small">{{ NET.nodes.length }} nodes · {{ NET.links.length }} links</span>
                    <button class="btn" @click="fitNetwork">Re-layout</button>
                </div>
            </div>
            <div class="networkWrap">
                <div class="nethint">Scroll = zoom · drag kanvas = geser · drag bulatan = pindahkan · klik = fokus</div>
                <div class="netctrls">
                    <div class="netbtn" @click="zoom(1.2)">+</div>
                    <div class="netbtn" @click="zoom(1/1.2)">−</div>
                    <div class="netbtn" @click="fitNetwork" title="Fit">⤢</div>
                </div>
                <div class="netlegend">
                    <span class="lg"><span class="dot" style="background:#9b6cff"></span>Domestic</span>
                    <span class="lg"><span class="dot" style="background:#ff5c67"></span>Foreign</span>
                    <span class="lg"><span class="dot" style="background:#1fb8a6"></span>Holding</span>
                </div>
                <svg class="svgnet" id="networkSvg" ref="networkSvg"></svg>
            </div>
        </div>
        <div class="panelBody">
            <div class="cards" id="insightCards" ref="insightCards"></div>
        </div>
    </div>
    
    <div class="twoCols" style="padding:0 16px 16px">
        <div class="panel" style="box-shadow:none">
            <div class="panelHead">
                <h3>Owners of selected entity</h3>
                <span class="muted small">{{ filteredInEdges.length }} links</span>
            </div>
            <div class="tableWrap">
                <table class="tbl">
                    <thead>
                        <tr><th>Holder</th><th>Type</th><th>Stake</th><th>Shares</th><th>Change</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <tr v-for="e in [...filteredInEdges].sort((a,b)=>b.pct-a.pct)" :key="e.from">
                            <td><span class="linkish" @click="setSelected(e.from)">{{ e.investor }}</span></td>
                            <td>{{ e.classification || '-' }} · {{ e.local_foreign || '-' }}</td>
                            <td>{{ fmtPct(e.pct) }}</td>
                            <td>{{ fmtShares(e.shares) }}</td>
                            <td>
                                <span :class="['tag', edgeClass(e)]">{{ e.direction }}</span>
                                {{ fmtShares(e.delta_shares) }}
                                <span class="muted">{{ fmtBps(e.delta_pct) }}</span>
                            </td>
                            <td><button class="btn" @click="setSelected(e.from)">Open</button></td>
                        </tr>
                        <tr v-if="filteredInEdges.length === 0"><td colspan="6" class="muted">No data under current filter.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="panel" style="box-shadow:none">
            <div class="panelHead">
                <h3>Holdings by selected entity</h3>
                <span class="muted small">{{ filteredOutEdges.length }} links</span>
            </div>
            <div class="tableWrap">
                <table class="tbl">
                    <thead>
                        <tr><th>Issuer</th><th>Stake</th><th>Shares</th><th>Change</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <tr v-for="e in [...filteredOutEdges].sort((a,b)=>b.pct-a.pct)" :key="e.to">
                            <td><span class="linkish" @click="setSelected(e.to)">{{ e.ticker }} · {{ e.issuer }}</span></td>
                            <td>{{ fmtPct(e.pct) }}</td>
                            <td>{{ fmtShares(e.shares) }}</td>
                            <td>
                                <span :class="['tag', edgeClass(e)]">{{ e.direction }}</span>
                                {{ fmtShares(e.delta_shares) }}
                                <span class="muted">{{ fmtBps(e.delta_pct) }}</span>
                            </td>
                            <td><button class="btn" @click="setSelected(e.to)">Open</button></td>
                        </tr>
                        <tr v-if="filteredOutEdges.length === 0"><td colspan="5" class="muted">No data under current filter.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
