<?php
require_once 'includes/config.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Payment methods config
$PAYMENT_METHODS = [
    'GCash'      => ['type' => 'mobile',  'label' => 'Mobile Number', 'pattern' => '09XXXXXXXXX'],
    'Maya'       => ['type' => 'mobile',  'label' => 'Mobile Number', 'pattern' => '09XXXXXXXXX'],
    'ShopeePay'  => ['type' => 'mobile',  'label' => 'Mobile Number', 'pattern' => '09XXXXXXXXX'],
    'GrabPay'    => ['type' => 'mobile',  'label' => 'Mobile Number', 'pattern' => '09XXXXXXXXX'],
    'BDO'        => ['type' => 'bank',    'label' => 'Account Number', 'pattern' => 'e.g. 1234567890'],
    'BPI'        => ['type' => 'bank',    'label' => 'Account Number', 'pattern' => 'e.g. 1234567890'],
    'Metrobank'  => ['type' => 'bank',    'label' => 'Account Number', 'pattern' => 'e.g. 1234567890'],
    'UnionBank'  => ['type' => 'bank',    'label' => 'Account Number', 'pattern' => 'e.g. 1234567890'],
    'PNB'        => ['type' => 'bank',    'label' => 'Account Number', 'pattern' => 'e.g. 1234567890'],
    'Landbank'   => ['type' => 'bank',    'label' => 'Account Number', 'pattern' => 'e.g. 1234567890'],
    'RCBC'       => ['type' => 'bank',    'label' => 'Account Number', 'pattern' => 'e.g. 1234567890'],
    'EastWest'   => ['type' => 'bank',    'label' => 'Account Number', 'pattern' => 'e.g. 1234567890'],
    'Seabank'    => ['type' => 'bank',    'label' => 'Account Number', 'pattern' => 'e.g. 1234567890'],
    'GoTyme'     => ['type' => 'bank',    'label' => 'Account Number', 'pattern' => 'e.g. 1234567890'],
    'Other'      => ['type' => 'other',   'label' => 'Account Number', 'pattern' => 'Enter account number'],
];

switch ($action) {

    case 'get_payment_methods':
        echo json_encode(['success' => true, 'methods' => $PAYMENT_METHODS]);
        break;

    case 'register':
        $name = trim($_POST['name'] ?? '');
        if (!$name) { echo json_encode(['success'=>false,'message'=>'Name is required.']); exit; }
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM players WHERE name = ?");
        $stmt->execute([$name]);
        $player = $stmt->fetch();
        if (!$player) {
            $db->prepare("INSERT INTO players (name) VALUES (?)")->execute([$name]);
            $player_id = $db->lastInsertId();
            $stmt = $db->prepare("SELECT * FROM players WHERE id = ?");
            $stmt->execute([$player_id]);
            $player = $stmt->fetch();
        }
        $_SESSION['player_id'] = $player['id'];
        $_SESSION['player_name'] = $player['name'];
        $today = $db->prepare("SELECT COUNT(*) as cnt FROM spin_history WHERE player_id=? AND DATE(spin_time)=CURDATE()");
        $today->execute([$player['id']]);
        $spins_today = $today->fetch()['cnt'];
        echo json_encode(['success'=>true,'player'=>$player,'spins_left'=>max(0,MAX_SPINS_PER_DAY-$spins_today),'max_spins'=>MAX_SPINS_PER_DAY]);
        break;

    case 'spin':
        if (empty($_SESSION['player_id'])) { echo json_encode(['success'=>false,'message'=>'Please register first.']); exit; }
        $db = getDB();
        $player_id = $_SESSION['player_id'];
        $today = $db->prepare("SELECT COUNT(*) as cnt FROM spin_history WHERE player_id=? AND DATE(spin_time)=CURDATE()");
        $today->execute([$player_id]);
        $spins_today = $today->fetch()['cnt'];
        if ($spins_today >= MAX_SPINS_PER_DAY) { echo json_encode(['success'=>false,'message'=>'No more spins today!']); exit; }

        $prizes = [
            ['label'=>'₱5',    'amount'=>5,   'weight'=>20],
            ['label'=>'₱10',   'amount'=>10,  'weight'=>18],
            ['label'=>'Try Again','amount'=>0,'weight'=>25],
            ['label'=>'₱20',   'amount'=>20,  'weight'=>15],
            ['label'=>'₱50',   'amount'=>50,  'weight'=>10],
            ['label'=>'Try Again','amount'=>0,'weight'=>5],
            ['label'=>'₱100',  'amount'=>100, 'weight'=>5],
            ['label'=>'₱200',  'amount'=>200, 'weight'=>2],
        ];
        $totalWeight = array_sum(array_column($prizes,'weight'));
        $rand = mt_rand(1,$totalWeight);
        $cumulative = 0; $won = null; $segment_index = 0;
        foreach ($prizes as $i => $prize) {
            $cumulative += $prize['weight'];
            if ($rand <= $cumulative) { $won=$prize; $segment_index=$i; break; }
        }
        $db->prepare("INSERT INTO spin_history (player_id,prize_label,prize_amount) VALUES (?,?,?)")->execute([$player_id,$won['label'],$won['amount']]);
        if ($won['amount']>0) $db->prepare("UPDATE players SET score=score+? WHERE id=?")->execute([$won['amount'],$player_id]);
        $spins_left = MAX_SPINS_PER_DAY - ($spins_today+1);
        $scoreRow = $db->prepare("SELECT score FROM players WHERE id=?"); $scoreRow->execute([$player_id]);
        echo json_encode(['success'=>true,'segment_index'=>$segment_index,'prize'=>$won,'spins_left'=>max(0,$spins_left),'new_score'=>$scoreRow->fetch()['score']]);
        break;

    case 'request_payout':
        if (empty($_SESSION['player_id'])) { echo json_encode(['success'=>false,'message'=>'Please register first.']); exit; }
        $db = getDB();
        $player_id = $_SESSION['player_id'];
        $player = $db->prepare("SELECT * FROM players WHERE id=?"); $player->execute([$player_id]); $player=$player->fetch();

        $method  = trim($_POST['payment_method'] ?? '');
        $acct_name = trim($_POST['account_name'] ?? '');
        $acct_num  = trim($_POST['account_number'] ?? '');
        $amount    = (float)($_POST['amount'] ?? $player['score']);

        if (!$method || !$acct_name || !$acct_num) {
            echo json_encode(['success'=>false,'message'=>'Please fill in all payment details.']); exit;
        }
        if (!isset($PAYMENT_METHODS[$method])) {
            echo json_encode(['success'=>false,'message'=>'Invalid payment method.']); exit;
        }
        if ($player['score'] < MIN_PAYOUT_AMOUNT) {
            echo json_encode(['success'=>false,'message'=>'Minimum payout is ₱'.MIN_PAYOUT_AMOUNT.'.']); exit;
        }
        $pending = $db->prepare("SELECT id FROM payout_requests WHERE player_id=? AND status='pending'");
        $pending->execute([$player_id]);
        if ($pending->fetch()) { echo json_encode(['success'=>false,'message'=>'You already have a pending request.']); exit; }

        $amount = min($amount, $player['score']);
        $db->prepare("INSERT INTO payout_requests (player_id,player_name,payment_method,account_name,account_number,amount) VALUES (?,?,?,?,?,?)")
           ->execute([$player_id,$player['name'],$method,$acct_name,$acct_num,$amount]);
        $db->prepare("UPDATE players SET score=score-? WHERE id=?")->execute([$amount,$player_id]);

        echo json_encode(['success'=>true,'message'=>"Payout request of ₱{$amount} via {$method} submitted! Admin will process within 24hrs."]);
        break;

    case 'leaderboard':
        $db = getDB();
        $rows = $db->query("SELECT name,score,total_spins,total_won FROM leaderboard LIMIT 10")->fetchAll();
        echo json_encode(['success'=>true,'data'=>$rows]);
        break;

    case 'status':
        if (empty($_SESSION['player_id'])) { echo json_encode(['success'=>false,'loggedIn'=>false]); exit; }
        $db = getDB();
        $player_id = $_SESSION['player_id'];
        $player = $db->prepare("SELECT * FROM players WHERE id=?"); $player->execute([$player_id]); $player=$player->fetch();
        $today = $db->prepare("SELECT COUNT(*) as cnt FROM spin_history WHERE player_id=? AND DATE(spin_time)=CURDATE()");
        $today->execute([$player_id]);
        $spins_today = $today->fetch()['cnt'];
        echo json_encode(['success'=>true,'loggedIn'=>true,'player'=>$player,'spins_left'=>max(0,MAX_SPINS_PER_DAY-$spins_today),'max_spins'=>MAX_SPINS_PER_DAY]);
        break;

    default:
        echo json_encode(['success'=>false,'message'=>'Unknown action.']);
}
