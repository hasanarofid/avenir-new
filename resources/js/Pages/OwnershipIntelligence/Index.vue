<script setup>
import { onMounted, onUnmounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

import { loadOwnershipData, dataLoaded, activeTab } from '../../Composables/useOwnershipLogic';

import Sidebar from './Components/Sidebar.vue';
import HeaderPanel from './Components/HeaderPanel.vue';
import SelectorPanel from './Components/SelectorPanel.vue';
import KpiGrid from './Components/KpiGrid.vue';
import NetworkPane from './Components/NetworkPane.vue';
import ChangeMonitorPane from './Components/ChangeMonitorPane.vue';
import IssuerAuditPane from './Components/IssuerAuditPane.vue';
import ProxyExplorerPane from './Components/ProxyExplorerPane.vue';
import MethodologyPane from './Components/MethodologyPane.vue';

onMounted(() => {
    
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = '/css/ownership-intelligence.css';
    link.id = 'ownership-css';
    document.head.appendChild(link);
    
    // Load data
    loadOwnershipData();
});

onUnmounted(() => {
    const link = document.getElementById('ownership-css');
    if (link) document.head.removeChild(link);
});</script>

<template>
  <Head>
        <title>Ownership Intelligence | Avenir</title>
        <meta name="description" content="Ownership Intelligence - Avenir Research Market Intelligence." />
        <meta property="og:title" content="Ownership Intelligence | Avenir" />
        <meta property="og:description" content="Ownership Intelligence - Avenir Research Market Intelligence." />
        <meta property="og:type" content="website" />
        <meta name="twitter:card" content="summary_large_image" />
        
        <!-- GEO Tags -->
        <meta name="geo.region" content="ID" />
        <meta name="geo.placename" content="Indonesia" />
        <meta name="geo.position" content="-0.789275;113.921327" />
        <meta name="ICBM" content="-0.789275, 113.921327" />
        <meta name="language" content="id-ID" />
        <meta name="view-transition" content="same-origin" />
  </Head>
  
  <AppLayout>
    <div v-if="!dataLoaded" style="display:flex; justify-content:center; align-items:center; height:100vh;">
        <h2 style="color:white;">Loading data...</h2>
    </div>
    <div v-else class="app" style="color:#ffffff; background:#0b0c10; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">
      <Sidebar />
      <main class="main">
        <HeaderPanel />
        <SelectorPanel />
        <KpiGrid />
        
        <section class="panel">
          <div class="tabpane" :class="{ on: activeTab === 'networkPane' }">
            <NetworkPane v-if="activeTab === 'networkPane'" />
          </div>
          
          <div class="tabpane" :class="{ on: activeTab === 'changesPane' }">
            <ChangeMonitorPane v-if="activeTab === 'changesPane'" />
          </div>
          
          <div class="tabpane" :class="{ on: activeTab === 'auditPane' }">
            <IssuerAuditPane v-if="activeTab === 'auditPane'" />
          </div>
          
          <div class="tabpane" :class="{ on: activeTab === 'proxyPane' }">
            <ProxyExplorerPane v-if="activeTab === 'proxyPane'" />
          </div>
          
          <div class="tabpane" :class="{ on: activeTab === 'methodPane' }">
            <MethodologyPane v-if="activeTab === 'methodPane'" />
          </div>
        </section>
        
        <div class="footerNote">Source: KSEI/BEI ownership disclosure PDFs uploaded by user. Avenir calculations are proxies for analysis and should be verified against issuer filings and official free-float data before investment use.</div>
      </main>
    </div>
  </AppLayout>
</template>

