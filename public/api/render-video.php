<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/csrf.php';

set_time_limit(600);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonError('POST only', 405);
verifyCsrf();
$couple = requireCouple();
$weddingId = $couple['wedding_id'];

$stmt = db()->prepare(
  'SELECT p.file_path FROM memory_selections ms
     JOIN photos p ON p.id = ms.photo_id
    WHERE ms.wedding_id = ?
    ORDER BY ms.position ASC'
);
$stmt->execute([$weddingId]);
$paths = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (count($paths) < 2) jsonError('Select at least 2 photos for a slideshow');
if (count($paths) > 40) $paths = array_slice($paths, 0, 40);

$absPaths = [];
foreach ($paths as $rel) {
  $abs = UPLOADS_DIR . '/' . $rel;
  if (is_file($abs)) $absPaths[] = $abs;
}
if (count($absPaths) < 2) jsonError('Selected photos are missing on disk', 500);

if (!is_dir(VIDEOS_PUB_DIR)) mkdir(VIDEOS_PUB_DIR, 0755, true);
$outFile = VIDEOS_PUB_DIR . '/' . $weddingId . '.mp4';
$outUrl  = VIDEOS_URL  . '/' . $weddingId . '.mp4?t=' . time();

$fps        = 25;
$durSec     = 5;
$xfadeSec   = 0.8;
$framesEach = $fps * $durSec;

// Build ffmpeg command
$args = [FFMPEG_BIN, '-y'];
foreach ($absPaths as $p) {
  $args[] = '-loop'; $args[] = '1';
  $args[] = '-t';    $args[] = (string) $durSec;
  $args[] = '-i';    $args[] = $p;
}

$filter = '';
$n = count($absPaths);
for ($i = 0; $i < $n; $i++) {
  $filter .= "[{$i}:v]scale=1920:1080:force_original_aspect_ratio=increase,"
    . "crop=1920:1080,setsar=1,"
    . "zoompan=z='min(zoom+0.0015,1.2)':d={$framesEach}:s=1920x1080:fps={$fps}[v{$i}];";
}

// Chain xfades
$last = 'v0';
$offset = $durSec - $xfadeSec;
for ($i = 1; $i < $n; $i++) {
  $out = ($i === $n - 1) ? 'vout' : ('vx' . $i);
  $filter .= "[{$last}][v{$i}]xfade=transition=fade:duration={$xfadeSec}:offset=" . number_format($offset, 2, '.', '') . "[{$out}];";
  $last = $out;
  $offset += $durSec - $xfadeSec;
}
if ($n === 1) { $filter .= '[v0]null[vout];'; }

$filter = rtrim($filter, ';');

$args[] = '-filter_complex'; $args[] = $filter;
$args[] = '-map'; $args[] = '[vout]';
$args[] = '-r';   $args[] = (string) $fps;
$args[] = '-c:v'; $args[] = 'libx264';
$args[] = '-pix_fmt'; $args[] = 'yuv420p';
$args[] = '-preset';  $args[] = 'medium';
$args[] = '-crf';     $args[] = '22';
$args[] = $outFile;

$cmd = implode(' ', array_map('escapeshellarg', $args));
exec($cmd . ' 2>&1', $output, $code);

if ($code !== 0 || !is_file($outFile)) {
  jsonError('ffmpeg failed. Make sure ffmpeg is installed and in PATH.', 500);
}

jsonResponse(['ok' => true, 'url' => $outUrl]);
