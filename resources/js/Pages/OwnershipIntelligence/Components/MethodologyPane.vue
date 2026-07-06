<script setup>
</script>

<template>
  <div class="panelBody">
    <div class="methodGrid">
      <div class="readCard">
        <h4>1. Link mapping</h4>
        <p>Investor names are normalized and matched against listed issuer names. Example: BARITO PACIFIC TBK maps to BRPT, so the graph can continue from BREN -> BRPT -> PRAJOGO PANGESTU and BRPT -> TPIA -> CDIA.</p>
      </div>
      <div class="readCard">
        <h4>2. Ownership change</h4>
        <p>Change = latest total holding shares minus previous comparable report. Positive is BUY / increase; negative is SELL / decrease. NEW and EXIT mean the holder appeared or disappeared from the disclosed-holder file.</p>
      </div>
      <div class="readCard">
        <h4>3. Effective proxy</h4>
        <p class="formula">Effective ownership path = stake_1 × stake_2 × ... × stake_n<br/>Example: Prajogo -> BRPT -> BREN = Prajogo% in BRPT × BRPT% in BREN.</p>
      </div>
      <div class="readCard">
        <h4>4. Issuer audit metrics</h4>
        <p class="formula">HHI = sum((holder_pct / 100)^2) × 10,000<br/>Nakamoto-50 = minimum holders needed to reach 50%<br/>Residual proxy = 100% - reported coverage<br/>Float proxy = residual + small financial/institutional holders + small individual holders</p>
      </div>
      <div class="readCard">
        <h4>5. Confidence</h4>
        <p>Confidence penalizes high residual, high scrip ratio, unclassified local/foreign exposure, and percentage integrity issues. It is an analytical confidence score, not an official audit opinion.</p>
      </div>
      <div class="readCard">
        <h4>6. Data caveats</h4>
        <p>Reports state that scripless data comes from SRE in C-BEST, while scrip data comes from eBAE. Local/foreign classifications may be unavailable for certain scrip accounts, and percentages for multiple voting shares are based on share ownership, not voting rights.</p>
      </div>
    </div>
  </div>
</template>
