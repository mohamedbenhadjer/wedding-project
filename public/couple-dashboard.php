<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/themes.php';

$couple = requireCouple();
$weddingId = $couple['wedding_id'];

$pdo = db();
$wStmt = $pdo->prepare('SELECT * FROM weddings WHERE id = ?');
$wStmt->execute([$weddingId]);
$wedding = $wStmt->fetch();
if (!$wedding) { logoutAll(); redirect('couple-login.php'); }

$guestCount   = (int) $pdo->query("SELECT COUNT(*) FROM guests   WHERE wedding_id = " . $pdo->quote($weddingId))->fetchColumn();
$photoCount   = (int) $pdo->query("SELECT COUNT(*) FROM photos   WHERE wedding_id = " . $pdo->quote($weddingId))->fetchColumn();
$selCount     = (int) $pdo->query("SELECT COUNT(*) FROM memory_selections WHERE wedding_id = " . $pdo->quote($weddingId))->fetchColumn();

$daysUntil = daysUntil($wedding['wedding_date']);

$guestsStmt = $pdo->prepare('SELECT username, name, email, password_plain, created_at FROM guests WHERE wedding_id = ? ORDER BY created_at DESC');
$guestsStmt->execute([$weddingId]);
$guests = $guestsStmt->fetchAll();

$allPhotosStmt = $pdo->prepare(
  'SELECT p.id, p.file_path, p.message, p.uploaded_at, g.name AS guest_name
     FROM photos p JOIN guests g ON g.username = p.guest_username
    WHERE p.wedding_id = ? ORDER BY p.uploaded_at DESC'
);
$allPhotosStmt->execute([$weddingId]);
$allPhotos = $allPhotosStmt->fetchAll();

$selStmt = $pdo->prepare('SELECT photo_id FROM memory_selections WHERE wedding_id = ? ORDER BY position ASC');
$selStmt->execute([$weddingId]);
$selectedIds = $selStmt->fetchAll(PDO::FETCH_COLUMN);
$selectedSet = array_flip($selectedIds);

$recent = array_slice($allPhotos, 0, 8);
$themeKey = $wedding['theme'] ?? 'dark_romantic';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard &mdash; Forever Together</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;1,400&family=Cormorant+Garamond:ital,wght@0,300;1,300&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style><?= themeCss($themeKey) ?></style>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--sidebar:220px;--serif:'Playfair Display',Georgia,serif;--cormo:'Cormorant Garamond',Georgia,serif;--sans:'DM Sans',system-ui,sans-serif}
body{font-family:var(--sans);background:var(--bg);color:var(--text);min-height:100vh;display:flex}
.sidebar{width:var(--sidebar);flex-shrink:0;background:var(--bg2);border-right:1px solid var(--border);display:flex;flex-direction:column;padding:32px 0;position:fixed;top:0;left:0;bottom:0;z-index:100}
.sb-logo{font-family:var(--serif);font-style:italic;font-size:17px;color:var(--text);opacity:0.6;text-decoration:none;padding:0 24px;margin-bottom:36px;display:block}
.sb-section{font-size:10px;letter-spacing:0.2em;text-transform:uppercase;color:var(--muted);padding:0 24px;margin:20px 0 8px;font-weight:500}
.sb-item{display:flex;align-items:center;gap:12px;padding:11px 24px;font-size:14px;color:var(--muted);cursor:pointer;transition:all 0.2s;border-left:2px solid transparent;text-decoration:none}
.sb-item:hover{color:var(--text);background:rgba(201,173,135,0.06)}
.sb-item.active{color:var(--text);background:rgba(201,173,135,0.08);border-left-color:var(--accent)}
.sb-icon{width:16px;height:16px;background:var(--accent);opacity:0.5;border-radius:2px;flex-shrink:0}
.sb-item.active .sb-icon,.sb-item:hover .sb-icon{opacity:1}
.sb-bottom{margin-top:auto;padding:0 24px}
.sb-couple{background:rgba(201,173,135,0.08);border:1px solid var(--border);border-radius:3px;padding:14px;font-size:13px;color:var(--muted);margin-bottom:16px}
.sb-couple strong{display:block;color:var(--text);font-weight:500;font-family:var(--serif);font-size:15px;margin-bottom:2px}
.btn-logout{width:100%;padding:10px;font-family:var(--sans);font-size:12px;font-weight:500;letter-spacing:0.1em;text-transform:uppercase;background:transparent;color:var(--muted);border:1px solid var(--border);border-radius:2px;cursor:pointer;text-decoration:none;display:block;text-align:center}
.btn-logout:hover{color:var(--text);border-color:var(--accent)}
.main{margin-left:var(--sidebar);flex:1;min-height:100vh;display:flex;flex-direction:column}
.topbar{padding:20px 40px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;background:var(--bg2);position:sticky;top:0;z-index:50}
.topbar-title{font-family:var(--serif);font-size:18px;font-weight:400;color:var(--text)}
.topbar-right{display:flex;gap:10px;align-items:center}
.panel{display:none;flex:1;padding:40px;animation:fadeIn 0.3s ease}
.panel.active{display:block}
@keyframes fadeIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:none}}
.page-title{font-family:var(--cormo);font-size:clamp(36px,3.5vw,52px);font-weight:300;margin-bottom:6px}
.page-sub{font-size:15px;color:var(--muted);font-weight:300;margin-bottom:36px}
.stat-row{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:36px}
.stat{background:var(--surface);border:1px solid var(--border);border-radius:3px;padding:24px}
.stat-num{font-family:var(--cormo);font-size:48px;font-weight:300;color:var(--text);line-height:1}
.stat-lbl{font-size:12px;letter-spacing:0.1em;text-transform:uppercase;color:var(--muted);margin-top:6px}
.card{background:var(--surface);border:1px solid var(--border);border-radius:3px;padding:28px;margin-bottom:24px}
.card-title{font-family:var(--serif);font-size:20px;font-weight:400;margin-bottom:6px;color:var(--text)}
.card-sub{font-size:14px;color:var(--muted);font-weight:300;margin-bottom:22px}
.divider{height:1px;background:var(--border);margin:22px 0}
.field{margin-bottom:16px}
.field label{display:block;font-size:11px;font-weight:500;letter-spacing:0.12em;text-transform:uppercase;color:var(--muted);margin-bottom:7px}
.field input,.field select,.field textarea{width:100%;padding:12px 14px;font-family:var(--sans);font-size:14px;color:var(--text);background:var(--bg);border:1px solid var(--border);border-radius:2px;outline:none}
.field input:focus,.field select:focus,.field textarea:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(201,173,135,0.1)}
.field-row{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.btn{padding:11px 24px;font-family:var(--sans);font-size:12px;font-weight:500;letter-spacing:0.1em;text-transform:uppercase;border-radius:2px;border:none;cursor:pointer;transition:all 0.22s;display:inline-flex;align-items:center;gap:8px;text-decoration:none}
.btn-accent{background:var(--accent);color:var(--bg)}
.btn-accent:hover{background:var(--accent-dark)}
.btn-dark{background:var(--bg);color:var(--text);border:1px solid var(--border)}
.btn-dark:hover{border-color:var(--accent)}
.btn-sm{padding:8px 16px;font-size:11px}
.theme-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:12px}
.theme-card{border:2px solid var(--border);border-radius:3px;padding:16px 12px;cursor:pointer;transition:all 0.25s;text-align:center;background:transparent;font-family:inherit}
.theme-card:hover{border-color:var(--accent);transform:translateY(-2px)}
.theme-card.selected{border-color:var(--accent)}
.theme-swatches{display:flex;gap:4px;justify-content:center;margin-bottom:10px}
.swatch{width:22px;height:22px;border-radius:50%}
.theme-name{font-size:12px;color:var(--muted);font-weight:400}
.theme-card.selected .theme-name{color:var(--accent)}
.csv-drop{border:1.5px dashed var(--border);border-radius:3px;padding:44px 24px;text-align:center;cursor:pointer}
.csv-drop:hover,.csv-drop.over{border-color:var(--accent);background:rgba(201,173,135,0.04)}
.csv-drop p{font-size:15px;color:var(--muted);font-weight:300;margin-top:12px}
.csv-drop p strong{color:var(--text);font-weight:500}
.csv-drop small{font-size:11px;color:var(--muted);letter-spacing:0.06em;text-transform:uppercase;opacity:0.6}
.guest-table{width:100%;border-collapse:collapse;margin-top:20px}
.guest-table th{font-size:10px;letter-spacing:0.14em;text-transform:uppercase;color:var(--muted);padding:10px 14px;text-align:left;border-bottom:1px solid var(--border)}
.guest-table td{padding:11px 14px;font-size:14px;border-bottom:1px solid rgba(201,173,135,0.07);vertical-align:middle}
.cred-badge{font-family:monospace;font-size:12px;background:var(--bg);border:1px solid var(--border);padding:3px 8px;border-radius:2px;color:var(--accent)}
.photo-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:12px;margin-top:20px}
.photo-item{position:relative;aspect-ratio:1;overflow:hidden;border-radius:2px;cursor:pointer;border:2px solid transparent;transition:border-color 0.2s}
.photo-item.selected{border-color:var(--accent)}
.photo-item img{width:100%;height:100%;object-fit:cover;transition:transform 0.4s}
.photo-item:hover img{transform:scale(1.05)}
.photo-item-overlay{position:absolute;inset:0;background:rgba(0,0,0,0.4);display:flex;align-items:center;justify-content:center;opacity:0;transition:opacity 0.25s}
.photo-item:hover .photo-item-overlay,.photo-item.selected .photo-item-overlay{opacity:1}
.photo-check{width:28px;height:28px;border-radius:50%;border:2px solid #fff;display:flex;align-items:center;justify-content:center;font-size:14px;color:#fff}
.photo-item.selected .photo-check{background:var(--accent);border-color:var(--accent);color:var(--bg)}
.photo-meta{position:absolute;bottom:0;left:0;right:0;padding:8px 10px;background:linear-gradient(transparent,rgba(0,0,0,0.7));font-size:11px;color:rgba(255,255,255,0.85)}
.empty{text-align:center;padding:60px 20px;color:var(--muted)}
.empty p{font-size:15px;font-weight:300;margin-bottom:20px}
.invite-styles{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px}
.invite-style{border:2px solid var(--border);border-radius:3px;padding:14px;cursor:pointer;text-align:center;font-size:13px;color:var(--muted);background:transparent;font-family:inherit}
.invite-style:hover,.invite-style.selected{border-color:var(--accent);color:var(--text)}
.invite-preview-mini{height:80px;border-radius:2px;margin-bottom:8px;display:flex;align-items:center;justify-content:center;font-family:var(--serif);font-size:10px;letter-spacing:0.1em}
.ip-dark{background:#16140f;border:1px solid rgba(201,173,135,0.3);color:rgba(201,173,135,0.8)}
.ip-cream{background:#faf6ef;border:1px solid #e6d9c8;color:#8a7a6a}
.ip-rose{background:#fdf4f4;border:1px solid #e8cece;color:#c4788a}
.ip-sage{background:#f0f4ef;border:1px solid #c8d9c4;color:#6b8f71}
.toast{position:fixed;bottom:28px;left:50%;transform:translateX(-50%) translateY(16px);background:var(--accent);color:var(--bg);padding:11px 28px;border-radius:100px;font-size:14px;font-weight:500;opacity:0;transition:all 0.3s;z-index:9999;pointer-events:none;white-space:nowrap}
.toast.show{opacity:1;transform:translateX(-50%) translateY(0)}
.video-box{position:relative;width:100%;aspect-ratio:16/9;background:#000;border-radius:2px;overflow:hidden}
.video-box video{width:100%;height:100%;display:block;object-fit:contain}
.video-placeholder{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,0.4);font-size:14px;font-weight:300}
@media(max-width:768px){.sidebar{display:none}.main{margin-left:0}.stat-row{grid-template-columns:1fr 1fr}.theme-grid{grid-template-columns:repeat(3,1fr)}.invite-styles{grid-template-columns:1fr 1fr}}
</style>
</head>
<body>

<aside class="sidebar">
  <a href="index.php" class="sb-logo">Forever Together</a>

  <span class="sb-section">Wedding</span>
  <a class="sb-item" data-panel="overview"><span class="sb-icon"></span>Overview</a>
  <a class="sb-item" data-panel="settings"><span class="sb-icon"></span>Settings &amp; Theme</a>

  <span class="sb-section">Guests</span>
  <a class="sb-item" data-panel="guests"><span class="sb-icon"></span>Guest List</a>
  <a class="sb-item" data-panel="invites"><span class="sb-icon"></span>Invite Cards</a>

  <span class="sb-section">Memories</span>
  <a class="sb-item" data-panel="photos"><span class="sb-icon"></span>All Photos</a>
  <a class="sb-item" data-panel="memory"><span class="sb-icon"></span>Memory Page</a>
  <a class="sb-item" data-panel="video"><span class="sb-icon"></span>Video Slideshow</a>

  <div class="sb-bottom">
    <div class="sb-couple">
      <strong><?= h($wedding['name1']) ?> &amp; <?= h($wedding['name2']) ?></strong>
      <span><?= h(date('j M Y', strtotime($wedding['wedding_date']))) ?></span>
    </div>
    <a class="btn-logout" href="couple-logout.php">Sign Out</a>
  </div>
</aside>

<main class="main">
  <div class="topbar">
    <span class="topbar-title" id="topbar-title">Overview</span>
    <div class="topbar-right">
      <a class="btn btn-accent btn-sm" data-panel="photos" href="#photos">View Photos</a>
    </div>
  </div>

  <!-- Overview -->
  <div class="panel" id="panel-overview">
    <h1 class="page-title">Welcome back <span style="color:var(--accent)">&#10022;</span></h1>
    <p class="page-sub">Here's how your wedding memory collection is growing.</p>
    <div class="stat-row">
      <div class="stat"><div class="stat-num"><?= $guestCount ?></div><div class="stat-lbl">Guests Invited</div></div>
      <div class="stat"><div class="stat-num"><?= $photoCount ?></div><div class="stat-lbl">Photos Uploaded</div></div>
      <div class="stat"><div class="stat-num"><?= $selCount ?></div><div class="stat-lbl">Selected for Memory</div></div>
      <div class="stat">
        <div class="stat-num"><?= $daysUntil >= 0 ? $daysUntil : abs($daysUntil) ?></div>
        <div class="stat-lbl"><?= $daysUntil >= 0 ? 'Days Until Wedding' : 'Days Since Wedding' ?></div>
      </div>
    </div>
    <div class="card">
      <h3 class="card-title">Quick Actions</h3>
      <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:8px">
        <a class="btn btn-accent"  data-panel="guests">Import Guests</a>
        <a class="btn btn-dark"    data-panel="invites">View Invite Cards</a>
        <a class="btn btn-dark"    data-panel="photos">Browse Photos</a>
        <a class="btn btn-dark"    data-panel="memory">Create Memory Page</a>
        <a class="btn btn-dark"    data-panel="video">Make Slideshow</a>
      </div>
    </div>
    <div class="card">
      <h3 class="card-title">Recent Uploads</h3>
      <p class="card-sub">Latest photos from your guests</p>
      <?php if (!$recent): ?>
        <div class="empty"><p>No photos uploaded yet &mdash; the gallery will fill up after the wedding.</p></div>
      <?php else: ?>
        <div class="photo-grid">
          <?php foreach ($recent as $p): ?>
            <div class="photo-item"><img src="<?= h(UPLOADS_URL . '/' . $p['file_path']) ?>" alt=""><div class="photo-meta"><?= h($p['guest_name']) ?></div></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Settings -->
  <div class="panel" id="panel-settings">
    <h1 class="page-title">Settings &amp; Theme</h1>
    <p class="page-sub">Customise your wedding page appearance and details.</p>

    <div class="card">
      <h3 class="card-title">Color Theme</h3>
      <p class="card-sub">This theme applies to the dashboard and the guest portal.</p>
      <div class="theme-grid">
        <?php foreach (THEMES as $key => $t): ?>
          <button class="theme-card <?= $key === $themeKey ? 'selected' : '' ?>" data-theme="<?= h($key) ?>">
            <div class="theme-swatches">
              <?php foreach ($t['preview'] as $c): ?><div class="swatch" style="background:<?= h($c) ?>"></div><?php endforeach; ?>
            </div>
            <div class="theme-name"><?= h($t['name']) ?></div>
          </button>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="card">
      <h3 class="card-title">Wedding Details</h3>
      <p class="card-sub">Update your wedding information.</p>
      <form id="settings-form">
        <div class="field-row">
          <div class="field"><label>Partner 1 Name</label><input name="name1" value="<?= h($wedding['name1']) ?>" required></div>
          <div class="field"><label>Partner 2 Name</label><input name="name2" value="<?= h($wedding['name2']) ?>" required></div>
        </div>
        <div class="field-row">
          <div class="field"><label>Wedding Date</label><input type="date" name="date" value="<?= h($wedding['wedding_date']) ?>" required></div>
          <div class="field"><label>Venue</label><input name="venue" value="<?= h($wedding['venue'] ?? '') ?>" placeholder="Venue name"></div>
        </div>
        <button type="submit" class="btn btn-accent">Save Changes</button>
      </form>
    </div>
  </div>

  <!-- Guests -->
  <div class="panel" id="panel-guests">
    <h1 class="page-title">Guest List</h1>
    <p class="page-sub">Import your guests from a CSV and manage their access.</p>

    <div class="card">
      <h3 class="card-title">Import from CSV</h3>
      <p class="card-sub">Your CSV should have columns: <code style="background:var(--bg);padding:2px 6px;border-radius:2px;font-size:12px">name, email</code></p>
      <div class="csv-drop" id="csv-drop">
        <p><strong>Click to upload your guest CSV</strong><br>or drag and drop the file here</p>
        <small>CSV &middot; name, email columns</small>
      </div>
      <input type="file" id="csv-file" accept=".csv,text/csv" style="display:none">
    </div>

    <div class="card">
      <h3 class="card-title">Add Guest Manually</h3>
      <form id="add-guest-form">
        <div class="field-row">
          <div class="field"><label>Name</label><input name="name" required></div>
          <div class="field"><label>Email (optional)</label><input type="email" name="email"></div>
        </div>
        <button type="submit" class="btn btn-accent">Add Guest</button>
      </form>
    </div>

    <div class="card">
      <h3 class="card-title">Guests <span style="font-size:14px;color:var(--muted);font-weight:300">(<?= count($guests) ?>)</span></h3>
      <p class="card-sub">Each guest has auto-generated credentials. Print the card to hand over.</p>
      <?php if (!$guests): ?>
        <div class="empty"><p>No guests yet &mdash; import a CSV or add one manually above.</p></div>
      <?php else: ?>
        <div style="overflow-x:auto">
          <table class="guest-table">
            <thead><tr><th>Name</th><th>Username</th><th>Password</th><th>Email</th><th style="text-align:right">Actions</th></tr></thead>
            <tbody>
              <?php foreach ($guests as $g): ?>
                <tr data-username="<?= h($g['username']) ?>">
                  <td><?= h($g['name']) ?></td>
                  <td><span class="cred-badge"><?= h($g['username']) ?></span></td>
                  <td><span class="cred-badge"><?= h($g['password_plain']) ?></span></td>
                  <td><?= h($g['email'] ?? '') ?></td>
                  <td style="text-align:right;white-space:nowrap">
                    <a class="btn btn-dark btn-sm" href="invite-card.php?guest=<?= urlencode($g['username']) ?>" target="_blank">Card</a>
                    <button type="button" class="btn btn-dark btn-sm js-del-guest" data-username="<?= h($g['username']) ?>">Remove</button>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Invites -->
  <div class="panel" id="panel-invites">
    <h1 class="page-title">Invite Cards</h1>
    <p class="page-sub">Choose a card style, then print or save PDFs of each guest's card.</p>
    <div class="card">
      <h3 class="card-title">Card Style</h3>
      <form id="card-style-form" class="invite-styles">
        <?php $cs = $wedding['card_style'] ?: 'dark'; ?>
        <?php foreach (['dark'=>'Dark Romantic','cream'=>'Cream &amp; Gold','rose'=>'Dusty Rose','sage'=>'Sage Garden'] as $k => $label): ?>
          <button type="button" class="invite-style <?= $cs === $k ? 'selected' : '' ?>" data-style="<?= $k ?>">
            <div class="invite-preview-mini ip-<?= $k ?>"><?= $label ?></div>
            <?= $label ?>
          </button>
        <?php endforeach; ?>
      </form>
    </div>

    <div class="card">
      <h3 class="card-title">Print Cards</h3>
      <p class="card-sub">Open any guest's card in a new tab and print or save as PDF.</p>
      <?php if (!$guests): ?>
        <div class="empty"><p>Add guests first, then come back to print their cards.</p></div>
      <?php else: ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:10px">
          <?php foreach ($guests as $g): ?>
            <a class="btn btn-dark" style="justify-content:space-between" href="invite-card.php?guest=<?= urlencode($g['username']) ?>" target="_blank">
              <?= h($g['name']) ?> <span class="cred-badge" style="margin-left:auto"><?= h($g['username']) ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- All Photos -->
  <div class="panel" id="panel-photos">
    <h1 class="page-title">All Photos</h1>
    <p class="page-sub">Every photo uploaded by your guests. Click to select for the memory page.</p>
    <div style="display:flex;gap:10px;margin-bottom:24px;flex-wrap:wrap;align-items:center">
      <button class="btn btn-accent" id="btn-save-selection">Save Selection</button>
      <a class="btn btn-dark" data-panel="memory">Memory Page &rarr;</a>
      <span id="selected-count" style="font-size:14px;color:var(--muted)"><?= count($selectedIds) ?> selected</span>
    </div>
    <?php if (!$allPhotos): ?>
      <div class="empty"><p>No photos uploaded yet. Your guests will add them after the wedding date.</p></div>
    <?php else: ?>
      <div class="photo-grid" id="all-photos-grid">
        <?php foreach ($allPhotos as $p): ?>
          <div class="photo-item <?= isset($selectedSet[$p['id']]) ? 'selected' : '' ?>" data-id="<?= h($p['id']) ?>">
            <img src="<?= h(UPLOADS_URL . '/' . $p['file_path']) ?>" alt="" loading="lazy">
            <div class="photo-item-overlay"><div class="photo-check">&#10003;</div></div>
            <div class="photo-meta"><?= h($p['guest_name']) ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Memory -->
  <div class="panel" id="panel-memory">
    <h1 class="page-title">Memory Page</h1>
    <p class="page-sub">A beautiful photo book built from your selected photos.</p>
    <div class="card">
      <h3 class="card-title">Your Selection <span style="font-size:14px;color:var(--muted);font-weight:300">(<?= count($selectedIds) ?> photos)</span></h3>
      <p class="card-sub">Selections made in the <strong>All Photos</strong> tab appear here.</p>
      <div style="display:flex;gap:10px;flex-wrap:wrap">
        <a class="btn btn-accent" href="memory-book.php" target="_blank">Open Memory Book</a>
        <a class="btn btn-dark"   data-panel="photos">&larr; Change Selection</a>
      </div>
      <?php if ($selectedIds): ?>
        <div class="photo-grid" style="margin-top:20px">
          <?php foreach ($allPhotos as $p): if (!isset($selectedSet[$p['id']])) continue; ?>
            <div class="photo-item selected"><img src="<?= h(UPLOADS_URL . '/' . $p['file_path']) ?>" alt=""><div class="photo-meta"><?= h($p['guest_name']) ?></div></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Video -->
  <div class="panel" id="panel-video">
    <h1 class="page-title">Video Slideshow</h1>
    <p class="page-sub">A cinematic Ken Burns slideshow rendered from your selected photos.</p>
    <div class="card">
      <h3 class="card-title">Slideshow</h3>
      <p class="card-sub">The video is rendered server-side with ffmpeg &mdash; takes a minute or two.</p>
      <div class="video-box" id="video-box">
        <video id="video-player" controls <?= is_file(VIDEOS_PUB_DIR . '/' . $weddingId . '.mp4') ? '' : 'style="display:none"' ?>>
          <source id="video-src" src="<?= is_file(VIDEOS_PUB_DIR . '/' . $weddingId . '.mp4') ? VIDEOS_URL . '/' . $weddingId . '.mp4?t=' . filemtime(VIDEOS_PUB_DIR . '/' . $weddingId . '.mp4') : '' ?>" type="video/mp4">
        </video>
        <?php if (!is_file(VIDEOS_PUB_DIR . '/' . $weddingId . '.mp4')): ?>
          <div class="video-placeholder" id="video-placeholder">Select photos first, then generate the slideshow.</div>
        <?php endif; ?>
      </div>
      <div style="display:flex;gap:10px;margin-top:14px;flex-wrap:wrap">
        <button class="btn btn-accent" id="btn-render-video">Generate Slideshow</button>
        <a class="btn btn-dark" id="btn-download-video" href="<?= is_file(VIDEOS_PUB_DIR . '/' . $weddingId . '.mp4') ? VIDEOS_URL . '/' . $weddingId . '.mp4' : '#' ?>" download <?= is_file(VIDEOS_PUB_DIR . '/' . $weddingId . '.mp4') ? '' : 'style="display:none"' ?>>Download MP4</a>
        <span id="render-status" style="font-size:13px;color:var(--muted);align-self:center"></span>
      </div>
    </div>
  </div>
</main>

<div class="toast" id="toast"></div>

<script>
const CSRF = <?= json_encode(csrfToken()) ?>;

function toast(m){ const el=document.getElementById('toast'); el.textContent=m; el.classList.add('show'); setTimeout(()=>el.classList.remove('show'),3000); }

// ── Panel nav ──
const panelTitles = {
  overview:'Overview', settings:'Settings & Theme', guests:'Guest List',
  invites:'Invite Cards', photos:'All Photos', memory:'Memory Page', video:'Video Slideshow'
};
function navTo(key){
  document.querySelectorAll('.panel').forEach(p=> p.classList.remove('active'));
  document.getElementById('panel-'+key)?.classList.add('active');
  document.querySelectorAll('.sb-item').forEach(a=> a.classList.toggle('active', a.dataset.panel===key));
  document.getElementById('topbar-title').textContent = panelTitles[key] || '';
  history.replaceState(null,'','#'+key);
}
document.querySelectorAll('[data-panel]').forEach(el=>{
  el.addEventListener('click', e=>{ e.preventDefault(); navTo(el.dataset.panel); });
});
navTo((location.hash||'#overview').slice(1));

// ── Theme picker ──
document.querySelectorAll('.theme-card').forEach(card=>{
  card.addEventListener('click', async ()=>{
    const theme = card.dataset.theme;
    const fd = new FormData(); fd.append('_csrf', CSRF); fd.append('theme', theme);
    const res = await fetch('api/save-theme.php', {method:'POST', body: fd});
    const d = await res.json();
    if (d.ok) { toast('Theme saved'); setTimeout(()=> location.reload(), 700); }
    else toast(d.error || 'Failed to save');
  });
});

// ── Settings form ──
document.getElementById('settings-form').addEventListener('submit', async e=>{
  e.preventDefault();
  const fd = new FormData(e.target); fd.append('_csrf', CSRF);
  const res = await fetch('api/save-wedding.php', {method:'POST', body:fd});
  const d = await res.json();
  toast(d.ok ? 'Saved' : (d.error || 'Failed'));
  if (d.ok) setTimeout(()=> location.reload(), 700);
});

// ── Card style ──
document.querySelectorAll('#card-style-form .invite-style').forEach(btn=>{
  btn.addEventListener('click', async ()=>{
    document.querySelectorAll('#card-style-form .invite-style').forEach(b=> b.classList.remove('selected'));
    btn.classList.add('selected');
    const fd = new FormData();
    fd.append('_csrf', CSRF);
    fd.append('name1', <?= json_encode($wedding['name1']) ?>);
    fd.append('name2', <?= json_encode($wedding['name2']) ?>);
    fd.append('date',  <?= json_encode($wedding['wedding_date']) ?>);
    fd.append('venue', <?= json_encode($wedding['venue'] ?? '') ?>);
    fd.append('card_style', btn.dataset.style);
    const res = await fetch('api/save-wedding.php', {method:'POST', body:fd});
    const d = await res.json();
    toast(d.ok ? 'Card style saved' : (d.error || 'Failed'));
  });
});

// ── CSV upload ──
const csvDrop = document.getElementById('csv-drop');
const csvFile = document.getElementById('csv-file');
csvDrop.addEventListener('click', ()=> csvFile.click());
csvDrop.addEventListener('dragover', e=>{ e.preventDefault(); csvDrop.classList.add('over'); });
csvDrop.addEventListener('dragleave', ()=> csvDrop.classList.remove('over'));
csvDrop.addEventListener('drop', e=>{ e.preventDefault(); csvDrop.classList.remove('over'); if (e.dataTransfer.files[0]) uploadCsv(e.dataTransfer.files[0]); });
csvFile.addEventListener('change', ()=> csvFile.files[0] && uploadCsv(csvFile.files[0]));
async function uploadCsv(file){
  const fd = new FormData();
  fd.append('_csrf', CSRF);
  fd.append('csv', file);
  toast('Importing…');
  const res = await fetch('api/import-csv.php', {method:'POST', body: fd});
  const d = await res.json();
  if (!d.ok) return toast(d.error || 'Import failed');
  toast(`Imported ${d.created.length} guest${d.created.length===1?'':'s'}`);
  setTimeout(()=> location.reload(), 800);
}

// ── Manual guest add ──
document.getElementById('add-guest-form').addEventListener('submit', async e=>{
  e.preventDefault();
  const fd = new FormData(e.target);
  fd.append('_csrf', CSRF);
  fd.append('manual', '1');
  const res = await fetch('api/import-csv.php', {method:'POST', body:fd});
  const d = await res.json();
  if (!d.ok) return toast(d.error || 'Failed');
  toast('Guest added');
  setTimeout(()=> location.reload(), 700);
});

// ── Remove guest ──
document.querySelectorAll('.js-del-guest').forEach(btn=>{
  btn.addEventListener('click', async ()=>{
    if (!confirm('Remove this guest and all their uploaded photos?')) return;
    const fd = new FormData();
    fd.append('_csrf', CSRF);
    fd.append('username', btn.dataset.username);
    const res = await fetch('api/delete-guest.php', {method:'POST', body:fd});
    const d = await res.json();
    if (!d.ok) return toast(d.error || 'Failed');
    btn.closest('tr').remove();
    toast('Guest removed');
  });
});

// ── Photo selection ──
const selCount = document.getElementById('selected-count');
document.querySelectorAll('#all-photos-grid .photo-item').forEach(el=>{
  el.addEventListener('click', ()=>{
    el.classList.toggle('selected');
    const n = document.querySelectorAll('#all-photos-grid .photo-item.selected').length;
    selCount.textContent = n + ' selected';
  });
});

document.getElementById('btn-save-selection')?.addEventListener('click', async ()=>{
  const ids = [...document.querySelectorAll('#all-photos-grid .photo-item.selected')].map(el=> el.dataset.id);
  const fd = new FormData();
  fd.append('_csrf', CSRF);
  fd.append('photo_ids', JSON.stringify(ids));
  const res = await fetch('api/save-selected-photos.php', {method:'POST', body:fd});
  const d = await res.json();
  toast(d.ok ? 'Selection saved' : (d.error || 'Failed'));
});

// ── Video render ──
const renderBtn = document.getElementById('btn-render-video');
renderBtn?.addEventListener('click', async ()=>{
  renderBtn.disabled = true;
  const status = document.getElementById('render-status');
  status.textContent = 'Rendering… this can take a minute.';
  const fd = new FormData(); fd.append('_csrf', CSRF);
  try {
    const res = await fetch('api/render-video.php', {method:'POST', body:fd});
    const d = await res.json();
    if (!d.ok) throw new Error(d.error || 'Failed');
    const ph = document.getElementById('video-placeholder'); if (ph) ph.remove();
    const video = document.getElementById('video-player');
    document.getElementById('video-src').src = d.url;
    video.style.display = 'block';
    video.load();
    const dl = document.getElementById('btn-download-video');
    dl.href = d.url.replace(/\?.*$/, '');
    dl.style.display = 'inline-flex';
    status.textContent = 'Done.';
    toast('Slideshow ready');
  } catch (e) {
    status.textContent = '';
    toast(e.message);
  } finally {
    renderBtn.disabled = false;
  }
});
</script>
</body>
</html>
