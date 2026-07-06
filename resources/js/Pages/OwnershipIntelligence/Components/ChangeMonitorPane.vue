<script setup>
import { computed } from 'vue';
import { 
    relatedChanges, setSelected, fmtShares, fmtBps, fmtPct, edgeClass 
} from '../../../Composables/useOwnershipLogic';

const buys = computed(() => relatedChanges.value.filter(c => c.deltaShares > 0).slice(0, 12));
const sells = computed(() => relatedChanges.value.filter(c => c.deltaShares < 0).slice(0, 12));

</script>

<template>
  <div class="panelBody">
    <div class="twoCols">
      <div class="panel" style="box-shadow:none">
        <div class="panelHead">
          <h3>Top net buys / increases</h3>
          <span class="muted small">Latest vs previous report</span>
        </div>
        <div class="tableWrap">
          <table class="tbl">
            <thead>
              <tr><th>Investor</th><th>Issuer</th><th>Buy / Increase</th><th>Pct Δ</th></tr>
            </thead>
            <tbody>
              <tr v-for="c in buys" :key="c.from + c.to">
                <td>{{ c.investor }}</td>
                <td><span class="linkish" @click="setSelected(c.to)">{{ c.ticker }}</span></td>
                <td class="green">+{{ fmtShares(c.deltaShares) }}</td>
                <td>{{ fmtBps(c.deltaPct) }}</td>
              </tr>
              <tr v-if="buys.length === 0"><td colspan="4" class="muted">No data under current filter.</td></tr>
            </tbody>
          </table>
        </div>
      </div>
      
      <div class="panel" style="box-shadow:none">
        <div class="panelHead">
          <h3>Top net sells / decreases</h3>
          <span class="muted small">Latest vs previous report</span>
        </div>
        <div class="tableWrap">
          <table class="tbl">
            <thead>
              <tr><th>Investor</th><th>Issuer</th><th>Sell / Decrease</th><th>Pct Δ</th></tr>
            </thead>
            <tbody>
              <tr v-for="c in sells" :key="c.from + c.to">
                <td>{{ c.investor }}</td>
                <td><span class="linkish" @click="setSelected(c.to)">{{ c.ticker }}</span></td>
                <td class="red">{{ fmtShares(c.deltaShares) }}</td>
                <td>{{ fmtBps(c.deltaPct) }}</td>
              </tr>
              <tr v-if="sells.length === 0"><td colspan="4" class="muted">No data under current filter.</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    
    <div class="panel" style="margin-top:14px;box-shadow:none">
      <div class="panelHead">
        <h3>All changes linked to selected entity</h3>
        <span class="muted small">{{ relatedChanges.length }} changed links</span>
      </div>
      <div class="tableWrap">
        <table class="tbl">
          <thead>
            <tr><th>Direction</th><th>Investor</th><th>Issuer</th><th>Previous</th><th>Latest</th><th>Shares Δ</th><th>Pct Δ</th></tr>
          </thead>
          <tbody>
            <tr v-for="c in relatedChanges" :key="c.from + c.to">
              <td><span :class="['tag', edgeClass(c)]">{{ c.direction }}</span></td>
              <td><span class="linkish" @click="setSelected(c.from)">{{ c.investor }}</span></td>
              <td><span class="linkish" @click="setSelected(c.to)">{{ c.ticker }} · {{ c.issuer }}</span></td>
              <td>{{ fmtShares(c.prevShares) }} · {{ fmtPct(c.prevPct) }}</td>
              <td>{{ fmtShares(c.latestShares) }} · {{ fmtPct(c.latestPct) }}</td>
              <td :class="c.deltaShares >= 0 ? 'green' : 'red'">
                {{ c.deltaShares > 0 ? '+' : '' }}{{ fmtShares(c.deltaShares) }}
              </td>
              <td>{{ fmtBps(c.deltaPct) }}</td>
            </tr>
            <tr v-if="relatedChanges.length === 0"><td colspan="7" class="muted">No data under current filter.</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
