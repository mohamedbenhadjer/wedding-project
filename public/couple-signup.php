<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

startSession();
if (currentCouple()) redirect('couple-dashboard.php');

$mode   = $_GET['mode'] ?? 'signup';
$err    = null;
$values = ['name1'=>'','name2'=>'','email'=>'','date'=>'','venue'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  verifyCsrf();
  $mode = $_POST['mode'] ?? 'signup';

  if ($mode === 'signup') {
    $values['name1'] = trim($_POST['name1'] ?? '');
    $values['name2'] = trim($_POST['name2'] ?? '');
    $values['email'] = strtolower(trim($_POST['email'] ?? ''));
    $values['date']  = trim($_POST['date']  ?? '');
    $values['venue'] = trim($_POST['venue'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if ($values['name1']==='' || $values['name2']==='' || $values['email']==='' || $values['date']==='') {
      $err = 'Please fill in all required fields.';
    } elseif (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
      $err = 'That email address looks off.';
    } elseif (strlen($pass) < 6) {
      $err = 'Password must be at least 6 characters.';
    } else {
      $pdo = db();
      $stmt = $pdo->prepare('SELECT 1 FROM couples WHERE email = ?');
      $stmt->execute([$values['email']]);
      if ($stmt->fetchColumn()) {
        $err = 'An account with this email already exists.';
      } else {
        try {
          $pdo->beginTransaction();
          $weddingId = uniqueWeddingId();
          $pdo->prepare('INSERT INTO couples (email,password,name1,name2,wedding_id) VALUES (?,?,?,?,?)')
              ->execute([$values['email'], password_hash($pass, PASSWORD_DEFAULT),
                         $values['name1'], $values['name2'], $weddingId]);
          $pdo->prepare('INSERT INTO weddings (id,couple_email,name1,name2,wedding_date,venue,theme) VALUES (?,?,?,?,?,?,?)')
              ->execute([$weddingId, $values['email'], $values['name1'], $values['name2'],
                         $values['date'], $values['venue'] ?: null, 'dark_romantic']);
          $pdo->commit();
        } catch (Throwable $e) {
          $pdo->rollBack();
          $err = 'Could not create your wedding. Please try again.';
        }
        if (!$err) {
          setCoupleSession([
            'email' => $values['email'], 'name1' => $values['name1'],
            'name2' => $values['name2'], 'wedding_id' => $weddingId,
          ]);
          redirect('couple-dashboard.php');
        }
      }
    }
  } else { // login
    $email = strtolower(trim($_POST['email'] ?? ''));
    $pass  = $_POST['password'] ?? '';
    $stmt  = db()->prepare('SELECT * FROM couples WHERE email = ?');
    $stmt->execute([$email]);
    $c = $stmt->fetch();
    if (!$c || !password_verify($pass, $c['password'])) {
      $err = 'Incorrect email or password.';
    } else {
      setCoupleSession([
        'email' => $c['email'], 'name1' => $c['name1'],
        'name2' => $c['name2'], 'wedding_id' => $c['wedding_id'],
      ]);
      redirect('couple-dashboard.php');
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $mode==='login' ? 'Sign In' : 'Create Your Wedding' ?> — Forever Together</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;1,400&family=Cormorant+Garamond:ital,wght@0,300;1,300&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--ink:#16140f;--white:#faf8f4;--gold:#c9ad87;--gold-lt:#ede4d6;--muted:#8a8278;--bg:#f5f2ed;--err:#b85555;--serif:'Playfair Display',Georgia,serif;--cormo:'Cormorant Garamond',Georgia,serif;--sans:'DM Sans',system-ui,sans-serif}
body{font-family:var(--sans);background:var(--ink);color:var(--white);min-height:100vh;display:grid;grid-template-columns:1fr 1fr}
.left{background:#0d0b08;display:flex;flex-direction:column;justify-content:space-between;padding:48px 56px;position:relative;overflow:hidden;min-height:100vh}
.left::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse 80% 60% at 10% 100%,rgba(201,173,135,0.1) 0%,transparent 55%),radial-gradient(ellipse 50% 40% at 90% 0%,rgba(201,173,135,0.07) 0%,transparent 50%)}
.lr{position:absolute;border-radius:50%;border:1px solid rgba(201,173,135,0.08);pointer-events:none}
.lr1{width:400px;height:400px;bottom:-100px;right:-120px}
.lr2{width:700px;height:700px;bottom:-250px;right:-280px}
.left-logo{font-family:var(--serif);font-style:italic;font-size:20px;color:rgba(255,255,255,0.6);text-decoration:none;position:relative;z-index:1}
.left-mid{position:relative;z-index:1;flex:1;display:flex;flex-direction:column;justify-content:center}
.left-eyebrow{font-size:10px;letter-spacing:0.24em;text-transform:uppercase;color:var(--gold);margin-bottom:18px;font-weight:500}
.left-title{font-family:var(--cormo);font-size:clamp(44px,4vw,68px);font-weight:300;line-height:1;margin-bottom:10px}
.left-title em{font-style:italic;color:rgba(255,255,255,0.35)}
.left-desc{font-size:15px;color:rgba(255,255,255,0.4);font-weight:300;line-height:1.8;max-width:340px;margin-top:20px}
.left-bottom{position:relative;z-index:1}
.left-steps{display:flex;flex-direction:column;gap:14px}
.ls{display:flex;align-items:center;gap:14px}
.ls-dot{width:28px;height:28px;border-radius:50%;background:rgba(201,173,135,0.1);border:1px solid rgba(201,173,135,0.25);display:flex;align-items:center;justify-content:center;font-family:var(--cormo);font-size:16px;color:var(--gold);flex-shrink:0}
.ls-text{font-size:13px;color:rgba(255,255,255,0.4);font-weight:300}
.right{display:flex;align-items:center;justify-content:center;padding:48px 56px;min-height:100vh;background:var(--bg)}
.form-card{width:100%;max-width:440px}
.form-tabs{display:flex;background:#e8e2d8;border-radius:4px;padding:4px;margin-bottom:36px}
.tab{flex:1;padding:10px;font-family:var(--sans);font-size:13px;font-weight:500;letter-spacing:0.07em;text-transform:uppercase;border:none;background:transparent;color:#8a8278;border-radius:2px;cursor:pointer;text-decoration:none;text-align:center;transition:all 0.2s}
.tab.active{background:#fff;color:var(--ink);box-shadow:0 1px 4px rgba(0,0,0,0.08)}
.form-title{font-family:var(--serif);font-size:30px;font-weight:400;color:var(--ink);margin-bottom:6px}
.form-sub{font-size:14px;color:var(--muted);font-weight:300;margin-bottom:30px;line-height:1.65}
.field{margin-bottom:18px}
.field label{display:block;font-size:11px;font-weight:500;letter-spacing:0.12em;text-transform:uppercase;color:var(--muted);margin-bottom:7px}
.field input{width:100%;padding:12px 14px;font-family:var(--sans);font-size:14px;color:var(--ink);background:#fff;border:1px solid #ddd6c8;border-radius:2px;outline:none;transition:border-color 0.2s,box-shadow 0.2s}
.field input:focus{border-color:var(--gold);box-shadow:0 0 0 3px rgba(201,173,135,0.12)}
.field input::placeholder{color:#bbb5ac}
.field-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.alert{padding:11px 14px;border-radius:2px;font-size:13px;margin-bottom:16px;background:#fdf0f0;border:1px solid #e8c4c4;color:var(--err)}
.btn-submit{width:100%;padding:15px;font-family:var(--sans);font-size:13px;font-weight:500;letter-spacing:0.1em;text-transform:uppercase;background:var(--ink);color:#fff;border:none;border-radius:2px;cursor:pointer;transition:background 0.2s;margin-top:6px}
.btn-submit:hover{background:#2a2820}
.switch-link{text-align:center;font-size:13px;color:var(--muted);margin-top:20px}
.switch-link a{color:var(--ink);font-weight:500;text-decoration:none}
.switch-link a:hover{text-decoration:underline}
@media(max-width:768px){body{grid-template-columns:1fr}.left{display:none}.right{padding:32px 24px}}
</style>
</head>
<body>
<div class="left">
  <div class="lr lr1"></div><div class="lr lr2"></div>
  <a href="index.php" class="left-logo">Forever Together</a>
  <div class="left-mid">
    <p class="left-eyebrow">For Couples</p>
    <h1 class="left-title">Your wedding,<br><em>beautifully preserved</em></h1>
    <p class="left-desc">Create your wedding page, invite your guests, and collect every memory into one timeless keepsake.</p>
  </div>
  <div class="left-bottom">
    <div class="left-steps">
      <div class="ls"><div class="ls-dot">1</div><span class="ls-text">Create your wedding profile in minutes</span></div>
      <div class="ls"><div class="ls-dot">2</div><span class="ls-text">Import guests &amp; generate invite cards</span></div>
      <div class="ls"><div class="ls-dot">3</div><span class="ls-text">Collect photos &amp; export your keepsake</span></div>
    </div>
  </div>
</div>

<div class="right">
  <div class="form-card">
    <div class="form-tabs">
      <a href="?mode=signup" class="tab <?= $mode==='signup'?'active':'' ?>">Create Wedding</a>
      <a href="?mode=login"  class="tab <?= $mode==='login' ?'active':'' ?>">Sign In</a>
    </div>

    <?php if ($err): ?><div class="alert"><?= h($err) ?></div><?php endif; ?>

    <?php if ($mode === 'signup'): ?>
      <h2 class="form-title">Start your wedding</h2>
      <p class="form-sub">Tell us about the couple and your big day.</p>
      <form method="post" action="?mode=signup">
        <?= csrfField() ?>
        <input type="hidden" name="mode" value="signup">
        <div class="field-row">
          <div class="field"><label>Partner 1 Name</label><input name="name1" value="<?= h($values['name1']) ?>" placeholder="e.g. Sarah" required></div>
          <div class="field"><label>Partner 2 Name</label><input name="name2" value="<?= h($values['name2']) ?>" placeholder="e.g. Youcef" required></div>
        </div>
        <div class="field"><label>Email Address</label><input type="email" name="email" value="<?= h($values['email']) ?>" placeholder="your@email.com" required></div>
        <div class="field"><label>Password</label><input type="password" name="password" placeholder="At least 6 characters" required></div>
        <div class="field"><label>Wedding Date</label><input type="date" name="date" value="<?= h($values['date']) ?>" required></div>
        <div class="field"><label>Venue Name</label><input name="venue" value="<?= h($values['venue']) ?>" placeholder="e.g. Ch&acirc;teau de Versailles"></div>
        <button type="submit" class="btn-submit">Create Our Wedding &rarr;</button>
      </form>
      <p class="switch-link">Already have a wedding? <a href="?mode=login">Sign in</a></p>
    <?php else: ?>
      <h2 class="form-title">Welcome back</h2>
      <p class="form-sub">Sign in to your wedding dashboard.</p>
      <form method="post" action="?mode=login">
        <?= csrfField() ?>
        <input type="hidden" name="mode" value="login">
        <div class="field"><label>Email Address</label><input type="email" name="email" placeholder="your@email.com" required></div>
        <div class="field"><label>Password</label><input type="password" name="password" placeholder="Your password" required></div>
        <button type="submit" class="btn-submit">Sign In &rarr;</button>
      </form>
      <p class="switch-link">New here? <a href="?mode=signup">Create your wedding</a></p>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
