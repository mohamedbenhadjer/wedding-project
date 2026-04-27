<?php require_once __DIR__ . '/../includes/auth.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Forever Together — Wedding Memory Platform</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;1,400;1,500&family=Cormorant+Garamond:ital,wght@0,300;0,400;1,300;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
:root {
  --ink: #16140f; --white: #faf8f4; --gold: #c9ad87; --gold-lt: #ede4d6;
  --muted: #8a8278; --bg: #f5f2ed;
  --serif: 'Playfair Display', Georgia, serif;
  --cormo: 'Cormorant Garamond', Georgia, serif;
  --sans: 'DM Sans', system-ui, sans-serif;
}
html { scroll-behavior: smooth; }
body { font-family: var(--sans); background: var(--ink); color: var(--white); overflow-x: hidden; }
nav { position: fixed; top: 0; left: 0; right: 0; z-index: 100; display: flex; align-items: center; justify-content: space-between; padding: 24px 60px; transition: background 0.4s, padding 0.3s; }
nav.scrolled { background: rgba(22,20,15,0.85); backdrop-filter: blur(16px); padding: 16px 60px; border-bottom: 1px solid rgba(201,173,135,0.1); }
.nav-logo { font-family: var(--serif); font-style: italic; font-size: 22px; color: rgba(255,255,255,0.85); text-decoration: none; }
.nav-links { display: flex; gap: 12px; align-items: center; }
.btn { padding: 10px 24px; font-family: var(--sans); font-size: 13px; font-weight: 500; letter-spacing: 0.08em; text-transform: uppercase; border-radius: 2px; border: none; cursor: pointer; transition: all 0.22s; text-decoration: none; display: inline-block; }
.btn-ghost { background: transparent; color: rgba(255,255,255,0.6); border: 1px solid rgba(255,255,255,0.18); }
.btn-ghost:hover { color: white; border-color: rgba(255,255,255,0.4); }
.btn-gold { background: var(--gold); color: var(--ink); }
.btn-gold:hover { background: #d4bc97; transform: translateY(-1px); }
.hero { min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: 120px 40px 80px; position: relative; overflow: hidden; }
.hero-bg { position: absolute; inset: 0; background: linear-gradient(to bottom, rgba(22,20,15,0.5) 0%, var(--ink) 100%), url('assets/images/hero-bg.png') center/cover no-repeat; }
.ring { position: absolute; border-radius: 50%; border: 1px solid rgba(201,173,135,0.09); pointer-events: none; top: 50%; left: 50%; transform: translate(-50%,-50%); }
.r1 { width: 500px; height: 500px; } .r2 { width: 850px; height: 850px; } .r3 { width: 1200px; height: 1200px; }
.hero-content { position: relative; z-index: 1; max-width: 820px; }
.hero-eyebrow { font-size: 10px; letter-spacing: 0.28em; text-transform: uppercase; color: var(--gold); margin-bottom: 22px; font-weight: 500; animation: fadeUp 1s ease 0.2s both; }
.hero-title { font-family: var(--cormo); font-size: clamp(64px, 9vw, 136px); font-weight: 300; line-height: 0.9; letter-spacing: -0.02em; margin-bottom: 24px; animation: fadeUp 1s ease 0.4s both; }
.hero-title em { font-style: italic; color: rgba(255,255,255,0.35); }
.hero-sub { font-size: 18px; color: rgba(255,255,255,0.45); font-weight: 300; max-width: 500px; margin: 0 auto 56px; line-height: 1.75; animation: fadeUp 1s ease 0.6s both; }
.hero-paths { display: flex; gap: 16px; justify-content: center; flex-wrap: nowrap; animation: fadeUp 1s ease 0.8s both; }
.path-card { flex: 1; display: flex; flex-direction: column; align-items: center; background: rgba(255,255,255,0.04); border: 1px solid rgba(201,173,135,0.2); border-radius: 4px; padding: 32px 24px; text-align: center; text-decoration: none; color: var(--white); transition: all 0.3s; cursor: pointer; }
.path-card:hover { background: rgba(201,173,135,0.08); border-color: rgba(201,173,135,0.4); transform: translateY(-3px); }
.path-icon { font-size: 32px; margin-bottom: 12px; display: block; }
.path-title { font-family: var(--serif); font-size: 22px; font-weight: 400; margin-bottom: 6px; }
.path-desc { font-size: 13px; color: rgba(255,255,255,0.4); font-weight: 300; line-height: 1.6; }
.path-btn { display: inline-block; margin-top: auto; padding: 10px 24px; font-size: 12px; font-family: var(--sans); font-weight: 500; letter-spacing: 0.1em; text-transform: uppercase; border-radius: 2px; }
.path-card.couple .path-btn { background: var(--gold); color: var(--ink); }
.path-card.guest .path-btn { background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.75); border: 1px solid rgba(255,255,255,0.15); }
.scroll-cue { position: absolute; bottom: 36px; left: 50%; transform: translateX(-50%); display: flex; flex-direction: column; align-items: center; gap: 8px; color: rgba(255,255,255,0.25); font-size: 10px; letter-spacing: 0.2em; text-transform: uppercase; animation: fadeIn 2s ease 1.5s both; }
.scroll-line { width: 1px; height: 44px; background: linear-gradient(to bottom, rgba(201,173,135,0.5), transparent); animation: sp 2s ease-in-out infinite; }
@keyframes sp { 0%,100%{opacity:.3} 50%{opacity:1} }
.section { padding: 100px 60px; }
.section-light { background: var(--bg); color: var(--ink); }
.section-dark { background: var(--ink); }
.s-label { font-size: 10px; letter-spacing: 0.24em; text-transform: uppercase; color: var(--gold); margin-bottom: 16px; font-weight: 500; }
.s-title { font-family: var(--cormo); font-size: clamp(36px, 4.5vw, 64px); font-weight: 300; line-height: 1.1; margin-bottom: 16px; }
.s-sub { font-size: 17px; color: var(--muted); font-weight: 300; max-width: 500px; line-height: 1.75; margin-bottom: 64px; }
.section-light .s-sub { color: #8a8278; }
.two-sides { display: grid; grid-template-columns: 1fr 1fr; gap: 60px; max-width: 1100px; margin: 0 auto; }
.side-card { border: 1px solid rgba(201,173,135,0.15); border-radius: 4px; padding: 48px 40px; position: relative; overflow: hidden; }
.side-card.light-card { background: var(--white); border-color: var(--gold-lt); }
.side-num { font-family: var(--cormo); font-size: 80px; font-weight: 300; color: rgba(201,173,135,0.3); line-height: 1; margin-bottom: -8px; }
.side-title { font-family: var(--serif); font-size: 26px; font-weight: 400; margin-bottom: 16px; color: var(--ink); }
.side-desc { font-size: 15px; line-height: 1.75; font-weight: 300; color: #6a6258; }
.side-features { list-style: none; margin-top: 28px; display: flex; flex-direction: column; gap: 10px; }
.side-features li { font-size: 14px; font-weight: 300; padding-left: 18px; position: relative; color: #6a6258; }
.side-features li::before { content: '—'; position: absolute; left: 0; color: var(--gold); font-size: 12px; }
.flow { max-width: 1000px; margin: 0 auto; }
.flow-steps { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0; position: relative; margin-top: 60px; }
.flow-steps::before { content: ''; position: absolute; top: 36px; left: 12.5%; right: 12.5%; height: 1px; background: linear-gradient(to right, transparent, rgba(201,173,135,0.3), transparent); }
.flow-step { text-align: center; padding: 0 20px; }
.fs-num { width: 72px; height: 72px; border-radius: 50%; background: rgba(201,173,135,0.08); border: 1px solid rgba(201,173,135,0.2); display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; font-family: var(--cormo); font-size: 28px; color: var(--gold); position: relative; z-index: 1; transition: all 0.3s; }
.fs-title { font-family: var(--serif); font-size: 17px; font-weight: 500; margin-bottom: 10px; }
.fs-desc { font-size: 13px; color: rgba(255,255,255,0.4); font-weight: 300; line-height: 1.7; }
.quote-section { padding: 80px 60px; background: var(--gold); text-align: center; }
.q-text { font-family: var(--cormo); font-style: italic; font-size: clamp(28px, 3.5vw, 48px); color: var(--ink); line-height: 1.35; max-width: 700px; margin: 0 auto 16px; font-weight: 300; }
.q-attr { font-size: 12px; letter-spacing: 0.18em; text-transform: uppercase; color: rgba(22,20,15,0.6); font-weight: 500; }
footer { background: #0d0b08; padding: 40px 60px; display: flex; align-items: center; justify-content: space-between; }
.footer-logo { font-family: var(--serif); font-style: italic; font-size: 18px; color: rgba(255,255,255,0.4); }
.footer-note { font-size: 13px; color: rgba(255,255,255,0.2); }
.reveal { opacity: 0; transform: translateY(20px); transition: opacity 0.7s ease, transform 0.7s ease; }
.reveal.visible { opacity: 1; transform: none; }
@keyframes fadeUp { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }
@keyframes fadeIn { from{opacity:0} to{opacity:1} }
.gallery-section { padding: 120px 0; overflow: hidden; background: var(--ink); position: relative; }
.gallery-section::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 1px; background: linear-gradient(to right, transparent, rgba(201,173,135,0.3), transparent); }
.gallery-header { text-align: center; margin-bottom: 80px; padding: 0 60px; }
.gallery-header .s-label { color: var(--gold); font-size: 11px; letter-spacing: 0.3em; margin-bottom: 24px; display: block; }
.gallery-header .s-title { font-size: clamp(48px, 6vw, 80px); margin-bottom: 24px; color: var(--white); font-weight: 300; line-height: 1.1; }
.gallery-header .s-title em { font-style: italic; color: var(--gold); }
.gallery-desc { color: rgba(255,255,255,0.45); font-size: 18px; font-weight: 300; line-height: 1.7; max-width: 600px; margin: 0 auto; }

.marquee-viewport { overflow: hidden; padding: 40px 0; mask-image: linear-gradient(to right, transparent, black 10%, black 90%, transparent); -webkit-mask-image: linear-gradient(to right, transparent, black 10%, black 90%, transparent); }
.marquee-wrapper-h { display: flex; width: max-content; animation: scrollHorizontal 40s linear infinite; }
.marquee-wrapper-h:hover { animation-play-state: paused; }
.marquee-wrapper-h:hover .marquee-item-h { filter: grayscale(80%) brightness(0.5); opacity: 0.5; }

.marquee-item-h { flex: 0 0 auto; width: 360px; height: 500px; overflow: hidden; position: relative; margin-right: 40px; transition: all 0.6s cubic-bezier(0.25, 1, 0.5, 1); cursor: pointer; border-radius: 4px; }
.marquee-item-h:nth-child(even) { transform: translateY(40px); --offset: 40px; }
.marquee-item-h:nth-child(odd) { transform: translateY(0px); --offset: 0px; }

.marquee-item-h img { width: 100%; height: 100%; object-fit: cover; transition: transform 1.2s cubic-bezier(0.25, 1, 0.5, 1); }
.marquee-item-h::after { content: ''; position: absolute; inset: 0; background: linear-gradient(to top, rgba(22,20,15,0.8) 0%, transparent 50%); opacity: 0; transition: opacity 0.6s; }

.marquee-wrapper-h .marquee-item-h:hover { filter: grayscale(0%) brightness(1); opacity: 1; transform: scale(1.05) translateY(calc(var(--offset) - 10px)); z-index: 10; box-shadow: 0 30px 60px rgba(0,0,0,0.5); border: 1px solid rgba(201,173,135,0.3); }
.marquee-item-h:hover img { transform: scale(1.08); }
.marquee-item-h:hover::after { opacity: 1; }

@keyframes scrollHorizontal { from { transform: translateX(0); } to { transform: translateX(-50%); } }

@media(max-width:768px) { nav { padding: 18px 24px; } .section { padding: 64px 24px; } .two-sides { grid-template-columns: 1fr; gap: 24px; } .flow-steps { grid-template-columns: repeat(2,1fr); gap: 40px; } .flow-steps::before { display: none; } .gallery-section { padding: 80px 0; } .marquee-item-h { width: 280px; height: 380px; margin-right: 20px; } .marquee-item-h:nth-child(even) { transform: translateY(20px); --offset: 20px; } }
</style>
</head>
<body>
<nav id="nav">
  <a href="index.php" class="nav-logo">Forever Together</a>
  <div class="nav-links">
    <a href="#how" class="btn btn-ghost">How It Works</a>
    <?php if (currentCouple()): ?>
      <a href="couple-dashboard.php" class="btn btn-ghost">Dashboard</a>
    <?php elseif (currentGuest()): ?>
      <a href="guest-portal.php" class="btn btn-ghost">My Wedding</a>
    <?php else: ?>
      <a href="guest-login.php" class="btn btn-ghost">Guest Login</a>
      <a href="couple-signup.php" class="btn btn-gold">Start Your Wedding</a>
    <?php endif; ?>
  </div>
</nav>

<section class="hero">
  <div class="hero-bg"></div>
  <div class="ring r1"></div><div class="ring r2"></div><div class="ring r3"></div>
  <div class="hero-content">
    <p class="hero-eyebrow">A Wedding Memory Platform</p>
    <h1 class="hero-title">Forever<br><em>Together</em></h1>
    <p class="hero-sub">Create your wedding, invite your guests, and collect every photo into one timeless memory — beautifully.</p>
    <div class="hero-paths">
      <a href="couple-signup.php" class="path-card couple">
        <span class="path-icon">&#128141;</span>
        <h3 class="path-title">We're a Couple</h3>
        <p class="path-desc">Create your wedding, invite guests, collect memories &amp; export a beautiful keepsake.</p>
        <span class="path-btn">Get Started</span>
      </a>
      <a href="guest-login.php" class="path-card guest">
        <span class="path-icon">&#127882;</span>
        <h3 class="path-title">I'm a Guest</h3>
        <p class="path-desc">Log in with your invitation credentials and share your photos from the day.</p>
        <span class="path-btn">Sign In</span>
      </a>
    </div>
  </div>
  <div class="scroll-cue"><div class="scroll-line"></div><span>Scroll</span></div>
</section>

<section class="section section-dark" id="how">
  <div class="flow">
    <p class="s-label reveal" style="text-align:center">How It Works</p>
    <h2 class="s-title reveal" style="text-align:center;max-width:600px;margin:0 auto 12px">From invitation to keepsake,<br>all in one place</h2>
    <p class="s-sub reveal" style="text-align:center;margin:0 auto 0">Everything you need to capture and preserve your wedding memories — beautifully and effortlessly.</p>
    <div class="flow-steps" style="margin-top:60px">
      <div class="flow-step reveal"><div class="fs-num">1</div><h3 class="fs-title">Create your wedding</h3><p class="fs-desc">Set up your profile, choose a theme, add your date and venue details.</p></div>
      <div class="flow-step reveal"><div class="fs-num">2</div><h3 class="fs-title">Import your guest list</h3><p class="fs-desc">Upload a CSV — we auto-generate accounts and beautiful invite cards for every guest.</p></div>
      <div class="flow-step reveal"><div class="fs-num">3</div><h3 class="fs-title">Guests upload photos</h3><p class="fs-desc">Guests log in with their personal credentials and share their favourite moments.</p></div>
      <div class="flow-step reveal"><div class="fs-num">4</div><h3 class="fs-title">Export your keepsake</h3><p class="fs-desc">Curate the best photos into a photo book or a cinematic video slideshow.</p></div>
    </div>
  </div>
</section>

<section class="section section-light">
  <div style="max-width:1100px;margin:0 auto">
    <p class="s-label reveal">Two Experiences</p>
    <h2 class="s-title reveal" style="color:var(--ink)">Built for both sides<br>of the celebration</h2>
    <div class="two-sides reveal">
      <div class="side-card light-card">
        <div class="side-num">01</div>
        <h3 class="side-title">The Couple</h3>
        <p class="side-desc">Everything you need to run your wedding memory platform — from invitations to final keepsake.</p>
        <ul class="side-features">
          <li>5 beautiful color themes for your page</li>
          <li>Import guests from a CSV file</li>
          <li>Auto-generated invite cards for every guest</li>
          <li>View all uploaded photos in one gallery</li>
          <li>Select favourites &amp; create a memory page</li>
          <li>Export as PDF photo book or video slideshow</li>
        </ul>
      </div>
      <div class="side-card light-card">
        <div class="side-num">02</div>
        <h3 class="side-title">The Guests</h3>
        <p class="side-desc">A personal, elegant experience — from receiving the invitation to contributing their photos.</p>
        <ul class="side-features">
          <li>Personalised login from the invite card</li>
          <li>See the wedding details &amp; countdown</li>
          <li>Upload photos after the wedding date</li>
          <li>View only their own uploaded memories</li>
          <li>Simple, beautiful — no app to download</li>
        </ul>
      </div>
    </div>
  </div>
</section>

<section class="gallery-section">
  <div class="gallery-header">
    <p class="s-label reveal">Our Beautiful Journey</p>
    <h2 class="s-title reveal">Every <em>detail</em> preserved</h2>
    <p class="gallery-desc reveal">A timeless collection of moments that will last a lifetime. Witness the love, the joy, and the magic of our special day.</p>
  </div>
  <div class="marquee-viewport reveal">
    <div class="marquee-wrapper-h">
      <!-- Original 5 items -->
      <div class="marquee-item-h"><img src="assets/images/gallery1.jpg" alt="Signing the Certificate" loading="lazy"></div>
      <div class="marquee-item-h"><img src="assets/images/gallery2.jpg" alt="Holding Flowers and Shoes" loading="lazy"></div>
      <div class="marquee-item-h"><img src="assets/images/gallery3.jpg" alt="Walking Together" loading="lazy"></div>
      <div class="marquee-item-h"><img src="assets/images/gallery4.jpg" alt="Evening Walk" loading="lazy"></div>
      <div class="marquee-item-h"><img src="assets/images/gallery5.jpg" alt="A White Rose" loading="lazy"></div>
      <!-- Duplicated 5 items for infinite scrolling -->
      <div class="marquee-item-h"><img src="assets/images/gallery1.jpg" alt="Signing the Certificate" loading="lazy"></div>
      <div class="marquee-item-h"><img src="assets/images/gallery2.jpg" alt="Holding Flowers and Shoes" loading="lazy"></div>
      <div class="marquee-item-h"><img src="assets/images/gallery3.jpg" alt="Walking Together" loading="lazy"></div>
      <div class="marquee-item-h"><img src="assets/images/gallery4.jpg" alt="Evening Walk" loading="lazy"></div>
      <div class="marquee-item-h"><img src="assets/images/gallery5.jpg" alt="A White Rose" loading="lazy"></div>
    </div>
  </div>
</section>

<div class="quote-section">
  <p class="q-text">"The best photographs aren't taken by the photographer — they're taken by the people who were truly present."</p>
  <p class="q-attr">— A note on wedding memories</p>
</div>

<section class="section section-dark" style="text-align:center">
  <p class="s-label reveal" style="text-align:center">Begin Your Story</p>
  <h2 class="s-title reveal" style="text-align:center;max-width:600px;margin:0 auto 16px">Ready to create your<br>wedding memory book?</h2>
  <p class="s-sub reveal" style="text-align:center;margin:0 auto 40px">Takes less than 5 minutes to set up. Your guests will thank you.</p>
  <div style="display:flex;gap:16px;justify-content:center;flex-wrap:wrap" class="reveal">
    <a href="couple-signup.php" class="btn btn-gold">Create Your Wedding</a>
    <a href="guest-login.php" class="btn btn-ghost">I'm a Guest &rarr;</a>
  </div>
</section>

<footer>
  <span class="footer-logo">Forever Together</span>
  <span class="footer-note">A private wedding memory platform</span>
</footer>

<script>
  window.addEventListener('scroll', ()=> document.getElementById('nav').classList.toggle('scrolled', scrollY > 60));
  const io = new IntersectionObserver(entries => entries.forEach(e => { if(e.isIntersecting) e.target.classList.add('visible'); }), {threshold:0.12});
  document.querySelectorAll('.reveal').forEach(el => io.observe(el));
</script>
</body>
</html>
