<script setup>
import { computed } from 'vue';
import { 
    selectedKey, depth, ent, shortLabel, dfsUp, dfsDown, setSelected, fmtPct 
} from '../../../Composables/useOwnershipLogic';

const label = computed(() => {
    const e = ent(selectedKey.value);
    return e.ticker || shortLabel(e.label, 16);
});

const upstream = computed(() => dfsUp(selectedKey.value, depth.value));
const downstream = computed(() => dfsDown(selectedKey.value, depth.value));
</script>

<template>
  <div class="panelBody">
    <div class="twoCols">
      <div class="panel" style="box-shadow:none">
        <div class="panelHead">
          <h3>Upstream control paths</h3>
          <span class="muted small">Who ultimately owns this?</span>
        </div>
        <div class="panelBody" id="upstreamPaths">
          <template v-if="upstream.length > 0">
            <div class="pathItem" v-for="(p, index) in upstream" :key="'up'+index">
              <div>
                <div class="path">
                  <template v-for="(x, i) in p.path" :key="x.key">
                    <span class="linkish" @click="setSelected(x.key)">{{ x.label }}</span> 
                    <span class="muted">({{ fmtPct(x.pct) }})</span> 
                    <span v-if="i !== p.path.length - 1"> → </span>
                  </template>
                  → <b>{{ label }}</b>
                </div>
                <div class="muted small">Effective path stake</div>
              </div>
              <div class="eff">{{ fmtPct(p.eff * 100) }}</div>
            </div>
          </template>
          <p v-else class="muted">No upstream path under current depth/filter.</p>
        </div>
      </div>
      
      <div class="panel" style="box-shadow:none">
        <div class="panelHead">
          <h3>Downstream economic exposure</h3>
          <span class="muted small">What does this entity own?</span>
        </div>
        <div class="panelBody" id="downstreamPaths">
          <template v-if="downstream.length > 0">
            <div class="pathItem" v-for="(p, index) in downstream" :key="'down'+index">
              <div>
                <div class="path">
                  <b>{{ label }}</b> → 
                  <template v-for="(x, i) in p.path" :key="x.key">
                    <span class="linkish" @click="setSelected(x.key)">{{ x.label }}</span> 
                    <span class="muted">({{ fmtPct(x.pct) }})</span> 
                    <span v-if="i !== p.path.length - 1"> → </span>
                  </template>
                </div>
                <div class="muted small">Effective path stake</div>
              </div>
              <div class="eff">{{ fmtPct(p.eff * 100) }}</div>
            </div>
          </template>
          <p v-else class="muted">No downstream path under current depth/filter.</p>
        </div>
      </div>
    </div>
  </div>
</template>
