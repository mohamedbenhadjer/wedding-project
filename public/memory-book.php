<?php
require_once __DIR__ . '/../includes/auth.php';

$couple = requireCouple();
$weddingId = $couple['wedding_id'];

$wStmt = db()->prepare('SELECT * FROM weddings WHERE id = ?');
$wStmt->execute([$weddingId]);
$wedding = $wStmt->fetch();

$pStmt = db()->prepare(
  'SELECT p.id, p.file_path, p.message, g.name AS guest_name
     FROM memory_selections ms
     JOIN photos p  ON p.id = ms.photo_id
     JOIN guests g  ON g.username = p.guest_username
    WHERE ms.wedding_id = ?
    ORDER BY ms.position ASC'
);
$pStmt->execute([$weddingId]);
$photos = $pStmt->fetchAll();

$names = h($wedding['name1']) . ' &amp; ' . h($wedding['name2']);
$dateLong  = $wedding['wedding_date'] ? date('l, j F Y', strtotime($wedding['wedding_date'])) : '';
$dateShort = $wedding['wedding_date'] ? date('j F Y',     strtotime($wedding['wedding_date'])) : '';
$year      = $wedding['wedding_date'] ? date('Y',         strtotime($wedding['wedding_date'])) : '';

$chapterLabels = [
  'The Ceremony','The Celebration','Candid Moments','Friends &amp; Family',
  'The Reception','A Night to Remember','Cherished Moments',
];
$patterns = ['full','spread-2a','spread-3','spread-2b','spread-2eq','full','spread-2a'];
$tilts    = ['tilt-l','tilt-r','','tilt-ll','tilt-r','tilt-l',''];

function frameHtml(array $p, int $idx, string $asp, string $tilt): string {
  $aspStyle = $asp === 'asp-port' ? 'style="aspect-ratio:3/4"'
            : ($asp === 'asp-sq'  ? 'style="aspect-ratio:1"'
            : 'style="aspect-ratio:4/3"');
  $src  = h(UPLOADS_URL . '/' . $p['file_path']);
  $name = !empty($p['guest_name']) ? '<p class="fc-name">' . h($p['guest_name']) . '</p>' : '';
  $msg  = !empty($p['message'])    ? '<p class="fc-msg">"' . h($p['message']) . '"</p>' : '';
  return "<div class=\"frame {$tilt}\" data-ph=\"{$idx}\">"
    . "<div class=\"frame-img\" {$aspStyle}><img src=\"{$src}\" alt=\"\" loading=\"lazy\"></div>"
    . "<div class=\"frame-caption\">{$name}{$msg}</div></div>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Our Memory Book &mdash; Forever Together</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;1,400;1,500&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400;1,500&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--ink:#1a1714;--white:#fefcf8;--gold:#c4a882;--gold-lt:#ede4d6;--muted:#8a8278;--bg:#f8f4ef;--serif:'Playfair Display',Georgia,serif;--cormo:'Cormorant Garamond',Georgia,serif;--sans:'DM Sans',system-ui,sans-serif}
html{scroll-behavior:smooth}
body{font-family:var(--sans);background:var(--bg);color:var(--ink);min-height:100vh}
.print-nav{position:fixed;top:0;left:0;right:0;z-index:100;display:flex;align-items:center;justify-content:space-between;padding:14px 40px;background:rgba(248,244,239,0.92);backdrop-filter:blur(12px);border-bottom:1px solid var(--gold-lt)}
.pn-logo{font-family:var(--serif);font-style:italic;font-size:18px;color:rgba(26,23,20,0.6);text-decoration:none}
.pn-right{display:flex;gap:10px}
.btn{padding:9px 20px;font-family:var(--sans);font-size:12px;font-weight:500;letter-spacing:0.1em;text-transform:uppercase;border-radius:2px;border:none;cursor:pointer;transition:all 0.2s;text-decoration:none;display:inline-block}
.btn-ink{background:var(--ink);color:var(--white)}
.btn-ink:hover{background:#2a2720}
.btn-ghost{background:transparent;color:var(--muted);border:1px solid var(--gold-lt)}
.btn-ghost:hover{border-color:var(--gold);color:var(--ink)}
@media print{.print-nav{display:none}}
.cover{min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:80px 60px 60px;background:var(--ink);color:var(--white);position:relative;overflow:hidden;page-break-after:always}
.cover-bg{position:absolute;inset:0;background:radial-gradient(ellipse 70% 60% at 15% 100%,rgba(196,168,130,0.1) 0%,transparent 55%),radial-gradient(ellipse 50% 50% at 85% 0%,rgba(196,168,130,0.07) 0%,transparent 50%)}
.cring{position:absolute;border-radius:50%;border:1px solid rgba(196,168,130,0.08);pointer-events:none;top:50%;left:50%;transform:translate(-50%,-50%)}
.cr1{width:500px;height:500px}.cr2{width:820px;height:820px}.cr3{width:1150px;height:1150px}
.cover-content{position:relative;z-index:1}
.cover-eyebrow{font-size:10px;letter-spacing:0.28em;text-transform:uppercase;color:var(--gold);margin-bottom:20px;font-weight:500}
.cover-title{font-family:var(--cormo);font-size:clamp(64px,8vw,120px);font-weight:300;line-height:0.92;margin-bottom:16px}
.cover-title em{font-style:italic;color:rgba(255,255,255,0.3)}
.cover-names{font-family:var(--serif);font-style:italic;font-size:clamp(24px,3vw,40px);color:var(--gold);margin-bottom:20px}
.cover-meta{font-size:15px;color:rgba(255,255,255,0.45);font-weight:300;letter-spacing:0.04em;margin-bottom:48px}
.cover-rule{width:48px;height:1px;background:rgba(196,168,130,0.4);margin:0 auto 32px}
.cover-photos-count{font-family:var(--cormo);font-size:72px;font-weight:300;color:rgba(255,255,255,0.08);position:absolute;bottom:40px;right:60px}
.book{background:var(--bg);padding:80px 0}
.spread{max-width:1200px;margin:0 auto 80px;padding:0 60px;display:grid;gap:20px}
.spread-1{grid-template-columns:1fr}
.spread-2a{grid-template-columns:1.4fr 1fr}
.spread-2b{grid-template-columns:1fr 1.4fr}
.spread-3{grid-template-columns:1fr 1fr 1fr}
.spread-2eq{grid-template-columns:1fr 1fr}
.frame{background:var(--white);padding:12px 12px 44px;box-shadow:0 6px 28px rgba(26,23,20,0.08);transition:transform 0.4s,box-shadow 0.4s;cursor:pointer;position:relative}
.frame:hover{transform:scale(1.02);box-shadow:0 16px 48px rgba(26,23,20,0.12)}
.frame-img{width:100%;overflow:hidden}
.frame-img img{width:100%;display:block;transition:transform 0.6s;height:100%;object-fit:cover}
.frame:hover .frame-img img{transform:scale(1.04)}
.frame-caption{text-align:center;margin-top:8px}
.fc-name{font-size:10px;letter-spacing:0.16em;text-transform:uppercase;color:var(--gold);margin-bottom:3px;font-weight:500}
.fc-msg{font-family:var(--cormo);font-style:italic;font-size:15px;color:var(--muted);line-height:1.4}
.tilt-l{transform:rotate(-1.8deg)}
.tilt-r{transform:rotate(1.5deg)}
.tilt-ll{transform:rotate(-2.5deg)}
.chapter{max-width:1200px;margin:80px auto 60px;padding:0 60px;display:flex;align-items:center;gap:28px}
.ch-line{flex:1;height:1px;background:var(--gold-lt)}
.ch-text{font-family:var(--cormo);font-style:italic;font-size:28px;color:var(--muted)}
.full-bleed{margin:60px 0;position:relative;overflow:hidden;cursor:pointer}
.full-bleed img{width:100%;max-height:560px;object-fit:cover;display:block;transition:transform 0.6s}
.full-bleed:hover img{transform:scale(1.02)}
.fb-overlay{position:absolute;inset:0;background:linear-gradient(to top,rgba(26,23,20,0.55) 0%,transparent 50%)}
.fb-caption{position:absolute;bottom:0;left:0;right:0;padding:36px 60px;color:#fff}
.fb-name{font-size:10px;letter-spacing:0.2em;text-transform:uppercase;color:var(--gold);margin-bottom:6px;font-weight:500}
.fb-msg{font-family:var(--cormo);font-style:italic;font-size:clamp(20px,2.5vw,32px);line-height:1.35}
.quote-page{background:var(--ink);padding:100px 60px;text-align:center;margin:80px 0;page-break-before:always}
.qp-rule{width:48px;height:1px;background:rgba(196,168,130,0.4);margin:0 auto 28px}
.qp-text{font-family:var(--cormo);font-style:italic;font-size:clamp(28px,3.5vw,48px);color:rgba(255,255,255,0.8);line-height:1.35;max-width:680px;margin:0 auto 24px}
.qp-attr{font-size:10px;letter-spacing:0.22em;text-transform:uppercase;color:var(--gold)}
.back-cover{background:var(--ink);padding:100px 60px;text-align:center;margin-top:80px;page-break-before:always}
.bc-title{font-family:var(--cormo);font-size:clamp(40px,5vw,72px);font-weight:300;color:#fff;margin-bottom:12px;line-height:1}
.bc-title em{font-style:italic;color:rgba(255,255,255,0.3)}
.bc-sub{font-family:var(--serif);font-style:italic;font-size:22px;color:var(--gold);margin-bottom:24px}
.bc-date{font-size:14px;color:rgba(255,255,255,0.35);letter-spacing:0.1em}
.bc-logo{font-family:var(--serif);font-style:italic;font-size:16px;color:rgba(255,255,255,0.25);margin-top:48px}
.lb{display:none;position:fixed;inset:0;z-index:500;background:rgba(10,8,6,0.96);align-items:center;justify-content:center;padding:40px}
.lb.open{display:flex}
.lb-img{max-width:min(900px,90vw);max-height:90vh;object-fit:contain}
.lb-close{position:absolute;top:20px;right:24px;width:40px;height:40px;border-radius:50%;background:rgba(255,255,255,0.08);border:none;color:rgba(255,255,255,0.7);font-size:20px;cursor:pointer}
.lb-arr{position:fixed;top:50%;transform:translateY(-50%);width:48px;height:48px;border-radius:50%;background:rgba(255,255,255,0.07);border:none;color:rgba(255,255,255,0.6);font-size:26px;cursor:pointer}
.lb-arr-l{left:16px}.lb-arr-r{right:16px}
@media print{
  .cover{page-break-after:always;-webkit-print-color-adjust:exact;print-color-adjust:exact}
  .frame{box-shadow:none;border:1px solid #e8e0d4}
  .frame:hover{transform:none}
  .quote-page,.back-cover{-webkit-print-color-adjust:exact;print-color-adjust:exact}
  .full-bleed{page-break-inside:avoid}
  body{background:#fff}
}
@media(max-width:768px){.spread{padding:0 20px}.spread-2a,.spread-2b,.spread-3,.spread-2eq{grid-template-columns:1fr}.chapter{padding:0 20px}.full-bleed .fb-caption{padding:24px 20px}.book{padding:40px 0}}
</style>
</head>
<body>

<div class="print-nav">
  <span class="pn-logo">Forever Together</span>
  <div class="pn-right">
    <a class="btn btn-ghost" href="couple-dashboard.php">&larr; Back</a>
    <button class="btn btn-ink" onclick="window.print()">Print / Save PDF</button>
  </div>
</div>

<?php if (!$photos): ?>
  <div style="padding:160px 40px;text-align:center;color:#8a8278">
    <p style="font-size:22px;font-family:'Cormorant Garamond',serif;font-style:italic">No photos selected yet.</p>
    <p style="margin-top:10px;font-size:14px">Go to the dashboard, open the <strong>All Photos</strong> tab and pick your favourites.</p>
  </div>
<?php else: ?>

<div class="cover">
  <div class="cover-bg"></div>
  <div class="cring cr1"></div><div class="cring cr2"></div><div class="cring cr3"></div>
  <div class="cover-content">
    <p class="cover-eyebrow">A Wedding Memory Book</p>
    <h1 class="cover-title">Our<br><em>Beautiful</em><br>Day</h1>
    <p class="cover-names"><?= $names ?></p>
    <p class="cover-meta"><?= h($dateLong) ?></p>
    <div class="cover-rule"></div>
  </div>
  <div class="cover-photos-count"><?= str_pad((string) count($photos), 2, '0', STR_PAD_LEFT) ?></div>
</div>

<div class="book">
<?php
$n = count($photos);
$i = 0;
$chapterNum = 1;
$lightboxSrcs = [];
while ($i < $n) {
  if ($i > 0 && $i % 6 === 0) {
    $lbl = $chapterLabels[min($chapterNum - 1, count($chapterLabels) - 1)];
    echo '<div class="chapter"><div class="ch-line"></div><span class="ch-text">' . $lbl . '</span><div class="ch-line"></div></div>';
    $chapterNum++;
  }
  $pat = $patterns[intdiv($i, 2) % count($patterns)];
  $remaining = $n - $i;

  if ($pat === 'full' && $remaining >= 1) {
    $p = $photos[$i];
    $src = h(UPLOADS_URL . '/' . $p['file_path']);
    $lightboxSrcs[] = $src;
    echo '<div class="full-bleed" data-ph="' . $i . '">'
      . '<img src="' . $src . '" alt="" loading="lazy">'
      . '<div class="fb-overlay"></div>'
      . '<div class="fb-caption">'
      . (!empty($p['guest_name']) ? '<p class="fb-name">' . h($p['guest_name']) . '</p>' : '')
      . (!empty($p['message'])    ? '<p class="fb-msg">"' . h($p['message']) . '"</p>' : '')
      . '</div></div>';
    $i++;
    if ($i === intdiv($n, 2)) {
      echo '<div class="quote-page"><div class="qp-rule"></div>'
        . '<p class="qp-text">"Every photo here was taken by someone who loved you enough to be present."</p>'
        . '<p class="qp-attr">' . $names . ' &mdash; ' . h($year) . '</p></div>';
    }
  } elseif ($pat === 'spread-2a' && $remaining >= 2) {
    $lightboxSrcs[] = h(UPLOADS_URL . '/' . $photos[$i]['file_path']);
    $lightboxSrcs[] = h(UPLOADS_URL . '/' . $photos[$i+1]['file_path']);
    echo '<div class="spread spread-2a">'
      . frameHtml($photos[$i], $i, 'asp-port', $tilts[$i % 7])
      . frameHtml($photos[$i+1], $i+1, 'asp-land', '')
      . '</div>';
    $i += 2;
  } elseif ($pat === 'spread-2b' && $remaining >= 2) {
    $lightboxSrcs[] = h(UPLOADS_URL . '/' . $photos[$i]['file_path']);
    $lightboxSrcs[] = h(UPLOADS_URL . '/' . $photos[$i+1]['file_path']);
    echo '<div class="spread spread-2b">'
      . frameHtml($photos[$i], $i, 'asp-sq', 'tilt-l')
      . frameHtml($photos[$i+1], $i+1, 'asp-port', 'tilt-r')
      . '</div>';
    $i += 2;
  } elseif ($pat === 'spread-3' && $remaining >= 3) {
    $lightboxSrcs[] = h(UPLOADS_URL . '/' . $photos[$i]['file_path']);
    $lightboxSrcs[] = h(UPLOADS_URL . '/' . $photos[$i+1]['file_path']);
    $lightboxSrcs[] = h(UPLOADS_URL . '/' . $photos[$i+2]['file_path']);
    echo '<div class="spread spread-3">'
      . frameHtml($photos[$i],   $i,   'asp-sq', 'tilt-r')
      . frameHtml($photos[$i+1], $i+1, 'asp-sq', '')
      . frameHtml($photos[$i+2], $i+2, 'asp-sq', 'tilt-l')
      . '</div>';
    $i += 3;
  } elseif ($pat === 'spread-2eq' && $remaining >= 2) {
    $lightboxSrcs[] = h(UPLOADS_URL . '/' . $photos[$i]['file_path']);
    $lightboxSrcs[] = h(UPLOADS_URL . '/' . $photos[$i+1]['file_path']);
    echo '<div class="spread spread-2eq">'
      . frameHtml($photos[$i],   $i,   'asp-land', 'tilt-ll')
      . frameHtml($photos[$i+1], $i+1, 'asp-land', 'tilt-r')
      . '</div>';
    $i += 2;
  } else {
    $lightboxSrcs[] = h(UPLOADS_URL . '/' . $photos[$i]['file_path']);
    echo '<div class="spread spread-1">' . frameHtml($photos[$i], $i, 'asp-land', '') . '</div>';
    $i++;
  }
}
?>
</div>

<div class="back-cover">
  <h2 class="bc-title">Thank you<br><em>for being there</em></h2>
  <p class="bc-sub"><?= $names ?></p>
  <p class="bc-date"><?= h($dateShort) ?></p>
  <p class="bc-logo">Forever Together</p>
</div>

<div class="lb" id="lb">
  <button class="lb-close" id="lb-close">&times;</button>
  <button class="lb-arr lb-arr-l" id="lb-prev">&lsaquo;</button>
  <button class="lb-arr lb-arr-r" id="lb-next">&rsaquo;</button>
  <img class="lb-img" id="lb-img" alt="">
</div>

<script>
const SRCS = <?= json_encode($lightboxSrcs) ?>;
let idx = 0;
function openLb(i){ idx=i; document.getElementById('lb-img').src=SRCS[i]; document.getElementById('lb').classList.add('open'); document.body.style.overflow='hidden'; }
function closeLb(){ document.getElementById('lb').classList.remove('open'); document.body.style.overflow=''; }
function nav(d){ idx=(idx+d+SRCS.length)%SRCS.length; document.getElementById('lb-img').src=SRCS[idx]; }
document.querySelectorAll('[data-ph]').forEach(el=> el.addEventListener('click',()=> openLb(+el.dataset.ph)));
document.getElementById('lb-close').addEventListener('click', closeLb);
document.getElementById('lb-prev').addEventListener('click', ()=>nav(-1));
document.getElementById('lb-next').addEventListener('click', ()=>nav(+1));
document.getElementById('lb').addEventListener('click', e=>{ if(e.target.id==='lb') closeLb(); });
document.addEventListener('keydown', e=>{
  if(!document.getElementById('lb').classList.contains('open')) return;
  if(e.key==='Escape') closeLb();
  if(e.key==='ArrowLeft') nav(-1);
  if(e.key==='ArrowRight') nav(+1);
});
</script>
<?php endif; ?>

</body>
</html>
