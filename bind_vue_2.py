import re

with open('resources/js/Pages/DeskBrief/Index.vue', 'r') as f:
    content = f.read()

# Replace Cross-Asset 3 (IHSG, LQ45, IDX30)
ca_static = """<div class="idx3">
        <div class="idxc"><div class="k">IHSG</div><div class="v">5,896</div><div class="c neg">−1.72%</div></div>
        <div class="idxc"><div class="k">LQ45</div><div class="v">583.7</div><div class="c neg">−0.68%</div></div>
        <div class="idxc"><div class="k">IDX30</div><div class="v">331.3</div><div class="c neg">−0.53%</div></div>
      </div>"""
ca_vue = """<div class="idx3">
        <div class="idxc" v-for="s in indexSnapshots" :key="s.symbol">
          <div class="k">{{ s.symbol }}</div>
          <div class="v">{{ s.price }}</div>
          <div class="c" :class="s.change > 0 ? 'pos' : (s.change < 0 ? 'neg' : 'amb')">{{ s.change > 0 ? '+' : '' }}{{ s.change }}%</div>
        </div>
      </div>"""
content = content.replace(ca_static, ca_vue)

# Replace Other Assets
oa_static = """<div class="oa">
        <div><div class="k">USD/IDR</div><div class="v">16,180</div><div class="c neg">+0.3%</div></div>
        <div><div class="k">10Y IND</div><div class="v">6.85%</div><div class="c neg">+3bps</div></div>
        <div><div class="k">Brent</div><div class="v">$78.4</div><div class="c neg">−0.6%</div></div>
        <div><div class="k">Gold</div><div class="v">$2,410</div><div class="c pos">+0.4%</div></div>
      </div>"""
oa_vue = """<div class="oa">
        <div v-for="s in otherSnapshots" :key="s.symbol">
          <div class="k">{{ s.symbol }}</div>
          <div class="v">{{ s.price }}</div>
          <div class="c" :class="s.change > 0 ? 'pos' : (s.change < 0 ? 'neg' : 'amb')">{{ s.change > 0 ? '+' : '' }}{{ s.change }}%</div>
        </div>
      </div>"""
content = content.replace(oa_static, oa_vue)

# Replace Top Movers (Gainers and Losers)
movers_static = """<div class="movers">
      <div>
        <div class="movhd"><span class="pos">▲ TOP GAINERS</span></div>
        <div class="mvr"><span class="rk">1</span><span class="ff ff-ok">▲</span><span class="tk">BHAT</span><span class="nm">PT Bhakti Multi Artha Tbk</span><span class="pr">2,680</span><span class="ch pos">+20.23%</span></div>
        <div class="mvr"><span class="rk">2</span><span class="ff ff-warn">▶</span><span class="tk">SURE</span><span class="nm">PT Super Energy Tbk</span><span class="pr">3,330</span><span class="ch pos">+9.84%</span></div>
        <div class="mvr"><span class="rk">3</span><span class="ff ff-ok">▲</span><span class="tk">ARKO</span><span class="nm">PT Arkora Hydro Tbk</span><span class="pr">5,200</span><span class="ch pos">+7.88%</span></div>
        <div class="mvr"><span class="rk">4</span><span class="ff ff-warn">▲</span><span class="tk">SAME</span><span class="nm">Sarana Meditama Metropol...</span><span class="pr">400</span><span class="ch pos">+7.53%</span></div>
        <div class="mvr"><span class="rk">5</span><span class="ff ff-warn">▶</span><span class="tk">BINA</span><span class="nm">PT Bank Ina Perdana Tbk</span><span class="pr">3,780</span><span class="ch pos">+6.48%</span></div>
      </div>
      <div>
        <div class="movhd"><span class="neg">▼ TOP LOSERS</span></div>
        <div class="mvr"><span class="rk">1</span><span class="ff ff-warn">▲</span><span class="tk">YUPI</span><span class="nm">PT Yupi Indo Jelly Gum Tbk</span><span class="pr">1,395</span><span class="ch neg">−14.68%</span></div>
        <div class="mvr"><span class="rk">2</span><span class="ff ff-warn">▶</span><span class="tk">EMAS</span><span class="nm">PT Merdeka Gold Resources...</span><span class="pr">6,125</span><span class="ch neg">−13.12%</span></div>
        <div class="mvr"><span class="rk">3</span><span class="ff ff-ok">▼</span><span class="tk">ENRG</span><span class="nm">Energi Mega Persada Tbk</span><span class="pr">1,040</span><span class="ch neg">−10.73%</span></div>
        <div class="mvr"><span class="rk">4</span><span class="ff ff-warn">▲</span><span class="tk">MGLV</span><span class="nm">PT Panca Anugrah Wisesa T...</span><span class="pr">7,750</span><span class="ch neg">−9.88%</span></div>
        <div class="mvr"><span class="rk">5</span><span class="ff ff-warn">▼</span><span class="tk">CUAN</span><span class="nm">PT Petrindo Jaya Kreasi Tbk</span><span class="pr">565</span><span class="ch neg">−9.60%</span></div>
      </div>
    </div>"""

movers_vue = """<div class="movers">
      <div>
        <div class="movhd"><span class="pos">▲ TOP GAINERS</span></div>
        <div class="mvr" v-for="(item, i) in topMovers.gainers" :key="item.symbol">
          <span class="rk">{{ i + 1 }}</span>
          <span class="ff ff-ok">▲</span>
          <span class="tk">{{ item.symbol }}</span>
          <span class="nm">{{ item.name }}</span>
          <span class="pr">{{ item.last_close || '-' }}</span>
          <span class="ch pos">+{{ item.price_pct }}%</span>
        </div>
      </div>
      <div>
        <div class="movhd"><span class="neg">▼ TOP LOSERS</span></div>
        <div class="mvr" v-for="(item, i) in topMovers.losers" :key="item.symbol">
          <span class="rk">{{ i + 1 }}</span>
          <span class="ff ff-warn">▼</span>
          <span class="tk">{{ item.symbol }}</span>
          <span class="nm">{{ item.name }}</span>
          <span class="pr">{{ item.last_close || '-' }}</span>
          <span class="ch neg">{{ item.price_pct }}%</span>
        </div>
      </div>
    </div>"""
content = content.replace(movers_static, movers_vue)

with open('resources/js/Pages/DeskBrief/Index.vue', 'w') as f:
    f.write(content)
