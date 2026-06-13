{{-- resources/views/analyse_zip/index_v2.blade.php --}}
{{-- PlagioScan ZIP Rapport v2 — design néo‑glass/indigo --}}
<!DOCTYPE html>
<html lang="fr" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PlagioScan – Analyse ZIP avancée</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        /* ================================================================
           DESIGN SYSTEM – Indigo Glass v2.0
           ================================================================ */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, system-ui, sans-serif;
            background: linear-gradient(145deg, #f6f9fc 0%, #eef2f8 100%);
            transition: background 0.3s ease, color 0.2s ease;
            min-height: 100vh;
        }

        body.dark {
            background: linear-gradient(145deg, #0b1120 0%, #0a0f1c 100%);
            color: #e2e8f0;
        }

        /* Theme toggle */
        .theme-switch {
            position: fixed;
            bottom: 1.5rem;
            right: 1.5rem;
            z-index: 1100;
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 3rem;
            padding: 0.6rem 1rem;
            display: flex;
            gap: 0.6rem;
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
            transition: transform 0.2s;
        }
        .theme-switch:hover {
            transform: scale(1.05);
        }
        .theme-switch i {
            font-size: 1.2rem;
            color: #fbbf24;
        }
        body.dark .theme-switch i:first-child { color: #94a3b8; }
        body.dark .theme-switch i:last-child { color: #fbbf24; }

        /* Main container */
        .report-container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 1.5rem 3rem;
        }

        /* Glassmorphic card base */
        .glass-card {
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(20px) saturate(1.5);
            border-radius: 2.5rem;
            border: 1px solid rgba(255,255,255,0.6);
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.15);
            transition: background 0.3s, border 0.3s, box-shadow 0.3s;
        }
        body.dark .glass-card {
            background: rgba(15, 23, 42, 0.75);
            border-color: rgba(99, 102, 241, 0.2);
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
        }

        /* Header */
        .report-header {
            padding: 2rem 2.5rem 1rem 2.5rem;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }
        body.dark .report-header {
            border-bottom-color: rgba(255,255,255,0.05);
        }
        .logo-area {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .logo-icon {
            width: 52px;
            height: 52px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border-radius: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.6rem;
            box-shadow: 0 8px 20px -4px rgba(79, 70, 229, 0.4);
        }
        .logo-text h1 {
            font-size: 1.6rem;
            font-weight: 800;
            margin: 0;
            background: linear-gradient(135deg, #1e293b, #4f46e5);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        body.dark .logo-text h1 {
            background: linear-gradient(135deg, #cbd5e1, #a5b4fc);
            -webkit-background-clip: text;
            background-clip: text;
        }
        .logo-text p {
            font-size: 0.7rem;
            margin: 0;
            color: #64748b;
        }
        body.dark .logo-text p {
            color: #94a3b8;
        }
        .badge-group {
            display: flex;
            gap: 0.6rem;
            align-items: center;
        }
        .report-badge {
            padding: 0.4rem 1rem;
            border-radius: 2rem;
            font-size: 0.7rem;
            font-weight: 700;
            background: rgba(0,0,0,0.04);
            backdrop-filter: blur(4px);
        }
        body.dark .report-badge {
            background: rgba(255,255,255,0.05);
        }
        .report-badge i {
            margin-right: 0.3rem;
        }

        /* Stats grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 1.2rem;
            padding: 2rem 2.5rem;
        }
        .stat-tile {
            background: white;
            border-radius: 1.6rem;
            padding: 1.2rem 1rem;
            transition: all 0.25s ease;
            box-shadow: 0 4px 12px rgba(0,0,0,0.02);
            border: 1px solid rgba(0,0,0,0.04);
        }
        body.dark .stat-tile {
            background: rgba(30, 41, 59, 0.6);
            border-color: rgba(255,255,255,0.05);
        }
        .stat-tile:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 30px -12px rgba(0,0,0,0.1);
        }
        .stat-icon {
            width: 42px;
            height: 42px;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            margin-bottom: 0.8rem;
        }
        .stat-value {
            font-size: 1.5rem;
            font-weight: 800;
            line-height: 1.2;
        }
        .stat-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            margin-top: 0.3rem;
        }
        body.dark .stat-label {
            color: #94a3b8;
        }

        /* Score ring section */
        .score-panel {
            display: flex;
            flex-wrap: wrap;
            gap: 2rem;
            align-items: center;
            padding: 0 2.5rem 2rem 2.5rem;
        }
        .score-ring-box {
            flex: 0 0 240px;
            text-align: center;
        }
        .score-ring-svg {
            position: relative;
            width: 200px;
            height: 200px;
            margin: 0 auto;
        }
        .score-ring-svg svg {
            width: 100%;
            height: 100%;
            transform: rotate(-90deg);
        }
        .ring-bg {
            stroke: rgba(0,0,0,0.08);
            stroke-width: 12;
            fill: none;
        }
        body.dark .ring-bg {
            stroke: rgba(255,255,255,0.1);
        }
        .ring-progress {
            stroke-width: 12;
            stroke-linecap: round;
            fill: none;
            transition: stroke-dashoffset 1.2s cubic-bezier(0.22, 1, 0.36, 1);
        }
        .score-center-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
        }
        .score-percent {
            font-size: 2.6rem;
            font-weight: 800;
            line-height: 1;
        }
        .plagiarism-alert {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: #ef4444;
            color: white;
            border-radius: 2rem;
            padding: 0.4rem 1.2rem;
            font-weight: 700;
            font-size: 0.8rem;
            margin-top: 0.8rem;
            box-shadow: 0 4px 12px rgba(239,68,68,0.3);
        }
        .plagiarism-alert.success {
            background: #10b981;
        }
        .score-summary {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .level-badge {
            display: inline-block;
            padding: 0.2rem 0.8rem;
            border-radius: 2rem;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
        }
        .level-critical { background: #ef4444; color: white; }
        .level-high { background: #f97316; color: white; }
        .level-medium { background: #f59e0b; color: #1e293b; }
        .level-low { background: #3b82f6; color: white; }
        .level-none { background: #94a3b8; color: white; }

        /* Tabs */
        .tabs-container {
            padding: 0 2rem;
        }
        .tabs-nav {
            display: flex;
            gap: 0.2rem;
            background: rgba(0,0,0,0.03);
            border-radius: 2rem;
            padding: 0.3rem;
        }
        body.dark .tabs-nav {
            background: rgba(255,255,255,0.03);
        }
        .tab-btn {
            flex: 1;
            padding: 0.7rem 1rem;
            border: none;
            background: transparent;
            border-radius: 2rem;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            color: #475569;
        }
        body.dark .tab-btn {
            color: #cbd5e1;
        }
        .tab-btn.active {
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            color: #4f46e5;
        }
        body.dark .tab-btn.active {
            background: #1e293b;
            color: #a5b4fc;
        }
        .tab-content {
            display: none;
            padding: 2rem 0;
            animation: fadeSlide 0.3s ease-out;
        }
        .tab-content.active {
            display: block;
        }
        @keyframes fadeSlide {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Match cards */
        .match-card {
            background: white;
            border-radius: 1.5rem;
            padding: 1.2rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 6px rgba(0,0,0,0.04);
            border-left: 4px solid;
            transition: all 0.25s;
        }
        body.dark .match-card {
            background: #1e293b;
        }
        .match-card:hover {
            transform: translateX(6px);
            box-shadow: 0 12px 20px -12px rgba(0,0,0,0.15);
        }

        /* File table */
        .file-table-wrapper {
            overflow-x: auto;
            margin: 0 2rem 2rem 2rem;
            border-radius: 1.2rem;
            background: white;
            border: 1px solid #e2e8f0;
        }
        body.dark .file-table-wrapper {
            background: #1e293b;
            border-color: #334155;
        }
        .file-table {
            width: 100%;
            font-size: 0.8rem;
            border-collapse: collapse;
        }
        .file-table th {
            padding: 1rem;
            text-align: left;
            font-weight: 700;
            color: #475569;
            border-bottom: 1px solid #e2e8f0;
        }
        body.dark .file-table th {
            color: #94a3b8;
            border-bottom-color: #334155;
        }
        .file-table td {
            padding: 0.8rem 1rem;
            border-bottom: 1px solid #f1f5f9;
        }
        body.dark .file-table td {
            border-bottom-color: #334155;
        }

        /* Buttons */
        .btn-glass {
            background: rgba(255,255,255,0.9);
            border: 1px solid rgba(0,0,0,0.05);
            border-radius: 2rem;
            padding: 0.5rem 1.2rem;
            font-weight: 600;
            transition: 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        body.dark .btn-glass {
            background: #1e293b;
            color: #e2e8f0;
            border-color: #334155;
        }
        .btn-glass:hover {
            transform: translateY(-2px);
            background: white;
            box-shadow: 0 8px 16px -8px rgba(0,0,0,0.1);
        }

        .action-bar {
            padding: 1.5rem 2.5rem 2rem;
            border-top: 1px solid rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }

        /* footer */
        .report-footer {
            text-align: center;
            padding: 1rem 0 1.5rem;
            font-size: 0.7rem;
            color: #64748b;
        }

        @media (max-width: 768px) {
            .stats-grid { padding: 1.5rem; }
            .score-panel { flex-direction: column; text-align: center; }
            .tabs-nav { flex-wrap: wrap; }
            .tab-btn { padding: 0.5rem; }
        }
        @media (max-width: 480px) {
            .report-header { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>
    <div class="theme-switch" id="themeToggle">
        <i class="bi bi-sun-fill"></i>
        <i class="bi bi-moon-fill"></i>
    </div>

    <div class="report-container">
        <div class="glass-card">
            <!-- Header -->
            <div class="report-header">
                <div class="logo-area">
                    <div class="logo-icon"><i class="bi bi-shield-shaded"></i></div>
                    <div class="logo-text">
                        <h1>PlagioScan ZIP</h1>
                        <p>analyse avancée de similarités</p>
                    </div>
                </div>
                <div class="badge-group">
                    <span class="report-badge"><i class="bi bi-file-zip"></i> Archive ZIP</span>
                    <span class="report-badge"><i class="bi bi-calendar3"></i> {{ now()->format('d/m/Y H:i') }}</span>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-tile" data-count="{{ $zip_info['total_files'] ?? 0 }}">
                    <div class="stat-icon" style="background: #eef2ff; color:#4f46e5;"><i class="bi bi-files"></i></div>
                    <div class="stat-value" id="statTotalFiles">0</div>
                    <div class="stat-label">Fichiers dans le ZIP</div>
                </div>
                <div class="stat-tile" data-count="{{ $zip_info['text_code_files'] ?? 0 }}">
                    <div class="stat-icon" style="background: #ecfdf5; color:#10b981;"><i class="bi bi-file-code"></i></div>
                    <div class="stat-value" id="statTextFiles">0</div>
                    <div class="stat-label">Fichiers texte / code</div>
                </div>
                <div class="stat-tile" data-count="{{ $cross_analysis['pairs_compared'] ?? 0 }}">
                    <div class="stat-icon" style="background: #fff7ed; color:#f97316;"><i class="bi bi-arrow-left-right"></i></div>
                    <div class="stat-value" id="statPairs">0</div>
                    <div class="stat-label">Paires comparées (interne)</div>
                </div>
                <div class="stat-tile" data-count="{{ $per_file_analysis['files_analyzed'] ?? 0 }}">
                    <div class="stat-icon" style="background: #f3e8ff; color:#7c3aed;"><i class="bi bi-database"></i></div>
                    <div class="stat-value" id="statFilesVsBase">0</div>
                    <div class="stat-label">Fichiers vs Base</div>
                </div>
                <div class="stat-tile" data-count="{{ $image_analysis['images_in_zip'] ?? 0 }}">
                    <div class="stat-icon" style="background: #ffe4e6; color:#ec4899;"><i class="bi bi-image"></i></div>
                    <div class="stat-value" id="statImages">0</div>
                    <div class="stat-label">Images extraites</div>
                </div>
            </div>

            <!-- Global score + mini summary -->
            <div class="score-panel">
                <div class="score-ring-box">
                    <div class="score-ring-svg">
                        @php
                            $overall = $overall_score ?? 0;
                            $circumference = 2 * pi() * 88;
                            $offset = $circumference - ($overall * $circumference);
                            $scoreColor = $overall >= 0.6 ? '#ef4444' : ($overall >= 0.4 ? '#f59e0b' : '#10b981');
                        @endphp
                        <svg viewBox="0 0 200 200">
                            <circle class="ring-bg" cx="100" cy="100" r="88" />
                            <circle class="ring-progress" cx="100" cy="100" r="88"
                                    style="stroke: {{ $scoreColor }}; stroke-dasharray: {{ $circumference }}; stroke-dashoffset: {{ $circumference }};"
                                    data-target="{{ $offset }}" />
                        </svg>
                        <div class="score-center-text">
                            <div class="score-percent" style="color: {{ $scoreColor }};" id="globalPercent">0</div>
                            <div style="font-size: 0.7rem;">similarité max</div>
                        </div>
                    </div>
                    @if($plagiarism_detected)
                        <div class="plagiarism-alert"><i class="bi bi-exclamation-triangle-fill"></i> Plagiat détecté</div>
                    @else
                        <div class="plagiarism-alert success"><i class="bi bi-check-circle-fill"></i> Aucun plagiat significatif</div>
                    @endif
                </div>
                <div class="score-summary">
                    <div style="display: flex; gap: 0.8rem; flex-wrap: wrap;">
                        @php
                            $levels = ['critical' => 'Critique', 'high' => 'Élevé', 'medium' => 'Moyen', 'low' => 'Faible'];
                            $summary = $cross_analysis['summary'] ?? ['critical'=>0,'high'=>0,'medium'=>0,'low'=>0];
                        @endphp
                        @foreach($levels as $key => $label)
                            <div>
                                <span class="level-badge level-{{ $key }}">{{ $label }}</span>
                                <div class="stat-value" style="font-size: 1.4rem;" id="summary-{{ $key }}">0</div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-2">
                        <span class="badge bg-secondary bg-opacity-25 p-2">Score croisé max : {{ round(($cross_analysis['max_score'] ?? 0)*100,1) }}%</span>
                        <span class="badge bg-secondary bg-opacity-25 p-2 ms-2">Base max : {{ round(($per_file_analysis['max_score'] ?? 0)*100,1) }}%</span>
                        <span class="badge bg-secondary bg-opacity-25 p-2 ms-2">Images max : {{ round(($image_analysis['max_score'] ?? 0)*100,1) }}%</span>
                    </div>
                </div>
            </div>

            <!-- Fichiers extraits (tableau) -->
            <div class="file-table-wrapper">
                <table class="file-table">
                    <thead>
                        <tr><th>#</th><th>Nom du fichier</th><th>Type</th><th>Extension</th><th>Taille</th><th>Images incluses</th></tr>
                    </thead>
                    <tbody>
                        @foreach($files_extracted as $idx => $f)
                        <tr>
                            <td>{{ $idx+1 }}</td>
                            <td><i class="bi bi-file-earmark-text me-2"></i>{{ $f['filename'] }}</td>
                            <td><span class="badge bg-secondary bg-opacity-25">{{ $f['file_type'] }}</span></td>
                            <td>{{ $f['extension'] }}</td>
                            <td>{{ number_format($f['content_length']/1024,1) }} Ko</td>
                            <td>{{ $f['images_count'] ?? 0 }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Tabs navigation -->
            <div class="tabs-container">
                <div class="tabs-nav">
                    <button class="tab-btn active" data-tab="cross"><i class="bi bi-arrow-left-right"></i> Croisement ZIP <span class="badge bg-light text-dark ms-1" id="badgeCrossCount">{{ count($cross_matches) }}</span></button>
                    <button class="tab-btn" data-tab="base"><i class="bi bi-database"></i> Base de référence <span class="badge bg-light text-dark ms-1" id="badgeBaseCount">{{ count($per_file_results) }}</span></button>
                    <button class="tab-btn" data-tab="images"><i class="bi bi-image"></i> Images <span class="badge bg-light text-dark ms-1" id="badgeImgCount">{{ count($image_matches) }}</span></button>
                </div>
            </div>

            <!-- Tab 1 : Cross file matches -->
            <div class="tab-content active" id="tab-cross">
                @if(count($cross_matches) > 0)
                    <div style="padding: 0 2rem;">
                        @foreach($cross_matches as $match)
                            @php
                                $score = round($match['combined_score']*100,1);
                                $level = $match['level'];
                            @endphp
                            <div class="match-card" style="border-left-color: {{ match($level){'critical'=>'#ef4444','high'=>'#f97316','medium'=>'#f59e0b',default=>'#94a3b8'} }}">
                                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                    <div>
                                        <strong><i class="bi bi-file-text-fill"></i> {{ $match['file_a'] }}</strong>
                                        <i class="bi bi-arrow-left-right mx-2"></i>
                                        <strong><i class="bi bi-file-text-fill"></i> {{ $match['file_b'] }}</strong>
                                        <span class="level-badge level-{{ $level }} ms-2">{{ ucfirst($level) }}</span>
                                    </div>
                                    <div class="fw-bold fs-4">{{ $score }}%</div>
                                </div>
                                <div class="progress mt-2" style="height: 6px;">
                                    <div class="progress-bar" style="width: {{ $score }}%; background: {{ match($level){'critical'=>'#ef4444','high'=>'#f97316','medium'=>'#f59e0b',default=>'#94a3b8'} }}; transition: width 0.6s;"></div>
                                </div>
                                @if(isset($match['engines']))
                                    <button class="btn btn-sm btn-link p-0 mt-2 text-muted" onclick="toggleEngine(this)">
                                        <i class="bi bi-chevron-down"></i> Détails des moteurs
                                    </button>
                                    <div class="engine-details d-none mt-2 small">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr><th>Moteur</th><th>Raw</th><th>Poids</th><th>Contribution</th></tr>
                                            </thead>
                                            <tbody>
                                                @foreach($match['engines'] as $e => $d)
                                                <tr>
                                                    <td>{{ strtoupper($e) }}</td>
                                                    <td>{{ round($d['raw']??0,3) }}</td>
                                                    <td>{{ $d['weight']??0 }}</td>
                                                    <td>{{ round($d['contribution']??0,4) }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-success mx-4 my-2 text-center">Aucune correspondance interne trouvée.</div>
                @endif
            </div>

            <!-- Tab 2 : Per file vs database -->
            <div class="tab-content" id="tab-base">
                @if(count($per_file_results) > 0)
                    <div style="padding: 0 2rem;">
                        @foreach($per_file_results as $pfr)
                            <div class="card mb-3 border-0 shadow-sm">
                                <div class="card-header bg-transparent d-flex justify-content-between align-items-center flex-wrap" style="cursor: pointer;" onclick="togglePerFile(this)">
                                    <div><strong>{{ $pfr['filename'] }}</strong> <span class="badge bg-secondary">{{ $pfr['file_type'] }}</span></div>
                                    <div>
                                        <span class="level-badge level-{{ $pfr['max_level'] }}">{{ ucfirst($pfr['max_level']) }}</span>
                                        <span class="fw-bold ms-2">{{ round($pfr['max_score']*100,1) }}%</span>
                                    </div>
                                </div>
                                <div class="collapse-content" style="display: none;">
                                    <div class="card-body">
                                        @if(count($pfr['matches']) > 0)
                                            @foreach($pfr['matches'] as $m)
                                                <div class="border-bottom pb-2 mb-2">
                                                    <div><i class="bi bi-file-text"></i> <strong>{{ $m['filename'] }}</strong> (ID: {{ $m['submission_id'] }})</div>
                                                    <div class="progress mt-1" style="height: 4px;"><div class="progress-bar bg-primary" style="width: {{ $m['combined_score']*100 }}%"></div></div>
                                                    <span class="badge bg-secondary">{{ round($m['combined_score']*100,1) }}%</span>
                                                    @if(isset($m['engines']))
                                                        <button class="btn btn-sm btn-link p-0 mt-1" onclick="toggleEngine(this)">Détails moteurs</button>
                                                        <div class="engine-details d-none small">@foreach($m['engines'] as $en=>$ed) <span class="badge bg-light">{{ strtoupper($en) }}:{{ round($ed['raw']??0,2) }}</span> @endforeach</div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        @else
                                            <p class="text-muted">Aucune correspondance trouvée.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-success mx-4 my-2 text-center">Aucune analyse par fichier disponible.</div>
                @endif
            </div>

            <!-- Tab 3 : Images -->
            <div class="tab-content" id="tab-images">
                @if(count($image_matches) > 0)
                    <div style="padding: 0 2rem;">
                        <div class="row g-3">
                            @foreach($image_matches as $img)
                                <div class="col-md-6">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between">
                                                <span><i class="bi bi-image-fill"></i> Image #{{ $img['new_image_index']+1 }}</span>
                                                <span class="level-badge level-{{ $img['level'] }}">{{ ucfirst($img['level']) }}</span>
                                            </div>
                                            <div class="mt-2">Confiance : <strong>{{ round($img['confidence']*100,1) }}%</strong></div>
                                            <div class="progress mt-1"><div class="progress-bar" style="width: {{ $img['confidence']*100 }}%"></div></div>
                                            <div class="small text-muted mt-2">vs {{ $img['matched_filename'] }}</div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="alert alert-info mx-4 my-2 text-center">Aucune similarité d'image détectée.</div>
                @endif
            </div>

            <!-- Actions -->
            <div class="action-bar">
                <div>
                    <a href="{{ route('upload.index') }}" class="btn-glass"><i class="bi bi-plus-circle"></i> Nouvelle analyse</a>
                    <a href="{{ route('upload.index') }}" class="btn-glass ms-2"><i class="bi bi-file-zip"></i> Analyser un autre ZIP</a>
                </div>
                <button class="btn-glass" onclick="window.print()"><i class="bi bi-printer"></i> Imprimer le rapport</button>
            </div>
            <div class="report-footer">
                <i class="bi bi-shield-check"></i> Rapport généré par PlagioScan – Moteurs : TF‑IDF, BERT, Winnowing, LCS, pHash
            </div>
        </div>
    </div>

    <script>
        (function() {
            // Theme toggle
            const themeBtn = document.getElementById('themeToggle');
            themeBtn.addEventListener('click', () => {
                document.body.classList.toggle('dark');
                const isDark = document.body.classList.contains('dark');
                localStorage.setItem('theme', isDark ? 'dark' : 'light');
                document.documentElement.setAttribute('data-bs-theme', isDark ? 'dark' : 'light');
            });
            if (localStorage.getItem('theme') === 'dark') {
                document.body.classList.add('dark');
                document.documentElement.setAttribute('data-bs-theme', 'dark');
            }

            // Animated counters
            function animateNumber(element, target) {
                let current = 0;
                const step = Math.ceil(target / 60);
                const interval = setInterval(() => {
                    current += step;
                    if (current >= target) {
                        element.innerText = target;
                        clearInterval(interval);
                    } else {
                        element.innerText = current;
                    }
                }, 16);
            }
            // Stats tiles
            document.querySelectorAll('.stat-tile[data-count]').forEach(tile => {
                const val = parseInt(tile.getAttribute('data-count'));
                const targetSpan = tile.querySelector('.stat-value');
                if (targetSpan && val) animateNumber(targetSpan, val);
            });
            // Summary levels
            const summaryData = @json($summary);
            if (summaryData) {
                if(summaryData.critical) animateNumber(document.getElementById('summary-critical'), summaryData.critical);
                if(summaryData.high) animateNumber(document.getElementById('summary-high'), summaryData.high);
                if(summaryData.medium) animateNumber(document.getElementById('summary-medium'), summaryData.medium);
                if(summaryData.low) animateNumber(document.getElementById('summary-low'), summaryData.low);
            }
            // Global score percent
            const globalPercent = {{ round($overall_score * 100) }};
            animateNumber(document.getElementById('globalPercent'), globalPercent);
            // Stat total files
            animateNumber(document.getElementById('statTotalFiles'), {{ $zip_info['total_files'] ?? 0 }});
            animateNumber(document.getElementById('statTextFiles'), {{ $zip_info['text_code_files'] ?? 0 }});
            animateNumber(document.getElementById('statPairs'), {{ $cross_analysis['pairs_compared'] ?? 0 }});
            animateNumber(document.getElementById('statFilesVsBase'), {{ $per_file_analysis['files_analyzed'] ?? 0 }});
            animateNumber(document.getElementById('statImages'), {{ $image_analysis['images_in_zip'] ?? 0 }});

            // Ring animation
            const ring = document.querySelector('.ring-progress');
            if (ring) {
                setTimeout(() => {
                    ring.style.strokeDashoffset = ring.dataset.target;
                }, 200);
            }

            // Tabs
            const tabs = document.querySelectorAll('.tab-btn');
            tabs.forEach(btn => {
                btn.addEventListener('click', () => {
                    const targetTab = btn.dataset.tab;
                    document.querySelectorAll('.tab-content').forEach(tc => tc.classList.remove('active'));
                    document.getElementById(`tab-${targetTab}`).classList.add('active');
                    tabs.forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                });
            });
        })();

        function toggleEngine(btn) {
            const details = btn.nextElementSibling;
            details.classList.toggle('d-none');
            btn.querySelector('i').classList.toggle('bi-chevron-down');
            btn.querySelector('i').classList.toggle('bi-chevron-up');
        }
        function togglePerFile(header) {
            const content = header.parentElement.querySelector('.collapse-content');
            if (content) content.style.display = content.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</body>
</html>
