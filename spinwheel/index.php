<?php require_once 'includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>🎰 Spin to Win – Cash Prizes</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=DM+Mono:wght@500&display=swap" rel="stylesheet">
<style>
:root{--bg:#07070f;--surface:#0f0f1a;--card:#13131f;--border:#1a1a2e;--accent:#8b5cf6;--accent2:#c084fc;--gold:#fbbf24;--gold2:#f59e0b;--green:#34d399;--red:#f87171;--text:#f1f5f9;--muted:#64748b;--glow:rgba(139,92,246,.4)}
*{box-sizing:border-box;margin:0;padding:0}
body{background:var(--bg);color:var(--text);font-family:'Syne',sans-serif;min-height:100vh;overflow-x:hidden}
body::before{content:'';position:fixed;inset:0;background-image:linear-gradient(rgba(139,92,246,.03) 1px,transparent 1px),linear-gradient(90deg,rgba(139,92,246,.03) 1px,transparent 1px);background-size:40px 40px;pointer-events:none;z-index:0}
header{position:relative;z-index:10;display:flex;align-items:center;justify-content:space-between;padding:18px 32px;background:rgba(15,15,26,.9);border-bottom:1px solid var(--border);backdrop-filter:blur(12px)}
.logo{font-size:1.4rem;font-weight:800;background:linear-gradient(135deg,var(--gold),var(--accent2));-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.player-info{display:flex;align-items:center;gap:12px;font-size:.85rem}
.score-badge{background:linear-gradient(135deg,rgba(251,191,36,.15),rgba(251,191,36,.05));border:1px solid rgba(251,191,36,.3);padding:6px 16px;border-radius:20px;font-weight:700;color:var(--gold)}
.spins-badge{background:rgba(139,92,246,.15);border:1px solid rgba(139,92,246,.3);padding:6px 16px;border-radius:20px;font-weight:700;color:var(--accent2)}
.main{position:relative;z-index:1;display:grid;grid-template-columns:1fr 380px;gap:24px;max-width:1100px;margin:40px auto;padding:0 24px}
.wheel-section{display:flex;flex-direction:column;align-items:center;gap:24px}
.wheel-container{position:relative;width:420px;height:420px}
.wheel-pointer{position:absolute;top:-16px;left:50%;transform:translateX(-50%);font-size:2.5rem;filter:drop-shadow(0 0 10px var(--gold));z-index:10}
#wheelCanvas{border-radius:50%;box-shadow:0 0 60px var(--glow),0 0 120px rgba(139,92,246,.15)}
.spin-btn{background:linear-gradient(135deg,var(--gold2),var(--gold));color:#1a0a00;font-family:'Syne',sans-serif;font-size:1.2rem;font-weight:800;border:none;padding:16px 48px;border-radius:50px;cursor:pointer;transition:all .3s;box-shadow:0 8px 32px rgba(245,158,11,.4);letter-spacing:.05em;text-transform:uppercase}
.spin-btn:hover:not(:disabled){transform:translateY(-2px);box-shadow:0 12px 40px rgba(245,158,11,.6)}
.spin-btn:disabled{background:var(--border);color:var(--muted);box-shadow:none;cursor:not-allowed}
.prize-display{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:20px 32px;text-align:center;min-width:280px;transition:all .4s}
.prize-display .prize-label{font-size:2.2rem;font-weight:800}
.prize-display .prize-sub{color:var(--muted);font-size:.85rem;margin-top:4px}
.prize-display.win{border-color:var(--gold);box-shadow:0 0 24px rgba(251,191,36,.2)}
.prize-display.win .prize-label{color:var(--gold)}
.prize-display.lose .prize-label{color:var(--muted)}
.sidebar{display:flex;flex-direction:column;gap:16px}
.panel{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:24px}
.panel h2{font-size:.9rem;font-weight:800;margin-bottom:18px;display:flex;align-items:center;gap:8px;text-transform:uppercase;letter-spacing:.08em;color:var(--muted)}
.form-group{margin-bottom:14px}
label{display:block;font-size:.8rem;color:var(--muted);margin-bottom:6px;font-weight:700}
input[type=text],select{width:100%;background:var(--bg);border:1px solid var(--border);color:var(--text);padding:10px 14px;border-radius:8px;font-family:'DM Mono',monospace;font-size:.9rem;outline:none;transition:border-color .2s;appearance:none;-webkit-appearance:none}
input:focus,select:focus{border-color:var(--accent)}
select{background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 12px center;padding-right:36px}
.btn-register{width:100%;background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;border:none;padding:12px;border-radius:10px;font-family:'Syne',sans-serif;font-weight:800;font-size:.95rem;cursor:pointer;transition:all .2s;box-shadow:0 4px 20px rgba(139,92,246,.35)}
.btn-register:hover{transform:translateY(-1px);box-shadow:0 6px 28px rgba(139,92,246,.5)}
.lb-row{display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border);font-size:.88rem}
.lb-row:last-child{border-bottom:none}
.lb-rank{font-weight:800;width:32px;text-align:center}
.lb-name{flex:1;margin-left:8px}
.lb-score{font-weight:700;color:var(--gold);font-family:'DM Mono',monospace}

/* Payout Modal Overlay */
.modal-bg{display:none;position:fixed;inset:0;background:rgba(0,0,0,.75);z-index:200;align-items:center;justify-content:center;backdrop-filter:blur(4px)}
.modal-bg.open{display:flex}
.modal-box{background:var(--surface);border:1px solid var(--border);border-radius:20px;padding:32px;width:460px;max-width:95vw;position:relative;max-height:90vh;overflow-y:auto}
.modal-box h3{font-size:1.2rem;font-weight:800;margin-bottom:6px}
.modal-box .modal-sub{color:var(--muted);font-size:.85rem;margin-bottom:24px}
.modal-close{position:absolute;top:16px;right:16px;background:var(--border);border:none;color:var(--muted);width:32px;height:32px;border-radius:50%;cursor:pointer;font-size:1.1rem;display:flex;align-items:center;justify-content:center}
.modal-close:hover{color:var(--text)}

/* Payment method grid */
.method-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:20px}
.method-btn{background:var(--bg);border:1.5px solid var(--border);border-radius:10px;padding:10px 8px;cursor:pointer;text-align:center;transition:all .2s;font-family:'Syne',sans-serif}
.method-btn:hover{border-color:var(--accent);background:rgba(139,92,246,.08)}
.method-btn.selected{border-color:var(--gold);background:rgba(251,191,36,.08)}
.method-btn .m-icon{font-size:1.4rem;display:block;margin-bottom:4px}
.method-btn .m-name{font-size:.72rem;font-weight:700;color:var(--muted)}
.method-btn.selected .m-name{color:var(--gold)}
.method-tag{display:inline-block;font-size:.68rem;font-weight:700;padding:2px 8px;border-radius:10px;margin-top:3px}
.tag-ewallet{background:rgba(139,92,246,.2);color:var(--accent2)}
.tag-bank{background:rgba(52,211,153,.15);color:var(--green)}

.payout-fields{background:var(--bg);border:1px solid var(--border);border-radius:12px;padding:16px;margin-bottom:18px}
.amount-display{background:linear-gradient(135deg,rgba(251,191,36,.1),rgba(251,191,36,.05));border:1px solid rgba(251,191,36,.25);border-radius:10px;padding:12px 16px;text-align:center;margin-bottom:18px}
.amount-display .amt-label{color:var(--muted);font-size:.8rem}
.amount-display .amt-val{font-size:2rem;font-weight:800;color:var(--gold)}

.btn-submit-payout{width:100%;background:linear-gradient(135deg,#065f46,#059669);color:#fff;border:none;padding:14px;border-radius:12px;font-family:'Syne',sans-serif;font-weight:800;font-size:1rem;cursor:pointer;transition:all .2s;box-shadow:0 4px 20px rgba(5,150,105,.3)}
.btn-submit-payout:hover:not(:disabled){transform:translateY(-1px);box-shadow:0 6px 28px rgba(5,150,105,.5)}
.btn-submit-payout:disabled{background:var(--border);color:var(--muted);box-shadow:none;cursor:not-allowed}

.btn-open-payout{width:100%;background:rgba(5,150,105,.15);border:1.5px solid rgba(52,211,153,.3);color:var(--green);font-family:'Syne',sans-serif;font-weight:800;font-size:.95rem;padding:12px;border-radius:10px;cursor:pointer;transition:all .2s}
.btn-open-payout:hover:not(:disabled){background:rgba(5,150,105,.25);border-color:var(--green)}
.btn-open-payout:disabled{background:var(--border);border-color:var(--border);color:var(--muted);cursor:not-allowed}

.toast{position:fixed;top:24px;left:50%;transform:translateX(-50%) translateY(-80px);background:var(--card);border:1px solid var(--border);border-radius:12px;padding:14px 24px;font-size:.9rem;font-weight:700;box-shadow:0 8px 32px rgba(0,0,0,.5);z-index:999;transition:transform .4s cubic-bezier(.34,1.56,.64,1);max-width:90vw;text-align:center}
.toast.show{transform:translateX(-50%) translateY(0)}
.toast.success{border-color:var(--green);color:var(--green)}
.toast.error{border-color:var(--red);color:var(--red)}
.toast.info{border-color:var(--accent);color:var(--accent2)}

@keyframes glow-pulse{0%,100%{box-shadow:0 0 60px var(--glow),0 0 120px rgba(139,92,246,.15)}50%{box-shadow:0 0 80px var(--glow),0 0 160px rgba(139,92,246,.25)}}
#wheelCanvas{animation:glow-pulse 3s ease-in-out infinite}
@keyframes prize-pop{0%{transform:scale(.8);opacity:0}70%{transform:scale(1.05)}100%{transform:scale(1);opacity:1}}
.prize-display.animate{animation:prize-pop .5s cubic-bezier(.34,1.56,.64,1)}
.stars{position:fixed;inset:0;pointer-events:none;z-index:0;overflow:hidden}
.star{position:absolute;width:2px;height:2px;background:#fff;border-radius:50%;animation:twinkle var(--d) var(--delay) ease-in-out infinite}
@keyframes twinkle{0%,100%{opacity:.1}50%{opacity:.8}}
.hidden{display:none!important}
@media(max-width:768px){.main{grid-template-columns:1fr}.wheel-container{width:300px;height:300px}header{padding:14px 20px}.player-info{gap:8px}.method-grid{grid-template-columns:repeat(3,1fr)}}
</style>
</head>
<body>

<div class="stars" id="stars"></div>

<header>
  <div class="logo">🎰 Spin to Win</div>
  <div class="player-info" id="headerInfo">
    <span style="color:var(--muted);font-size:.85rem">Register to play →</span>
  </div>
</header>

<div class="main">
  <!-- Wheel -->
  <div class="wheel-section">
    <div class="wheel-container">
      <div class="wheel-pointer">▼</div>
      <canvas id="wheelCanvas" width="420" height="420"></canvas>
    </div>
    <div class="prize-display" id="prizeDisplay">
      <div class="prize-label">🎡</div>
      <div class="prize-sub">Spin the wheel to win cash prizes!</div>
    </div>
    <button class="spin-btn" id="spinBtn" disabled onclick="doSpin()">SPIN!</button>
  </div>

  <!-- Sidebar -->
  <div class="sidebar">
    <!-- Register -->
    <div class="panel" id="registerPanel">
      <h2>👤 Your Info</h2>
      <div class="form-group">
        <label>Your Name</label>
        <input type="text" id="inputName" placeholder="Juan dela Cruz" maxlength="50">
      </div>
      <button class="btn-register" onclick="doRegister()">🎮 Start Playing</button>
    </div>

    <!-- Payout button -->
    <div class="panel hidden" id="payoutPanel">
      <h2>💸 Cash Out</h2>
      <p style="color:var(--muted);font-size:.82rem;margin-bottom:14px">Minimum ₱<?= MIN_PAYOUT_AMOUNT ?> to cash out. Admin sends via your chosen bank or e-wallet.</p>
      <button class="btn-open-payout" id="openPayoutBtn" onclick="openPayoutModal()" disabled>📲 Request Cash Out</button>
    </div>

    <!-- Leaderboard -->
    <div class="panel">
      <h2>🏆 Leaderboard</h2>
      <div id="leaderboard"><div style="color:var(--muted);font-size:.85rem;text-align:center;padding:20px">Loading…</div></div>
    </div>
  </div>
</div>

<!-- Payout Modal -->
<div class="modal-bg" id="payoutModal">
  <div class="modal-box">
    <button class="modal-close" onclick="closePayoutModal()">✕</button>
    <h3>💸 Request Cash Out</h3>
    <p class="modal-sub">Choose your bank or e-wallet and enter your account details.</p>

    <div class="amount-display">
      <div class="amt-label">Amount to receive</div>
      <div class="amt-val" id="modalAmount">₱0</div>
    </div>

    <label style="margin-bottom:10px;display:block">Select Payment Method</label>
    <div class="method-grid" id="methodGrid"></div>

    <div class="payout-fields" id="payoutFields" style="display:none">
      <div class="form-group" style="margin-bottom:12px">
        <label id="acctNumLabel">Account Number</label>
        <input type="text" id="inputAcctNum" placeholder="">
      </div>
      <div class="form-group" style="margin-bottom:0">
        <label>Account Name (Full Name)</label>
        <input type="text" id="inputAcctName" placeholder="e.g. Juan dela Cruz">
      </div>
    </div>

    <button class="btn-submit-payout" id="submitPayoutBtn" disabled onclick="submitPayout()">Submit Request</button>
  </div>
</div>

<div class="toast" id="toast"></div>

<script>
// Stars
const starsEl=document.getElementById('stars');
for(let i=0;i<80;i++){const s=document.createElement('div');s.className='star';s.style.cssText=`left:${Math.random()*100}%;top:${Math.random()*100}%;--d:${2+Math.random()*4}s;--delay:${Math.random()*4}s`;starsEl.appendChild(s);}

// Wheel
const SEGMENTS=[
  {label:'₱5',   color:'#7c3aed',text:'#fff'},
  {label:'₱10',  color:'#b45309',text:'#fff'},
  {label:'Try Again',color:'#1e293b',text:'#475569'},
  {label:'₱20',  color:'#065f46',text:'#fff'},
  {label:'₱50',  color:'#9a3412',text:'#fff'},
  {label:'Try Again',color:'#1e293b',text:'#475569'},
  {label:'₱100', color:'#1d4ed8',text:'#fff'},
  {label:'₱200', color:'#7e22ce',text:'#fbbf24'},
];
const N=SEGMENTS.length,ARC=(2*Math.PI)/N;
const canvas=document.getElementById('wheelCanvas'),ctx=canvas.getContext('2d');
let currentAngle=0,isSpinning=false,playerData=null,spinsLeft=0,selectedMethod=null;

function drawWheel(angle){
  const cx=canvas.width/2,cy=canvas.height/2,r=cx-10;
  ctx.clearRect(0,0,canvas.width,canvas.height);
  for(let i=0;i<N;i++){
    const start=angle+i*ARC,end=start+ARC,seg=SEGMENTS[i];
    ctx.beginPath();ctx.moveTo(cx,cy);ctx.arc(cx,cy,r,start,end);ctx.closePath();
    ctx.fillStyle=seg.color;ctx.fill();ctx.strokeStyle='#07070f';ctx.lineWidth=2;ctx.stroke();
    ctx.save();ctx.translate(cx,cy);ctx.rotate(start+ARC/2);ctx.textAlign='right';
    ctx.fillStyle=seg.text;ctx.font=`bold ${seg.label.length>4?12:15}px Syne,sans-serif`;
    ctx.fillText(seg.label,r-14,5);ctx.restore();
  }
  ctx.beginPath();ctx.arc(cx,cy,28,0,2*Math.PI);
  const cg=ctx.createRadialGradient(cx-4,cy-4,4,cx,cy,28);
  cg.addColorStop(0,'#8b5cf6');cg.addColorStop(1,'#4c1d95');
  ctx.fillStyle=cg;ctx.fill();ctx.strokeStyle='#fbbf24';ctx.lineWidth=3;ctx.stroke();
  ctx.fillStyle='#fbbf24';ctx.font='bold 18px Syne';ctx.textAlign='center';ctx.textBaseline='middle';
  ctx.fillText('★',cx,cy);
}
drawWheel(0);

function showToast(msg,type='info',duration=3500){
  const t=document.getElementById('toast');t.textContent=msg;t.className=`toast ${type} show`;
  clearTimeout(t._timer);t._timer=setTimeout(()=>t.classList.remove('show'),duration);
}

async function api(data){
  const fd=new FormData();Object.entries(data).forEach(([k,v])=>fd.append(k,v));
  const res=await fetch('api.php',{method:'POST',body:fd});return res.json();
}

async function doRegister(){
  const name=document.getElementById('inputName').value.trim();
  if(!name){showToast('Please enter your name.','error');return;}
  const r=await api({action:'register',name});
  if(!r.success){showToast(r.message,'error');return;}
  playerData=r.player;spinsLeft=r.spins_left;
  updateUI();showToast(`Welcome, ${name}! You have ${spinsLeft} spin(s) today.`,'success');
}

function updateUI(){
  if(!playerData)return;
  document.getElementById('headerInfo').innerHTML=`
    <span style="color:var(--muted)">👤 ${esc(playerData.name)}</span>
    <span class="score-badge">₱${parseInt(playerData.score).toLocaleString()}</span>
    <span class="spins-badge">🎡 ${spinsLeft} spin${spinsLeft!==1?'s':''} left</span>`;
  const pp=document.getElementById('payoutPanel');
  pp.classList.remove('hidden');
  const ob=document.getElementById('openPayoutBtn');
  const canPayout=parseInt(playerData.score)>=<?= MIN_PAYOUT_AMOUNT ?>;
  ob.disabled=!canPayout;
  ob.textContent=canPayout?`📲 Request ₱${parseInt(playerData.score).toLocaleString()} Cash Out`:`Need ₱<?= MIN_PAYOUT_AMOUNT ?> to cash out (₱${playerData.score} balance)`;
  const sb=document.getElementById('spinBtn');
  sb.disabled=spinsLeft<=0||isSpinning;
  sb.textContent=spinsLeft<=0?'No Spins Left Today':'SPIN!';
}

async function doSpin(){
  if(!playerData||spinsLeft<=0||isSpinning)return;
  isSpinning=true;document.getElementById('spinBtn').disabled=true;
  const r=await api({action:'spin'});
  if(!r.success){showToast(r.message,'error');isSpinning=false;updateUI();return;}
  const targetIndex=r.segment_index;
  const segCenter=targetIndex*ARC+ARC/2;
  const targetAngle=((-Math.PI/2)-segCenter+2*Math.PI*5)%(2*Math.PI)+2*Math.PI*5;
  const startAngle=currentAngle,spinDuration=4000+Math.random()*1500,start=performance.now();
  function easeOut(t){return 1-Math.pow(1-t,4)}
  function animate(now){
    const elapsed=now-start,progress=Math.min(elapsed/spinDuration,1),eased=easeOut(progress);
    currentAngle=startAngle+(targetAngle-startAngle+2*Math.PI*5)*eased;
    drawWheel(currentAngle%(2*Math.PI));
    if(progress<1){requestAnimationFrame(animate);}
    else{currentAngle=currentAngle%(2*Math.PI);isSpinning=false;spinsLeft=r.spins_left;playerData.score=r.new_score;showPrize(r.prize);updateUI();loadLeaderboard();}
  }
  requestAnimationFrame(animate);
}

function showPrize(prize){
  const d=document.getElementById('prizeDisplay');
  d.className='prize-display animate '+(prize.amount>0?'win':'lose');
  if(prize.amount>0){
    d.innerHTML=`<div class="prize-label">🎉 ${esc(prize.label)}!</div><div class="prize-sub">Added to your balance!</div>`;
    showToast(`🎉 You won ${prize.label}!`,'success',4000);
  }else{
    d.innerHTML=`<div class="prize-label">😅 Try Again</div><div class="prize-sub">Better luck next spin!</div>`;
    showToast('No luck this time!','info');
  }
  setTimeout(()=>d.classList.remove('animate'),600);
}

// ── Payment Methods ──────────────────────────────────────────
const METHODS={
  'GCash':    {icon:'💚',tag:'E-Wallet',type:'ewallet',hint:'09XXXXXXXXX'},
  'Maya':     {icon:'🔵',tag:'E-Wallet',type:'ewallet',hint:'09XXXXXXXXX'},
  'ShopeePay':{icon:'🟠',tag:'E-Wallet',type:'ewallet',hint:'09XXXXXXXXX'},
  'GrabPay':  {icon:'🟢',tag:'E-Wallet',type:'ewallet',hint:'09XXXXXXXXX'},
  'BDO':      {icon:'🏦',tag:'Bank',    type:'bank',   hint:'Account number'},
  'BPI':      {icon:'🏦',tag:'Bank',    type:'bank',   hint:'Account number'},
  'Metrobank':{icon:'🏦',tag:'Bank',    type:'bank',   hint:'Account number'},
  'UnionBank':{icon:'🏦',tag:'Bank',    type:'bank',   hint:'Account number'},
  'PNB':      {icon:'🏦',tag:'Bank',    type:'bank',   hint:'Account number'},
  'Landbank': {icon:'🏦',tag:'Bank',    type:'bank',   hint:'Account number'},
  'RCBC':     {icon:'🏦',tag:'Bank',    type:'bank',   hint:'Account number'},
  'Seabank':  {icon:'🏦',tag:'Bank',    type:'bank',   hint:'Account number'},
  'GoTyme':   {icon:'🏦',tag:'Bank',    type:'bank',   hint:'Account number'},
  'Other':    {icon:'💳',tag:'Other',   type:'other',  hint:'Account number'},
};

function buildMethodGrid(){
  const grid=document.getElementById('methodGrid');
  grid.innerHTML=Object.entries(METHODS).map(([name,m])=>`
    <button class="method-btn" onclick="selectMethod('${name}')" id="mb_${name.replace(/\s/g,'_')}">
      <span class="m-icon">${m.icon}</span>
      <span class="m-name">${name}</span>
      <span class="method-tag ${m.type==='ewallet'?'tag-ewallet':'tag-bank'}">${m.tag}</span>
    </button>`).join('');
}

function selectMethod(name){
  selectedMethod=name;
  document.querySelectorAll('.method-btn').forEach(b=>b.classList.remove('selected'));
  document.getElementById('mb_'+name.replace(/\s/g,'_')).classList.add('selected');
  const m=METHODS[name];
  document.getElementById('payoutFields').style.display='block';
  document.getElementById('acctNumLabel').textContent=m.type==='ewallet'?'Mobile Number':'Account Number';
  document.getElementById('inputAcctNum').placeholder=m.hint;
  document.getElementById('inputAcctNum').value='';
  document.getElementById('inputAcctName').value='';
  checkPayoutReady();
}

function checkPayoutReady(){
  const ready=selectedMethod&&document.getElementById('inputAcctNum').value.trim()&&document.getElementById('inputAcctName').value.trim();
  document.getElementById('submitPayoutBtn').disabled=!ready;
}

document.addEventListener('input',function(e){
  if(e.target.id==='inputAcctNum'||e.target.id==='inputAcctName') checkPayoutReady();
});

function openPayoutModal(){
  if(!playerData||parseInt(playerData.score)<<?= MIN_PAYOUT_AMOUNT ?>)return;
  document.getElementById('modalAmount').textContent='₱'+parseInt(playerData.score).toLocaleString();
  buildMethodGrid();
  document.getElementById('payoutFields').style.display='none';
  document.getElementById('submitPayoutBtn').disabled=true;
  selectedMethod=null;
  document.getElementById('payoutModal').classList.add('open');
}
function closePayoutModal(){document.getElementById('payoutModal').classList.remove('open');}
document.getElementById('payoutModal').addEventListener('click',function(e){if(e.target===this)closePayoutModal();});

async function submitPayout(){
  const acctNum=document.getElementById('inputAcctNum').value.trim();
  const acctName=document.getElementById('inputAcctName').value.trim();
  if(!selectedMethod||!acctNum||!acctName){showToast('Please fill in all details.','error');return;}
  document.getElementById('submitPayoutBtn').disabled=true;
  document.getElementById('submitPayoutBtn').textContent='Submitting…';
  const r=await api({action:'request_payout',payment_method:selectedMethod,account_name:acctName,account_number:acctNum,amount:playerData.score});
  if(!r.success){showToast(r.message,'error');document.getElementById('submitPayoutBtn').disabled=false;document.getElementById('submitPayoutBtn').textContent='Submit Request';return;}
  playerData.score=0;updateUI();closePayoutModal();
  showToast(r.message,'success',6000);
}

async function loadLeaderboard(){
  const r=await fetch('api.php?action=leaderboard');const data=await r.json();
  if(!data.success)return;
  const medals=['🥇','🥈','🥉'];
  const html=data.data.map((p,i)=>`<div class="lb-row"><span class="lb-rank">${medals[i]||'#'+(i+1)}</span><span class="lb-name">${esc(p.name)}</span><span class="lb-score">₱${parseInt(p.score).toLocaleString()}</span></div>`).join('');
  document.getElementById('leaderboard').innerHTML=html||'<div style="color:var(--muted);text-align:center;padding:20px">No players yet.</div>';
}

function esc(s){return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}

loadLeaderboard();
setInterval(loadLeaderboard,30000);
</script>
</body>
</html>
