<script setup>
import { computed } from 'vue';
import { 
    selectedKey, ent, audits, investorSummaries, fmtPct 
} from '../../../Composables/useOwnershipLogic';

const entity = computed(() => ent(selectedKey.value));
const audit = computed(() => audits.value[selectedKey.value]);

const rows = computed(() => {
    if (!audit.value) return [];
    return [
        ['Reported coverage', fmtPct(audit.value.coverage), 'Sum of disclosed holders in the file'],
        ['Residual proxy', fmtPct(audit.value.residual), '100% minus reported coverage'],
        ['Float proxy', fmtPct(audit.value.floatProxy), 'Residual + small financial/institutional holders + small individuals'],
        ['Top 1 / Top 3 / Top 5', `${fmtPct(audit.value.top1)} / ${fmtPct(audit.value.top3)} / ${fmtPct(audit.value.top5)}`, 'Control concentration'],
        ['HHI', audit.value.hhi, 'Higher = more concentrated'],
        ['Nakamoto-50', audit.value.nakamoto50 || '-', 'Minimum holders required to cross 50%'],
        ['Foreign exposure', fmtPct(audit.value.foreignPct), 'Foreign-classified holder points'],
        ['Scrip ratio', fmtPct(audit.value.scripRatio), 'Scrip holdings / reported holdings'],
        ['Listed-holder exposure', fmtPct(audit.value.listedHolderPct), 'Holders mapped to listed issuers'],
        ['Audit confidence', audit.value.confidence + ' / 100', 'Analytical confidence score']
    ];
});
</script>

<template>
  <div class="panelBody" id="auditBody">
    <div v-if="!audit" class="readCard">
      <h4>{{ entity.label }}</h4>
      <p>This is an investor / non-issuer node. Issuer-level concentration audit is not applicable. Use the holdings table and proxy explorer to audit its direct and indirect exposure.</p>
    </div>
    
    <template v-else>
      <div class="twoCols">
        <div class="readCard">
          <h4>{{ audit.ticker }} · {{ audit.issuer }}</h4>
          <p>
            Audit status: <b>{{ audit.controlLabel }}</b>. 
            Network risk: <b :class="audit.riskLabel === 'High' ? 'red' : audit.riskLabel === 'Medium' ? 'yellow' : 'green'">{{ audit.riskLabel }}</b> 
            ({{ audit.riskScore }}/100). Confidence {{ audit.confidence }}/100.
          </p>
        </div>
        <div class="readCard">
          <h4>Proxy interpretation</h4>
          <p>Residual proxy is not official free float. If residual is high, many holders are outside the disclosed table. If scrip ratio is high, ownership classification may be less complete because scrip data comes from eBAE.</p>
        </div>
      </div>
      <div class="tableWrap" style="margin-top:14px">
        <table class="tbl">
          <thead>
            <tr><th>Metric</th><th>Value</th><th>Meaning</th></tr>
          </thead>
          <tbody>
            <tr v-for="r in rows" :key="r[0]">
              <td>{{ r[0] }}</td>
              <td><b>{{ r[1] }}</b></td>
              <td class="muted">{{ r[2] }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </template>
  </div>
</template>
