<template>
  <div class="card span3">
    <div class="chd"><div class="t"><b>8.</b>CATALYST CALENDAR</div></div>
    <div class="caltabs">
      <span class="caltab" :class="{ on: activeTab === 'upcoming' }" @click="activeTab = 'upcoming'">Upcoming</span>
      <span class="caltab" :class="{ on: activeTab === 'past' }" @click="activeTab = 'past'">Past Events</span>
    </div>
    <div class="cal2-wrap">
      <table class="cal2">
        <thead><tr><th>Date</th><th>Event</th><th>Imp</th></tr></thead>
        <tbody>
          <tr v-for="(c, idx) in filteredCatalysts" :key="idx">
            <td class="dt">{{ c.date }}</td>
            <td class="ev" v-html="c.event"></td>
            <td><span :class="'lvl-' + (c.impact === 'High' ? 'high' : (c.impact === 'Medium' ? 'med' : 'low'))" style="font-weight:700;font-size:9px">● {{ c.impact }}</span></td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="viewall" @click="showModal = true" style="cursor:pointer">View Full Calendar →</div>
    
    <!-- Modal -->
    <Teleport to="body">
      <div v-if="showModal" class="calmodal show" @click.self="showModal = false">
        <div class="calmodal-box">
          <div class="calmodal-hd">
            <span class="t">Catalyst Calendar</span>
            <button class="nav-btn" @click="prevMonth">‹</button>
            <span class="mo">{{ monthYearLabel }}</span>
            <button class="nav-btn" @click="nextMonth">›</button>
            <button class="x" @click="showModal = false">✕</button>
          </div>
          <div class="calmodal-body" @click="closeBubble" ref="modalBody">
            <div class="calgrid">
              <div class="dow">Sun</div><div class="dow">Mon</div><div class="dow">Tue</div><div class="dow">Wed</div><div class="dow">Thu</div><div class="dow">Fri</div><div class="dow">Sat</div>
              
              <div v-for="empty in emptyDaysStart" :key="'e1'+empty" class="calcell empty"></div>
              
              <div v-for="d in daysInMonth" :key="'d'+d" class="calcell" :class="{'has-ev': getEventsForDay(d).length > 0, 'selected': selectedDay === d, 'today': isToday(d)}" @click.stop="openBubble($event, d)">
                <div class="dnum">{{ d }}</div>
                <div class="caldots" v-if="getEventsForDay(d).length > 0">
                  <div v-for="(ev, i) in getEventsForDay(d).slice(0, 5)" :key="i" class="caldot" :class="ev.impact.toLowerCase() === 'high' ? 'high' : (ev.impact.toLowerCase() === 'medium' ? 'med' : 'low')"></div>
                  <div v-if="getEventsForDay(d).length > 5" style="font-size:8px;color:#888;margin-left:2px">+</div>
                </div>
              </div>
              
              <div v-for="empty in emptyDaysEnd" :key="'e2'+empty" class="calcell empty"></div>
            </div>
            <div class="calmodal-legend">
              <span><i style="background:var(--red)"></i> High impact</span>
              <span><i style="background:var(--amber)"></i> Medium</span>
              <span><i style="background:var(--green)"></i> Low</span>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Bubble -->
      <div v-if="bubbleVisible" class="calbubble show" :style="bubbleStyle" @click.stop>
        <div class="bdate">{{ bubbleDateStr }}</div>
        <div v-for="(ev, i) in bubbleEvents" :key="i" class="brow">
          <div class="btop">
            <span class="bname">{{ ev.event }}</span>
            <span class="bimp" :class="ev.impact.toLowerCase() === 'high' ? 'high' : (ev.impact.toLowerCase() === 'medium' ? 'med' : 'low')">{{ ev.impact }}</span>
          </div>
          <div class="bnote" v-if="ev.note">{{ ev.note }}</div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  catalysts: { type: Array, default: () => [] }
});

const activeTab = ref('upcoming');
const showModal = ref(false);

const filteredCatalysts = computed(() => {
  return props.catalysts.filter(c => activeTab.value === 'upcoming' ? !c.isPast : c.isPast).slice(0, 5);
});

// Modal Logic
const currentDate = new Date();
const viewMonth = ref(currentDate.getMonth());
const viewYear = ref(currentDate.getFullYear());

const monthYearLabel = computed(() => {
  const d = new Date(viewYear.value, viewMonth.value, 1);
  return d.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
});

const daysInMonth = computed(() => new Date(viewYear.value, viewMonth.value + 1, 0).getDate());
const emptyDaysStart = computed(() => new Date(viewYear.value, viewMonth.value, 1).getDay());
const emptyDaysEnd = computed(() => {
  const total = emptyDaysStart.value + daysInMonth.value;
  return total % 7 === 0 ? 0 : 7 - (total % 7);
});

const isToday = (d) => {
  const today = new Date();
  return today.getDate() === d && today.getMonth() === viewMonth.value && today.getFullYear() === viewYear.value;
};

// API proxy ready structure - mapped from events
const allEvents = computed(() => {
  return props.catalysts.map(c => {
    let parsedDate = null;
    try {
        if(c.date.length <= 6) {
           parsedDate = new Date(`${c.date} ${viewYear.value}`);
        } else {
           parsedDate = new Date(c.date);
        }
    } catch(e) {}
    
    return { ...c, parsedObj: parsedDate };
  });
});

const getEventsForDay = (d) => {
  return allEvents.value.filter(ev => {
    if (!ev.parsedObj || isNaN(ev.parsedObj)) return false;
    return ev.parsedObj.getDate() === d && ev.parsedObj.getMonth() === viewMonth.value && ev.parsedObj.getFullYear() === viewYear.value;
  });
};

const prevMonth = () => {
  viewMonth.value--;
  if(viewMonth.value < 0) { viewMonth.value = 11; viewYear.value--; }
  closeBubble();
};
const nextMonth = () => {
  viewMonth.value++;
  if(viewMonth.value > 11) { viewMonth.value = 0; viewYear.value++; }
  closeBubble();
};

// Bubble
const bubbleVisible = ref(false);
const bubbleEvents = ref([]);
const bubbleDateStr = ref('');
const bubbleStyle = ref({});
const selectedDay = ref(null);

const openBubble = (e, d) => {
  const evs = getEventsForDay(d);
  if(!evs.length) return;
  
  selectedDay.value = d;
  bubbleEvents.value = evs;
  bubbleDateStr.value = new Date(viewYear.value, viewMonth.value, d).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long' });
  
  const rect = e.currentTarget.getBoundingClientRect();
  let left = rect.left + window.scrollX + rect.width / 2 - 125;
  let top = rect.top + window.scrollY + rect.height + 8;
  
  if (left < 10) left = 10;
  if (left + 250 > window.innerWidth - 10) left = window.innerWidth - 260;
  
  bubbleStyle.value = {
    left: left + 'px',
    top: top + 'px'
  };
  bubbleVisible.value = true;
};

const closeBubble = () => {
  bubbleVisible.value = false;
  selectedDay.value = null;
};
</script>

<style scoped>
.chd{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px}
.chd .t{font-size:11px;letter-spacing:.1em;text-transform:uppercase;color:var(--ink2);font-weight:700}
.chd .t b{color:#888888;font-weight:700;margin-right:5px}
.chd .meta{font-size:10px;color:var(--muted);display:flex;align-items:center;gap:6px}
.caltabs{display:flex;gap:16px;border-bottom:1px solid var(--line);margin-bottom:6px}
.caltab{font-size:11px;font-weight:600;color:var(--muted);padding-bottom:8px;cursor:pointer}
.caltab.on{color:var(--green);border-bottom:2px solid var(--green)}
.cal2{width:100%;border-collapse:collapse;font-size:11px}
.cal2 th{text-align:left;font-size:8px;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;font-weight:600;padding:6px 5px}
.cal2 th:last-child,.cal2 td:last-child{text-align:right}
.cal2 td{padding:8px 5px;border-top:1px solid var(--line);color:var(--ink2)}
.cal2 .dt{color:var(--muted);white-space:nowrap;font-size:10px}
.cal2 .ev{color:var(--ink);font-weight:500}
.viewall{font-size:11px;color:var(--green);font-weight:600;margin-top:11px}

</style>

<style>
/* ===== calendar modal (grid bulanan) - unscoped for Teleport ===== */
.calmodal, .calbubble {
  --bg:#090B0A; --bg2:#0E0E0E; --card:#151515; --card2:#1A1A1A; --inset:#101010;
  --line:#242424; --line2:#2E2E2E; --line3:#383838;
  --ink:#EAEAE7; --ink2:#B6B6B0; --muted:#7C7C76; --faint:#565651;
  --green:#46C46E; --green2:#2E9E55; --greend:#1C6B3C; --greensoft:rgba(70,196,110,.12);
  --red:#E2705C; --redsoft:rgba(226,112,92,.12); --reddim:#9A4438;
  --amber:#D99B3E; --gold:#C9A227; --blue:#5FA0D8;
  --sans:Arial,Arimo,'Liberation Sans','DejaVu Sans',sans-serif;
  --mono:'Arimo',Arial,'Liberation Mono','DejaVu Sans Mono',monospace;
}

.calmodal{position:fixed;inset:0;z-index:300;display:none;align-items:center;justify-content:center;background:rgba(0,0,0,.72);backdrop-filter:blur(3px)}
.calmodal.show{display:flex}
.calmodal-box{background:var(--card);border:1px solid var(--line2);border-radius:14px;width:min(920px,94vw);max-height:90vh;overflow:hidden;display:flex;flex-direction:column;box-shadow:0 24px 70px rgba(0,0,0,.6)}
.calmodal-hd{display:flex;align-items:center;gap:14px;padding:16px 20px;border-bottom:1px solid var(--line)}
.calmodal-hd .t{font-size:14px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;color:var(--ink)}
.calmodal-hd .mo{font-size:13px;color:var(--ink2);font-weight:600;font-family:var(--mono)}
.calmodal-hd .nav-btn{background:#161616;border:1px solid var(--line2);color:var(--ink2);border-radius:7px;width:30px;height:30px;cursor:pointer;font-size:15px;display:flex;align-items:center;justify-content:center}
.calmodal-hd .nav-btn:hover{background:#1E1E1E;color:var(--green)}
.calmodal-hd .x{margin-left:auto;background:#161616;border:1px solid var(--line2);color:var(--muted);border-radius:7px;width:30px;height:30px;cursor:pointer;font-size:16px;display:flex;align-items:center;justify-content:center}
.calmodal-hd .x:hover{color:var(--red);border-color:var(--reddim)}
.calmodal-body{padding:16px 20px 20px;overflow-y:auto}
.calgrid{display:grid;grid-template-columns:repeat(7,1fr);gap:5px}
.calgrid .dow{font-size:9px;text-transform:uppercase;letter-spacing:.08em;color:var(--muted);font-weight:700;text-align:center;padding:4px 0 8px}
.calcell{min-height:64px;background:var(--inset);border:1px solid var(--line);border-radius:7px;padding:5px 6px;display:flex;flex-direction:column;gap:4px;position:relative;box-sizing:border-box}
.calcell.empty{background:transparent;border:none}
.calcell.has-ev{cursor:pointer}
.calcell.has-ev:hover{border-color:var(--line3);background:#131313}
.calcell.today{border-color:var(--green2);box-shadow:inset 0 0 0 1px var(--green2)}
.calcell .dnum{font-size:10px;font-weight:700;color:var(--ink2);font-family:var(--mono)}
.calcell.today .dnum{color:var(--green)}
.caldots{display:flex;flex-wrap:wrap;gap:3px;margin-top:1px}
.caldot{width:7px;height:7px;border-radius:50%}
.caldot.high{background:var(--red)}
.caldot.med{background:var(--amber)}
.caldot.low{background:var(--green)}
.calcell.selected{border-color:var(--green);box-shadow:0 0 0 1px var(--green)}
.calmodal-legend{display:flex;gap:16px;margin-top:14px;font-size:10px;color:var(--muted)}
.calmodal-legend span{display:flex;align-items:center;gap:5px}
.calmodal-legend i{width:9px;height:9px;border-radius:50%;display:inline-block}

/* bubble popover detail event */
.calbubble{position:absolute;z-index:320;opacity:0;pointer-events:none;transform:translateY(6px);transition:opacity .12s,transform .12s;background:#1A1A1A;border:1px solid var(--line3);border-radius:11px;padding:11px 13px;width:250px;box-shadow:0 16px 44px rgba(0,0,0,.65);box-sizing:border-box}
.calbubble.show{opacity:1;pointer-events:auto;transform:translateY(0)}
.calbubble .bdate{font-size:10px;font-family:var(--mono);color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px}
.calbubble .brow{padding:7px 0;border-top:1px solid var(--line)}
.calbubble .brow:first-of-type{border-top:none;padding-top:2px}
.calbubble .btop{display:flex;align-items:baseline;justify-content:space-between;gap:10px}
.calbubble .bname{font-size:12px;font-weight:600;color:var(--ink);line-height:1.3}
.calbubble .bimp{font-size:8.5px;font-weight:700;white-space:nowrap;padding-top:1px}
.calbubble .bimp.high{color:var(--red)}
.calbubble .bimp.med{color:var(--amber)}
.calbubble .bimp.low{color:var(--green)}
.calbubble .bnote{font-size:9.5px;color:var(--muted);margin-top:3px;line-height:1.4}
.calbubble .bnote b{color:#7FB98C}
</style>
