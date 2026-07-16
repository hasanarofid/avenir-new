<script setup>
import { stats } from '../../../Composables/useOwnershipLogic';

function vSwitchTab(tab) {
    if (window.switchTab) window.switchTab(tab);
}

function vSwitchMode(mode) {
    if (window.switchMode) window.switchMode(mode);
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
      <!-- Detail mode items — vanilla switchMode controls display via data-mode -->
      <a data-nav="networkPane" data-mode="detail" href="#" @click.prevent="vSwitchTab('networkPane')">
        <span class="ic">◎</span> Jaringan Emiten
      </a>
      <a data-nav="auditPane" data-mode="detail" href="#" @click.prevent="vSwitchTab('auditPane')">
        <span class="ic">◌</span> Audit &amp; Detail
      </a>

      <!-- Global mode items — initially hidden by vanilla switchMode('detail') -->
      <div class="navSep">Analisis Global</div>
      <a data-nav="changesPane" data-mode="global" href="#" @click.prevent="vSwitchTab('changesPane')">
        <span class="ic">↕</span> Change Monitor
      </a>
      <a data-nav="groupPane" data-mode="global" href="#" @click.prevent="vSwitchTab('groupPane')">
        <span class="ic">◈</span> Conglomerate Groups
      </a>
      <a data-nav="govPane" data-mode="global" href="#" @click.prevent="vSwitchTab('govPane')">
        <span class="ic">🏛</span> Government Layer
      </a>
      <a data-nav="instPane" data-mode="global" href="#" @click.prevent="vSwitchTab('instPane')">
        <span class="ic">▤</span> Institutional
      </a>
      <a data-nav="shadowPane" data-mode="global" href="#" @click.prevent="vSwitchTab('shadowPane')">
        <span class="ic">◇</span> Shadow Network
      </a>
      <a data-nav="insiderPane" data-mode="global" href="#" @click.prevent="vSwitchTab('insiderPane')">
        <span class="ic">⇅</span> Pergerakan 5%
      </a>
      <a data-nav="komposisiPane" data-mode="global" href="#" @click.prevent="vSwitchTab('komposisiPane')">
        <span class="ic">◧</span> Komposisi 1%
      </a>
      <a data-nav="klasifikasiPane" data-mode="global" href="#" @click.prevent="vSwitchTab('klasifikasiPane')">
        <span class="ic">▦</span> Klasifikasi
      </a>
      <a data-nav="tipePane" data-mode="global" href="#" @click.prevent="vSwitchTab('tipePane')">
        <span class="ic">◐</span> Domestik/Asing
      </a>
      <a data-nav="proxyPane" data-mode="global" href="#" @click.prevent="vSwitchTab('proxyPane')">
        <span class="ic">⛓</span> Proxy Explorer
      </a>
      <a data-nav="methodPane" data-mode="global" href="#" @click.prevent="vSwitchTab('methodPane')">
        <span class="ic">ⓘ</span> Methodology
      </a>
    </nav>

    <div class="dataCard">
      <div class="k">Latest source date</div>
      <div class="v" id="sideDate">{{ stats.latestDate || '-' }}</div>
      <div class="k" style="margin-top:12px">Coverage</div>
      <div class="v"><span id="sideIssuers">{{ stats.issuersLatest || 0 }}</span> issuers</div>
      <div class="k" style="margin-top:12px">Gov-held issuers</div>
      <div class="v"><span id="sideGov">{{ stats.govIssuers || 0 }}</span> issuers</div>
    </div>
    <div class="dataCard info">
      <div class="k">Proxy note</div>
      <p class="small" style="margin:6px 0 0;color:var(--text2);line-height:1.5">Residual and float metrics are proxies from disclosed holders, not official free-float. Scrip and unclassified accounts reduce confidence.</p>
    </div>
  </aside>
</template>
