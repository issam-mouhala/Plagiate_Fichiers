{{-- resources/views/plagiat/results.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PlagioScan – Rapport d'analyse</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <style>
        /* ===== VARIABLES & RESET ===== */
        :root {
            --primary: #0f172a;
            --primary-light: #1e293b;
            --accent: #3b82f6;
            --accent-light: #93c5fd;
            --success: #10b981;
            --success-bg: #ecfdf5;
            --warning: #f59e0b;
            --warning-bg: #fffbeb;
            --danger: #ef4444;
            --danger-bg: #fef2f2;
            --info: #06b6d4;
            --surface: #ffffff;
            --surface-hover: #f8fafc;
            --border: #e2e8f0;
            --muted: #64748b;
            --text: #0f172a;
            --text-secondary: #475569;
            --radius-xl: 1.25rem;
            --radius-2xl: 1.5rem;
            --radius-3xl: 2rem;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.04), 0 1px 2px rgba(0,0,0,0.06);
            --shadow-md: 0 4px 16px -2px rgba(0,0,0,0.08), 0 2px 8px rgba(0,0,0,0.04);
            --shadow-lg: 0 12px 40px -8px rgba(0,0,0,0.12), 0 4px 12px rgba(0,0,0,0.04);
            --shadow-xl: 0 24px 60px -12px rgba(0,0,0,0.18);
        }

        *, *::before, *::after { box-sizing: border-box; }

        body {
            background: #f1f5f9;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--text);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        /* ===== ANIMATED BACKGROUND ===== */
        .bg-mesh {
            position: fixed;
            inset: 0;
            z-index: -1;
            overflow: hidden;
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 40%, #0f172a 100%);
        }
        .bg-mesh::before,
        .bg-mesh::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            filter: blur(100px);
            opacity: 0.15;
            animation: float 20s ease-in-out infinite;
        }
        .bg-mesh::before {
            width: 600px; height: 600px;
            background: var(--accent);
            top: -200px; right: -100px;
        }
        .bg-mesh::after {
            width: 500px; height: 500px;
            background: #8b5cf6;
            bottom: -150px; left: -100px;
            animation-delay: -10s;
        }
        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -30px) scale(1.05); }
            66% { transform: translate(-20px, 20px) scale(0.95); }
        }

        /* ===== LAYOUT ===== */
        .main-container {
            max-width: 1120px;
            margin: 0 auto;
            padding: 2rem 1.25rem 4rem;
        }

        /* ===== GLASS CARD ===== */
        .glass-card {
            background: rgba(255,255,255,0.92);
            backdrop-filter: blur(24px) saturate(1.4);
            -webkit-backdrop-filter: blur(24px) saturate(1.4);
            border: 1px solid rgba(255,255,255,0.5);
            border-radius: var(--radius-3xl);
            box-shadow: var(--shadow-xl);
            padding: 2.5rem 3rem;
            animation: cardIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
        }
        @keyframes cardIn {
            from { opacity: 0; transform: translateY(30px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* ===== HEADER ===== */
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 1rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--border);
            margin-bottom: 2rem;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .brand-icon {
            width: 48px; height: 48px;
            background: linear-gradient(135deg, var(--accent), #8b5cf6);
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 1.4rem;
            box-shadow: 0 4px 16px rgba(59,130,246,0.35);
        }
        .brand-name {
            font-size: 1.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .report-meta {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 0.25rem;
            color: var(--muted);
            font-size: 0.8rem;
        }
        .report-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.3rem 0.9rem;
            border-radius: 2rem;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            background: linear-gradient(135deg, #eff6ff, #e0e7ff);
            color: var(--accent);
        }

        /* ===== SECTION TITLE ===== */
        .section-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .section-title i {
            color: var(--accent);
            font-size: 1.1rem;
        }

        /* ===== STAT CARDS GRID ===== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-xl);
            padding: 1.25rem 1.5rem;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
        }
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--accent), #8b5cf6);
            opacity: 0;
            transition: opacity 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
            border-color: var(--accent-light);
        }
        .stat-card:hover::before { opacity: 1; }
        .stat-icon {
            width: 40px; height: 40px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem;
            margin-bottom: 0.75rem;
        }
        .stat-icon.blue { background: #eff6ff; color: var(--accent); }
        .stat-icon.green { background: #ecfdf5; color: var(--success); }
        .stat-icon.purple { background: #f5f3ff; color: #8b5cf6; }
        .stat-icon.orange { background: #fff7ed; color: #f97316; }
        .stat-value {
            font-size: 1.3rem;
            font-weight: 800;
            color: var(--text);
            line-height: 1.2;
        }
        .stat-label {
            font-size: 0.78rem;
            color: var(--muted);
            font-weight: 500;
            margin-top: 0.15rem;
        }
        .stat-detail {
            font-size: 0.72rem;
            color: var(--text-secondary);
            margin-top: 0.5rem;
            line-height: 1.5;
        }

        /* ===== EXTRACTION INFO (PDF/Word) ===== */
        .extraction-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 0.6rem;
            margin-top: 0.75rem;
        }
        .extraction-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.25rem 0.65rem;
            border-radius: 1rem;
            font-size: 0.7rem;
            font-weight: 600;
            background: #f1f5f9;
            color: var(--text-secondary);
            border: 1px solid var(--border);
        }
        .extraction-tag i { font-size: 0.75rem; }
        .extraction-tag.pdf { background: #fef2f2; color: #dc2626; border-color: #fecaca; }
        .extraction-tag.docx { background: #eff6ff; color: #2563eb; border-color: #bfdbfe; }
        .extraction-tag.img { background: #f5f3ff; color: #7c3aed; border-color: #ddd6fe; }

        /* ===== SCORE SECTION ===== */
        .score-section {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 2rem;
            align-items: center;
            padding: 2rem;
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            border-radius: var(--radius-2xl);
            border: 1px solid var(--border);
            margin-bottom: 2rem;
        }
        @media (max-width: 768px) {
            .score-section { grid-template-columns: 1fr; text-align: center; }
            .glass-card { padding: 1.5rem; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 480px) {
            .stats-grid { grid-template-columns: 1fr; }
        }

        /* Animated Score Circle */
        .score-ring {
            position: relative;
            width: 180px; height: 180px;
            margin: 0 auto 1rem;
        }
        .score-ring svg {
            width: 100%; height: 100%;
            transform: rotate(-90deg);
        }
        .score-ring .track {
            fill: none;
            stroke: var(--border);
            stroke-width: 10;
        }
        .score-ring .progress-arc {
            fill: none;
            stroke-width: 10;
            stroke-linecap: round;
            stroke-dasharray: 502.65;
            stroke-dashoffset: 502.65;
            transition: stroke-dashoffset 1.5s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .score-ring .score-text {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .score-number {
            font-size: 2.75rem;
            font-weight: 800;
            line-height: 1;
        }
        .score-unit {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--muted);
            margin-top: 0.1rem;
        }
        .score-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-secondary);
            margin-top: 0.75rem;
            text-align: center;
        }

        /* Summary boxes */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.75rem;
        }
        @media (max-width: 576px) {
            .summary-grid { grid-template-columns: repeat(2, 1fr); }
        }
        .summary-box {
            text-align: center;
            padding: 1rem 0.5rem;
            border-radius: var(--radius-xl);
            transition: transform 0.2s;
        }
        .summary-box:hover { transform: scale(1.04); }
        .summary-box.critical { background: var(--danger-bg); border: 1px solid #fecaca; }
        .summary-box.high { background: #fff7ed; border: 1px solid #fed7aa; }
        .summary-box.medium { background: var(--warning-bg); border: 1px solid #fde68a; }
        .summary-box.low { background: #f1f5f9; border: 1px solid var(--border); }
        .summary-count {
            font-size: 1.75rem;
            font-weight: 800;
            line-height: 1;
        }
        .summary-box.critical .summary-count { color: var(--danger); }
        .summary-box.high .summary-count { color: #ea580c; }
        .summary-box.medium .summary-count { color: var(--warning); }
        .summary-box.low .summary-count { color: var(--muted); }
        .summary-label {
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-secondary);
            margin-top: 0.3rem;
        }

        /* Level badge */
        .level-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.25rem 0.7rem;
            border-radius: 1rem;
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .level-badge.critical { background: var(--danger); color: white; }
        .level-badge.high { background: #f97316; color: white; }
        .level-badge.medium { background: var(--warning); color: #1e293b; }
        .level-badge.low { background: var(--accent); color: white; }
        .level-badge.none { background: #94a3b8; color: white; }

        /* ===== PLAGIAT ALERT ===== */
        .alert-plagiat {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.2rem;
            border-radius: var(--radius-xl);
            font-weight: 700;
            font-size: 0.85rem;
            margin-top: 0.75rem;
            animation: pulse 2s ease-in-out infinite;
        }
        .alert-plagiat.danger {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: white;
            box-shadow: 0 4px 20px rgba(220,38,38,0.4);
        }
        .alert-plagiat.success {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            box-shadow: 0 4px 20px rgba(16,185,129,0.3);
        }
        @keyframes pulse {
            0%, 100% { box-shadow: 0 4px 20px rgba(0,0,0,0.2); }
            50% { box-shadow: 0 4px 30px rgba(0,0,0,0.35); }
        }

        /* ===== MATCHES ===== */
        .matches-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .match-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-xl);
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            animation: slideUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
        }
        .match-card:nth-child(1) { animation-delay: 0.1s; }
        .match-card:nth-child(2) { animation-delay: 0.15s; }
        .match-card:nth-child(3) { animation-delay: 0.2s; }
        .match-card:nth-child(4) { animation-delay: 0.25s; }
        .match-card:nth-child(5) { animation-delay: 0.3s; }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .match-card:hover {
            border-color: var(--accent-light);
            box-shadow: var(--shadow-lg);
            transform: translateX(4px);
        }
        .match-accent {
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 4px;
        }
        .match-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
            padding: 1.25rem 1.5rem 0;
            position: relative;
        }
        .match-filename {
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }
        .match-filename i {
            font-size: 1.2rem;
        }
        .match-filename strong {
            font-size: 0.95rem;
        }
        .match-score-big {
            font-size: 1.75rem;
            font-weight: 800;
        }

        /* Progress bar inside match */
        .match-progress {
            padding: 0.75rem 1.5rem;
        }
        .progress-track {
            height: 6px;
            background: var(--border);
            border-radius: 1rem;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            border-radius: 1rem;
            transition: width 1s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* Engine table */
        .engine-details {
            padding: 0 1.5rem 1.25rem;
        }
        .engine-toggle {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--muted);
            cursor: pointer;
            padding: 0.5rem 0;
            border: none;
            background: none;
            transition: color 0.2s;
        }
        .engine-toggle:hover { color: var(--accent); }
        .engine-toggle i { transition: transform 0.3s; }
        .engine-toggle.active i { transform: rotate(180deg); }

        .engine-table-wrap {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .engine-table-wrap.open { max-height: 500px; }

        .engine-table {
            width: 100%;
            font-size: 0.78rem;
            border-collapse: collapse;
        }
        .engine-table th {
            font-weight: 600;
            color: var(--muted);
            text-transform: uppercase;
            font-size: 0.68rem;
            letter-spacing: 0.04em;
            padding: 0.5rem 0.75rem;
            border-bottom: 1px solid var(--border);
            text-align: left;
        }
        .engine-table td {
            padding: 0.6rem 0.75rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .engine-table tr:last-child td { border-bottom: none; }
        .engine-table tr:hover td { background: var(--surface-hover); }
        .engine-pill {
            display: inline-block;
            padding: 0.15rem 0.55rem;
            border-radius: 0.75rem;
            font-size: 0.68rem;
            font-weight: 700;
            background: #f1f5f9;
            color: var(--text);
            border: 1px solid var(--border);
        }
        .mini-progress {
            width: 80px; height: 4px;
            background: var(--border);
            border-radius: 1rem;
            overflow: hidden;
            display: inline-block;
            vertical-align: middle;
        }
        .mini-progress .fill {
            height: 100%;
            border-radius: 1rem;
            background: var(--accent);
            transition: width 0.8s ease;
        }

        .match-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 1.5rem 1rem;
            font-size: 0.72rem;
            color: var(--muted);
        }

        /* ===== IMAGE ANALYSIS SECTION ===== */
        .image-analysis {
            background: linear-gradient(135deg, #faf5ff, #f1f5f9);
            border: 1px solid #e9d5ff;
            border-radius: var(--radius-2xl);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        .image-analysis h5 {
            font-size: 0.9rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 1rem;
            display: flex; align-items: center; gap: 0.5rem;
        }
        .image-analysis h5 i { color: #8b5cf6; }

        .img-match-card {
            background: white;
            border: 1px solid #e9d5ff;
            border-radius: var(--radius-xl);
            padding: 1rem 1.25rem;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .img-match-card:last-child { margin-bottom: 0; }
        .img-icon {
            width: 44px; height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, #8b5cf6, #a78bfa);
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 1.1rem;
            flex-shrink: 0;
        }
        .img-info { flex: 1; min-width: 200px; }
        .img-info strong { font-size: 0.85rem; }
        .img-info small { color: var(--muted); font-size: 0.72rem; }
        .img-score-badge {
            padding: 0.35rem 0.9rem;
            border-radius: 2rem;
            font-weight: 800;
            font-size: 0.8rem;
            color: white;
        }

        /* ===== NO MATCHES ===== */
        .no-matches {
            text-align: center;
            padding: 3rem 2rem;
            background: var(--success-bg);
            border: 1px solid #a7f3d0;
            border-radius: var(--radius-2xl);
        }
        .no-matches .icon-circle {
            width: 72px; height: 72px;
            border-radius: 50%;
            background: var(--success);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1rem;
            color: white; font-size: 2rem;
        }
        .no-matches h4 {
            font-weight: 700;
            color: #065f46;
            margin-bottom: 0.3rem;
        }
        .no-matches p {
            color: var(--muted);
            font-size: 0.85rem;
            margin: 0;
        }

        /* ===== FOOTER / ACTIONS ===== */
        .actions-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 2.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border);
        }
        .btn-primary-custom {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.7rem 1.5rem;
            border-radius: 2rem;
            font-weight: 600;
            font-size: 0.85rem;
            border: none;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            text-decoration: none;
            transition: all 0.3s;
            box-shadow: 0 4px 16px rgba(15,23,42,0.25);
        }
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(15,23,42,0.35);
            color: white;
        }
        .btn-outline-custom {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.3rem;
            border-radius: 2rem;
            font-weight: 600;
            font-size: 0.82rem;
            border: 1px solid var(--border);
            background: white;
            color: var(--text-secondary);
            text-decoration: none;
            transition: all 0.3s;
        }
        .btn-outline-custom:hover {
            background: var(--surface-hover);
            border-color: var(--accent);
            color: var(--accent);
        }

        .report-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border);
            font-size: 0.75rem;
            color: var(--muted);
        }
        .report-footer i { margin-right: 0.3rem; }

        /* ===== PRINT ===== */
        @media print {
            body { background: white !important; }
            .bg-mesh { display: none; }
            .glass-card {
                box-shadow: none;
                backdrop-filter: none;
                animation: none;
                border: 1px solid #ddd;
            }
            .btn-primary-custom,
            .btn-outline-custom,
            .engine-toggle { display: none; }
            .match-card:hover { transform: none; box-shadow: none; }
            .stat-card:hover { transform: none; box-shadow: none; }
        }
    </style>
</head>
<body>
    <!-- Background mesh -->
    <div class="bg-mesh"></div>

    <div class="main-container">
        <div class="glass-card">

            <!-- ===== HEADER ===== -->
            <div class="report-header">
                <div class="brand">
                    <div class="brand-icon"><i class="bi bi-shield-shaded"></i></div>
                    <div>
                        <div class="brand-name">PlagioScan</div>
                        <div style="font-size: 0.72rem; color: var(--muted); font-weight: 500;">Moteur de d&eacute;tection anti-plagiat</div>
                    </div>
                </div>
                <div>
                    <span class="report-badge"><i class="bi bi-file-earmark-bar-graph"></i> Rapport d'analyse</span>
                    <div class="report-meta" style="margin-top: 0.5rem;">
                        <span><i class="bi bi-clock me-1"></i>{{ now()->format('d/m/Y &agrave; H:i') }}</span>
                    </div>
                </div>
            </div>

            <!-- ===== FILE INFO STATS ===== -->
            <div class="section-title"><i class="bi bi-info-circle"></i> Informations du fichier</div>
            <div class="stats-grid">
                <!-- Fichier -->
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="bi bi-file-earmark-text"></i></div>
                    <div class="stat-value">{{ $filename }}</div>
                    <div class="stat-label">Fichier analys&eacute;</div>
                    <div class="stat-detail">
                        Type : {{ isset($detected_format) && $detected_format ? ucfirst($detected_format) : ($file_type === 'code' ? 'Code source' : 'Texte naturel') }}<br>
                        Taille : {{ number_format($content_length / 1024, 2) }} Ko
                    </div>
                </div>
                <!-- Comparaisons -->
                <div class="stat-card">
                    <div class="stat-icon green"><i class="bi bi-database"></i></div>
                    <div class="stat-value">{{ $num_comparisons ?? count($top_matches ?? []) }}</div>
                    <div class="stat-label">Documents compar&eacute;s</div>
                    <div class="stat-detail">
                        Base de r&eacute;f&eacute;rence statique
                    </div>
                </div>
                <!-- Moteurs -->
                <div class="stat-card">
                    <div class="stat-icon purple"><i class="bi bi-cpu"></i></div>
                    <div class="stat-value">{{ count(array_filter($engines_used ?? [])) }}</div>
                    <div class="stat-label">Moteurs actifs</div>
                    <div class="stat-detail">
                        @foreach($engines_used as $engine => $active)
                            @if($active)
                                <span class="engine-pill">{{ strtoupper($engine) }}</span>
                            @endif
                        @endforeach
                    </div>
                </div>
                <!-- Extraction (PDF/Word) -->
                <div class="stat-card">
                    <div class="stat-icon orange"><i class="bi bi-extract"></i></div>
                    <div class="stat-value">{{ isset($extraction) && is_array($extraction) && count($extraction) > 0 ? ucfirst($extraction['format'] ?? 'N/A') : 'Texte' }}</div>
                    <div class="stat-label">Extraction</div>
                    <div class="stat-detail">
                        @if(isset($extraction) && is_array($extraction) && count($extraction) > 0)
                            @if(isset($extraction['format']) && $extraction['format'] === 'pdf')
                                <span class="extraction-tag pdf"><i class="bi bi-filetype-pdf"></i> PDF</span>
                                <span class="extraction-tag"><i class="bi bi-file-text"></i> {{ $extraction['pages'] ?? 0 }} page(s)</span>
                            @elseif(isset($extraction['format']) && $extraction['format'] === 'docx')
                                <span class="extraction-tag docx"><i class="bi bi-filetype-docx"></i> Word</span>
                            @endif
                            @if(isset($extraction['images_extracted']) && $extraction['images_extracted'] > 0)
                                <span class="extraction-tag img"><i class="bi bi-image"></i> {{ $extraction['images_extracted'] }} image(s)</span>
                            @endif
                        @else
                            <span class="extraction-tag">Contenu textuel direct</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- ===== IMAGE ANALYSIS SECTION (if available) ===== -->
            @if(isset($image_analysis) && $image_analysis && isset($image_analysis['analyzed']) && $image_analysis['analyzed'])
                <div class="image-analysis">
                    <h5><i class="bi bi-image"></i> Analyse d'images</h5>
                    @if(isset($image_analysis['matches']) && count($image_analysis['matches']) > 0)
                        @foreach($image_analysis['matches'] as $imgMatch)
                            @php
                                $imgConf = ($imgMatch['confidence'] ?? 0) * 100;
                                $imgColor = $imgConf >= 80 ? '#dc2626' : ($imgConf >= 60 ? '#f97316' : '#3b82f6');
                            @endphp
                            <div class="img-match-card">
                                <div class="img-icon"><i class="bi bi-images"></i></div>
                                <div class="img-info">
                                    <strong>{{ $imgMatch['image_name'] ?? 'Image inconnue' }}</strong><br>
                                    <small>
                                        vs {{ $imgMatch['compared_with'] ?? 'R&eacute;f&eacute;rence' }}
                                        &bull; M&eacute;thode : {{ $imgMatch['method'] ?? 'pHash' }}
                                    </small>
                                </div>
                                <span class="img-score-badge" style="background: {{ $imgColor }};">
                                    {{ round($imgConf, 1) }}%
                                </span>
                            </div>
                        @endforeach
                    @else
                        <p style="margin:0; font-size: 0.82rem; color: var(--muted);">
                            <i class="bi bi-check-circle me-1"></i> Aucune similarit&eacute; d'image d&eacute;tect&eacute;e.
                        </p>
                    @endif
                </div>
            @endif

            <!-- ===== SCORE PRINCIPAL & R&Eacute;SUM&Eacute; ===== -->
            <div class="section-title"><i class="bi bi-speedometer2"></i> Score de similarit&eacute;</div>
            <div class="score-section">
                <div style="text-align: center;">
                    <div class="score-ring">
                        <svg viewBox="0 0 180 180">
                            <circle class="track" cx="90" cy="90" r="80" />
                            @php
                                $circumference = 2 * pi() * 80; // 502.65
                                $offset = $circumference - ($max_score * $circumference);
                                $scoreColor = $max_score >= 0.6 ? '#ef4444' : ($max_score >= 0.4 ? '#f59e0b' : '#10b981');
                            @endphp
                            <circle class="progress-arc" cx="90" cy="90" r="80"
                                    style="stroke: {{ $scoreColor }}; stroke-dashoffset: {{ $offset }};"
                                    data-target="{{ $offset }}" />
                        </svg>
                        <div class="score-text">
                            <div class="score-number" style="color: {{ $scoreColor }};">{{ round($max_score * 100) }}</div>
                            <div class="score-unit">% de similarit&eacute;</div>
                        </div>
                    </div>
                    <div class="score-label">Score maximal d&eacute;tect&eacute;</div>
                    @if($possible_plagiarism)
                        <div class="alert-plagiat danger">
                            <i class="bi bi-exclamation-triangle-fill"></i> Plagiat potentiel d&eacute;tect&eacute;
                        </div>
                    @else
                        <div class="alert-plagiat success">
                            <i class="bi bi-check-circle-fill"></i> Aucun plagiat significatif
                        </div>
                    @endif
                </div>

                <div>
                    <div style="font-size: 0.85rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 1rem;">
                        R&eacute;partition des correspondances
                    </div>
                    <div class="summary-grid">
                        <div class="summary-box critical">
                            <div class="summary-count">{{ $summary['critical'] ?? 0 }}</div>
                            <div class="summary-label">Critique</div>
                        </div>
                        <div class="summary-box high">
                            <div class="summary-count">{{ $summary['high'] ?? 0 }}</div>
                            <div class="summary-label">&Eacute;lev&eacute;</div>
                        </div>
                        <div class="summary-box medium">
                            <div class="summary-count">{{ $summary['medium'] ?? 0 }}</div>
                            <div class="summary-label">Moyen</div>
                        </div>
                        <div class="summary-box low">
                            <div class="summary-count">{{ $summary['low'] ?? 0 }}</div>
                            <div class="summary-label">Faible</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== D&Eacute;TAIL DES MATCHES ===== -->
            <div class="section-title"><i class="bi bi-files"></i> Correspondances d&eacute;taill&eacute;es</div>

            @if(isset($top_matches) && count($top_matches) > 0)
                <div class="matches-list">
                    @foreach($top_matches as $index => $match)
                        @php
                            $scorePercent = round(($match['combined_score'] ?? 0) * 100, 1);
                            $level = $match['level'] ?? 'low';
                            $borderColor = match($level) {
                                'critical' => '#ef4444',
                                'high' => '#f97316',
                                'medium' => '#f59e0b',
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
                        <div class="match-card">
                            <div class="match-accent" style="background: {{ $borderColor }};"></div>
                            <div class="match-header">
                                <div class="match-filename">
                                    <i class="bi bi-file-text-fill" style="color: {{ $borderColor }};"></i>
                                    <strong>{{ $match['filename'] ?? 'Document inconnu' }}</strong>
                                    <span class="level-badge {{ $level }}">{{ $levelLabels[$level] ?? ucfirst($level) }}</span>
                                </div>
                                <div class="match-score-big" style="color: {{ $borderColor }};">
                                    {{ $scorePercent }}%
                                </div>
                            </div>

                            <div class="match-progress">
                                <div class="progress-track">
                                    <div class="progress-fill" style="width: {{ $scorePercent }}%; background: {{ $borderColor }};"></div>
                                </div>
                            </div>

                            @if(isset($match['engines']) && count($match['engines']) > 0)
                                <div class="engine-details">
                                    <button class="engine-toggle" onclick="toggleEngine(this)">
                                        <i class="bi bi-chevron-down"></i> D&eacute;tails par moteur ({{ count($match['engines']) }})
                                    </button>
                                    <div class="engine-table-wrap">
                                        <table class="engine-table">
                                            <thead>
                                                <tr>
                                                    <th>Moteur</th>
                                                    <th>Score brut</th>
                                                    <th>Poids</th>
                                                    <th>Contribution</th>
                                                    <th style="width:100px;">Visuel</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($match['engines'] as $engineName => $data)
                                                    @php
                                                        $contribPercent = ($data['contribution'] ?? 0) * 100;
                                                    @endphp
                                                    <tr>
                                                        <td><span class="engine-pill">{{ strtoupper($engineName) }}</span></td>
                                                        <td>{{ round($data['raw_score'] ?? 0, 4) }}</td>
                                                        <td>{{ $data['weight'] ?? 0 }}</td>
                                                        <td>{{ round($data['contribution'] ?? 0, 4) }}</td>
                                                        <td>
                                                            <div class="mini-progress">
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

                            <div class="match-footer">
                                <span><i class="bi bi-hash"></i> R&eacute;f&eacute;rence : {{ $match['submission_id'] ?? 'N/A' }}</span>
                                <span>Score combin&eacute; : {{ $scorePercent }}%</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="no-matches">
                    <div class="icon-circle"><i class="bi bi-check-lg"></i></div>
                    <h4>Aucune correspondance trouv&eacute;e</h4>
                    <p>Le document analys&eacute; ne pr&eacute;sente aucune similarit&eacute; significative avec la base de r&eacute;f&eacute;rence.</p>
                </div>
            @endif

            <!-- ===== ACTIONS ===== -->
            <div class="actions-bar">
                <a href="{{ route('upload.index') }}" class="btn-primary-custom">
                    <i class="bi bi-arrow-left"></i> Nouvelle analyse
                </a>
                <div style="display: flex; gap: 0.75rem;">
                    <button onclick="window.print();" class="btn-outline-custom">
                        <i class="bi bi-printer"></i> Imprimer
                    </button>
                    <button onclick="downloadJSON()" class="btn-outline-custom">
                        <i class="bi bi-download"></i> Exporter JSON
                    </button>
                </div>
            </div>

            <div class="report-footer">
                <i class="bi bi-shield-lock-fill"></i> Analyse confidentielle &bull; Algorithmes : TF-IDF, S&eacute;mantique BERT, Winnowing, LCS, pHash
            </div>

        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Toggle engine details
    function toggleEngine(btn) {
        const wrap = btn.nextElementSibling;
        wrap.classList.toggle('open');
        btn.classList.toggle('active');
    }

    // Animate score circle on load
    document.addEventListener('DOMContentLoaded', function() {
        const arc = document.querySelector('.progress-arc');
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

    // Export JSON (stores the full response data)
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
    @else
    function downloadJSON() {
        alert('Les donn&eacute;es brutes ne sont pas disponibles pour cet export.');
    }
    @endif
</script>
</body>
</html>
