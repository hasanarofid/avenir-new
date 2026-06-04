<script setup>
import { ref, computed } from 'vue';
import { Search, Edit2, Trash2 } from '@lucide/vue';

const props = defineProps({
  items: {
    type: Array,
    required: true
  },
  headers: {
    type: Array,
    required: true // e.g. [ { text: 'Title', value: 'title' }, { text: 'Status', value: 'status', type: 'badge' } ]
  },
  searchPlaceholder: {
    type: String,
    default: 'Cari data...'
  },
  searchKey: {
    type: String,
    default: 'title'
  },
  showActions: {
    type: Boolean,
    default: true
  }
});

const emit = defineEmits(['edit', 'delete']);

const search = ref('');

const filteredItems = computed(() => {
  if (!search.value) return props.items;
  const query = search.value.toLowerCase().trim();
  return props.items.filter(item => {
    const value = item[props.searchKey];
    if (value === null || value === undefined) return false;
    return String(value).toLowerCase().includes(query);
  });
});
</script>

<template>
  <div class="bg-[#121614] border border-emerald-950/30 rounded-2xl overflow-hidden shadow-xl shadow-slate-950/20">
    <!-- Header Controls -->
    <div class="p-6 border-b border-emerald-950/30 flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div class="relative max-w-md w-full">
        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
          <Search class="w-5 h-5" />
        </span>
        <input 
          v-model="search"
          type="text" 
          :placeholder="searchPlaceholder"
          class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl pl-10 pr-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors"
        />
      </div>
      <div class="flex items-center gap-2">
        <slot name="actions-header" />
      </div>
    </div>

    <!-- Table Container -->
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="border-b border-emerald-950/30 bg-[#090b0a]/40">
            <th 
              v-for="header in headers" 
              :key="header.value" 
              class="px-6 py-4 text-xs font-bold tracking-wider text-slate-400 uppercase"
            >
              {{ header.text }}
            </th>
            <th v-if="showActions" class="px-6 py-4 text-xs font-bold tracking-wider text-slate-400 uppercase text-right">
              Aksi
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-emerald-950/20">
          <tr 
            v-for="(item, index) in filteredItems" 
            :key="item.id || index"
            class="hover:bg-[#090b0a]/30 transition-colors"
          >
            <td 
              v-for="header in headers" 
              :key="header.value" 
              class="px-6 py-4.5 text-sm text-slate-300"
            >
              <!-- Slot override for custom formatting -->
              <slot :name="`cell(${header.value})`" :item="item">
                <!-- Badge Type -->
                <span 
                  v-if="header.type === 'badge'"
                  :class="[
                    item[header.value] === 'published' || item[header.value] === true || item[header.value] === 'active' || item[header.value] === 1
                      ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20'
                      : 'bg-slate-800 text-slate-400 border border-slate-700',
                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold'
                  ]"
                >
                  {{ item[header.value] === true || item[header.value] === 'active' || item[header.value] === 1 ? 'Aktif' : (item[header.value] === 'published' ? 'Published' : (item[header.value] === 'draft' ? 'Draft' : 'Tidak Aktif')) }}
                </span>
                
                <!-- Date Type -->
                <span v-else-if="header.type === 'date'">
                  {{ new Date(item[header.value]).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) }}
                </span>

                <!-- Text fallback -->
                <span v-else class="line-clamp-1">
                  {{ item[header.value] }}
                </span>
              </slot>
            </td>
            
            <!-- Actions -->
            <td v-if="showActions" class="px-6 py-4.5 text-sm text-right whitespace-nowrap">
              <div class="flex items-center justify-end gap-2">
                <button 
                  @click="emit('edit', item)" 
                  class="p-2 text-emerald-400 hover:bg-emerald-600/10 rounded-lg transition-colors"
                  title="Edit"
                >
                  <Edit2 class="w-4 h-4" />
                </button>
                <button 
                  @click="emit('delete', item)" 
                  class="p-2 text-rose-400 hover:bg-rose-600/10 rounded-lg transition-colors"
                  title="Hapus"
                >
                  <Trash2 class="w-4 h-4" />
                </button>
              </div>
            </td>
          </tr>
          
          <!-- Empty State -->
          <tr v-if="filteredItems.length === 0">
            <td :colspan="headers.length + (showActions ? 1 : 0)" class="px-6 py-12 text-center text-slate-500 text-sm">
              Tidak ada data yang ditemukan.
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
