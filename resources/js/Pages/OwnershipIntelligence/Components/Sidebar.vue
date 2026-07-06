<script setup>
import { stats } from '../../../Composables/useOwnershipLogic';
import { activeTab } from '../../../Composables/useOwnershipLogic';
</script>

<template>
  <aside class="side">
    <div class="brand">
      <img src="/favicon.png" class="logo" style="background: none;" alt="Avenir" />
      <div><h1>AVENIR</h1><p>RESEARCH</p></div>
    </div>
    <div class="navTitle">Ownership Intelligence</div>
    <nav class="nav">
      <a :class="{ active: activeTab === 'networkPane' }" href="#" @click.prevent="activeTab = 'networkPane'"><span>◎</span> Linked Network</a>
      <a :class="{ active: activeTab === 'changesPane' }" href="#" @click.prevent="activeTab = 'changesPane'"><span>↕</span> Change Monitor</a>
      <a :class="{ active: activeTab === 'auditPane' }" href="#" @click.prevent="activeTab = 'auditPane'"><span>◌</span> Issuer Audit</a>
      <a :class="{ active: activeTab === 'proxyPane' }" href="#" @click.prevent="activeTab = 'proxyPane'"><span>⛓</span> Proxy Explorer</a>
      <a :class="{ active: activeTab === 'methodPane' }" href="#" @click.prevent="activeTab = 'methodPane'"><span>ⓘ</span> Methodology</a>
    </nav>
    <div class="dataCard">
      <div class="k">Latest source date</div>
      <div class="v">{{ stats.latestDate || '-' }}</div>
      <div class="k" style="margin-top:12px">Previous comparable date</div>
      <div class="v">{{ stats.prevDate || '-' }}</div>
      <div class="k" style="margin-top:12px">Coverage</div>
      <div class="v"><span>{{ stats.issuersLatest || 0 }}</span> issuers</div>
    </div>
    <div class="dataCard warn">
      <div class="k">Important proxy note</div>
      <p class="small muted">Residual and float metrics are proxies. The reports show disclosed holders in the file; they are not official free-float calculations. Scrip ownership and unclassified accounts reduce confidence.</p>
    </div>
  </aside>
</template>
