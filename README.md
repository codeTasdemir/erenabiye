<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>README Preview</title>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
<style>
  :root {
    --bg: #0a0a0f;
    --surface: #111118;
    --surface2: #16161f;
    --border: #1e1e2e;
    --accent: #6c63ff;
    --accent2: #ff6584;
    --accent3: #43e97b;
    --text: #e2e2f0;
    --muted: #6b6b8a;
    --card: #13131c;
  }

  * { margin: 0; padding: 0; box-sizing: border-box; }

  body {
    background: var(--bg);
    color: var(--text);
    font-family: 'Sora', sans-serif;
    line-height: 1.7;
    min-height: 100vh;
  }

  .lang-switcher {
    position: fixed;
    top: 24px;
    right: 24px;
    z-index: 100;
    display: flex;
    gap: 8px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 50px;
    padding: 6px;
    backdrop-filter: blur(10px);
  }

  .lang-btn {
    padding: 6px 18px;
    border-radius: 50px;
    border: none;
    cursor: pointer;
    font-family: 'Sora', sans-serif;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.3s ease;
    background: transparent;
    color: var(--muted);
  }

  .lang-btn.active {
    background: var(--accent);
    color: #fff;
    box-shadow: 0 0 20px rgba(108,99,255,0.4);
  }

  .readme {
    max-width: 860px;
    margin: 0 auto;
    padding: 60px 24px 80px;
    display: none;
  }

  .readme.active { display: block; animation: fadeIn 0.4s ease; }

  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(12px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  /* ── HERO ── */
  .hero {
    text-align: center;
    padding: 60px 0 56px;
    position: relative;
  }

  .hero::before {
    content: '';
    position: absolute;
    top: 0; left: 50%;
    transform: translateX(-50%);
    width: 600px; height: 300px;
    background: radial-gradient(ellipse at center, rgba(108,99,255,0.12) 0%, transparent 70%);
    pointer-events: none;
  }

  .badge-row {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    justify-content: center;
    margin-bottom: 32px;
  }

  .badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 14px;
    border-radius: 50px;
    font-size: 12px;
    font-weight: 600;
    font-family: 'JetBrains Mono', monospace;
    border: 1px solid;
  }

  .badge-php    { background: rgba(119,82,254,0.12); border-color: rgba(119,82,254,0.3); color: #a78bfa; }
  .badge-laravel{ background: rgba(255,45,32,0.10);  border-color: rgba(255,45,32,0.3);  color: #f87171; }
  .badge-filament{background: rgba(251,146,60,0.10); border-color: rgba(251,146,60,0.3); color: #fb923c; }
  .badge-lw     { background: rgba(56,189,248,0.10); border-color: rgba(56,189,248,0.3); color: #38bdf8; }
  .badge-tw     { background: rgba(20,184,166,0.10); border-color: rgba(20,184,166,0.3); color: #2dd4bf; }
  .badge-pg     { background: rgba(67,233,123,0.10); border-color: rgba(67,233,123,0.3); color: #43e97b; }
  .badge-paytr  { background: rgba(255,101,132,0.10);border-color: rgba(255,101,132,0.3);color: #fb7185; }

  .hero-title {
    font-size: clamp(32px, 6vw, 52px);
    font-weight: 800;
    letter-spacing: -1.5px;
    line-height: 1.15;
    margin-bottom: 16px;
    background: linear-gradient(135deg, #fff 30%, var(--accent) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  .hero-sub {
    font-size: 16px;
    color: var(--muted);
    max-width: 540px;
    margin: 0 auto 32px;
    font-weight: 300;
  }

  .hero-tags {
    display: flex;
    gap: 10px;
    justify-content: center;
    flex-wrap: wrap;
  }

  .tag {
    padding: 4px 14px;
    background: var(--surface2);
    border: 1px solid var(--border);
    border-radius: 50px;
    font-size: 12px;
    color: var(--muted);
    font-weight: 600;
    letter-spacing: 0.5px;
    text-transform: uppercase;
  }

  /* ── DIVIDER ── */
  .divider {
    border: none;
    border-top: 1px solid var(--border);
    margin: 40px 0;
  }

  /* ── SECTION ── */
  .section { margin-bottom: 48px; }

  .section-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
  }

  .section-icon {
    width: 36px; height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 17px;
    flex-shrink: 0;
  }

  .icon-purple { background: rgba(108,99,255,0.15); }
  .icon-green  { background: rgba(67,233,123,0.15); }
  .icon-red    { background: rgba(255,101,132,0.15); }
  .icon-blue   { background: rgba(56,189,248,0.15); }
  .icon-orange { background: rgba(251,146,60,0.15); }

  .section-title {
    font-size: 20px;
    font-weight: 700;
    color: #fff;
    letter-spacing: -0.3px;
  }

  /* ── ABOUT CARD ── */
  .about-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 28px 32px;
    position: relative;
    overflow: hidden;
  }

  .about-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 2px;
    background: linear-gradient(90deg, var(--accent), var(--accent2), var(--accent3));
  }

  .about-card p {
    color: #c4c4d8;
    font-size: 15px;
    line-height: 1.8;
  }

  /* ── TECH GRID ── */
  .tech-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 12px;
  }

  .tech-item {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 18px 20px;
    display: flex;
    align-items: center;
    gap: 14px;
    transition: all 0.25s ease;
    cursor: default;
  }

  .tech-item:hover {
    border-color: var(--accent);
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(108,99,255,0.15);
  }

  .tech-emoji {
    font-size: 24px;
    width: 36px;
    text-align: center;
    flex-shrink: 0;
  }

  .tech-info { min-width: 0; }

  .tech-name {
    font-size: 14px;
    font-weight: 700;
    color: #fff;
    display: block;
  }

  .tech-version {
    font-size: 12px;
    color: var(--muted);
    font-family: 'JetBrains Mono', monospace;
  }

  /* ── FEATURES ── */
  .features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 12px;
  }

  .feature-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 20px;
    transition: border-color 0.25s;
  }

  .feature-card:hover { border-color: rgba(108,99,255,0.4); }

  .feature-icon {
    font-size: 22px;
    margin-bottom: 10px;
    display: block;
  }

  .feature-title {
    font-size: 14px;
    font-weight: 700;
    color: #fff;
    margin-bottom: 6px;
  }

  .feature-desc {
    font-size: 13px;
    color: var(--muted);
    line-height: 1.6;
  }

  /* ── SETUP ── */
  .steps { display: flex; flex-direction: column; gap: 12px; }

  .step {
    display: flex;
    gap: 16px;
    align-items: flex-start;
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 18px 20px;
  }

  .step-num {
    width: 28px; height: 28px;
    border-radius: 8px;
    background: var(--accent);
    color: #fff;
    font-size: 13px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-top: 1px;
  }

  .step-content { flex: 1; }

  .step-label {
    font-size: 14px;
    font-weight: 700;
    color: #fff;
    margin-bottom: 6px;
  }

  code {
    display: block;
    background: #0d0d14;
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 10px 14px;
    font-family: 'JetBrains Mono', monospace;
    font-size: 12.5px;
    color: #a5f3fc;
    white-space: pre;
    overflow-x: auto;
  }

  .inline-code {
    display: inline;
    padding: 2px 8px;
    border-radius: 5px;
    font-size: 12px;
    color: #fb923c;
  }

  /* ── PAYMENT ── */
  .payment-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 28px 32px;
    display: flex;
    gap: 24px;
    align-items: flex-start;
  }

  .payment-logo {
    font-size: 40px;
    flex-shrink: 0;
  }

  .payment-info h3 {
    font-size: 17px;
    font-weight: 700;
    color: #fff;
    margin-bottom: 8px;
  }

  .payment-info p {
    font-size: 14px;
    color: var(--muted);
    line-height: 1.7;
  }

  /* ── FOOTER ── */
  .footer {
    margin-top: 64px;
    padding-top: 32px;
    border-top: 1px solid var(--border);
    text-align: center;
  }

  .footer-text {
    font-size: 13px;
    color: var(--muted);
  }

  .footer-text span {
    color: var(--accent2);
    font-weight: 600;
  }

  .copy-note {
    margin-top: 40px;
    background: var(--surface2);
    border: 1px dashed var(--border);
    border-radius: 12px;
    padding: 16px 20px;
    font-size: 13px;
    color: var(--muted);
    text-align: center;
  }

  .copy-note strong { color: #fff; }
</style>
</head>
<body>

<!-- Lang Switcher -->
<div class="lang-switcher">
  <button class="lang-btn active" onclick="switchLang('tr')">🇹🇷 TR</button>
  <button class="lang-btn" onclick="switchLang('en')">🇬🇧 EN</button>
</div>

<!-- ═══════════════════════════ TURKISH ═══════════════════════════ -->
<div class="readme active" id="readme-tr">

  <div class="hero">
    <div class="badge-row">
      <span class="badge badge-php">PHP 8.3</span>
      <span class="badge badge-laravel">Laravel 12</span>
      <span class="badge badge-filament">Filament 5</span>
      <span class="badge badge-lw">Livewire 4</span>
      <span class="badge badge-tw">Tailwind CSS</span>
      <span class="badge badge-pg">PostgreSQL</span>
      <span class="badge badge-paytr">PayTR</span>
    </div>
    <h1 class="hero-title">👔 Eren Abiye E-Ticaret<br>Platformu</h1>
    <p class="hero-sub">Giyim sektörüne özel geliştirilmiş, modern ve ölçeklenebilir online satış çözümü.</p>
    <div class="hero-tags">
      <span class="tag">E-Ticaret</span>
      <span class="tag">Giyim</span>
      <span class="tag">Online Ödeme</span>
      <span class="tag">Admin Panel</span>
    </div>
  </div>

  <hr class="divider">

  <!-- Hakkında -->
  <div class="section">
    <div class="section-header">
      <div class="section-icon icon-purple">📌</div>
      <h2 class="section-title">Proje Hakkında</h2>
    </div>
    <div class="about-card">
      <p>Bu proje, <strong style="color:#fff">Eren Abiye firmasının</strong> e-ticaret altyapısını hayata geçirmek amacıyla geliştirilmiştir. Giyim sektöründe online satış yapmak isteyen firmaların kolayca adapte edebileceği şekilde tasarlanmış, modern teknoloji yığını üzerine inşa edilmiş kapsamlı bir e-ticaret çözümüdür. Müşteri arayüzü, yönetim paneli ve güvenli ödeme entegrasyonunu tek çatı altında sunar.</p>
    </div>
  </div>

  <!-- Teknolojiler -->
  <div class="section">
    <div class="section-header">
      <div class="section-icon icon-blue">⚙️</div>
      <h2 class="section-title">Kullanılan Teknolojiler</h2>
    </div>
    <div class="tech-grid">
      <div class="tech-item">
        <span class="tech-emoji">🐘</span>
        <div class="tech-info">
          <span class="tech-name">PHP</span>
          <span class="tech-version">v8.3</span>
        </div>
      </div>
      <div class="tech-item">
        <span class="tech-emoji">🔴</span>
        <div class="tech-info">
          <span class="tech-name">Laravel</span>
          <span class="tech-version">v12</span>
        </div>
      </div>
      <div class="tech-item">
        <span class="tech-emoji">🔶</span>
        <div class="tech-info">
          <span class="tech-name">Filament</span>
          <span class="tech-version">v5 — Admin Panel</span>
        </div>
      </div>
      <div class="tech-item">
        <span class="tech-emoji">⚡</span>
        <div class="tech-info">
          <span class="tech-name">Livewire</span>
          <span class="tech-version">v4 — Reaktif UI</span>
        </div>
      </div>
      <div class="tech-item">
        <span class="tech-emoji">🎨</span>
        <div class="tech-info">
          <span class="tech-name">Tailwind CSS</span>
          <span class="tech-version">Utility-first CSS</span>
        </div>
      </div>
      <div class="tech-item">
        <span class="tech-emoji">🐘</span>
        <div class="tech-info">
          <span class="tech-name">PostgreSQL</span>
          <span class="tech-version">Veritabanı</span>
        </div>
      </div>
      <div class="tech-item">
        <span class="tech-emoji">💳</span>
        <div class="tech-info">
          <span class="tech-name">PayTR</span>
          <span class="tech-version">iFrame API</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Özellikler -->
  <div class="section">
    <div class="section-header">
      <div class="section-icon icon-green">✨</div>
      <h2 class="section-title">Öne Çıkan Özellikler</h2>
    </div>
    <div class="features-grid">
      <div class="feature-card">
        <span class="feature-icon">🛍️</span>
        <div class="feature-title">Ürün Yönetimi</div>
        <div class="feature-desc">Kategori, beden, renk ve stok yönetimi ile kapsamlı ürün kataloğu.</div>
      </div>
      <div class="feature-card">
        <span class="feature-icon">🛒</span>
        <div class="feature-title">Sepet & Sipariş</div>
        <div class="feature-desc">Gerçek zamanlı sepet yönetimi, sipariş takibi ve durum bildirimleri.</div>
      </div>
      <div class="feature-card">
        <span class="feature-icon">💳</span>
        <div class="feature-title">Güvenli Ödeme</div>
        <div class="feature-desc">PayTR iFrame API ile PCI-DSS uyumlu, güvenli online ödeme akışı.</div>
      </div>
      <div class="feature-card">
        <span class="feature-icon">🖥️</span>
        <div class="feature-title">Admin Panel</div>
        <div class="feature-desc">Filament 5 tabanlı modern, kullanıcı dostu yönetim arayüzü.</div>
      </div>
      <div class="feature-card">
        <span class="feature-icon">⚡</span>
        <div class="feature-title">Reaktif Arayüz</div>
        <div class="feature-desc">Livewire 4 ile sayfa yenilemeden çalışan dinamik kullanıcı deneyimi.</div>
      </div>
      <div class="feature-card">
        <span class="feature-icon">📱</span>
        <div class="feature-title">Responsive Tasarım</div>
        <div class="feature-desc">Tailwind CSS ile tüm cihazlarda kusursuz görüntüleme.</div>
      </div>
    </div>
  </div>

  <!-- Kurulum -->
  <div class="section">
    <div class="section-header">
      <div class="section-icon icon-orange">🚀</div>
      <h2 class="section-title">Kurulum</h2>
    </div>
    <div class="steps">
      <div class="step">
        <div class="step-num">1</div>
        <div class="step-content">
          <div class="step-label">Repoyu klonlayın</div>
          <code>git clone https://github.com/KULLANICI/REPO.git
cd REPO</code>
        </div>
      </div>
      <div class="step">
        <div class="step-num">2</div>
        <div class="step-content">
          <div class="step-label">Bağımlılıkları yükleyin</div>
          <code>composer install
npm install && npm run build</code>
        </div>
      </div>
      <div class="step">
        <div class="step-num">3</div>
        <div class="step-content">
          <div class="step-label">Ortam değişkenlerini ayarlayın</div>
          <code>cp .env.example .env
php artisan key:generate</code>
        </div>
      </div>
      <div class="step">
        <div class="step-num">4</div>
        <div class="step-content">
          <div class="step-label">Veritabanını yapılandırın (PostgreSQL)</div>
          <code>php artisan migrate --seed</code>
        </div>
      </div>
      <div class="step">
        <div class="step-num">5</div>
        <div class="step-content">
          <div class="step-label">Uygulamayı başlatın</div>
          <code>php artisan serve</code>
        </div>
      </div>
    </div>
  </div>

  <!-- Ödeme -->
  <div class="section">
    <div class="section-header">
      <div class="section-icon icon-red">💳</div>
      <h2 class="section-title">Ödeme Altyapısı</h2>
    </div>
    <div class="payment-card">
      <div class="payment-logo">🏦</div>
      <div class="payment-info">
        <h3>PayTR iFrame API</h3>
        <p>Ödeme altyapısı olarak <strong style="color:#fff">PayTR iFrame API</strong> kullanılmaktadır. Müşteri kart bilgilerini doğrudan PayTR altyapısına girerek ödeme yapar; bu sayede kart bilgileri hiçbir zaman uygulama sunucusuna ulaşmaz ve yüksek güvenlik standardı sağlanır.</p>
      </div>
    </div>
  </div>

  <div class="footer">
    <p class="footer-text">Bu proje <span>Eren Abiye Firması</span> için geliştirilmiştir. Giyim sektöründeki tüm e-ticaret ihtiyaçları gözetilerek tasarlanmıştır.</p>
  </div>

</div>

<!-- ═══════════════════════════ ENGLISH ═══════════════════════════ -->
<div class="readme" id="readme-en">

  <div class="hero">
    <div class="badge-row">
      <span class="badge badge-php">PHP 8.3</span>
      <span class="badge badge-laravel">Laravel 12</span>
      <span class="badge badge-filament">Filament 5</span>
      <span class="badge badge-lw">Livewire 4</span>
      <span class="badge badge-tw">Tailwind CSS</span>
      <span class="badge badge-pg">PostgreSQL</span>
      <span class="badge badge-paytr">PayTR</span>
    </div>
    <h1 class="hero-title">👔 Eren Abiye E-Commerce<br>Platform</h1>
    <p class="hero-sub">A modern, scalable online sales solution built specifically for the fashion & clothing industry.</p>
    <div class="hero-tags">
      <span class="tag">E-Commerce</span>
      <span class="tag">Fashion</span>
      <span class="tag">Online Payment</span>
      <span class="tag">Admin Panel</span>
    </div>
  </div>

  <hr class="divider">

  <!-- About -->
  <div class="section">
    <div class="section-header">
      <div class="section-icon icon-purple">📌</div>
      <h2 class="section-title">About the Project</h2>
    </div>
    <div class="about-card">
      <p>This project was developed to bring the e-commerce infrastructure of <strong style="color:#fff">Eren Abiye Company</strong> to life. It is a comprehensive e-commerce solution built on a modern technology stack, designed to be easily adaptable for businesses looking to sell clothing online. It provides a customer-facing interface, an administration panel, and secure payment integration all under one roof.</p>
    </div>
  </div>

  <!-- Tech Stack -->
  <div class="section">
    <div class="section-header">
      <div class="section-icon icon-blue">⚙️</div>
      <h2 class="section-title">Tech Stack</h2>
    </div>
    <div class="tech-grid">
      <div class="tech-item">
        <span class="tech-emoji">🐘</span>
        <div class="tech-info">
          <span class="tech-name">PHP</span>
          <span class="tech-version">v8.3</span>
        </div>
      </div>
      <div class="tech-item">
        <span class="tech-emoji">🔴</span>
        <div class="tech-info">
          <span class="tech-name">Laravel</span>
          <span class="tech-version">v12</span>
        </div>
      </div>
      <div class="tech-item">
        <span class="tech-emoji">🔶</span>
        <div class="tech-info">
          <span class="tech-name">Filament</span>
          <span class="tech-version">v5 — Admin Panel</span>
        </div>
      </div>
      <div class="tech-item">
        <span class="tech-emoji">⚡</span>
        <div class="tech-info">
          <span class="tech-name">Livewire</span>
          <span class="tech-version">v4 — Reactive UI</span>
        </div>
      </div>
      <div class="tech-item">
        <span class="tech-emoji">🎨</span>
        <div class="tech-info">
          <span class="tech-name">Tailwind CSS</span>
          <span class="tech-version">Utility-first CSS</span>
        </div>
      </div>
      <div class="tech-item">
        <span class="tech-emoji">🐘</span>
        <div class="tech-info">
          <span class="tech-name">PostgreSQL</span>
          <span class="tech-version">Database</span>
        </div>
      </div>
      <div class="tech-item">
        <span class="tech-emoji">💳</span>
        <div class="tech-info">
          <span class="tech-name">PayTR</span>
          <span class="tech-version">iFrame API</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Features -->
  <div class="section">
    <div class="section-header">
      <div class="section-icon icon-green">✨</div>
      <h2 class="section-title">Key Features</h2>
    </div>
    <div class="features-grid">
      <div class="feature-card">
        <span class="feature-icon">🛍️</span>
        <div class="feature-title">Product Management</div>
        <div class="feature-desc">Full product catalog with category, size, color, and stock management.</div>
      </div>
      <div class="feature-card">
        <span class="feature-icon">🛒</span>
        <div class="feature-title">Cart & Orders</div>
        <div class="feature-desc">Real-time cart management, order tracking, and status notifications.</div>
      </div>
      <div class="feature-card">
        <span class="feature-icon">💳</span>
        <div class="feature-title">Secure Payments</div>
        <div class="feature-desc">PCI-DSS compliant, secure online payment flow via PayTR iFrame API.</div>
      </div>
      <div class="feature-card">
        <span class="feature-icon">🖥️</span>
        <div class="feature-title">Admin Panel</div>
        <div class="feature-desc">Modern, user-friendly management interface powered by Filament 5.</div>
      </div>
      <div class="feature-card">
        <span class="feature-icon">⚡</span>
        <div class="feature-title">Reactive UI</div>
        <div class="feature-desc">Dynamic, page-reload-free user experience powered by Livewire 4.</div>
      </div>
      <div class="feature-card">
        <span class="feature-icon">📱</span>
        <div class="feature-title">Responsive Design</div>
        <div class="feature-desc">Flawless display across all devices with Tailwind CSS.</div>
      </div>
    </div>
  </div>

  <!-- Setup -->
  <div class="section">
    <div class="section-header">
      <div class="section-icon icon-orange">🚀</div>
      <h2 class="section-title">Getting Started</h2>
    </div>
    <div class="steps">
      <div class="step">
        <div class="step-num">1</div>
        <div class="step-content">
          <div class="step-label">Clone the repository</div>
          <code>git clone https://github.com/USERNAME/REPO.git
cd REPO</code>
        </div>
      </div>
      <div class="step">
        <div class="step-num">2</div>
        <div class="step-content">
          <div class="step-label">Install dependencies</div>
          <code>composer install
npm install && npm run build</code>
        </div>
      </div>
      <div class="step">
        <div class="step-num">3</div>
        <div class="step-content">
          <div class="step-label">Configure environment variables</div>
          <code>cp .env.example .env
php artisan key:generate</code>
        </div>
      </div>
      <div class="step">
        <div class="step-num">4</div>
        <div class="step-content">
          <div class="step-label">Set up the database (PostgreSQL)</div>
          <code>php artisan migrate --seed</code>
        </div>
      </div>
      <div class="step">
        <div class="step-num">5</div>
        <div class="step-content">
          <div class="step-label">Start the application</div>
          <code>php artisan serve</code>
        </div>
      </div>
    </div>
  </div>

  <!-- Payment -->
  <div class="section">
    <div class="section-header">
      <div class="section-icon icon-red">💳</div>
      <h2 class="section-title">Payment Infrastructure</h2>
    </div>
    <div class="payment-card">
      <div class="payment-logo">🏦</div>
      <div class="payment-info">
        <h3>PayTR iFrame API</h3>
        <p>The payment infrastructure is built on <strong style="color:#fff">PayTR iFrame API</strong>. Customers enter their card details directly into PayTR's secure environment, meaning sensitive payment data never touches the application server — ensuring a high standard of security and compliance.</p>
      </div>
    </div>
  </div>

  <div class="footer">
    <p class="footer-text">This project was developed for <span>Eren Abiye Company</span>. Designed to meet all e-commerce needs in the clothing industry.</p>
  </div>

</div>

<!-- Copy note -->
<div style="max-width:860px;margin:0 auto;padding:0 24px 60px;">
  <div class="copy-note">
    💡 Bu dosya sadece <strong>önizleme</strong> amaçlıdır. GitHub README için içeriği <strong>Markdown formatında</strong> kullanmanız gerekiyorsa söyleyin, dönüştüreyim.
  </div>
</div>

<script>
  function switchLang(lang) {
    document.querySelectorAll('.readme').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.lang-btn').forEach(el => el.classList.remove('active'));
    document.getElementById('readme-' + lang).classList.add('active');
    event.currentTarget.classList.add('active');
  }
</script>
</body>
</html>
