<template>
  <div class="panelBody">
    <div class="twoCols">
      <div class="panel" style="box-shadow:none"><div class="panelHead"><h3>Upstream control paths</h3><span class="muted small">Who ultimately owns this?</span></div><div class="panelBody" id="upstreamPaths"></div></div>
      <div class="panel" style="box-shadow:none"><div class="panelHead"><h3>Downstream exposure</h3><span class="muted small">What does this entity own?</span></div><div class="panelBody" id="downstreamPaths"></div></div>
    </div>
  </div>
</template>
<script setup>
import { onMounted, watch } from 'vue';
import { activeTab } from '../../../Composables/useOwnershipLogic';
onMounted(() => { if (window.renderProxy) window.renderProxy(); });
watch(activeTab, (val) => { if (val === 'proxyPane' && window.renderProxy) setTimeout(() => window.renderProxy(), 50); });
</script>
