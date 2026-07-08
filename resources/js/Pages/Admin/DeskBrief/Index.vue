<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import RegimeSimulator from './Components/RegimeSimulator.vue';
import { Edit, Eye, CheckCircle, Upload, X } from '@lucide/vue';
import Swal from 'sweetalert2';

const props = defineProps({
  deskBriefs: {
    type: Object,
    required: true
  }
});

const showUploadModal = ref(false);
const showUploadCsvModal = ref(false);

const form = useForm({
  pdf_file: null,
});

const csvForm = useForm({
  csv_file: null,
});

const handleFileChange = (e) => {
  if (e.target.files.length > 0) {
    form.pdf_file = e.target.files[0];
  }
};

const handleCsvFileChange = (e) => {
  if (e.target.files.length > 0) {
    csvForm.csv_file = e.target.files[0];
  }
};

const submitUpload = () => {
  if (!form.pdf_file) {
    Swal.fire('Error', 'Pilih file PDF terlebih dahulu', 'error');
    return;
  }
  form.post(route('admin.desk-brief.upload-pdf'), {
    preserveScroll: true,
    onSuccess: () => {
      showUploadModal.value = false;
      form.reset();
      Swal.fire('Berhasil!', 'PDF berhasil diproses dan Draft dibuat.', 'success');
    },
    onError: (errors) => {
      Swal.fire('Gagal', errors.pdf_file || 'Terjadi kesalahan saat memproses file.', 'error');
    }
  });
};

const submitCsvUpload = () => {
  if (!csvForm.csv_file) {
    Swal.fire('Error', 'Pilih file CSV terlebih dahulu', 'error');
    return;
  }
  csvForm.post(route('admin.desk-brief.upload-ihsg-csv'), {
    preserveScroll: true,
    onSuccess: (page) => {
      showUploadCsvModal.value = false;
      csvForm.reset();
      
      const summary = page.props.flash?.ihsg_trend_summary;
      if (summary) {
        const html = `
          <div class="text-left font-mono text-sm bg-[#111] p-4 rounded-lg border border-gray-800 text-gray-300">
            <div class="text-white font-bold mb-2">AVENIR IHSG PRICE TREND ENGINE</div>
            <div>Latest Date &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: ${summary.date}</div>
            <div>Close &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: ${summary.close}</div>
            <div>MA20 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: ${summary.ma20}</div>
            <div>MA60 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: ${summary.ma60}</div>
            <div>Return 5D &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: ${(summary.ret_5d * 100).toFixed(2)}%</div>
            <div>Return 20D &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: ${(summary.ret_20d * 100).toFixed(2)}%</div>
            <div>Drawdown 20D &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: ${(summary.drawdown_20d * 100).toFixed(2)}%</div>
            <div class="mt-2 pt-2 border-t border-gray-700 text-blue-400 font-bold">Price Trend Score &nbsp;: ${summary.score} / 100</div>
          </div>
        `;
        Swal.fire({
          title: '<span class="text-white">Hasil Analisis Trend IHSG</span>',
          html: html,
          background: '#1A1A1A',
          width: '500px',
          showConfirmButton: true,
          confirmButtonColor: '#3B82F6'
        });
      } else {
        Swal.fire('Berhasil!', 'CSV berhasil diproses.', 'success');
      }
    },
    onError: (errors) => {
      Swal.fire('Gagal', errors.csv_file || 'Terjadi kesalahan saat memproses file CSV.', 'error');
    }
  });
};

const activeTab = ref('data'); // 'data' or 'simulator'

const headers = [
  { text: 'Tanggal', value: 'date', type: 'date' },
  { text: 'Regime Summary', value: 'market_stance_id' },
  { text: 'Status', value: 'status' },
  { text: 'Tgl Publish', value: 'published_at', type: 'datetime' }
];

const handlePreview = (item) => {
  window.open(route('desk-brief.index', { preview_id: item.id }), '_blank');
};

const handleEdit = (item) => {
  router.get(route('admin.desk-brief.edit', item.id));
};

const handlePublish = (item) => {
  Swal.fire({
    title: 'Publish Desk Brief?',
    text: "Brief akan langsung tampil di halaman publik.",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#10B981',
    cancelButtonColor: '#EF4444',
    confirmButtonText: 'Ya, Publish!',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) {
      router.post(route('admin.desk-brief.publish', item.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
          Swal.fire(
            'Berhasil!',
            'Desk Brief telah dipublish.',
            'success'
          );
        }
      });
    }
  });
};

const showBreakdown = (stance) => {
  const momentumRes = stance.momentum_score * 0.3;
  const breadthRes = stance.breadth_score * 0.25;
  const foreignRes = stance.foreign_score * 0.2;
  const sectorRes = stance.sector_score * 0.15;
  const rupiahRes = stance.rupiah_score * 0.1;
  const total = momentumRes + breadthRes + foreignRes + sectorRes + rupiahRes;

  const getDynamicNote = (key, score) => {
    if (key === 'momentum') {
      if (score >= 80) return '(Skor ini naik maksimal mendekati 100 karena penguatan IHSG membuat mayoritas indikator tren teknikal seperti Close > MA20 terpenuhi).';
      if (score <= 30) return '(Skor rendah karena IHSG di bawah MA20 dan MA60, menunjukkan downtrend).';
      return '(Skor moderat, tren harga IHSG berada dalam fase konsolidasi atau transisi).';
    }
    if (key === 'breadth') {
      if (score >= 80) return '(Rasio saham naik jauh mengungguli saham turun, breadth sangat sehat).';
      if (score <= 30) return '(Lebih banyak saham yang turun signifikan, breadth sangat lemah).';
      return '(Perbandingan saham naik dan turun relatif seimbang di pasar).';
    }
    if (key === 'foreign') {
      if (score >= 70) return '(Asing mencatatkan Net Buy yang deras sehingga mendapat poin penuh).';
      if (score <= 30) return '(Sesuai tarikan API live, asing masih mencatatkan Outflow deras sehingga tidak mendapat poin).';
      return '(Aliran dana asing cenderung netral atau mixed).';
    }
    if (key === 'sector') {
      if (score >= 70) return '(Rotasi sektor berjalan mulus dengan partisipasi merata dari berbagai sektor).';
      if (score <= 30) return '(Konsentrasi kepemimpinan pasar sangat sempit atau hanya tertuju pada satu sektor).';
      return '(Partisipasi sektoral cukup, namun belum sepenuhnya merata).';
    }
    if (key === 'rupiah') {
      if (score >= 70) return '(Likuiditas memadai dan volatilitas terjaga).';
      if (score <= 30) return '(Likuiditas mengering atau terjadi lonjakan volatilitas yang tinggi).';
      return '(Kondisi likuiditas pasar dan volatilitas pada batas wajar).';
    }
    return '';
  };

  const html = `
    <div class="text-left text-sm text-gray-300 space-y-4 font-sans leading-relaxed">
      <div class="flex flex-col">
        <div><span class="font-bold text-white">1. Price Trend (Momentum):</span> Skor <span class="text-emerald-400 font-bold">${stance.momentum_score}</span> Bobot 30% &rarr; ${stance.momentum_score} &times; 30% = <span class="text-white font-bold">${momentumRes.toFixed(2)}</span></div>
        <div class="text-[13px] text-gray-400 italic">${getDynamicNote('momentum', stance.momentum_score)}</div>
      </div>
      <div class="flex flex-col">
        <div><span class="font-bold text-white">2. Market Breadth:</span> Skor <span class="text-emerald-400 font-bold">${stance.breadth_score}</span> Bobot 25% &rarr; ${stance.breadth_score} &times; 25% = <span class="text-white font-bold">${breadthRes.toFixed(2)}</span></div>
        <div class="text-[13px] text-gray-400 italic">${getDynamicNote('breadth', stance.breadth_score)}</div>
      </div>
      <div class="flex flex-col">
        <div><span class="font-bold text-white">3. Flow (Foreign):</span> Skor <span class="text-emerald-400 font-bold">${stance.foreign_score}</span> Bobot 20% &rarr; ${stance.foreign_score} &times; 20% = <span class="text-white font-bold">${foreignRes.toFixed(2)}</span></div>
        <div class="text-[13px] text-gray-400 italic">${getDynamicNote('foreign', stance.foreign_score)}</div>
      </div>
      <div class="flex flex-col">
        <div><span class="font-bold text-white">4. Sector Rotation:</span> Skor <span class="text-emerald-400 font-bold">${stance.sector_score}</span> Bobot 15% &rarr; ${stance.sector_score} &times; 15% = <span class="text-white font-bold">${sectorRes.toFixed(2)}</span></div>
        <div class="text-[13px] text-gray-400 italic">${getDynamicNote('sector', stance.sector_score)}</div>
      </div>
      <div class="flex flex-col">
        <div><span class="font-bold text-white">5. Volatility & Liquidity (Rupiah):</span> Skor <span class="text-emerald-400 font-bold">${stance.rupiah_score}</span> Bobot 10% &rarr; ${stance.rupiah_score} &times; 10% = <span class="text-white font-bold">${rupiahRes.toFixed(2)}</span></div>
        <div class="text-[13px] text-gray-400 italic">${getDynamicNote('rupiah', stance.rupiah_score)}</div>
      </div>
      <div class="mt-4 pt-4 border-t border-gray-700">
        <div class="font-bold text-lg text-white">Total Final Score: <span class="text-blue-400">${total.toFixed(2)}</span></div>
      </div>
    </div>
  `;

  Swal.fire({
    title: '<span class="text-lg font-bold text-white">Breakdown Perhitungan Regime</span>',
    html: html,
    background: '#1A1A1A',
    color: '#fff',
    width: '650px',
    showConfirmButton: true,
    confirmButtonText: 'Tutup',
    confirmButtonColor: '#3B82F6',
    customClass: {
      popup: 'border border-gray-800 rounded-xl'
    }
  });
};
</script>

<template>
  <AdminLayout>
    <Head title="Manajemen Desk Brief" />

    <div class="mb-6 flex justify-between items-center">
      <div>
        <h2 class="text-2xl font-bold text-white">Desk Brief</h2>
        <p class="text-gray-400 mt-1">Kelola data market intelligence harian</p>
      </div>
      <div class="flex items-center gap-3">
        <button 
          @click="showUploadCsvModal = true"
          class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-medium flex items-center gap-2 transition-colors shadow-lg shadow-emerald-900/20"
        >
          <Upload class="w-4 h-4" />
          IHSG Price Trend (CSV)
        </button>
        <button 
          @click="showUploadModal = true"
          class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium flex items-center gap-2 transition-colors shadow-lg shadow-blue-900/20"
        >
          <Upload class="w-4 h-4" />
          Upload PDF Data & Draft
        </button>
      </div>
    </div>

    <!-- Upload PDF Modal -->
    <div v-if="showUploadModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
      <div class="bg-[#1A1A1A] border border-gray-800 rounded-xl w-full max-w-md shadow-2xl overflow-hidden">
        <div class="p-4 border-b border-gray-800 flex justify-between items-center bg-[#222]">
          <h3 class="text-lg font-bold text-white flex items-center gap-2">
            <Upload class="w-5 h-5 text-blue-400" />
            Upload PDF Statistik Harian
          </h3>
          <button @click="showUploadModal = false" class="text-gray-400 hover:text-white transition-colors">
            <X class="w-5 h-5" />
          </button>
        </div>
        
        <form @submit.prevent="submitUpload" class="p-6">
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-300 mb-2">File PDF (IDX Daily Statistics)</label>
            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-700 border-dashed rounded-lg hover:border-blue-500 hover:bg-blue-500/5 transition-all">
              <div class="space-y-1 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                  <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <div class="flex text-sm text-gray-400 justify-center">
                  <label for="file-upload" class="relative cursor-pointer bg-[#1A1A1A] rounded-md font-medium text-blue-400 hover:text-blue-300 focus-within:outline-none">
                    <span>Upload a file</span>
                    <input id="file-upload" name="file-upload" type="file" accept="application/pdf" class="sr-only" @change="handleFileChange" />
                  </label>
                  <p class="pl-1">or drag and drop</p>
                </div>
                <p class="text-xs text-gray-500 mt-2">
                  {{ form.pdf_file ? form.pdf_file.name : 'PDF up to 10MB' }}
                </p>
              </div>
            </div>
            <p v-if="form.errors.pdf_file" class="mt-2 text-sm text-red-500">{{ form.errors.pdf_file }}</p>
          </div>

          <div class="flex justify-end gap-3 mt-8">
            <button type="button" @click="showUploadModal = false" class="px-4 py-2 bg-transparent text-gray-400 hover:text-white transition-colors">
              Batal
            </button>
            <button type="submit" :disabled="form.processing || !form.pdf_file" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white rounded-lg font-medium transition-colors flex items-center gap-2">
              <span v-if="form.processing" class="w-4 h-4 rounded-full border-2 border-white/20 border-t-white animate-spin"></span>
              {{ form.processing ? 'Memproses...' : 'Proses & Buat Draft' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Upload CSV Modal -->
    <div v-if="showUploadCsvModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
      <div class="bg-[#1A1A1A] border border-gray-800 rounded-xl w-full max-w-md shadow-2xl overflow-hidden">
        <div class="p-4 border-b border-gray-800 flex justify-between items-center bg-[#222]">
          <h3 class="text-lg font-bold text-white flex items-center gap-2">
            <Upload class="w-5 h-5 text-emerald-400" />
            Upload CSV IHSG Historical
          </h3>
          <button @click="showUploadCsvModal = false" class="text-gray-400 hover:text-white transition-colors">
            <X class="w-5 h-5" />
          </button>
        </div>
        
        <form @submit.prevent="submitCsvUpload" class="p-6">
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-300 mb-2">File CSV (investing.com)</label>
            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-700 border-dashed rounded-lg hover:border-emerald-500 hover:bg-emerald-500/5 transition-all">
              <div class="space-y-1 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                  <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <div class="flex text-sm text-gray-400 justify-center">
                  <label for="csv-upload" class="relative cursor-pointer bg-[#1A1A1A] rounded-md font-medium text-emerald-400 hover:text-emerald-300 focus-within:outline-none">
                    <span>Upload a file</span>
                    <input id="csv-upload" name="csv-upload" type="file" accept=".csv" class="sr-only" @change="handleCsvFileChange" />
                  </label>
                  <p class="pl-1">or drag and drop</p>
                </div>
                <p class="text-xs text-gray-500 mt-2">
                  {{ csvForm.csv_file ? csvForm.csv_file.name : 'CSV up to 10MB' }}
                </p>
              </div>
            </div>
            <p v-if="csvForm.errors.csv_file" class="mt-2 text-sm text-red-500">{{ csvForm.errors.csv_file }}</p>
          </div>

          <div class="flex justify-end gap-3 mt-8">
            <button type="button" @click="showUploadCsvModal = false" class="px-4 py-2 bg-transparent text-gray-400 hover:text-white transition-colors">
              Batal
            </button>
            <button type="submit" :disabled="csvForm.processing || !csvForm.csv_file" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white rounded-lg font-medium transition-colors flex items-center gap-2">
              <span v-if="csvForm.processing" class="w-4 h-4 rounded-full border-2 border-white/20 border-t-white animate-spin"></span>
              {{ csvForm.processing ? 'Memproses...' : 'Proses CSV' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="flex border-b border-gray-800 mb-6 gap-6">
      <button 
        @click="activeTab = 'data'"
        :class="['pb-3 font-medium transition-colors border-b-2', activeTab === 'data' ? 'border-blue-500 text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-300']"
      >
        Daftar Desk Brief
      </button>
      <button 
        @click="activeTab = 'simulator'"
        :class="['pb-3 font-medium transition-colors border-b-2', activeTab === 'simulator' ? 'border-emerald-500 text-emerald-400' : 'border-transparent text-gray-500 hover:text-gray-300']"
      >
        Regime Calculation Simulator & Verifier
      </button>
    </div>

    <!-- Tab Content: Data Table -->
    <div v-show="activeTab === 'data'" class="bg-[#1A1A1A] rounded-xl border border-gray-800">
      <DataTable
        :headers="headers"
        :items="deskBriefs.data"
        :pagination="deskBriefs.links"
        :show-checkbox="false"
      >
        <template #cell(status)="{ item }">
          <span :class="[
            'px-2 py-1 text-xs font-medium rounded-full',
            item.status === 'published' ? 'bg-green-500/10 text-green-500' : 'bg-yellow-500/10 text-yellow-500'
          ]">
            {{ item.status === 'published' ? 'Published' : 'Draft' }}
          </span>
        </template>
        
        <template #cell(market_stance_id)="{ item }">
          <div v-if="item.market_stance" class="flex flex-col gap-2 py-2">
            <div class="flex items-center gap-2">
              <span class="px-2.5 py-1 bg-emerald-500/10 text-emerald-400 text-xs font-bold rounded border border-emerald-500/20 shadow-sm flex items-center gap-2">
                <span class="text-white">{{ item.market_stance.score }}</span> <span class="text-emerald-600/50">|</span> {{ item.market_stance.label }}
              </span>
              <button @click="showBreakdown(item.market_stance)" class="p-1 text-blue-400 hover:bg-blue-500/10 rounded-md transition-colors" title="Lihat Breakdown Perhitungan">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 16v-4"></path><path d="M12 8h.01"></path></svg>
              </button>
            </div>
            <div class="grid grid-cols-5 gap-2 text-[10px] mt-1 bg-[#161616] p-2 rounded-lg border border-gray-800/60 shadow-inner w-fit">
              <div class="flex flex-col items-center justify-center px-1">
                <span class="uppercase tracking-wider text-[8px] text-gray-500 mb-0.5">Foreign</span>
                <span class="font-bold text-[11px]" :class="item.market_stance.foreign_score >= 50 ? 'text-emerald-400' : 'text-red-400'">{{ item.market_stance.foreign_score }}</span>
              </div>
              <div class="flex flex-col items-center justify-center px-1 border-l border-gray-800/50">
                <span class="uppercase tracking-wider text-[8px] text-gray-500 mb-0.5">Breadth</span>
                <span class="font-bold text-[11px]" :class="item.market_stance.breadth_score >= 50 ? 'text-emerald-400' : 'text-red-400'">{{ item.market_stance.breadth_score }}</span>
              </div>
              <div class="flex flex-col items-center justify-center px-1 border-l border-gray-800/50">
                <span class="uppercase tracking-wider text-[8px] text-gray-500 mb-0.5">Momentum</span>
                <span class="font-bold text-[11px]" :class="item.market_stance.momentum_score >= 50 ? 'text-emerald-400' : 'text-red-400'">{{ item.market_stance.momentum_score }}</span>
              </div>
              <div class="flex flex-col items-center justify-center px-1 border-l border-gray-800/50">
                <span class="uppercase tracking-wider text-[8px] text-gray-500 mb-0.5">Volatility</span>
                <span class="font-bold text-[11px]" :class="item.market_stance.rupiah_score >= 50 ? 'text-emerald-400' : 'text-red-400'">{{ item.market_stance.rupiah_score }}</span>
              </div>
              <div class="flex flex-col items-center justify-center px-1 border-l border-gray-800/50">
                <span class="uppercase tracking-wider text-[8px] text-gray-500 mb-0.5">Sector</span>
                <span class="font-bold text-[11px]" :class="item.market_stance.sector_score >= 50 ? 'text-emerald-400' : 'text-red-400'">{{ item.market_stance.sector_score }}</span>
              </div>
            </div>
          </div>
          <div v-else class="text-gray-500 italic text-sm">
            -
          </div>
        </template>

        <template #actions="{ item }">
          <div class="flex items-center gap-2">
            <button
              @click="handlePreview(item)"
              class="p-2 text-blue-400 hover:bg-blue-500/10 rounded-lg transition-colors"
              title="Preview Frontend"
            >
              <Eye class="w-4 h-4" />
            </button>
            <button
              @click="handleEdit(item)"
              class="p-2 text-yellow-400 hover:bg-yellow-500/10 rounded-lg transition-colors"
              title="Edit Narasi"
            >
              <Edit class="w-4 h-4" />
            </button>
            <button
              v-if="item.status !== 'published'"
              @click="handlePublish(item)"
              class="p-2 text-green-400 hover:bg-green-500/10 rounded-lg transition-colors"
              title="Publish"
            >
              <CheckCircle class="w-4 h-4" />
            </button>
          </div>
        </template>
      </DataTable>
    </div>

    <!-- Tab Content: Simulator -->
    <div v-show="activeTab === 'simulator'">
      <RegimeSimulator />
    </div>
  </AdminLayout>
</template>
