<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import { authStore } from '@/Stores/authStore';
import { ref, computed } from 'vue';
import { Eye, EyeOff } from 'lucide-vue-next';

const page = usePage();

const loginForm = useForm({
    email: '',
    password: '',
});

const registerForm = useForm({
    fname: '',
    lname: '',
    email: '',
    password: '',
    password_confirmation: '',
    profile: 'Investor Individu',
});

const loginError = ref('');
const registerError = ref('');
const showPassword = ref(false);
const showRegisterPassword = ref(false);
const showRegisterPasswordConfirm = ref(false);

const submitLogin = () => {
    loginError.value = '';
    loginForm.post('/login', {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            loginForm.reset('password');
            authStore.close();
        },
        onError: (errors) => {
            console.error('Registration Errors:', errors);
            if (errors.email) {
                loginError.value = errors.email;
            } else if (errors.message) {
                loginError.value = errors.message;
            } else {
                loginError.value = 'Email atau password salah.';
            }
        }
    });
};

const submitRegister = () => {
    registerError.value = '';
    registerForm.post('/register', {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            registerForm.reset('password');
            authStore.close();
        },
        onError: (errors) => {
            console.error('Registration Errors:', errors);
            if (errors.email) {
                registerError.value = errors.email;
            } else if (errors.fname || errors.lname || errors.name) {
                registerError.value = errors.name || 'Nama depan dan belakang harus diisi.';
            } else if (errors.password) {
                registerError.value = errors.password;
            } else if (errors.error) {
                registerError.value = errors.error;
            } else if (errors.message) {
                registerError.value = errors.message;
            } else {
                registerError.value = 'Terjadi kesalahan saat pendaftaran.';
            }
        }
    });
};
</script>

<template>
  <div 
    v-if="authStore.modalOpen" 
    class="auth-overlay" 
    @click.self="authStore.close()"
  >
    <div class="auth-box">
      <button class="auth-close" @click="authStore.close()">✕</button>

      <!-- LOGIN TAB -->
      <div v-if="authStore.activeTab === 'login'" class="auth-view active">
        <h3>Selamat Datang</h3>
        <p class="auth-sub">Masuk untuk mengakses riset yang sudah Anda beli</p>
        
        <div v-if="loginError" class="auth-err">{{ loginError }}</div>
        
        <form @submit.prevent="submitLogin">
          <div class="auth-fg">
            <label>EMAIL</label>
            <input 
              v-model="loginForm.email" 
              type="email" 
              placeholder="email@anda.com" 
              required
            />
          </div>
          <div class="auth-fg">
            <label>PASSWORD</label>
            <div class="password-input-wrapper">
              <input 
                v-model="loginForm.password" 
                :type="showPassword ? 'text' : 'password'" 
                placeholder="••••••••" 
                required
              />
              <button type="button" class="password-toggle" @click="showPassword = !showPassword">
                <Eye v-if="!showPassword" class="w-4 h-4" />
                <EyeOff v-else class="w-4 h-4" />
              </button>
            </div>
          </div>
          
          <button 
            type="submit" 
            class="auth-submit grn"
            :disabled="loginForm.processing"
          >
            {{ loginForm.processing ? 'Memproses...' : 'Masuk →' }}
          </button>
        </form>

        <div class="auth-sep">Belum punya akun?</div>
        <button 
          class="auth-submit-btn" 
          @click="authStore.activeTab = 'register'"
        >
          Daftar Gratis
        </button>
      </div>

      <!-- REGISTER TAB -->
      <div v-else-if="authStore.activeTab === 'register'" class="auth-view active">
        <h3>Buat Akun</h3>
        <p class="auth-sub">Akun baru otomatis mendapat akses penuh selama 7 hari — tanpa kartu kredit, tanpa komitmen.</p>
        
        <div class="auth-trial-banner">
          <div class="auth-trial-icon">🎁</div>
          <div class="auth-trial-text">
            <strong>7 Hari Akses Gratis</strong><br />
            <span style="font-size:11px;color:#4b5563;">Mulai aktif segera setelah Anda mendaftar.</span>
          </div>
        </div>

        <div v-if="registerError" class="auth-err">{{ registerError }}</div>

        <form @submit.prevent="submitRegister">
          <div class="auth-2col">
            <div class="auth-fg">
              <label>NAMA DEPAN</label>
              <input 
                v-model="registerForm.fname" 
                type="text" 
                placeholder="Budi" 
                required
              />
            </div>
            <div class="auth-fg">
              <label>NAMA BELAKANG</label>
              <input 
                v-model="registerForm.lname" 
                type="text" 
                placeholder="Santoso" 
                required
              />
            </div>
          </div>
          <div class="auth-fg">
            <label>EMAIL</label>
            <input 
              v-model="registerForm.email" 
              type="email" 
              placeholder="email@anda.com" 
              required
            />
          </div>
          <div class="auth-fg">
            <label>PASSWORD</label>
            <div class="password-input-wrapper">
              <input 
                v-model="registerForm.password" 
                :type="showRegisterPassword ? 'text' : 'password'" 
                placeholder="Min. 8 karakter" 
                required
              />
              <button type="button" class="password-toggle" @click="showRegisterPassword = !showRegisterPassword">
                <Eye v-if="!showRegisterPassword" class="w-4 h-4" />
                <EyeOff v-else class="w-4 h-4" />
              </button>
            </div>
          </div>
          <div class="auth-fg">
            <label>KONFIRMASI PASSWORD</label>
            <div class="password-input-wrapper">
              <input 
                v-model="registerForm.password_confirmation" 
                :type="showRegisterPasswordConfirm ? 'text' : 'password'" 
                placeholder="Ulangi password" 
                required
              />
              <button type="button" class="password-toggle" @click="showRegisterPasswordConfirm = !showRegisterPasswordConfirm">
                <Eye v-if="!showRegisterPasswordConfirm" class="w-4 h-4" />
                <EyeOff v-else class="w-4 h-4" />
              </button>
            </div>
          </div>
          <div class="auth-fg">
            <label>PROFIL INVESTOR</label>
            <select v-model="registerForm.profile">
              <option value="Investor Individu">Investor Individu</option>
              <option value="Trader Aktif">Trader Aktif</option>
              <option value="Fund Manager">Fund Manager</option>
              <option value="Institusi / Perusahaan">Institusi / Perusahaan</option>
            </select>
          </div>
          
          <button 
            type="submit" 
            class="auth-submit grn"
            :disabled="registerForm.processing"
          >
            {{ registerForm.processing ? 'Mendaftar...' : 'Daftar Sekarang →' }}
          </button>
        </form>

        <div class="auth-toggle">
          Sudah punya akun? <a @click="authStore.activeTab = 'login'">Masuk</a>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.auth-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.85);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100000;
  padding: 16px;
  backdrop-filter: blur(8px);
  font-family: 'Roboto', sans-serif;
}

.auth-box {
  background: #121614;
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 24px;
  max-width: 440px;
  width: 100%;
  max-height: 92vh;
  overflow-y: scroll;
  -webkit-overflow-scrolling: touch;
  padding: 40px 32px;
  position: relative;
  box-shadow: 0 30px 60px rgba(0, 0, 0, 0.5);
}

.auth-close {
  position: absolute;
  top: 16px;
  right: 16px;
  background: transparent;
  border: none;
  font-size: 20px;
  cursor: pointer;
  color: #64748b;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.auth-close:hover {
  background: rgba(255, 255, 255, 0.05);
  color: #fff;
}

.auth-view h3 {
  font-family: 'Roboto', sans-serif;
  font-size: 28px;
  font-weight: 800;
  margin: 0 0 8px;
  color: #ffffff;
  letter-spacing: -0.02em;
}

.auth-sub {
  font-size: 14px;
  color: #94a3b8;
  margin-bottom: 24px;
  line-height: 1.6;
}

.auth-fg {
  margin-bottom: 16px;
}

.auth-fg label {
  display: block;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: .08em;
  color: #64748b;
  text-transform: uppercase;
  margin-bottom: 8px;
}

.auth-fg input,
.auth-fg select {
  width: 100%;
  padding: 12px 16px;
  background: #090b0a;
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  font-size: 14px;
  color: #fff;
  font-family: inherit;
  box-sizing: border-box;
  transition: all 0.2s;
}

.auth-fg input:focus,
.auth-fg select:focus {
  outline: none;
  border-color: #059669;
  background: #111413;
  box-shadow: 0 0 0 4px rgba(5, 150, 105, 0.15);
}

.password-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.password-toggle {
  position: absolute;
  right: 12px;
  background: transparent;
  border: none;
  color: #64748b;
  cursor: pointer;
  padding: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: color 0.2s;
}

.password-toggle:hover {
  color: #10b981;
}

.auth-fg input::placeholder {
  color: #475569;
}

.auth-2col {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.auth-submit {
  width: 100%;
  padding: 14px;
  border: none;
  border-radius: 12px;
  font-size: 15px;
  font-weight: 700;
  cursor: pointer;
  font-family: inherit;
  transition: all .2s;
  margin-top: 8px;
}

.auth-submit.grn {
  background: #059669;
  color: #fff;
  box-shadow: 0 4px 12px rgba(5, 150, 105, 0.2);
}

.auth-submit.grn:hover {
  background: #10b981;
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(16, 185, 129, 0.3);
}

.auth-sep {
  text-align: center;
  font-size: 12px;
  color: #475569;
  margin: 24px 0 16px;
  position: relative;
}

.auth-sep::before,
.auth-sep::after {
  content: '';
  display: inline-block;
  width: 25%;
  height: 1px;
  background: rgba(255, 255, 255, 0.05);
  vertical-align: middle;
  margin: 0 12px;
}

.auth-submit-btn {
  background: transparent;
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: #f8fafc;
  font-weight: 700;
  padding: 12px;
  border-radius: 12px;
  width: 100%;
  cursor: pointer;
  font-family: inherit;
  font-size: 14px;
  transition: all 0.2s;
}

.auth-submit-btn:hover {
  background: rgba(255, 255, 255, 0.03);
  border-color: rgba(255, 255, 255, 0.2);
  color: #fff;
}

.auth-toggle {
  text-align: center;
  font-size: 14px;
  color: #94a3b8;
  margin-top: 20px;
}

.auth-toggle a {
  color: #10b981;
  font-weight: 700;
  cursor: pointer;
  text-decoration: none;
  margin-left: 4px;
}

.auth-toggle a:hover {
  text-decoration: underline;
}

.auth-err {
  padding: 12px 16px;
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.2);
  color: #f87171;
  border-radius: 12px;
  font-size: 13px;
  line-height: 1.5;
  margin-bottom: 20px;
}

.auth-trial-banner {
  display: flex;
  gap: 12px;
  align-items: center;
  background: rgba(16, 185, 129, 0.05);
  border: 1px solid rgba(16, 185, 129, 0.15);
  border-radius: 12px;
  padding: 12px 16px;
  margin-bottom: 24px;
}

.auth-trial-icon {
  font-size: 24px;
}

.auth-trial-text {
  font-size: 14px;
  line-height: 1.5;
  color: #e2e8f0;
}

.auth-trial-text strong {
  color: #10b981;
  font-weight: 700;
}
</style>
