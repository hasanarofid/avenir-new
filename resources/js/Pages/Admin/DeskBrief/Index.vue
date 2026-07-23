<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import RegimeSimulator from './Components/RegimeSimulator.vue';
import { Edit, Eye, CheckCircle, Upload, X, Trash2 } from '@lucide/vue';
import Swal from 'sweetalert2';

const props = defineProps({
  deskBriefs: {
    type: Object,
    required: true
  }
});

const showUploadModal = ref(false);
const showUploadCsvModal = ref(false);
const showMasterlistModal = ref(false);
const showRingkasanSahamModal = ref(false);
const showForeignFlowModal = ref(false);
const showDataMakroModal = ref(false);

const form = useForm({
  pdf_file: null,
});

const csvForm = useForm({
  csv_file: null,
});

const masterlistForm = useForm({
  excel_file: null,
});

const ringkasanSahamForm = useForm({
  ringkasan_saham: null,
  index_summary: null,
  date: new Date().toISOString().split('T')[0],
});

const foreignFlowForm = useForm({
  foreign_flow: null,
  date: new Date().toISOString().split('T')[0],
});

const dataMakroForm = useForm({
  file_makro: null,
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

const handleMasterlistChange = (e) => {
  if (e.target.files.length > 0) {
    masterlistForm.excel_file = e.target.files[0];
  }
};

const handleRingkasanSahamChange = (e) => {
  if (e.target.files.length > 0) {
    ringkasanSahamForm.ringkasan_saham = e.target.files[0];
  }
};

const handleIndexSummaryChange = (e) => {
  if (e.target.files.length > 0) {
    ringkasanSahamForm.index_summary = e.target.files[0];
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

const submitMasterlistUpload = () => {
  if (!masterlistForm.excel_file) {
    Swal.fire('Error', 'Pilih file Excel terlebih dahulu', 'error');
    return;
  }
  masterlistForm.post(route('admin.desk-brief.upload-masterlist'), {
    preserveScroll: true,
    onSuccess: () => {
      showMasterlistModal.value = false;
      masterlistForm.reset();
      Swal.fire('Berhasil!', 'Masterlist berhasil diupdate.', 'success');
    },
    onError: (errors) => {
      Swal.fire('Gagal', errors.excel_file || 'Terjadi kesalahan saat memproses masterlist.', 'error');
    }
  });
};

const submitRingkasanSahamUpload = () => {
  if (!ringkasanSahamForm.ringkasan_saham || !ringkasanSahamForm.index_summary || !ringkasanSahamForm.date) {
    Swal.fire('Error', 'Lengkapi kedua file dan tanggal terlebih dahulu', 'error');
    return;
  }
  ringkasanSahamForm.post(route('admin.desk-brief.upload-ringkasan-saham'), {
    preserveScroll: true,
    onSuccess: (page) => {
      showRingkasanSahamModal.value = false;
      ringkasanSahamForm.reset();
      
      const summary = page.props.flash?.market_breadth_summary;
      if (summary) {
        const html = `
          <div class="text-left font-mono text-sm bg-[#111] p-4 rounded-lg border border-gray-800 text-gray-300">
            <div class="text-white font-bold mb-2">MARKET BREADTH SUMMARY</div>
            <div>Advancers / Decliners: ${summary.advancers} / ${summary.decliners}</div>
            <div>Score Raw: ${summary.market_breadth_score_raw}</div>
            <div>Score Final: ${summary.market_breadth_score}</div>
            <div class="text-blue-400 font-bold">${summary.market_breadth_label}</div>
          </div>
        `;
        Swal.fire({
          title: '<span class="text-white">Hasil Analisis Market Breadth</span>',
          html: html,
          background: '#1A1A1A',
          width: '500px',
          showConfirmButton: true,
          confirmButtonColor: '#3B82F6'
        });
      } else {
        Swal.fire('Berhasil!', 'File Ringkasan Saham berhasil diproses.', 'success');
      }
    },
    onError: (errors) => {
      Swal.fire('Gagal', errors.ringkasan_saham || 'Terjadi kesalahan saat memproses file.', 'error');
    }
  });
};

const handleForeignFlowChange = (e) => {
  if (e.target.files.length > 0) {
    foreignFlowForm.foreign_flow = e.target.files[0];
  }
};

const submitForeignFlowUpload = () => {
  foreignFlowForm.post(route('admin.desk-brief.upload-foreign-flow'), {
    preserveScroll: true,
    onSuccess: () => {
      showForeignFlowModal.value = false;
      foreignFlowForm.reset();
      Swal.fire('Berhasil!', 'File Data Foreign Flow berhasil diproses.', 'success');
    },
    onError: (errors) => {
      Swal.fire('Gagal', errors.foreign_flow || 'Terjadi kesalahan saat memproses file.', 'error');
    }
  });
};

const handleDataMakroChange = (e) => {
  if (e.target.files.length > 0) {
    dataMakroForm.file_makro = e.target.files[0];
  }
};

const submitDataMakroUpload = () => {
  if (!dataMakroForm.file_makro) {
    Swal.fire('Error', 'Pilih file Excel Data Makro terlebih dahulu', 'error');
    return;
  }
  dataMakroForm.post(route('admin.desk-brief.upload-data-makro'), {
    preserveScroll: true,
    onSuccess: () => {
      showDataMakroModal.value = false;
      dataMakroForm.reset();
      Swal.fire('Berhasil!', 'File Data Makro Avenir berhasil di-import.', 'success');
    },
    onError: (errors) => {
      Swal.fire('Gagal', errors.file_makro || 'Terjadi kesalahan saat memproses file.', 'error');
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

const handleDelete = (item) => {
  Swal.fire({
    title: 'Hapus Desk Brief?',
    text: "Data yang dihapus tidak dapat dikembalikan.",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#EF4444',
    cancelButtonColor: '#3B82F6',
    confirmButtonText: 'Ya, Hapus!',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) {
      router.delete(route('admin.desk-brief.destroy', item.id), {
        preserveScroll: true,
        onSuccess: () => {
          Swal.fire(
            'Terhapus!',
            'Desk Brief telah dihapus.',
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
    </div>

    <!-- Upload Workflow Steps -->
    <div class="mb-8 space-y-6">
      
      <!-- Wajib Harian -->
      <div class="bg-[#1A1A1A] p-6 rounded-xl border border-gray-800 shadow-xl">
        <h3 class="text-white font-bold text-lg mb-4 flex items-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-400"><path d="M12 2v20"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
          Wajib Harian (Setiap Bursa Tutup)
        </h3>
        <p class="text-sm text-gray-400 mb-6">Data Price Trend dan Volatility otomatis ditarik via API. Silakan upload 2 data berikut untuk melengkapi perhitungan Regime Engine:</p>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Step 1: Market Breadth -->
          <div class="bg-[#222] p-4 rounded-lg border border-purple-500/30 relative flex flex-col justify-between">
            <div>
              <div class="absolute -top-3 -left-3 w-6 h-6 bg-purple-600 rounded-full flex items-center justify-center text-xs font-bold text-white border-2 border-[#1A1A1A]">1</div>
              <div class="text-purple-400 font-bold mb-1">Upload Ringkasan Saham & Index Summary</div>
              <p class="text-xs text-gray-400 mb-4 leading-relaxed">Format: <span class="text-gray-300 font-medium">.xlsx</span> (Untuk menghitung Market Breadth & Sector Rotation).</p>
            </div>
            <button @click="showRingkasanSahamModal = true" class="w-full py-2 bg-purple-600/10 hover:bg-purple-600/20 text-purple-400 border border-purple-500/30 rounded-lg text-sm font-medium transition-colors flex justify-center items-center gap-2">
              <Upload class="w-4 h-4" /> Pilih File Ringkasan & Index Summary
            </button>
          </div>

          <!-- Step 2: Foreign Flow -->
          <div class="bg-[#222] p-4 rounded-lg border border-pink-500/30 relative flex flex-col justify-between">
            <div>
              <div class="absolute -top-3 -left-3 w-6 h-6 bg-pink-600 rounded-full flex items-center justify-center text-xs font-bold text-white border-2 border-[#1A1A1A]">2</div>
              <div class="text-pink-400 font-bold mb-1">Upload Data Foreign Flow</div>
              <p class="text-xs text-gray-400 mb-4 leading-relaxed">Format: <span class="text-gray-300 font-medium">.xlsx</span> (Untuk menghitung Foreign Net Flow & Value Traded).</p>
            </div>
            <button @click="showForeignFlowModal = true" class="w-full py-2 bg-pink-600/10 hover:bg-pink-600/20 text-pink-400 border border-pink-500/30 rounded-lg text-sm font-medium transition-colors flex justify-center items-center gap-2">
              <Upload class="w-4 h-4" /> Pilih File Flow
            </button>
          </div>
        </div>
      </div>

      <!-- Opsional / Data Master -->
      <details class="group bg-[#1A1A1A] rounded-xl border border-gray-800 shadow-xl overflow-hidden cursor-pointer">
        <summary class="p-4 bg-[#222] text-gray-300 font-medium flex items-center justify-between outline-none">
          <span class="flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
            Menu Data Master & Upload Opsional
          </span>
          <svg class="w-5 h-5 text-gray-500 group-open:rotate-180 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
        </summary>
        
        <div class="p-6 grid grid-cols-1 md:grid-cols-4 gap-4 cursor-default">
          <!-- Masterlist -->
          <div class="bg-[#2a2a2a] p-4 rounded-lg border border-indigo-500/20 flex flex-col justify-between">
            <div>
              <div class="text-indigo-400 font-bold mb-1">Masterlist Saham</div>
              <p class="text-xs text-gray-400 mb-4 leading-relaxed">Update daftar Indeks & Sektor Saham (Jarang dilakukan).</p>
            </div>
            <button @click.stop="showMasterlistModal = true" class="w-full py-2 bg-indigo-600/10 hover:bg-indigo-600/20 text-indigo-400 border border-indigo-500/30 rounded-lg text-sm font-medium transition-colors flex justify-center items-center gap-2">
              <Upload class="w-4 h-4" /> Upload Masterlist
            </button>
          </div>

          <!-- Price Trend Manual -->
          <div class="bg-[#2a2a2a] p-4 rounded-lg border border-emerald-500/20 flex flex-col justify-between">
            <div>
              <div class="text-emerald-400 font-bold mb-1">IHSG Historis (Manual)</div>
              <p class="text-xs text-gray-400 mb-4 leading-relaxed">Jika API Sectors gagal, gunakan file CSV Investing.com.</p>
            </div>
            <button @click.stop="showUploadCsvModal = true" class="w-full py-2 bg-emerald-600/10 hover:bg-emerald-600/20 text-emerald-400 border border-emerald-500/30 rounded-lg text-sm font-medium transition-colors flex justify-center items-center gap-2">
              <Upload class="w-4 h-4" /> Upload CSV IHSG
            </button>
          </div>

          <!-- Upload PDF Harian (Legacy) -->
          <div class="bg-[#2a2a2a] p-4 rounded-lg border border-blue-500/20 flex flex-col justify-between">
            <div>
              <div class="text-blue-400 font-bold mb-1">PDF Harian & Draft</div>
              <p class="text-xs text-gray-400 mb-4 leading-relaxed">Upload Daily Statistics PDF untuk generate AI Draft.</p>
            </div>
            <button @click.stop="showUploadModal = true" class="w-full py-2 bg-blue-600/10 hover:bg-blue-600/20 text-blue-400 border border-blue-500/30 rounded-lg text-sm font-medium transition-colors flex justify-center items-center gap-2">
              <Upload class="w-4 h-4" /> Upload PDF
            </button>
          </div>

          <!-- Upload Data Makro Avenir -->
          <div class="bg-[#2a2a2a] p-4 rounded-lg border border-amber-500/20 flex flex-col justify-between">
            <div>
              <div class="text-amber-400 font-bold mb-1">Data Makro Avenir</div>
              <p class="text-xs text-gray-400 mb-4 leading-relaxed">Upload Excel GDP, Inflasi, M2, FX & Flow.</p>
            </div>
            <button @click.stop="showDataMakroModal = true" class="w-full py-2 bg-amber-600/10 hover:bg-amber-600/20 text-amber-400 border border-amber-500/30 rounded-lg text-sm font-medium transition-colors flex justify-center items-center gap-2">
              <Upload class="w-4 h-4" /> Upload Data Makro
            </button>
          </div>
        </div>
      </details>
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

    <!-- Upload Masterlist Modal -->
    <div v-if="showMasterlistModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
      <div class="bg-[#1A1A1A] border border-gray-800 rounded-xl w-full max-w-md shadow-2xl overflow-hidden">
        <div class="p-4 border-b border-gray-800 flex justify-between items-center bg-[#222]">
          <h3 class="text-lg font-bold text-white flex items-center gap-2">
            <Upload class="w-5 h-5 text-indigo-400" />
            Upload Masterlist Saham
          </h3>
          <button @click="showMasterlistModal = false" class="text-gray-400 hover:text-white transition-colors">
            <X class="w-5 h-5" />
          </button>
        </div>
        
        <form @submit.prevent="submitMasterlistUpload" class="p-6">
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-300 mb-2">File Excel (Financial Data & Ratio)</label>
            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-700 border-dashed rounded-lg hover:border-indigo-500 hover:bg-indigo-500/5 transition-all">
              <div class="space-y-1 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                  <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <div class="flex text-sm text-gray-400 justify-center">
                  <label for="masterlist-upload" class="relative cursor-pointer bg-[#1A1A1A] rounded-md font-medium text-indigo-400 hover:text-indigo-300 focus-within:outline-none">
                    <span>Upload a file</span>
                    <input id="masterlist-upload" name="masterlist-upload" type="file" accept=".xlsx,.xls,.csv" class="sr-only" @change="handleMasterlistChange" />
                  </label>
                </div>
                <p class="text-xs text-gray-500 mt-2">
                  {{ masterlistForm.excel_file ? masterlistForm.excel_file.name : 'Excel up to 10MB' }}
                </p>
              </div>
            </div>
            <p v-if="masterlistForm.errors.excel_file" class="mt-2 text-sm text-red-500">{{ masterlistForm.errors.excel_file }}</p>
          </div>

          <div class="flex justify-end gap-3 mt-8">
            <button type="button" @click="showMasterlistModal = false" class="px-4 py-2 bg-transparent text-gray-400 hover:text-white transition-colors">Batal</button>
            <button type="submit" :disabled="masterlistForm.processing || !masterlistForm.excel_file" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-lg font-medium transition-colors flex items-center gap-2">
              <span v-if="masterlistForm.processing" class="w-4 h-4 rounded-full border-2 border-white/20 border-t-white animate-spin"></span>
              {{ masterlistForm.processing ? 'Memproses...' : 'Proses Masterlist' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Upload Ringkasan Saham Modal -->
    <div v-if="showRingkasanSahamModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
      <div class="bg-[#1A1A1A] border border-gray-800 rounded-xl w-full max-w-md shadow-2xl overflow-hidden">
        <div class="p-4 border-b border-gray-800 flex justify-between items-center bg-[#222]">
          <h3 class="text-lg font-bold text-white flex items-center gap-2">
            <Upload class="w-5 h-5 text-purple-400" />
            Upload Ringkasan Saham & Index Summary (Breadth & Sector Rotation)
          </h3>
          <button @click="showRingkasanSahamModal = false" class="text-gray-400 hover:text-white transition-colors">
            <X class="w-5 h-5" />
          </button>
        </div>
        
        <form @submit.prevent="submitRingkasanSahamUpload" class="p-6">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-300 mb-2">Tanggal Data</label>
            <input type="date" v-model="ringkasanSahamForm.date" class="w-full bg-[#111] border border-gray-700 rounded-lg text-white px-4 py-2" required>
          </div>
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-300 mb-2">File Excel (Ringkasan Saham)</label>
            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-700 border-dashed rounded-lg hover:border-purple-500 hover:bg-purple-500/5 transition-all">
              <div class="space-y-1 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                  <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <div class="flex text-sm text-gray-400 justify-center">
                  <label for="ringkasan-upload" class="relative cursor-pointer bg-[#1A1A1A] rounded-md font-medium text-purple-400 hover:text-purple-300 focus-within:outline-none">
                    <span>Upload a file</span>
                    <input id="ringkasan-upload" name="ringkasan-upload" type="file" accept=".xlsx,.xls,.csv" class="sr-only" @change="handleRingkasanSahamChange" />
                  </label>
                </div>
                <p class="text-xs text-gray-500 mt-2">
                  {{ ringkasanSahamForm.ringkasan_saham ? ringkasanSahamForm.ringkasan_saham.name : 'Excel up to 50MB' }}
                </p>
              </div>
            </div>
            <p v-if="ringkasanSahamForm.errors.ringkasan_saham" class="mt-2 text-sm text-red-500">{{ ringkasanSahamForm.errors.ringkasan_saham }}</p>
          </div>

          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-300 mb-2">File Excel (Index Summary)</label>
            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-700 border-dashed rounded-lg hover:border-purple-500 hover:bg-purple-500/5 transition-all">
              <div class="space-y-1 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                  <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <div class="flex text-sm text-gray-400 justify-center">
                  <label for="index-summary-upload" class="relative cursor-pointer bg-[#1A1A1A] rounded-md font-medium text-purple-400 hover:text-purple-300 focus-within:outline-none">
                    <span>Upload a file</span>
                    <input id="index-summary-upload" name="index-summary-upload" type="file" accept=".xlsx,.xls,.csv" class="sr-only" @change="handleIndexSummaryChange" />
                  </label>
                </div>
                <p class="text-xs text-gray-500 mt-2">
                  {{ ringkasanSahamForm.index_summary ? ringkasanSahamForm.index_summary.name : 'Excel up to 50MB' }}
                </p>
              </div>
            </div>
            <p v-if="ringkasanSahamForm.errors.index_summary" class="mt-2 text-sm text-red-500">{{ ringkasanSahamForm.errors.index_summary }}</p>
          </div>

          <div class="flex justify-end gap-3 mt-8">
            <button type="button" @click="showRingkasanSahamModal = false" class="px-4 py-2 bg-transparent text-gray-400 hover:text-white transition-colors">Batal</button>
            <button type="submit" :disabled="ringkasanSahamForm.processing || !ringkasanSahamForm.ringkasan_saham || !ringkasanSahamForm.index_summary" class="px-5 py-2 bg-purple-600 hover:bg-purple-700 disabled:opacity-50 text-white rounded-lg font-medium transition-colors flex items-center gap-2">
              <span v-if="ringkasanSahamForm.processing" class="w-4 h-4 rounded-full border-2 border-white/20 border-t-white animate-spin"></span>
              {{ ringkasanSahamForm.processing ? 'Memproses...' : 'Proses Data' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Upload Foreign Flow Modal -->
    <div v-if="showForeignFlowModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
      <div class="bg-[#1A1A1A] border border-gray-800 rounded-xl w-full max-w-md shadow-2xl overflow-hidden">
        <div class="p-4 border-b border-gray-800 flex justify-between items-center bg-[#222]">
          <h3 class="text-lg font-bold text-white flex items-center gap-2">
            <Upload class="w-5 h-5 text-pink-400" />
            Upload Data Foreign Flow
          </h3>
          <button @click="showForeignFlowModal = false" class="text-gray-400 hover:text-white transition-colors">
            <X class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="submitForeignFlowUpload" class="p-6">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-300 mb-2">Tanggal Data</label>
            <input type="date" v-model="foreignFlowForm.date" class="w-full bg-[#111] border border-gray-700 rounded-lg text-white px-4 py-2" required>
          </div>
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-300 mb-2">File Excel (Data Foreign Flow)</label>
            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-700 border-dashed rounded-lg hover:border-pink-500 hover:bg-pink-500/5 transition-all">
              <div class="space-y-1 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                  <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <div class="flex text-sm text-gray-400 justify-center">
                  <label for="foreign-flow-upload" class="relative cursor-pointer bg-[#1A1A1A] rounded-md font-medium text-pink-400 hover:text-pink-300 focus-within:outline-none">
                    <span>Upload a file</span>
                    <input id="foreign-flow-upload" name="foreign-flow-upload" type="file" accept=".xlsx,.xls" class="sr-only" @change="handleForeignFlowChange" />
                  </label>
                </div>
                <p class="text-xs text-gray-500 mt-2">
                  Format: .xlsx (Misal: Data Foreign Flow.xlsx)
                </p>
                <p v-if="foreignFlowForm.foreign_flow" class="text-xs text-pink-400 font-medium mt-2">
                  File terpilih: {{ foreignFlowForm.foreign_flow.name }}
                </p>
              </div>
            </div>
          </div>

          <div class="flex justify-end gap-3 mt-8">
            <button type="button" @click="showForeignFlowModal = false" class="px-4 py-2 bg-transparent text-gray-400 hover:text-white transition-colors">Batal</button>
            <button type="submit" :disabled="foreignFlowForm.processing || !foreignFlowForm.foreign_flow" class="px-5 py-2 bg-pink-600 hover:bg-pink-700 disabled:opacity-50 text-white rounded-lg font-medium transition-colors flex items-center gap-2">
              <span v-if="foreignFlowForm.processing" class="w-4 h-4 rounded-full border-2 border-white/20 border-t-white animate-spin"></span>
              {{ foreignFlowForm.processing ? 'Memproses...' : 'Proses Foreign Flow' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Upload Data Makro Modal -->
    <div v-if="showDataMakroModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
      <div class="bg-[#1A1A1A] border border-gray-800 rounded-xl w-full max-w-md shadow-2xl overflow-hidden">
        <div class="p-4 border-b border-gray-800 flex justify-between items-center bg-[#222]">
          <h3 class="text-lg font-bold text-white flex items-center gap-2">
            <Upload class="w-5 h-5 text-amber-400" />
            Upload Data Makro Avenir (Excel)
          </h3>
          <button @click="showDataMakroModal = false" class="text-gray-400 hover:text-white transition-colors">
            <X class="w-5 h-5" />
          </button>
        </div>
        
        <form @submit.prevent="submitDataMakroUpload" class="p-6">
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-300 mb-2">File Excel (Data Makro Avenir.xlsx)</label>
            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-700 border-dashed rounded-lg hover:border-amber-500 hover:bg-amber-500/5 transition-all">
              <div class="space-y-1 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                  <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <div class="flex text-sm text-gray-400 justify-center">
                  <label for="makro-upload" class="relative cursor-pointer bg-[#1A1A1A] rounded-md font-medium text-amber-400 hover:text-amber-300 focus-within:outline-none">
                    <span>Upload a file</span>
                    <input id="makro-upload" name="makro-upload" type="file" accept=".xlsx,.xls,.csv" class="sr-only" @change="handleDataMakroChange" />
                  </label>
                </div>
                <p class="text-xs text-gray-500 mt-2">
                  {{ dataMakroForm.file_makro ? dataMakroForm.file_makro.name : 'Excel up to 20MB' }}
                </p>
              </div>
            </div>
            <p v-if="dataMakroForm.errors.file_makro" class="mt-2 text-sm text-red-500">{{ dataMakroForm.errors.file_makro }}</p>
          </div>

          <div class="flex justify-end gap-3 mt-8">
            <button type="button" @click="showDataMakroModal = false" class="px-4 py-2 bg-transparent text-gray-400 hover:text-white transition-colors">Batal</button>
            <button type="submit" :disabled="dataMakroForm.processing || !dataMakroForm.file_makro" class="px-5 py-2 bg-amber-600 hover:bg-amber-700 disabled:opacity-50 text-white rounded-lg font-medium transition-colors flex items-center gap-2">
              <span v-if="dataMakroForm.processing" class="w-4 h-4 rounded-full border-2 border-white/20 border-t-white animate-spin"></span>
              {{ dataMakroForm.processing ? 'Memproses...' : 'Proses Data Makro' }}
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
        v-if="false"
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
            <button
              @click="handleDelete(item)"
              class="p-2 text-red-400 hover:bg-red-500/10 rounded-lg transition-colors"
              title="Hapus"
            >
              <Trash2 class="w-4 h-4" />
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
