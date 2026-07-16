<script setup>
import { onMounted, onUnmounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

import { loadOwnershipData, dataLoaded } from '../../Composables/useOwnershipLogic';

import Sidebar from './Components/Sidebar.vue';
import HeaderPanel from './Components/HeaderPanel.vue';
import SelectorPanel from './Components/SelectorPanel.vue';
import KpiGrid from './Components/KpiGrid.vue';
import NetworkPane from './Components/NetworkPane.vue';
import ChangeMonitorPane from './Components/ChangeMonitorPane.vue';
import IssuerAuditPane from './Components/IssuerAuditPane.vue';
import ProxyExplorerPane from './Components/ProxyExplorerPane.vue';
import MethodologyPane from './Components/MethodologyPane.vue';
import GroupPane from './Components/GroupPane.vue';
import GovPane from './Components/GovPane.vue';
import InstitutionalPane from './Components/InstitutionalPane.vue';
import ShadowPane from './Components/ShadowPane.vue';
import InsiderPane from './Components/InsiderPane.vue';
import KomposisiPane from './Components/KomposisiPane.vue';
import KlasifikasiPane from './Components/KlasifikasiPane.vue';
import TipePane from './Components/TipePane.vue';
import AdminPane from './Components/AdminPane.vue';

function vSwitchTab(tab) {
    if (window.switchTab) window.switchTab(tab);
}

function vSwitchMode(mode) {
    if (window.switchMode) window.switchMode(mode);
}

onMounted(async () => {
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = '/css/ownership-intelligence.css';
    link.id = 'ownership-css';
    document.head.appendChild(link);

    // Load mock data (force=true so it reloads even if production data was already loaded)
    await loadOwnershipData('/ownership_mock.json', true);
});

onUnmounted(() => {
    const link = document.getElementById('ownership-css');
    if (link) document.head.removeChild(link);
});
</script>

<template>
  <Head>
    <title>Avenir Ownership Intelligence (Mockup)</title>
    <meta name="description" content="Ownership Intelligence Mockup - Avenir." />
  </Head>

  <AppLayout>
    <div v-if="!dataLoaded" style="display:flex; justify-content:center; align-items:center; height:100vh;">
      <h2 style="color:white;">Loading data...</h2>
    </div>
    <div v-else class="app">
      <Sidebar />
      <main class="main">
        <HeaderPanel />

        <section class="modeToggle">
          <button class="modeBtn on" data-mode="detail" @click="vSwitchMode('detail')">🔍 Detail Emiten</button>
          <button class="modeBtn" data-mode="global" @click="vSwitchMode('global')">🌐 Analisis Global</button>
        </section>

        <SelectorPanel />
        <KpiGrid />

        <section class="panel">
          <div class="tabbar">
            <button class="on" data-tab="networkPane" data-mode="detail" @click="vSwitchTab('networkPane')">Jaringan Emiten</button>
            <button data-tab="auditPane" data-mode="detail" @click="vSwitchTab('auditPane')">Audit &amp; Detail</button>
            <button data-tab="changesPane" data-mode="global" @click="vSwitchTab('changesPane')">Change Monitor</button>
            <button data-tab="groupPane" data-mode="global" @click="vSwitchTab('groupPane')">Groups</button>
            <button data-tab="govPane" data-mode="global" @click="vSwitchTab('govPane')">Government Layer</button>
            <button data-tab="instPane" data-mode="global" @click="vSwitchTab('instPane')">Institutional</button>
            <button data-tab="shadowPane" data-mode="global" @click="vSwitchTab('shadowPane')">Shadow Network</button>
            <button data-tab="insiderPane" data-mode="global" @click="vSwitchTab('insiderPane')">Pergerakan 5%</button>
            <button data-tab="komposisiPane" data-mode="global" @click="vSwitchTab('komposisiPane')">Komposisi 1%</button>
            <button data-tab="klasifikasiPane" data-mode="global" @click="vSwitchTab('klasifikasiPane')">Klasifikasi</button>
            <button data-tab="tipePane" data-mode="global" @click="vSwitchTab('tipePane')">Domestik/Asing</button>
            <button data-tab="proxyPane" data-mode="global" @click="vSwitchTab('proxyPane')">Proxy Explorer</button>
            <button data-tab="methodPane" data-mode="global" @click="vSwitchTab('methodPane')">Methodology</button>
          </div>

          <div class="tabpane on" id="networkPane"><NetworkPane /></div>
          <div class="tabpane" id="auditPane"><IssuerAuditPane /></div>
          <div class="tabpane" id="changesPane"><ChangeMonitorPane /></div>
          <div class="tabpane" id="groupPane"><GroupPane /></div>
          <div class="tabpane" id="govPane"><GovPane /></div>
          <div class="tabpane" id="instPane"><InstitutionalPane /></div>
          <div class="tabpane" id="shadowPane"><ShadowPane /></div>
          <div class="tabpane" id="insiderPane"><InsiderPane /></div>
          <div class="tabpane" id="komposisiPane"><KomposisiPane /></div>
          <div class="tabpane" id="klasifikasiPane"><KlasifikasiPane /></div>
          <div class="tabpane" id="tipePane"><TipePane /></div>
          <div class="tabpane" id="proxyPane"><ProxyExplorerPane /></div>
          <div class="tabpane" id="methodPane"><MethodologyPane /></div>
          <div class="tabpane" id="adminPane"><AdminPane /></div>
        </section>

        <div class="footerNote">Source: KSEI/BEI ownership disclosure data. Avenir calculations are analytical proxies and should be verified against issuer filings and official free-float data before investment use.</div>
      </main>
    </div>
  </AppLayout>
</template>
