<script setup>
import { computed } from 'vue';
import { 
    selectedKey, ent, audits, investorSummaries, 
    inEdges, outEdges, relatedChanges, fmtPct, fmtShares 
} from '../../../Composables/useOwnershipLogic';

const kpis = computed(() => {
    const key = selectedKey.value;
    const e = ent(key);
    const audit = audits.value[key];
    const inv = investorSummaries.value[key];
    
    const ownerCount = (inEdges.value[key] || []).length;
    const holdCount = (outEdges.value[key] || []).length;
    
    const relChanges = relatedChanges.value;
    const buy = relChanges.filter(c => c.deltaShares > 0).reduce((a, c) => a + c.deltaShares, 0);
    const sell = -relChanges.filter(c => c.deltaShares < 0).reduce((a, c) => a + c.deltaShares, 0);
    
    const kpisList = [];
    if (e.kind === 'issuer') {
        kpisList.push({ label: 'Audit confidence', value: audit ? audit.confidence + ' / 100' : '-', note: audit ? 'Issuer-level data quality' : 'No issuer audit' });
        kpisList.push({ label: 'Control concentration', value: audit ? fmtPct(audit.top1) : '-', note: audit ? audit.controlLabel : '' });
        kpisList.push({ label: 'Residual proxy', value: audit ? fmtPct(audit.residual) : '-', note: '100% - reported coverage' });
        kpisList.push({ label: 'Nakamoto-50', value: audit && audit.nakamoto50 ? audit.nakamoto50 + ' holders' : '-', note: 'Minimum holders to reach 50%' });
        kpisList.push({ label: 'Upstream owners', value: ownerCount, note: 'Direct owners in latest file' });
        kpisList.push({ label: 'Downstream holdings', value: holdCount, note: 'Issuer as investor elsewhere' });
    } else {
        kpisList.push({ label: 'Direct holdings', value: holdCount, note: 'Issuers held in latest file' });
        kpisList.push({ label: 'Ownership points', value: inv ? fmtPct(inv.ownershipPoints) : '-', note: 'Sum of direct reported stakes' });
        kpisList.push({ label: 'Largest position', value: inv ? `${inv.largestTicker} ${fmtPct(inv.largestPct)}` : '-', note: inv ? inv.largestIssuer : '' });
        kpisList.push({ label: 'Bought / increased', value: fmtShares(buy), note: 'Latest vs previous' });
        kpisList.push({ label: 'Sold / reduced', value: fmtShares(sell), note: 'Latest vs previous' });
        kpisList.push({ label: 'Upstream owners', value: ownerCount, note: 'Owners if this is listed/mapped' });
    }
    return kpisList;
});
</script>

<template>
  <section class="gridKpi" id="kpiGrid">
    <div class="kpi" v-for="(kpi, index) in kpis" :key="index">
      <div class="label">{{ kpi.label }}</div>
      <div class="num">{{ kpi.value }}</div>
      <div class="note">{{ kpi.note }}</div>
    </div>
  </section>
</template>
