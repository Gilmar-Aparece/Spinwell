<?php
require_once '../includes/config.php';

$error='';
if(($_POST['action']??'')==='login'){
  $db=getDB();
  $stmt=$db->prepare("SELECT * FROM admin_users WHERE username=?");
  $stmt->execute([$_POST['username']??'']);
  $admin=$stmt->fetch();
  if($admin&&password_verify($_POST['password']??'',$admin['password'])){$_SESSION['admin_logged_in']=true;header('Location: index.php');exit;}
  $error='Invalid username or password.';
}
if(($_POST['action']??'')==='logout'){unset($_SESSION['admin_logged_in']);header('Location: index.php');exit;}

if(!empty($_SESSION['admin_logged_in'])&&($_POST['action']??'')==='update_payout'){
  $db=getDB();
  $id=(int)$_POST['id'];
  $status=in_array($_POST['status'],['approved','rejected'])?$_POST['status']:'pending';
  $note=trim($_POST['note']??'');
  $db->prepare("UPDATE payout_requests SET status=?,admin_note=?,processed_at=NOW() WHERE id=?")->execute([$status,$note,$id]);
  if($status==='rejected'){
    $req=$db->prepare("SELECT player_id,amount FROM payout_requests WHERE id=?");$req->execute([$id]);$req=$req->fetch();
    $db->prepare("UPDATE players SET score=score+? WHERE id=?")->execute([$req['amount'],$req['player_id']]);
  }
  header('Location: index.php?tab=payouts');exit;
}

$isAdmin=!empty($_SESSION['admin_logged_in']);
$tab=$_GET['tab']??'payouts';

if($isAdmin){
  $db=getDB();
  $payouts=$db->query("SELECT * FROM payout_requests ORDER BY requested_at DESC LIMIT 100")->fetchAll();
  $players=$db->query("SELECT * FROM leaderboard LIMIT 50")->fetchAll();
  $stats=$db->query("SELECT (SELECT COUNT(*) FROM payout_requests WHERE status='pending') as pending_count,(SELECT SUM(amount) FROM payout_requests WHERE status='approved') as total_paid,(SELECT COUNT(*) FROM players) as total_players,(SELECT COUNT(*) FROM spin_history WHERE DATE(spin_time)=CURDATE()) as spins_today")->fetch();
}

// Method icons
$methodIcons=['GCash'=>'💚','Maya'=>'🔵','ShopeePay'=>'🟠','GrabPay'=>'🟢','BDO'=>'🏦','BPI'=>'🏦','Metrobank'=>'🏦','UnionBank'=>'🏦','PNB'=>'🏦','Landbank'=>'🏦','RCBC'=>'🏦','EastWest'=>'🏦','Seabank'=>'🏦','GoTyme'=>'🏦','Other'=>'💳'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Admin – Spin to Win</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=DM+Mono:wght@400;500&display=swap');
:root{--bg:#0a0a0f;--surface:#12121a;--border:#1e1e2e;--accent:#7c3aed;--gold:#f59e0b;--green:#10b981;--red:#ef4444;--text:#e2e8f0;--muted:#64748b}
*{box-sizing:border-box;margin:0;padding:0}
body{background:var(--bg);color:var(--text);font-family:'Syne',sans-serif;min-height:100vh}
.login-wrap{display:flex;align-items:center;justify-content:center;min-height:100vh}
.login-box{background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:40px;width:360px}
.login-box h1{font-size:1.8rem;font-weight:800;margin-bottom:8px}
.login-box p{color:var(--muted);margin-bottom:28px;font-size:.9rem}
.form-group{margin-bottom:16px}
label{display:block;font-size:.85rem;color:var(--muted);margin-bottom:6px}
input[type=text],input[type=password],textarea,select{width:100%;background:var(--bg);border:1px solid var(--border);color:var(--text);padding:10px 14px;border-radius:8px;font-family:'DM Mono',monospace;font-size:.9rem;outline:none;transition:border-color .2s}
input:focus,textarea:focus,select:focus{border-color:var(--accent)}
.btn{display:inline-block;padding:10px 20px;border-radius:8px;border:none;cursor:pointer;font-family:'Syne',sans-serif;font-weight:700;font-size:.9rem;transition:all .2s}
.btn-primary{background:var(--accent);color:#fff}
.btn-primary:hover{background:#6d28d9}
.btn-approve{background:var(--green);color:#fff}
.btn-reject{background:var(--red);color:#fff}
.btn-sm{padding:6px 12px;font-size:.8rem}
.error{background:rgba(239,68,68,.15);border:1px solid var(--red);color:var(--red);padding:10px 14px;border-radius:8px;margin-bottom:16px;font-size:.85rem}
header{background:var(--surface);border-bottom:1px solid var(--border);padding:16px 32px;display:flex;align-items:center;justify-content:space-between}
header h1{font-size:1.3rem;font-weight:800}
header span{color:var(--accent)}
.logout{font-size:.85rem;background:none;border:1px solid var(--border);padding:6px 14px;border-radius:6px;color:var(--text);font-family:'Syne',sans-serif;cursor:pointer}
.container{max-width:1300px;margin:0 auto;padding:32px 24px}
.stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:32px}
.stat-card{background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:20px}
.stat-card .val{font-size:2rem;font-weight:800}
.stat-card .lbl{color:var(--muted);font-size:.8rem;margin-top:4px}
.stat-card.gold .val{color:var(--gold)}.stat-card.green .val{color:var(--green)}.stat-card.red .val{color:var(--red)}.stat-card.accent .val{color:#a78bfa}
.tabs{display:flex;gap:4px;margin-bottom:24px;border-bottom:1px solid var(--border)}
.tab{padding:10px 20px;cursor:pointer;color:var(--muted);font-weight:700;font-size:.9rem;border-bottom:2px solid transparent;margin-bottom:-1px;text-decoration:none}
.tab.active{color:var(--text);border-bottom-color:var(--accent)}
table{width:100%;border-collapse:collapse;background:var(--surface);border-radius:12px;overflow:hidden}
th{background:var(--border);padding:12px 16px;text-align:left;font-size:.78rem;color:var(--muted);text-transform:uppercase;letter-spacing:.05em}
td{padding:12px 16px;border-bottom:1px solid var(--border);font-size:.88rem;vertical-align:middle}
tr:last-child td{border-bottom:none}
tr:hover td{background:rgba(124,58,237,.05)}
.badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:.75rem;font-weight:700}
.badge-pending{background:rgba(245,158,11,.15);color:var(--gold)}
.badge-approved{background:rgba(16,185,129,.15);color:var(--green)}
.badge-rejected{background:rgba(239,68,68,.15);color:var(--red)}
.method-pill{display:inline-flex;align-items:center;gap:5px;background:rgba(124,58,237,.12);border:1px solid rgba(124,58,237,.25);border-radius:20px;padding:4px 10px;font-size:.8rem;font-weight:700;color:#a78bfa}
.acct-block{line-height:1.6}
.acct-name{font-weight:700;font-size:.9rem}
.acct-num{font-family:'DM Mono',monospace;font-size:.82rem;color:var(--muted)}
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:100;align-items:center;justify-content:center}
.modal-overlay.open{display:flex}
.modal{background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:32px;width:460px}
.modal h3{font-size:1.2rem;font-weight:800;margin-bottom:6px}
.modal .modal-detail{background:rgba(124,58,237,.08);border:1px solid rgba(124,58,237,.2);border-radius:10px;padding:14px 16px;margin-bottom:18px;font-size:.88rem;line-height:1.8}
.modal-actions{display:flex;gap:10px;margin-top:20px}
.mono{font-family:'DM Mono',monospace}
@media(max-width:768px){.stats-grid{grid-template-columns:repeat(2,1fr)}}
</style>
</head>
<body>

<?php if(!$isAdmin): ?>
<div class="login-wrap">
  <div class="login-box">
    <h1>🎰 Admin</h1>
    <p>Spin to Win – Control Panel</p>
    <?php if($error): ?><div class="error"><?=htmlspecialchars($error)?></div><?php endif; ?>
    <form method="POST">
      <input type="hidden" name="action" value="login">
      <div class="form-group"><label>Username</label><input type="text" name="username" required autofocus></div>
      <div class="form-group"><label>Password</label><input type="password" name="password" required></div>
      <button type="submit" class="btn btn-primary" style="width:100%;margin-top:8px">Sign In</button>
    </form>
    <p style="color:var(--muted);font-size:.75rem;margin-top:16px;text-align:center">Default: admin / password</p>
  </div>
</div>

<?php else: ?>
<header>
  <h1>🎰 Spin to Win <span>Admin</span></h1>
  <form method="POST" style="display:inline">
    <input type="hidden" name="action" value="logout">
    <button type="submit" class="logout">Logout</button>
  </form>
</header>

<div class="container">
  <div class="stats-grid">
    <div class="stat-card red"><div class="val"><?=$stats['pending_count']??0?></div><div class="lbl">Pending Payouts</div></div>
    <div class="stat-card gold"><div class="val">₱<?=number_format($stats['total_paid']??0,0)?></div><div class="lbl">Total Paid Out</div></div>
    <div class="stat-card accent"><div class="val"><?=$stats['total_players']??0?></div><div class="lbl">Total Players</div></div>
    <div class="stat-card green"><div class="val"><?=$stats['spins_today']??0?></div><div class="lbl">Spins Today</div></div>
  </div>

  <div class="tabs">
    <a href="?tab=payouts" class="tab <?=$tab==='payouts'?'active':''?>">💸 Payout Requests</a>
    <a href="?tab=players" class="tab <?=$tab==='players'?'active':''?>">👥 Players</a>
  </div>

  <?php if($tab==='payouts'): ?>
  <table>
    <thead><tr><th>#</th><th>Player</th><th>Payment Method</th><th>Account Details</th><th>Amount</th><th>Status</th><th>Requested</th><th>Action</th></tr></thead>
    <tbody>
    <?php foreach($payouts as $p): $icon=$methodIcons[$p['payment_method']]??'💳'; ?>
      <tr>
        <td class="mono">#<?=$p['id']?></td>
        <td><?=htmlspecialchars($p['player_name'])?></td>
        <td><span class="method-pill"><?=$icon?> <?=htmlspecialchars($p['payment_method'])?></span></td>
        <td>
          <div class="acct-block">
            <div class="acct-name"><?=htmlspecialchars($p['account_name'])?></div>
            <div class="acct-num"><?=htmlspecialchars($p['account_number'])?></div>
          </div>
        </td>
        <td style="color:var(--gold);font-weight:700;font-family:'DM Mono',monospace">₱<?=number_format($p['amount'],2)?></td>
        <td><span class="badge badge-<?=$p['status']?>"><?=strtoupper($p['status'])?></span></td>
        <td class="mono" style="font-size:.78rem;color:var(--muted)"><?=$p['requested_at']?></td>
        <td>
          <?php if($p['status']==='pending'): ?>
          <button class="btn btn-sm btn-approve" onclick="openModal(<?=$p['id']?>,<?=json_encode($p['player_name'])?>,<?=json_encode($p['payment_method'])?>,<?=json_encode($p['account_name'])?>,<?=json_encode($p['account_number'])?>,<?=$p['amount']?>,'approved')">✓ Approve</button>
          <button class="btn btn-sm btn-reject" style="margin-left:4px" onclick="openModal(<?=$p['id']?>,<?=json_encode($p['player_name'])?>,<?=json_encode($p['payment_method'])?>,<?=json_encode($p['account_name'])?>,<?=json_encode($p['account_number'])?>,<?=$p['amount']?>,'rejected')">✗ Reject</button>
          <?php else: ?>
          <span style="color:var(--muted);font-size:.8rem"><?=$p['admin_note']?htmlspecialchars($p['admin_note']):'—'?></span>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if(empty($payouts)): ?><tr><td colspan="8" style="text-align:center;color:var(--muted);padding:40px">No payout requests yet.</td></tr><?php endif; ?>
    </tbody>
  </table>

  <?php elseif($tab==='players'): ?>
  <table>
    <thead><tr><th>Rank</th><th>Name</th><th>Balance (₱)</th><th>Total Spins</th><th>Total Won</th><th>Joined</th></tr></thead>
    <tbody>
    <?php foreach($players as $i=>$p): ?>
      <tr>
        <td style="font-weight:800;color:<?=$i===0?'var(--gold)':($i===1?'#94a3b8':($i===2?'#b45309':'var(--muted)'))?>"><?=$i===0?'🥇':($i===1?'🥈':($i===2?'🥉':'#'.($i+1)))?></td>
        <td><?=htmlspecialchars($p['name'])?></td>
        <td style="color:var(--green);font-weight:700;font-family:'DM Mono',monospace">₱<?=number_format($p['score'],0)?></td>
        <td><?=$p['total_spins']?></td>
        <td class="mono">₱<?=number_format($p['total_won']??0,2)?></td>
        <td style="font-size:.8rem;color:var(--muted)"><?=date('M d, Y',strtotime($p['created_at']))?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>

<!-- Action Modal -->
<div class="modal-overlay" id="modal">
  <div class="modal">
    <h3 id="modal-title">Confirm Action</h3>
    <div class="modal-detail" id="modal-detail"></div>
    <form method="POST">
      <input type="hidden" name="action" value="update_payout">
      <input type="hidden" name="id" id="modal-id">
      <input type="hidden" name="status" id="modal-status">
      <div class="form-group">
        <label>Admin Note</label>
        <textarea name="note" id="modal-note" rows="2" placeholder="e.g. Sent via GCash on May 20"></textarea>
      </div>
      <div class="modal-actions">
        <button type="submit" class="btn btn-primary" id="modal-btn">Confirm</button>
        <button type="button" class="btn" style="background:var(--border)" onclick="closeModal()">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal(id,name,method,acctName,acctNum,amount,status){
  document.getElementById('modal-id').value=id;
  document.getElementById('modal-status').value=status;
  document.getElementById('modal-title').textContent=status==='approved'?'✓ Approve Payout':'✗ Reject Payout';
  document.getElementById('modal-detail').innerHTML=`
    <strong>${name}</strong><br>
    Method: <strong>${method}</strong><br>
    Account Name: <strong>${acctName}</strong><br>
    Account Number: <strong>${acctNum}</strong><br>
    Amount: <strong style="color:var(--gold)">₱${parseFloat(amount).toFixed(2)}</strong>`;
  document.getElementById('modal-btn').textContent=status==='approved'?'Confirm & Approve':'Confirm & Reject';
  document.getElementById('modal-btn').className='btn '+(status==='approved'?'btn-approve':'btn-reject');
  if(status==='approved') document.getElementById('modal-note').placeholder=`e.g. Sent ₱${parseFloat(amount).toFixed(2)} via ${method}`;
  document.getElementById('modal').classList.add('open');
}
function closeModal(){document.getElementById('modal').classList.remove('open');}
document.getElementById('modal').addEventListener('click',function(e){if(e.target===this)closeModal();});
</script>
<?php endif; ?>
</body>
</html>
