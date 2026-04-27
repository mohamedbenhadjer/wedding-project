<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/themes.php';

$session = requireGuest();

$stmt = db()->prepare('SELECT * FROM weddings WHERE id = ?');
$stmt->execute([$session['wedding_id']]);
$wedding = $stmt->fetch();

if (!$wedding) {
  logoutAll();
  redirect('guest-login.php');
}

$today  = new DateTime('today');
$wDate  = new DateTime($wedding['wedding_date']);
$isPast = $wDate <= $today;

$diff        = $today->diff($wDate);
$cdDays      = $isPast ? 0 : (int) $diff->format('%a');
$cdHours     = 23 - (int) date('H');
$cdMinutes   = 59 - (int) date('i');

$photoStmt = db()->prepare('SELECT * FROM photos WHERE wedding_id = ? AND guest_username = ? ORDER BY uploaded_at DESC');
$photoStmt->execute([$session['wedding_id'], $session['username']]);
$myPhotos = $photoStmt->fetchAll();

$themeKey = $wedding['theme'] ?? 'dark_romantic';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Your Invitation &mdash; Forever Together</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;1,400&family=Cormorant+Garamond:ital,wght@0,300;0,400;1,300;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style><?= themeCss($themeKey) ?></style>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--serif:'Playfair Display',Georgia,serif;--cormo:'Cormorant Garamond',Georgia,serif;--sans:'DM Sans',system-ui,sans-serif}
html{scroll-behavior:smooth}
body{font-family:var(--sans);background:var(--bg);color:var(--text);min-height:100vh}
nav{position:fixed;top:0;left:0;right:0;z-index:100;display:flex;align-items:center;justify-content:space-between;padding:20px 52px;background:rgba(0,0,0,0.45);backdrop-filter:blur(16px);border-bottom:1px solid var(--border)}
.nav-logo{font-family:var(--serif);font-style:italic;font-size:19px;color:var(--text);opacity:0.7;text-decoration:none}
.nav-right{display:flex;align-items:center;gap:16px}
.nav-user{font-size:13px;color:var(--muted)}
.nav-user strong{color:var(--text);font-weight:500}
.btn{padding:9px 20px;font-family:var(--sans);font-size:12px;font-weight:500;letter-spacing:0.1em;text-transform:uppercase;border-radius:2px;border:none;cursor:pointer;transition:all 0.22s;text-decoration:none;display:inline-block}
.btn-accent{background:var(--accent);color:var(--bg)}
.btn-accent:hover{background:var(--accent-dark)}
.btn-ghost{background:transparent;color:var(--muted);border:1px solid var(--border)}
.btn-ghost:hover{border-color:var(--accent);color:var(--text)}
.hero{min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:120px 40px 80px;position:relative;overflow:hidden}
.hero-bg{position:absolute;inset:0;background:radial-gradient(ellipse 70% 60% at 20% 100%,rgba(201,173,135,0.09) 0%,transparent 55%),radial-gradient(ellipse 50% 50% at 80% 0%,rgba(201,173,135,0.06) 0%,transparent 50%),var(--bg)}
.ring{position:absolute;border-radius:50%;border:1px solid var(--border);pointer-events:none;top:50%;left:50%;transform:translate(-50%,-50%)}
.r1{width:480px;height:480px}.r2{width:800px;height:800px}.r3{width:1150px;height:1150px}
.hero-content{position:relative;z-index:1;max-width:680px}
.hero-eyebrow{font-size:10px;letter-spacing:0.28em;text-transform:uppercase;color:var(--accent);margin-bottom:18px;font-weight:500}
.hero-title{font-family:var(--cormo);font-size:clamp(56px,7vw,104px);font-weight:300;line-height:0.95;letter-spacing:-0.01em;margin-bottom:12px}
.hero-title em{font-style:italic;opacity:0.4}
.hero-names{font-family:var(--serif);font-style:italic;font-size:clamp(22px,3vw,36px);color:var(--accent);margin-bottom:28px}
.hero-meta{display:flex;gap:32px;justify-content:center;flex-wrap:wrap;margin-bottom:40px}
.meta-item{text-align:center}
.meta-label{font-size:10px;letter-spacing:0.2em;text-transform:uppercase;color:var(--muted);margin-bottom:6px;font-weight:500}
.meta-value{font-family:var(--cormo);font-size:22px;color:var(--text)}
.hero-actions{display:flex;gap:12px;justify-content:center;flex-wrap:wrap}
.status-badge{display:inline-flex;align-items:center;gap:8px;padding:10px 20px;border-radius:100px;font-size:12px;font-weight:500;letter-spacing:0.08em;margin-bottom:28px}
.badge-future{background:rgba(201,173,135,0.1);border:1px solid rgba(201,173,135,0.25);color:var(--accent)}
.badge-past{background:rgba(107,143,113,0.15);border:1px solid rgba(107,143,113,0.3);color:#8fbe96}
.badge-dot{width:7px;height:7px;border-radius:50%}
.badge-future .badge-dot{background:var(--accent)}
.badge-past .badge-dot{background:#8fbe96}
.countdown{display:flex;gap:24px;justify-content:center;margin-bottom:40px}
.cd-item{text-align:center;min-width:64px}
.cd-num{font-family:var(--cormo);font-size:52px;font-weight:300;line-height:1;color:var(--text)}
.cd-lbl{font-size:10px;letter-spacing:0.16em;text-transform:uppercase;color:var(--muted);margin-top:4px}
.cd-sep{font-family:var(--cormo);font-size:40px;color:var(--border);align-self:flex-start;padding-top:8px}
.main-wrap{max-width:1100px;margin:0 auto;padding:0 52px 80px}
.section{margin-bottom:56px}
.sec-label{font-size:10px;letter-spacing:0.24em;text-transform:uppercase;color:var(--accent);margin-bottom:14px;font-weight:500}
.sec-title{font-family:var(--cormo);font-size:clamp(32px,3.5vw,52px);font-weight:300;line-height:1.1;margin-bottom:8px}
.sec-sub{font-size:15px;color:var(--muted);font-weight:300;line-height:1.75;margin-bottom:28px}
.upload-card{background:var(--surface);border:1px solid var(--border);border-radius:3px;overflow:hidden}
.uc-head{padding:24px 28px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px}
.uc-title{font-family:var(--serif);font-size:18px;font-weight:400}
.uc-body{padding:28px}
.drop-zone{border:1.5px dashed var(--border);border-radius:3px;padding:44px 24px;text-align:center;cursor:pointer;transition:all 0.25s}
.drop-zone:hover,.drop-zone.over{border-color:var(--accent);background:rgba(201,173,135,0.04)}
.drop-lbl{font-size:15px;color:var(--muted);font-weight:300;line-height:1.65}
.drop-lbl strong{color:var(--text);font-weight:500}
.drop-hint{font-size:11px;color:var(--muted);letter-spacing:0.08em;text-transform:uppercase;margin-top:6px;opacity:0.6}
#file-input{display:none}
.thumb-strip{display:flex;gap:10px;flex-wrap:wrap;margin-top:16px}
.thumb{width:80px;height:80px;border-radius:2px;overflow:hidden;position:relative;border:1px solid var(--border);flex-shrink:0}
.thumb img{width:100%;height:100%;object-fit:cover}
.thumb-x{position:absolute;top:3px;right:3px;width:18px;height:18px;background:rgba(16,14,10,0.75);color:#fff;border:none;border-radius:50%;cursor:pointer;font-size:11px;display:flex;align-items:center;justify-content:center;opacity:0;transition:opacity 0.2s}
.thumb:hover .thumb-x{opacity:1}
.up-field{margin-bottom:16px}
.up-field label{display:block;font-size:11px;font-weight:500;letter-spacing:0.12em;text-transform:uppercase;color:var(--muted);margin-bottom:7px}
.up-field textarea{width:100%;padding:12px 14px;font-family:var(--sans);font-size:14px;color:var(--text);background:var(--bg);border:1px solid var(--border);border-radius:2px;outline:none;resize:none;min-height:80px;line-height:1.65}
.up-field textarea:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(201,173,135,0.1)}
.prog-wrap{height:2px;background:var(--border);border-radius:2px;overflow:hidden;display:none;margin-top:12px}
.prog-fill{height:100%;background:var(--accent);width:0%;transition:width 0.3s}
.btn-upload{padding:14px 32px;font-family:var(--sans);font-size:13px;font-weight:500;letter-spacing:0.1em;text-transform:uppercase;background:var(--accent);color:var(--bg);border:none;border-radius:2px;cursor:pointer;transition:background 0.2s;margin-top:8px}
.btn-upload:hover{background:var(--accent-dark)}
.btn-upload:disabled{opacity:0.4;cursor:not-allowed}
.my-photos-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px;margin-top:16px}
.mphoto{position:relative;border-radius:2px;overflow:hidden;cursor:pointer;background:var(--surface)}
.mphoto img{width:100%;display:block;transition:transform 0.5s}
.mphoto:hover img{transform:scale(1.04)}
.mphoto-overlay{position:absolute;inset:0;background:linear-gradient(transparent,rgba(0,0,0,0.65));opacity:0;transition:opacity 0.35s}
.mphoto:hover .mphoto-overlay{opacity:1}
.mphoto-info{position:absolute;bottom:0;left:0;right:0;padding:14px 16px;z-index:2;transform:translateY(4px);opacity:0;transition:all 0.35s}
.mphoto:hover .mphoto-info{transform:none;opacity:1}
.mphoto-msg{font-family:var(--cormo);font-style:italic;font-size:16px;color:rgba(255,255,255,0.9)}
.mphoto-date{font-size:11px;color:rgba(255,255,255,0.55);margin-top:4px}
.empty-state{text-align:center;padding:60px 20px;color:var(--muted)}
.locked-card{background:var(--surface);border:1px solid var(--border);border-radius:3px;padding:48px;text-align:center}
.lock-icon{font-size:40px;margin-bottom:16px;opacity:0.4}
.lock-title{font-family:var(--serif);font-size:24px;font-weight:400;margin-bottom:8px}
.lock-sub{font-size:15px;color:var(--muted);font-weight:300;max-width:400px;margin:0 auto;line-height:1.75}
.lb{display:none;position:fixed;inset:0;z-index:500;background:rgba(10,8,6,0.96);align-items:center;justify-content:center;padding:40px}
.lb.open{display:flex}
.lb-img{max-width:min(800px,90vw);max-height:90vh;object-fit:contain;border-radius:2px}
.lb-close{position:absolute;top:20px;right:24px;width:40px;height:40px;border-radius:50%;background:rgba(255,255,255,0.08);border:none;color:rgba(255,255,255,0.6);font-size:20px;cursor:pointer}
.toast{position:fixed;bottom:28px;left:50%;transform:translateX(-50%) translateY(16px);background:var(--accent);color:var(--bg);padding:11px 28px;border-radius:100px;font-size:14px;font-weight:500;opacity:0;transition:all 0.3s;z-index:9999;pointer-events:none;white-space:nowrap}
.toast.show{opacity:1;transform:translateX(-50%) translateY(0)}
@media(max-width:768px){nav{padding:16px 20px}.main-wrap{padding:0 20px 60px}.hero{padding:100px 20px 60px}.countdown{gap:12px}.cd-num{font-size:36px}}
</style>
</head>
<body>

<nav>
  <a href="index.php" class="nav-logo">Forever Together</a>
  <div class="nav-right">
    <span class="nav-user">Hello, <strong><?= h($session['name']) ?></strong></span>
    <a class="btn btn-ghost" href="guest-logout.php">Sign Out</a>
  </div>
</nav>

<div class="hero">
  <div class="hero-bg"></div>
  <div class="ring r1"></div><div class="ring r2"></div><div class="ring r3"></div>
  <div class="hero-content">
    <p class="hero-eyebrow">You're Invited</p>
    <h1 class="hero-title">The Wedding of<br><em>a Lifetime</em></h1>
    <p class="hero-names"><?= h($wedding['name1']) ?> &amp; <?= h($wedding['name2']) ?></p>
    <div class="hero-meta">
      <div class="meta-item"><div class="meta-label">Date</div><div class="meta-value"><?= h(date('j F Y', strtotime($wedding['wedding_date']))) ?></div></div>
      <div class="meta-item"><div class="meta-label">Venue</div><div class="meta-value"><?= h($wedding['venue'] ?: 'Venue TBD') ?></div></div>
    </div>

    <?php if ($isPast): ?>
      <div class="status-badge badge-past"><div class="badge-dot"></div>Wedding has taken place &mdash; share your photos!</div>
    <?php else: ?>
      <div class="status-badge badge-future"><div class="badge-dot"></div>Wedding coming up!</div>
      <div class="countdown">
        <div class="cd-item"><div class="cd-num"><?= $cdDays ?></div><div class="cd-lbl">Days</div></div>
        <div class="cd-sep">&middot;</div>
        <div class="cd-item"><div class="cd-num"><?= max(0, $cdHours) ?></div><div class="cd-lbl">Hours</div></div>
        <div class="cd-sep">&middot;</div>
        <div class="cd-item"><div class="cd-num"><?= max(0, $cdMinutes) ?></div><div class="cd-lbl">Minutes</div></div>
      </div>
    <?php endif; ?>

    <div class="hero-actions">
      <?php if ($isPast): ?><a href="#upload" class="btn btn-accent">Upload My Photos &darr;</a><?php endif; ?>
      <a href="#my-photos" class="btn btn-ghost">My Photos &darr;</a>
    </div>
  </div>
</div>

<div class="main-wrap">
  <div class="section" id="upload" style="scroll-margin-top:80px">
    <p class="sec-label">Share the Moment</p>
    <h2 class="sec-title">Upload Your Memories</h2>
    <p class="sec-sub"><?= $isPast ? 'Your photos will be saved to your personal gallery.' : 'Photo uploads will open after the wedding date.' ?></p>

    <?php if ($isPast): ?>
      <div class="upload-card">
        <div class="uc-head">
          <span class="uc-title">Add Your Photos</span>
          <span style="font-size:13px;color:var(--muted)">Up to 10 photos per upload</span>
        </div>
        <div class="uc-body">
          <div class="drop-zone" id="drop-zone">
            <p class="drop-lbl"><strong>Click to choose photos</strong><br>or drag &amp; drop here</p>
            <p class="drop-hint">JPG &middot; PNG &middot; WEBP</p>
          </div>
          <input type="file" id="file-input" accept="image/*" multiple>
          <div class="thumb-strip" id="thumb-strip"></div>
          <div style="margin-top:20px">
            <div class="up-field"><label>A note for the couple</label><textarea id="up-msg" placeholder="A wish, a memory, something from your heart&hellip;" maxlength="300"></textarea></div>
            <div class="prog-wrap" id="prog-wrap"><div class="prog-fill" id="prog-fill"></div></div>
            <button class="btn-upload" id="btn-upload" disabled>Share with <?= h($wedding['name1']) ?> &amp; <?= h($wedding['name2']) ?></button>
          </div>
        </div>
      </div>
    <?php else: ?>
      <div class="locked-card">
        <div class="lock-icon">&#128274;</div>
        <h3 class="lock-title">Photo uploads open after the wedding</h3>
        <p class="lock-sub">Come back after <strong><?= h(date('j F Y', strtotime($wedding['wedding_date']))) ?></strong> to share your photos with the couple.</p>
      </div>
    <?php endif; ?>
  </div>

  <div class="section" id="my-photos" style="scroll-margin-top:80px">
    <p class="sec-label">Your Contribution</p>
    <h2 class="sec-title">My Photos</h2>
    <p class="sec-sub">Photos you've uploaded to this wedding.</p>
    <div id="my-photos-grid">
      <?php if (!$myPhotos): ?>
        <div class="empty-state"><p style="font-size:15px;font-weight:300">You haven't uploaded any photos yet.</p></div>
      <?php else: ?>
        <div class="my-photos-grid">
          <?php foreach ($myPhotos as $p): ?>
            <div class="mphoto" data-src="<?= h(UPLOADS_URL . '/' . $p['file_path']) ?>">
              <img src="<?= h(UPLOADS_URL . '/' . $p['file_path']) ?>" alt="" loading="lazy">
              <div class="mphoto-overlay"></div>
              <div class="mphoto-info">
                <?php if (!empty($p['message'])): ?><p class="mphoto-msg">"<?= h($p['message']) ?>"</p><?php endif; ?>
                <p class="mphoto-date"><?= h(date('j M Y', strtotime($p['uploaded_at']))) ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="lb" id="lb"><button class="lb-close" id="lb-close">&times;</button><img class="lb-img" id="lb-img" alt=""></div>
<div class="toast" id="toast"></div>

<script>
const CSRF = <?= json_encode(csrfToken()) ?>;
const IS_PAST = <?= $isPast ? 'true' : 'false' ?>;

function toast(m){ const el=document.getElementById('toast'); el.textContent=m; el.classList.add('show'); setTimeout(()=>el.classList.remove('show'),3500); }

// Lightbox
document.querySelectorAll('.mphoto').forEach(el=>{
  el.addEventListener('click',()=>{
    document.getElementById('lb-img').src = el.dataset.src;
    document.getElementById('lb').classList.add('open');
    document.body.style.overflow='hidden';
  });
});
document.getElementById('lb-close').addEventListener('click', closeLb);
document.getElementById('lb').addEventListener('click', e=>{ if(e.target.id==='lb') closeLb(); });
document.addEventListener('keydown', e=>{ if(e.key==='Escape') closeLb(); });
function closeLb(){ document.getElementById('lb').classList.remove('open'); document.body.style.overflow=''; }

if (IS_PAST) {
  let pending = [];
  const fi = document.getElementById('file-input');
  const dz = document.getElementById('drop-zone');
  const strip = document.getElementById('thumb-strip');
  const btn = document.getElementById('btn-upload');

  dz.addEventListener('click', ()=> fi.click());
  fi.addEventListener('change', ()=> addFiles([...fi.files]));
  dz.addEventListener('dragover', e=>{ e.preventDefault(); dz.classList.add('over'); });
  dz.addEventListener('dragleave', ()=> dz.classList.remove('over'));
  dz.addEventListener('drop', e=>{
    e.preventDefault(); dz.classList.remove('over');
    addFiles([...e.dataTransfer.files].filter(f=>f.type.startsWith('image/')));
  });
  btn.addEventListener('click', doUpload);

  function addFiles(files){
    pending.push(...files.slice(0, 10-pending.length));
    render();
  }
  function render(){
    strip.innerHTML = pending.map((f,i)=>{
      const url = URL.createObjectURL(f);
      return `<div class="thumb"><img src="${url}"><button class="thumb-x" data-i="${i}">&times;</button></div>`;
    }).join('');
    strip.querySelectorAll('.thumb-x').forEach(b=>{
      b.addEventListener('click', e=>{ e.stopPropagation(); pending.splice(+b.dataset.i,1); render(); });
    });
    btn.disabled = pending.length===0;
  }

  async function doUpload(){
    if (!pending.length) return;
    btn.disabled=true; btn.textContent='Uploading…';
    const pw = document.getElementById('prog-wrap');
    const pf = document.getElementById('prog-fill');
    pw.style.display='block';
    const fd = new FormData();
    fd.append('_csrf', CSRF);
    fd.append('message', document.getElementById('up-msg').value.trim());
    pending.forEach(f => fd.append('photos[]', f));
    try {
      const res = await fetch('api/upload-photo.php', { method:'POST', body: fd });
      const data = await res.json();
      if (!data.ok) throw new Error(data.error || 'Upload failed');
      pf.style.width='100%';
      toast('Photos shared — thank you!');
      setTimeout(()=> location.reload(), 900);
    } catch (e) {
      toast(e.message);
      btn.disabled=false;
      btn.textContent='Share with <?= addslashes(h($wedding['name1'])) ?> & <?= addslashes(h($wedding['name2'])) ?>';
      pw.style.display='none'; pf.style.width='0%';
    }
  }
}
</script>
</body>
</html>
