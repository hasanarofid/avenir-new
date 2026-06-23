<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    data: {
        type: Object,
        required: true
    },
    isFeatured: {
        type: Boolean,
        default: false
    }
});

const formattedPrice = computed(() => {
    if (!props.data.target_price) return '-';
    if (isNaN(props.data.target_price)) return props.data.target_price;
    return `Rp ${Number(props.data.target_price).toLocaleString('id-ID')}`;
});

const upsideColor = computed(() => {
    if (!props.data.upside) return 'text-slate-400';
    return props.data.upside.includes('-') ? 'text-red-400' : 'text-emerald-400';
});

const recColor = computed(() => {
    const rec = props.data.recommendation?.toUpperCase() || 'BUY';
    if (rec === 'BUY') return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30';
    if (rec === 'SELL') return 'bg-red-500/10 text-red-400 border-red-500/30';
    return 'bg-amber-500/10 text-amber-400 border-amber-500/30'; // HOLD
});

const formattedDate = computed(() => {
    if (!props.data.created_at) return 'TBA';
    const date = new Date(props.data.created_at);
    return isNaN(date.getTime()) ? props.data.created_at : date.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
});
</script>

<template>
  <Link :href="data.slug ? `/katalog/${data.slug}` : `/katalog/${data.id}`" class="rcard-link">
    <div :class="['rcard', isFeatured ? 'rcard-featured' : '']">
      <!-- Cover Image (Left side) -->
      <div class="rcard-cover">
        <div v-if="isFeatured" class="rcard-featured-badge">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          LAPORAN UNGGULAN
        </div>
        <img v-if="data.image" :src="data.image" :alt="data.title" loading="lazy" />
        <div v-else class="rcard-cover-placeholder">
          <span class="placeholder-icon">📊</span>
        </div>
        <div class="rcard-cover-overlay"></div>
      </div>

      <!-- Body (Right side) -->
      <div class="rcard-body">
        
        <!-- Header -->
        <div class="rcard-header">
          <div class="rcard-header-info">
            <span class="ticker">{{ data.ticker || 'N/A' }}</span>
            <span class="company-name text-slate-400 text-xs ml-2 truncate" style="max-width: 150px;">{{ data.company_name || data.sector || 'Perusahaan' }}</span>
          </div>
          <div v-if="isFeatured" class="ml-auto hidden sm:flex items-center gap-2">
             <span v-if="data.is_premium" class="premium-badge-gold">
               PREMIUM
               <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
             </span>
          </div>
          <div v-else class="ml-auto">
            <span :class="['rec-badge', recColor]">{{ data.recommendation || 'BUY' }}</span>
          </div>
        </div>

        <!-- Title -->
        <h2 class="rcard-title" :class="isFeatured ? 'text-2xl' : 'text-base'">{{ data.title }}</h2>
        <p v-if="isFeatured && data.subtitle" class="rcard-desc text-sm text-slate-400 mt-2 line-clamp-2" v-html="data.subtitle"></p>

        <!-- Meta Grid -->
        <div class="rcard-meta" :class="isFeatured ? 'mt-6' : 'mt-auto'">
          <div class="meta-item">
            <span class="meta-lbl">Target Price</span>
            <span class="meta-val">{{ formattedPrice }}</span>
          </div>
          <div class="meta-item">
            <span class="meta-lbl">Upside</span>
            <span class="meta-val" :class="upsideColor">{{ data.upside || '-' }}</span>
          </div>
          <div class="meta-item">
            <span class="meta-lbl">Tanggal Terbit</span>
            <span class="meta-val text-slate-400 text-xs">{{ formattedDate }}</span>
          </div>
          <div class="meta-item" v-if="isFeatured">
            <span class="meta-lbl">Rekomendasi</span>
            <span :class="['rec-badge mt-1 inline-block', recColor]">{{ data.recommendation || 'BUY' }}</span>
          </div>
          <div class="meta-item ml-6" v-if="isFeatured">
            <span class="meta-lbl">Engagement</span>
            <div class="flex items-center gap-4 mt-1">
              <span class="flex items-center gap-1.5 hover:text-rose-400 transition-colors text-sm text-slate-300 font-medium">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                {{ data.likes_count || 0 }}
              </span>
              <span class="flex items-center gap-1.5 hover:text-blue-400 transition-colors text-sm text-slate-300 font-medium">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                {{ data.comments_count || 0 }}
              </span>
              <span class="flex items-center gap-1.5 hover:text-emerald-400 transition-colors text-sm text-slate-300 font-medium">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                {{ data.shares_count || 0 }}
              </span>
            </div>
          </div>
          
          <div class="meta-item analyst-info ml-auto" v-if="isFeatured">
            <span class="meta-lbl">Analis</span>
            <div class="flex items-center gap-2 mt-1">
              <div class="w-8 h-8 rounded-full bg-slate-800 overflow-hidden flex items-center justify-center border border-white/10">
                <img v-if="data.author?.profile_photo_url" :src="data.author.profile_photo_url" :alt="data.author?.name" class="w-full h-full object-cover" />
                <span v-else class="text-xs">🧑‍💼</span>
              </div>
              <div class="flex flex-col">
                <span class="text-sm font-semibold text-white">{{ data.author?.name || 'Tim Avenir' }}</span>
                <span class="text-[10px] text-slate-500">Equity Analyst</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer for Standard Card -->
        <div v-if="!isFeatured" class="rcard-footer">
          <span v-if="data.is_premium" class="premium-badge-gold-sm">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            PREMIUM
          </span>
          <span v-else class="free-badge-sm">GRATIS</span>
          
          <div class="flex items-center gap-3 ml-4">
              <span class="flex items-center gap-1 hover:text-rose-400 transition-colors text-xs text-slate-400">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                {{ data.likes_count || 0 }}
              </span>
              <span class="flex items-center gap-1 hover:text-blue-400 transition-colors text-xs text-slate-400">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                {{ data.comments_count || 0 }}
              </span>
              <span class="flex items-center gap-1 hover:text-emerald-400 transition-colors text-xs text-slate-400">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                {{ data.shares_count || 0 }}
              </span>
          </div>

          <div class="analyst-info-sm ml-auto flex items-center gap-2">
            <div class="w-5 h-5 rounded-full bg-slate-800 overflow-hidden flex items-center justify-center border border-white/10">
              <img v-if="data.author?.profile_photo_url" :src="data.author.profile_photo_url" :alt="data.author?.name" class="w-full h-full object-cover" />
              <span v-else class="text-[8px]">🧑‍💼</span>
            </div>
            <span class="text-xs text-slate-400 font-medium">{{ data.author?.name || 'Tim Avenir' }}</span>
          </div>
        </div>

      </div>
    </div>
  </Link>
</template>

<style scoped>
.rcard-link {
  text-decoration: none;
  display: block;
  height: 100%;
}

.rcard {
  display: flex;
  background: #111413;
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 12px;
  overflow: hidden;
  height: 100%;
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.rcard:hover {
  border-color: rgba(16, 185, 129, 0.3);
  transform: translateY(-4px);
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.4), 0 0 20px rgba(16, 185, 129, 0.05);
}

.rcard-cover {
  width: 140px;
  flex-shrink: 0;
  position: relative;
  background: #0d1110;
  border-right: 1px solid rgba(255, 255, 255, 0.04);
}

.rcard-cover img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.rcard-cover-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #111413 0%, #161c1a 100%);
}

.placeholder-icon {
  font-size: 24px;
  opacity: 0.2;
}

.rcard-cover-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to bottom, rgba(17, 20, 19, 0) 50%, rgba(17, 20, 19, 0.8) 100%);
}

.rcard-body {
  padding: 16px;
  display: flex;
  flex-direction: column;
  flex: 1;
  min-width: 0; /* Important for truncation */
}

.rcard-header {
  display: flex;
  align-items: center;
  margin-bottom: 12px;
}

.rcard-header-info {
  display: flex;
  align-items: center;
  min-width: 0;
}

.ticker {
  font-family: 'Roboto', sans-serif;
  font-size: 13px;
  font-weight: 700;
  color: #fff;
}

.rec-badge {
  font-size: 9px;
  font-weight: 800;
  padding: 3px 8px;
  border-radius: 4px;
  border: 1px solid transparent;
  letter-spacing: 0.05em;
}

.rcard-title {
  font-family: 'Roboto', sans-serif;
  font-weight: 600;
  color: #fff;
  line-height: 1.4;
  margin-bottom: 16px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.rcard:hover .rcard-title {
  color: #10b981;
}

.rcard-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 16px 24px;
}

.meta-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.meta-lbl {
  font-size: 10px;
  color: #64748b;
}

.meta-val {
  font-family: 'Roboto', sans-serif;
  font-size: 13px;
  font-weight: 600;
  color: #fff;
}

.rcard-footer {
  margin-top: 20px;
  padding-top: 12px;
  border-top: 1px solid rgba(255, 255, 255, 0.05);
  display: flex;
  align-items: center;
}

.premium-badge-gold-sm {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 10px;
  font-weight: 700;
  color: #fbbf24;
}

.free-badge-sm {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 10px;
  font-weight: 700;
  color: #10b981;
}

/* ── Featured Card Overrides ── */
.rcard-featured {
  background: #0d1110;
  border-color: rgba(16, 185, 129, 0.15);
}

.rcard-featured .rcard-cover {
  width: 35%;
  min-width: 250px;
}

.rcard-featured-badge {
  position: absolute;
  top: 16px;
  left: 16px;
  z-index: 10;
  background: #10b981;
  color: #fff;
  font-size: 10px;
  font-weight: 800;
  padding: 4px 10px;
  border-radius: 4px;
  display: flex;
  align-items: center;
  gap: 6px;
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.rcard-featured .rcard-body {
  padding: 24px 32px;
}

.premium-badge-gold {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 11px;
  font-weight: 700;
  color: #fbbf24;
  background: rgba(245, 158, 11, 0.1);
  border: 1px solid rgba(245, 158, 11, 0.2);
  padding: 4px 12px;
  border-radius: 6px;
}

@media (max-width: 640px) {
  .rcard {
    flex-direction: column;
  }
  .rcard-cover {
    width: 100%;
    height: 180px;
    border-right: none;
    border-bottom: 1px solid rgba(255, 255, 255, 0.04);
  }
  .rcard-featured .rcard-cover {
    width: 100%;
  }
}
</style>
