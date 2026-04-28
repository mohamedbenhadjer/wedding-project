<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

startSession();
if (currentCouple()) redirect('couple-dashboard.php');

$err = null;
$success = null;

$token = $_GET['token'] ?? '';
if (!$token) {
    die("Invalid or missing reset token.");
}

$pdo = db();
$stmt = $pdo->prepare('SELECT email FROM couples WHERE reset_token = ? AND reset_expires > NOW()');
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    die("This password reset link is invalid or has expired.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  verifyCsrf();
  $password = $_POST['password'] ?? '';
  $confirm = $_POST['password_confirm'] ?? '';

  if (strlen($password) < 6) {
    $err = 'Password must be at least 6 characters.';
  } elseif ($password !== $confirm) {
    $err = 'Passwords do not match.';
  } else {
    // Update password
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $pdo->prepare('UPDATE couples SET password = ?, reset_token = NULL, reset_expires = NULL WHERE email = ?')
        ->execute([$hashed, $user['email']]);
    
    $success = 'Your password has been reset successfully. You can now <a href="couple-login.php">login</a>.';
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset Password - Forever Together</title>
<style>
/* CSS Reset and Styling matches exactly with forgot-password.php */
:root{--ink:#1a1914;--muted:#6b675d;--bg:#f5f3ef;--gold:#c9ad87;--err:#bc4c4c;--sans:system-ui,-apple-system,sans-serif;--serif:"PP Editorial New",Georgia,serif;--cormo:"Cormorant Garamond",serif}
*{box-sizing:border-box;margin:0;padding:0}
body{background:var(--bg);color:var(--ink);font-family:var(--sans);display:grid;grid-template-columns:1fr 1fr;min-height:100vh}
.left{background:var(--ink);color:#fff;padding:64px;display:flex;flex-direction:column;justify-content:space-between;position:relative;overflow:hidden}
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
.form-title{font-family:var(--serif);font-size:30px;font-weight:400;color:var(--ink);margin-bottom:6px}
.form-sub{font-size:14px;color:var(--muted);font-weight:300;margin-bottom:30px;line-height:1.65}
.field{margin-bottom:18px}
.field label{display:block;font-size:11px;font-weight:500;letter-spacing:0.12em;text-transform:uppercase;color:var(--muted);margin-bottom:7px}
.field input{width:100%;padding:12px 14px;font-family:var(--sans);font-size:14px;color:var(--ink);background:#fff;border:1px solid #ddd6c8;border-radius:2px;outline:none;transition:border-color 0.2s,box-shadow 0.2s}
.field input:focus{border-color:var(--gold);box-shadow:0 0 0 3px rgba(201,173,135,0.12)}
.field input::placeholder{color:#bbb5ac}
.alert{padding:11px 14px;border-radius:2px;font-size:13px;margin-bottom:16px;background:#fdf0f0;border:1px solid #e8c4c4;color:var(--err)}
.success-alert{padding:11px 14px;border-radius:2px;font-size:13px;margin-bottom:16px;background:#f0fdf4;border:1px solid #c4e8c6;color:#2e6b30}
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
    <p class="left-desc">Set your new password to get back to planning.</p>
  </div>
</div>

<div class="right">
  <div class="form-card">
    <h2 class="form-title">Reset Password</h2>
    <p class="form-sub">Please enter your new password below. It must be at least 6 characters long.</p>

    <?php if ($err): ?>
      <div class="alert"><?= htmlspecialchars($err) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="success-alert"><?= $success ?></div>
    <?php else: ?>
      <form method="post">
        <?= csrfField() ?>
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
        <div class="field">
          <label>New Password</label>
          <input type="password" name="password" placeholder="At least 6 characters" required>
        </div>
        
        <div class="field">
          <label>Confirm Password</label>
          <input type="password" name="password_confirm" placeholder="Type password again" required>
        </div>

        <button type="submit" class="btn-submit">Reset Password &rarr;</button>
      </form>
    <?php endif; ?>
  </div>
</div>
</body>
</html>