import { ref, computed, reactive, shallowRef, watch, nextTick } from 'vue';
import { initVanillaLogic } from './vanillaPrdLogic';

// Global state so it's shared across components if imported multiple times
const dataLoaded = ref(false);
const entities = shallowRef({});
const edges = shallowRef([]);
const changes = shallowRef([]);
const audits = shallowRef({});
const investorSummaries = shallowRef({});
const stats = shallowRef({});

const entityArr = shallowRef([]);
const outEdges = shallowRef({});
const inEdges = shallowRef({});
const changeByFrom = shallowRef({});
const changeByTo = shallowRef({});

// Reactive UI state
export const selectedKey = ref('E:BRPT');
export const minPct = ref(1);
export const depth = ref(2);
export const directionMode = ref('both');
export const activeTab = ref('networkPane'); // For tab navigation
export const activeMode = ref('detail'); // 'detail' | 'global'

// D3/Network State
export const NET = reactive({
    k: 1, tx: 0, ty: 0,
    nodes: [], links: [],
    wired: false, pointers: new Map(), raf: null,
    W: 900, H: 560, cx: 450, cy: 280,
    adj: {}, linkEls: [], nodeEls: {}, labEls: {}
});

// Helpers
export const fmtPct = (x) => (x == null || isNaN(x)) ? '-' : `${(+x).toFixed(2)}%`;
export const fmtBps = (x) => (x == null || isNaN(x)) ? '-' : `${x > 0 ? '+' : ''}${(+x).toFixed(2)} ppt`;
export const fmtShares = (n) => {
    n = +n || 0; const s = n < 0 ? '-' : ''; n = Math.abs(n);
    if (n >= 1e12) return s + (n / 1e12).toFixed(2) + 'T';
    if (n >= 1e9) return s + (n / 1e9).toFixed(2) + 'B';
    if (n >= 1e6) return s + (n / 1e6).toFixed(2) + 'M';
    if (n >= 1e3) return s + (n / 1e3).toFixed(1) + 'K';
    return s + Math.round(n).toLocaleString('id-ID');
};
export const ent = (k) => entities.value[k] || { key: k, label: k, kind: 'unknown' };
export const shortLabel = (s, n = 24) => { s = s || ''; return s.length > n ? s.slice(0, n - 1) + '…' : s; };
export const edgeClass = (e) => e.direction === 'BUY' ? 'buy' : e.direction === 'SELL' ? 'sell' : e.direction === 'NEW' ? 'new' : e.direction === 'EXIT' ? 'exit' : 'unchanged';

export function setSelected(k) {
    if (!entities.value[k]) return;
    selectedKey.value = k;
}

export const filteredInEdges = computed(() => (inEdges.value[selectedKey.value] || []).filter(e => e.pct >= minPct.value));
export const filteredOutEdges = computed(() => (outEdges.value[selectedKey.value] || []).filter(e => e.pct >= minPct.value));

export const filteredEdgesGlobal = (list) => (list || []).filter(e => e.pct >= minPct.value);

export const relatedChanges = computed(() => {
    return changes.value.filter(c => c.from === selectedKey.value || c.to === selectedKey.value)
        .sort((a, b) => Math.abs(b.deltaShares) - Math.abs(a.deltaShares));
});

export function setOwnershipData(DATA) {
    entities.value = DATA.entities;
    edges.value = DATA.edges;
    changes.value = DATA.changes;
    audits.value = DATA.audits;
    investorSummaries.value = DATA.investorSummaries;
    stats.value = DATA.stats;

    entityArr.value = Object.values(entities.value);
    
    if (entities.value['E:BRPT']) selectedKey.value = 'E:BRPT';
    else selectedKey.value = stats.value.defaultKey || Object.keys(entities.value)[0];

    const oEdges = {};
    const iEdges = {};
    const cbFrom = {};
    const cbTo = {};

    for (const e of edges.value) {
        (oEdges[e.from] || (oEdges[e.from] = [])).push(e);
        (iEdges[e.to] || (iEdges[e.to] = [])).push(e);
    }
    for (const c of changes.value) {
        (cbFrom[c.from] || (cbFrom[c.from] = [])).push(c);
        (cbTo[c.to] || (cbTo[c.to] = [])).push(c);
    }

    for (const k in oEdges) oEdges[k].sort((a, b) => b.pct - a.pct);
    for (const k in iEdges) iEdges[k].sort((a, b) => b.pct - a.pct);

    outEdges.value = oEdges;
    inEdges.value = iEdges;
    changeByFrom.value = cbFrom;
    changeByTo.value = cbTo;

    dataLoaded.value = true;
    
    nextTick(() => {
        // Initialize Vanilla PRD Logic after DOM is ready
        initVanillaLogic(DATA, DATA);
        window.onVanillaSelectedKey = (key) => {
            selectedKey.value = key;
        };
    });

    // Sync Vue state changes back to vanilla
    watch(selectedKey, (val) => {
        if (window._vanillaSelectedKey !== val) {
            window._vanillaSelectedKey = val;
            if (window.renderAll) window.renderAll();
        }
    });
    watch(minPct, (val) => {
        window._minPct = val;
        if (window.renderAll) window.renderAll();
    });
    watch(depth, (val) => {
        window._depth = val;
        if (window.renderAll) window.renderAll();
    });
    watch(directionMode, (val) => {
        window._directionMode = val;
        if (window.renderAll) window.renderAll();
    });
}

// Async Load Data
export async function loadOwnershipData(url = '/desk-brief/ownership-intelligence/data', force = false) {
    if (dataLoaded.value && !force) return;
    // Reset if force-loading (e.g. mockup vs production)
    if (force) dataLoaded.value = false;
    try {
        const res = await fetch(url);
        const DATA = await res.json();
        setOwnershipData(DATA);
    } catch (err) {
        console.error("Failed to load ownership data:", err);
    }
}

// Graph Traversal
export function collectSubgraph() {
    const nodesMap = new Map();
    const eds = [];
    const level = {};
    nodesMap.set(selectedKey.value, ent(selectedKey.value));
    level[selectedKey.value] = 0;
    
    const q = [{ key: selectedKey.value, lvl: 0, d: 0 }];
    const seen = new Set([selectedKey.value + ':0']);
    
    while (q.length) {
        const cur = q.shift();
        if (cur.d >= depth.value) continue;
        let candidates = [];
        
        const expandDown = (directionMode.value !== 'up') && (cur.d === 0);
        if (directionMode.value !== 'down') {
            candidates = candidates.concat(filteredEdgesGlobal(inEdges.value[cur.key]).slice(0, 8).map(e => ({ edge: e, next: e.from, lvl: cur.lvl - 1 })));
        }
        if (expandDown) {
            candidates = candidates.concat(filteredEdgesGlobal(outEdges.value[cur.key]).slice(0, 8).map(e => ({ edge: e, next: e.to, lvl: cur.lvl + 1 })));
        }
        
        for (const x of candidates) {
            const key = x.next;
            nodesMap.set(key, ent(key));
            eds.push(x.edge);
            if (level[key] === undefined || Math.abs(x.lvl) < Math.abs(level[key])) level[key] = x.lvl;
            const state = key + ':' + x.lvl;
            if (!seen.has(state)) {
                seen.add(state);
                q.push({ key, lvl: x.lvl, d: cur.d + 1 });
            }
        }
    }
    
    return { 
        nodes: [...nodesMap.values()], 
        edges: [...new Map(eds.map(e => [e.from + '>' + e.to, e])).values()], 
        level 
    };
}

export function dfsUp(key, maxDepth = 4) {
    const paths = [];
    function walk(cur, path, eff, d) {
        const incoming = filteredEdgesGlobal(inEdges.value[cur] || []);
        if (d >= maxDepth || incoming.length === 0) {
            if (path.length) paths.push({ path: [...path], eff });
            return;
        }
        for (const e of incoming) {
            if (path.some(p => p.key === e.from)) continue;
            const step = { key: e.from, label: ent(e.from).ticker || ent(e.from).label, pct: e.pct, edge: e };
            walk(e.from, [step, ...path], eff * (e.pct / 100), d + 1);
        }
    }
    walk(key, [], 1, 0);
    return paths.sort((a, b) => b.eff - a.eff).slice(0, 30);
}

export function dfsDown(key, maxDepth = 4) {
    const paths = [];
    function walk(cur, path, eff, d) {
        const outgoing = filteredEdgesGlobal(outEdges.value[cur] || []);
        if (d >= maxDepth || outgoing.length === 0) {
            if (path.length) paths.push({ path: [...path], eff });
            return;
        }
        for (const e of outgoing) {
            if (path.some(p => p.key === e.to)) continue;
            const step = { key: e.to, label: ent(e.to).ticker || ent(e.to).label, pct: e.pct, edge: e };
            walk(e.to, [...path, step], eff * (e.pct / 100), d + 1);
        }
    }
    walk(key, [], 1, 0);
    return paths.sort((a, b) => b.eff - a.eff).slice(0, 30);
}

export function ownershipIntel() {
    const e = ent(selectedKey.value);
    const audit = audits.value[selectedKey.value];
    const owners = filteredInEdges.value.slice().sort((a, b) => b.pct - a.pct);
    const flags = [];
    const top = owners[0];
    
    if (top) {
        const hi = top.pct >= 50;
        flags.push({ t: `${hi ? 'Pengendali tunggal' : 'Top owner'} ${fmtPct(top.pct)}`, tone: hi ? 'warn' : 'mut' });
    }
    if (audit && audit.hhi != null) {
        flags.push({ t: `HHI ${audit.hhi}`, tone: audit.hhi > 2500 ? 'bad' : audit.hhi > 1500 ? 'warn' : 'good' });
    }
    if (audit && audit.residual != null) {
        flags.push({ t: `Residual ${fmtPct(audit.residual)}`, tone: audit.residual >= 25 ? 'bad' : audit.residual >= 12 ? 'warn' : 'good' });
    }
    if (audit && audit.floatProxy != null) {
        flags.push({ t: `Float ${fmtPct(audit.floatProxy)}`, tone: audit.floatProxy < 12 ? 'warn' : 'good' });
    }
    let f = 0, l = 0;
    for (const o of owners) {
        if (o.local_foreign === 'F') f += o.pct;
        else if (o.local_foreign === 'L') l += o.pct;
    }
    let fs = null;
    if (f + l > 0) {
        fs = f / (f + l) * 100;
        flags.push({ t: `Asing ${fs.toFixed(0)}%`, tone: fs >= 40 ? 'warn' : 'mut' });
    }
    const chs = (changeByTo.value[selectedKey.value] || []);
    let net = 0;
    for (const c of chs) net += (c.deltaShares || 0);
    if (chs.length) {
        flags.push({ t: `${net >= 0 ? 'Net akumulasi' : 'Net distribusi'} ${fmtShares(Math.abs(net))}`, tone: net >= 0 ? 'good' : 'warn' });
    }
    const parts = [];
    if (top) parts.push(`${e.ticker || e.label} dikuasai <b>${top.investor}</b> (${fmtPct(top.pct)})`);
    if (audit && audit.controlLabel) parts.push(audit.controlLabel.toLowerCase());
    if (fs != null) parts.push(`porsi asing ~${fs.toFixed(0)}% dari kepemilikan terpetakan`);
    if (audit && audit.residual != null) parts.push(`residual proxy ${fmtPct(audit.residual)} belum terungkap`);
    
    const narrative = parts.join('; ') + '.';
    let group = null;
    
    if (top) {
        const ck = top.from;
        const holds = (outEdges.value[ck] || []).filter(x => x.pct >= 5).slice().sort((a, b) => b.pct - a.pct);
        const members = holds.map(x => ({ key: x.to, ticker: x.ticker, issuer: x.issuer, pct: x.pct })).filter(m => m.key !== selectedKey.value);
        if (members.length) group = { controller: top.investor, members: members.slice(0, 8) };
    }
    
    const affiliation = affiliationAudit(selectedKey.value);
    
    return { flags, narrative, group, affiliation };
}

export function exportChanges() {
    const rel = relatedChanges.value;
    const header = ['direction', 'investor', 'ticker', 'issuer', 'previous_shares', 'latest_shares', 'delta_shares', 'previous_pct', 'latest_pct', 'delta_pct'];
    const rows = rel.map(c => [c.direction, c.investor, c.ticker, c.issuer, c.prevShares, c.latestShares, c.deltaShares, c.prevPct, c.latestPct, c.deltaPct]);
    const csv = [header, ...rows].map(r => r.map(v => `"${String(v).replace(/"/g, '""')}"`).join(',')).join('\n');
    const blob = new Blob([csv], { type: 'text/csv' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'avenir_ownership_changes_selected.csv';
    a.click();
}

class UnionFind {
    constructor(n) {
        this.parent = Array.from({ length: n }, (_, i) => i);
    }
    find(i) {
        if (this.parent[i] === i) return i;
        return this.parent[i] = this.find(this.parent[i]);
    }
    union(i, j) {
        const rootI = this.find(i);
        const rootJ = this.find(j);
        if (rootI !== rootJ) {
            this.parent[rootI] = rootJ;
        }
    }
}

export function affiliationAudit(issuerKey) {
    const owners = filteredInEdges.value.filter(e => e.pct > 0);
    if (owners.length < 2) return null;
    
    const STOPWORDS = new Set(["PT", "TBK", "INVESTAMA", "PERKASA", "JAYA", "PRATAMA", "HOLDING", "SEKURITAS", "CAPITAL", "INDONESIA", "CORP", "CORPORATION", "PERSERO", "LIMITED", "LTD", "INC", "GRP", "GROUP", "BANK", "ASURANSI", "DANA", "PENSIUN", "YAYASAN", "KOPERASI", "TERBATAS"]);
    
    const tokensList = owners.map(o => {
        const name = o.investor || ent(o.from).label || o.from;
        const words = name.toUpperCase().replace(/[^A-Z0-9\s]/g, '').split(/\s+/);
        return new Set(words.filter(w => w.length >= 4 && !STOPWORDS.has(w)));
    });
    
    const uf = new UnionFind(owners.length);
    
    for (let i = 0; i < owners.length; i++) {
        for (let j = i + 1; j < owners.length; j++) {
            const intersection = [...tokensList[i]].filter(x => tokensList[j].has(x));
            if (intersection.length > 0) {
                uf.union(i, j);
            } else {
                const fromI = outEdges.value[owners[i].from] || [];
                const fromJ = outEdges.value[owners[j].from] || [];
                if (fromI.some(e => e.to === owners[j].from) || fromJ.some(e => e.to === owners[i].from)) {
                    uf.union(i, j);
                }
            }
        }
    }
    
    const clusters = {};
    for (let i = 0; i < owners.length; i++) {
        const root = uf.find(i);
        if (!clusters[root]) clusters[root] = [];
        clusters[root].push(i);
    }
    
    let bloc = null;
    let maxBlocSum = 0;
    
    for (const root in clusters) {
        if (clusters[root].length >= 2) {
            let sum = 0;
            for (const idx of clusters[root]) sum += owners[idx].pct;
            if (sum > maxBlocSum) {
                maxBlocSum = sum;
                const allTokens = [];
                for (const idx of clusters[root]) {
                    allTokens.push(...tokensList[idx]);
                }
                const tokenCounts = {};
                for (const t of allTokens) tokenCounts[t] = (tokenCounts[t] || 0) + 1;
                let mostShared = null;
                let mostCount = 0;
                for (const t in tokenCounts) {
                    if (tokenCounts[t] > mostCount) {
                        mostShared = t;
                        mostCount = tokenCounts[t];
                    }
                }
                
                bloc = {
                    members: clusters[root].map(idx => ({ investor: owners[idx].investor, pct: owners[idx].pct, from: owners[idx].from })),
                    sum: sum,
                    token: mostShared
                };
            }
        }
    }
    
    if (!bloc) return null;
    
    let largest = null;
    let maxPct = 0;
    for (const o of owners) {
        if (o.pct > maxPct) {
            maxPct = o.pct;
            largest = { investor: o.investor, pct: o.pct };
        }
    }
    
    bloc.members.sort((a, b) => b.pct - a.pct);
    const hidden = bloc.sum > maxPct;
    return { bloc, largest, hidden };
}

export { dataLoaded, entities, edges, changes, audits, investorSummaries, stats, entityArr, outEdges, inEdges, changeByFrom, changeByTo };
