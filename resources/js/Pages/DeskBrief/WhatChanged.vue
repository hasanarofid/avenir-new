<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  delta: Object,
  todayStance: Object,
  yesterdayStance: Object,
});

</script>

<template>
  <Head title="What Changed | Desk Brief" />

  <AppLayout>
    <div class="bg-[#090b0a] min-h-screen text-gray-200 pb-16 font-sans pt-5">
      <div class="w-full max-w-[1024px] mx-auto px-4 lg:px-6">
        
        <div class="mb-6 flex items-center gap-4">
          <Link href="/desk-brief" class="text-gray-500 hover:text-white transition-colors">← Back to Desk Brief</Link>
          <h1 class="text-2xl font-bold text-white">What Changed (Delta Engine)</h1>
        </div>

        <div class="bg-[#131714] border border-[#252b27] rounded-xl p-6 mb-6">
          <h2 class="text-sm font-semibold text-gray-400 tracking-widest uppercase mb-6">Market Stance Delta</h2>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Yesterday -->
            <div class="p-4 border border-[#1e2420] rounded-lg bg-[#0c0f0d]">
              <p class="text-xs text-gray-500 mb-2">Yesterday ({{ yesterdayStance?.date ?? 'N/A' }})</p>
              <div class="text-3xl font-bold mb-2">{{ yesterdayStance?.score ?? '-' }} / 100</div>
              <div class="text-sm font-medium text-gray-300">{{ yesterdayStance?.label ?? 'N/A' }}</div>
            </div>

            <!-- Today -->
            <div class="p-4 border border-[#1e2420] rounded-lg bg-[#0c0f0d] relative overflow-hidden">
              <div v-if="delta.regime_trend === 'warming'" class="absolute top-0 right-0 w-16 h-16 bg-green-500/10 rounded-bl-full flex items-start justify-end p-3 text-green-500">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"/></svg>
              </div>
              <div v-if="delta.regime_trend === 'cooling'" class="absolute top-0 right-0 w-16 h-16 bg-red-500/10 rounded-bl-full flex items-start justify-end p-3 text-red-500">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
              </div>

              <p class="text-xs text-gray-500 mb-2">Today ({{ todayStance?.date ?? 'N/A' }})</p>
              <div class="text-3xl font-bold mb-2 flex items-baseline gap-2">
                {{ todayStance?.score ?? '-' }} / 100
                <span class="text-xs font-normal" :class="delta.regime > 0 ? 'text-green-400' : 'text-red-400'">
                  ({{ delta.regime > 0 ? '+' : '' }}{{ delta.regime }})
                </span>
              </div>
              <div class="text-sm font-medium" :class="delta.stance_changed ? 'text-yellow-400' : 'text-gray-300'">
                {{ todayStance?.label ?? 'N/A' }} 
                <span v-if="delta.stance_changed" class="text-xs border border-yellow-500/30 bg-yellow-500/10 px-2 py-0.5 rounded ml-2">Flipped</span>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-[#131714] border border-[#252b27] rounded-xl p-6">
           <h2 class="text-sm font-semibold text-gray-400 tracking-widest uppercase mb-4">Other Changes</h2>
           <p class="text-sm text-gray-500">
             Additional delta logic (Sector Confluence, Risk Flags, Stealth Accumulation) will be populated here based on the daily snapshots.
           </p>
        </div>

      </div>
    </div>
  </AppLayout>
</template>
