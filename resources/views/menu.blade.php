<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta name="theme-color" content="#ff6600" />
    <title>{{ $company->name ?? config('app.name') }} — Menu</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />

    <style>
        /* ─── Reset & Base ─────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body {
            font-family: 'Poppins', sans-serif;
            background: #f7f3ef;
            color: #222;
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        /* ─── CSS Variables ────────────────────────────────── */
        :root {
            --brand:        #ff6600;
            --brand-dark:   #e05500;
            --brand-light:  #fff3ec;
            --surface:      #ffffff;
            --radius:       14px;
            --shadow:       0 2px 16px rgba(0,0,0,.08);
            --shadow-card:  0 4px 20px rgba(0,0,0,.10);
            --text-muted:   #888;
        }

        /* ─── Header ───────────────────────────────────────── */
        .site-header {
            position: relative;
            background: linear-gradient(135deg, var(--brand) 0%, #ff8c42 50%, #ffaa5c 100%);
            color: #fff;
            padding: 48px 20px 60px;
            text-align: center;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(255, 102, 0, 0.2);
        }
        .site-header::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: radial-gradient(circle at top right, rgba(255,255,255,.1) 0%, transparent 70%);
            pointer-events: none;
        }
        .site-header::after {
            content: '';
            position: absolute;
            bottom: -40px; left: 0; right: 0;
            height: 80px;
            background: #f7f3ef;
            border-radius: 50% 50% 0 0 / 80px 80px 0 0;
            box-shadow: inset 0 2px 8px rgba(0,0,0,.05);
        }
        .header-content {
            position: relative;
            z-index: 2;
        }
        .header-logo {
            width: 100px; height: 100px;
            border-radius: 16px;
            object-fit: cover;
            border: 4px solid rgba(255,255,255,.9);
            margin: 0 auto 20px;
            box-shadow: 0 12px 32px rgba(0,0,0,.25), 0 0 0 1px rgba(255,255,255,.3);
            transition: transform .3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .header-logo:hover {
            transform: translateY(-4px) scale(1.02);
        }
        .header-logo-placeholder {
            width: 100px; height: 100px;
            border-radius: 16px;
            background: linear-gradient(135deg, rgba(255,255,255,.25) 0%, rgba(255,255,255,.1) 100%);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px;
            font-size: 44px;
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(0,0,0,.2), 0 0 0 1px rgba(255,255,255,.3);
        }
        .site-header h1 {
            font-size: 2rem; font-weight: 700;
            letter-spacing: -.5px;
            text-shadow: 0 2px 8px rgba(0,0,0,.2);
            margin: 8px 0;
            line-height: 1.2;
        }
        .site-header p {
            font-size: .95rem; opacity: .9; margin-top: 8px;
            font-weight: 500;
            letter-spacing: 0.2px;
        }

        /* ─── Sticky Category Nav ──────────────────────────── */
        .category-nav-wrapper {
            position: sticky; top: 0; z-index: 100;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.98) 0%, rgba(247, 243, 239, 0.98) 100%);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 20px rgba(0,0,0,.12);
            padding: 0;
        }
        .category-nav-container {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 8px;
            position: relative;
        }
        .slider-arrow {
            flex-shrink: 0;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--brand) 0%, #ff8c42 100%);
            border: none;
            border-radius: 50%;
            color: white;
            font-size: 18px;
            cursor: pointer;
            transition: all .3s ease;
            box-shadow: 0 2px 8px rgba(255, 102, 0, 0.3);
        }
        .slider-arrow:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 16px rgba(255, 102, 0, 0.4);
        }
        .slider-arrow:active {
            transform: scale(0.95);
        }
        .slider-arrow.disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }
        .category-nav {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            overflow-y: hidden;
            padding: 14px 8px;
            scroll-snap-type: x mandatory;
            -webkit-overflow-scrolling: touch;
            flex: 1;
            scroll-behavior: smooth;
        }
        .category-nav::-webkit-scrollbar { display: none; }
        .cat-pill {
            flex-shrink: 0;
            scroll-snap-align: start;
            padding: 10px 22px;
            border-radius: 25px;
            font-size: .85rem;
            font-weight: 600;
            border: 2px solid transparent;
            background: linear-gradient(135deg, #f5f5f5 0%, #e8e8e8 100%);
            color: #444;
            cursor: pointer;
            transition: all .3s cubic-bezier(0.34, 1.56, 0.64, 1);
            white-space: nowrap;
            letter-spacing: 0.3px;
            box-shadow: 0 2px 8px rgba(0,0,0,.06);
            position: relative;
            overflow: hidden;
        }
        .cat-pill::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(135deg, var(--brand) 0%, #ff8c42 100%);
            transition: left .3s ease;
            z-index: -1;
            border-radius: 25px;
        }
        .cat-pill:hover {
            background: linear-gradient(135deg, var(--brand) 0%, #ff8c42 100%);
            color: #fff;
            border-color: var(--brand);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 102, 0, 0.3);
        }
        .cat-pill.active {
            background: linear-gradient(135deg, var(--brand) 0%, #ff8c42 100%);
            color: #fff;
            border-color: var(--brand);
            box-shadow: 0 8px 24px rgba(255, 102, 0, 0.4);
            transform: scale(1.05);
        }

        /* ─── Main content ──────────────────────────────────── */
        .main { padding: 24px 16px 100px; max-width: 600px; margin: 0 auto; }

        /* ─── Category Section ──────────────────────────────── */
        .category-section {
            margin-bottom: 36px;
            scroll-margin-top: 70px; /* offset for sticky nav */
        }
        .section-header {
            display: flex; align-items: center; gap: 12px;
            margin-bottom: 16px;
        }
        .section-thumb {
            width: 44px; height: 44px;
            border-radius: 12px;
            object-fit: cover;
            flex-shrink: 0;
        }
        .section-thumb-placeholder {
            width: 44px; height: 44px;
            border-radius: 12px;
            background: var(--brand-light);
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; flex-shrink: 0;
        }
        .section-title {
            font-size: 1.1rem; font-weight: 700; color: #1a1a1a;
        }
        .section-divider {
            flex: 1;
            height: 2px;
            background: linear-gradient(to right, var(--brand), transparent);
            border-radius: 2px;
        }

        /* ─── Product Grid ──────────────────────────────────── */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 14px;
        }

        /* ─── Product Card ──────────────────────────────────── */
        .product-card {
            background: var(--surface);
            border-radius: var(--radius);
            box-shadow: var(--shadow-card);
            overflow: hidden;
            transition: transform .18s ease, box-shadow .18s ease;
            position: relative;
        }
        .product-card:active { transform: scale(.97); }

        .product-img-wrap {
            position: relative;
            width: 100%; padding-top: 70%;
            overflow: hidden;
            background: #f0ebe5;
        }
        .product-img-wrap img {
            position: absolute; inset: 0;
            width: 100%; height: 100%;
            object-fit: cover;
            transition: transform .3s ease;
        }
        .product-card:hover .product-img-wrap img { transform: scale(1.04); }
        .product-img-placeholder {
            position: absolute; inset: 0;
            display: flex; align-items: center; justify-content: center;
            font-size: 48px; color: #ccc;
        }

        /* Discount badge */
        .badge-discount {
            position: absolute; top: 8px; left: 8px;
            background: var(--brand);
            color: #fff;
            font-size: .65rem; font-weight: 700;
            padding: 3px 7px; border-radius: 20px;
            letter-spacing: .3px;
        }

        /* Beverage badge */
        .badge-beverage {
            position: absolute; top: 8px; right: 8px;
            background: #1a9bd8;
            color: #fff;
            font-size: .6rem; font-weight: 600;
            padding: 3px 7px; border-radius: 20px;
        }

        /* Card body */
        .product-info {
            padding: 10px 12px 12px;
        }
        .product-name {
            font-size: .88rem; font-weight: 600;
            line-height: 1.3; color: #1a1a1a;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            margin-bottom: 6px;
        }
        .price-row {
            display: flex; align-items: center; gap: 6px; flex-wrap: wrap;
        }
        .price-final {
            font-size: .95rem; font-weight: 700; color: var(--brand);
        }
        .price-original {
            font-size: .75rem; color: var(--text-muted);
            text-decoration: line-through;
        }
        .product-desc {
            font-size: .73rem; color: var(--text-muted);
            margin-top: 4px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* ─── Empty State ───────────────────────────────────── */
        .empty-state {
            text-align: center; padding: 60px 20px;
        }
        .empty-state .icon { font-size: 60px; }
        .empty-state h2 { margin-top: 12px; font-size: 1.1rem; color: #555; }
        .empty-state p  { font-size: .85rem; color: var(--text-muted); margin-top: 6px; }

        /* ─── Footer ────────────────────────────────────────── */
        .site-footer {
            text-align: center;
            padding: 16px 20px 24px;
            font-size: .75rem; color: var(--text-muted);
        }

        /* ─── Responsive: single column on very small screens ── */
        @media (max-width: 360px) {
            .product-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <!-- ═══════════════ HEADER ════════════════════════════════ -->
    <header class="site-header">
        <div class="header-content">
            @if(!empty($company->logo))
                <img class="header-logo" src="{{ asset($company->logo) }}" alt="{{ $company->name }}" />
            @else
                <img class="header-logo" src="/images/delicasy_logo.png" alt="{{ $company->name }}" />
            @endif

            <h1>{{ $company->name ?? 'Our Food Menu' }}</h1>

            @if(!empty($company->address))
                <p>📍 {{ $company->address }}</p>
            @endif
        </div>
    </header>

    <!-- ═══════════════ CATEGORY NAV ═════════════════════════ -->
    @if($sections->isNotEmpty())
    <div class="category-nav-wrapper">
        <div class="category-nav-container">
            <button class="slider-arrow slider-prev" id="sliderPrev" title="Previous">‹</button>
            <nav class="category-nav" id="categoryNav">
                @foreach($sections as $section)
                    <button class="cat-pill" data-target="{{ $section['id'] }}">
                        {{ $section['name'] }}
                    </button>
                @endforeach
            </nav>
            <button class="slider-arrow slider-next" id="sliderNext" title="Next">›</button>
        </div>
    </div>
    @endif

    <!-- ═══════════════ MENU BODY ════════════════════════════ -->
    <main class="main">

        @if($sections->isEmpty())
            <div class="empty-state">
                <div class="icon">🍽️</div>
                <h2>Menu Coming Soon</h2>
                <p>We're updating our menu. Please check back shortly.</p>
            </div>
        @else
            @foreach($sections as $section)
            <section class="category-section" id="{{ $section['id'] }}">

                <!-- Section Header -->
                <div class="section-header">
                    @if(!empty($section['image']))
                        {{-- <img class="section-thumb" src="{{ asset($section['image']) }}" alt="{{ $section['name'] }}" /> --}}
                    @else
                        <div class="section-thumb-placeholder">🍴</div>
                    @endif
                    <span class="section-title">{{ $section['name'] }}</span>
                    <div class="section-divider"></div>
                </div>

                <!-- Product Cards -->
                <div class="product-grid">
                    @foreach($section['products'] as $product)
                    <div class="product-card">

                        <!-- Image -->
                        <div class="product-img-wrap">
                            @if(!empty($product->image))
                                <img src="{{ asset($product->image) }}" alt="{{ $product->name }}" loading="lazy" />
                            @else
                                <div class="product-img-placeholder">🍽️</div>
                            @endif

                            @if($product->discount > 0)
                                <span class="badge-discount">-{{ $product->discount }}%</span>
                            @endif

                            @if($product->is_beverage)
                                <span class="badge-beverage">🥤 Drink</span>
                            @endif
                        </div>

                        <!-- Info -->
                        <div class="product-info">
                            <p class="product-name">{{ $product->name }}</p>
                            @if(!empty($product->description))
                                <p class="product-desc">{{ $product->description }}</p>
                            @endif
                            <div class="price-row">
                                @if($product->discounted_price && $product->discounted_price < $product->selling_price)
                                    <span class="price-final">Rs. {{ number_format($product->discounted_price, 2) }}</span>
                                    <span class="price-original">Rs. {{ number_format($product->selling_price, 2) }}</span>
                                @else
                                    <span class="price-final">Rs. {{ number_format($product->selling_price, 2) }}</span>
                                @endif
                            </div>
                            
                        </div>
                    </div>
                    @endforeach
                </div>

            </section>
            @endforeach
        @endif
    </main>

    <!-- ═══════════════ FOOTER ═══════════════════════════════ -->
    {{-- <footer class="site-footer">
        @if(!empty($company->phone))
            📞 {{ $company->phone }} &nbsp;|&nbsp;
        @endif
        &copy; {{ date('Y') }} {{ $company->name ?? config('app.name') }}
    </footer> --}}

    <!-- ═══════════════ SCRIPTS ═══════════════════════════════ -->
    <script>
        // ── Sticky nav pill active state on scroll ──────────────
        const sections  = document.querySelectorAll('.category-section');
        const pills     = document.querySelectorAll('.cat-pill');
        const navBar    = document.getElementById('categoryNav');
        const prevBtn   = document.getElementById('sliderPrev');
        const nextBtn   = document.getElementById('sliderNext');

        function setActive(id) {
            pills.forEach(p => {
                const isActive = p.dataset.target === id;
                p.classList.toggle('active', isActive);
                if (isActive) {
                    p.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                    updateArrowStates();
                }
            });
        }

        // ── Slider Navigation ─────────────────────────────────
        function updateArrowStates() {
            if (navBar) {
                const scrollLeft = navBar.scrollLeft;
                const scrollWidth = navBar.scrollWidth;
                const clientWidth = navBar.clientWidth;
                
                prevBtn.classList.toggle('disabled', scrollLeft <= 0);
                nextBtn.classList.toggle('disabled', scrollLeft >= scrollWidth - clientWidth - 10);
            }
        }

        function scrollSlider(direction) {
            if (navBar) {
                const scrollAmount = 200;
                navBar.scrollBy({
                    left: direction === 'next' ? scrollAmount : -scrollAmount,
                    behavior: 'smooth'
                });
                setTimeout(updateArrowStates, 300);
            }
        }

        if (prevBtn) prevBtn.addEventListener('click', () => scrollSlider('prev'));
        if (nextBtn) nextBtn.addEventListener('click', () => scrollSlider('next'));

        if (navBar) {
            navBar.addEventListener('scroll', updateArrowStates);
            // Initial state
            setTimeout(updateArrowStates, 100);
        }

        // Click pill → scroll to section
        pills.forEach(pill => {
            pill.addEventListener('click', () => {
                const target = document.getElementById(pill.dataset.target);
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });

        // IntersectionObserver → update active pill while scrolling
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    setActive(entry.target.id);
                }
            });
        }, { rootMargin: '-50% 0px -40% 0px', threshold: 0 });

        sections.forEach(s => observer.observe(s));

        // Activate first pill on load
        if (pills.length > 0) pills[0].classList.add('active');
    </script>
</body>
</html>
