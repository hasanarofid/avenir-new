<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

const date = ref(new Date().toISOString().split('T')[0]);
const loading = ref(false);
const result = ref(null);
const error = ref(null);

const runSimulation = async () => {
    loading.value = true;
    error.value = null;
    result.value = null;

    try {
        const response = await axios.post(route('admin.desk-brief.tester.run'), {
            date: date.value
        });
        result.value = response.data;
    } catch (err) {
        error.value = err.response?.data?.message || err.message;
    } finally {
        loading.value = false;
    }
};

const formatNumber = (num) => {
    if (num === null || num === undefined) return '-';
    return new Intl.NumberFormat('id-ID').format(num);
};

const formatPercent = (num) => {
    if (num === null || num === undefined) return '-';
    return (num * 100).toFixed(2) + '%';
};
</script>

<template>
    <div class="bg-[#1A1A1A] rounded-xl border border-gray-800 p-6 font-sans text-gray-300">
        <div class="mb-6 pb-6 border-b border-gray-800 flex flex-col md:flex-row md:items-end gap-4">
            <div class="flex-1">
                <h3 class="text-xl font-bold text-white mb-2">Simulasi Kalkulasi Market Regime</h3>
                <p class="text-sm text-gray-400">Pilih tanggal untuk menarik raw data dari Sectors API dan menjalankan perhitungan skor untuk memverifikasi logika cronjob.</p>
            </div>
            <div class="flex items-end gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Tanggal Analisis</label>
                    <input 
                        type="date" 
                        v-model="date" 
                        class="bg-[#0b0c10] border border-gray-700 text-white rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500"
                    />
                </div>
                <button 
                    @click="runSimulation" 
                    :disabled="loading"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition-colors flex items-center gap-2 disabled:opacity-50"
                >
                    <span v-if="loading">Menghitung...</span>
                    <span v-else>Simulasikan</span>
                </button>
            </div>
        </div>

        <div v-if="error" class="bg-red-500/10 border border-red-500/20 text-red-400 p-4 rounded-lg mb-6">
            {{ error }}
        </div>

        <div v-if="result" class="space-y-6">
            <!-- Data Date Warning -->
            <div v-if="result.latest_available_date !== result.date" class="bg-yellow-500/10 border border-yellow-500/20 text-yellow-400 p-4 rounded-lg flex items-start gap-3">
                <svg class="w-5 h-5 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div>
                    <h4 class="font-bold">Perhatian: Data {{ result.date }} Belum Lengkap/Tersedia</h4>
                    <p class="text-sm mt-1">Data Sectors API untuk tanggal yang Anda pilih belum keluar. Simulator saat ini menggunakan data mundur yang terakhir kali tersedia (<strong>{{ result.latest_available_date || 'Tidak ada' }}</strong>).</p>
                </div>
            </div>

            <!-- Header Score -->
            <div class="flex flex-col md:flex-row gap-4 items-center justify-between bg-[#222] p-4 rounded-xl border border-gray-800">
                <div>
                    <div class="text-sm text-gray-400 uppercase tracking-wider font-bold mb-1">Final Regime Score</div>
                    <div class="text-3xl font-bold text-blue-400">{{ result.final_score }}</div>
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-400 uppercase tracking-wider font-bold mb-1">Market Stance</div>
                    <div class="px-4 py-1.5 bg-emerald-500/10 text-emerald-400 font-bold rounded-lg border border-emerald-500/20">
                        {{ result.regime.replace(/_/g, ' ') }}
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4">
                <!-- 1. Momentum -->
                <div class="bg-[#161616] border border-gray-800 rounded-lg p-5">
                    <div class="flex justify-between items-start mb-4">
                        <h4 class="text-lg font-bold text-white flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-blue-500/20 text-blue-400 flex items-center justify-center text-xs">1</span>
                            Price Trend (Momentum)
                        </h4>
                        <div class="text-right">
                            <div class="text-2xl font-bold text-emerald-400">{{ result.scores.price_trend }}</div>
                            <div class="text-xs text-gray-500">Bobot 30% = {{ (result.scores.price_trend * 0.3).toFixed(2) }}</div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm bg-black/20 p-4 rounded-lg">
                        <div><div class="text-gray-500 text-xs">Close</div><div class="font-bold text-white">{{ formatNumber(result.raw_data.close) }}</div></div>
                        <div><div class="text-gray-500 text-xs">MA20 / MA60</div><div class="font-bold text-white">{{ formatNumber(result.raw_data.ma20) }} / {{ formatNumber(result.raw_data.ma60) }}</div></div>
                        <div><div class="text-gray-500 text-xs">Ret 5d / 20d</div><div class="font-bold text-white">{{ formatPercent(result.raw_data.ret_5d) }} / {{ formatPercent(result.raw_data.ret_20d) }}</div></div>
                        <div><div class="text-gray-500 text-xs">Drawdown 20d</div><div class="font-bold text-white">{{ formatPercent(result.raw_data.drawdown_20d) }}</div></div>
                    </div>
                    <div class="mt-3 text-xs text-gray-400">
                        <span class="font-bold">Rumus:</span> Interpolasi piecewise linier berdasarkan spesifikasi resmi (close_vs_ma20, ma20_vs_ma60, ret_5d, ret_20d, drawdown_20d).
                    </div>
                </div>

                <!-- 2. Breadth -->
                <div class="bg-[#161616] border border-gray-800 rounded-lg p-5">
                    <div class="flex justify-between items-start mb-4">
                        <h4 class="text-lg font-bold text-white flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-blue-500/20 text-blue-400 flex items-center justify-center text-xs">2</span>
                            Market Breadth
                        </h4>
                        <div class="text-right">
                            <div class="text-2xl font-bold text-emerald-400">{{ result.scores.breadth }}</div>
                            <div class="text-xs text-gray-500">Bobot 25% = {{ (result.scores.breadth * 0.25).toFixed(2) }}</div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 text-sm bg-black/20 p-4 rounded-lg">
                        <div><div class="text-gray-500 text-xs">Saham Naik (Advancers)</div><div class="font-bold text-green-400">{{ formatNumber(result.raw_data.advancers) }}</div></div>
                        <div><div class="text-gray-500 text-xs">Saham Turun (Decliners)</div><div class="font-bold text-red-400">{{ formatNumber(result.raw_data.decliners) }}</div></div>
                    </div>
                    <div class="mt-3 text-xs text-gray-400">
                        <span class="font-bold">Rumus:</span> Interpolasi piecewise berdasarkan spesifikasi resmi (ad_score, strong_movers, mcap_breadth, value_breadth, active_participation).
                    </div>
                </div>

                <!-- 3. Flow -->
                <div class="bg-[#161616] border border-gray-800 rounded-lg p-5">
                    <div class="flex justify-between items-start mb-4">
                        <h4 class="text-lg font-bold text-white flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-blue-500/20 text-blue-400 flex items-center justify-center text-xs">3</span>
                            Flow (Foreign & Inst)
                        </h4>
                        <div class="text-right">
                            <div class="text-2xl font-bold text-emerald-400">{{ result.scores.flow }}</div>
                            <div class="text-xs text-gray-500">Bobot 20% = {{ (result.scores.flow * 0.2).toFixed(2) }}</div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm bg-black/20 p-4 rounded-lg">
                        <div><div class="text-gray-500 text-xs">Foreign Net 5D</div><div class="font-bold text-white">{{ formatNumber(result.raw_data.foreign_net_5d) }}</div></div>
                        <div><div class="text-gray-500 text-xs">Inst Net 5D</div><div class="font-bold text-white">{{ formatNumber(result.raw_data.institutional_net_5d) }}</div></div>
                        <div><div class="text-gray-500 text-xs">Positive Days</div><div class="font-bold text-white">{{ result.raw_data.positive_flow_days_5d }} Hari</div></div>
                        <div><div class="text-gray-500 text-xs">Market Gross</div><div class="font-bold text-white">{{ formatNumber(result.raw_data.total_market_value_5d) }}</div></div>
                    </div>
                    <div class="mt-3 text-xs text-gray-400">
                        <span class="font-bold">Rumus:</span> Interpolasi piecewise berdasarkan spesifikasi resmi (foreign_flow_trend, flow_consistency, liquidity_confirmation).
                    </div>
                </div>

                <!-- 4. Sector Rotation -->
                <div class="bg-[#161616] border border-gray-800 rounded-lg p-5">
                    <div class="flex justify-between items-start mb-4">
                        <h4 class="text-lg font-bold text-white flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-blue-500/20 text-blue-400 flex items-center justify-center text-xs">4</span>
                            Sector Rotation
                        </h4>
                        <div class="text-right">
                            <div class="text-2xl font-bold text-emerald-400">{{ result.scores.sector_rotation }}</div>
                            <div class="text-xs text-gray-500">Bobot 15% = {{ (result.scores.sector_rotation * 0.15).toFixed(2) }}</div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm bg-black/20 p-4 rounded-lg">
                        <div><div class="text-gray-500 text-xs">Positive Sectors</div><div class="font-bold text-white">{{ result.raw_data.positive_sectors }} / {{ result.raw_data.total_sectors }}</div></div>
                        <div><div class="text-gray-500 text-xs">Cyclical Ratio</div><div class="font-bold text-white">{{ formatPercent(result.raw_data.cyclical_positive_ratio) }}</div></div>
                        <div><div class="text-gray-500 text-xs">Concentration</div><div class="font-bold text-white">{{ formatPercent(result.raw_data.leadership_concentration) }}</div></div>
                        <div><div class="text-gray-500 text-xs">Consistency</div><div class="font-bold text-white">{{ result.raw_data.leadership_consistency_days }} Hari</div></div>
                    </div>
                    <div class="mt-3 text-xs text-gray-400">
                        <span class="font-bold">Rumus:</span> Interpolasi piecewise berdasarkan spesifikasi resmi (sector_return, risk_on_part, sector_val_conf, sector_breadth, leadership_hhi, risk_on_vs_def).
                    </div>
                </div>

                <!-- 5. Volatility -->
                <div class="bg-[#161616] border border-gray-800 rounded-lg p-5">
                    <div class="flex justify-between items-start mb-4">
                        <h4 class="text-lg font-bold text-white flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-blue-500/20 text-blue-400 flex items-center justify-center text-xs">5</span>
                            Volatility & Liquidity
                        </h4>
                        <div class="text-right">
                            <div class="text-2xl font-bold text-emerald-400">{{ result.scores.volatility_stability }}</div>
                            <div class="text-xs text-gray-500">Bobot 10% = {{ ((result.scores.volatility_stability || 0) * 0.1).toFixed(2) }}</div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm bg-black/20 p-4 rounded-lg">
                        <div><div class="text-gray-500 text-xs">Vol Percentile</div><div class="font-bold text-white">{{ formatPercent(result.raw_data.volatility_percentile) }}</div></div>
                        <div><div class="text-gray-500 text-xs">Value Traded</div><div class="font-bold text-white">{{ formatNumber(result.raw_data.value_traded) }}</div></div>
                        <div><div class="text-gray-500 text-xs">Avg Value 20d</div><div class="font-bold text-white">{{ formatNumber(result.raw_data.avg_value_20d) }}</div></div>
                        <div><div class="text-gray-500 text-xs">IHSG 1D Ret</div><div class="font-bold text-white">{{ formatPercent(result.raw_data.ihsg_return_1d) }}</div></div>
                    </div>
                    <div class="mt-3 text-xs text-gray-400">
                        <span class="font-bold">Rumus:</span> Interpolasi piecewise berdasarkan spesifikasi resmi (volatility_regime, liquidity_quality, intraday_range, return_shock, close_location).
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>
