<script setup>
import { computed, ref, watch } from 'vue';
import { 
    entityArr, selectedKey, ent, minPct, depth, directionMode, setSelected, shortLabel 
} from '../../../Composables/useOwnershipLogic';

const searchQuery = ref('');
const displayValue = computed(() => {
    const e = ent(selectedKey.value);
    return e.key ? ((e.ticker ? e.ticker + ' - ' : '') + e.label) : '-';
});

const filteredOptions = computed(() => {
    if (!searchQuery.value) return [];
    const q = searchQuery.value.toUpperCase();
    return entityArr.value.filter(e => {
        return (e.ticker && e.ticker.includes(q)) || 
               (e.label && e.label.toUpperCase().includes(q)) ||
               (e.norm && e.norm.includes(q.replace(/[^A-Z0-9]+/g, ' ')));
    }).slice(0, 50); // limit to 50 for performance
});

function handleSearch(item) {
    if (item && item.key) {
        setSelected(item.key);
        searchQuery.value = ''; // clear after selection
    }
}

// Fixed quick chips
const chips = ['E:BREN','E:BRPT','I:PRAJOGO PANGESTU','E:TPIA','E:CDIA','E:ADRO','I:LO KHENG HONG','E:BBRI'];
</script>

<template>
  <section class="selectorPanel">
    <div class="selectorGrid">
      <div class="field">
        <label>Selected entity</label>
        <div style="position: relative;">
            <input 
                v-model="searchQuery"
                :placeholder="displayValue"
                @keyup.enter="handleSearch(filteredOptions[0])"
            />
            <div v-if="searchQuery && filteredOptions.length > 0" class="autocomplete-dropdown" style="position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #ddd; z-index: 100; max-height: 200px; overflow-y: auto;">
                <div 
                    v-for="opt in filteredOptions" 
                    :key="opt.key"
                    @click="handleSearch(opt)"
                    style="padding: 8px; cursor: pointer; border-bottom: 1px solid #eee;"
                >
                    {{ opt.ticker ? opt.ticker + ' - ' : '' }}{{ opt.label }}
                </div>
            </div>
        </div>
      </div>
      <div class="field">
        <label>Network depth</label>
        <select v-model="depth">
            <option :value="1">1-hop</option>
            <option :value="2">2-hop</option>
            <option :value="3">3-hop</option>
            <option :value="4">4-hop</option>
        </select>
      </div>
      <div class="field">
        <label>Minimum stake</label>
        <select v-model="minPct">
            <option :value="0">All</option>
            <option :value="1">>= 1%</option>
            <option :value="3">>= 3%</option>
            <option :value="5">>= 5%</option>
            <option :value="10">>= 10%</option>
        </select>
      </div>
      <div class="field">
        <label>Direction</label>
        <select v-model="directionMode">
            <option value="both">Owners + holdings</option>
            <option value="up">Owners only</option>
            <option value="down">Holdings only</option>
        </select>
      </div>
    </div>
    <div class="chips" id="quickChips">
        <template v-for="key in chips" :key="key">
            <span 
                v-if="ent(key).key !== key || ent(key).kind !== 'unknown'"
                class="chip" 
                :class="{ on: selectedKey === key }"
                @click="setSelected(key)"
            >
                {{ ent(key).ticker || shortLabel(ent(key).label, 18) }}
            </span>
        </template>
    </div>
  </section>
</template>

<style scoped>
.autocomplete-dropdown div:hover {
    background-color: #f5f5f5;
}
</style>
