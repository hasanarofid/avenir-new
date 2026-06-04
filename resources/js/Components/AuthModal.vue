<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import { authStore } from '@/Stores/authStore';
import { ref, computed } from 'vue';

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
    profile: 'Investor Individu',
});

const loginError = ref('');
const registerError = ref('');

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
            if (errors.email) {
                registerError.value = errors.email;
            } else if (errors.fname || errors.lname) {
                registerError.value = 'Nama depan dan belakang harus diisi.';
            } else if (errors.password) {
                registerError.value = errors.password;
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
            <input 
              v-model="loginForm.password" 
              type="password" 
              placeholder="••••••••" 
              required
            />
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
            <input 
              v-model="registerForm.password" 
              type="password" 
              placeholder="Min. 8 karakter" 
              required
            />
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
@import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Inter:wght@400;500;600;700&display=swap');

.auth-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.55);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100000;
  padding: 16px;
  backdrop-filter: blur(4px);
  font-family: 'Inter', sans-serif;
}

.auth-box {
  background: #fff;
  border-radius: 16px;
  max-width: 440px;
  width: 100%;
  max-height: 92vh;
  overflow-y: auto;
  padding: 30px 26px;
  position: relative;
  box-shadow: 0 30px 60px rgba(0, 0, 0, 0.25);
}

.auth-close {
  position: absolute;
  top: 12px;
  right: 12px;
  background: transparent;
  border: none;
  font-size: 20px;
  cursor: pointer;
  color: #9ca3af;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.auth-close:hover {
  background: #f3f4f6;
  color: #111827;
}

.auth-view h3 {
  font-family: 'Cormorant Garamond', Georgia, serif;
  font-size: 24px;
  font-weight: 600;
  margin: 0 0 6px;
  color: #111827;
}

.auth-sub {
  font-size: 13px;
  color: #6b7280;
  margin-bottom: 18px;
  line-height: 1.6;
}

.auth-fg {
  margin-bottom: 12px;
}

.auth-fg label {
  display: block;
  font-size: 10.5px;
  font-weight: 700;
  letter-spacing: .1em;
  color: #6b7280;
  text-transform: uppercase;
  margin-bottom: 6px;
}

.auth-fg input,
.auth-fg select {
  width: 100%;
  padding: 11px 13px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 13.5px;
  font-family: inherit;
  box-sizing: border-box;
}

.auth-fg input:focus,
.auth-fg select:focus {
  outline: none;
  border-color: #1B6B3A;
  box-shadow: 0 0 0 3px rgba(27, 107, 58, 0.08);
}

.auth-2col {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.auth-submit {
  width: 100%;
  padding: 12px;
  border: none;
  border-radius: 50px;
  font-size: 13px;
  font-weight: 700;
  letter-spacing: .05em;
  text-transform: uppercase;
  cursor: pointer;
  font-family: inherit;
  transition: all .15s;
  margin-top: 4px;
}

.auth-submit.grn {
  background: #1B6B3A;
  color: #fff;
}

.auth-submit.grn:hover {
  background: #155028;
}

.auth-sep {
  text-align: center;
  font-size: 11px;
  color: #9ca3af;
  margin: 18px 0 12px;
  letter-spacing: .04em;
  position: relative;
}

.auth-sep::before,
.auth-sep::after {
  content: '';
  display: inline-block;
  width: 30%;
  height: 1px;
  background: #e5e7eb;
  vertical-align: middle;
  margin: 0 8px;
}

.auth-submit-btn {
  background: transparent;
  border: 1px solid #e5e7eb;
  color: #4b5563;
  font-weight: 600;
  padding: 11px;
  border-radius: 50px;
  width: 100%;
  cursor: pointer;
  font-family: inherit;
  font-size: 13px;
  letter-spacing: .05em;
  text-transform: uppercase;
  transition: all 0.15s;
}

.auth-submit-btn:hover {
  border-color: #1B6B3A;
  color: #1B6B3A;
}

.auth-toggle {
  text-align: center;
  font-size: 12.5px;
  color: #6b7280;
  margin-top: 14px;
}

.auth-toggle a {
  color: #1B6B3A;
  font-weight: 600;
  cursor: pointer;
  text-decoration: underline;
}

.auth-err {
  padding: 10px 12px;
  background: #fef2f2;
  color: #b91c1c;
  border-radius: 8px;
  font-size: 12.5px;
  line-height: 1.5;
  margin-bottom: 14px;
}

.auth-trial-banner {
  display: flex;
  gap: 10px;
  align-items: center;
  background: rgba(27, 107, 58, 0.07);
  border: 1px solid rgba(27, 107, 58, 0.2);
  border-radius: 8px;
  padding: 10px 14px;
  margin: 10px 0 18px;
}

.auth-trial-icon {
  font-size: 22px;
  line-height: 1;
}

.auth-trial-text {
  font-size: 13px;
  line-height: 1.45;
  color: #1f2937;
}

.auth-trial-text strong {
  color: #155028;
  font-size: 13.5px;
}
</style>
