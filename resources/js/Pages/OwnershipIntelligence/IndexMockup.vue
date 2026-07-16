<script setup>
import { onMounted, onUnmounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

import { loadOwnershipData, dataLoaded, activeTab, activeMode } from '../../Composables/useOwnershipLogic';

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

const detailTabs = ['networkPane', 'auditPane'];
const globalTabs = ['changesPane', 'groupPane', 'govPane', 'instPane', 'shadowPane', 'insiderPane', 'komposisiPane', 'klasifikasiPane', 'tipePane', 'proxyPane', 'methodPane', 'adminPane'];

function switchMode(mode) {
    activeMode.value = mode;
    if (mode === 'detail' && !detailTabs.includes(activeTab.value)) {
        activeTab.value = 'networkPane';
    } else if (mode === 'global' && !globalTabs.includes(activeTab.value)) {
        activeTab.value = 'changesPane';
    }
}

function switchTab(tab, mode) {
    activeTab.value = tab;
    activeMode.value = mode;
}

onMounted(() => {
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = '/css/ownership-intelligence.css';
    link.id = 'ownership-css';
    document.head.appendChild(link);

    // Load real data
    loadOwnershipData();
});

onUnmounted(() => {
    const link = document.getElementById('ownership-css');
    if (link) document.head.removeChild(link);
});
</script>

<template>
  <Head>
    <title>Avenir Ownership Intelligence</title>
    <meta name="description" content="Ownership Intelligence - Avenir Research Market Intelligence." />
    <meta property="og:title" content="Avenir Ownership Intelligence" />
    <meta property="og:description" content="Ownership Intelligence - Avenir Research Market Intelligence." />
    <meta property="og:type" content="website" />
    <meta name="twitter:card" content="summary_large_image" />
  </Head>

  <AppLayout>
    <div v-if="!dataLoaded" style="display:flex; justify-content:center; align-items:center; height:100vh;">
      <h2 style="color:white;">Loading data...</h2>
    </div>
    <div v-else class="app">
      <Sidebar />
      <main class="main">
        <HeaderPanel />

        <!-- Mode Toggle -->
        <section class="modeToggle">
          <button class="modeBtn" :class="{ on: activeMode === 'detail' }" @click="switchMode('detail')">🔍 Detail Emiten</button>
          <button class="modeBtn" :class="{ on: activeMode === 'global' }" @click="switchMode('global')">🌐 Analisis Global</button>
        </section>

        <SelectorPanel v-if="activeMode === 'detail'" />
        <KpiGrid v-if="activeMode === 'detail'" />

        <section class="panel">
          <!-- Tabbar -->
          <div class="tabbar">
            <!-- Detail mode tabs -->
            <template v-if="activeMode === 'detail'">
              <button :class="{ on: activeTab === 'networkPane' }" @click="switchTab('networkPane', 'detail')">Jaringan Emiten</button>
              <button :class="{ on: activeTab === 'auditPane' }" @click="switchTab('auditPane', 'detail')">Audit &amp; Detail</button>
            </template>
            <!-- Global mode tabs -->
            <template v-else>
              <button :class="{ on: activeTab === 'changesPane' }" @click="switchTab('changesPane', 'global')">Change Monitor</button>
              <button :class="{ on: activeTab === 'groupPane' }" @click="switchTab('groupPane', 'global')">Groups</button>
              <button :class="{ on: activeTab === 'govPane' }" @click="switchTab('govPane', 'global')">Government Layer</button>
              <button :class="{ on: activeTab === 'instPane' }" @click="switchTab('instPane', 'global')">Institutional</button>
              <button :class="{ on: activeTab === 'shadowPane' }" @click="switchTab('shadowPane', 'global')">Shadow Network</button>
              <button :class="{ on: activeTab === 'insiderPane' }" @click="switchTab('insiderPane', 'global')">Pergerakan 5%</button>
              <button :class="{ on: activeTab === 'komposisiPane' }" @click="switchTab('komposisiPane', 'global')">Komposisi 1%</button>
              <button :class="{ on: activeTab === 'klasifikasiPane' }" @click="switchTab('klasifikasiPane', 'global')">Klasifikasi</button>
              <button :class="{ on: activeTab === 'tipePane' }" @click="switchTab('tipePane', 'global')">Domestik/Asing</button>
              <button :class="{ on: activeTab === 'proxyPane' }" @click="switchTab('proxyPane', 'global')">Proxy Explorer</button>
              <button :class="{ on: activeTab === 'methodPane' }" @click="switchTab('methodPane', 'global')">Methodology</button>
            </template>
          </div>

          <!-- Detail panes -->
          <div class="tabpane" :class="{ on: activeTab === 'networkPane' }">
            <NetworkPane v-if="activeTab === 'networkPane'" />
          </div>
          <div class="tabpane" :class="{ on: activeTab === 'auditPane' }">
            <IssuerAuditPane v-if="activeTab === 'auditPane'" />
          </div>

          <!-- Global panes -->
          <div class="tabpane" :class="{ on: activeTab === 'changesPane' }">
            <ChangeMonitorPane v-if="activeTab === 'changesPane'" />
          </div>
          <div class="tabpane" :class="{ on: activeTab === 'groupPane' }">
            <GroupPane v-if="activeTab === 'groupPane'" />
          </div>
          <div class="tabpane" :class="{ on: activeTab === 'govPane' }">
            <GovPane v-if="activeTab === 'govPane'" />
          </div>
          <div class="tabpane" :class="{ on: activeTab === 'instPane' }">
            <InstitutionalPane v-if="activeTab === 'instPane'" />
          </div>
          <div class="tabpane" :class="{ on: activeTab === 'shadowPane' }">
            <ShadowPane v-if="activeTab === 'shadowPane'" />
          </div>
          <div class="tabpane" :class="{ on: activeTab === 'insiderPane' }">
            <InsiderPane v-if="activeTab === 'insiderPane'" />
          </div>
          <div class="tabpane" :class="{ on: activeTab === 'komposisiPane' }">
            <KomposisiPane v-if="activeTab === 'komposisiPane'" />
          </div>
          <div class="tabpane" :class="{ on: activeTab === 'klasifikasiPane' }">
            <KlasifikasiPane v-if="activeTab === 'klasifikasiPane'" />
          </div>
          <div class="tabpane" :class="{ on: activeTab === 'tipePane' }">
            <TipePane v-if="activeTab === 'tipePane'" />
          </div>
          <div class="tabpane" :class="{ on: activeTab === 'proxyPane' }">
            <ProxyExplorerPane v-if="activeTab === 'proxyPane'" />
          </div>
          <div class="tabpane" :class="{ on: activeTab === 'methodPane' }">
            <MethodologyPane v-if="activeTab === 'methodPane'" />
          </div>
         
        </section>

        <div class="footerNote">Source: KSEI/BEI ownership disclosure data. Avenir calculations are analytical proxies and should be verified against issuer filings and official free-float data before investment use.</div>
      </main>
    </div>
  </AppLayout>
</template>
