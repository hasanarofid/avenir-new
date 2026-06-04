<script setup>
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';
import AuthModal from '@/Components/AuthModal.vue';
</script>

<template>
  <div>
    <Navbar />
    
    <!-- Page Content -->
    <main>
      <slot />
    </main>
    
    <Footer />

    <!-- Global Auth Modal -->
    <AuthModal />
  </div>
</template>
