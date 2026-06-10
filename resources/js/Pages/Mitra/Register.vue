<script setup>
import { computed, ref } from 'vue';
import { Head, useForm, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    password: '',
    password_confirmation: '',
    certification: '',
    specializations: '',
    portfolio_link: '',
    bank_name: '',
    bank_account_number: '',  
    bank_account_name: '',
});

const submit = () => {
    form.post(route('mitra.register.store'));
};
</script>

<template>
  <Head title="Daftar Mitra Analis" />

  <AppLayout>
    <div class="partners-dark-wrapper">
      <!-- Glow Backdrops -->
      <div class="radial-glow glow-top-right"></div>
      <div class="radial-glow glow-bottom-left"></div>

      <div class="register-page">
        <!-- Hero Section -->
        <div class="register-hero">
          <Link :href="route('home')" class="back-link">&larr; Kembali ke Home</Link>
          <div class="hero-eyebrow">◆ FORM PENDAFTARAN MITRA ANALIS</div>
          <h1>Daftar Sebagai Mitra Analis</h1>
          <p>
            Isi formulir pendaftaran berikut untuk bergabung sebagai Mitra Analis Avenir Research. Tim editorial akan review aplikasi Anda dan menghubungi Anda dalam 5-7 hari kerja.
          </p>
        </div>

        <!-- Form Section -->
        <div class="register-form-box">
          <div v-if="form.errors.error" class="error-banner">
            {{ form.errors.error }}
          </div>

          <form @submit.prevent="submit" class="register-form">
            <!-- Guest Registration Section -->
            <div v-if="!user" class="guest-register-fields">
              <div class="form-header">Informasi Akun</div>
              
              <div class="form-row-2col">
                <div class="form-group">
                  <label for="first_name">Nama Depan <span class="required">*</span></label>
                  <input 
                    id="first_name" 
                    v-model="form.first_name" 
                    type="text" 
                    placeholder="Nama Depan" 
                    class="form-input" 
                    :required="!user" 
                  />
                  <div v-if="form.errors.first_name" class="error-msg">{{ form.errors.first_name }}</div>
                </div>

                <div class="form-group">
                  <label for="last_name">Nama Belakang <span class="required">*</span></label>
                  <input 
                    id="last_name" 
                    v-model="form.last_name" 
                    type="text" 
                    placeholder="Nama Belakang" 
                    class="form-input" 
                    :required="!user" 
                  />
                  <div v-if="form.errors.last_name" class="error-msg">{{ form.errors.last_name }}</div>
                </div>
              </div>

              <div class="form-group">
                <label for="email">Email <span class="required">*</span></label>
                <input 
                  id="email" 
                  v-model="form.email" 
                  type="email" 
                  placeholder="email@anda.com" 
                  class="form-input" 
                  :required="!user" 
                />
                <div v-if="form.errors.email" class="error-msg">{{ form.errors.email }}</div>
              </div>

              <div class="form-row-2col">
                <div class="form-group">
                  <label for="password">Password <span class="required">*</span></label>
                  <div class="password-input-wrapper">
                    <input 
                      id="password" 
                      v-model="form.password" 
                      :type="showPassword ? 'text' : 'password'" 
                      placeholder="Min. 8 karakter" 
                      class="form-input" 
                      :required="!user" 
                    />
                    <button 
                      type="button" 
                      class="toggle-password-btn" 
                      @click="showPassword = !showPassword"
                      tabindex="-1"
                      aria-label="Toggle password visibility"
                    >
                      <svg v-if="showPassword" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0z"/>
                        <circle cx="12" cy="12" r="3"/>
                      </svg>
                      <svg v-else xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/>
                        <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/>
                        <path d="M6.61 6.61A13.52 13.52 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/>
                        <line x1="2" y1="2" x2="22" y2="22"/>
                      </svg>
                    </button>
                  </div>
                  <div v-if="form.errors.password" class="error-msg">{{ form.errors.password }}</div>
                </div>

                <div class="form-group">
                  <label for="password_confirmation">Konfirmasi Password <span class="required">*</span></label>
                  <div class="password-input-wrapper">
                    <input 
                      id="password_confirmation" 
                      v-model="form.password_confirmation" 
                      :type="showPasswordConfirmation ? 'text' : 'password'" 
                      placeholder="Ulangi password" 
                      class="form-input" 
                      :required="!user" 
                    />
                    <button 
                      type="button" 
                      class="toggle-password-btn" 
                      @click="showPasswordConfirmation = !showPasswordConfirmation"
                      tabindex="-1"
                      aria-label="Toggle password confirmation visibility"
                    >
                      <svg v-if="showPasswordConfirmation" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0z"/>
                        <circle cx="12" cy="12" r="3"/>
                      </svg>
                      <svg v-else xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/>
                        <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/>
                        <path d="M6.61 6.61A13.52 13.52 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/>
                        <line x1="2" y1="2" x2="22" y2="22"/>
                      </svg>
                    </button>
                  </div>
                  <div v-if="form.errors.password_confirmation" class="error-msg">{{ form.errors.password_confirmation }}</div>
                </div>
              </div>

              <div class="divider-line"></div>
            </div>

            <div class="form-header">Data Pribadi & Kredensial</div>

            <!-- Certification & Specializations -->
            <div class="form-row-2col">
              <div class="form-group">
                <label for="certification">Sertifikasi / Kualifikasi <span class="required">*</span></label>
                <p class="hint">Misal: CFA, WPPE, WPEE, atau kualifikasi profesional lainnya.</p>
                <input 
                  id="certification" 
                  v-model="form.certification" 
                  type="text" 
                  placeholder="Masukkan sertifikasi Anda" 
                  class="form-input" 
                  required 
                />
                <div v-if="form.errors.certification" class="error-msg">{{ form.errors.certification }}</div>
              </div>

              <div class="form-group">
                <label for="specializations">Spesialisasi (pisahkan dengan koma) <span class="required">*</span></label>
                <p class="hint">Misal: Mining, Commodities, Corporate Action</p>
                <input 
                  id="specializations" 
                  v-model="form.specializations" 
                  type="text" 
                  placeholder="Contoh: Banking, Consumer Goods, Valuation" 
                  class="form-input" 
                  required 
                />
                <div v-if="form.errors.specializations" class="error-msg">{{ form.errors.specializations }}</div>
              </div>
            </div>

            <!-- Portfolio Link -->
            <div class="form-group">
              <label for="portfolio_link">Link Portfolio (Opsional)</label>
              <input 
                id="portfolio_link" 
                v-model="form.portfolio_link" 
                type="url" 
                placeholder="https://linkedin.com/in/yourprofile" 
                class="form-input" 
              />
              <div v-if="form.errors.portfolio_link" class="error-msg">{{ form.errors.portfolio_link }}</div>
            </div>

            <div class="divider-line"></div>
            <div class="form-header">Data Bank untuk Pembayaran</div>

            <!-- Bank Row -->
            <div class="form-row-2col">
              <div class="form-group">
                <label for="bank_name">Nama Bank <span class="required">*</span></label>
                <input 
                  id="bank_name" 
                  v-model="form.bank_name" 
                  type="text" 
                  placeholder="Misal: BCA, Mandiri, BNI" 
                  class="form-input" 
                  required 
                />
                <div v-if="form.errors.bank_name" class="error-msg">{{ form.errors.bank_name }}</div>
              </div>

              <div class="form-group">
                <label for="bank_account_number">Nomor Rekening <span class="required">*</span></label>
                <input 
                  id="bank_account_number" 
                  v-model="form.bank_account_number" 
                  type="text" 
                  placeholder="Masukkan nomor rekening Anda" 
                  class="form-input" 
                  required 
                />
                <div v-if="form.errors.bank_account_number" class="error-msg">{{ form.errors.bank_account_number }}</div>
              </div>
            </div>

            <!-- Bank Account Name -->
            <div class="form-group">
              <label for="bank_account_name">Nama Pemilik Rekening <span class="required">*</span></label>
              <p class="hint">Nama harus sama dengan nama di buku tabungan.</p>
              <input 
                id="bank_account_name" 
                v-model="form.bank_account_name" 
                type="text" 
                placeholder="Masukkan nama pemilik rekening" 
                class="form-input" 
                required 
              />
              <div v-if="form.errors.bank_account_name" class="error-msg">{{ form.errors.bank_account_name }}</div>
            </div>

            <!-- Submit Button -->
            <button 
              type="submit" 
              :disabled="form.processing" 
              class="submit-btn"
            >
              {{ form.processing ? 'Menyimpan...' : 'Kirim Pendaftaran' }}
            </button>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.partners-dark-wrapper {
  background-color: #090b0a;
  color: #cbd5e1;
  font-family: 'Roboto', sans-serif;
  min-height: 100vh;
  position: relative;
  overflow-x: hidden;
  padding-top: 40px;
}

/* Glowing Background Vectors */
.radial-glow {
  position: absolute;
  border-radius: 50%;
  filter: blur(140px);
  pointer-events: none;
  z-index: 1;
}
.glow-top-right {
  top: -10%;
  right: -10%;
  width: 500px;
  height: 500px;
  background: radial-gradient(circle, rgba(16, 185, 129, 0.08) 0%, rgba(9, 11, 10, 0) 70%);
}
.glow-bottom-left {
  bottom: -10%;
  left: -10%;
  width: 600px;
  height: 600px;
  background: radial-gradient(circle, rgba(52, 211, 153, 0.06) 0%, rgba(9, 11, 10, 0) 70%);
}

.register-page {
  max-width: 800px;
  margin: 0 auto;
  padding: 40px 24px 100px;
  position: relative;
  z-index: 2;
}

.back-link {
  display: inline-block;
  font-size: 13px;
  color: #64748b;
  text-decoration: none;
  margin-bottom: 24px;
  transition: color 0.2s;
}
.back-link:hover {
  color: #10b981;
}

.register-hero {
  margin-bottom: 40px;
}
.hero-eyebrow {
  display: inline-block;
  background: rgba(16, 185, 129, 0.1);
  color: #10b981;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.12em;
  padding: 6px 14px;
  border-radius: 50px;
  margin-bottom: 20px;
}
.register-hero h1 {
  font-family: 'Roboto', sans-serif;
  font-size: clamp(28px, 4vw, 36px);
  font-weight: 700;
  line-height: 1.15;
  color: #ffffff;
  margin-bottom: 20px;
}
.register-hero p {
  font-size: 14.5px;
  line-height: 1.7;
  color: #94a3b8;
}

.register-form-box {
  background: #121614;
  border: 1px solid rgba(16, 185, 129, 0.2);
  border-radius: 20px;
  padding: 40px 32px;
}

.form-header {
  font-family: 'Roboto', sans-serif;
  font-size: 14px;
  font-weight: 700;
  letter-spacing: 0.1em;
  color: #10b981;
  text-transform: uppercase;
  margin-bottom: 24px;
}

.divider-line {
  height: 1px;
  background: rgba(255, 255, 255, 0.05);
  margin: 32px 0;
}

.register-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.form-group label {
  font-size: 13px;
  font-weight: 600;
  color: #f8fafc;
}
.required {
  color: #ef4444;
}
.form-group .hint {
  font-size: 11.5px;
  color: #64748b;
  margin-top: -4px;
}

.form-input {
  background: #090b0a;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 10px;
  padding: 14px 16px;
  font-size: 14px;
  color: #f8fafc;
  font-family: 'Roboto', sans-serif;
  transition: all 0.2s;
}
.form-input::placeholder {
  color: #64748b;
}
.form-input:focus {
  outline: none;
  border-color: rgba(16, 185, 129, 0.4);
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

.error-msg {
  font-size: 12px;
  color: #ef4444;
  margin-top: 4px;
}

.submit-btn {
  margin-top: 12px;
  background: linear-gradient(135deg, #10b981 0%, #047857 100%);
  color: #ffffff;
  padding: 14px 32px;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 700;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  border: none;
  cursor: pointer;
  transition: all 0.2s;
  box-shadow: 0 4px 14px rgba(16, 185, 129, 0.2);
}
.submit-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 18px rgba(16, 185, 129, 0.3);
}
.submit-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.guest-register-fields {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.form-row-2col {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

@media (max-width: 640px) {
  .form-row-2col {
    grid-template-columns: 1fr;
    gap: 16px;
  }
}

.error-banner {
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.2);
  color: #ef4444;
  padding: 12px 16px;
  border-radius: 10px;
  font-size: 14px;
  margin-bottom: 20px;
}

.password-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  width: 100%;
}

.password-input-wrapper .form-input {
  width: 100%;
  padding-right: 48px;
}

.toggle-password-btn {
  position: absolute;
  right: 16px;
  background: none;
  border: none;
  color: #64748b;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 4px;
  transition: color 0.2s;
  z-index: 10;
}

.toggle-password-btn:hover {
  color: #10b981;
}
</style>
