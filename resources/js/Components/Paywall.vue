<script setup>
import { authStore } from '@/Stores/authStore';

defineProps({
    title: {
        type: String,
        default: 'Daftar Gratis untuk Mulai Akses'
    },
    subtitle: {
        type: String,
        default: 'Daftar sekarang dan dapatkan akses ke seluruh katalog riset ekuitas, scenario analysis, dan investment thesis Avenir Research.'
    },
    price: {
        type: String,
        default: 'Mulai dari <strong>Rp 149.000 / bulan</strong>'
    }
});
</script>

<template>
    <div class="paywall-overlay">
        <div class="pw-icon">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="emerald-glow">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
        </div>
        <h2 class="pw-title">{{ title }}</h2>
        <p class="pw-sub">{{ subtitle }}</p>
        
        <div class="pw-price" v-html="price"></div>
        
        <div class="pw-actions">
            <button class="pw-btn-primary" @click="authStore.open('register')">
                Mulai Trial 7 Hari
            </button>
            <button class="pw-btn-secondary" @click="authStore.open('login')">
                Sudah Punya Akun
            </button>
        </div>
        
        <div class="pw-link">
            <a href="/langganan">Lihat detail paket</a>
        </div>
    </div>
</template>

<style scoped>
.paywall-overlay {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    padding: 60px 24px;
    text-align: center;
    background: linear-gradient(180deg, rgba(9, 11, 10, 0) 0%, rgba(9, 11, 10, 0.96) 25%, #090b0a 55%);
    z-index: 100;
}

.pw-icon {
    margin-bottom: 24px;
    color: #10B981;
}

.emerald-glow {
    filter: drop-shadow(0 0 8px rgba(16, 185, 129, 0.4));
}

.pw-title {
    font-size: clamp(24px, 5vw, 32px);
    font-weight: 800;
    color: #ffffff;
    margin-bottom: 16px;
    line-height: 1.2;
    max-width: 600px;
    letter-spacing: -0.02em;
}

.pw-sub {
    font-size: 15px;
    color: #94a3b8;
    line-height: 1.6;
    margin-bottom: 24px;
    max-width: 560px;
}

.pw-price {
    display: inline-block;
    padding: 10px 24px;
    background: rgba(16, 185, 129, 0.08);
    border: 1px solid rgba(16, 185, 129, 0.2);
    border-radius: 100px;
    font-size: 13.5px;
    color: #10B981;
    margin-bottom: 32px;
}

:deep(.pw-price strong) {
    color: #ffffff;
    font-weight: 700;
}

.pw-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    justify-content: center;
    margin-bottom: 24px;
}

.pw-btn-primary,
.pw-btn-secondary {
    padding: 14px 32px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 14px;
    cursor: pointer;
    transition: all .2s ease;
    border: none;
    font-family: inherit;
}

.pw-btn-primary {
    background: #059669;
    color: #fff;
    box-shadow: 0 4px 12px rgba(5, 150, 105, 0.2);
}

.pw-btn-primary:hover {
    background: #10b981;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.3);
}

.pw-btn-secondary {
    background: #121614;
    color: #f8fafc;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.pw-btn-secondary:hover {
    background: #1a201d;
    border-color: rgba(255, 255, 255, 0.2);
    transform: translateY(-2px);
}

.pw-link {
    font-size: 13px;
    color: #64748b;
}

.pw-link a {
    color: #10B981;
    text-decoration: none;
    font-weight: 600;
}

.pw-link a:hover {
    text-decoration: underline;
}
</style>
