<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

startSession();
if (currentGuest()) redirect('guest-portal.php');

$err = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  verifyCsrf();
  $username = strtolower(trim($_POST['username'] ?? ''));
  $password = $_POST['password'] ?? '';
  $stmt = db()->prepare('SELECT * FROM guests WHERE username = ?');
  $stmt->execute([$username]);
  $g = $stmt->fetch();
  if (!$g || !password_verify($password, $g['password_hash'])) {
    $err = 'Incorrect username or password. Check your invitation card.';
  } else {
    setGuestSession([
      'username'   => $g['username'],
      'name'       => $g['name'],
      'email'      => $g['email'] ?? '',
      'wedding_id' => $g['wedding_id'],
    ]);
    redirect('guest-portal.php');
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Guest Login — Forever Together</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;1,400&family=Cormorant+Garamond:ital,wght@0,300;1,300&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--ink:#16140f;--gold:#c9ad87;--gold-lt:#ede4d6;--muted:#8a8278;--bg:#f5f2ed;--err:#b85555;--serif:'Playfair Display',Georgia,serif;--cormo:'Cormorant Garamond',Georgia,serif;--sans:'DM Sans',system-ui,sans-serif}
body{font-family:var(--sans);background:var(--ink);min-height:100vh;display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden}
.bg{position:absolute;inset:0;background:radial-gradient(ellipse 70% 60% at 20% 100%,rgba(201,173,135,0.09) 0%,transparent 55%),radial-gradient(ellipse 50% 50% at 80% 0%,rgba(201,173,135,0.06) 0%,transparent 50%)}
.ring{position:absolute;border-radius:50%;border:1px solid rgba(201,173,135,0.08);top:50%;left:50%;transform:translate(-50%,-50%);pointer-events:none}
.r1{width:500px;height:500px}.r2{width:850px;height:850px}.r3{width:1200px;height:1200px}
.card{position:relative;z-index:1;background:var(--bg);width:min(420px,90vw);border-radius:4px;overflow:hidden;box-shadow:0 40px 100px rgba(0,0,0,0.4)}
.card-top{background:var(--ink);padding:40px 40px 32px;text-align:center;border-bottom:1px solid rgba(201,173,135,0.1)}
.card-logo{font-family:var(--serif);font-style:italic;font-size:18px;color:rgba(255,255,255,0.5);text-decoration:none;display:block;margin-bottom:24px}
.card-eyebrow{font-size:10px;letter-spacing:0.24em;text-transform:uppercase;color:var(--gold);margin-bottom:12px;font-weight:500}
.card-title{font-family:var(--cormo);font-size:42px;font-weight:300;color:#fff;line-height:1.1}
.card-title em{font-style:italic;color:rgba(255,255,255,0.35)}
.card-body{padding:36px 40px}
.form-sub{font-size:14px;color:var(--muted);font-weight:300;margin-bottom:28px;line-height:1.65}
.field{margin-bottom:18px}
.field label{display:block;font-size:11px;font-weight:500;letter-spacing:0.12em;text-transform:uppercase;color:var(--muted);margin-bottom:7px}
.field input{width:100%;padding:13px 14px;font-family:var(--sans);font-size:14px;color:var(--ink);background:#fff;border:1px solid #ddd6c8;border-radius:2px;outline:none;transition:border-color 0.2s,box-shadow 0.2s}
.field input:focus{border-color:var(--gold);box-shadow:0 0 0 3px rgba(201,173,135,0.12)}
.field input::placeholder{color:#bbb5ac}
.alert{padding:11px 14px;border-radius:2px;font-size:13px;margin-bottom:16px;background:#fdf0f0;border:1px solid #e8c4c4;color:var(--err)}
.btn-submit{width:100%;padding:15px;font-family:var(--sans);font-size:13px;font-weight:500;letter-spacing:0.1em;text-transform:uppercase;background:var(--ink);color:#fff;border:none;border-radius:2px;cursor:pointer;transition:background 0.2s;margin-top:4px}
.btn-submit:hover{background:#2a2820}
.card-footer{padding:0 40px 32px;text-align:center;font-size:13px;color:var(--muted)}
.card-footer a{color:var(--ink);font-weight:500;text-decoration:none}
.card-footer a:hover{text-decoration:underline}
.hint{background:rgba(201,173,135,0.08);border:1px solid rgba(201,173,135,0.18);border-radius:2px;padding:12px 14px;font-size:12px;color:var(--muted);line-height:1.6;margin-bottom:20px}
</style>
</head>
<body>
<div class="bg"></div>
<div class="ring r1"></div><div class="ring r2"></div><div class="ring r3"></div>

<div class="card">
  <div class="card-top">
    <a href="index.php" class="card-logo">Forever Together</a>
    <p class="card-eyebrow">Guest Access</p>
    <h1 class="card-title">Welcome,<br><em>dear guest</em></h1>
  </div>
  <div class="card-body">
    <p class="form-sub">Sign in with the credentials from your invitation card.</p>
    <div class="hint">Your username and password were printed on your invitation. Contact the couple if you need help.</div>
    <?php if ($err): ?><div class="alert"><?= h($err) ?></div><?php endif; ?>
    <form method="post">
      <?= csrfField() ?>
      <div class="field"><label>Username</label><input name="username" placeholder="e.g. john482" required autocomplete="username"></div>
      <div class="field"><label>Password</label><input type="password" name="password" placeholder="Your invitation password" required autocomplete="current-password"></div>
      <button type="submit" class="btn-submit">Enter the Wedding &rarr;</button>
    </form>
  </div>
  <div class="card-footer">
    <a href="couple-signup.php">Planning a wedding?</a> &nbsp;&middot;&nbsp; <a href="index.php">Back to home</a>
  </div>
</div>
</body>
</html>
