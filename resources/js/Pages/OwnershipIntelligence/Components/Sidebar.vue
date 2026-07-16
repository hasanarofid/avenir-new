<script setup>
import { stats, activeTab, activeMode } from '../../../Composables/useOwnershipLogic';

function nav(tab, mode) {
    activeTab.value = tab;
    activeMode.value = mode;
}
</script>

<template>
  <aside class="side">
    <div class="brand">
      <img src="/favicon.png" class="logo" style="background:none" alt="Avenir" />
      <div><h1>AVENIR</h1><p>RESEARCH</p></div>
    </div>
    <div class="navTitle">Ownership Intelligence</div>
    <nav class="nav">
      <!-- Detail mode items: only show in detail mode -->
      <a v-show="activeMode === 'detail'" :class="{ active: activeTab === 'networkPane' }" href="#" @click.prevent="nav('networkPane','detail')">
        <span class="ic">◎</span> Jaringan Emiten
      </a>
      <a v-show="activeMode === 'detail'" :class="{ active: activeTab === 'auditPane' }" href="#" @click.prevent="nav('auditPane','detail')">
        <span class="ic">◌</span> Audit &amp; Detail
      </a>

      <div v-show="activeMode === 'global'" class="navSep">Analisis Global</div>
        <a v-show="activeMode === 'global'" :class="{ active: activeTab === 'changesPane' }" href="#" @click.prevent="nav('changesPane','global')">
          <span class="ic">↕</span> Change Monitor
        </a>
        <a v-show="activeMode === 'global'" :class="{ active: activeTab === 'groupPane' }" href="#" @click.prevent="nav('groupPane','global')">
          <span class="ic">◈</span> Conglomerate Groups
        </a>
        <a v-show="activeMode === 'global'" :class="{ active: activeTab === 'govPane' }" href="#" @click.prevent="nav('govPane','global')">
          <span class="ic">🏛</span> Government Layer
        </a>
        <a v-show="activeMode === 'global'" :class="{ active: activeTab === 'instPane' }" href="#" @click.prevent="nav('instPane','global')">
          <span class="ic">▤</span> Institutional
        </a>
        <a v-show="activeMode === 'global'" :class="{ active: activeTab === 'shadowPane' }" href="#" @click.prevent="nav('shadowPane','global')">
          <span class="ic">◇</span> Shadow Network
        </a>
        <a v-show="activeMode === 'global'" :class="{ active: activeTab === 'insiderPane' }" href="#" @click.prevent="nav('insiderPane','global')">
          <span class="ic">⇅</span> Pergerakan 5%
        </a>
        <a v-show="activeMode === 'global'" :class="{ active: activeTab === 'komposisiPane' }" href="#" @click.prevent="nav('komposisiPane','global')">
          <span class="ic">◧</span> Komposisi 1%
        </a>
        <a v-show="activeMode === 'global'" :class="{ active: activeTab === 'klasifikasiPane' }" href="#" @click.prevent="nav('klasifikasiPane','global')">
          <span class="ic">▦</span> Klasifikasi
        </a>
        <a v-show="activeMode === 'global'" :class="{ active: activeTab === 'tipePane' }" href="#" @click.prevent="nav('tipePane','global')">
          <span class="ic">◐</span> Domestik/Asing
        </a>
        <a v-show="activeMode === 'global'" :class="{ active: activeTab === 'proxyPane' }" href="#" @click.prevent="nav('proxyPane','global')">
          <span class="ic">⛓</span> Proxy Explorer
        </a>
        <a v-show="activeMode === 'global'" :class="{ active: activeTab === 'methodPane' }" href="#" @click.prevent="nav('methodPane','global')">
          <span class="ic">ⓘ</span> Methodology
        </a>
    </nav>

    <div class="dataCard">
      <div class="k">Latest source date</div>
      <div class="v">{{ stats.latestDate || '-' }}</div>
      <div class="k" style="margin-top:12px">Coverage</div>
      <div class="v"><span>{{ stats.issuersLatest || 0 }}</span> issuers</div>
      <div class="k" style="margin-top:12px">Gov-held issuers</div>
      <div class="v"><span>{{ stats.govIssuers || 0 }}</span> issuers</div>
    </div>
    <div class="dataCard info">
      <div class="k">Proxy note</div>
      <p class="small" style="margin:6px 0 0;color:var(--text2);line-height:1.5">Residual and float metrics are proxies from disclosed holders, not official free-float. Scrip and unclassified accounts reduce confidence.</p>
    </div>
  </aside>
</template>
