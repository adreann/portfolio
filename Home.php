<?php
// All editable content (name, bio, skills, projects, links) lives in config.php
require_once __DIR__ . '/config.php';

// Path to your profile photo — set $avatarUrl in config.php (e.g. "assets/me.jpg").
// Falls back to a placeholder silhouette if it isn't set yet.
$avatarUrl = $avatarUrl ?? 'https://api.dicebear.com/7.x/initials/svg?seed=' . urlencode($shortName ?? 'AA') . '&backgroundType=gradientLinear&backgroundColor=6c5ce7,29d3c7';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $shortName; ?> — <?php echo $title; ?></title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700&family=Inter:wght@400;500&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

<style>
  :root{
    --void:        #05060f;
    --deep-space:  #0d1024;
    --panel:       #11152a;
    --nebula:      #6c5ce7;
    --nebula-teal: #29d3c7;
    --starlight:   #f5f3ff;
    --comet-gold:  #ffd166;
    --dim-text:    #9d9bc2;
    --border:      rgba(255,255,255,0.08);
  }

  *{ box-sizing:border-box; }

  html{ scroll-behavior:smooth; }

  body{
    margin:0;
    background: radial-gradient(ellipse at 50% 0%, var(--deep-space) 0%, var(--void) 70%);
    color: var(--starlight);
    font-family:'Inter', sans-serif;
    position:relative;
  }

  #galaxy-canvas{
    position:fixed;
    top:0; left:0;
    width:100%; height:100%;
    z-index:0;
  }

  .content{ position:relative; z-index:2; }

  /* ---------- Nav ---------- */
  nav.navbar{
    background: rgba(5,6,15,0.55);
    backdrop-filter: blur(8px);
    border-bottom: 1px solid var(--border);
  }
  nav .navbar-brand{
    font-family:'Space Grotesk', sans-serif;
    font-weight:700;
    color: var(--starlight) !important;
  }
  nav .nav-link{
    font-family:'JetBrains Mono', monospace;
    font-size:0.85rem;
    color: var(--dim-text) !important;
    letter-spacing:0.03em;
  }
  nav .nav-link:hover{ color: var(--nebula-teal) !important; }

  /* ---------- Shared section styles ---------- */
  section{ padding: 6rem 1.5rem; max-width:1100px; margin:0 auto; }

  .eyebrow{
    font-family:'JetBrains Mono', monospace;
    font-size:0.8rem;
    letter-spacing:0.25em;
    text-transform:uppercase;
    color: var(--nebula-teal);
    margin-bottom:0.75rem;
  }
  .eyebrow::before{ content:"// "; color: var(--dim-text); }

  h2.section-title{
    font-family:'Space Grotesk', sans-serif;
    font-weight:700;
    font-size: clamp(1.8rem, 4vw, 2.6rem);
    margin-bottom:2rem;
  }

  .reveal{
    opacity:0;
    transform: translateY(20px);
    transition: opacity 0.7s ease, transform 0.7s ease;
  }
  .reveal.is-visible{ opacity:1; transform:translateY(0); }

  /* ---------- Hero ---------- */
  .hero{
    min-height:90vh;
    display:flex;
    align-items:center;
    gap: 3rem;
  }
  .hero-text{ flex: 1 1 480px; }
  .hero h1{
    font-family:'Space Grotesk', sans-serif;
    font-weight:700;
    font-size: clamp(2.4rem, 6vw, 4rem);
    line-height:1.1;
    margin:0.5rem 0 1rem 0;
    background: linear-gradient(120deg, var(--starlight) 30%, var(--nebula-teal) 70%, var(--comet-gold) 100%);
    -webkit-background-clip:text;
    background-clip:text;
    -webkit-text-fill-color:transparent;
  }
  .hero .title{
    font-family:'JetBrains Mono', monospace;
    color: var(--nebula-teal);
    font-size:1.1rem;
    margin-bottom:1.25rem;
  }
  .hero .bio{
    max-width:620px;
    color: var(--dim-text);
    font-size:1.05rem;
    line-height:1.7;
    margin-bottom:2rem;
  }
  .hero .cta-row a{
    font-family:'JetBrains Mono', monospace;
    font-size:0.9rem;
    text-decoration:none;
    padding: 0.75rem 1.6rem;
    border-radius:999px;
    margin-right:0.75rem;
    display:inline-block;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }
  .cta-row .primary{
    background: linear-gradient(120deg, var(--nebula-teal), var(--nebula));
    color: var(--void);
  }
  .cta-row .secondary{
    border:1px solid var(--border);
    color: var(--starlight);
  }
  .cta-row a:hover{ transform: translateY(-2px); }

  /* ---------- Hero profile card (tilt + glow) ---------- */
  .pc-wrapper{
    --pc-x: 50%;
    --pc-y: 50%;
    --pc-rot-x: 0deg;
    --pc-rot-y: 0deg;
    --pc-opacity: 0;
    flex: 0 0 auto;
    perspective: 700px;
    position: relative;
  }
  .pc-behind{
    position:absolute;
    inset:-20px;
    z-index:0;
    pointer-events:none;
    border-radius:32px;
    background: radial-gradient(circle at var(--pc-x) var(--pc-y), rgba(41,211,199,0.55) 0%, rgba(108,92,231,0.35) 35%, transparent 65%);
    filter: blur(35px);
    opacity: calc(0.55 + 0.45 * var(--pc-opacity));
    transition: opacity 0.3s ease;
  }
  .pc-shell{
    position:relative;
    z-index:1;
    width: min(300px, 60vw);
    aspect-ratio: 0.78;
    border-radius: 26px;
    transform: rotateX(var(--pc-rot-y)) rotateY(var(--pc-rot-x));
    transition: transform 1s ease;
    box-shadow: 0 25px 50px -12px rgba(0,0,0,0.65);
    overflow:hidden;
    background: var(--panel);
    border: 1px solid var(--border);
  }
  .pc-shell.active{ transition:none; }
  .pc-holo{
    position:absolute;
    inset:0;
    z-index:2;
    pointer-events:none;
    opacity:0;
    mix-blend-mode: color-dodge;
    background: repeating-linear-gradient(
      115deg,
      var(--nebula) 0%,
      var(--nebula-teal) 12%,
      var(--comet-gold) 24%,
      var(--nebula) 36%
    );
    background-size: 250% 250%;
    background-position: var(--pc-x) var(--pc-y);
    transition: opacity 0.4s ease;
  }
  .pc-glare{
    position:absolute;
    inset:0;
    z-index:3;
    pointer-events:none;
    background: radial-gradient(circle at var(--pc-x) var(--pc-y), rgba(255,255,255,0.35), transparent 60%);
    mix-blend-mode: overlay;
    opacity:0;
    transition: opacity 0.4s ease;
  }
  .pc-avatar{
    position:absolute;
    inset:0;
    width:100%;
    height:100%;
    object-fit:cover;
    z-index:0;
  }
  .pc-badge{
    position:absolute;
    left:14px; right:14px; bottom:14px;
    z-index:4;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:0.5rem;
    background: rgba(5,6,15,0.55);
    backdrop-filter: blur(12px);
    border:1px solid var(--border);
    border-radius:14px;
    padding:0.6rem 0.85rem;
    font-family:'JetBrains Mono', monospace;
  }
  .pc-badge .pc-dot{
    width:8px; height:8px; border-radius:50%;
    background: var(--nebula-teal);
    box-shadow: 0 0 8px var(--nebula-teal);
    flex-shrink:0;
  }
  .pc-badge .pc-status-text{
    font-size:0.72rem;
    color: var(--starlight);
    line-height:1.2;
  }

  @media (max-width: 767px){
    .hero{ flex-direction:column; text-align:left; padding-top:2rem; }
    .pc-wrapper{ order:-1; margin-bottom:1rem; }
  }

  /* ---------- Skills ---------- */
  .skill-group{ margin-bottom:2rem; }
  .skill-group h3{
    font-family:'JetBrains Mono', monospace;
    font-size:0.95rem;
    color: var(--dim-text);
    margin-bottom:0.9rem;
  }
  .pill{
    display:inline-block;
    font-family:'JetBrains Mono', monospace;
    font-size:0.85rem;
    padding:0.4rem 0.9rem;
    border-radius:999px;
    border:1px solid var(--border);
    background: rgba(108,92,231,0.08);
    color: var(--starlight);
    margin:0 0.5rem 0.5rem 0;
  }

  /* ---------- Projects ---------- */
  .project-card{
    background: var(--panel);
    border:1px solid var(--border);
    border-radius:14px;
    padding:1.75rem;
    height:100%;
    transition: transform 0.25s ease, border-color 0.25s ease;
  }
  .project-card:hover{
    transform: translateY(-4px);
    border-color: rgba(41,211,199,0.4);
  }
  .flagship-tag{
    font-family:'JetBrains Mono', monospace;
    font-size:0.7rem;
    letter-spacing:0.1em;
    text-transform:uppercase;
    color: var(--comet-gold);
    border:1px solid rgba(255,209,102,0.4);
    padding:0.2rem 0.6rem;
    border-radius:999px;
    display:inline-block;
    margin-bottom:0.75rem;
  }
  .project-card h3{
    font-family:'Space Grotesk', sans-serif;
    font-size:1.25rem;
    margin-bottom:0.4rem;
  }
  .project-card .stack{
    font-family:'JetBrains Mono', monospace;
    font-size:0.8rem;
    color: var(--nebula-teal);
    margin-bottom:0.75rem;
  }
  .project-card p{
    color: var(--dim-text);
    font-size:0.92rem;
    line-height:1.6;
    margin:0;
  }

  /* ---------- Contact ---------- */
  .contact-box{
    background: var(--panel);
    border:1px solid var(--border);
    border-radius:16px;
    padding:2.5rem;
    text-align:center;
  }
  .contact-links a{
    font-family:'JetBrains Mono', monospace;
    color: var(--starlight);
    text-decoration:none;
    border:1px solid var(--border);
    border-radius:999px;
    padding:0.6rem 1.4rem;
    margin:0.4rem;
    display:inline-block;
    transition: border-color 0.2s ease, color 0.2s ease;
  }
  .contact-links a:hover{
    border-color: var(--nebula-teal);
    color: var(--nebula-teal);
  }

  footer{
    text-align:center;
    padding:2rem 1rem;
    color: var(--dim-text);
    font-family:'JetBrains Mono', monospace;
    font-size:0.8rem;
    position:relative;
    z-index:2;
  }

  /* ---------- Image Stack (project modal) ---------- */
  .stack-wrapper{
    display:flex;
    justify-content:center;
    align-items:center;
    padding: 2rem 0 1rem 0;
  }
  .stack-container{
    position:relative;
    width:320px;
    height:320px;
    perspective:900px;
  }
  .stack-card{
    position:absolute;
    top:0; left:0;
    width:100%; height:100%;
    border-radius:1rem;
    overflow:hidden;
    cursor:grab;
    box-shadow:0 20px 40px rgba(0,0,0,0.5);
    transition: transform 0.35s cubic-bezier(0.22,1,0.36,1);
    will-change:transform;
  }
  .stack-card.dragging{ transition:none; cursor:grabbing; }
  .stack-card img{
    width:100%; height:100%;
    object-fit:cover;
    pointer-events:none;
    user-select:none;
    -webkit-user-drag:none;
  }
  .stack-hint{
    text-align:center;
    color: var(--dim-text);
    font-family:'JetBrains Mono', monospace;
    font-size:0.8rem;
    margin-top:0.5rem;
  }

  @media (prefers-reduced-motion: reduce){
    .reveal{ opacity:1 !important; transform:none !important; transition:none !important; }
    .pc-shell{ transition:none !important; }
  }
</style>
</head>
<body>

<canvas id="galaxy-canvas"></canvas>

<div class="content">

  <nav class="navbar navbar-expand-lg fixed-top px-3">
    <div class="container-fluid">
      <a class="navbar-brand" href="#top"><?php echo $shortName; ?></a>
      <div class="d-flex">
        <a class="nav-link px-2" href="#about">about</a>
        <a class="nav-link px-2" href="#skills">skills</a>
        <a class="nav-link px-2" href="#projects">projects</a>
        <a class="nav-link px-2" href="#contact">contact</a>
      </div>
    </div>
  </nav>

  <section id="top" class="hero">

    <div class="hero-text">
      <div class="eyebrow">whoami</div>
      <h1><?php echo htmlspecialchars($fullName); ?></h1>
      <div class="title"><?php echo htmlspecialchars($title); ?></div>
      <p class="bio"><?php echo htmlspecialchars($bio); ?></p>
      <div class="cta-row">
        <a href="#projects" class="primary">View Projects</a>
        <a href="#contact" class="secondary">Get In Touch</a>
      </div>
    </div>

    <div class="pc-wrapper" id="pcWrapper">
      <div class="pc-behind"></div>
      <div class="pc-shell" id="pcShell">
        <img class="pc-avatar" src="<?php echo htmlspecialchars($avatarUrl); ?>" alt="<?php echo htmlspecialchars($fullName); ?>">
        <div class="pc-holo"></div>
        <div class="pc-glare"></div>
        <div class="pc-badge">
          <span class="pc-dot"></span>
          <span class="pc-status-text"><?php echo htmlspecialchars($shortName); ?> // open to work</span>
        </div>
      </div>
    </div>

  </section>

  <section id="about" class="reveal">
    <div class="eyebrow">about</div>
    <h2 class="section-title">A bit more about me</h2>
      <p class="bio" style="max-width:720px;">
        I'm an aspiring Software Engineer passionate about building modern, user-friendly web applications and solving real-world problems through technology. I enjoy working with PHP, JavaScript, MySQL, and responsive web design while continuously expanding my skills through hands-on projects.
      </p>

      <p class="bio" style="max-width:720px;">
        I believe in learning by building. Every project helps me improve my problem-solving, coding, and software development skills. Outside of programming, I enjoy going to the gym and continuously challenging myself to grow both personally and professionally.
      </p>
  </section>

  <section id="skills" class="reveal">
    <div class="eyebrow">skills.stack</div>
    <h2 class="section-title">What I work with</h2>
    <div class="row">
      <?php foreach ($skillGroups as $group => $items): ?>
        <div class="col-md-4 skill-group">
          <h3><?php echo htmlspecialchars($group); ?></h3>
          <div>
            <?php foreach ($items as $item): ?>
              <span class="pill"><?php echo htmlspecialchars($item); ?></span>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

<section id="projects" class="reveal">
    <div class="eyebrow">projects.log</div>
    <h2 class="section-title">Things I've built</h2>

    <div class="row g-4">

        <?php foreach ($projects as $p): ?>

        <div class="col-md-6">

            <div class="project-card"
                 style="cursor:pointer;"
                 data-bs-toggle="modal"
                 data-bs-target="#projectModal"
                 data-title="<?php echo htmlspecialchars($p['name']); ?>"
                 data-images='<?php echo json_encode($p["images"]); ?>'>

                <?php if ($p['flagship']): ?>
                    <span class="flagship-tag">
                        Flagship Project
                    </span>
                <?php endif; ?>

                <h3><?php echo htmlspecialchars($p['name']); ?></h3>

                <div class="stack">
                    <?php echo htmlspecialchars($p['stack']); ?>
                </div>

                <p>
                    <?php echo htmlspecialchars($p['desc']); ?>
                </p>

            </div>

        </div>

        <?php endforeach; ?>

    </div>

</section>
  <section id="contact" class="reveal">
    <div class="eyebrow">contact.channel</div>
    <h2 class="section-title">Let's talk</h2>
    <div class="contact-box">
      <p class="bio" style="margin:0 auto 1rem auto;">
        Open to internships and junior software engineer roles. Reach out through any of these:
      </p>
      <div class="contact-links">
        <a href="mailto:<?php echo htmlspecialchars($email); ?>">email</a>
        <a href="<?php echo htmlspecialchars($github); ?>" target="_blank" rel="noopener">github</a>
        <a href="<?php echo htmlspecialchars($linkedin); ?>" target="_blank" rel="noopener">linkedin</a>
        <a href="<?php echo htmlspecialchars($whatsaap); ?>" target="_blank" rel="noopener">Whatsaap Me</a>
      </div>
    </div>
  </section>

  <footer>
    &copy; <?php echo date("Y"); ?> <?php echo htmlspecialchars($fullName); ?>. Built with PHP, Bootstrap, and a lot of debugging.
  </footer>

</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
  const canvas = document.getElementById('galaxy-canvas');
  const ctx = canvas.getContext('2d');
  let width, height;
  const glyphs = ['0','1','{','}','<','/','>'];
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function resize(){
    width = canvas.width = window.innerWidth;
    height = canvas.height = document.documentElement.scrollHeight;
  }
  resize();
  window.addEventListener('resize', resize);

  const STAR_COUNT = 90;
  const stars = [];
  for(let i=0; i<STAR_COUNT; i++){
    stars.push({
      x: Math.random()*width,
      y: Math.random()*height,
      r: Math.random()*1.4 + 0.4,
      isGlyph: Math.random() < 0.1,
      glyph: glyphs[Math.floor(Math.random()*glyphs.length)],
      baseAlpha: Math.random()*0.5 + 0.25,
      twinkleSpeed: Math.random()*0.015 + 0.004,
      phase: Math.random()*Math.PI*2
    });
  }

  let t = 0;
  function draw(){
    ctx.clearRect(0,0,width,height);
    stars.forEach(s => {
      const twinkle = prefersReducedMotion ? s.baseAlpha : s.baseAlpha + Math.sin(t*s.twinkleSpeed + s.phase)*0.2;
      const alpha = Math.max(0, Math.min(1, twinkle));
      if(s.isGlyph){
        ctx.font = `${s.r*8+8}px 'JetBrains Mono', monospace`;
        ctx.fillStyle = `rgba(41, 211, 199, ${alpha*0.45})`;
        ctx.fillText(s.glyph, s.x, s.y);
      } else {
        ctx.beginPath();
        ctx.arc(s.x, s.y, s.r, 0, Math.PI*2);
        ctx.fillStyle = `rgba(245, 243, 255, ${alpha})`;
        ctx.fill();
      }
    });
    t++;
    requestAnimationFrame(draw);
  }
  draw();

  // Scroll-reveal for sections
  const revealEls = document.querySelectorAll('.reveal');
  if('IntersectionObserver' in window && !prefersReducedMotion){
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if(entry.isIntersecting){
          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.15 });
    revealEls.forEach(el => observer.observe(el));
  } else {
    revealEls.forEach(el => el.classList.add('is-visible'));
  }

  // ---------- Vanilla JS tilt + glow + holo card (ported from ProfileCard, no React needed) ----------
  (function initProfileCard(){
    const shell = document.getElementById('pcShell');
    const wrapper = document.getElementById('pcWrapper');
    if(!shell || !wrapper) return;
    if(prefersReducedMotion) return;

    let raf = null;
    let curX = shell.clientWidth/2, curY = shell.clientHeight/2;
    let tgtX = curX, tgtY = curY;

    function apply(x, y){
      const w = shell.clientWidth || 1;
      const h = shell.clientHeight || 1;
      const px = Math.min(100, Math.max(0, (100/w)*x));
      const py = Math.min(100, Math.max(0, (100/h)*y));
      const cx = px - 50, cy = py - 50;
      wrapper.style.setProperty('--pc-x', px + '%');
      wrapper.style.setProperty('--pc-y', py + '%');
      wrapper.style.setProperty('--pc-rot-x', (cy/6) + 'deg');
      wrapper.style.setProperty('--pc-rot-y', (-cx/8) + 'deg');
    }

    function step(){
      curX += (tgtX - curX) * 0.15;
      curY += (tgtY - curY) * 0.15;
      apply(curX, curY);
      if(Math.abs(tgtX-curX) > 0.3 || Math.abs(tgtY-curY) > 0.3){
        raf = requestAnimationFrame(step);
      } else {
        raf = null;
      }
    }

    function start(){ if(!raf) raf = requestAnimationFrame(step); }

    shell.addEventListener('pointerenter', () => shell.classList.add('active'));
    shell.addEventListener('pointermove', (e) => {
      const rect = shell.getBoundingClientRect();
      tgtX = e.clientX - rect.left;
      tgtY = e.clientY - rect.top;
      start();
    });
    shell.addEventListener('pointerleave', () => {
      shell.classList.remove('active');
      tgtX = shell.clientWidth/2;
      tgtY = shell.clientHeight/2;
      start();
    });
  })();

$('.project-card').on('click', function () {

    const title = $(this).data('title');
    const images = $(this).data('images');

    $('#modalTitle').text(title);

    let html = "";

    images.forEach(function(image, index){

        html += `
            <div class="carousel-item ${index === 0 ? 'active' : ''}">
                <img src="${image}"
                     class="d-block w-100 rounded"
                     style="max-height:700px; object-fit:contain;">
            </div>
        `;

    });

    $('#carouselImages').html(html);

});
</script>

<div class="modal fade" id="projectModal" tabindex="-1">

    <div class="modal-dialog modal-xl modal-dialog-centered">

        <div class="modal-content bg-dark border-secondary">

            <div class="modal-header border-secondary">

                <h5 class="modal-title" id="modalTitle">
                    Project Screenshots
                </h5>

                <button class="btn-close btn-close-white"
                        data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body">

                <div id="projectCarousel"
                     class="carousel slide">

                    <div class="carousel-inner"
                         id="carouselImages">

                    </div>

                    <button class="carousel-control-prev"
                            type="button"
                            data-bs-target="#projectCarousel"
                            data-bs-slide="prev">

                        <span class="carousel-control-prev-icon"></span>

                    </button>

                    <button class="carousel-control-next"
                            type="button"
                            data-bs-target="#projectCarousel"
                            data-bs-slide="next">

                        <span class="carousel-control-next-icon"></span>

                    </button>

                </div>

            </div>

        </div>

    </div>

</div>
</body>
</html>