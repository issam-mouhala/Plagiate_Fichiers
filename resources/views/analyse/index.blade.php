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
        /* ================================================================
           DESIGN TOKENS – Modern Glass Design System
           ================================================================ */
        :root {
            /* Core palette */
            --ps-primary: #0f172a;
            --ps-primary-rgb: 15,23,42;
            --ps-accent: #6366f1;
            --ps-accent-rgb: 99,102,241;
            --ps-accent-hover: #4f46e5;
            --ps-accent-light: #a5b4fc;
            --ps-accent-subtle: #eef2ff;
            --ps-accent-glow: rgba(99,102,241,0.25);
            --ps-success: #10b981;
            --ps-success-rgb: 16,185,129;
            --ps-success-subtle: #ecfdf5;
            --ps-warning: #f59e0b;
            --ps-warning-rgb: 245,158,11;
            --ps-warning-subtle: #fffbeb;
            --ps-danger: #ef4444;
            --ps-danger-rgb: 239,68,68;
            --ps-danger-subtle: #fef2f2;
            --ps-orange: #f97316;
            --ps-purple: #8b5cf6;
            --ps-cyan: #06b6d4;

            /* Surface system */
            --ps-body-bg: #f8fafc;
            --ps-card-bg: rgba(255,255,255,0.82);
            --ps-card-bg-solid: #ffffff;
            --ps-card-border: rgba(255,255,255,0.5);
            --ps-border: #e2e8f0;
            --ps-border-subtle: #f1f5f9;

            /* Typography */
            --ps-muted: #64748b;
            --ps-text: #0f172a;
            --ps-text-secondary: #475569;
            --ps-text-tertiary: #94a3b8;

            /* Radius scale */
            --ps-radius-sm: 0.5rem;
            --ps-radius: 0.75rem;
            --ps-radius-md: 1rem;
            --ps-radius-lg: 1.25rem;
            --ps-radius-xl: 1.5rem;
            --ps-radius-2xl: 2rem;

            /* Shadows */
            --ps-shadow-xs: 0 1px 2px rgba(0,0,0,0.04);
            --ps-shadow-sm: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
            --ps-shadow-md: 0 4px 12px -2px rgba(0,0,0,0.08), 0 2px 4px -1px rgba(0,0,0,0.04);
            --ps-shadow-lg: 0 10px 30px -5px rgba(0,0,0,0.1), 0 4px 8px rgba(0,0,0,0.04);
            --ps-shadow-xl: 0 20px 50px -12px rgba(0,0,0,0.18);
            --ps-shadow-glow: 0 0 40px -10px var(--ps-accent-glow);
            --ps-shadow-glass: inset 0 1px 0 0 rgba(255,255,255,0.6), 0 4px 16px -4px rgba(0,0,0,0.08);

            /* Transitions */
            --ps-ease-out: cubic-bezier(0.16, 1, 0.3, 1);
            --ps-ease-spring: cubic-bezier(0.34, 1.56, 0.64, 1);
            --ps-transition-fast: all 0.2s var(--ps-ease-out);
            --ps-transition: all 0.35s var(--ps-ease-out);
            --ps-transition-slow: all 0.5s var(--ps-ease-out);

            /* Z-index */
            --ps-z-bg: -1;
            --ps-z-content: 1;
            --ps-z-sticky: 1000;
            --ps-z-overlay: 1050;
        }

        /* ================================================================
           RESET & BASE
           ================================================================ */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html {
            scroll-behavior: smooth;
            -webkit-text-size-adjust: 100%;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
            background: var(--ps-body-bg);
            color: var(--ps-text);
            min-height: 100vh;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            overflow-x: hidden;
        }

        /* ================================================================
           AMBIENT BACKGROUND – Gradient Mesh
           ================================================================ */
        .ps-bg-ambient {
            position: fixed;
            inset: 0;
            z-index: var(--ps-z-bg);
            overflow: hidden;
            background:
                radial-gradient(ellipse 80% 60% at 10% 20%, rgba(99,102,241,0.08) 0%, transparent 60%),
                radial-gradient(ellipse 60% 80% at 90% 80%, rgba(139,92,246,0.07) 0%, transparent 60%),
                radial-gradient(ellipse 50% 50% at 50% 50%, rgba(6,182,212,0.04) 0%, transparent 60%),
                linear-gradient(170deg, #0f172a 0%, #1e1b4b 35%, #0f172a 65%, #020617 100%);
        }

        .ps-bg-ambient .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(100px);
            will-change: transform;
        }
        .ps-bg-ambient .orb-1 {
            width: 600px; height: 600px;
            background: radial-gradient(circle, var(--ps-accent) 0%, transparent 70%);
            top: -200px; right: -100px;
            opacity: 0.15;
            animation: orbDrift 30s ease-in-out infinite;
        }
        .ps-bg-ambient .orb-2 {
            width: 450px; height: 450px;
            background: radial-gradient(circle, var(--ps-purple) 0%, transparent 70%);
            bottom: -150px; left: -80px;
            opacity: 0.12;
            animation: orbDrift 35s ease-in-out infinite reverse;
            animation-delay: -15s;
        }
        .ps-bg-ambient .orb-3 {
            width: 300px; height: 300px;
            background: radial-gradient(circle, var(--ps-cyan) 0%, transparent 70%);
            top: 40%; left: 40%;
            opacity: 0.06;
            animation: orbDrift 25s ease-in-out infinite;
            animation-delay: -8s;
        }

        /* Subtle noise overlay */
        .ps-bg-ambient::after {
            content: '';
            position: absolute;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.03'/%3E%3C/svg%3E");
            opacity: 0.4;
            pointer-events: none;
        }

        @keyframes orbDrift {
            0%, 100% { transform: translate(0, 0) scale(1) rotate(0deg); }
            25% { transform: translate(30px, -40px) scale(1.05) rotate(2deg); }
            50% { transform: translate(-15px, 20px) scale(0.97) rotate(-1deg); }
            75% { transform: translate(20px, 30px) scale(1.02) rotate(1deg); }
        }

        /* ================================================================
           SCROLL REVEAL ANIMATIONS
           ================================================================ */
        .reveal {
            opacity: 0;
            transform: translateY(24px);
            transition: opacity 0.6s var(--ps-ease-out), transform 0.6s var(--ps-ease-out);
        }
        .reveal.visible {
            opacity: 1;
            transform: translateY(0);
        }
        .reveal-delay-1 { transition-delay: 0.08s; }
        .reveal-delay-2 { transition-delay: 0.16s; }
        .reveal-delay-3 { transition-delay: 0.24s; }
        .reveal-delay-4 { transition-delay: 0.32s; }

        /* ================================================================
           NAVBAR – Frosted Glass
           ================================================================ */
        .ps-navbar {
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(24px) saturate(1.8);
            -webkit-backdrop-filter: blur(24px) saturate(1.8);
            border-bottom: 1px solid rgba(255,255,255,0.06);
            padding: 0.6rem 0;
            position: sticky;
            top: 0;
            z-index: var(--ps-z-sticky);
            transition: background 0.3s, box-shadow 0.3s;
        }
        .ps-navbar.scrolled {
            background: rgba(15, 23, 42, 0.92);
            box-shadow: 0 4px 30px rgba(0,0,0,0.3);
        }

        .ps-navbar .navbar-brand {
            display: flex;
            align-items: center;
            gap: 0.7rem;
            font-weight: 800;
            font-size: 1.2rem;
            color: #ffffff !important;
            letter-spacing: -0.025em;
            text-decoration: none;
            transition: opacity 0.2s;
        }
        .ps-navbar .navbar-brand:hover { opacity: 0.85; }

        .ps-brand-icon {
            width: 36px; height: 36px;
            background: linear-gradient(135deg, var(--ps-accent) 0%, var(--ps-purple) 100%);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            color: #fff;
            font-size: 1rem;
            box-shadow: 0 4px 16px rgba(99,102,241,0.45);
            position: relative;
            overflow: hidden;
        }
        .ps-brand-icon::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.2) 0%, transparent 50%);
            border-radius: inherit;
        }

        .ps-brand-sub {
            font-size: 0.65rem;
            font-weight: 500;
            color: rgba(255,255,255,0.4);
            letter-spacing: 0.02em;
        }

        .ps-nav-right {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .ps-nav-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.3rem 0.75rem;
            border-radius: 50rem;
            font-size: 0.68rem;
            font-weight: 600;
            color: var(--ps-accent-light);
            background: rgba(99,102,241,0.1);
            border: 1px solid rgba(99,102,241,0.18);
            white-space: nowrap;
        }

        .ps-nav-meta {
            font-size: 0.68rem;
            color: rgba(255,255,255,0.35);
            white-space: nowrap;
        }

        /* ================================================================
           MAIN LAYOUT
           ================================================================ */
        .ps-main {
            width: min(80vw, 1200px);
            margin: 0 auto;
            padding: 2.5rem 1rem 4rem;
        }

        /* ================================================================
           HERO CARD – Glass Panel
           ================================================================ */
        .ps-hero-card {
            background: var(--ps-card-bg);
            backdrop-filter: blur(40px) saturate(1.6);
            -webkit-backdrop-filter: blur(40px) saturate(1.6);
            border: 1px solid var(--ps-card-border);
            border-radius: var(--ps-radius-2xl);
            box-shadow: var(--ps-shadow-glass), var(--ps-shadow-xl);
            padding: 2.5rem 2.75rem;
            position: relative;
            overflow: hidden;
            animation: heroEnter 0.8s var(--ps-ease-out) both;
        }
        /* Top accent line */
        .ps-hero-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 2px;
            background: linear-gradient(90deg,
                transparent 0%,
                var(--ps-accent) 20%,
                var(--ps-purple) 50%,
                var(--ps-cyan) 80%,
                transparent 100%
            );
            opacity: 0.7;
        }
        /* Subtle inner glow */
        .ps-hero-card::after {
            content: '';
            position: absolute;
            top: 0; left: 50%;
            transform: translateX(-50%);
            width: 60%;
            height: 300px;
            background: radial-gradient(ellipse, rgba(99,102,241,0.03) 0%, transparent 70%);
            pointer-events: none;
        }

        @keyframes heroEnter {
            from {
                opacity: 0;
                transform: translateY(50px) scale(0.97);
                filter: blur(4px);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
                filter: blur(0);
            }
        }

        /* ================================================================
           SECTION HEADERS
           ================================================================ */
        .ps-section {
            margin-top: 2rem;
            margin-bottom: 0;
        }

        .ps-section-header {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            margin-bottom: 1.1rem;
        }

        .ps-section-icon {
            width: 34px; height: 34px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.85rem;
            flex-shrink: 0;
            transition: transform 0.3s var(--ps-ease-spring);
        }
        .ps-section-header:hover .ps-section-icon {
            transform: scale(1.1) rotate(-3deg);
        }

        .ps-section-icon.accent  { background: var(--ps-accent-subtle); color: var(--ps-accent); }
        .ps-section-icon.success { background: var(--ps-success-subtle); color: var(--ps-success); }
        .ps-section-icon.warning { background: var(--ps-warning-subtle); color: var(--ps-warning); }
        .ps-section-icon.danger  { background: var(--ps-danger-subtle);  color: var(--ps-danger); }
        .ps-section-icon.purple  { background: #f5f3ff; color: var(--ps-purple); }
        .ps-section-icon.orange  { background: #fff7ed; color: var(--ps-orange); }

        .ps-section-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--ps-text);
            margin: 0;
            letter-spacing: -0.01em;
        }

        /* ================================================================
           STAT CARDS – Glass Cards Grid
           ================================================================ */
        .ps-stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.75rem;
        }

        .ps-stat-card {
            background: var(--ps-card-bg-solid);
            border: 1px solid var(--ps-border);
            border-radius: var(--ps-radius-lg);
            padding: 1.2rem 1.3rem;
            transition: var(--ps-transition);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        /* Hover accent bar */
        .ps-stat-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--ps-accent), var(--ps-purple));
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.4s var(--ps-ease-out);
        }
        /* Hover glow */
        .ps-stat-card::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            box-shadow: var(--ps-shadow-glow);
            opacity: 0;
            transition: opacity 0.4s;
            pointer-events: none;
        }
        .ps-stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--ps-shadow-lg);
            border-color: var(--ps-accent-light);
        }
        .ps-stat-card:hover::before { transform: scaleX(1); }
        .ps-stat-card:hover::after { opacity: 1; }

        .ps-stat-icon {
            width: 30px; height: 30px;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.8rem;
            margin-bottom: 0.65rem;
            transition: transform 0.3s var(--ps-ease-spring);
        }
        .ps-stat-card:hover .ps-stat-icon { transform: scale(1.12) rotate(-5deg); }

        .ps-stat-label {
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--ps-muted);
            margin-bottom: 0.5rem;
        }

        .ps-stat-value {
            font-size: 1.05rem;
            font-weight: 800;
            color: var(--ps-text);
            line-height: 1.3;
            word-break: break-all;
            letter-spacing: -0.02em;
        }

        .ps-stat-detail {
            font-size: 0.7rem;
            color: var(--ps-text-secondary);
            margin-top: auto;
            padding-top: 0.55rem;
            line-height: 1.65;
        }

        /* Tags */
        .ps-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.2rem;
            padding: 0.12rem 0.5rem;
            border-radius: 50rem;
            font-size: 0.58rem;
            font-weight: 700;
            border: 1px solid;
            transition: var(--ps-transition-fast);
        }
        .ps-tag:hover { transform: scale(1.05); }
        .ps-tag.pdf    { background: #fef2f2; color: #dc2626; border-color: #fecaca; }
        .ps-tag.docx   { background: #eff6ff; color: #2563eb; border-color: #bfdbfe; }
        .ps-tag.img    { background: #f5f3ff; color: #7c3aed; border-color: #ddd6fe; }
        .ps-tag.default{ background: #f3f4f6; color: #4b5563; border-color: var(--ps-border); }

        .ps-engine-pill {
            display: inline-block;
            padding: 0.08rem 0.4rem;
            border-radius: 50rem;
            font-size: 0.58rem;
            font-weight: 700;
            background: #f3f4f6;
            color: var(--ps-text);
            border: 1px solid var(--ps-border);
            transition: var(--ps-transition-fast);
        }
        .ps-engine-pill:hover { background: var(--ps-accent-subtle); border-color: var(--ps-accent-light); color: var(--ps-accent); }

        /* ================================================================
           SCORE SECTION
           ================================================================ */
        .ps-score-card {
            background: linear-gradient(135deg, rgba(249,250,251,0.9), rgba(243,244,246,0.9));
            border: 1px solid var(--ps-border);
            border-radius: var(--ps-radius-xl);
            padding: 2.25rem;
            position: relative;
            overflow: hidden;
        }
        /* Decorative circles */
        .ps-score-card::before {
            content: '';
            position: absolute;
            width: 400px; height: 400px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(99,102,241,0.04) 0%, transparent 70%);
            top: -200px; right: -100px;
            pointer-events: none;
        }

        .ps-score-ring-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .ps-score-ring {
            position: relative;
            width: 180px;
            height: 180px;
        }
        .ps-score-ring svg {
            width: 100%; height: 100%;
            transform: rotate(-90deg);
            filter: drop-shadow(0 4px 12px rgba(0,0,0,0.06));
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
            transition: stroke-dashoffset 1.8s var(--ps-ease-out);
        }
        /* Glow ring behind */
        .ps-score-ring .glow {
            fill: none;
            stroke-width: 14;
            stroke-linecap: round;
            stroke-dasharray: 502.65;
            stroke-dashoffset: 502.65;
            opacity: 0.15;
            filter: blur(8px);
            transition: stroke-dashoffset 1.8s var(--ps-ease-out);
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
            font-size: 2.8rem;
            font-weight: 900;
            line-height: 1;
            letter-spacing: -0.04em;
        }
        .ps-score-unit {
            font-size: 0.72rem;
            font-weight: 600;
            color: var(--ps-muted);
            margin-top: 0.15rem;
        }
        .ps-score-caption {
            font-size: 0.72rem;
            font-weight: 600;
            color: var(--ps-text-secondary);
            text-align: center;
            margin-top: 0.85rem;
        }

        /* Summary boxes */
        .ps-summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.6rem;
            margin-bottom: 1rem;
        }

        .ps-summary-box {
            text-align: center;
            padding: 0.9rem 0.5rem;
            border-radius: var(--ps-radius-md);
            transition: transform 0.25s var(--ps-ease-spring), box-shadow 0.25s;
            border: 1px solid;
            cursor: default;
        }
        .ps-summary-box:hover {
            transform: translateY(-3px) scale(1.03);
            box-shadow: var(--ps-shadow-md);
        }
        .ps-summary-box.critical { background: var(--ps-danger-subtle); border-color: #fecaca; }
        .ps-summary-box.high     { background: #fff7ed; border-color: #fed7aa; }
        .ps-summary-box.medium   { background: var(--ps-warning-subtle); border-color: #fde68a; }
        .ps-summary-box.low      { background: #f1f5f9; border-color: var(--ps-border); }

        .ps-summary-count {
            font-size: 1.6rem;
            font-weight: 900;
            line-height: 1;
            letter-spacing: -0.03em;
            transition: transform 0.3s var(--ps-ease-spring);
        }
        .ps-summary-box:hover .ps-summary-count { transform: scale(1.08); }
        .ps-summary-box.critical .ps-summary-count { color: var(--ps-danger); }
        .ps-summary-box.high     .ps-summary-count { color: var(--ps-orange); }
        .ps-summary-box.medium   .ps-summary-count { color: var(--ps-warning); }
        .ps-summary-box.low      .ps-summary-count { color: var(--ps-muted); }

        .ps-summary-label {
            font-size: 0.62rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--ps-text-secondary);
            margin-top: 0.3rem;
        }

        /* Level badges */
        .ps-level-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.2rem;
            padding: 0.18rem 0.55rem;
            border-radius: 50rem;
            font-size: 0.6rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            transition: var(--ps-transition-fast);
        }
        .ps-level-badge.critical { background: var(--ps-danger); color: #fff; }
        .ps-level-badge.high     { background: var(--ps-orange); color: #fff; }
        .ps-level-badge.medium   { background: var(--ps-warning); color: #1e293b; }
        .ps-level-badge.low      { background: var(--ps-accent); color: #fff; }
        .ps-level-badge.none     { background: #94a3b8; color: #fff; }

        /* Plagiat alert banner */
        .ps-plagiat-alert {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.25rem;
            border-radius: var(--ps-radius);
            font-weight: 700;
            font-size: 0.8rem;
            margin-top: 0.85rem;
            transition: transform 0.25s var(--ps-ease-spring);
            animation: alertPulse 3s ease-in-out infinite;
        }
        .ps-plagiat-alert:hover { transform: scale(1.03); }

        .ps-plagiat-alert.danger {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: #fff;
            box-shadow: 0 4px 20px rgba(220,38,38,0.35), inset 0 1px 0 rgba(255,255,255,0.15);
        }
        .ps-plagiat-alert.success {
            background: linear-gradient(135deg, #10b981, #059669);
            color: #fff;
            box-shadow: 0 4px 20px rgba(16,185,129,0.3), inset 0 1px 0 rgba(255,255,255,0.15);
        }

        @keyframes alertPulse {
            0%, 100% { box-shadow: 0 4px 20px rgba(220,38,38,0.35); }
            50% { box-shadow: 0 4px 30px rgba(220,38,38,0.5); }
        }
        .ps-plagiat-alert.success {
            animation-name: alertPulseSuccess;
        }
        @keyframes alertPulseSuccess {
            0%, 100% { box-shadow: 0 4px 20px rgba(16,185,129,0.3); }
            50% { box-shadow: 0 4px 30px rgba(16,185,129,0.45); }
        }

        /* ================================================================
           TEXT MATCHES
           ================================================================ */
        .ps-matches-list {
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
            margin-bottom: 1.75rem;
        }

        .ps-match-card {
            background: var(--ps-card-bg-solid);
            border: 1px solid var(--ps-border);
            border-radius: var(--ps-radius-lg);
            overflow: hidden;
            transition: var(--ps-transition);
            position: relative;
        }
        .ps-match-card::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            box-shadow: var(--ps-shadow-glow);
            opacity: 0;
            transition: opacity 0.4s;
            pointer-events: none;
        }
        .ps-match-card:hover {
            border-color: var(--ps-accent-light);
            box-shadow: var(--ps-shadow-lg);
            transform: translateX(4px);
        }
        .ps-match-card:hover::after { opacity: 1; }

        .ps-match-bar {
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 4px;
            transition: width 0.3s var(--ps-ease-spring);
        }
        .ps-match-card:hover .ps-match-bar { width: 6px; }

        .ps-match-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
            padding: 1.1rem 1.3rem 0;
            position: relative;
            z-index: 1;
        }

        .ps-match-filename {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .ps-match-filename strong {
            font-size: 0.88rem;
            font-weight: 700;
            letter-spacing: -0.01em;
        }
        .ps-match-filename i {
            transition: transform 0.3s var(--ps-ease-spring);
        }
        .ps-match-card:hover .ps-match-filename i { transform: scale(1.15) rotate(-5deg); }

        .ps-match-score {
            font-size: 1.65rem;
            font-weight: 900;
            letter-spacing: -0.03em;
            line-height: 1;
        }

        .ps-match-progress {
            padding: 0.65rem 1.3rem;
            position: relative;
            z-index: 1;
        }
        .ps-match-progress .progress {
            height: 5px !important;
            border-radius: 50rem !important;
            background: var(--ps-border) !important;
            overflow: hidden;
        }
        .ps-match-progress .progress-bar {
            border-radius: 50rem !important;
            position: relative;
            overflow: hidden;
        }
        /* Animated shimmer on progress bar */
        .ps-match-progress .progress-bar::after {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            animation: progressShimmer 2.5s ease-in-out infinite;
        }
        @keyframes progressShimmer {
            0% { left: -100%; }
            100% { left: 200%; }
        }

        /* Suspicious sections */
        .ps-suspect {
            background: var(--ps-warning-subtle);
            border: 1px solid #fde68a;
            border-radius: var(--ps-radius-sm);
            padding: 0.5rem 0.7rem;
            margin-bottom: 0.35rem;
            font-size: 0.68rem;
            color: var(--ps-text-secondary);
            transition: var(--ps-transition-fast);
            border-left: 3px solid var(--ps-warning);
        }
        .ps-suspect:hover {
            background: #fef3c7;
            transform: translateX(4px);
        }

        /* Engine details */
        .ps-match-engine-details {
            padding: 0 1.3rem 1rem;
            position: relative;
            z-index: 1;
        }

        .ps-engine-toggle {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.7rem;
            font-weight: 600;
            color: var(--ps-muted);
            cursor: pointer;
            padding: 0.4rem 0;
            border: none;
            background: none;
            transition: color 0.2s;
        }
        .ps-engine-toggle:hover { color: var(--ps-accent); }
        .ps-engine-toggle i { transition: transform 0.4s var(--ps-ease-out); }
        .ps-engine-toggle.active i { transform: rotate(180deg); }

        .ps-engine-collapse {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.5s var(--ps-ease-out);
        }
        .ps-engine-collapse.open { max-height: 600px; }

        .ps-engine-table {
            width: 100%;
            font-size: 0.72rem;
            border-collapse: separate;
            border-spacing: 0;
        }
        .ps-engine-table th {
            font-weight: 700;
            color: var(--ps-muted);
            text-transform: uppercase;
            font-size: 0.6rem;
            letter-spacing: 0.08em;
            padding: 0.55rem 0.65rem;
            border-bottom: 2px solid var(--ps-border);
        }
        .ps-engine-table td {
            padding: 0.55rem 0.65rem;
            border-bottom: 1px solid var(--ps-border-subtle);
            vertical-align: middle;
            transition: background 0.2s;
        }
        .ps-engine-table tr:last-child td { border-bottom: none; }
        .ps-engine-table tbody tr {
            transition: background 0.2s;
        }
        .ps-engine-table tbody tr:hover td { background: var(--ps-accent-subtle); }

        .ps-mini-bar {
            width: 80px; height: 5px;
            background: var(--ps-border);
            border-radius: 50rem;
            overflow: hidden;
            display: inline-block;
            vertical-align: middle;
        }
        .ps-mini-bar .fill {
            height: 100%;
            border-radius: 50rem;
            background: linear-gradient(90deg, var(--ps-accent), var(--ps-purple));
            transition: width 1s var(--ps-ease-out);
        }

        .ps-match-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.4rem 1.3rem 0.9rem;
            font-size: 0.65rem;
            color: var(--ps-text-tertiary);
            position: relative;
            z-index: 1;
        }

        /* ================================================================
           IMAGE ANALYSIS
           ================================================================ */
        .ps-image-card {
            background: linear-gradient(135deg, #faf5ff 0%, #f8fafc 100%);
            border: 1px solid #e9d5ff;
            border-radius: var(--ps-radius-lg);
            padding: 1.2rem 1.35rem;
            margin-bottom: 0.85rem;
            transition: var(--ps-transition);
        }
        .ps-image-card:last-child { margin-bottom: 0; }
        .ps-image-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--ps-shadow-md);
            border-color: var(--ps-purple);
        }

        .ps-img-icon {
            width: 42px; height: 42px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--ps-purple) 0%, #a78bfa 100%);
            display: flex; align-items: center; justify-content: center;
            color: #fff;
            font-size: 1rem;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(139,92,246,0.3);
            position: relative;
            overflow: hidden;
        }
        .ps-img-icon::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.2) 0%, transparent 50%);
            border-radius: inherit;
        }

        .ps-img-score-badge {
            padding: 0.3rem 0.8rem;
            border-radius: 50rem;
            font-weight: 800;
            font-size: 0.78rem;
            color: #fff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transition: transform 0.3s var(--ps-ease-spring);
        }
        .ps-image-card:hover .ps-img-score-badge { transform: scale(1.08); }

        /* ================================================================
           NO MATCHES
           ================================================================ */
        .ps-no-matches {
            text-align: center;
            padding: 3rem 1.5rem;
            background: linear-gradient(135deg, var(--ps-success-subtle) 0%, #f0fdf4 100%);
            border: 1px solid #a7f3d0;
            border-radius: var(--ps-radius-lg);
            position: relative;
            overflow: hidden;
        }
        .ps-no-matches::before {
            content: '';
            position: absolute;
            width: 200px; height: 200px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(16,185,129,0.08) 0%, transparent 70%);
            top: -60px; right: -60px;
        }

        .ps-no-matches-icon {
            width: 68px; height: 68px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--ps-success) 0%, #059669 100%);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1rem;
            color: #fff;
            font-size: 1.8rem;
            box-shadow: 0 8px 24px rgba(16,185,129,0.35);
            position: relative;
            animation: successBounce 2s var(--ps-ease-spring) infinite;
        }
        @keyframes successBounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }

        .ps-no-matches h4 {
            font-weight: 700;
            color: #065f46;
            font-size: 1.05rem;
            margin-bottom: 0.3rem;
        }
        .ps-no-matches p {
            color: var(--ps-muted);
            font-size: 0.82rem;
            margin: 0;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }

        /* ================================================================
           TEXT INFO BOX
           ================================================================ */
        .ps-text-info-box {
            margin-top: 1rem;
            padding: 0.9rem 1.1rem;
            background: #fff;
            border-radius: var(--ps-radius-md);
            border: 1px solid var(--ps-border);
            font-size: 0.73rem;
            box-shadow: var(--ps-shadow-xs);
            transition: var(--ps-transition-fast);
        }
        .ps-text-info-box:hover {
            border-color: var(--ps-accent-light);
            box-shadow: var(--ps-shadow-sm);
        }
        .ps-text-info-box strong { font-weight: 700; }

        /* ================================================================
           CONTENT CARD
           ================================================================ */
        .ps-content-card {
            background: var(--ps-card-bg-solid);
            border: 1px solid var(--ps-border);
            border-radius: var(--ps-radius-lg);
            padding: 1rem 1.25rem;
            box-shadow: var(--ps-shadow-xs);
        }

        /* ================================================================
           RAW JSON
           ================================================================ */
        .ps-raw-json-toggle {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.72rem;
            font-weight: 600;
            color: var(--ps-muted);
            cursor: pointer;
            border: none;
            background: none;
            padding: 0.4rem 0;
            transition: color 0.2s;
        }
        .ps-raw-json-toggle:hover { color: var(--ps-accent); }
        .ps-raw-json-toggle i { transition: transform 0.4s var(--ps-ease-out); }
        .ps-raw-json-toggle.active i { transform: rotate(90deg); }

        .ps-raw-json-wrap {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.6s var(--ps-ease-out);
        }
        .ps-raw-json-wrap.open { max-height: 2500px; }

        .ps-raw-json-pre {
            background: linear-gradient(135deg, #0f172a, #1e293b);
            color: #a5f3fc;
            padding: 1.35rem;
            border-radius: var(--ps-radius-md);
            font-size: 0.68rem;
            font-family: 'Fira Code', 'Cascadia Code', 'JetBrains Mono', monospace;
            overflow-x: auto;
            max-height: 480px;
            overflow-y: auto;
            line-height: 1.75;
            margin-top: 0.6rem;
            border: 1px solid rgba(99,102,241,0.15);
            box-shadow: inset 0 2px 8px rgba(0,0,0,0.2);
        }

        /* ================================================================
           BUTTONS
           ================================================================ */
        .ps-btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.7rem 1.5rem;
            border-radius: 50rem;
            font-weight: 650;
            font-size: 0.82rem;
            border: none;
            background: var(--ps-primary);
            color: #fff;
            text-decoration: none;
            transition: var(--ps-transition);
            box-shadow: 0 4px 16px rgba(var(--ps-primary-rgb), 0.3);
            position: relative;
            overflow: hidden;
        }
        .ps-btn-primary::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, transparent 50%);
            border-radius: inherit;
            opacity: 0;
            transition: opacity 0.3s;
        }
        .ps-btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 28px rgba(var(--ps-primary-rgb), 0.45);
            color: #fff;
        }
        .ps-btn-primary:hover::before { opacity: 1; }
        .ps-btn-primary:active { transform: translateY(-1px); }

        .ps-btn-outline {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.6rem 1.2rem;
            border-radius: 50rem;
            font-weight: 600;
            font-size: 0.76rem;
            border: 1px solid var(--ps-border);
            background: rgba(255,255,255,0.8);
            color: var(--ps-text-secondary);
            text-decoration: none;
            transition: var(--ps-transition);
            cursor: pointer;
            backdrop-filter: blur(8px);
        }
        .ps-btn-outline:hover {
            background: #fff;
            border-color: var(--ps-accent);
            color: var(--ps-accent);
            transform: translateY(-2px);
            box-shadow: var(--ps-shadow-sm);
        }
        .ps-btn-outline:active { transform: translateY(0); }

        /* ================================================================
           ACTION BAR
           ================================================================ */
        .ps-action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.85rem;
            margin-top: 1.75rem;
            padding-top: 1.25rem;
            border-top: 1px solid var(--ps-border);
        }
        .ps-action-buttons {
            display: flex;
            gap: 0.6rem;
            flex-wrap: wrap;
        }

        /* ================================================================
           FOOTER
           ================================================================ */
        .ps-report-footer {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.1rem;
            border-top: 1px solid var(--ps-border-subtle);
            font-size: 0.68rem;
            color: var(--ps-text-tertiary);
            letter-spacing: 0.01em;
        }
        .ps-report-footer i { margin-right: 0.25rem; }

        /* ================================================================
           TOAST NOTIFICATION
           ================================================================ */
        .ps-toast {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: var(--ps-primary);
            color: #fff;
            padding: 0.75rem 1.25rem;
            border-radius: var(--ps-radius);
            font-size: 0.78rem;
            font-weight: 600;
            box-shadow: var(--ps-shadow-xl);
            transform: translateY(120%);
            opacity: 0;
            transition: all 0.4s var(--ps-ease-spring);
            z-index: var(--ps-z-overlay);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .ps-toast.show {
            transform: translateY(0);
            opacity: 1;
        }

        /* ================================================================
           SCROLL TO TOP
           ================================================================ */
        .ps-scroll-top {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 44px; height: 44px;
            border-radius: 50%;
            background: var(--ps-accent);
            color: #fff;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            cursor: pointer;
            box-shadow: 0 4px 16px rgba(99,102,241,0.4);
            opacity: 0;
            transform: translateY(20px);
            transition: var(--ps-transition);
            z-index: 999;
        }
        .ps-scroll-top.visible {
            opacity: 1;
            transform: translateY(0);
        }
        .ps-scroll-top:hover {
            background: var(--ps-accent-hover);
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(99,102,241,0.5);
        }

        /* ================================================================
           RESPONSIVE
           ================================================================ */
        @media (max-width: 1200px) {
            .ps-main { width: 90vw; }
        }

        @media (max-width: 1023px) {
            .ps-stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .ps-score-card > .row > [class*="col-"] {
                text-align: center;
            }
            .ps-score-ring-wrap { margin-bottom: 1.5rem; }
            .ps-summary-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        @media (max-width: 767px) {
            .ps-main {
                width: 95vw;
                padding: 1.25rem 0.5rem 3rem;
            }
            .ps-hero-card {
                padding: 1.5rem 1.15rem;
                border-radius: var(--ps-radius-xl);
            }
            .ps-stats-grid {
                grid-template-columns: 1fr 1fr;
                gap: 0.75rem;
            }
            .ps-summary-grid {
                grid-template-columns: 1fr 1fr;
            }
            .ps-score-card { padding: 1.5rem; }
            .ps-score-ring { width: 150px; height: 150px; }
            .ps-score-number { font-size: 2.2rem; }
        }

        @media (max-width: 575px) {
            .ps-stats-grid {
                grid-template-columns: 1fr;
            }
            .ps-summary-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .ps-stat-value { font-size: 0.95rem; }
            .ps-match-score { font-size: 1.3rem; }
            .ps-score-ring { width: 130px; height: 130px; }
            .ps-score-number { font-size: 1.9rem; }
            .ps-action-bar {
                flex-direction: column;
                align-items: stretch;
            }
            .ps-action-bar .ps-btn-primary,
            .ps-action-buttons {
                width: 100%;
                justify-content: center;
            }
            .ps-nav-badge { display: none; }
        }

        /* ================================================================
           PRINT
           ================================================================ */
        @media print {
            body { background: #fff !important; }
            .ps-bg-ambient { display: none; }
            .ps-navbar { display: none; }
            .ps-hero-card {
                box-shadow: none;
                backdrop-filter: none;
                animation: none;
                border: 1px solid #ddd;
                border-radius: 0.5rem;
                padding: 1.5rem;
            }
            .ps-hero-card::before,
            .ps-hero-card::after { display: none; }
            .ps-btn-primary,
            .ps-btn-outline,
            .ps-engine-toggle,
            .ps-raw-json-toggle,
            .ps-scroll-top,
            .ps-toast { display: none !important; }
            .ps-match-card:hover,
            .ps-stat-card:hover { transform: none; box-shadow: none; }
            .ps-match-card:hover .ps-match-bar { width: 4px; }
            .ps-raw-json-wrap { display: none; }
            .reveal { opacity: 1; transform: none; }
        }

        /* ================================================================
           ACCESSIBILITY
           ================================================================ */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
            .reveal { opacity: 1; transform: none; }
        }

        /* Focus styles */
        .ps-btn-primary:focus-visible,
        .ps-btn-outline:focus-visible,
        .ps-engine-toggle:focus-visible,
        .ps-raw-json-toggle:focus-visible,
        .ps-scroll-top:focus-visible {
            outline: 2px solid var(--ps-accent);
            outline-offset: 2px;
        }

        /* Selection */
        ::selection {
            background: rgba(99,102,241,0.2);
            color: var(--ps-text);
        }
    </style>
</head>
<body>

{{-- ===== AMBIENT BACKGROUND ===== --}}
<div class="ps-bg-ambient" aria-hidden="true">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>
</div>

{{-- ===== STICKY NAVBAR ===== --}}
<nav class="ps-navbar" role="navigation" aria-label="Navigation principale">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <a class="navbar-brand" href="{{ route('upload.index') }}">
                <div class="ps-brand-icon"><i class="bi bi-shield-shaded"></i></div>
                <div>
                    <div>PlagioScan</div>
                    <div class="ps-brand-sub">Moteur de d&eacute;tection anti-plagiat</div>
                </div>
            </a>
            <div class="ps-nav-right">
                <span class="ps-nav-badge">
                    <i class="bi bi-file-earmark-bar-graph"></i> Rapport d'analyse
                </span>
                <span class="ps-nav-meta d-none d-sm-inline">
                    <i class="bi bi-clock me-1"></i>{{ now()->format('d/m/Y  H:i') }}
                </span>
            </div>
        </div>
    </div>
</nav>

{{-- ===== MAIN CONTENT ===== --}}
<main class="ps-main">
    <div class="ps-hero-card">

        {{-- ===== FILE INFO STATS ===== --}}
        <section class="ps-section reveal" aria-label="Informations du fichier">
            <div class="ps-section-header">
                <div class="ps-section-icon accent"><i class="bi bi-info-circle"></i></div>
                <h2 class="ps-section-title">Informations du fichier</h2>
            </div>
        </section>

        <div class="ps-stats-grid mb-4">
            {{-- Fichier --}}
            <div class="ps-stat-card reveal reveal-delay-1">
                <div class="ps-stat-icon accent"><i class="bi bi-file-earmark-text"></i></div>
                <div class="ps-stat-label">Fichier analys&eacute;</div>
                <div class="ps-stat-value">{{ $filename }}</div>
                <div class="ps-stat-detail">
                    Type : {{ $file_type === 'code' ? 'Code source' : ($file_type === 'image' ? 'Image' : 'Texte') }}
                    <br>Taille : {{ number_format($content_length / 1024, 2) }} Ko
                </div>
            </div>

            {{-- Comparaisons --}}
            <div class="ps-stat-card reveal reveal-delay-2">
                <div class="ps-stat-icon success"><i class="bi bi-database"></i></div>
                <div class="ps-stat-label">Documents compar&eacute;s</div>
                <div class="ps-stat-value" data-counter="{{ $num_comparisons }}">{{ $num_comparisons }}</div>
                <div class="ps-stat-detail">Base de r&eacute;f&eacute;rence statique</div>
            </div>

            {{-- Moteurs --}}
            <div class="ps-stat-card reveal reveal-delay-3">
                <div class="ps-stat-icon purple"><i class="bi bi-cpu"></i></div>
                <div class="ps-stat-label">Moteurs actifs</div>
                <div class="ps-stat-value" data-counter="{{ count($engines_used) }}">{{ count($engines_used) }}</div>
                <div class="ps-stat-detail">
                    @foreach($engines_used as $engine => $active)
                        <span class="ps-engine-pill">{{ strtoupper($engine) }}</span>
                    @endforeach
                    @if(empty($engines_used))
                        <span class="ps-tag default">Aucun (base vide)</span>
                    @endif
                </div>
            </div>

            {{-- Extraction --}}
            <div class="ps-stat-card reveal reveal-delay-4">
                <div class="ps-stat-icon orange"><i class="bi bi-extract"></i></div>
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

        {{-- ===== IMAGE ANALYSIS ===== --}}
        @if(is_array($image_analysis) && isset($image_analysis['analyzed']) && $image_analysis['analyzed'])
            <section class="ps-section reveal" aria-label="Analyse d'images">
                <div class="ps-section-header">
                    <div class="ps-section-icon purple"><i class="bi bi-image"></i></div>
                    <h2 class="ps-section-title">Analyse d'images</h2>
                </div>
            </section>
            <div class="mb-4 reveal">
                @if(count($image_matches) > 0)
                    @foreach($image_matches as $imgMatch)
                        @php
                            $imgConf = ($imgMatch['confidence'] ?? 0) * 100;
                            $imgColor = $imgConf >= 80 ? '#dc2626' : ($imgConf >= 60 ? '#f97316' : '#3b82f6');
                            $imgLevelLabels = ['critical' => 'Critique', 'high' => '&Eacute;lev&eacute;', 'medium' => 'Moyen', 'low' => 'Faible'];
                        @endphp
                        <div class="ps-image-card">
                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                <div class="ps-img-icon"><i class="bi bi-images"></i></div>
                                <div class="flex-grow-1" style="min-width: 200px;">
                                    <strong style="font-size: 0.82rem;">Image #{{ ($imgMatch['new_image_index'] ?? 0) + 1 }}</strong>
                                    <br>
                                    <small class="text-muted" style="font-size: 0.68rem;">
                                        vs {{ $imgMatch['matched_filename'] ?? html_entity_decode('R&eacute;f&eacute;rence') }}
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
                        <span class="text-muted" style="font-size: 0.78rem;">
                            <i class="bi bi-check-circle me-1" style="color: var(--ps-success);"></i>
                            Aucune similarit&eacute; d'image d&eacute;tect&eacute;e
                            ({{ $image_analysis['images_checked'] ?? 0 }} image(s) analys&eacute;e(s) contre {{ $image_analysis['images_in_database'] ?? 0 }} en base).
                        </span>
                    </div>
                @endif
            </div>
        @endif

        {{-- ===== SCORE PRINCIPAL ===== --}}
        <section class="ps-section reveal" aria-label="Score de similarit&eacute;">
            <div class="ps-section-header">
                <div class="ps-section-icon accent"><i class="bi bi-speedometer2"></i></div>
                <h2 class="ps-section-title">Score de similarit&eacute;</h2>
            </div>
        </section>

        <div class="ps-score-card mb-4 reveal">
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
                                <circle class="glow" cx="90" cy="90" r="80"
                                        style="stroke: {{ $scoreColor }}; stroke-dashoffset: {{ $offset }};"
                                        data-target-glow="{{ $offset }}" />
                                <circle class="arc" cx="90" cy="90" r="80"
                                        style="stroke: {{ $scoreColor }}; stroke-dashoffset: 502.65;"
                                        data-target="{{ $offset }}" />
                            </svg>
                            <div class="ps-score-center">
                                <div class="ps-score-number" style="color: {{ $scoreColor }};"
                                     data-count-to="{{ round($overall_score * 100) }}">0</div>
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
                    <div style="font-size: 0.8rem; font-weight: 600; color: var(--ps-text-secondary); margin-bottom: 0.9rem;">
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
                    <div class="ps-summary-grid mb-3">
                        <div class="ps-summary-box critical">
                            <div class="ps-summary-count" data-counter="{{ $summaryLevels['critical'] }}">0</div>
                            <div class="ps-summary-label">Critique</div>
                        </div>
                        <div class="ps-summary-box high">
                            <div class="ps-summary-count" data-counter="{{ $summaryLevels['high'] }}">0</div>
                            <div class="ps-summary-label">&Eacute;lev&eacute;</div>
                        </div>
                        <div class="ps-summary-box medium">
                            <div class="ps-summary-count" data-counter="{{ $summaryLevels['medium'] }}">0</div>
                            <div class="ps-summary-label">Moyen</div>
                        </div>
                        <div class="ps-summary-box low">
                            <div class="ps-summary-count" data-counter="{{ $summaryLevels['low'] }}">0</div>
                            <div class="ps-summary-label">Faible</div>
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
        <section class="ps-section reveal" aria-label="Correspondances d&eacute;taill&eacute;es">
            <div class="ps-section-header">
                <div class="ps-section-icon warning"><i class="bi bi-files"></i></div>
                <h2 class="ps-section-title">Correspondances d&eacute;taill&eacute;es (texte)</h2>
            </div>
        </section>

        @if(count($text_matches) > 0)
            <div class="ps-matches-list mb-4">
                @foreach($text_matches as $index => $match)
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
                    <article class="ps-match-card reveal" style="transition-delay: {{ min($index * 0.06, 0.3) }}s;">
                        <div class="ps-match-bar" style="background: {{ $borderColor }};"></div>

                        <div class="ps-match-header">
                            <div class="ps-match-filename">
                                <i class="bi bi-file-text-fill" style="color: {{ $borderColor }}; font-size: 1.1rem;"></i>
                                <strong>{{ $match['filename'] ?? 'Document inconnu' }}</strong>
                                <span class="ps-level-badge {{ $level }}">{{ $levelLabels[$level] ?? ucfirst($level) }}</span>
                                @if(isset($match['file_type']))
                                    <span class="ps-engine-pill" style="font-size: 0.56rem;">{{ strtoupper($match['file_type']) }}</span>
                                @endif
                            </div>
                            <div class="ps-match-score" style="color: {{ $borderColor }};">
                                <span data-count-to="{{ $scorePercent }}" data-decimals="1">0</span>%
                            </div>
                        </div>

                        {{-- Progress bar --}}
                        <div class="ps-match-progress">
                            <div class="progress" role="progressbar"
                                 aria-valuenow="{{ $scorePercent }}" aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar"
                                     data-width-target="{{ $scorePercent }}"
                                     style="width: 0%; background: {{ $borderColor }}; transition: width 1.2s var(--ps-ease-out);"></div>
                            </div>
                        </div>

                        {{-- Suspicious sections --}}
                        @if(isset($match['suspicious_sections']) && count($match['suspicious_sections']) > 0)
                            <div style="padding: 0 1.3rem;">
                                <div style="font-size: 0.68rem; font-weight: 700; color: var(--ps-warning); margin-bottom: 0.35rem;">
                                    <i class="bi bi-exclamation-diamond me-1"></i>
                                    {{ count($match['suspicious_sections']) }} section(s) suspecte(s)
                                </div>
                                @foreach(array_slice($match['suspicious_sections'], 0, 3) as $sus)
                                    <div class="ps-suspect">
                                        <span class="ps-level-badge medium" style="font-size: 0.56rem;">{{ ucfirst($sus['level'] ?? '?') }} {{ round(($sus['score'] ?? 0) * 100) }}%</span>
                                        <span style="margin-left: 0.4rem;">{{ Str::limit(strip_tags($sus['preview'] ?? ''), 120) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- Engine details toggle --}}
                        @if(isset($match['engines']) && count($match['engines']) > 0)
                            <div class="ps-match-engine-details">
                                <button class="ps-engine-toggle" onclick="toggleEngine(this)" aria-expanded="false">
                                    <i class="bi bi-chevron-down"></i> D&eacute;tails par moteur ({{ count($match['engines']) }})
                                </button>
                                <div class="ps-engine-collapse" role="region">
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
                                                            <div class="fill" style="width: 0%;" data-width-target="{{ min($contribPercent, 100) }}"></div>
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
                    </article>
                @endforeach
            </div>
        @else
            <div class="ps-no-matches mb-4 reveal">
                <div class="ps-no-matches-icon"><i class="bi bi-check-lg"></i></div>
                <h4>Aucune correspondance texte</h4>
                <p>Le document analys&eacute; ne pr&eacute;sente aucune similarit&eacute; textuelle avec la base de r&eacute;f&eacute;rence.</p>
            </div>
        @endif

        {{-- ===== RAW JSON (debug) ===== --}}
        @if(isset($raw_response) && $raw_response)
            <div class="ps-section reveal" style="margin-top: 1.5rem;">
                <button class="ps-raw-json-toggle" onclick="toggleRawJson(this)" aria-expanded="false">
                    <i class="bi bi-code-slash"></i> Voir la r&eacute;ponse JSON brute de l'API
                </button>
                <div class="ps-raw-json-wrap" role="region">
                    <pre class="ps-raw-json-pre"><code>{{ json_encode($raw_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                </div>
            </div>
        @endif

        {{-- ===== ACTION BAR ===== --}}
        <div class="ps-action-bar reveal">
            <a href="{{ route('upload.index') }}" class="ps-btn-primary">
                <i class="bi bi-arrow-left"></i> Nouvelle analyse
            </a>
            <div class="ps-action-buttons">
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
        <footer class="ps-report-footer">
            <i class="bi bi-shield-lock-fill"></i> Analyse confidentielle &bull; Algorithmes : TF-IDF, S&eacute;mantique BERT, Winnowing, LCS, pHash
        </footer>

    </div><!-- /.ps-hero-card -->
</main>

{{-- Scroll to top button --}}
<button class="ps-scroll-top" id="scrollTopBtn" onclick="window.scrollTo({top:0,behavior:'smooth'})" aria-label="Retour en haut">
    <i class="bi bi-chevron-up"></i>
</button>

{{-- Toast container --}}
<div class="ps-toast" id="toast" role="alert" aria-live="polite"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function() {
    'use strict';

    /* ================================================================
       UTILITY HELPERS
       ================================================================ */
    const $ = (sel, ctx = document) => ctx.querySelector(sel);
    const $$ = (sel, ctx = document) => [...ctx.querySelectorAll(sel)];

    /* ================================================================
       NAVBAR SCROLL EFFECT
       ================================================================ */
    const navbar = $('.ps-navbar');
    let lastScroll = 0;
    window.addEventListener('scroll', () => {
        const y = window.scrollY;
        navbar.classList.toggle('scrolled', y > 20);
        lastScroll = y;
    }, { passive: true });

    /* ================================================================
       SCROLL TO TOP BUTTON
       ================================================================ */
    const scrollTopBtn = $('#scrollTopBtn');
    window.addEventListener('scroll', () => {
        scrollTopBtn.classList.toggle('visible', window.scrollY > 400);
    }, { passive: true });

    /* ================================================================
       INTERSECTION OBSERVER – Scroll Reveal
       ================================================================ */
    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');

                // Trigger child animations for counters & progress bars
                const counters = $$('[data-count-to]', entry.target);
                counters.forEach(el => animateCounter(el));

                const progressBars = $$('[data-width-target]', entry.target);
                progressBars.forEach(el => {
                    setTimeout(() => {
                        el.style.width = el.dataset.widthTarget + '%';
                    }, 200);
                });

                // Trigger mini-bars inside engine collapses only if open
                const miniBars = $$('.ps-engine-collapse.open .ps-mini-bar .fill[data-width-target]', entry.target);
                miniBars.forEach(el => {
                    el.style.width = el.dataset.widthTarget + '%';
                });

                revealObserver.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.15,
        rootMargin: '0px 0px -40px 0px'
    });

    $$('.reveal').forEach(el => revealObserver.observe(el));

    /* ================================================================
       ANIMATED COUNTER
       ================================================================ */
    function animateCounter(el) {
        if (el._animated) return;
        el._animated = true;

        const target = parseFloat(el.dataset.countTo);
        const decimals = parseInt(el.dataset.decimals) || 0;
        const duration = 1400;
        const start = performance.now();

        function tick(now) {
            const elapsed = now - start;
            const progress = Math.min(elapsed / duration, 1);
            // Ease out cubic
            const eased = 1 - Math.pow(1 - progress, 3);
            const current = eased * target;

            el.textContent = decimals > 0
                ? current.toFixed(decimals)
                : Math.round(current);

            if (progress < 1) requestAnimationFrame(tick);
        }

        requestAnimationFrame(tick);
    }

    /* ================================================================
       SCORE ARC ANIMATION
       ================================================================ */
    const scoreArc = $('.arc');
    const scoreGlow = $('.glow');
    if (scoreArc) {
        const targetOffset = parseFloat(scoreArc.dataset.target);
        const glowTarget = scoreGlow ? parseFloat(scoreGlow.dataset.targetGlow) : targetOffset;

        const scoreObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    requestAnimationFrame(() => {
                        requestAnimationFrame(() => {
                            scoreArc.style.strokeDashoffset = targetOffset;
                            if (scoreGlow) scoreGlow.style.strokeDashoffset = glowTarget;
                        });
                    });
                    scoreObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.3 });

        scoreObserver.observe(scoreArc.closest('.ps-score-ring'));
    }

    /* ================================================================
       TOAST NOTIFICATION
       ================================================================ */
    window.showToast = function(message, duration = 3000) {
        const toast = $('#toast');
        toast.innerHTML = `<i class="bi bi-info-circle"></i> ${message}`;
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), duration);
    };

})();

/* ================================================================
   TOGGLE FUNCTIONS (global scope for inline onclick)
   ================================================================ */
function toggleEngine(btn) {
    const wrap = btn.nextElementSibling;
    const isOpen = wrap.classList.toggle('open');
    btn.classList.toggle('active');
    btn.setAttribute('aria-expanded', isOpen);

    // Animate mini-bars on open
    if (isOpen) {
        const bars = wrap.querySelectorAll('.ps-mini-bar .fill[data-width-target]');
        bars.forEach(bar => {
            bar.style.width = '0%';
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    bar.style.width = bar.dataset.widthTarget + '%';
                });
            });
        });
    }
}

function toggleRawJson(btn) {
    const wrap = btn.nextElementSibling;
    const isOpen = wrap.classList.toggle('open');
    btn.classList.toggle('active');
    btn.setAttribute('aria-expanded', isOpen);
}

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
    if (window.showToast) showToast('Fichier JSON t&eacute;l&eacute;charg&eacute;');
}
@endif
</script>
</body>
</html>
