<template>
  <section class="selectorPanel" id="selectorPanel">
    <div class="selectorGrid">
      <!-- selectedDisplay: vanilla renderKpis() writes the selected entity name here -->
      <div class="field">
        <label>Selected entity</label>
        <div id="selectedDisplayContainer" style="display:flex; align-items:center; background:rgba(255,255,255,0.05); padding: 8px 12px; border-radius: 6px; border: 1px solid rgba(255,255,255,0.1); height: 38px; cursor:default;">
          <img id="selectedLogo" src="" style="width: 20px; height: 20px; border-radius: 50%; margin-right: 8px; display: none; object-fit: contain;" />
          <span id="selectedDisplay" style="color: #fff; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; outline: none;" contenteditable="false">—</span>
        </div>
      </div>
      <div class="field">
        <label>Network depth</label>
        <select id="depthSel">
          <option value="1">1-hop</option>
          <option value="2" selected>2-hop</option>
          <option value="3">3-hop</option>
          <option value="4">4-hop</option>
        </select>
      </div>
      <div class="field">
        <label>Minimum stake</label>
        <select id="minPctSel">
          <option value="0">All</option>
          <option value="1" selected>&gt;= 1%</option>
          <option value="3">&gt;= 3%</option>
          <option value="5">&gt;= 5%</option>
          <option value="10">&gt;= 10%</option>
        </select>
      </div>
      <div class="field">
        <label>Direction</label>
        <select id="dirSel">
          <option value="both">Owners + holdings</option>
          <option value="up">Owners only</option>
          <option value="down">Holdings only</option>
        </select>
      </div>
    </div>
    <!-- Quick chips — vanilla initControls fills this container with data-key chips -->
    <div class="chips" id="quickChips"></div>
  </section>
</template>

<script setup>
// This component provides the HTML structure for the vanilla PRD logic.
// Content is managed by initVanillaLogic in vanillaPrdLogic.js.
</script>
