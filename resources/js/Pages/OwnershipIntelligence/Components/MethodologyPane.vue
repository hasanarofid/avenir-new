<template>
  <div class="panelBody">
    <div class="methodGrid">
      <div class="readCard"><h4>1. Link mapping</h4><p>Investor names are normalized and matched against listed issuer names, so the graph can continue across chains such as BREN → BRPT → PRAJOGO PANGESTU.</p></div>
      <div class="readCard"><h4>2. Ownership change</h4><p>Movement classifies each holder as increase, decrease, new, or exit versus the prior filing. Magnitude is the reported percentage weight of the move.</p></div>
      <div class="readCard"><h4>3. Effective proxy</h4><p class="formula">Effective path = stake_1 × stake_2 × … × stake_n<br/>e.g. Prajogo → BRPT → BREN = Prajogo% in BRPT × BRPT% in BREN.</p></div>
      <div class="readCard"><h4>4. Issuer audit metrics</h4><p class="formula">HHI = Σ(pct/100)² × 10,000<br/>Nakamoto-50 = holders needed to reach 50%<br/>Residual = 100% − disclosed coverage<br/>Gov% = disclosed government / SOE stake</p></div>
      <div class="readCard"><h4>5. Government layer</h4><p>Holders flagged as State-Owned Enterprises, Sovereign Wealth Funds, government pension programs, or direct government entities are separated into a dedicated layer for state-exposure analysis.</p></div>
      <div class="readCard"><h4>6. Data caveats</h4><p>Scripless data comes from SRE in C-BEST; scrip data from eBAE. Local/foreign and domicile may be unavailable for some scrip accounts, and multiple-voting-share percentages reflect ownership, not voting rights.</p></div>
    </div>
  </div>
</template>
<script setup>
</script>
