<template>
  <section class="selectorPanel" id="selectorPanel">
    <div class="selectorGrid">
      <!-- selectedDisplay: vanilla renderKpis() writes the selected entity name here -->
      <div class="field">
        <label>Selected entity</label>
        <input id="selectedDisplay" readonly style="background:rgba(255,255,255,0.05); cursor:default;" placeholder="—" />
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
