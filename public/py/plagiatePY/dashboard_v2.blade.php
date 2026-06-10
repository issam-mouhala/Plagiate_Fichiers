{{-- resources/views/dashboard.blade.php --}}
{{-- PlagioScan — Modern Unified Dashboard — File + ZIP Upload + Analysis --}}
@extends('layouts.app')
@section('content')
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PlagioScan – Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0f172a;
            --primary-light: #1e293b;
            --accent: #3b82f6;
            --accent-light: #93c5fd;
            --accent-dark: #2563eb;
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
            --border-light: #f1f5f9;
            --muted: #64748b;
            --text: #0f172a;
            --text-secondary: #475569;
            --radius-sm: 0.75rem;
            --radius-md: 1rem;
            --radius-xl: 1.25rem;
            --radius-2xl: 1.5rem;
            --radius-3xl: 2rem;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.04);
            --shadow-md: 0 4px 16px -2px rgba(0,0,0,0.08);
            --shadow-lg: 0 12px 40px -8px rgba(0,0,0,0.12);
            --shadow-xl: 0 24px 60px -12px rgba(0,0,0,0.18);
            --sidebar-w: 260px;
            --header-h: 70px;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--text);
            background: #f1f5f9;
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }

        /* ========== BACKGROUND ========== */
        .bg-mesh {
            position: fixed; inset: 0; z-index: -1; overflow: hidden;
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 40%, #0f172a 100%);
        }
        .bg-mesh::before, .bg-mesh::after {
            content: ''; position: absolute; border-radius: 50%;
            filter: blur(120px); opacity: 0.12;
            animation: bgFloat 25s ease-in-out infinite;
        }
        .bg-mesh::before { width: 700px; height: 700px; background: var(--accent); top: -250px; right: -150px; }
        .bg-mesh::after { width: 600px; height: 600px; background: #8b5cf6; bottom: -200px; left: -150px; animation-delay: -12s; }
        @keyframes bgFloat {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(40px, -40px) scale(1.06); }
            66% { transform: translate(-30px, 30px) scale(0.94); }
        }

        /* ========== SIDEBAR ========== */
        .sidebar {
            position: fixed; left: 0; top: 0; bottom: 0;
            width: var(--sidebar-w); z-index: 100;
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px);
            border-right: 1px solid rgba(255,255,255,0.08);
            display: flex; flex-direction: column;
            transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .sidebar-brand {
            padding: 1.25rem 1.5rem; display: flex; align-items: center; gap: 0.75rem;
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }
        .sidebar-brand-icon {
            width: 42px; height: 42px; border-radius: 12px;
            background: linear-gradient(135deg, var(--accent), #8b5cf6);
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 1.3rem;
            box-shadow: 0 4px 16px rgba(59,130,246,0.35);
        }
        .sidebar-brand-name {
            font-size: 1.2rem; font-weight: 800; color: white;
        }
        .sidebar-brand-sub {
            font-size: 0.65rem; color: rgba(255,255,255,0.5); font-weight: 500;
        }
        .sidebar-nav { padding: 1rem 0.75rem; flex: 1; overflow-y: auto; }
        .sidebar-nav-label {
            font-size: 0.65rem; font-weight: 700; color: rgba(255,255,255,0.35);
            text-transform: uppercase; letter-spacing: 0.08em;
            padding: 0.75rem 0.75rem 0.5rem; margin-top: 0.5rem;
        }
        .sidebar-link {
            display: flex; align-items: center; gap: 0.75rem;
            padding: 0.7rem 1rem; border-radius: var(--radius-md);
            color: rgba(255,255,255,0.65); text-decoration: none;
            font-size: 0.82rem; font-weight: 500;
            transition: all 0.2s; margin-bottom: 0.15rem;
            cursor: pointer; border: none; background: none; width: 100%; text-align: left;
        }
        .sidebar-link:hover { background: rgba(255,255,255,0.08); color: white; }
        .sidebar-link.active {
            background: linear-gradient(135deg, var(--accent), #6366f1);
            color: white; box-shadow: 0 4px 12px rgba(59,130,246,0.3);
        }
        .sidebar-link i { font-size: 1.1rem; width: 20px; text-align: center; }
        .sidebar-link .link-badge {
            margin-left: auto; min-width: 22px; height: 22px; border-radius: 1rem;
            background: rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center;
            font-size: 0.65rem; font-weight: 700; padding: 0 0.35rem;
        }
        .sidebar-link.active .link-badge { background: rgba(255,255,255,0.25); }
        .sidebar-footer {
            padding: 1rem 1.25rem; border-top: 1px solid rgba(255,255,255,0.06);
        }
        .sidebar-footer-text { font-size: 0.68rem; color: rgba(255,255,255,0.35); }
        .api-status {
            display: flex; align-items: center; gap: 0.5rem;
            font-size: 0.72rem; color: rgba(255,255,255,0.5); margin-top: 0.5rem;
        }
        .api-dot {
            width: 8px; height: 8px; border-radius: 50%;
            animation: dotPulse 2s ease-in-out infinite;
        }
        .api-dot.online { background: var(--success); box-shadow: 0 0 8px rgba(16,185,129,0.5); }
        .api-dot.offline { background: var(--danger); box-shadow: 0 0 8px rgba(239,68,68,0.5); }
        .api-dot.checking { background: var(--warning); box-shadow: 0 0 8px rgba(245,158,11,0.5); }
        @keyframes dotPulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }

        /* ========== MAIN CONTENT ========== */
        .main-wrap {
            margin-left: var(--sidebar-w);
            min-height: 100vh;
            transition: margin-left 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .main-header {
            position: sticky; top: 0; z-index: 50;
            height: var(--header-h);
            background: rgba(255,255,255,0.85);
            backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 2rem;
        }
        .main-header-left { display: flex; align-items: center; gap: 1rem; }
        .main-header-title { font-size: 1.05rem; font-weight: 700; }
        .main-header-right { display: flex; align-items: center; gap: 0.75rem; }
        .header-btn {
            width: 40px; height: 40px; border-radius: var(--radius-md);
            border: 1px solid var(--border); background: white;
            display: flex; align-items: center; justify-content: center;
            color: var(--muted); font-size: 1.1rem; cursor: pointer;
            transition: all 0.2s;
        }
        .header-btn:hover { background: var(--surface-hover); color: var(--accent); border-color: var(--accent-light); }
        .mobile-toggle { display: none; }

        .main-content { padding: 2rem; max-width: 1280px; }

        /* ========== VIEW PANELS ========== */
        .view-panel { display: none; animation: fadeSlideIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) both; }
        .view-panel.active { display: block; }
        @keyframes fadeSlideIn { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }

        /* ========== UPLOAD PANEL ========== */
        .upload-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem; }

        .upload-card {
            background: var(--surface); border: 2px dashed var(--border);
            border-radius: var(--radius-2xl); padding: 2rem 2rem 1.5rem;
            text-align: center; cursor: pointer;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative; overflow: hidden;
        }
        .upload-card:hover {
            border-color: var(--accent-light); transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }
        .upload-card.dragover {
            border-color: var(--accent); border-style: solid;
            background: rgba(59,130,246,0.03);
        }
        .upload-card.dragover .upload-icon { transform: scale(1.15); }
        .upload-card-icon {
            width: 72px; height: 72px; border-radius: 20px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.8rem; margin: 0 auto 1.25rem;
            transition: transform 0.3s;
        }
        .upload-card-icon.file-icon {
            background: linear-gradient(135deg, #eff6ff, #dbeafe); color: var(--accent);
            box-shadow: 0 4px 16px rgba(59,130,246,0.15);
        }
        .upload-card-icon.zip-icon {
            background: linear-gradient(135deg, #fff7ed, #ffedd5); color: #ea580c;
            box-shadow: 0 4px 16px rgba(234,88,12,0.15);
        }
        .upload-card-title { font-size: 1.05rem; font-weight: 700; margin-bottom: 0.4rem; }
        .upload-card-desc { font-size: 0.78rem; color: var(--muted); line-height: 1.5; margin-bottom: 1rem; }
        .upload-card-formats {
            display: flex; flex-wrap: wrap; gap: 0.35rem; justify-content: center;
        }
        .upload-card-formats span {
            font-size: 0.65rem; font-weight: 600; padding: 0.2rem 0.55rem;
            border-radius: 0.5rem; background: var(--border-light);
            color: var(--text-secondary); border: 1px solid var(--border);
        }
        .upload-card input[type="file"] { display: none; }
        .upload-card .btn-upload {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.65rem 1.5rem; border-radius: 2rem;
            font-weight: 600; font-size: 0.82rem; border: none;
            transition: all 0.3s; margin-top: 1.25rem;
            text-decoration: none; cursor: pointer;
        }
        .upload-card .btn-upload.file-btn {
            background: linear-gradient(135deg, var(--accent), var(--accent-dark));
            color: white; box-shadow: 0 4px 12px rgba(59,130,246,0.3);
        }
        .upload-card .btn-upload.file-btn:hover { box-shadow: 0 8px 24px rgba(59,130,246,0.4); transform: translateY(-2px); }
        .upload-card .btn-upload.zip-btn {
            background: linear-gradient(135deg, #f97316, #ea580c);
            color: white; box-shadow: 0 4px 12px rgba(249,115,22,0.3);
        }
        .upload-card .btn-upload.zip-btn:hover { box-shadow: 0 8px 24px rgba(249,115,22,0.4); transform: translateY(-2px); }

        /* Selected file info */
        .selected-file-info {
            display: none; margin-top: 1rem; padding: 0.75rem 1rem;
            background: var(--border-light); border-radius: var(--radius-md);
            font-size: 0.78rem; color: var(--text-secondary);
        }
        .selected-file-info.visible { display: flex; align-items: center; gap: 0.5rem; animation: fadeSlideIn 0.3s ease; }
        .selected-file-info i { color: var(--success); }

        /* ========== QUICK STATS BAR ========== */
        .quick-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 2rem; }
        .quick-stat-card {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: var(--radius-xl); padding: 1.25rem 1.5rem;
            display: flex; align-items: center; gap: 1rem;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .quick-stat-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); border-color: var(--accent-light); }
        .quick-stat-icon {
            width: 48px; height: 48px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0;
        }
        .quick-stat-icon.blue { background: #eff6ff; color: var(--accent); }
        .quick-stat-icon.green { background: #ecfdf5; color: var(--success); }
        .quick-stat-icon.purple { background: #f5f3ff; color: #8b5cf6; }
        .quick-stat-icon.orange { background: #fff7ed; color: #f97316; }
        .quick-stat-value { font-size: 1.5rem; font-weight: 800; line-height: 1.2; }
        .quick-stat-label { font-size: 0.72rem; color: var(--muted); font-weight: 500; }

        /* ========== RESULTS PANEL ========== */
        .results-container {
            background: rgba(255,255,255,0.92);
            backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255,255,255,0.5);
            border-radius: var(--radius-3xl);
            box-shadow: var(--shadow-xl);
            padding: 2.5rem;
            animation: cardIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
        }
        @keyframes cardIn { from { opacity: 0; transform: translateY(30px) scale(0.98); } to { opacity: 1; transform: translateY(0) scale(1); } }

        /* RESULTS HEADER */
        .results-header {
            display: flex; justify-content: space-between; align-items: flex-start;
            flex-wrap: wrap; gap: 1rem; padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--border); margin-bottom: 2rem;
        }
        .results-badge {
            display: inline-flex; align-items: center; gap: 0.4rem;
            padding: 0.3rem 0.9rem; border-radius: 2rem;
            font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em;
        }
        .results-badge.file { background: linear-gradient(135deg, #eff6ff, #e0e7ff); color: var(--accent); }
        .results-badge.zip { background: linear-gradient(135deg, #fff7ed, #ffedd5); color: #ea580c; }
        .results-meta { font-size: 0.8rem; color: var(--muted); display: flex; flex-direction: column; align-items: flex-end; gap: 0.25rem; }

        /* SECTION TITLE */
        .section-title {
            font-size: 1rem; font-weight: 700; color: var(--text);
            margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.5rem;
        }
        .section-title i { color: var(--accent); font-size: 1.1rem; }

        /* STAT CARDS */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
        .stat-card {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: var(--radius-xl); padding: 1.25rem 1.5rem;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative; overflow: hidden;
        }
        .stat-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, var(--accent), #8b5cf6); opacity: 0; transition: opacity 0.3s; }
        .stat-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); border-color: var(--accent-light); }
        .stat-card:hover::before { opacity: 1; }
        .stat-icon { width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; margin-bottom: 0.75rem; }
        .stat-icon.blue { background: #eff6ff; color: var(--accent); }
        .stat-icon.green { background: #ecfdf5; color: var(--success); }
        .stat-icon.purple { background: #f5f3ff; color: #8b5cf6; }
        .stat-icon.orange { background: #fff7ed; color: #f97316; }
        .stat-icon.cyan { background: #ecfeff; color: var(--info); }
        .stat-icon.red { background: #fef2f2; color: var(--danger); }
        .stat-value { font-size: 1.3rem; font-weight: 800; color: var(--text); line-height: 1.2; word-break: break-all; }
        .stat-label { font-size: 0.78rem; color: var(--muted); font-weight: 500; margin-top: 0.15rem; }
        .stat-detail { font-size: 0.72rem; color: var(--text-secondary); margin-top: 0.5rem; line-height: 1.5; }

        .extraction-bar { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.6rem; }
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
        .engine-pill {
            display: inline-block; padding: 0.12rem 0.5rem; border-radius: 0.75rem;
            font-size: 0.65rem; font-weight: 700; background: #f1f5f9; color: var(--text); border: 1px solid var(--border);
        }

        /* SCORE SECTION */
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

        /* MATCHES */
        .matches-list { display: flex; flex-direction: column; gap: 1rem; }
        .match-card {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: var(--radius-xl); overflow: hidden;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            animation: slideUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
            position: relative;
        }
        .match-card:hover { border-color: var(--accent-light); box-shadow: var(--shadow-lg); transform: translateX(4px); }
        .match-accent { position: absolute; left: 0; top: 0; bottom: 0; width: 4px; }
        .match-header { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; padding: 1.25rem 1.5rem 0; }
        .match-filename { display: flex; align-items: center; gap: 0.6rem; }
        .match-filename strong { font-size: 0.95rem; }
        .match-score-big { font-size: 1.75rem; font-weight: 800; }
        .match-progress { padding: 0.75rem 1.5rem; }
        .progress-track { height: 6px; background: var(--border); border-radius: 1rem; overflow: hidden; }
        .progress-fill { height: 100%; border-radius: 1rem; transition: width 1s cubic-bezier(0.16, 1, 0.3, 1); }
        @keyframes slideUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }

        .engine-details { padding: 0 1.5rem 1.25rem; }
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
        .match-footer { display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 1.5rem 1rem; font-size: 0.72rem; color: var(--muted); }

        /* IMAGE ANALYSIS */
        .image-analysis {
            background: linear-gradient(135deg, #faf5ff, #f1f5f9);
            border: 1px solid #e9d5ff; border-radius: var(--radius-2xl); padding: 1.5rem; margin-bottom: 2rem;
        }
        .image-analysis h5 { font-size: 0.9rem; font-weight: 700; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; }
        .image-analysis h5 i { color: #8b5cf6; }
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
        .img-compare-row { display: flex; gap: 1rem; align-items: center; width: 100%; margin-top: 0.5rem; flex-wrap: wrap; }
        .img-compare-box { flex: 1; min-width: 140px; max-width: 260px; text-align: center; }
        .img-compare-box img { max-width: 100%; max-height: 180px; border-radius: 10px; border: 2px solid #e9d5ff; object-fit: contain; background: #f9fafb; }
        .img-compare-box .img-label { font-size: 0.7rem; color: var(--muted); margin-top: 0.3rem; }
        .img-vs-label { font-weight: 800; color: #8b5cf6; font-size: 0.9rem; flex-shrink: 0; padding: 0 0.25rem; }

        /* NO MATCHES */
        .no-matches { text-align: center; padding: 3rem 2rem; background: var(--success-bg); border: 1px solid #a7f3d0; border-radius: var(--radius-2xl); }
        .no-matches .icon-circle { width: 72px; height: 72px; border-radius: 50%; background: var(--success); display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; color: white; font-size: 2rem; }
        .no-matches h4 { font-weight: 700; color: #065f46; margin-bottom: 0.3rem; }
        .no-matches p { color: var(--muted); font-size: 0.85rem; margin: 0; }

        /* ZIP FILE TABLE */
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
        .ext-badge { display: inline-block; padding: 0.1rem 0.45rem; border-radius: 0.5rem; font-size: 0.65rem; font-weight: 700; background: #f1f5f9; color: var(--text-secondary); border: 1px solid var(--border); }

        /* ZIP CROSS MATCH CARDS */
        .cross-card {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: var(--radius-xl); overflow: hidden;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); position: relative; margin-bottom: 1rem;
        }
        .cross-card:hover { border-color: var(--accent-light); box-shadow: var(--shadow-lg); transform: translateX(4px); }
        .cross-accent { position: absolute; left: 0; top: 0; bottom: 0; width: 4px; }
        .cross-header { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; padding: 1.25rem 1.5rem 0.5rem; }
        .cross-pair { display: flex; align-items: center; gap: 0.6rem; font-size: 0.9rem; }
        .cross-pair strong { font-size: 0.88rem; }
        .cross-pair .arrow-icon { color: var(--muted); font-size: 0.8rem; }
        .cross-score-big { font-size: 1.6rem; font-weight: 800; }
        .cross-progress { padding: 0.5rem 1.5rem; }

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
        .tab-content.active { display: block; animation: fadeSlideIn 0.3s ease; }

        /* PER-FILE ACCORDION */
        .per-file-card {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: var(--radius-xl); overflow: hidden; margin-bottom: 1rem;
            transition: all 0.3s;
        }
        .per-file-card:hover { box-shadow: var(--shadow-md); }
        .per-file-header {
            display: flex; justify-content: space-between; align-items: center;
            flex-wrap: wrap; gap: 0.75rem; padding: 1.25rem 1.5rem;
            cursor: pointer; transition: background 0.2s;
        }
        .per-file-header:hover { background: var(--surface-hover); }
        .per-file-matches-wrap { max-height: 0; overflow: hidden; transition: max-height 0.5s cubic-bezier(0.16, 1, 0.3, 1); }
        .per-file-matches-wrap.open { max-height: 5000px; }
        .per-file-matches-inner { padding: 0 1.5rem 1.25rem; border-top: 1px solid var(--border); }
        .per-file-match-row {
            display: flex; align-items: center; gap: 0.75rem; padding: 0.6rem 0;
            border-bottom: 1px solid #f1f5f9; font-size: 0.8rem;
        }
        .per-file-match-row:last-child { border-bottom: none; }

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
            text-decoration: none; transition: all 0.3s; cursor: pointer;
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
            font-size: 0.72rem; font-family: 'Fira Code', 'Courier New', monospace; overflow: auto;
            max-height: 500px; line-height: 1.6; margin-top: 0.5rem; white-space: pre-wrap; word-break: break-all;
        }

        /* ========== LOADING OVERLAY ========== */
        .loading-overlay {
            display: none; position: fixed; inset: 0; z-index: 9999;
            background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(8px);
            align-items: center; justify-content: center; flex-direction: column; gap: 1.5rem;
        }
        .loading-overlay.visible { display: flex; }
        .loading-spinner {
            width: 64px; height: 64px; border: 4px solid rgba(255,255,255,0.15);
            border-top-color: var(--accent); border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .loading-text { color: white; font-size: 1rem; font-weight: 600; }
        .loading-sub { color: rgba(255,255,255,0.6); font-size: 0.82rem; }

        /* ========== HISTORY PANEL ========== */
        .history-empty {
            text-align: center; padding: 3rem 2rem; color: var(--muted);
        }
        .history-empty i { font-size: 3rem; margin-bottom: 1rem; display: block; opacity: 0.3; }
        .history-empty h5 { font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem; }
        .history-empty p { font-size: 0.82rem; }

        /* ========== RESPONSIVE ========== */
        @media (max-width: 1024px) {
            .upload-grid { grid-template-columns: 1fr; }
            .quick-stats { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-wrap { margin-left: 0; }
            .mobile-toggle { display: flex; }
            .main-content { padding: 1.25rem; }
            .results-container { padding: 1.5rem; }
            .score-section { grid-template-columns: 1fr; text-align: center; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
            .analysis-tabs { flex-wrap: wrap; }
            .analysis-tab { flex: 1 1 33%; border-bottom: 1px solid var(--border); }
        }
        @media (max-width: 480px) {
            .quick-stats { grid-template-columns: 1fr; }
            .stats-grid { grid-template-columns: 1fr; }
            .main-header { padding: 0 1rem; }
        }
        @media print {
            body { background: white !important; }
            .bg-mesh, .sidebar, .main-header, .loading-overlay { display: none !important; }
            .main-wrap { margin-left: 0 !important; }
            .results-container { box-shadow: none; backdrop-filter: none; animation: none; border: 1px solid #ddd; padding: 1.5rem; }
            .btn-primary-custom, .btn-outline-custom, .engine-toggle, .raw-json-toggle { display: none; }
            .match-card:hover, .stat-card:hover { transform: none; box-shadow: none; }
        }
    </style>
</head>
<body>
    <!-- Background mesh -->
    <div class="bg-mesh"></div>

    <!-- Loading overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
        <div class="loading-text">Analyse en cours...</div>
        <div class="loading-sub" id="loadingSub">Veuillez patienter, traitement du fichier</div>
    </div>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-brand-icon"><i class="bi bi-shield-shaded"></i></div>
            <div>
                <div class="sidebar-brand-name">PlagioScan</div>
                <div class="sidebar-brand-sub">Anti-Plagiat Engine</div>
            </div>
        </div>
        <nav class="sidebar-nav">
            <div class="sidebar-nav-label">Navigation</div>
            <button class="sidebar-link active" data-panel="upload" onclick="switchPanel('upload', this)">
                <i class="bi bi-cloud-arrow-up"></i> Nouvelle analyse
            </button>
            <button class="sidebar-link" data-panel="results" id="navResults" onclick="switchPanel('results', this)" style="display:none;">
                <i class="bi bi-bar-chart-line"></i> Resultats
                <span class="link-badge" id="resultsBadge" style="display:none;">!</span>
            </button>
            <div class="sidebar-nav-label">Informations</div>
            <button class="sidebar-link" onclick="checkApiStatus()">
                <i class="bi bi-hdd-rack"></i> Statut API
            </button>
            <button class="sidebar-link" onclick="window.print()">
                <i class="bi bi-printer"></i> Imprimer
            </button>
        </nav>
        <div class="sidebar-footer">
            <div class="sidebar-footer-text">PlagioScan v4 &mdash; 5 Engines</div>
            <div class="api-status">
                <div class="api-dot checking" id="apiDot"></div>
                <span id="apiStatusText">V&eacute;rification...</span>
            </div>
        </div>
    </aside>

    <!-- Main -->
    <div class="main-wrap" id="mainWrap">
        <!-- Header -->
        <header class="main-header">
            <div class="main-header-left">
                <button class="header-btn mobile-toggle" onclick="toggleSidebar()">
                    <i class="bi bi-list"></i>
                </button>
                <div class="main-header-title" id="headerTitle">
                    <i class="bi bi-cloud-arrow-up" style="color: var(--accent); margin-right: 0.3rem;"></i> Nouvelle analyse
                </div>
            </div>
            <div class="main-header-right">
                <button class="header-btn" title="Imprimer" onclick="window.print()">
                    <i class="bi bi-printer"></i>
                </button>
                <button class="header-btn" title="Nouvelle analyse" onclick="resetDashboard()">
                    <i class="bi bi-plus-lg"></i>
                </button>
            </div>
        </header>

        <!-- Content -->
        <main class="main-content">

            <!-- ===== PANEL: UPLOAD ===== -->
            <div class="view-panel active" id="panelUpload">

                <!-- Quick Stats -->
                <div class="quick-stats">
                    <div class="quick-stat-card">
                        <div class="quick-stat-icon blue"><i class="bi bi-cpu"></i></div>
                        <div>
                            <div class="quick-stat-value">5</div>
                            <div class="quick-stat-label">Moteurs d'analyse</div>
                        </div>
                    </div>
                    <div class="quick-stat-card">
                        <div class="quick-stat-icon green"><i class="bi bi-speedometer2"></i></div>
                        <div>
                            <div class="quick-stat-value">Multi</div>
                            <div class="quick-stat-label">Niveaux de d&eacute;tection</div>
                        </div>
                    </div>
                    <div class="quick-stat-card">
                        <div class="quick-stat-icon purple"><i class="bi bi-images"></i></div>
                        <div>
                            <div class="quick-stat-value">pHash</div>
                            <div class="quick-stat-label">Analyse d'images</div>
                        </div>
                    </div>
                    <div class="quick-stat-card">
                        <div class="quick-stat-icon orange"><i class="bi bi-file-earmark-zip"></i></div>
                        <div>
                            <div class="quick-stat-value">ZIP</div>
                            <div class="quick-stat-label">Analyse batch</div>
                        </div>
                    </div>
                </div>

                <!-- Upload cards -->
                <div class="upload-grid">
                    <!-- File upload -->
                    <div class="upload-card" id="dropFile" onclick="document.getElementById('fileInput').click()">
                        <div class="upload-card-icon file-icon"><i class="bi bi-file-earmark-text"></i></div>
                        <div class="upload-card-title">Analyser un fichier</div>
                        <div class="upload-card-desc">
                            Glissez-d&eacute;posez un fichier ou cliquez pour parcourir.<br>
                            Analyse avec 5 moteurs : TF-IDF, BERT, Winnowing, AST, LCS.
                        </div>
                        <div class="upload-card-formats">
                            <span>PDF</span><span>DOCX</span><span>TXT</span><span>PY</span><span>JAVA</span><span>C</span><span>JS</span>
                        </div>
                        <div class="selected-file-info" id="selectedFileInfo">
                            <i class="bi bi-check-circle-fill"></i>
                            <span id="selectedFileName">Aucun fichier</span>
                        </div>
                        <form id="fileForm" method="POST" action="{{ route('dashboard.analyse') }}" enctype="multipart/form-data">
                            @csrf
                            <input type="file" name="submission" id="fileInput" accept=".txt,.pdf,.docx,.py,.java,.c,.cpp,.js,.ts,.php,.rb,.go,.rs,.html,.css,.md,.json,.xml,.sql,.r,.m,.sh,.bat">
                            <button type="submit" class="btn-upload file-btn" id="fileBtnSubmit" onclick="event.stopPropagation();">
                                <i class="bi bi-search"></i> Lancer l'analyse
                            </button>
                        </form>
                    </div>

                    <!-- ZIP upload -->
                    <div class="upload-card" id="dropZip" onclick="document.getElementById('zipInput').click()">
                        <div class="upload-card-icon zip-icon"><i class="bi bi-file-earmark-zip"></i></div>
                        <div class="upload-card-title">Analyser une archive ZIP</div>
                        <div class="upload-card-desc">
                            Glissez-d&eacute;posez un ZIP contenant plusieurs fichiers.<br>
                            Croisement interne + comparaison base de donn&eacute;es.
                        </div>
                        <div class="upload-card-formats">
                            <span>ZIP</span><span>MAX 50 Mo</span>
                        </div>
                        <div class="selected-file-info" id="selectedZipInfo">
                            <i class="bi bi-check-circle-fill"></i>
                            <span id="selectedZipName">Aucun fichier</span>
                        </div>
                        <form id="zipForm" method="POST" action="{{ route('dashboard.analyse-zip') }}" enctype="multipart/form-data">
                            @csrf
                            <input type="file" name="submission" id="zipInput" accept=".zip">
                            <button type="submit" class="btn-upload zip-btn" id="zipBtnSubmit" onclick="event.stopPropagation();">
                                <i class="bi bi-archive"></i> Lancer l'analyse ZIP
                            </button>
                        </form>
                    </div>
                </div>

                <!-- History section -->
                <div class="section-title"><i class="bi bi-clock-history"></i> Analyses r&eacute;centes</div>
                <div class="history-empty" id="historyEmpty">
                    <i class="bi bi-inbox"></i>
                    <h5>Aucune analyse r&eacute;cente</h5>
                    <p>Vos analyses appara&icirc;tront ici apr&egrave;s le premier traitement.</p>
                </div>
            </div>

            <!-- ===== PANEL: RESULTS (Single File) ===== -->
            <div class="view-panel" id="panelResults">
                <div class="results-container">
                    @if(isset($result_mode) && $result_mode === 'file')
                        {{-- FILE RESULTS --}}
                        <div class="results-header">
                            <div class="d-flex align-items-center gap-2">
                                <div class="sidebar-brand-icon" style="width:36px;height:36px;border-radius:10px;font-size:1.1rem;">
                                    <i class="bi bi-shield-shaded"></i>
                                </div>
                                <div>
                                    <div style="font-size:1.15rem;font-weight:800;color:var(--text);">PlagioScan</div>
                                    <div style="font-size:0.68rem;color:var(--muted);">Rapport d'analyse</div>
                                </div>
                            </div>
                            <div>
                                <span class="results-badge file"><i class="bi bi-file-earmark-text"></i> Fichier unique</span>
                                <div class="results-meta" style="margin-top:0.5rem;">
                                    <span><i class="bi bi-clock me-1"></i>{{ now()->format('d/m/Y \&agrave; H:i') }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- File Info --}}
                        <div class="section-title"><i class="bi bi-info-circle"></i> Informations du fichier</div>
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-icon blue"><i class="bi bi-file-earmark-text"></i></div>
                                <div class="stat-value">{{ $filename ?? 'N/A' }}</div>
                                <div class="stat-label">Fichier analys&eacute;</div>
                                <div class="stat-detail">
                                    Type : {{ ($file_type ?? 'text') === 'code' ? 'Code source' : 'Texte' }}
                                    <br>Taille : {{ number_format(($content_length ?? 0) / 1024, 2) }} Ko
                                </div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-icon green"><i class="bi bi-database"></i></div>
                                <div class="stat-value">{{ $num_comparisons ?? 0 }}</div>
                                <div class="stat-label">Documents compar&eacute;s</div>
                                <div class="stat-detail">Base de r&eacute;f&eacute;rence statique</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-icon purple"><i class="bi bi-cpu"></i></div>
                                <div class="stat-value">{{ count($engines_used ?? []) }}</div>
                                <div class="stat-label">Moteurs actifs</div>
                                <div class="stat-detail">
                                    @foreach($engines_used as $engine => $active)
                                        <span class="engine-pill">{{ strtoupper($engine) }}</span>
                                    @endforeach
                                    @if(empty($engines_used))
                                        <span class="extraction-tag">Aucun (base vide)</span>
                                    @endif
                                </div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-icon orange"><i class="bi bi-extract"></i></div>
                                <div class="stat-value">
                                    @if(is_array($extraction ?? null) && count($extraction ?? []) > 0)
                                        {{ ucfirst($extraction['format'] ?? 'N/A') }}
                                    @else
                                        Texte
                                    @endif
                                </div>
                                <div class="stat-label">Extraction</div>
                                <div class="extraction-bar">
                                    @if(is_array($extraction ?? null) && count($extraction ?? []) > 0)
                                        @if(($extraction['format'] ?? '') === 'pdf')
                                            <span class="extraction-tag pdf"><i class="bi bi-filetype-pdf"></i> PDF</span>
                                            <span class="extraction-tag"><i class="bi bi-file-text"></i> {{ $extraction['pages'] ?? 0 }} page(s)</span>
                                        @elseif(($extraction['format'] ?? '') === 'docx')
                                            <span class="extraction-tag docx"><i class="bi bi-filetype-docx"></i> Word</span>
                                        @endif
                                        @if(($images_extracted ?? 0) > 0)
                                            <span class="extraction-tag img"><i class="bi bi-image"></i> {{ $images_extracted }} image(s)</span>
                                        @endif
                                    @else
                                        <span class="extraction-tag">Contenu textuel direct</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Image Analysis --}}
                        @if(isset($image_analysis) && is_array($image_analysis) && isset($image_analysis['analyzed']) && $image_analysis['analyzed'])
                            <div class="image-analysis">
                                <h5><i class="bi bi-image"></i> Analyse d'images</h5>
                                @if(count($image_matches ?? []) > 0)
                                    @foreach($image_matches as $imgMatch)
                                        @php
                                            $imgConf = ($imgMatch['confidence'] ?? 0) * 100;
                                            $imgColor = $imgConf >= 80 ? '#dc2626' : ($imgConf >= 60 ? '#f97316' : '#3b82f6');
                                            $imgLevelLabels = ['critical' => 'Critique', 'high' => 'Élevé', 'medium' => 'Moyen', 'low' => 'Faible'];
                                        @endphp
                                        <div class="img-match-card">
                                            <div class="img-icon"><i class="bi bi-images"></i></div>
                                            <div class="img-info">
                                                <strong>Image #{{ ($imgMatch['new_image_index'] ?? 0) + 1 }}</strong><br>
                                                <small>
                                                    vs {{ $imgMatch['matched_filename'] ?? 'Référence' }}
                                                    &bull; {{ $imgLevelLabels[$imgMatch['level'] ?? 'low'] ?? ucfirst($imgMatch['level'] ?? 'low') }}
                                                    @if(isset($imgMatch['phash_distance']))
                                                        &bull; pHash: {{ $imgMatch['phash_distance'] }}
                                                    @endif
                                                </small>
                                            </div>
                                            <span class="img-score-badge" style="background: {{ $imgColor }};">{{ round($imgConf, 1) }}%</span>
                                        </div>
                                    @endforeach
                                @else
                                    <p style="margin:0;font-size:0.82rem;color:var(--muted);">
                                        <i class="bi bi-check-circle me-1"></i> Aucune similarit&eacute; d'image d&eacute;tect&eacute;e.
                                    </p>
                                @endif
                            </div>
                        @endif

                        {{-- Score --}}
                        <div class="section-title"><i class="bi bi-speedometer2"></i> Score de similarit&eacute;</div>
                        <div class="score-section">
                            <div style="text-align: center;">
                                @php
                                    $circumference = 2 * pi() * 80;
                                    $offset = $circumference - (($overall_score ?? 0) * $circumference);
                                    $scoreColor = ($overall_score ?? 0) >= 0.6 ? '#ef4444' : (($overall_score ?? 0) >= 0.4 ? '#f59e0b' : '#10b981');
                                @endphp
                                <div class="score-ring">
                                    <svg viewBox="0 0 180 180">
                                        <circle class="track" cx="90" cy="90" r="80" />
                                        <circle class="progress-arc" cx="90" cy="90" r="80"
                                                style="stroke: {{ $scoreColor }}; stroke-dashoffset: {{ $offset }};" />
                                    </svg>
                                    <div class="score-text">
                                        <div class="score-number" style="color: {{ $scoreColor }};">{{ round(($overall_score ?? 0) * 100) }}</div>
                                        <div class="score-unit">% de similarit&eacute;</div>
                                    </div>
                                </div>
                                <div class="score-label">Score global d&eacute;tect&eacute;</div>
                                @if($plagiarism_detected ?? false)
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
                                @php
                                    $summaryLevels = [
                                        'critical' => ($content_analysis['summary']['critical'] ?? 0) + (($image_analysis['summary']['critical'] ?? 0)),
                                        'high'     => ($content_analysis['summary']['high'] ?? 0) + (($image_analysis['summary']['high'] ?? 0)),
                                        'medium'   => ($content_analysis['summary']['medium'] ?? 0) + (($image_analysis['summary']['medium'] ?? 0)),
                                        'low'      => ($content_analysis['summary']['low'] ?? 0) + (($image_analysis['summary']['low'] ?? 0)),
                                    ];
                                @endphp
                                <div style="font-size:0.85rem;font-weight:600;color:var(--text-secondary);margin-bottom:1rem;">
                                    R&eacute;partition des correspondances
                                </div>
                                <div class="row g-2">
                                    <div class="col-3">
                                        <div style="text-align:center;padding:1rem;background:var(--danger-bg);border:1px solid #fecaca;border-radius:var(--radius-xl);">
                                            <div style="font-size:1.5rem;font-weight:800;color:var(--danger);">{{ $summaryLevels['critical'] }}</div>
                                            <div style="font-size:0.65rem;font-weight:700;text-transform:uppercase;color:var(--muted);margin-top:0.2rem;">Critique</div>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div style="text-align:center;padding:1rem;background:#fff7ed;border:1px solid #fed7aa;border-radius:var(--radius-xl);">
                                            <div style="font-size:1.5rem;font-weight:800;color:#ea580c;">{{ $summaryLevels['high'] }}</div>
                                            <div style="font-size:0.65rem;font-weight:700;text-transform:uppercase;color:var(--muted);margin-top:0.2rem;">Élev&eacute;</div>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div style="text-align:center;padding:1rem;background:var(--warning-bg);border:1px solid #fde68a;border-radius:var(--radius-xl);">
                                            <div style="font-size:1.5rem;font-weight:800;color:var(--warning);">{{ $summaryLevels['medium'] }}</div>
                                            <div style="font-size:0.65rem;font-weight:700;text-transform:uppercase;color:var(--muted);margin-top:0.2rem;">Moyen</div>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div style="text-align:center;padding:1rem;background:#f1f5f9;border:1px solid var(--border);border-radius:var(--radius-xl);">
                                            <div style="font-size:1.5rem;font-weight:800;color:var(--muted);">{{ $summaryLevels['low'] }}</div>
                                            <div style="font-size:0.65rem;font-weight:700;text-transform:uppercase;color:var(--muted);margin-top:0.2rem;">Faible</div>
                                        </div>
                                    </div>
                                </div>
                                @if(is_array($content_analysis ?? null) && isset($content_analysis['max_score']))
                                    <div style="margin-top:1rem;padding:1rem;background:white;border-radius:var(--radius-xl);border:1px solid var(--border);font-size:0.78rem;">
                                        <strong><i class="bi bi-file-text me-1"></i> Analyse texte</strong>
                                        @if(isset($content_analysis['paragraphs_analysed']))
                                            &mdash; {{ $content_analysis['paragraphs_analysed'] }} paragraphe(s)
                                        @endif
                                        <br>Score max :
                                        <strong style="color: {{ ($content_analysis['max_score'] ?? 0) >= 0.6 ? 'var(--danger)' : 'var(--success)' }};">
                                            {{ round(($content_analysis['max_score'] ?? 0) * 100, 1) }}%
                                        </strong>
                                        <span class="level-badge {{ $content_analysis['max_level'] ?? 'none' }}" style="margin-left:0.3rem;">
                                            {{ ucfirst($content_analysis['max_level'] ?? 'none') }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Text Matches --}}
                        <div class="section-title"><i class="bi bi-files"></i> Correspondances d&eacute;taill&eacute;es (texte)</div>
                        @if(count($text_matches ?? []) > 0)
                            <div class="matches-list">
                                @foreach($text_matches as $match)
                                    @php
                                        $scorePercent = round(($match['combined_score'] ?? 0) * 100, 1);
                                        $level = $match['level'] ?? 'low';
                                        $borderColor = match($level) {
                                            'critical' => '#ef4444',
                                            'high' => '#f97316',
                                            'medium' => '#f59e0b',
                                            default => '#94a3b8'
                                        };
                                        $levelLabels = ['critical' => 'Critique', 'high' => 'Élevé', 'medium' => 'Moyen', 'low' => 'Faible', 'none' => 'Nul'];
                                    @endphp
                                    <div class="match-card">
                                        <div class="match-accent" style="background: {{ $borderColor }};"></div>
                                        <div class="match-header">
                                            <div class="match-filename">
                                                <i class="bi bi-file-text-fill" style="color: {{ $borderColor }};"></i>
                                                <strong>{{ $match['filename'] ?? 'Document inconnu' }}</strong>
                                                <span class="level-badge {{ $level }}">{{ $levelLabels[$level] ?? ucfirst($level) }}</span>
                                            </div>
                                            <div class="match-score-big" style="color: {{ $borderColor }};">{{ $scorePercent }}%</div>
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
                                                        <thead><tr><th>Moteur</th><th>Score brut</th><th>Poids</th><th>Contribution</th><th style="width:100px;">Visuel</th></tr></thead>
                                                        <tbody>
                                                            @foreach($match['engines'] as $engineName => $engineData)
                                                                <tr>
                                                                    <td><span class="engine-pill">{{ strtoupper($engineName) }}</span></td>
                                                                    <td>{{ round($engineData['raw'] ?? 0, 4) }}</td>
                                                                    <td>{{ $engineData['weight'] ?? 0 }}</td>
                                                                    <td>{{ round($engineData['contribution'] ?? 0, 4) }}</td>
                                                                    <td><div class="mini-progress"><div class="fill" style="width: {{ min(($engineData['contribution'] ?? 0) * 100, 100) }}%;"></div></div></td>
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
                                <h4>Aucune correspondance texte</h4>
                                <p>Le document ne pr&eacute;sente aucune similarit&eacute; significative avec la base.</p>
                            </div>
                        @endif

                    @elseif(isset($result_mode) && $result_mode === 'zip')
                        {{-- ZIP RESULTS --}}
                        <div class="results-header">
                            <div class="d-flex align-items-center gap-2">
                                <div class="sidebar-brand-icon" style="width:36px;height:36px;border-radius:10px;font-size:1.1rem;">
                                    <i class="bi bi-shield-shaded"></i>
                                </div>
                                <div>
                                    <div style="font-size:1.15rem;font-weight:800;color:var(--text);">PlagioScan</div>
                                    <div style="font-size:0.68rem;color:var(--muted);">Rapport d'analyse ZIP</div>
                                </div>
                            </div>
                            <div>
                                <span class="results-badge zip"><i class="bi bi-file-earmark-zip"></i> Archive ZIP</span>
                                <div class="results-meta" style="margin-top:0.5rem;">
                                    <span><i class="bi bi-clock me-1"></i>{{ now()->format('d/m/Y \&agrave; H:i') }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- ZIP Info --}}
                        <div class="section-title"><i class="bi bi-archive"></i> Informations de l'archive</div>
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-icon orange"><i class="bi bi-file-earmark-zip"></i></div>
                                <div class="stat-value">{{ $zip_filename ?? 'N/A' }}</div>
                                <div class="stat-label">Archive analys&eacute;e</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-icon blue"><i class="bi bi-file-earmark-text"></i></div>
                                <div class="stat-value">{{ $zip_info['total_files'] ?? 0 }}</div>
                                <div class="stat-label">Fichiers extraits</div>
                                <div class="stat-detail">
                                    {{ $zip_info['text_code_files'] ?? 0 }} texte/code &bull; {{ $zip_info['image_files'] ?? 0 }} image(s)
                                </div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-icon purple"><i class="bi bi-arrow-left-right"></i></div>
                                <div class="stat-value">{{ $cross_analysis['pairs_compared'] ?? 0 }}</div>
                                <div class="stat-label">Paires crois&eacute;es</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-icon green"><i class="bi bi-database"></i></div>
                                <div class="stat-value">{{ $per_file_analysis['files_analyzed'] ?? 0 }}</div>
                                <div class="stat-label">Fichiers vs Base</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-icon cyan"><i class="bi bi-images"></i></div>
                                <div class="stat-value">{{ $image_analysis['images_in_zip'] ?? 0 }}</div>
                                <div class="stat-label">Images dans le ZIP</div>
                                <div class="stat-detail">{{ $image_analysis['matches_found'] ?? 0 }} correspondance(s)</div>
                            </div>
                        </div>

                        {{-- Files table --}}
                        <div class="section-title"><i class="bi bi-list-ul"></i> Fichiers extraits</div>
                        <div class="file-table-wrap">
                            <table class="file-table">
                                <thead><tr><th>#</th><th>Fichier</th><th>Type</th><th>Ext</th><th>Taille</th><th>Images</th></tr></thead>
                                <tbody>
                                    @foreach($files_extracted ?? [] as $idx => $f)
                                        @php
                                            $fType = $f['file_type'] ?? 'text';
                                            $fExt = $f['extension'] ?? '';
                                            $fIcon = match($fType) { 'code' => 'code', 'image' => 'image', default => 'text' };
                                            if ($fExt === 'pdf') $fIcon = 'pdf';
                                            if ($fExt === 'docx') $fIcon = 'docx';
                                        @endphp
                                        <tr>
                                            <td style="color:var(--muted);font-weight:600;">{{ $idx + 1 }}</td>
                                            <td>
                                                <div style="display:flex;align-items:center;gap:0.6rem;">
                                                    <div class="file-icon-mini {{ $fIcon }}"><i class="bi {{ match($fIcon) { 'code' => 'bi-code-slash', 'image' => 'bi-image', 'pdf' => 'bi-filetype-pdf', 'docx' => 'bi-filetype-docx', default => 'bi-file-text' } }}"></i></div>
                                                    <strong style="font-size:0.85rem;">{{ $f['filename'] ?? 'inconnu' }}</strong>
                                                </div>
                                            </td>
                                            <td><span class="level-badge {{ $fType === 'code' ? 'low' : 'none' }}" style="font-size:0.6rem;">{{ ucfirst($fType) }}</span></td>
                                            <td><span class="ext-badge">{{ $fExt }}</span></td>
                                            <td>{{ number_format(($f['content_length'] ?? 0) / 1024, 1) }} Ko</td>
                                            <td style="text-align:center;">
                                                @if(($f['images_count'] ?? 0) > 0)
                                                    <span class="extraction-tag img"><i class="bi bi-image"></i> {{ $f['images_count'] }}</span>
                                                @else
                                                    <span style="color:var(--muted);">&mdash;</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <hr style="border:none;border-top:1px solid var(--border);margin:2rem 0;">

                        {{-- Score --}}
                        <div class="section-title"><i class="bi bi-speedometer2"></i> Score global</div>
                        <div class="score-section">
                            <div style="text-align:center;">
                                @php
                                    $circumference = 2 * pi() * 80;
                                    $offset = $circumference - (($overall_score ?? 0) * $circumference);
                                    $scoreColor = ($overall_score ?? 0) >= 0.6 ? '#ef4444' : (($overall_score ?? 0) >= 0.4 ? '#f59e0b' : '#10b981');
                                @endphp
                                <div class="score-ring">
                                    <svg viewBox="0 0 180 180">
                                        <circle class="track" cx="90" cy="90" r="80" />
                                        <circle class="progress-arc" cx="90" cy="90" r="80"
                                                style="stroke: {{ $scoreColor }}; stroke-dashoffset: {{ $offset }};" />
                                    </svg>
                                    <div class="score-text">
                                        <div class="score-number" style="color: {{ $scoreColor }};">{{ round(($overall_score ?? 0) * 100) }}</div>
                                        <div class="score-unit">% de similarit&eacute;</div>
                                    </div>
                                </div>
                                <div class="score-label">Score global du ZIP</div>
                                @if($plagiarism_detected ?? false)
                                    <div class="alert-plagiat danger"><i class="bi bi-exclamation-triangle-fill"></i> Plagiat d&eacute;tect&eacute;</div>
                                @else
                                    <div class="alert-plagiat success"><i class="bi bi-check-circle-fill"></i> Aucun plagiat significatif</div>
                                @endif
                            </div>
                            <div>
                                @php $levelLabels = ['critical' => 'Critique', 'high' => 'Élevé', 'medium' => 'Moyen', 'low' => 'Faible']; @endphp
                                <div style="font-size:0.85rem;font-weight:600;color:var(--text-secondary);margin-bottom:1rem;">R&eacute;sum&eacute; par source</div>
                                <div class="row g-2 mb-3">
                                    <div class="col-4">
                                        <div style="text-align:center;padding:1rem;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-xl);">
                                            <div style="font-size:0.65rem;font-weight:700;color:var(--muted);text-transform:uppercase;">Crois&eacute; ZIP</div>
                                            <div style="font-size:1.3rem;font-weight:800;margin-top:0.3rem;color:{{ ($cross_analysis['max_score'] ?? 0) >= 0.5 ? 'var(--danger)' : 'var(--success)' }};">
                                                {{ round(($cross_analysis['max_score'] ?? 0) * 100, 1) }}%
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div style="text-align:center;padding:1rem;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-xl);">
                                            <div style="font-size:0.65rem;font-weight:700;color:var(--muted);text-transform:uppercase;">Base</div>
                                            <div style="font-size:1.3rem;font-weight:800;margin-top:0.3rem;color:{{ ($per_file_analysis['max_score'] ?? 0) >= 0.5 ? 'var(--danger)' : 'var(--success)' }};">
                                                {{ round(($per_file_analysis['max_score'] ?? 0) * 100, 1) }}%
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div style="text-align:center;padding:1rem;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-xl);">
                                            <div style="font-size:0.65rem;font-weight:700;color:var(--muted);text-transform:uppercase;">Images</div>
                                            <div style="font-size:1.3rem;font-weight:800;margin-top:0.3rem;color:{{ ($image_analysis['max_score'] ?? 0) >= 0.5 ? 'var(--danger)' : 'var(--success)' }};">
                                                {{ round(($image_analysis['max_score'] ?? 0) * 100, 1) }}%
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div style="padding:0.75rem 1rem;background:white;border-radius:var(--radius-xl);border:1px solid var(--border);font-size:0.78rem;color:var(--text-secondary);">
                                    <i class="bi bi-info-circle me-1" style="color:var(--accent);"></i>
                                    Score global = max(croisement, base, images).
                                    Niveau : <span class="level-badge {{ $overall_level ?? 'none' }}">{{ $levelLabels[$overall_level ?? 'none'] ?? ucfirst($overall_level ?? 'none') }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Tabs --}}
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

                        {{-- Tab: Cross --}}
                        <div class="tab-content active" id="tab-cross">
                            @if(count($cross_matches ?? []) > 0)
                                @foreach($cross_matches as $cm)
                                    @php
                                        $cmScore = round(($cm['combined_score'] ?? 0) * 100, 1);
                                        $cmLevel = $cm['level'] ?? 'none';
                                        $cmColor = match($cmLevel) { 'critical' => '#ef4444', 'high' => '#f97316', 'medium' => '#f59e0b', default => '#94a3b8' };
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
                                        @if(isset($cm['engines']) && count($cm['engines']) > 0)
                                            <div class="engine-details">
                                                <button class="engine-toggle" onclick="toggleEngine(this)">
                                                    <i class="bi bi-chevron-down"></i> Moteurs ({{ count($cm['engines']) }})
                                                </button>
                                                <div class="engine-table-wrap">
                                                    <table class="engine-table">
                                                        <thead><tr><th>Moteur</th><th>Score</th><th>Poids</th><th>Contribution</th><th style="width:80px;">Visuel</th></tr></thead>
                                                        <tbody>
                                                            @foreach($cm['engines'] as $en => $ed)
                                                                <tr>
                                                                    <td><span class="engine-pill">{{ strtoupper($en) }}</span></td>
                                                                    <td>{{ round($ed['raw'] ?? 0, 4) }}</td>
                                                                    <td>{{ $ed['weight'] ?? 0 }}</td>
                                                                    <td>{{ round($ed['contribution'] ?? 0, 4) }}</td>
                                                                    <td><div class="mini-progress"><div class="fill" style="width: {{ min(($ed['contribution'] ?? 0) * 100, 100) }}%;"></div></div></td>
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
                                    <h4>Aucun plagiat crois&eacute;</h4>
                                    <p>Les fichiers du ZIP ne pr&eacute;sentent pas de similarit&eacute;s entre eux.</p>
                                </div>
                            @endif
                        </div>

                        {{-- Tab: Per-file --}}
                        <div class="tab-content" id="tab-perfile">
                            @if(count($per_file_results ?? []) > 0)
                                @foreach($per_file_results as $pfr)
                                    @php
                                        $pfrScore = round(($pfr['max_score'] ?? 0) * 100, 1);
                                        $pfrDetected = $pfr['plagiarism_detected'] ?? false;
                                        $pfrColor = $pfrScore >= 50 ? 'var(--danger)' : 'var(--success)';
                                    @endphp
                                    <div class="per-file-card">
                                        <div class="per-file-header" onclick="togglePerFile(this)">
                                            <div style="display:flex;align-items:center;gap:0.75rem;">
                                                <div class="file-icon-mini code"><i class="bi bi-file-text"></i></div>
                                                <strong style="font-size:0.9rem;">{{ $pfr['filename'] ?? 'Inconnu' }}</strong>
                                                @if($pfrDetected)
                                                    <span class="level-badge critical">Plagiat</span>
                                                @else
                                                    <span class="level-badge low">OK</span>
                                                @endif
                                            </div>
                                            <div style="display:flex;align-items:center;gap:1rem;">
                                                <strong style="font-size:1.2rem;color:{{ $pfrColor }};">{{ $pfrScore }}%</strong>
                                                <i class="bi bi-chevron-down" style="transition:transform 0.3s;"></i>
                                            </div>
                                        </div>
                                        <div class="per-file-matches-wrap">
                                            <div class="per-file-matches-inner">
                                                @if(count($pfr['matches'] ?? []) > 0)
                                                    @foreach($pfr['matches'] as $pm)
                                                        <div class="per-file-match-row">
                                                            <span style="color:var(--muted);font-weight:600;">{{ $pm['filename'] ?? '?' }}</span>
                                                            <span class="level-badge {{ $pm['level'] ?? 'low' }}" style="font-size:0.6rem;">{{ ucfirst($pm['level'] ?? 'low') }}</span>
                                                            <strong style="margin-left:auto;color:{{ ($pm['score'] ?? 0) >= 0.5 ? 'var(--danger)' : 'var(--success)' }};">
                                                                {{ round(($pm['score'] ?? 0) * 100, 1) }}%
                                                            </strong>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <p style="margin:0;padding:0.5rem;font-size:0.8rem;color:var(--muted);">Aucune correspondance</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="no-matches">
                                    <div class="icon-circle"><i class="bi bi-check-lg"></i></div>
                                    <h4>Aucun r&eacute;sultat</h4>
                                    <p>Aucun fichier n'a pu &ecirc;tre compar&eacute; &agrave; la base.</p>
                                </div>
                            @endif
                        </div>

                        {{-- Tab: Images --}}
                        <div class="tab-content" id="tab-images">
                            @if(count($image_matches ?? []) > 0)
                                @foreach($image_matches as $im)
                                    @php
                                        $imConf = ($im['confidence'] ?? 0) * 100;
                                        $imColor = $imConf >= 80 ? '#dc2626' : ($imConf >= 60 ? '#f97316' : '#3b82f6');
                                    @endphp
                                    <div class="img-match-card">
                                        <div class="img-icon"><i class="bi bi-images"></i></div>
                                        <div class="img-info">
                                            <strong>Image #{{ ($im['new_image_index'] ?? 0) + 1 }}</strong><br>
                                            <small>
                                                vs {{ $im['matched_filename'] ?? '?' }}
                                                @if(isset($im['phash_distance'])) &bull; pHash: {{ $im['phash_distance'] }} @endif
                                            </small>
                                        </div>
                                        <span class="img-score-badge" style="background: {{ $imColor }};">{{ round($imConf, 1) }}%</span>
                                    </div>
                                @endforeach
                            @else
                                <div class="no-matches">
                                    <div class="icon-circle"><i class="bi bi-check-lg"></i></div>
                                    <h4>Aucune similarit&eacute; image</h4>
                                    <p>{{ $image_analysis['images_checked'] ?? 0 }} image(s) analys&eacute;e(s), 0 correspondance.</p>
                                </div>
                            @endif
                        </div>

                    @endif

                    {{-- Actions bar (both modes) --}}
                    @if(isset($result_mode))
                        <div class="actions-bar">
                            <a href="{{ url('/') }}" class="btn-primary-custom"><i class="bi bi-cloud-arrow-up"></i> Nouvelle analyse</a>
                            <div style="display:flex;gap:0.75rem;">
                                <button class="btn-outline-custom" onclick="window.print()"><i class="bi bi-printer"></i> Imprimer</button>
                                <button class="btn-outline-custom" onclick="toggleRawJson()"><i class="bi bi-code-slash"></i> JSON brut</button>
                            </div>
                        </div>
                        <div class="raw-json-section">
                            <button class="raw-json-toggle" onclick="toggleRawJsonWrap(this)">
                                <i class="bi bi-chevron-down"></i> Voir la r&eacute;ponse JSON brute
                            </button>
                            <div class="raw-json-wrap">
                                <pre class="raw-json-pre">{{ json_encode($raw_response ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        </main>
    </div>

    <script>
        // ===== SIDEBAR =====
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
        }

        // ===== PANEL SWITCH =====
        function switchPanel(panel, el) {
            document.querySelectorAll('.view-panel').forEach(p => p.classList.remove('active'));
            document.querySelectorAll('.sidebar-link').forEach(l => l.classList.remove('active'));

            const panelId = panel === 'upload' ? 'panelUpload' : 'panelResults';
            document.getElementById(panelId).classList.add('active');
            if (el) el.classList.add('active');

            const titles = {
                upload: '<i class="bi bi-cloud-arrow-up" style="color:var(--accent);margin-right:0.3rem;"></i> Nouvelle analyse',
                results: '<i class="bi bi-bar-chart-line" style="color:var(--accent);margin-right:0.3rem;"></i> R&eacute;sultats'
            };
            document.getElementById('headerTitle').innerHTML = titles[panel] || titles.upload;

            // Close mobile sidebar
            document.getElementById('sidebar').classList.remove('open');
        }

        // ===== DRAG & DROP =====
        function setupDragDrop(cardId, inputId) {
            const card = document.getElementById(cardId);
            const input = document.getElementById(inputId);
            if (!card || !input) return;

            ['dragenter', 'dragover'].forEach(evt => {
                card.addEventListener(evt, e => { e.preventDefault(); e.stopPropagation(); card.classList.add('dragover'); });
            });
            ['dragleave', 'drop'].forEach(evt => {
                card.addEventListener(evt, e => { e.preventDefault(); e.stopPropagation(); card.classList.remove('dragover'); });
            });
            card.addEventListener('drop', e => {
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    input.files = files;
                    input.dispatchEvent(new Event('change'));
                }
            });
        }
        setupDragDrop('dropFile', 'fileInput');
        setupDragDrop('dropZip', 'zipInput');

        // ===== FILE INPUT CHANGE =====
        document.getElementById('fileInput').addEventListener('change', function() {
            const info = document.getElementById('selectedFileInfo');
            const name = document.getElementById('selectedFileName');
            if (this.files.length > 0) {
                name.textContent = this.files[0].name + ' (' + (this.files[0].size / 1024).toFixed(1) + ' Ko)';
                info.classList.add('visible');
            } else {
                info.classList.remove('visible');
            }
        });
        document.getElementById('zipInput').addEventListener('change', function() {
            const info = document.getElementById('selectedZipInfo');
            const name = document.getElementById('selectedZipName');
            if (this.files.length > 0) {
                name.textContent = this.files[0].name + ' (' + (this.files[0].size / 1024 / 1024).toFixed(2) + ' Mo)';
                info.classList.add('visible');
            } else {
                info.classList.remove('visible');
            }
        });

        // ===== FORM SUBMIT =====
        document.getElementById('fileForm').addEventListener('submit', function() {
            const file = document.getElementById('fileInput').files[0];
            if (!file) { alert('Veuillez s&eacute;lectionner un fichier.'); return false; }
            showLoading('Analyse de "' + file.name + '" en cours...', 'Traitement avec 5 moteurs de d&eacute;tection');
        });
        document.getElementById('zipForm').addEventListener('submit', function() {
            const file = document.getElementById('zipInput').files[0];
            if (!file) { alert('Veuillez s&eacute;lectionner un fichier ZIP.'); return false; }
            showLoading('Extraction et analyse du ZIP...', 'Comparaison crois&eacute;e + base de donn&eacute;es. Cela peut prendre un moment.');
        });

        function showLoading(text, sub) {
            document.getElementById('loadingText').textContent = text;
            document.getElementById('loadingSub').textContent = sub;
            document.getElementById('loadingOverlay').classList.add('visible');
        }

        // ===== API STATUS CHECK =====
        function checkApiStatus() {
            const dot = document.getElementById('apiDot');
            const text = document.getElementById('apiStatusText');
            dot.className = 'api-dot checking';
            text.textContent = 'V&eacute;rification...';

            fetch('/api/health-proxy')
                .then(r => r.json())
                .then(d => {
                    dot.className = 'api-dot online';
                    text.textContent = 'API connect&eacute;e';
                })
                .catch(() => {
                    dot.className = 'api-dot offline';
                    text.textContent = 'API hors ligne';
                });
        }
        // Auto-check on load
        setTimeout(checkApiStatus, 1000);

        // ===== ENGINE TOGGLE =====
        function toggleEngine(btn) {
            btn.classList.toggle('active');
            const wrap = btn.nextElementSibling;
            wrap.classList.toggle('open');
        }

        // ===== PER-FILE TOGGLE =====
        function togglePerFile(header) {
            const card = header.closest('.per-file-card');
            const wrap = card.querySelector('.per-file-matches-wrap');
            const icon = header.querySelector('.bi-chevron-down');
            wrap.classList.toggle('open');
            if (icon) icon.style.transform = wrap.classList.contains('open') ? 'rotate(180deg)' : '';
        }

        // ===== TABS =====
        function switchTab(tabId, btn) {
            document.querySelectorAll('.analysis-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById('tab-' + tabId).classList.add('active');
        }

        // ===== RAW JSON =====
        function toggleRawJson() {
            const wrap = document.querySelector('.raw-json-wrap');
            if (wrap) wrap.classList.toggle('open');
        }
        function toggleRawJsonWrap(btn) {
            const wrap = btn.nextElementSibling;
            btn.classList.toggle('active');
            wrap.classList.toggle('open');
        }

        // ===== RESET =====
        function resetDashboard() {
            window.location.href = '{{ url('/') }}';
        }

        // ===== SHOW RESULTS IF EXISTS =====
        @if(isset($result_mode))
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('loadingOverlay').classList.remove('visible');
                switchPanel('results', document.getElementById('navResults'));
                document.getElementById('navResults').style.display = 'flex';
                document.getElementById('resultsBadge').style.display = 'flex';
                document.getElementById('resultsBadge').textContent = ({{ $overall_score ?? 0 }} * 100).toFixed(0) + '%';
                {{ ($plagiarism_detected ?? false) ? "document.getElementById('resultsBadge').style.background='var(--danger)';document.getElementById('resultsBadge').style.color='white';" : '' }}
            });
        @endif

        // ===== FLASH MESSAGES =====
        @if(session('error'))
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('loadingOverlay').classList.remove('visible');
                alert('{{ session("error") }}');
            });
        @endif
    </script>
</body>
@endsection
