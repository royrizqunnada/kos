<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $kos['name'] }} Kos Putri — Modern Student Living Semarang</title>
<meta name="description" content="Kos putri modern dekat UNDIP Tembalang, Semarang. Kamar mandi dalam, AC, WiFi, CCTV 24 jam.">
<link rel="icon" href="{{ asset('logo.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  :root{
    --green:#4a6a2f;--green-deep:#3a5424;--green-soft:#eef3e7;--cream:#faf8f2;
    --ink:#23291d;--ink-soft:#5e6553;--line:#e7e4d8;--gold:#c79a3e;
    --shadow:0 18px 50px rgba(58,84,36,.12);--serif:'Fraunces',serif;--sans:'Plus Jakarta Sans',sans-serif;
  }
  *{margin:0;padding:0;box-sizing:border-box;}
  html{scroll-behavior:smooth;}
  body{font-family:var(--sans);background:var(--cream);color:var(--ink);line-height:1.6;overflow-x:hidden;}
  .wrap{max-width:1080px;margin:0 auto;padding:0 24px;}
  a{color:inherit;text-decoration:none;}
  .topbar{position:sticky;top:0;z-index:100;background:rgba(250,248,242,.85);backdrop-filter:blur(12px);border-bottom:1px solid var(--line);}
  .topbar .wrap{display:flex;align-items:center;justify-content:space-between;height:68px;}
  .nav-brand{display:flex;align-items:center;gap:10px;font-family:var(--serif);font-weight:600;font-size:18px;color:var(--green-deep);}
  .nav-brand img{width:38px;height:38px;object-fit:contain;}
  .nav-links{display:flex;gap:28px;font-size:14px;font-weight:500;color:var(--ink-soft);}
  .nav-links a:hover{color:var(--green);}
  .btn{display:inline-flex;align-items:center;gap:8px;background:var(--green);color:#fff!important;padding:11px 20px;border-radius:40px;font-weight:600;font-size:14px;transition:.25s;border:none;cursor:pointer;}
  .btn:hover{background:var(--green-deep);transform:translateY(-2px);box-shadow:var(--shadow);}
  .btn.ghost{background:transparent;color:var(--green)!important;border:1.5px solid var(--green);}
  .btn.ghost:hover{background:var(--green-soft);}
  @media(max-width:760px){.nav-links{display:none;}}
  .hero{position:relative;padding:70px 0 80px;overflow:hidden;}
  .hero::before{content:"";position:absolute;top:-120px;right:-120px;width:420px;height:420px;border-radius:50%;background:radial-gradient(circle,var(--green-soft),transparent 70%);z-index:0;}
  .hero .wrap{position:relative;z-index:1;display:grid;grid-template-columns:1.05fr .95fr;gap:48px;align-items:center;}
  .hero-text{order:1;}
  .hero-visual{order:2;}
  .eyebrow{display:inline-flex;align-items:center;gap:8px;background:var(--green-soft);color:var(--green-deep);font-size:13px;font-weight:600;padding:7px 15px;border-radius:30px;margin-bottom:20px;letter-spacing:.02em;}
  h1{font-family:var(--serif);font-weight:600;font-size:clamp(34px,5vw,54px);line-height:1.05;letter-spacing:-.02em;color:var(--green-deep);}
  h1 em{font-style:italic;color:var(--gold);}
  .hero p.lead{font-size:17px;color:var(--ink-soft);margin:18px 0 28px;max-width:460px;}
  .hero-cta{display:flex;gap:12px;flex-wrap:wrap;}
  @media(max-width:860px){.hero .wrap{grid-template-columns:1fr;gap:32px;}.hero-visual{order:-1;}}
  section{padding:64px 0;}
  .sec-tag{font-size:13px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:var(--gold);margin-bottom:10px;}
  .sec-title{font-family:var(--serif);font-size:clamp(26px,4vw,38px);font-weight:600;color:var(--green-deep);letter-spacing:-.02em;margin-bottom:8px;}
  .sec-sub{color:var(--ink-soft);max-width:560px;margin-bottom:36px;}
  .loc{background:var(--green-deep);color:#fff;border-radius:32px;padding:44px;display:grid;grid-template-columns:1.3fr .7fr;gap:36px;align-items:center;}
  .loc h2{font-family:var(--serif);font-size:28px;font-weight:600;margin-bottom:14px;}
  .loc address{font-style:normal;font-size:16px;line-height:1.8;opacity:.92;}
  .loc .dist{display:inline-flex;align-items:center;gap:10px;background:rgba(255,255,255,.12);padding:10px 18px;border-radius:30px;margin-top:20px;font-weight:600;font-size:15px;}
  .loc .map-box{display:flex;flex-direction:column;gap:14px;}
  .loc .pin{font-size:54px;text-align:center;}
  .btn.light{background:#fff;color:var(--green-deep)!important;justify-content:center;}
  .btn.light:hover{background:var(--cream);}
  @media(max-width:760px){.loc{grid-template-columns:1fr;padding:32px;text-align:center;}.loc .dist{margin-inline:auto;}}
  .fac-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:24px;}
  .fac-card{background:#fff;border:1px solid var(--line);border-radius:24px;padding:30px;box-shadow:0 6px 20px rgba(58,84,36,.05);}
  .fac-card h3{font-family:var(--serif);font-size:21px;color:var(--green-deep);display:flex;align-items:center;gap:10px;margin-bottom:18px;}
  .fac-card ul{list-style:none;display:grid;gap:12px;}
  .fac-card li{display:flex;align-items:center;gap:11px;font-size:15px;color:var(--ink);}
  .fac-card li .tick{width:22px;height:22px;border-radius:50%;background:var(--green-soft);color:var(--green);display:grid;place-items:center;font-size:12px;font-weight:700;flex-shrink:0;}
  @media(max-width:680px){.fac-grid{grid-template-columns:1fr;}}
  .price-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:18px;}
  .pcard{background:#fff;border:1px solid var(--line);border-radius:22px;padding:26px 22px;text-align:center;transition:.25s;position:relative;display:block;color:inherit;}
  .pcard:hover{transform:translateY(-6px);box-shadow:var(--shadow);border-color:var(--green);}
  .pcard.best{background:var(--green);color:#fff;border-color:var(--green);}
  .pcard.best .type,.pcard.best .per{color:rgba(255,255,255,.85);}
  .pcard .type{font-size:14px;font-weight:600;color:var(--ink-soft);letter-spacing:.05em;}
  .pcard .amt{font-family:var(--serif);font-size:30px;font-weight:600;color:var(--green-deep);margin:8px 0 2px;}
  .pcard.best .amt{color:#fff;}
  .pcard .per{font-size:13px;color:var(--ink-soft);}
  .pcard .badge{position:absolute;top:-12px;left:50%;transform:translateX(-50%);background:var(--gold);color:#fff;font-size:11px;font-weight:700;padding:5px 12px;border-radius:20px;letter-spacing:.04em;}
  .avail{margin-top:12px;font-size:12px;font-weight:700;padding:5px 0;border-radius:20px;}
  .avail.ok{background:var(--green-soft);color:var(--green-deep);}
  .pcard.best .avail.ok{background:rgba(255,255,255,.2);color:#fff;}
  .avail.full{background:#f3eee2;color:var(--ink-soft);}
  .pcard.best .avail.full{background:rgba(255,255,255,.2);color:rgba(255,255,255,.8);}
  @media(max-width:780px){.price-grid{grid-template-columns:repeat(2,1fr);}}
  .terms{background:var(--green-soft);border-radius:24px;padding:30px 34px;display:flex;align-items:center;gap:18px;font-size:15px;color:var(--green-deep);}
  .terms .ic{font-size:30px;}
  .cta{text-align:center;padding:76px 0;}
  .cta h2{font-family:var(--serif);font-size:clamp(28px,4vw,42px);font-weight:600;color:var(--green-deep);margin-bottom:14px;}
  .cta p{color:var(--ink-soft);margin-bottom:26px;font-size:17px;}
  .cta .btn{font-size:16px;padding:15px 30px;}
  footer{background:var(--green-deep);color:#fff;padding:48px 0 30px;}
  footer .wrap{display:flex;flex-direction:column;align-items:center;gap:14px;text-align:center;}
  footer img{width:60px;height:60px;object-fit:contain;background:#fff;border-radius:16px;padding:6px;}
  footer .fbrand{font-family:var(--serif);font-size:22px;font-weight:600;}
  footer .ftag{opacity:.75;font-size:14px;}
  footer .fcontact{opacity:.9;font-size:14px;margin-top:6px;line-height:1.9;}
  footer .copy{opacity:.5;font-size:12px;margin-top:18px;border-top:1px solid rgba(255,255,255,.15);padding-top:18px;width:100%;}
  .socials{display:flex;gap:14px;margin-top:6px;flex-wrap:wrap;justify-content:center;}
  .socials a{display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.12);padding:9px 16px;border-radius:30px;font-size:14px;font-weight:600;transition:.25s;}
  .socials a:hover{background:#fff;color:var(--green-deep);transform:translateY(-2px);}
  .socials svg{width:18px;height:18px;fill:currentColor;}
  .cta-socials{display:flex;gap:14px;justify-content:center;margin-top:20px;flex-wrap:wrap;}
  .cta-socials a{display:inline-flex;align-items:center;gap:8px;color:var(--green-deep);font-weight:600;font-size:15px;padding:8px 4px;transition:.2s;}
  .cta-socials a:hover{color:var(--gold);}
  .cta-socials svg{width:20px;height:20px;fill:currentColor;}
  .carousel{position:relative;border-radius:28px;overflow:hidden;box-shadow:var(--shadow);aspect-ratio:4/3;background:var(--green-deep);}
  .slides{position:absolute;inset:0;}
  .slide{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;color:#fff;padding:34px;opacity:0;transform:scale(1.04);transition:opacity .8s ease,transform .8s ease;}
  .slide.active{opacity:1;transform:scale(1);}
  .slide::before{content:"";position:absolute;top:-60px;right:-60px;width:200px;height:200px;border-radius:50%;background:rgba(255,255,255,.08);}
  .slide::after{content:"";position:absolute;bottom:-70px;left:-50px;width:180px;height:180px;border-radius:50%;background:rgba(255,255,255,.06);}
  .slide .s-ic{width:78px;height:78px;border-radius:24px;background:rgba(255,255,255,.16);display:grid;place-items:center;font-size:38px;margin-bottom:18px;backdrop-filter:blur(4px);}
  .slide h3{font-family:var(--serif);font-size:27px;font-weight:600;margin-bottom:8px;position:relative;}
  .slide p{font-size:15px;opacity:.92;max-width:260px;position:relative;line-height:1.5;}
  .slide.s1{background:linear-gradient(140deg,#3a5424,#5a7d36);}
  .slide.s2{background:linear-gradient(140deg,#4a6a2f,#6b8a3e);}
  .slide.s3{background:linear-gradient(140deg,#2f4a22,#4a6a2f);}
  .slide.s4{background:linear-gradient(140deg,#3a5424,#c79a3e);}
  .dots{position:absolute;bottom:16px;left:50%;transform:translateX(-50%);display:flex;gap:8px;z-index:5;}
  .dots button{width:8px;height:8px;border-radius:50%;border:none;background:rgba(255,255,255,.45);cursor:pointer;padding:0;transition:.3s;}
  .dots button.active{background:#fff;width:24px;border-radius:5px;}
  .c-tag{position:absolute;top:16px;left:16px;z-index:5;background:rgba(255,255,255,.2);backdrop-filter:blur(6px);color:#fff;font-size:12px;font-weight:600;padding:6px 13px;border-radius:20px;letter-spacing:.03em;}
  .reveal{opacity:0;transform:translateY(24px);transition:.7s cubic-bezier(.2,.7,.2,1);}
  .reveal.in{opacity:1;transform:none;}
</style>
</head>
@php
  $waBase = 'https://wa.me/'.$kos['phone'];
  $waTanya = $waBase.'?text='.rawurlencode('Halo '.$kos['name'].', saya mau tanya ketersediaan kamar kosong & jadwal survey. Terima kasih.');
  $rupiahJt = fn ($v) => 'Rp '.number_format(((float) $v) / 1000000, 1, ',', '.').'jt';
@endphp
<body>

<div class="topbar">
  <div class="wrap">
    <a href="#" class="nav-brand"><img src="{{ asset('logo.png') }}" alt="Logo">{{ $kos['name'] }}</a>
    <div class="nav-links">
      <a href="#lokasi">Lokasi</a>
      <a href="#harga">Harga</a>
      <a href="#fasilitas">Fasilitas</a>
      <a href="#kontak">Kontak</a>
    </div>
    <a href="{{ $waTanya }}" class="btn">Chat WhatsApp</a>
  </div>
</div>

<div class="hero">
  <div class="wrap">
    <div class="hero-text">
      @if($totalAvailable > 0)
        <span class="eyebrow">✨ {{ $totalAvailable }} kamar kosong tersedia</span>
      @else
        <span class="eyebrow">📲 Hubungi kami untuk ketersediaan</span>
      @endif
      <h1>{{ $kos['name'] }}<br><em>Modern Student Living</em></h1>
      <p class="lead">{{ $kos['tagline_long'] }}</p>
      <div class="hero-cta">
        <a href="{{ $waTanya }}" class="btn">📲 Tanya Kamar Kosong</a>
        <a href="#lokasi" class="btn ghost">📍 Lihat Lokasi</a>
      </div>
    </div>
    <div class="hero-visual">
      <div class="carousel" id="carousel">
        <span class="c-tag">✨ Highlight</span>
        <div class="slides">
          <div class="slide s1 active"><div class="s-ic">🎓</div><h3>Dekat Kampus</h3><p>Hanya {{ $kos['near'] }} — praktis untuk kuliah sehari-hari.</p></div>
          <div class="slide s2"><div class="s-ic">🛏️</div><h3>Kamar Nyaman</h3><p>Kamar mandi dalam, AC, kasur, lemari, dan meja belajar — siap huni.</p></div>
          <div class="slide s3"><div class="s-ic">🔒</div><h3>Aman 24 Jam</h3><p>CCTV 24 jam dan penjaga kost — nyaman untuk orang tua titipkan putrinya.</p></div>
          <div class="slide s4"><div class="s-ic">💰</div><h3>Harga Bersahabat</h3><p>Fasilitas lengkap dengan harga yang ramah kantong.</p></div>
        </div>
        <div class="dots" id="dots"></div>
      </div>
    </div>
  </div>
</div>

<section id="lokasi">
  <div class="wrap reveal">
    <div class="loc">
      <div>
        <h2>📍 Lokasi Strategis</h2>
        <address>
          {{ $kos['address_line1'] }}<br>
          {{ $kos['address_line2'] }}<br>
          {{ $kos['address_line3'] }}
        </address>
        <div class="dist">🎓 {{ $kos['near'] }}</div>
      </div>
      <div class="map-box">
        <div class="pin">🗺️</div>
        <a href="{{ $kos['maps_url'] }}" target="_blank" rel="noreferrer" class="btn light">Buka di Google Maps</a>
      </div>
    </div>
  </div>
</section>

<section id="harga">
  <div class="wrap reveal">
    <div class="sec-tag">Pilihan Kamar</div>
    <h2 class="sec-title">Harga Sewa per Bulan</h2>
    <p class="sec-sub">Setiap tipe kamar sudah termasuk kamar mandi dalam, AC, dan perabot. Ketersediaan kamar di bawah ini update otomatis dari sistem kami.</p>
    <div class="price-grid">
      @forelse($types as $t)
        @php
          $best = $cheapest !== null && (float) $t['price'] === (float) $cheapest;
          $waType = $waBase.'?text='.rawurlencode('Halo '.$kos['name'].', saya mau tanya ketersediaan kamar '.$t['label'].'. Terima kasih.');
        @endphp
        <a href="{{ $waType }}" class="pcard {{ $best ? 'best' : '' }}">
          @if($best)<div class="badge">Termurah</div>@endif
          <div class="type">{{ strtoupper($t['label']) }}</div>
          <div class="amt">{{ $rupiahJt($t['price']) }}</div>
          <div class="per">/bulan</div>
          @if($t['available'] > 0)
            <div class="avail ok">✓ {{ $t['available'] }} kamar tersedia</div>
          @else
            <div class="avail full">Penuh — tanya waiting list</div>
          @endif
        </a>
      @empty
        <div class="pcard"><div class="type">INFO</div><div class="amt" style="font-size:18px;">Hubungi kami</div><div class="per">untuk daftar kamar & harga</div></div>
      @endforelse
    </div>
  </div>
</section>

<section id="fasilitas">
  <div class="wrap reveal">
    <div class="sec-tag">Fasilitas</div>
    <h2 class="sec-title">Semua yang Kamu Butuhkan</h2>
    <p class="sec-sub">Dari kamar pribadi yang nyaman sampai fasilitas bersama yang lengkap — semua dirancang untuk mendukung keseharian mahasiswi.</p>
    <div class="fac-grid">
      <div class="fac-card">
        <h3>🛏️ Fasilitas Kamar</h3>
        <ul>
          <li><span class="tick">✓</span> Kamar Mandi Dalam</li>
          <li><span class="tick">✓</span> AC</li>
          <li><span class="tick">✓</span> Kasur + Bantal</li>
          <li><span class="tick">✓</span> Lemari Pakaian</li>
          <li><span class="tick">✓</span> Meja Belajar</li>
          <li><span class="tick">✓</span> Kursi</li>
        </ul>
      </div>
      <div class="fac-card">
        <h3>🏡 Fasilitas Bersama</h3>
        <ul>
          <li><span class="tick">✓</span> WiFi</li>
          <li><span class="tick">✓</span> Dapur Bersama</li>
          <li><span class="tick">✓</span> Lemari Es</li>
          <li><span class="tick">✓</span> Mesin Cuci &amp; Area Jemur</li>
          <li><span class="tick">✓</span> Rooftop</li>
          <li><span class="tick">✓</span> Parkir Motor &amp; Mobil</li>
          <li><span class="tick">✓</span> CCTV 24 Jam</li>
          <li><span class="tick">✓</span> Penjaga Kost</li>
        </ul>
      </div>
    </div>
    <div class="terms" style="margin-top:28px;">
      <span class="ic">📝</span>
      <span>Masa sewa minimal <b>3 bulan</b> dengan pembayaran di muka. Hubungi kami untuk info ketersediaan kamar dan jadwal survey lokasi.</span>
    </div>
  </div>
</section>

<section id="kontak" class="cta">
  <div class="wrap reveal">
    <h2>Tertarik? Yuk, Survey Dulu!</h2>
    <p>Info kamar kosong &amp; jadwal survey lokasi — langsung chat admin kami.</p>
    <a href="{{ $waTanya }}" class="btn">📲 WhatsApp: {{ $kos['phone_display'] }}</a>
    <div class="cta-socials">
      <a href="{{ $kos['instagram'] }}" target="_blank" rel="noreferrer"><svg viewBox="0 0 24 24"><path d="M12 2.2c3.2 0 3.6 0 4.85.07 1.17.05 1.8.25 2.23.41.56.22.96.48 1.38.9.42.42.68.82.9 1.38.16.42.36 1.06.41 2.23.06 1.27.07 1.65.07 4.85s0 3.58-.07 4.85c-.05 1.17-.25 1.8-.41 2.23-.22.56-.48.96-.9 1.38-.42.42-.82.68-1.38.9-.42.16-1.06.36-2.23.41-1.27.06-1.65.07-4.85.07s-3.58 0-4.85-.07c-1.17-.05-1.8-.25-2.23-.41a3.7 3.7 0 0 1-1.38-.9 3.7 3.7 0 0 1-.9-1.38c-.16-.42-.36-1.06-.41-2.23C2.2 15.58 2.2 15.2 2.2 12s0-3.58.07-4.85c.05-1.17.25-1.8.41-2.23.22-.56.48-.96.9-1.38.42-.42.82-.68 1.38-.9.42-.16 1.06-.36 2.23-.41C8.42 2.2 8.8 2.2 12 2.2z"/></svg>Instagram</a>
      <a href="{{ $kos['tiktok'] }}" target="_blank" rel="noreferrer"><svg viewBox="0 0 24 24"><path d="M16.6 5.82a4.28 4.28 0 0 1-1.06-2.82h-3.3v12.86a2.55 2.55 0 0 1-2.55 2.45 2.55 2.55 0 1 1 .7-5v-3.36a5.9 5.9 0 0 0-.7-.04 5.9 5.9 0 1 0 5.9 5.9V9.01a7.56 7.56 0 0 0 4.41 1.41V7.1a4.28 4.28 0 0 1-3.4-1.28z"/></svg>TikTok</a>
    </div>
  </div>
</section>

<footer>
  <div class="wrap">
    <img src="{{ asset('logo.png') }}" alt="Logo">
    <div class="fbrand">{{ $kos['name'] }} Kos Putri</div>
    <div class="ftag">Modern Student Living · Semarang</div>
    <div class="fcontact">
      {{ $kos['address_line1'] }}, {{ $kos['address_line2'] }}, {{ $kos['address_line3'] }}<br>
      WA: {{ $kos['phone_display'] }}
    </div>
    <div class="socials">
      <a href="{{ $kos['instagram'] }}" target="_blank" rel="noreferrer">{{ $kos['instagram_handle'] }}</a>
      <a href="{{ $kos['tiktok'] }}" target="_blank" rel="noreferrer">TikTok</a>
    </div>
    <div class="copy">© {{ date('Y') }} {{ $kos['name'] }} Kos Putri. Semua hak dilindungi.</div>
  </div>
</footer>

<script>
  const io=new IntersectionObserver((es)=>{es.forEach(e=>{if(e.isIntersecting)e.target.classList.add('in');});},{threshold:.15});
  document.querySelectorAll('.reveal').forEach(el=>io.observe(el));
  (function(){
    const slides=[...document.querySelectorAll('#carousel .slide')];
    const dotsBox=document.getElementById('dots');
    let i=0,timer;
    slides.forEach((_,n)=>{
      const d=document.createElement('button');
      if(n===0)d.classList.add('active');
      d.addEventListener('click',()=>{go(n);reset();});
      dotsBox.appendChild(d);
    });
    const dots=[...dotsBox.children];
    function go(n){
      slides[i].classList.remove('active');dots[i].classList.remove('active');
      i=(n+slides.length)%slides.length;
      slides[i].classList.add('active');dots[i].classList.add('active');
    }
    function reset(){clearInterval(timer);timer=setInterval(()=>go(i+1),4000);}
    reset();
  })();
</script>
</body>
</html>
