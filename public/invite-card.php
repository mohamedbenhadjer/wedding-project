<?php
require_once __DIR__ . '/../includes/auth.php';

$couple = requireCouple();
$username = $_GET['guest'] ?? '';

$stmt = db()->prepare(
  'SELECT g.*, w.name1, w.name2, w.wedding_date, w.venue, w.card_style
     FROM guests g JOIN weddings w ON w.id = g.wedding_id
    WHERE g.username = ? AND w.id = ?'
);
$stmt->execute([$username, $couple['wedding_id']]);
$data = $stmt->fetch();
if (!$data) { http_response_code(404); exit('Guest not found'); }

$style = in_array($data['card_style'] ?? 'dark', ['dark','cream','rose','sage'], true) ? $data['card_style'] : 'dark';
$palettes = [
  'dark'  => ['bg'=>'#16140f','surface'=>'#1e1b14','text'=>'#faf8f4','muted'=>'rgba(250,248,244,0.5)','accent'=>'#c9ad87','border'=>'rgba(201,173,135,0.25)'],
  'cream' => ['bg'=>'#faf6ef','surface'=>'#ffffff','text'=>'#2a2420','muted'=>'#8a7a6a','accent'=>'#b8965a','border'=>'#e6d9c8'],
  'rose'  => ['bg'=>'#fdf4f4','surface'=>'#ffffff','text'=>'#2a1820','muted'=>'#8a6870','accent'=>'#c4788a','border'=>'#e8cece'],
  'sage'  => ['bg'=>'#f0f4ef','surface'=>'#ffffff','text'=>'#1e2c1e','muted'=>'#6a826a','accent'=>'#6b8f71','border'=>'#c8d9c4'],
];
$p = $palettes[$style];

$dateLong = $data['wedding_date'] ? date('l, j F Y', strtotime($data['wedding_date'])) : 'Date TBD';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Invitation &mdash; <?= h($data['name']) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;1,400&family=Cormorant+Garamond:ital,wght@0,300;0,400;1,300;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:<?= $p['bg'] ?>; --surface:<?= $p['surface'] ?>;
  --text:<?= $p['text'] ?>; --muted:<?= $p['muted'] ?>;
  --accent:<?= $p['accent'] ?>; --border:<?= $p['border'] ?>;
  --serif:'Playfair Display',Georgia,serif;
  --cormo:'Cormorant Garamond',Georgia,serif;
  --sans:'DM Sans',system-ui,sans-serif;
}
body{font-family:var(--sans);background:#e9e4dc;color:var(--text);min-height:100vh;padding:40px 20px;display:flex;flex-direction:column;align-items:center}
.print-nav{width:100%;max-width:680px;display:flex;justify-content:space-between;align-items:center;margin-bottom:28px}
.pn-logo{font-family:var(--serif);font-style:italic;font-size:18px;color:#6a6258;text-decoration:none}
.btn{padding:9px 22px;font-family:var(--sans);font-size:12px;font-weight:500;letter-spacing:0.1em;text-transform:uppercase;border-radius:2px;border:none;cursor:pointer;background:#16140f;color:#fff;text-decoration:none;display:inline-block}
.btn-ghost{background:transparent;color:#6a6258;border:1px solid #c8bfb3;margin-right:8px}
@media print{
  * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
  .print-nav{display:none}
  body{background:#fff;padding:0}
}

.card{
  width:680px; max-width:100%;
  background:var(--bg); color:var(--text);
  border:1px solid var(--border); border-radius:4px;
  padding:56px 48px; position:relative; overflow:hidden;
  box-shadow:0 30px 80px rgba(0,0,0,0.2);
}
.card::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse 70% 40% at 50% 0%,rgba(201,173,135,0.08) 0%,transparent 60%);pointer-events:none}
.c-eyebrow{font-size:10px;letter-spacing:0.28em;text-transform:uppercase;color:var(--accent);text-align:center;margin-bottom:18px;font-weight:500}
.c-title{font-family:var(--cormo);font-size:clamp(38px,5vw,64px);font-weight:300;line-height:1;text-align:center;margin-bottom:18px}
.c-title em{font-style:italic;opacity:0.45}
.c-names{font-family:var(--serif);font-style:italic;font-size:clamp(22px,3vw,30px);color:var(--accent);text-align:center;margin-bottom:28px}
.c-rule{width:56px;height:1px;background:var(--accent);opacity:0.5;margin:0 auto 28px}
.c-meta{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:36px}
.c-meta > div{text-align:center;padding:14px;border:1px solid var(--border);border-radius:3px}
.c-meta .lbl{font-size:10px;letter-spacing:0.18em;text-transform:uppercase;color:var(--muted);font-weight:500;margin-bottom:6px}
.c-meta .val{font-family:var(--cormo);font-size:18px;color:var(--text)}
.c-body{font-family:var(--cormo);font-style:italic;font-size:18px;color:var(--muted);line-height:1.7;text-align:center;max-width:440px;margin:0 auto 36px}
.c-body strong{color:var(--text);font-style:normal;font-weight:500}

.login-box{
  background:var(--surface); border:1px solid var(--border); border-radius:3px;
  padding:20px 24px;
}
.login-box .lbl{font-size:10px;letter-spacing:0.22em;text-transform:uppercase;color:var(--accent);font-weight:500;margin-bottom:14px;text-align:center}
.login-row{display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px dashed var(--border);font-family:var(--sans);font-size:14px}
.login-row:last-child{border-bottom:none}
.login-row .k{font-size:11px;letter-spacing:0.14em;text-transform:uppercase;color:var(--muted);font-weight:500}
.login-row .v{font-family:'Courier New',monospace;font-weight:600;font-size:16px;color:var(--text);letter-spacing:0.06em}
.c-hint{font-size:11px;color:var(--muted);text-align:center;margin-top:14px;letter-spacing:0.04em}
.c-footer{font-family:var(--serif);font-style:italic;font-size:14px;color:var(--muted);text-align:center;margin-top:32px}
</style>
</head>
<body>

<div class="print-nav">
  <a href="couple-dashboard.php#guests" class="pn-logo">&larr; Back to dashboard</a>
  <div>
    <a href="couple-dashboard.php#guests" class="btn btn-ghost">Close</a>
    <button class="btn" onclick="window.print()">Print / Save PDF</button>
  </div>
</div>

<div class="card">
  <p class="c-eyebrow">You are invited</p>
  <h1 class="c-title">Our<br><em>Wedding</em></h1>
  <p class="c-names"><?= h($data['name1']) ?> &amp; <?= h($data['name2']) ?></p>
  <div class="c-rule"></div>

  <div class="c-meta">
    <div><div class="lbl">Date</div><div class="val"><?= h($dateLong) ?></div></div>
    <div><div class="lbl">Venue</div><div class="val"><?= h($data['venue'] ?: 'To be announced') ?></div></div>
  </div>

  <p class="c-body">Dearest <strong><?= h($data['name']) ?></strong>, we'd be honoured by your presence on our wedding day. Please sign in with the credentials below to see details and share your photos afterwards.</p>

  <div class="login-box">
    <p class="lbl">Your Sign-In</p>
    <div class="login-row"><span class="k">Site</span><span class="v">forever-together</span></div>
    <div class="login-row"><span class="k">Username</span><span class="v"><?= h($data['username']) ?></span></div>
    <div class="login-row"><span class="k">Password</span><span class="v"><?= h($data['password_plain']) ?></span></div>
  </div>
  <p class="c-hint">Visit the guest portal &middot; keep this card safe</p>

  <p class="c-footer">&mdash; with love, <?= h($data['name1']) ?> &amp; <?= h($data['name2']) ?> &mdash;</p>
</div>

</body>
</html>
