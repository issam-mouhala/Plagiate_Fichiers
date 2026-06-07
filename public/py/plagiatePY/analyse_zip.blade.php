{{-- resources/views/analyse_zip/index.blade.php --}}
{{-- Rapport d'analyse ZIP — PlagioScan --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PlagioScan – Rapport d'analyse ZIP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <style>
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
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.04);
            --shadow-md: 0 4px 16px -2px rgba(0,0,0,0.08);
            --shadow-lg: 0 12px 40px -8px rgba(0,0,0,0.12);
            --shadow-xl: 0 24px 60px -12px rgba(0,0,0,0.18);
        }
        *, *::before, *::after { box-sizing: border-box; }
        body {
            background: #f1f5f9;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--text);
            margin: 0; padding: 0; min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }
        .bg-mesh {
            position: fixed; inset: 0; z-index: -1; overflow: hidden;
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 40%, #0f172a 100%);
        }
        .bg-mesh::before, .bg-mesh::after {
            content: ''; position: absolute; border-radius: 50%;
            filter: blur(100px); opacity: 0.15;
            animation: float 20s ease-in-out infinite;
        }
        .bg-mesh::before { width: 600px; height: 600px; background: var(--accent); top: -200px; right: -100px; }
        .bg-mesh::after { width: 500px; height: 500px; background: #8b5cf6; bottom: -150px; left: -100px; animation-delay: -10s; }
        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -30px) scale(1.05); }
            66% { transform: translate(-20px, 20px) scale(0.95); }
        }
        .main-container { max-width: 1200px; margin: 0 auto; padding: 2rem 1.25rem 4rem; }
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
        @keyframes cardIn { from { opacity: 0; transform: translateY(30px) scale(0.98); } to { opacity: 1; transform: translateY(0) scale(1); } }

        /* HEADER */
        .report-header {
            display: flex; justify-content: space-between; align-items: flex-start;
            flex-wrap: wrap; gap: 1rem; padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--border); margin-bottom: 2rem;
        }
        .brand { display: flex; align-items: center; gap: 0.75rem; }
        .brand-icon {
            width: 48px; height: 48px;
            background: linear-gradient(135deg, var(--accent), #8b5cf6);
            border-radius: 14px; display: flex; align-items: center; justify-content: center;
            color: white; font-size: 1.4rem; box-shadow: 0 4px 16px rgba(59,130,246,0.35);
        }
        .brand-name {
            font-size: 1.5rem; font-weight: 800;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        }
        .report-meta { display: flex; flex-direction: column; align-items: flex-end; gap: 0.25rem; color: var(--muted); font-size: 0.8rem; }
        .report-badge {
            display: inline-flex; align-items: center; gap: 0.4rem;
            padding: 0.3rem 0.9rem; border-radius: 2rem;
            font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em;
        }
        .report-badge.zip-badge { background: linear-gradient(135deg, #fff7ed, #ffedd5); color: #ea580c; }
        .report-badge.analysis-badge { background: linear-gradient(135deg, #eff6ff, #e0e7ff); color: var(--accent); }

        /* SECTION TITLE */
        .section-title {
            font-size: 1rem; font-weight: 700; color: var(--text);
            margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.5rem;
        }
        .section-title i { color: var(--accent); font-size: 1.1rem; }
        .section-divider { border: none; border-top: 1px solid var(--border); margin: 2rem 0; }

        /* STAT CARDS */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
        .stat-card {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: var(--radius-xl); padding: 1.25rem 1.5rem;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); position: relative; overflow: hidden;
        }
        .stat-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, var(--accent), #8b5cf6); opacity: 0; transition: opacity 0.3s; }
        .stat-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); border-color: var(--accent-light); }
        .stat-card:hover::before { opacity: 1; }
        .stat-icon { width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; margin-bottom: 0.75rem; }
        .stat-icon.blue { background: #eff6ff; color: var(--accent); }
        .stat-icon.green { background: #ecfdf5; color: var(--success); }
        .stat-icon.purple { background: #f5f3ff; color: #8b5cf6; }
        .stat-icon.orange { background: #fff7ed; color: #f97316; }
        .stat-icon.red { background: #fef2f2; color: var(--danger); }
        .stat-icon.cyan { background: #ecfeff; color: var(--info); }
        .stat-value { font-size: 1.3rem; font-weight: 800; color: var(--text); line-height: 1.2; word-break: break-all; }
        .stat-label { font-size: 0.78rem; color: var(--muted); font-weight: 500; margin-top: 0.15rem; }
        .stat-detail { font-size: 0.72rem; color: var(--text-secondary); margin-top: 0.5rem; line-height: 1.5; }

        .extraction-tag {
            display: inline-flex; align-items: center; gap: 0.3rem;
            padding: 0.2rem 0.6rem; border-radius: 1rem;
            font-size: 0.68rem; font-weight: 600;
            background: #f1f5f9; color: var(--text-secondary); border: 1px solid var(--border);
        }
        .extraction-tag.pdf { background: #fef2f2; color: #dc2626; border-color: #fecaca; }
        .extraction-tag.docx { background: #eff6ff; color: #2563eb; border-color: #bfdbfe; }
        .extraction-tag.img { background: #f5f3ff; color: #7c3aed; border-color: #ddd6fe; }
        .extraction-tag.zip { background: #fff7ed; color: #ea580c; border-color: #fed7aa; }
        .extraction-tag.code { background: #ecfdf5; color: #059669; border-color: #a7f3d0; }

        /* LEVEL BADGE */
        .level-badge {
            display: inline-flex; align-items: center; gap: 0.3rem;
            padding: 0.25rem 0.7rem; border-radius: 1rem;
            font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em;
        }
        .level-badge.critical { background: var(--danger); color: white; }
        .level-badge.high { background: #f97316; color: white; }
        .level-badge.medium { background: var(--warning); color: #1e293b; }
        .level-badge.low { background: var(--accent); color: white; }
        .level-badge.none { background: #94a3b8; color: white; }

        /* SCORE RING */
        .score-section {
            display: grid; grid-template-columns: 280px 1fr; gap: 2rem; align-items: center;
            padding: 2rem; background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            border-radius: var(--radius-2xl); border: 1px solid var(--border); margin-bottom: 2rem;
        }
        .score-ring { position: relative; width: 180px; height: 180px; margin: 0 auto 1rem; }
        .score-ring svg { width: 100%; height: 100%; transform: rotate(-90deg); }
        .score-ring .track { fill: none; stroke: var(--border); stroke-width: 10; }
        .score-ring .progress-arc { fill: none; stroke-width: 10; stroke-linecap: round; stroke-dasharray: 502.65; stroke-dashoffset: 502.65; transition: stroke-dashoffset 1.5s cubic-bezier(0.16, 1, 0.3, 1); }
        .score-ring .score-text { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; }
        .score-number { font-size: 2.75rem; font-weight: 800; line-height: 1; }
        .score-unit { font-size: 0.85rem; font-weight: 600; color: var(--muted); margin-top: 0.1rem; }
        .score-label { font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-top: 0.75rem; text-align: center; }

        .alert-plagiat {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.6rem 1.2rem; border-radius: var(--radius-xl);
            font-weight: 700; font-size: 0.85rem; margin-top: 0.75rem;
            animation: pulse 2s ease-in-out infinite;
        }
        .alert-plagiat.danger { background: linear-gradient(135deg, #dc2626, #b91c1c); color: white; box-shadow: 0 4px 20px rgba(220,38,38,0.4); }
        .alert-plagiat.success { background: linear-gradient(135deg, #10b981, #059669); color: white; box-shadow: 0 4px 20px rgba(16,185,129,0.3); }
        @keyframes pulse { 0%, 100% { box-shadow: 0 4px 20px rgba(0,0,0,0.2); } 50% { box-shadow: 0 4px 30px rgba(0,0,0,0.35); } }

        /* FILE TABLE */
        .file-table-wrap {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: var(--radius-xl); overflow: hidden; margin-bottom: 2rem;
        }
        .file-table { width: 100%; font-size: 0.82rem; border-collapse: collapse; }
        .file-table thead { background: linear-gradient(135deg, #f8fafc, #f1f5f9); }
        .file-table th {
            font-weight: 700; color: var(--muted); text-transform: uppercase;
            font-size: 0.68rem; letter-spacing: 0.05em; padding: 0.85rem 1.25rem;
            border-bottom: 2px solid var(--border); text-align: left;
        }
        .file-table td { padding: 0.75rem 1.25rem; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        .file-table tbody tr { transition: background 0.2s; }
        .file-table tbody tr:hover { background: var(--surface-hover); }
        .file-table tbody tr:last-child td { border-bottom: none; }
        .file-icon-mini { width: 32px; height: 32px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.9rem; }
        .file-icon-mini.text { background: #eff6ff; color: var(--accent); }
        .file-icon-mini.code { background: #ecfdf5; color: #059669; }
        .file-icon-mini.image { background: #f5f3ff; color: #7c3aed; }
        .file-icon-mini.pdf { background: #fef2f2; color: #dc2626; }
        .file-icon-mini.docx { background: #dbeafe; color: #2563eb; }
        .ext-badge {
            display: inline-block; padding: 0.1rem 0.45rem; border-radius: 0.5rem;
            font-size: 0.65rem; font-weight: 700; background: #f1f5f9; color: var(--text-secondary);
            border: 1px solid var(--border);
        }

        /* CROSS MATCH CARDS */
        .cross-card {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: var(--radius-xl); overflow: hidden;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            animation: slideUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
            position: relative; margin-bottom: 1rem;
        }
        .cross-card:hover { border-color: var(--accent-light); box-shadow: var(--shadow-lg); transform: translateX(4px); }
        @keyframes slideUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
        .cross-accent { position: absolute; left: 0; top: 0; bottom: 0; width: 4px; }
        .cross-header { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; padding: 1.25rem 1.5rem 0.5rem; }
        .cross-pair { display: flex; align-items: center; gap: 0.6rem; font-size: 0.9rem; }
        .cross-pair strong { font-size: 0.88rem; }
        .cross-pair .arrow-icon { color: var(--muted); font-size: 0.8rem; }
        .cross-score-big { font-size: 1.6rem; font-weight: 800; }
        .cross-progress { padding: 0.5rem 1.5rem; }
        .progress-track { height: 6px; background: var(--border); border-radius: 1rem; overflow: hidden; }
        .progress-fill { height: 100%; border-radius: 1rem; transition: width 1s cubic-bezier(0.16, 1, 0.3, 1); }

        /* PER-FILE RESULTS */
        .per-file-card {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: var(--radius-xl); overflow: hidden; margin-bottom: 1rem;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            animation: slideUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
        }
        .per-file-card:hover { box-shadow: var(--shadow-md); }
        .per-file-header {
            display: flex; justify-content: space-between; align-items: center;
            flex-wrap: wrap; gap: 0.75rem; padding: 1.25rem 1.5rem;
            cursor: pointer; transition: background 0.2s;
        }
        .per-file-header:hover { background: var(--surface-hover); }
        .per-file-name { display: flex; align-items: center; gap: 0.6rem; }
        .per-file-name strong { font-size: 0.92rem; }
        .per-file-score { display: flex; align-items: center; gap: 0.75rem; }
        .per-file-matches-wrap { max-height: 0; overflow: hidden; transition: max-height 0.5s cubic-bezier(0.16, 1, 0.3, 1); }
        .per-file-matches-wrap.open { max-height: 5000px; }
        .per-file-matches-inner { padding: 0 1.5rem 1.25rem; border-top: 1px solid var(--border); }
        .per-file-match-row {
            display: flex; align-items: center; gap: 0.75rem; padding: 0.6rem 0;
            border-bottom: 1px solid #f1f5f9; font-size: 0.8rem;
        }
        .per-file-match-row:last-child { border-bottom: none; }

        /* ENGINE DETAILS */
        .engine-details { padding: 0 1.5rem 1rem; }
        .engine-toggle {
            display: flex; align-items: center; gap: 0.4rem; font-size: 0.75rem;
            font-weight: 600; color: var(--muted); cursor: pointer;
            padding: 0.5rem 0; border: none; background: none; transition: color 0.2s;
        }
        .engine-toggle:hover { color: var(--accent); }
        .engine-toggle i { transition: transform 0.3s; }
        .engine-toggle.active i { transform: rotate(180deg); }
        .engine-table-wrap { max-height: 0; overflow: hidden; transition: max-height 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
        .engine-table-wrap.open { max-height: 500px; }
        .engine-table { width: 100%; font-size: 0.78rem; border-collapse: collapse; }
        .engine-table th { font-weight: 600; color: var(--muted); text-transform: uppercase; font-size: 0.68rem; letter-spacing: 0.04em; padding: 0.5rem 0.75rem; border-bottom: 1px solid var(--border); text-align: left; }
        .engine-table td { padding: 0.6rem 0.75rem; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        .engine-table tr:last-child td { border-bottom: none; }
        .engine-table tr:hover td { background: var(--surface-hover); }
        .mini-progress { width: 80px; height: 4px; background: var(--border); border-radius: 1rem; overflow: hidden; display: inline-block; vertical-align: middle; }
        .mini-progress .fill { height: 100%; border-radius: 1rem; background: var(--accent); transition: width 0.8s ease; }
        .engine-pill {
            display: inline-block; padding: 0.12rem 0.5rem; border-radius: 0.75rem;
            font-size: 0.65rem; font-weight: 700; background: #f1f5f9; color: var(--text); border: 1px solid var(--border);
        }

        /* IMAGE ANALYSIS */
        .image-analysis-section {
            background: linear-gradient(135deg, #faf5ff, #f1f5f9);
            border: 1px solid #e9d5ff; border-radius: var(--radius-2xl); padding: 1.5rem; margin-bottom: 2rem;
        }
        .image-analysis-section h5 { font-size: 0.9rem; font-weight: 700; color: var(--text); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; }
        .image-analysis-section h5 i { color: #8b5cf6; }
        .img-match-card {
            background: white; border: 1px solid #e9d5ff; border-radius: var(--radius-xl);
            padding: 1rem 1.25rem; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;
        }
        .img-match-card:last-child { margin-bottom: 0; }
        .img-icon { width: 44px; height: 44px; border-radius: 12px; background: linear-gradient(135deg, #8b5cf6, #a78bfa); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.1rem; flex-shrink: 0; }
        .img-info { flex: 1; min-width: 200px; }
        .img-info strong { font-size: 0.85rem; }
        .img-info small { color: var(--muted); font-size: 0.72rem; }
        .img-score-badge { padding: 0.35rem 0.9rem; border-radius: 2rem; font-weight: 800; font-size: 0.8rem; color: white; }

        /* NO MATCHES */
        .no-matches { text-align: center; padding: 2.5rem 2rem; background: var(--success-bg); border: 1px solid #a7f3d0; border-radius: var(--radius-2xl); }
        .no-matches .icon-circle { width: 64px; height: 64px; border-radius: 50%; background: var(--success); display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; color: white; font-size: 1.75rem; }
        .no-matches h4 { font-weight: 700; color: #065f46; margin-bottom: 0.3rem; font-size: 0.95rem; }
        .no-matches p { color: var(--muted); font-size: 0.82rem; margin: 0; }

        /* TABS */
        .analysis-tabs {
            display: flex; gap: 0; background: var(--surface); border: 1px solid var(--border);
            border-radius: var(--radius-xl); overflow: hidden; margin-bottom: 2rem;
        }
        .analysis-tab {
            flex: 1; padding: 0.85rem 1rem; font-size: 0.82rem; font-weight: 600;
            text-align: center; cursor: pointer; border: none; background: none;
            color: var(--muted); transition: all 0.25s; border-right: 1px solid var(--border);
            display: flex; align-items: center; justify-content: center; gap: 0.4rem;
        }
        .analysis-tab:last-child { border-right: none; }
        .analysis-tab:hover { background: var(--surface-hover); color: var(--text); }
        .analysis-tab.active { background: linear-gradient(135deg, var(--accent), #6366f1); color: white; }
        .analysis-tab .tab-count {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 20px; height: 20px; border-radius: 1rem; font-size: 0.65rem; font-weight: 700;
            background: rgba(255,255,255,0.25); padding: 0 0.3rem;
        }
        .analysis-tab:not(.active) .tab-count { background: #f1f5f9; color: var(--text-secondary); }
        .tab-content { display: none; }
        .tab-content.active { display: block; }

        /* ACTIONS */
        .actions-bar { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-top: 2.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border); }
        .btn-primary-custom {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.7rem 1.5rem; border-radius: 2rem; font-weight: 600; font-size: 0.85rem;
            border: none; background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white; text-decoration: none; transition: all 0.3s; box-shadow: 0 4px 16px rgba(15,23,42,0.25);
        }
        .btn-primary-custom:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(15,23,42,0.35); color: white; }
        .btn-outline-custom {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.6rem 1.3rem; border-radius: 2rem; font-weight: 600; font-size: 0.82rem;
            border: 1px solid var(--border); background: white; color: var(--text-secondary);
            text-decoration: none; transition: all 0.3s;
        }
        .btn-outline-custom:hover { background: var(--surface-hover); border-color: var(--accent); color: var(--accent); }

        /* RAW JSON */
        .raw-json-section { margin-top: 2rem; }
        .raw-json-toggle { display: flex; align-items: center; gap: 0.5rem; font-size: 0.8rem; font-weight: 600; color: var(--muted); cursor: pointer; border: none; background: none; padding: 0.5rem 0; }
        .raw-json-toggle:hover { color: var(--accent); }
        .raw-json-wrap { max-height: 0; overflow: hidden; transition: max-height 0.5s ease; }
        .raw-json-wrap.open { max-height: 3000px; }
        .raw-json-pre {
            background: #0f172a; color: #a5f3fc; padding: 1.5rem; border-radius: var(--radius-xl);
            font-size: 0.72rem; font-family: 'Fira Code', 'Courier New', monospace; overflow-x: auto; max-height: 500px; overflow-y: auto;
            line-height: 1.6; margin-top: 0.5rem; white-space: pre-wrap; word-break: break-all;
        }

        @media (max-width: 768px) {
            .score-section { grid-template-columns: 1fr; text-align: center; }
            .glass-card { padding: 1.5rem; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
            .analysis-tabs { flex-wrap: wrap; }
            .analysis-tab { flex: 1 1 33%; border-bottom: 1px solid var(--border); }
        }
        @media (max-width: 480px) {
            .stats-grid { grid-template-columns: 1fr; }
        }
        @media print {
            body { background: white !important; }
            .bg-mesh { display: none; }
            .glass-card { box-shadow: none; backdrop-filter: none; animation: none; border: 1px solid #ddd; }
            .btn-primary-custom, .btn-outline-custom, .engine-toggle, .raw-json-toggle, .analysis-tab { display: none; }
            .tab-content { display: block !important; }
            .per-file-matches-wrap { max-height: none !important; }
            .raw-json-section { display: none; }
        }
    </style>
</head>
<body>
    <div class="bg-mesh"></div>
    <div class="main-container">
        <div class="glass-card">

            {{-- ===== HEADER ===== --}}
            <div class="report-header">
                <div class="brand">
                    <div class="brand-icon"><i class="bi bi-shield-shaded"></i></div>
                    <div>
                        <div class="brand-name">PlagioScan</div>
                        <div style="font-size: 0.72rem; color: var(--muted); font-weight: 500;">Moteur de d&eacute;tection anti-plagiat</div>
                    </div>
                </div>
                <div>
                    <span class="report-badge zip-badge"><i class="bi bi-file-earmark-zip"></i> Analyse ZIP</span>
                    <span class="report-badge analysis-badge" style="margin-left:0.4rem;"><i class="bi bi-bar-chart-line"></i> Rapport</span>
                    <div class="report-meta" style="margin-top: 0.5rem;">
                        <span><i class="bi bi-clock me-1"></i>{{ now()->format('d/m/Y \&agrave; H:i') }}</span>
                    </div>
                </div>
            </div>

            {{-- ===== ZIP INFO STATS ===== --}}
            <div class="section-title"><i class="bi bi-archive"></i> Informations de l'archive</div>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon orange"><i class="bi bi-file-earmark-zip"></i></div>
                    <div class="stat-value">{{ $zip_filename }}</div>
                    <div class="stat-label">Archive analys&eacute;e</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="bi bi-file-earmark-text"></i></div>
                    <div class="stat-value">{{ $zip_info['total_files'] ?? 0 }}</div>
                    <div class="stat-label">Fichiers extraits</div>
                    <div class="stat-detail">
                        {{ $zip_info['text_code_files'] ?? 0 }} texte/code &bull;
                        {{ $zip_info['image_files'] ?? 0 }} image(s)
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon purple"><i class="bi bi-arrow-left-right"></i></div>
                    <div class="stat-value">{{ $cross_analysis['pairs_compared'] ?? 0 }}</div>
                    <div class="stat-label">Paires crois&eacute;es</div>
                    <div class="stat-detail">Comparaison entre fichiers du ZIP</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green"><i class="bi bi-database"></i></div>
                    <div class="stat-value">{{ $per_file_analysis['files_analyzed'] ?? 0 }}</div>
                    <div class="stat-label">Fichiers vs Base</div>
                    <div class="stat-detail">Comparaison individuelle avec la base</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon cyan"><i class="bi bi-images"></i></div>
                    <div class="stat-value">{{ $image_analysis['images_in_zip'] ?? 0 }}</div>
                    <div class="stat-label">Images dans le ZIP</div>
                    <div class="stat-detail">{{ $image_analysis['matches_found'] ?? 0 }} correspondance(s) trouv&eacute;e(s)</div>
                </div>
            </div>

            {{-- ===== FILES TABLE ===== --}}
            <div class="section-title"><i class="bi bi-list-ul"></i> Fichiers extraits de l'archive</div>
            <div class="file-table-wrap">
                <table class="file-table">
                    <thead>
                        <tr>
                            <th style="width:40px;">#</th>
                            <th>Fichier</th>
                            <th>Type</th>
                            <th>Extension</th>
                            <th>Taille</th>
                            <th>Images</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($files_extracted as $idx => $f)
                            @php
                                $fType = $f['file_type'] ?? 'text';
                                $fIcon = match($fType) {
                                    'code' => 'code',
                                    'image' => 'image',
                                    default => 'text'
                                };
                                $fExt = $f['extension'] ?? '';
                                if ($fExt === 'pdf') $fIcon = 'pdf';
                                if ($fExt === 'docx') $fIcon = 'docx';
                            @endphp
                            <tr>
                                <td style="color: var(--muted); font-weight: 600;">{{ $idx + 1 }}</td>
                                <td>
                                    <div style="display:flex; align-items:center; gap:0.6rem;">
                                        <div class="file-icon-mini {{ $fIcon }}"><i class="bi {{ match($fIcon) { 'code' => 'bi-code-slash', 'image' => 'bi-image', 'pdf' => 'bi-filetype-pdf', 'docx' => 'bi-filetype-docx', default => 'bi-file-text' } }}"></i></div>
                                        <strong style="font-size:0.85rem;">{{ $f['filename'] ?? 'inconnu' }}</strong>
                                    </div>
                                </td>
                                <td>
                                    <span class="level-badge {{ $fType === 'code' ? 'low' : 'none' }}" style="font-size:0.6rem;">
                                        {{ $fType === 'code' ? 'Code' : ($fType === 'image' ? 'Image' : 'Texte') }}
                                    </span>
                                </td>
                                <td><span class="ext-badge">{{ $fExt }}</span></td>
                                <td style="color: var(--text-secondary); font-size: 0.82rem;">
                                    {{ number_format(($f['content_length'] ?? 0) / 1024, 1) }} Ko
                                </td>
                                <td style="text-align:center;">
                                    @if(($f['images_count'] ?? 0) > 0)
                                        <span class="extraction-tag img"><i class="bi bi-image"></i> {{ $f['images_count'] }}</span>
                                    @else
                                        <span style="color: var(--muted);">&mdash;</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <hr class="section-divider">

            {{-- ===== SCORE GLOBAL ===== --}}
            <div class="section-title"><i class="bi bi-speedometer2"></i> Score global de l'archive</div>
            <div class="score-section">
                <div style="text-align: center;">
                    @php
                        $circumference = 2 * pi() * 80;
                        $offset = $circumference - ($overall_score * $circumference);
                        $scoreColor = $overall_score >= 0.6 ? '#ef4444' : ($overall_score >= 0.4 ? '#f59e0b' : '#10b981');
                    @endphp
                    <div class="score-ring">
                        <svg viewBox="0 0 180 180">
                            <circle class="track" cx="90" cy="90" r="80" />
                            <circle class="progress-arc" cx="90" cy="90" r="80"
                                    style="stroke: {{ $scoreColor }}; stroke-dashoffset: {{ $offset }};"
                                    data-target="{{ $offset }}" />
                        </svg>
                        <div class="score-text">
                            <div class="score-number" style="color: {{ $scoreColor }};">{{ round($overall_score * 100) }}</div>
                            <div class="score-unit">% de similarit&eacute;</div>
                        </div>
                    </div>
                    <div class="score-label">Score global du ZIP</div>
                    @if($plagiarism_detected)
                        <div class="alert-plagiat danger">
                            <i class="bi bi-exclamation-triangle-fill"></i> Plagiat d&eacute;tect&eacute; dans l'archive
                        </div>
                    @else
                        <div class="alert-plagiat success">
                            <i class="bi bi-check-circle-fill"></i> Aucun plagiat significatif
                        </div>
                    @endif
                </div>
                <div>
                    @php
                        $levelLabels = ['critical' => 'Critique', 'high' => 'Elev\u00e9', 'medium' => 'Moyen', 'low' => 'Faible'];
                    @endphp
                    <div style="font-size: 0.85rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 1rem;">
                        R&eacute;sum&eacute; par source de d&eacute;tection
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-4">
                            <div style="text-align:center; padding:1rem; background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-xl);">
                                <div style="font-size:0.65rem; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:0.05em;">Crois&eacute; ZIP</div>
                                <div style="font-size:1.3rem; font-weight:800; margin-top:0.3rem; color:{{ ($cross_analysis['max_score'] ?? 0) >= 0.5 ? 'var(--danger)' : 'var(--success)' }};">
                                    {{ round(($cross_analysis['max_score'] ?? 0) * 100, 1) }}%
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div style="text-align:center; padding:1rem; background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-xl);">
                                <div style="font-size:0.65rem; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:0.05em;">Base</div>
                                <div style="font-size:1.3rem; font-weight:800; margin-top:0.3rem; color:{{ ($per_file_analysis['max_score'] ?? 0) >= 0.5 ? 'var(--danger)' : 'var(--success)' }};">
                                    {{ round(($per_file_analysis['max_score'] ?? 0) * 100, 1) }}%
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div style="text-align:center; padding:1rem; background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-xl);">
                                <div style="font-size:0.65rem; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:0.05em;">Images</div>
                                <div style="font-size:1.3rem; font-weight:800; margin-top:0.3rem; color:{{ ($image_analysis['max_score'] ?? 0) >= 0.5 ? 'var(--danger)' : 'var(--success)' }};">
                                    {{ round(($image_analysis['max_score'] ?? 0) * 100, 1) }}%
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Summary from cross analysis --}}
                    @if(isset($cross_analysis['summary']))
                        <div style="display:flex; gap:0.5rem; flex-wrap:wrap; margin-top:0.5rem;">
                            @foreach($cross_analysis['summary'] as $lvl => $cnt)
                                @if($cnt > 0)
                                    <span class="level-badge {{ $lvl }}">{{ $cnt }} {{ $levelLabels[$lvl] ?? ucfirst($lvl) }}</span>
                                @endif
                            @endforeach
                        </div>
                    @endif
                    <div style="margin-top:1rem; padding:0.75rem 1rem; background:white; border-radius:var(--radius-xl); border:1px solid var(--border); font-size:0.78rem; color:var(--text-secondary);">
                        <i class="bi bi-info-circle me-1" style="color:var(--accent);"></i>
                        Le score global est le <strong>maximum</strong> entre les 3 sources : croisement interne, base de donn&eacute;es, et images.
                        Niveau global :
                        <span class="level-badge {{ $overall_level ?? 'none' }}">{{ $levelLabels[$overall_level] ?? ucfirst($overall_level) }}</span>
                    </div>
                </div>
            </div>

            <hr class="section-divider">

            {{-- ===== ANALYSIS TABS ===== --}}
            <div class="analysis-tabs">
                <button class="analysis-tab active" onclick="switchTab('cross', this)">
                    <i class="bi bi-arrow-left-right"></i> Crois&eacute; interne
                    <span class="tab-count">{{ $cross_analysis['matches_found'] ?? 0 }}</span>
                </button>
                <button class="analysis-tab" onclick="switchTab('perfile', this)">
                    <i class="bi bi-file-earmark-break"></i> Par fichier vs Base
                    <span class="tab-count">{{ $per_file_analysis['files_analyzed'] ?? 0 }}</span>
                </button>
                <button class="analysis-tab" onclick="switchTab('images', this)">
                    <i class="bi bi-image"></i> Images
                    <span class="tab-count">{{ $image_analysis['matches_found'] ?? 0 }}</span>
                </button>
            </div>

            {{-- ===== TAB 1: CROSS FILE ANALYSIS ===== --}}
            <div class="tab-content active" id="tab-cross">
                @if(count($cross_matches) > 0)
                    <div class="section-title" style="font-size:0.9rem;"><i class="bi bi-arrow-left-right"></i> Comparaison crois&eacute;e entre fichiers du ZIP</div>
                    @foreach($cross_matches as $cm)
                        @php
                            $cmScore = round(($cm['combined_score'] ?? 0) * 100, 1);
                            $cmLevel = $cm['level'] ?? 'none';
                            $cmColor = match($cmLevel) {
                                'critical' => '#ef4444',
                                'high' => '#f97316',
                                'medium' => '#f59e0b',
                                default => '#94a3b8'
                            };
                        @endphp
                        <div class="cross-card">
                            <div class="cross-accent" style="background: {{ $cmColor }};"></div>
                            <div class="cross-header">
                                <div class="cross-pair">
                                    <div class="file-icon-mini code"><i class="bi bi-code-slash"></i></div>
                                    <strong>{{ $cm['file_a'] ?? '?' }}</strong>
                                    <i class="bi bi-arrow-left-right arrow-icon"></i>
                                    <strong>{{ $cm['file_b'] ?? '?' }}</strong>
                                    <span class="level-badge {{ $cmLevel }}">{{ $levelLabels[$cmLevel] ?? ucfirst($cmLevel) }}</span>
                                </div>
                                <div class="cross-score-big" style="color: {{ $cmColor }};">{{ $cmScore }}%</div>
                            </div>
                            <div class="cross-progress">
                                <div class="progress-track">
                                    <div class="progress-fill" style="width: {{ $cmScore }}%; background: {{ $cmColor }};"></div>
                                </div>
                            </div>
                            {{-- Engine details --}}
                            @if(isset($cm['engines']) && count($cm['engines']) > 0)
                                <div class="engine-details">
                                    <button class="engine-toggle" onclick="toggleEngine(this)">
                                        <i class="bi bi-chevron-down"></i> D&eacute;tails par moteur ({{ count($cm['engines']) }})
                                    </button>
                                    <div class="engine-table-wrap">
                                        <table class="engine-table">
                                            <thead>
                                                <tr><th>Moteur</th><th>Score brut</th><th>Poids</th><th>Contribution</th><th style="width:100px;">Visuel</th></tr>
                                            </thead>
                                            <tbody>
                                                @foreach($cm['engines'] as $eName => $eData)
                                                    @php $contribPct = ($eData['contribution'] ?? 0) * 100; @endphp
                                                    <tr>
                                                        <td><span class="engine-pill">{{ strtoupper($eName) }}</span></td>
                                                        <td>{{ round($eData['raw'] ?? 0, 4) }}</td>
                                                        <td>{{ $eData['weight'] ?? 0 }}</td>
                                                        <td>{{ round($eData['contribution'] ?? 0, 4) }}</td>
                                                        <td>
                                                            <div class="mini-progress">
                                                                <div class="fill" style="width: {{ min($contribPct, 100) }}%;"></div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                @else
                    <div class="no-matches">
                        <div class="icon-circle"><i class="bi bi-check-lg"></i></div>
                        <h4>Aucune similarit&eacute; crois&eacute;e</h4>
                        <p>Les fichiers du ZIP ne se ressemblent pas entre eux. Aucun copiage interne d&eacute;tect&eacute;.</p>
                    </div>
                @endif
            </div>

            {{-- ===== TAB 2: PER-FILE DATABASE ANALYSIS ===== --}}
            <div class="tab-content" id="tab-perfile">
                <div class="section-title" style="font-size:0.9rem;"><i class="bi bi-file-earmark-break"></i> Analyse par fichier vs Base de donn&eacute;es</div>
                @if(count($per_file_results) > 0)
                    @foreach($per_file_results as $pfr)
                        @php
                            $pfScore = round(($pfr['max_score'] ?? 0) * 100, 1);
                            $pfLevel = $pfr['max_level'] ?? 'none';
                            $pfColor = match($pfLevel) {
                                'critical' => '#ef4444',
                                'high' => '#f97316',
                                'medium' => '#f59e0b',
                                default => '#94a3b8'
                            };
                            $pfMatches = $pfr['matches'] ?? [];
                        @endphp
                        <div class="per-file-card">
                            <div class="per-file-header" onclick="togglePerFile(this)">
                                <div class="per-file-name">
                                    <div class="file-icon-mini {{ $pfr['file_type'] ?? 'text' === 'code' ? 'code' : 'text' }}">
                                        <i class="bi {{ ($pfr['file_type'] ?? 'text') === 'code' ? 'bi-code-slash' : 'bi-file-text' }}"></i>
                                    </div>
                                    <div>
                                        <strong>{{ $pfr['filename'] ?? 'inconnu' }}</strong>
                                        <div style="font-size:0.72rem; color:var(--muted); margin-top:0.1rem;">
                                            {{ $pfr['file_type'] ?? 'text' }}
                                            @if(isset($pfr['content_length']))
                                                &bull; {{ number_format($pfr['content_length'] / 1024, 1) }} Ko
                                            @endif
                                            @if(($pfr['best_match'] ?? null))
                                                &bull; Meilleur match: <em>{{ $pfr['best_match'] }}</em>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="per-file-score">
                                    <span class="level-badge {{ $pfLevel }}">{{ $levelLabels[$pfLevel] ?? ucfirst($pfLevel) }}</span>
                                    <div class="cross-score-big" style="color: {{ $pfColor }};">{{ $pfScore }}%</div>
                                    <div class="progress-track" style="width:100px;">
                                        <div class="progress-fill" style="width: {{ $pfScore }}%; background: {{ $pfColor }};"></div>
                                    </div>
                                    <i class="bi bi-chevron-down toggle-arrow" style="color:var(--muted); transition:transform 0.3s;"></i>
                                </div>
                            </div>
                            {{-- Expandable matches --}}
                            <div class="per-file-matches-wrap">
                                <div class="per-file-matches-inner">
                                    @if(count($pfMatches) > 0)
                                        <div style="font-size:0.72rem; font-weight:600; color:var(--muted); margin-bottom:0.5rem;">
                                            {{ count($pfMatches) }} correspondance(s) trouv&eacute;e(s) dans la base
                                        </div>
                                        @foreach($pfMatches as $pfm)
                                            @php
                                                $pfmScore = round(($pfm['combined_score'] ?? 0) * 100, 1);
                                                $pfmLevel = $pfm['level'] ?? 'none';
                                                $pfmColor = match($pfmLevel) {
                                                    'critical' => '#ef4444',
                                                    'high' => '#f97316',
                                                    'medium' => '#f59e0b',
                                                    default => '#94a3b8'
                                                };
                                            @endphp
                                            <div class="per-file-match-row">
                                                <div style="width:36px; height:36px; border-radius:8px; background:{{ $pfmColor }}15; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                                    <i class="bi bi-file-text" style="color:{{ $pfmColor }};"></i>
                                                </div>
                                                <div style="flex:1; min-width:0;">
                                                    <strong style="font-size:0.82rem;">{{ $pfm['filename'] ?? '?' }}</strong>
                                                    <div style="font-size:0.7rem; color:var(--muted);">
                                                        ID: {{ $pfm['submission_id'] ?? 'N/A' }}
                                                        @if(isset($pfm['file_type']))
                                                            &bull; {{ $pfm['file_type'] }}
                                                        @endif
                                                    </div>
                                                </div>
                                                <div style="display:flex; align-items:center; gap:0.5rem;">
                                                    <span class="level-badge {{ $pfmLevel }}" style="font-size:0.58rem;">
                                                        {{ $levelLabels[$pfmLevel] ?? ucfirst($pfmLevel) }}
                                                    </span>
                                                    <strong style="color:{{ $pfmColor }}; font-size:1rem;">{{ $pfmScore }}%</strong>
                                                </div>
                                            </div>
                                            {{-- Engine details for this match --}}
                                            @if(isset($pfm['engines']) && count($pfm['engines']) > 0)
                                                <div class="engine-details" style="padding-left:3.5rem;">
                                                    <button class="engine-toggle" onclick="toggleEngine(this)">
                                                        <i class="bi bi-chevron-down"></i> Moteurs
                                                    </button>
                                                    <div class="engine-table-wrap">
                                                        <table class="engine-table">
                                                            <thead><tr><th>Moteur</th><th>Brut</th><th>Poids</th><th>Contrib.</th></tr></thead>
                                                            <tbody>
                                                                @foreach($pfm['engines'] as $en => $ed)
                                                                    <tr>
                                                                        <td><span class="engine-pill">{{ strtoupper($en) }}</span></td>
                                                                        <td>{{ round($ed['raw'] ?? 0, 4) }}</td>
                                                                        <td>{{ $ed['weight'] ?? 0 }}</td>
                                                                        <td>{{ round($ed['contribution'] ?? 0, 4) }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    @else
                                        <div style="text-align:center; padding:1.5rem; color:var(--muted); font-size:0.82rem;">
                                            <i class="bi bi-check-circle me-1"></i> Aucune correspondance pour ce fichier
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="no-matches">
                        <div class="icon-circle"><i class="bi bi-check-lg"></i></div>
                        <h4>Aucune comparaison effectu&eacute;e</h4>
                        <p>Aucun fichier texte/code n'a pu &ecirc;tre analys&eacute; ou la base est vide.</p>
                    </div>
                @endif
            </div>

            {{-- ===== TAB 3: IMAGE ANALYSIS ===== --}}
            <div class="tab-content" id="tab-images">
                <div class="image-analysis-section">
                    <h5><i class="bi bi-image"></i> Analyse d'images du ZIP</h5>
                    @if(isset($image_analysis['analyzed']) && $image_analysis['analyzed'])
                        <div style="display:flex; gap:1rem; flex-wrap:wrap; margin-bottom:1rem; font-size:0.78rem; color:var(--text-secondary);">
                            <span><i class="bi bi-collection-image me-1"></i> {{ $image_analysis['images_in_zip'] ?? 0 }} image(s) dans le ZIP</span>
                            <span><i class="bi bi-database me-1"></i> {{ $image_analysis['images_in_database'] ?? 0 }} image(s) en base</span>
                            <span><i class="bi bi-link-45deg me-1"></i> {{ $image_analysis['matches_found'] ?? 0 }} correspondance(s)</span>
                        </div>
                        @if(count($image_matches) > 0)
                            @foreach($image_matches as $imgM)
                                @php
                                    $iConf = ($imgM['confidence'] ?? 0) * 100;
                                    $iColor = $iConf >= 80 ? '#dc2626' : ($iConf >= 60 ? '#f97316' : '#3b82f6');
                                @endphp
                                <div class="img-match-card">
                                    <div class="img-icon"><i class="bi bi-images"></i></div>
                                    <div class="img-info">
                                        <strong>Image #{{ ($imgM['new_image_index'] ?? 0) + 1 }}</strong><br>
                                        <small>
                                            vs {{ $imgM['matched_filename'] ?? 'R\u00e9f\u00e9rence' }}
                                            @if(isset($imgM['source']) && $imgM['source'] === 'cross_zip')
                                                <span class="extraction-tag zip" style="font-size:0.58rem;"><i class="bi bi-zip"></i> Interne ZIP</span>
                                            @endif
                                            @if(isset($imgM['phash_distance']))
                                                &bull; pHash: {{ $imgM['phash_distance'] }}
                                            @endif
                                            @if(isset($imgM['feature_similarity']) && $imgM['feature_similarity'] !== null)
                                                &bull; Sim: {{ round(($imgM['feature_similarity']) * 100, 1) }}%
                                            @endif
                                        </small>
                                    </div>
                                    <span class="img-score-badge" style="background: {{ $iColor }};">
                                        {{ round($iConf, 1) }}%
                                    </span>
                                </div>
                            @endforeach
                        @else
                            <p style="margin:0; font-size:0.82rem; color:var(--muted);">
                                <i class="bi bi-check-circle me-1"></i> Aucune similarit&eacute; d'image d&eacute;tect&eacute;e.
                            </p>
                        @endif
                    @else
                        <p style="margin:0; font-size:0.82rem; color:var(--muted);">
                            <i class="bi bi-slash-circle me-1"></i> Analyse d'images non effectu&eacute;e (aucune image ou base vide).
                        </p>
                    @endif
                </div>
            </div>

            <hr class="section-divider">

            {{-- ===== RAW JSON ===== --}}
            <div class="raw-json-section">
                <button class="raw-json-toggle" onclick="toggleRawJson(this)">
                    <i class="bi bi-code-square"></i> Afficher le JSON brut de la r&eacute;ponse API
                </button>
                <div class="raw-json-wrap">
                    <pre class="raw-json-pre">{{ json_encode($raw_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            </div>

            {{-- ===== ACTIONS ===== --}}
            <div class="actions-bar">
                <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
                    <a href="{{ url('/') }}" class="btn-primary-custom">
                        <i class="bi bi-plus-circle"></i> Nouvelle analyse
                    </a>
                    <a href="{{ url('/upload') }}" class="btn-outline-custom">
                        <i class="bi bi-cloud-upload"></i> Uploader un fichier
                    </a>
                </div>
                <button class="btn-outline-custom" onclick="window.print()">
                    <i class="bi bi-printer"></i> Imprimer
                </button>
            </div>

            <div style="text-align:center; margin-top:2rem; padding-top:1.5rem; border-top:1px solid var(--border); font-size:0.75rem; color:var(--muted);">
                <i class="bi bi-shield-check me-1"></i> PlagioScan &mdash; Rapport g&eacute;n&eacute;r&eacute; le {{ now()->format('d/m/Y \&agrave; H:i') }}
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Tab switching
        function switchTab(tabId, btn) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.analysis-tab').forEach(el => el.classList.remove('active'));
            document.getElementById('tab-' + tabId).classList.add('active');
            btn.classList.add('active');
        }

        // Engine detail toggle
        function toggleEngine(btn) {
            btn.classList.toggle('active');
            const wrap = btn.nextElementSibling;
            wrap.classList.toggle('open');
        }

        // Per-file expand toggle
        function togglePerFile(header) {
            const card = header.closest('.per-file-card');
            const matchesWrap = card.querySelector('.per-file-matches-wrap');
            const arrow = card.querySelector('.toggle-arrow');
            matchesWrap.classList.toggle('open');
            if (matchesWrap.classList.contains('open')) {
                arrow.style.transform = 'rotate(180deg)';
            } else {
                arrow.style.transform = 'rotate(0deg)';
            }
        }

        // Raw JSON toggle
        function toggleRawJson(btn) {
            const wrap = btn.nextElementSibling;
            wrap.classList.toggle('open');
            const icon = btn.querySelector('i');
            if (wrap.classList.contains('open')) {
                icon.className = 'bi bi-code-square';
            } else {
                icon.className = 'bi bi-code-square';
            }
        }
    </script>
</body>
</html>
