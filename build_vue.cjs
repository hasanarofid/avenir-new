const fs = require('fs');
const htmlRaw = fs.readFileSync('prd-testing/new_concept/Avenir-OwnershipIntelligence-logic.html', 'utf8');

const styleMatch = htmlRaw.match(/<style>(.*?)<\/style>/s);
const style = styleMatch ? styleMatch[1] : '';

const bodyMatch = htmlRaw.match(/<div class="app">(.*?)<\/div>\n<script>/s);
const body = bodyMatch ? `<div class="app">\n${bodyMatch[1]}\n</div>` : '';

const scriptMatch = htmlRaw.match(/<script>(.*?)<\/script>/s);
let script = scriptMatch ? scriptMatch[1] : '';

// Remove the `const DATA = {}; // Replaced` line
script = script.replace('const DATA = {}; // Replaced\n', '');
// Replace `const DATA = ...` initialization if any
script = script.replace(/const entities = DATA\.entities.*?affiliations = DATA\.affiliations \|\| \{\};/s, '');
script = script.replace('const entityArr = Object.values(entities);', '');

const vueContent = `
<template>
  <Head title="Ownership Intelligence" />
  <div v-if="loading" class="min-h-screen bg-[#070b0a] flex items-center justify-center text-emerald-500 font-bold text-xl">
     Memuat Data Ownership Graph...
  </div>
  <div v-else-if="!dataUrl" class="min-h-screen bg-[#070b0a] flex items-center justify-center text-gray-400 font-bold text-xl">
     Belum ada data Ownership Snapshot yang diunggah.
  </div>
  ${body}
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import * as d3 from 'd3';

const props = defineProps({
    dataUrl: String
});

const loading = ref(true);

onMounted(async () => {
    if (!props.dataUrl) {
        loading.value = false;
        return;
    }
    
    try {
        const response = await fetch(props.dataUrl);
        const DATA = await response.json();
        
        loading.value = false;
        
        // Wait for DOM to update after v-if changes
        setTimeout(() => {
            initVanillaLogic(DATA);
        }, 100);
        
    } catch (e) {
        console.error(e);
        loading.value = false;
    }
});

function initVanillaLogic(DATA) {
    const entities = DATA.entities || {};
    const edges = DATA.edges || [];
    const changes = DATA.changes || [];
    const audits = DATA.audits || {};
    const investorSummaries = DATA.investorSummaries || {};
    const stats = DATA.stats || {};
    const govHoldings = DATA.govHoldings || [];
    const govByIssuer = DATA.govByIssuer || {};
    const groups = DATA.groups || {};
    const entityGroup = DATA.entityGroup || {};
    const institutions = DATA.institutions || {top:[],flow:{buy:[],sell:[]},classes:[]};
    const shadow = DATA.shadow || {sharedHolders:[],connectorCount:0};
    const affiliations = DATA.affiliations || {};
    const entityArr = Object.values(entities);

    ${script}
    
    initSearch();
    initControls();
    renderKpis();
    switchTab('networkPane');
}
</script>

<style>
${style}

/* Make sure modal doesn't conflict with layout */
.app {
    min-height: 100vh;
}
</style>
`;

fs.writeFileSync('resources/js/Pages/OwnershipIntelligence/Index.vue', vueContent);
