{{-- resources/views/analyse/index.blade.php --}}
<!DOCTYPE html>
<html lang="fr" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PlagioScan – Rapport d'analyse</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800;14..32,900&display=swap" rel="stylesheet">
    <style>
        /* ===== DESIGN TOKENS ===== */
        :root {
            --ps-primary: #1a1d23;
            --ps-primary-rgb: 26,29,35;
            --ps-accent: #6366f1;
            --ps-accent-rgb: 99,102,241;
            --ps-accent-light: #a5b4fc;
            --ps-accent-subtle: #eef2ff;
            --ps-success: #10b981;
            --ps-success-rgb: 16,185,129;
            --ps-success-subtle: #ecfdf5;
            --ps-warning: #f59e0b;
            --ps-warning-subtle: #fffbeb;
            --ps-danger: #ef4444;
            --ps-danger-rgb: 239,68,68;
            --ps-danger-subtle: #fef2f2;
            --ps-orange: #f97316;
            --ps-purple: #8b5cf6;
            --ps-cyan: #06b6d4;
            --ps-body-bg: #f0f2f5;
            --ps-card-bg: #ffffff;
            --ps-border: #e5e7eb;
            --ps-muted: #6b7280;
            --ps-text: #111827;
            --ps-text-secondary: #4b5563;
            --ps-radius: 1rem;
            --ps-radius-lg: 1.25rem;
            --ps-radius-xl: 1.5rem;
            --ps-shadow-card: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
            --ps-shadow-card-hover: 0 10px 30px -5px rgba(0,0,0,0.1), 0 4px 8px rgba(0,0,0,0.04);
            --ps-shadow-hero: 0 25px 60px -12px rgba(0,0,0,0.2);
            --ps-transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* ===== BASE ===== */
        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--ps-body-bg);
            color: var(--ps-text);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* ===== AMBIENT BACKGROUND ===== */
        .ps-bg-ambient {
            position: fixed;
            inset: 0;
            z-index: -1;
            overflow: hidden;
            background: linear-gradient(160deg, #0f1117 0%, #1a1d2e 30%, #12141c 60%, #0d0f14 100%);
        }
        .ps-bg-ambient .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(120px);
            opacity: 0.12;
            animation: orbFloat 25s ease-in-out infinite;
        }
        .ps-bg-ambient .orb-1 {
            width: 700px; height: 700px;
            background: var(--ps-accent);
            top: -250px; right: -150px;
        }
        .ps-bg-ambient .orb-2 {
            width: 500px; height: 500px;
            background: var(--ps-purple);
            bottom: -200px; left: -100px;
            animation-delay: -12s;
        }
        .ps-bg-ambient .orb-3 {
            width: 350px; height: 350px;
            background: var(--ps-cyan);
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            animation-delay: -7s;
            opacity: 0.06;
        }
        @keyframes orbFloat {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(25px, -35px) scale(1.04); }
            66% { transform: translate(-20px, 25px) scale(0.96); }
        }

        /* ===== TOP NAVBAR ===== */
        .ps-navbar {
            background: rgba(26, 29, 35, 0.85);
            backdrop-filter: blur(20px) saturate(1.5);
            -webkit-backdrop-filter: blur(20px) saturate(1.5);
            border-bottom: 1px solid rgba(255,255,255,0.06);
            padding: 0.75rem 0;
            position: sticky;
            top: 0;
            z-index: 1030;
        }
        .ps-navbar .navbar-brand {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            font-weight: 800;
            font-size: 1.25rem;
            color: #ffffff !important;
            letter-spacing: -0.02em;
        }
        .ps-brand-icon {
            width: 38px; height: 38px;
            background: linear-gradient(135deg, var(--ps-accent), var(--ps-purple));
            border-radius: 11px;
            display: flex; align-items: center; justify-content: center;
            color: #fff;
            font-size: 1.1rem;
            box-shadow: 0 4px 14px rgba(99,102,241,0.4);
        }
        .ps-brand-sub {
            font-size: 0.68rem;
            font-weight: 500;
            color: rgba(255,255,255,0.45);
            letter-spacing: 0.02em;
        }
        .ps-navbar .nav-report-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.35rem 0.85rem;
            border-radius: 50rem;
            font-size: 0.72rem;
            font-weight: 600;
            color: var(--ps-accent-light);
            background: rgba(99,102,241,0.12);
            border: 1px solid rgba(99,102,241,0.2);
        }
        .ps-navbar .nav-meta {
            font-size: 0.72rem;
            color: rgba(255,255,255,0.4);
        }

        /* ===== MAIN LAYOUT ===== */
        .ps-main {
            width : 1896px;
            margin: 0 auto;
            padding: 2rem 1rem 4rem;
        }

        /* ===== HERO CARD ===== */
        .ps-hero-card {
            background: rgba(255,255,255,0.94);
            backdrop-filter: blur(30px) saturate(1.5);
            -webkit-backdrop-filter: blur(30px) saturate(1.5);
            border: 1px solid rgba(255,255,255,0.6);
            border-radius: var(--ps-radius-xl);
            box-shadow: var(--ps-shadow-hero);
            padding: 2.25rem 2.5rem;
            animation: heroReveal 0.7s cubic-bezier(0.16, 1, 0.3, 1) both;
        }
        @keyframes heroReveal {
            from { opacity: 0; transform: translateY(40px) scale(0.97); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* ===== SECTION HEADERS ===== */
        .ps-section {
            margin-top: 1.75rem;
            margin-bottom: 0;
        }
        .ps-section-header {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            margin-bottom: 1rem;
        }
        .ps-section-icon {
            width: 32px; height: 32px;
            border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.9rem;
            flex-shrink: 0;
        }
        .ps-section-icon.accent { background: var(--ps-accent-subtle); color: var(--ps-accent); }
        .ps-section-icon.success { background: var(--ps-success-subtle); color: var(--ps-success); }
        .ps-section-icon.warning { background: var(--ps-warning-subtle); color: var(--ps-warning); }
        .ps-section-icon.danger { background: var(--ps-danger-subtle); color: var(--ps-danger); }
        .ps-section-icon.purple { background: #f5f3ff; color: var(--ps-purple); }
        .ps-section-icon.orange { background: #fff7ed; color: var(--ps-orange); }
        .ps-section-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--ps-text);
            margin: 0;
            letter-spacing: -0.01em;
        }

        /* ===== STAT CARDS (Bootstrap Grid) ===== */
        .ps-stat-card {
            background: var(--ps-card-bg);
            border: 1px solid var(--ps-border);
            border-radius: var(--ps-radius-lg);
            padding: 1.15rem 1.25rem;
            transition: var(--ps-transition);
            position: relative;
            overflow: hidden;
            height: 100%;
        }
        .ps-stat-card::after {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--ps-accent), var(--ps-purple));
            opacity: 0;
            transition: opacity 0.3s;
        }
        .ps-stat-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--ps-shadow-card-hover);
            border-color: var(--ps-accent-light);
        }
        .ps-stat-card:hover::after { opacity: 1; }
        .ps-stat-label {
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--ps-muted);
            margin-bottom: 0.6rem;
        }
        .ps-stat-value {
            font-size: 1.15rem;
            font-weight: 800;
            color: var(--ps-text);
            line-height: 1.25;
            word-break: break-all;
            letter-spacing: -0.02em;
        }
        .ps-stat-detail {
            font-size: 0.72rem;
            color: var(--ps-text-secondary);
            margin-top: 0.5rem;
            line-height: 1.6;
        }
        .ps-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.15rem 0.55rem;
            border-radius: 50rem;
            font-size: 0.62rem;
            font-weight: 700;
            border: 1px solid;
        }
        .ps-tag.pdf { background: #fef2f2; color: #dc2626; border-color: #fecaca; }
        .ps-tag.docx { background: #eff6ff; color: #2563eb; border-color: #bfdbfe; }
        .ps-tag.img { background: #f5f3ff; color: #7c3aed; border-color: #ddd6fe; }
        .ps-tag.default { background: #f3f4f6; color: #4b5563; border-color: var(--ps-border); }
        .ps-engine-pill {
            display: inline-block;
            padding: 0.1rem 0.45rem;
            border-radius: 50rem;
            font-size: 0.6rem;
            font-weight: 700;
            background: #f3f4f6;
            color: var(--ps-text);
            border: 1px solid var(--ps-border);
        }

        /* ===== SCORE SECTION ===== */
        .ps-score-card {
            background: linear-gradient(135deg, #f9fafb, #f3f4f6);
            border: 1px solid var(--ps-border);
            border-radius: var(--ps-radius-xl);
            padding: 2rem;
        }
        .ps-score-ring-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .ps-score-ring {
            position: relative;
            width: 170px;
            height: 170px;
        }
        .ps-score-ring svg {
            width: 100%; height: 100%;
            transform: rotate(-90deg);
        }
        .ps-score-ring .track {
            fill: none;
            stroke: var(--ps-border);
            stroke-width: 10;
        }
        .ps-score-ring .arc {
            fill: none;
            stroke-width: 10;
            stroke-linecap: round;
            stroke-dasharray: 502.65;
            stroke-dashoffset: 502.65;
            transition: stroke-dashoffset 1.5s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .ps-score-center {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .ps-score-number {
            font-size: 2.6rem;
            font-weight: 900;
            line-height: 1;
            letter-spacing: -0.04em;
        }
        .ps-score-unit {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--ps-muted);
            margin-top: 0.15rem;
        }
        .ps-score-caption {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--ps-text-secondary);
            text-align: center;
            margin-top: 0.75rem;
        }

        /* Summary boxes */
        .ps-summary-box {
            text-align: center;
            padding: 0.85rem 0.5rem;
            border-radius: var(--ps-radius);
            transition: transform 0.2s;
            border: 1px solid;
        }
        .ps-summary-box:hover { transform: scale(1.04); }
        .ps-summary-box.critical { background: var(--ps-danger-subtle); border-color: #fecaca; }
        .ps-summary-box.high { background: #fff7ed; border-color: #fed7aa; }
        .ps-summary-box.medium { background: var(--ps-warning-subtle); border-color: #fde68a; }
        .ps-summary-box.low { background: #f3f4f6; border-color: var(--ps-border); }
        .ps-summary-count {
            font-size: 1.5rem;
            font-weight: 900;
            line-height: 1;
            letter-spacing: -0.03em;
        }
        .ps-summary-box.critical .ps-summary-count { color: var(--ps-danger); }
        .ps-summary-box.high .ps-summary-count { color: var(--ps-orange); }
        .ps-summary-box.medium .ps-summary-count { color: var(--ps-warning); }
        .ps-summary-box.low .ps-summary-count { color: var(--ps-muted); }
        .ps-summary-label {
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--ps-text-secondary);
            margin-top: 0.3rem;
        }

        /* Alert badges */
        .ps-level-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.2rem 0.6rem;
            border-radius: 50rem;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .ps-level-badge.critical { background: var(--ps-danger); color: #fff; }
        .ps-level-badge.high { background: var(--ps-orange); color: #fff; }
        .ps-level-badge.medium { background: var(--ps-warning); color: #1e293b; }
        .ps-level-badge.low { background: var(--ps-accent); color: #fff; }
        .ps-level-badge.none { background: #94a3b8; color: #fff; }

        .ps-plagiat-alert {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.55rem 1.15rem;
            border-radius: var(--ps-radius);
            font-weight: 700;
            font-size: 0.82rem;
            margin-top: 0.75rem;
        }
        .ps-plagiat-alert.danger {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: #fff;
            box-shadow: 0 4px 18px rgba(220,38,38,0.35);
        }
        .ps-plagiat-alert.success {
            background: linear-gradient(135deg, #10b981, #059669);
            color: #fff;
            box-shadow: 0 4px 18px rgba(16,185,129,0.3);
        }

        /* ===== TEXT MATCHES ===== */
        .ps-match-card {
            background: var(--ps-card-bg);
            border: 1px solid var(--ps-border);
            border-radius: var(--ps-radius-lg);
            overflow: hidden;
            transition: var(--ps-transition);
            animation: matchSlide 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
            position: relative;
        }
        .ps-match-card:nth-child(1) { animation-delay: 0.05s; }
        .ps-match-card:nth-child(2) { animation-delay: 0.1s; }
        .ps-match-card:nth-child(3) { animation-delay: 0.15s; }
        .ps-match-card:nth-child(4) { animation-delay: 0.2s; }
        .ps-match-card:nth-child(5) { animation-delay: 0.25s; }
        @keyframes matchSlide {
            from { opacity: 0; transform: translateY(14px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .ps-match-card:hover {
            border-color: var(--ps-accent-light);
            box-shadow: var(--ps-shadow-card-hover);
        }
        .ps-match-bar {
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 4px;
        }
        .ps-match-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
            padding: 1rem 1.25rem 0;
        }
        .ps-match-filename {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .ps-match-filename strong {
            font-size: 0.9rem;
        }
        .ps-match-score {
            font-size: 1.6rem;
            font-weight: 900;
            letter-spacing: -0.03em;
        }
        .ps-match-progress {
            padding: 0.6rem 1.25rem;
        }
        .ps-match-engine-details {
            padding: 0 1.25rem 1rem;
        }
        .ps-engine-toggle {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.72rem;
            font-weight: 600;
            color: var(--ps-muted);
            cursor: pointer;
            padding: 0.4rem 0;
            border: none;
            background: none;
            transition: color 0.2s;
        }
        .ps-engine-toggle:hover { color: var(--ps-accent); }
        .ps-engine-toggle i { transition: transform 0.3s; }
        .ps-engine-toggle.active i { transform: rotate(180deg); }

        .ps-engine-collapse {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .ps-engine-collapse.open { max-height: 500px; }

        .ps-engine-table {
            width: 100%;
            font-size: 0.75rem;
        }
        .ps-engine-table th {
            font-weight: 700;
            color: var(--ps-muted);
            text-transform: uppercase;
            font-size: 0.65rem;
            letter-spacing: 0.06em;
            padding: 0.5rem 0.6rem;
            border-bottom: 1px solid var(--ps-border);
        }
        .ps-engine-table td {
            padding: 0.5rem 0.6rem;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: middle;
        }
        .ps-engine-table tr:last-child td { border-bottom: none; }
        .ps-engine-table tbody tr:hover td { background: #f9fafb; }

        .ps-mini-bar {
            width: 70px; height: 4px;
            background: var(--ps-border);
            border-radius: 50rem;
            overflow: hidden;
            display: inline-block;
            vertical-align: middle;
        }
        .ps-mini-bar .fill {
            height: 100%;
            border-radius: 50rem;
            background: var(--ps-accent);
            transition: width 0.8s ease;
        }

        .ps-match-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.4rem 1.25rem 0.85rem;
            font-size: 0.68rem;
            color: var(--ps-muted);
        }

        /* Suspicious sections */
        .ps-suspect {
            background: var(--ps-warning-subtle);
            border: 1px solid #fde68a;
            border-radius: 0.5rem;
            padding: 0.45rem 0.65rem;
            margin-bottom: 0.35rem;
            font-size: 0.7rem;
            color: var(--ps-text-secondary);
        }

        /* ===== IMAGE ANALYSIS ===== */
        .ps-image-card {
            background: linear-gradient(135deg, #faf5ff, #f9fafb);
            border: 1px solid #e9d5ff;
            border-radius: var(--ps-radius-lg);
            padding: 1.25rem 1.35rem;
            margin-bottom: 1rem;
        }
        .ps-image-card:last-child { margin-bottom: 0; }
        .ps-img-icon {
            width: 40px; height: 40px;
            border-radius: 11px;
            background: linear-gradient(135deg, var(--ps-purple), #a78bfa);
            display: flex; align-items: center; justify-content: center;
            color: #fff;
            font-size: 1rem;
            flex-shrink: 0;
        }
        .ps-img-score-badge {
            padding: 0.3rem 0.75rem;
            border-radius: 50rem;
            font-weight: 800;
            font-size: 0.78rem;
            color: #fff;
        }

        /* ===== NO MATCHES ===== */
        .ps-no-matches {
            text-align: center;
            padding: 2.5rem 1.5rem;
            background: var(--ps-success-subtle);
            border: 1px solid #a7f3d0;
            border-radius: var(--ps-radius-lg);
        }
        .ps-no-matches-icon {
            width: 64px; height: 64px;
            border-radius: 50%;
            background: var(--ps-success);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 0.85rem;
            color: #fff;
            font-size: 1.7rem;
        }
        .ps-no-matches h4 { font-weight: 700; color: #065f46; font-size: 1rem; }
        .ps-no-matches p { color: var(--ps-muted); font-size: 0.82rem; margin: 0; }

        /* ===== RAW JSON ===== */
        .ps-raw-json-toggle {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--ps-muted);
            cursor: pointer;
            border: none;
            background: none;
            padding: 0.4rem 0;
            transition: color 0.2s;
        }
        .ps-raw-json-toggle:hover { color: var(--ps-accent); }
        .ps-raw-json-wrap {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.5s ease;
        }
        .ps-raw-json-wrap.open { max-height: 2000px; }
        .ps-raw-json-pre {
            background: #0f172a;
            color: #a5f3fc;
            padding: 1.25rem;
            border-radius: var(--ps-radius);
            font-size: 0.7rem;
            font-family: 'Fira Code', 'Cascadia Code', monospace;
            overflow-x: auto;
            max-height: 450px;
            overflow-y: auto;
            line-height: 1.7;
            margin-top: 0.5rem;
        }

        /* ===== BUTTONS ===== */
        .ps-btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.65rem 1.35rem;
            border-radius: 50rem;
            font-weight: 600;
            font-size: 0.82rem;
            border: none;
            background: var(--ps-primary);
            color: #fff;
            text-decoration: none;
            transition: var(--ps-transition);
            box-shadow: 0 4px 14px rgba(var(--ps-primary-rgb), 0.3);
        }
        .ps-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 22px rgba(var(--ps-primary-rgb), 0.4);
            color: #fff;
        }
        .ps-btn-outline {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.55rem 1.15rem;
            border-radius: 50rem;
            font-weight: 600;
            font-size: 0.78rem;
            border: 1px solid var(--ps-border);
            background: #fff;
            color: var(--ps-text-secondary);
            text-decoration: none;
            transition: var(--ps-transition);
            cursor: pointer;
        }
        .ps-btn-outline:hover {
            background: #f9fafb;
            border-color: var(--ps-accent);
            color: var(--ps-accent);
        }

        /* ===== FOOTER ===== */
        .ps-report-footer {
            text-align: center;
            margin-top: 1.75rem;
            padding-top: 1.25rem;
            border-top: 1px solid var(--ps-border);
            font-size: 0.7rem;
            color: var(--ps-muted);
        }
        .ps-report-footer i { margin-right: 0.25rem; }

        /* ===== TEXT ANALYSIS INFO BOX ===== */
        .ps-text-info-box {
            margin-top: 1rem;
            padding: 0.85rem 1rem;
            background: #fff;
            border-radius: var(--ps-radius);
            border: 1px solid var(--ps-border);
            font-size: 0.75rem;
        }
        .ps-text-info-box strong {
            font-weight: 700;
        }

        /* ===== CONTENT ANALYSIS CARD ===== */
        .ps-content-card {
            background: var(--ps-card-bg);
            border: 1px solid var(--ps-border);
            border-radius: var(--ps-radius-lg);
            padding: 1rem 1.25rem;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 991px) {
            .ps-score-card > .row > [class*="col-"] {
                text-align: center;
            }
            .ps-score-ring-wrap { margin-bottom: 1.25rem; }
        }
        @media (max-width: 767px) {
            .ps-hero-card { padding: 1.5rem 1.15rem; }
            .ps-main { padding: 1.25rem 0.75rem 3rem; }
            .ps-summary-box { margin-bottom: 0.5rem; }
        }
        @media (max-width: 575px) {
            .ps-stat-value { font-size: 1rem; }
            .ps-match-score { font-size: 1.3rem; }
            .ps-score-ring { width: 140px; height: 140px; }
            .ps-score-number { font-size: 2rem; }
        }

        /* ===== PRINT ===== */
        @media print {
            body { background: #fff !important; }
            .ps-bg-ambient { display: none; }
            .ps-navbar { display: none; }
            .ps-hero-card {
                box-shadow: none;
                backdrop-filter: none;
                animation: none;
                border: 1px solid #ddd;
            }
            .ps-btn-primary,
            .ps-btn-outline,
            .ps-engine-toggle,
            .ps-raw-json-toggle { display: none !important; }
            .ps-match-card:hover,
            .ps-stat-card:hover { transform: none; box-shadow: none; }
            .ps-raw-json-wrap { display: none; }
        }
    </style>
</head>
<body>

{{-- ===== AMBIENT BACKGROUND ===== --}}
<div class="ps-bg-ambient">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>
</div>

{{-- ===== STICKY NAVBAR ===== --}}
<nav class="ps-navbar">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <a class="navbar-brand" href="{{ route('upload.index') }}">
                <div class="ps-brand-icon"><i class="bi bi-shield-shaded"></i></div>
                <div>
                    <div>PlagioScan</div>
                    <div class="ps-brand-sub">Moteur de d&eacute;tection anti-plagiat</div>
                </div>
            </a>
            <div class="d-flex align-items-center gap-3 flex-wrap justify-content-end">
                <span class="nav-report-badge">
                    <i class="bi bi-file-earmark-bar-graph"></i> Rapport d'analyse
                </span>
                <span class="nav-meta d-none d-sm-inline">
                    <i class="bi bi-clock me-1"></i>{{ now()->format('d/m/Y  H:i') }}
                </span>
            </div>
        </div>
    </div>
</nav>

{{-- ===== MAIN CONTENT ===== --}}
<div class="ps-main">
    <div class="ps-hero-card">

        {{-- ===== FILE INFO STATS ===== --}}
        <div class="ps-section">
            <div class="ps-section-header">
                <div class="ps-section-icon accent"><i class="bi bi-info-circle"></i></div>
                <h5 class="ps-section-title">Informations du fichier</h5>
            </div>
        </div>

        <div class="row g-3 mb-4">
            {{-- Fichier --}}
            <div class="col-sm-6 col-xl-3">
                <div class="ps-stat-card">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div class="ps-section-icon accent" style="width:28px;height:28px;font-size:0.8rem;">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                    </div>
                    <div class="ps-stat-label">Fichier analys&eacute;</div>
                    <div class="ps-stat-value">{{ $filename }}</div>
                    <div class="ps-stat-detail">
                        Type : {{ $file_type === 'code' ? 'Code source' : ($file_type === 'image' ? 'Image' : 'Texte') }}
                        <br>Taille : {{ number_format($content_length / 1024, 2) }} Ko
                    </div>
                </div>
            </div>

            {{-- Comparaisons --}}
            <div class="col-sm-6 col-xl-3">
                <div class="ps-stat-card">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div class="ps-section-icon success" style="width:28px;height:28px;font-size:0.8rem;">
                            <i class="bi bi-database"></i>
                        </div>
                    </div>
                    <div class="ps-stat-label">Documents compar&eacute;s</div>
                    <div class="ps-stat-value">{{ $num_comparisons }}</div>
                    <div class="ps-stat-detail">Base de r&eacute;f&eacute;rence statique</div>
                </div>
            </div>

            {{-- Moteurs --}}
            <div class="col-sm-6 col-xl-3">
                <div class="ps-stat-card">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div class="ps-section-icon purple" style="width:28px;height:28px;font-size:0.8rem;">
                            <i class="bi bi-cpu"></i>
                        </div>
                    </div>
                    <div class="ps-stat-label">Moteurs actifs</div>
                    <div class="ps-stat-value">{{ count($engines_used) }}</div>
                    <div class="ps-stat-detail">
                        @foreach($engines_used as $engine => $active)
                            <span class="ps-engine-pill">{{ strtoupper($engine) }}</span>
                        @endforeach
                        @if(empty($engines_used))
                            <span class="ps-tag default">Aucun (base vide)</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Extraction --}}
            <div class="col-sm-6 col-xl-3">
                <div class="ps-stat-card">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div class="ps-section-icon orange" style="width:28px;height:28px;font-size:0.8rem;">
                            <i class="bi bi-extract"></i>
                        </div>
                    </div>
                    <div class="ps-stat-label">Extraction</div>
                    <div class="ps-stat-value">
                        @if(is_array($extraction) && count($extraction) > 0)
                            {{ ucfirst($extraction['format'] ?? 'N/A') }}
                        @else
                            Texte
                        @endif
                    </div>
                    <div class="ps-stat-detail d-flex flex-wrap gap-1 mt-1">
                        @if(is_array($extraction) && count($extraction) > 0)
                            @if(isset($extraction['format']) && $extraction['format'] === 'pdf')
                                <span class="ps-tag pdf"><i class="bi bi-filetype-pdf"></i> PDF</span>
                                <span class="ps-tag default"><i class="bi bi-file-text"></i> {{ $extraction['pages'] ?? 0 }} p.</span>
                            @elseif(isset($extraction['format']) && $extraction['format'] === 'docx')
                                <span class="ps-tag docx"><i class="bi bi-filetype-docx"></i> Word</span>
                            @endif
                            @if($images_extracted > 0)
                                <span class="ps-tag img"><i class="bi bi-image"></i> {{ $images_extracted }} img</span>
                            @endif
                        @else
                            <span class="ps-tag default">Contenu textuel direct</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== IMAGE ANALYSIS ===== --}}
        @if(is_array($image_analysis) && isset($image_analysis['analyzed']) && $image_analysis['analyzed'])
            <div class="ps-section">
                <div class="ps-section-header">
                    <div class="ps-section-icon purple"><i class="bi bi-image"></i></div>
                    <h5 class="ps-section-title">Analyse d'images</h5>
                </div>
            </div>
            <div class="mb-4">
                @if(count($image_matches) > 0)
                    @foreach($image_matches as $imgMatch)
                        @php
                            $imgConf = ($imgMatch['confidence'] ?? 0) * 100;
                            $imgColor = $imgConf >= 80 ? '#dc2626' : ($imgConf >= 60 ? '#f97316' : '#3b82f6');
                            $imgLevelLabels = ['critical' => 'Critique', 'high' => 'Élevé', 'medium' => 'Moyen', 'low' => 'Faible'];
                        @endphp
                        <div class="ps-image-card">
                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                <div class="ps-img-icon"><i class="bi bi-images"></i></div>
                                <div class="flex-grow-1" style="min-width: 200px;">
                                    <strong style="font-size: 0.82rem;">Image #{{ ($imgMatch['new_image_index'] ?? 0) + 1 }}</strong>
                                    <br>
                                    <small class="text-muted" style="font-size: 0.7rem;">
                                        vs
                                        {{ $imgMatch['matched_filename'] ?? html_entity_decode('R&eacute;f&eacute;rence') }}
                                        &bull; {{ $imgLevelLabels[$imgMatch['level'] ?? 'low'] ?? ucfirst($imgMatch['level'] ?? 'low') }}
                                        @if(isset($imgMatch['phash_distance']))
                                            &bull; pHash: {{ $imgMatch['phash_distance'] }}
                                        @endif
                                        @if(isset($imgMatch['feature_similarity']))
                                            &bull; Feature: {{ round(($imgMatch['feature_similarity'] ?? 0) * 100, 1) }}%
                                        @endif
                                    </small>
                                </div>
                                <span class="ps-img-score-badge" style="background: {{ $imgColor }};">
                                    {{ round($imgConf, 1) }}%
                                </span>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="ps-content-card text-center py-3">
                        <span class="text-muted" style="font-size: 0.8rem;">
                            <i class="bi bi-check-circle me-1"></i> Aucune similarit&eacute; d'image d&eacute;tect&eacute;e
                            ({{ $image_analysis['images_checked'] ?? 0 }} image(s) analys&eacute;e(s) contre {{ $image_analysis['images_in_database'] ?? 0 }} en base).
                        </span>
                    </div>
                @endif
            </div>
        @endif

        {{-- ===== SCORE PRINCIPAL ===== --}}
        <div class="ps-section">
            <div class="ps-section-header">
                <div class="ps-section-icon accent"><i class="bi bi-speedometer2"></i></div>
                <h5 class="ps-section-title">Score de similarit&eacute;</h5>
            </div>
        </div>

        <div class="ps-score-card mb-4">
            <div class="row align-items-center g-4">
                {{-- Score ring --}}
                <div class="col-lg-4">
                    <div class="ps-score-ring-wrap">
                        @php
                            $circumference = 2 * pi() * 80;
                            $offset = $circumference - ($overall_score * $circumference);
                            $scoreColor = $overall_score >= 0.6 ? 'var(--ps-danger)' : ($overall_score >= 0.4 ? 'var(--ps-warning)' : 'var(--ps-success)');
                        @endphp
                        <div class="ps-score-ring">
                            <svg viewBox="0 0 180 180">
                                <circle class="track" cx="90" cy="90" r="80" />
                                <circle class="arc" cx="90" cy="90" r="80"
                                        style="stroke: {{ $scoreColor }}; stroke-dashoffset: {{ $offset }};"
                                        data-target="{{ $offset }}" />
                            </svg>
                            <div class="ps-score-center">
                                <div class="ps-score-number" style="color: {{ $scoreColor }};">{{ round($overall_score * 100) }}</div>
                                <div class="ps-score-unit">% de similarit&eacute;</div>
                            </div>
                        </div>
                        <div class="ps-score-caption">Score global d&eacute;tect&eacute;</div>
                        @if($plagiarism_detected)
                            <div class="ps-plagiat-alert danger">
                                <i class="bi bi-exclamation-triangle-fill"></i> Plagiat potentiel d&eacute;tect&eacute;
                            </div>
                        @else
                            <div class="ps-plagiat-alert success">
                                <i class="bi bi-check-circle-fill"></i> Aucun plagiat significatif
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Summary breakdown --}}
                <div class="col-lg-8">
                    <div style="font-size: 0.82rem; font-weight: 600; color: var(--ps-text-secondary); margin-bottom: 0.85rem;">
                        R&eacute;partition des correspondances
                    </div>
                    @php
                        $summaryLevels = [
                            'critical' => ($content_analysis['summary']['critical'] ?? 0) + (($image_analysis['summary']['critical'] ?? 0)),
                            'high'     => ($content_analysis['summary']['high'] ?? 0) + (($image_analysis['summary']['high'] ?? 0)),
                            'medium'   => ($content_analysis['summary']['medium'] ?? 0) + (($image_analysis['summary']['medium'] ?? 0)),
                            'low'      => ($content_analysis['summary']['low'] ?? 0) + (($image_analysis['summary']['low'] ?? 0)),
                        ];
                    @endphp
                    <div class="row g-2 mb-3">
                        <div class="col-6 col-sm-3">
                            <div class="ps-summary-box critical">
                                <div class="ps-summary-count">{{ $summaryLevels['critical'] }}</div>
                                <div class="ps-summary-label">Critique</div>
                            </div>
                        </div>
                        <div class="col-6 col-sm-3">
                            <div class="ps-summary-box high">
                                <div class="ps-summary-count">{{ $summaryLevels['high'] }}</div>
                                <div class="ps-summary-label">&Eacute;lev&eacute;</div>
                            </div>
                        </div>
                        <div class="col-6 col-sm-3">
                            <div class="ps-summary-box medium">
                                <div class="ps-summary-count">{{ $summaryLevels['medium'] }}</div>
                                <div class="ps-summary-label">Moyen</div>
                            </div>
                        </div>
                        <div class="col-6 col-sm-3">
                            <div class="ps-summary-box low">
                                <div class="ps-summary-count">{{ $summaryLevels['low'] }}</div>
                                <div class="ps-summary-label">Faible</div>
                            </div>
                        </div>
                    </div>

                    {{-- Text analysis info --}}
                    @if(is_array($content_analysis) && isset($content_analysis['max_score']))
                        <div class="ps-text-info-box">
                            <div style="font-weight: 700; margin-bottom: 0.4rem;">
                                <i class="bi bi-file-text me-1"></i> Analyse texte
                                @if(isset($content_analysis['paragraphs_analysed']))
                                    &mdash; {{ $content_analysis['paragraphs_analysed'] }} paragraphe(s) analys&eacute;(s)
                                @endif
                            </div>
                            Score max texte :
                            <strong style="color: {{ ($content_analysis['max_score'] ?? 0) >= 0.6 ? 'var(--ps-danger)' : 'var(--ps-success)' }};">
                                {{ round(($content_analysis['max_score'] ?? 0) * 100, 1) }}%
                            </strong>
                            &mdash;
                            <span class="ps-level-badge {{ $content_analysis['max_level'] ?? 'none' }}">
                                {{ ucfirst($content_analysis['max_level'] ?? 'none') }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ===== MATCHES D&Eacute;TAILL&Eacute;S ===== --}}
        <div class="ps-section">
            <div class="ps-section-header">
                <div class="ps-section-icon warning"><i class="bi bi-files"></i></div>
                <h5 class="ps-section-title">Correspondances d&eacute;taill&eacute;es (texte)</h5>
            </div>
        </div>

        @if(count($text_matches) > 0)
            <div class="d-flex flex-column gap-3 mb-4">
                @foreach($text_matches as $match)
                    @php
                        $scorePercent = round(($match['combined_score'] ?? 0) * 100, 1);
                        $level = $match['level'] ?? 'low';
                        $borderColor = match($level) {
                            'critical' => 'var(--ps-danger)',
                            'high' => 'var(--ps-orange)',
                            'medium' => 'var(--ps-warning)',
                            default => '#94a3b8'
                        };
                        $levelLabels = [
                            'critical' => 'Critique',
                            'high' => '&Eacute;lev&eacute;',
                            'medium' => 'Moyen',
                            'low' => 'Faible',
                            'none' => 'Nul'
                        ];
                    @endphp
                    <div class="ps-match-card">
                        <div class="ps-match-bar" style="background: {{ $borderColor }};"></div>

                        <div class="ps-match-header">
                            <div class="ps-match-filename">
                                <i class="bi bi-file-text-fill" style="color: {{ $borderColor }}; font-size: 1.1rem;"></i>
                                <strong>{{ $match['filename'] ?? 'Document inconnu' }}</strong>
                                <span class="ps-level-badge {{ $level }}">{{ $levelLabels[$level] ?? ucfirst($level) }}</span>
                                @if(isset($match['file_type']))
                                    <span class="ps-engine-pill" style="font-size: 0.58rem;">{{ strtoupper($match['file_type']) }}</span>
                                @endif
                            </div>
                            <div class="ps-match-score" style="color: {{ $borderColor }};">
                                {{ $scorePercent }}%
                            </div>
                        </div>

                        {{-- Progress bar (Bootstrap) --}}
                        <div class="ps-match-progress">
                            <div class="progress" style="height: 5px; border-radius: 50rem; background: var(--ps-border);">
                                <div class="progress-bar" role="progressbar"
                                     style="width: {{ $scorePercent }}%; background: {{ $borderColor }}; border-radius: 50rem; transition: width 1s cubic-bezier(0.16, 1, 0.3, 1);"
                                     aria-valuenow="{{ $scorePercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>

                        {{-- Suspicious sections --}}
                        @if(isset($match['suspicious_sections']) && count($match['suspicious_sections']) > 0)
                            <div style="padding: 0 1.25rem;">
                                <div style="font-size: 0.7rem; font-weight: 700; color: var(--ps-warning); margin-bottom: 0.35rem;">
                                    <i class="bi bi-exclamation-diamond me-1"></i>
                                    {{ count($match['suspicious_sections']) }} section(s) suspecte(s)
                                </div>
                                @foreach(array_slice($match['suspicious_sections'], 0, 3) as $sus)
                                    <div class="ps-suspect">
                                        <span class="ps-level-badge medium" style="font-size: 0.58rem;">{{ ucfirst($sus['level'] ?? '?') }} {{ round(($sus['score'] ?? 0) * 100) }}%</span>
                                        <span style="margin-left: 0.4rem;">{{ Str::limit(strip_tags($sus['preview'] ?? ''), 120) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- Engine details toggle --}}
                        @if(isset($match['engines']) && count($match['engines']) > 0)
                            <div class="ps-match-engine-details">
                                <button class="ps-engine-toggle" onclick="toggleEngine(this)">
                                    <i class="bi bi-chevron-down"></i> D&eacute;tails par moteur ({{ count($match['engines']) }})
                                </button>
                                <div class="ps-engine-collapse">
                                    <table class="ps-engine-table">
                                        <thead>
                                            <tr>
                                                <th>Moteur</th>
                                                <th>Score brut</th>
                                                <th>Poids</th>
                                                <th>Contribution</th>
                                                <th style="width: 90px;">Visuel</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($match['engines'] as $engineName => $engineData)
                                                @php $contribPercent = ($engineData['contribution'] ?? 0) * 100; @endphp
                                                <tr>
                                                    <td><span class="ps-engine-pill">{{ strtoupper($engineName) }}</span></td>
                                                    <td>{{ round($engineData['raw'] ?? 0, 4) }}</td>
                                                    <td>{{ $engineData['weight'] ?? 0 }}</td>
                                                    <td>{{ round($engineData['contribution'] ?? 0, 4) }}</td>
                                                    <td>
                                                        <div class="ps-mini-bar">
                                                            <div class="fill" style="width: {{ min($contribPercent, 100) }}%;"></div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        <div class="ps-match-footer">
                            <span><i class="bi bi-hash"></i> R&eacute;f. : {{ $match['submission_id'] ?? 'N/A' }}</span>
                            <span>Combin&eacute; : {{ $scorePercent }}%</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="ps-no-matches mb-4">
                <div class="ps-no-matches-icon"><i class="bi bi-check-lg"></i></div>
                <h4>Aucune correspondance texte</h4>
                <p>Le document analys&eacute; ne pr&eacute;sente aucune similarit&eacute; textuelle avec la base de r&eacute;f&eacute;rence.</p>
            </div>
        @endif

        {{-- ===== RAW JSON (debug) ===== --}}
        @if(isset($raw_response) && $raw_response)
            <div class="ps-section" style="margin-top: 1.5rem;">
                <button class="ps-raw-json-toggle" onclick="toggleRawJson(this)">
                    <i class="bi bi-code-slash"></i> Voir la r&eacute;ponse JSON brute de l'API
                </button>
                <div class="ps-raw-json-wrap">
                    <pre class="ps-raw-json-pre">{{ json_encode($raw_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            </div>
        @endif

        {{-- ===== ACTION BAR ===== --}}
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mt-4 pt-3" style="border-top: 1px solid var(--ps-border);">
            <a href="{{ route('upload.index') }}" class="ps-btn-primary">
                <i class="bi bi-arrow-left"></i> Nouvelle analyse
            </a>
            <div class="d-flex gap-2 flex-wrap">
                <button onclick="window.print();" class="ps-btn-outline">
                    <i class="bi bi-printer"></i> Imprimer
                </button>
                @if(isset($raw_response))
                    <button onclick="downloadJSON()" class="ps-btn-outline">
                        <i class="bi bi-download"></i> Exporter JSON
                    </button>
                @endif
            </div>
        </div>

        {{-- ===== FOOTER ===== --}}
        <div class="ps-report-footer">
            <i class="bi bi-shield-lock-fill"></i> Analyse confidentielle &bull; Algorithmes : TF-IDF, S&eacute;mantique BERT, Winnowing, LCS, pHash
        </div>

    </div><!-- /.ps-hero-card -->
</div><!-- /.ps-main -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleEngine(btn) {
        const wrap = btn.nextElementSibling;
        wrap.classList.toggle('open');
        btn.classList.toggle('active');
    }

    function toggleRawJson(btn) {
        const wrap = btn.nextElementSibling;
        wrap.classList.toggle('open');
        btn.classList.toggle('active');
    }

    // Animate score arc on load
    document.addEventListener('DOMContentLoaded', function () {
        const arc = document.querySelector('.arc');
        if (arc) {
            const target = parseFloat(arc.getAttribute('data-target'));
            arc.style.strokeDashoffset = '502.65';
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    arc.style.strokeDashoffset = target;
                });
            });
        }
    });

    @if(isset($raw_response))
    function downloadJSON() {
        const data = @json($raw_response);
        const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'plagiascan_report_{{ now()->format('Ymd_His') }}.json';
        a.click();
        URL.revokeObjectURL(url);
    }
    @endif
</script>
</body>
</html>
