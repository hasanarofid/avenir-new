<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
  <footer>
    <img src="/images/logo.png" alt="Avenir Logo" style="height: 22px; filter: brightness(0) invert(1); opacity: 0.85; margin: 0 auto 12px; display: block;" />
    <ul class="ft-links" style="display: flex; gap: 20px; list-style: none; justify-content: center; margin-bottom: 10px;">
      <li><Link href="/tentang" style="color: rgba(255,255,255,0.5); text-decoration: none; font-size: 12px;">Tentang Kami</Link></li>
      <li><Link href="/langganan" style="color: rgba(255,255,255,0.5); text-decoration: none; font-size: 12px;">Langganan</Link></li>
    </ul>
    <div class="ft-copy" style="font-size: 11px; color: rgba(255,255,255,0.45); font-family: monospace; text-align: center;">
      &copy; 2026 Avenir Research.
    </div>
  </footer>
</template>
