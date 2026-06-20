<script setup>
import { ref, computed, watch } from 'vue';
import { Search, Edit2, Trash2 } from '@lucide/vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  items: {
    type: Array,
    required: true
  },
  headers: {
    type: Array,
    required: true
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
  },
  pagination: {
    type: Array,
    default: null
  },
  serverSearch: {
    type: Boolean,
    default: false
  },
  initialSearch: {
    type: String,
    default: ''
  },
  selectable: {
    type: Boolean,
    default: false
  },
  selectedItems: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['edit', 'delete', 'search', 'update:selectedItems']);

const search = ref(props.initialSearch);

const toggleAll = (event) => {
  if (event.target.checked) {
    emit('update:selectedItems', filteredItems.value.map(i => i.id));
  } else {
    emit('update:selectedItems', []);
  }
};

const toggleItem = (item) => {
  const newSelected = [...(props.selectedItems || [])];
  const index = newSelected.indexOf(item.id);
  if (index > -1) {
    newSelected.splice(index, 1);
  } else {
    newSelected.push(item.id);
  }
  emit('update:selectedItems', newSelected);
};

const allSelected = computed(() => {
  if (!filteredItems.value || filteredItems.value.length === 0) return false;
  return filteredItems.value.every(item => (props.selectedItems || []).includes(item.id));
});

const someSelected = computed(() => {
  if (!filteredItems.value || filteredItems.value.length === 0) return false;
  return filteredItems.value.some(item => (props.selectedItems || []).includes(item.id)) && !allSelected.value;
});

watch(search, (newVal) => {
  if (props.serverSearch) {
    emit('search', newVal);
  }
});

const filteredItems = computed(() => {
  if (props.serverSearch || !search.value) return props.items;
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
            <th v-if="selectable" class="px-6 py-4 w-12 text-center">
              <input 
                type="checkbox" 
                :checked="allSelected"
                :indeterminate="someSelected"
                @change="toggleAll"
                class="w-4 h-4 rounded border-emerald-950/40 bg-[#090b0a] text-emerald-500 focus:ring-emerald-500/20 focus:ring-offset-[#121614] cursor-pointer"
              />
            </th>
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
            <td v-if="selectable" class="px-6 py-4 w-12 text-center">
              <input 
                type="checkbox" 
                :checked="(selectedItems || []).includes(item.id)"
                @change="toggleItem(item)"
                class="w-4 h-4 rounded border-emerald-950/40 bg-[#090b0a] text-emerald-500 focus:ring-emerald-500/20 focus:ring-offset-[#121614] cursor-pointer"
              />
            </td>
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
              <slot name="actions" :item="item">
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
              </slot>
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

    <!-- Pagination -->
    <div v-if="pagination && pagination.length > 3" class="p-6 border-t border-emerald-950/30 flex items-center justify-end gap-1 flex-wrap">
      <template v-for="(link, p) in pagination" :key="p">
        <div 
          v-if="link.url === null" 
          class="px-3 py-1.5 text-sm text-slate-500 bg-[#090b0a]/40 border border-emerald-950/30 rounded-lg" 
          v-html="link.label" 
        />
        <Link 
          v-else 
          :href="link.url" 
          class="px-3 py-1.5 text-sm border rounded-lg transition-colors" 
          :class="[
            link.active 
              ? 'bg-emerald-600/20 text-emerald-400 border-emerald-500/30' 
              : 'bg-[#090b0a] text-slate-300 border-emerald-950/40 hover:bg-emerald-900/20 hover:text-emerald-400'
          ]" 
          v-html="link.label" 
        />
      </template>
    </div>
  </div>
</template>
