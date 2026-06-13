<!DOCTYPE html>
<html lang="fr" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PlagioScan – Analyse avancée</title>
    <!-- Bootstrap 5 + Icons + Fonts -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        /* ================================================================
           DESIGN SYSTEM 2.0 – NEO-GLASS + DARK MODE
           ================================================================ */
        :root {
            --bg-gradient-light: radial-gradient(ellipse at 0% 20%, rgba(99,102,241,0.08), rgba(6,182,212,0.04));
            --bg-gradient-dark: radial-gradient(ellipse at 0% 20%, rgba(99,102,241,0.25), rgba(15,23,42,0.95));
            --card-bg-light: rgba(255,255,255,0.75);
            --card-bg-dark: rgba(15,23,42,0.75);
            --border-light: rgba(255,255,255,0.5);
            --border-dark: rgba(99,102,241,0.2);
            --text-light: #0f172a;
            --text-dark: #f1f5f9;
            --muted-light: #64748b;
            --muted-dark: #94a3b8;
            --accent: #6366f1;
            --accent-glow: 0 0 12px rgba(99,102,241,0.6);
            --transition: all 0.3s cubic-bezier(0.2, 0.9, 0.4, 1.1);
        }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(145deg, #f8fafc 0%, #eef2ff 100%);
            transition: background 0.4s ease;
            min-height: 100vh;
        }
        body.dark {
            background: linear-gradient(145deg, #0f172a 0%, #020617 100%);
            color: var(--text-dark);
        }
        /* Glass morphism cards */
        .glass-card {
            background: var(--card-bg-light);
            backdrop-filter: blur(16px) saturate(180%);
            border: 1px solid var(--border-light);
            border-radius: 2rem;
            box-shadow: 0 20px 40px -12px rgba(0,0,0,0.1);
            transition: var(--transition);
        }
        body.dark .glass-card {
            background: var(--card-bg-dark);
            border-color: var(--border-dark);
            box-shadow: 0 20px 40px -12px rgba(0,0,0,0.5);
        }
        /* Navbar glass */
        .navbar-glass {
            background: rgba(15,23,42,0.7);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        body.dark .navbar-glass {
            background: rgba(0,0,0,0.7);
        }
        /* Score ring */
        .score-ring {
            position: relative;
            width: 180px;
            height: 180px;
        }
        .score-ring svg {
            transform: rotate(-90deg);
        }
        .score-ring .track {
            fill: none;
            stroke: #e2e8f0;
            stroke-width: 12;
        }
        body.dark .score-ring .track {
            stroke: #334155;
        }
        .score-ring .arc {
            fill: none;
            stroke-width: 12;
            stroke-linecap: round;
            transition: stroke-dashoffset 1.6s cubic-bezier(0.2, 0.9, 0.4, 1.1);
        }
        /* Buttons */
        .btn-primary-glow {
            background: linear-gradient(135deg, var(--accent), #4f46e5);
            border: none;
            border-radius: 2rem;
            padding: 0.6rem 1.6rem;
            font-weight: 600;
            transition: var(--transition);
            box-shadow: 0 4px 12px rgba(99,102,241,0.3);
        }
        .btn-primary-glow:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(99,102,241,0.5);
        }
        /* Theme toggle */
        .theme-toggle {
            cursor: pointer;
            background: none;
            border: none;
            font-size: 1.3rem;
            color: white;
            transition: transform 0.2s;
        }
        .theme-toggle:hover {
            transform: scale(1.1);
        }
        /* Progress bar */
        .progress-modern {
            height: 6px;
            border-radius: 10px;
            background-color: #e2e8f0;
        }
        body.dark .progress-modern {
            background-color: #334155;
        }
        /* Match cards */
        .match-card {
            transition: var(--transition);
            border-left: 4px solid;
        }
        .match-card:hover {
            transform: translateX(8px);
            box-shadow: 0 12px 24px -8px rgba(0,0,0,0.2);
        }
        /* Badges */
        .badge-level {
            padding: 0.2rem 0.6rem;
            border-radius: 2rem;
            font-weight: 700;
            font-size: 0.65rem;
        }
        /* Toggle engines */
        .engine-toggle {
            background: none;
            border: none;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--accent);
        }
        /* Footer */
        .footer-glow {
            border-top: 1px solid rgba(99,102,241,0.2);
        }
        /* Responsive */
        @media (max-width: 768px) {
            .score-ring { width: 140px; height: 140px; }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-glass fixed-top">
    <div class="container">
        <a class="navbar-brand fw-bold text-white" href="#">
            <i class="bi bi-shield-shaded me-2"></i>PlagioScan
        </a>
        <div class="d-flex align-items-center gap-3">
            <button class="theme-toggle" id="themeToggle">
                <i class="bi bi-moon-stars-fill"></i>
            </button>
            <span class="badge bg-primary-subtle text-primary-emphasis rounded-pill">Rapport IA</span>
        </div>
    </div>
</nav>

<main class="container py-5" style="margin-top: 80px;">
    <div class="glass-card p-4 p-lg-5">

        <!-- En-tête avec métadonnées -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div>
                <h1 class="display-6 fw-bold mb-0">{{ $filename }}</h1>
                <p class="text-muted mt-2">
                    Type : {{ $file_type === 'code' ? 'Code source' : 'Texte' }} &nbsp;•&nbsp;
                    Taille : {{ number_format($content_length / 1024, 2) }} Ko &nbsp;•&nbsp;
                    {{ $num_comparisons }} références comparées
                </p>
            </div>
            <div class="mt-2 mt-sm-0">
                <span class="badge bg-secondary bg-opacity-25 p-2">
                    <i class="bi bi-cpu"></i> Moteurs:
                    @foreach($engines_used as $engine => $active) @if($active) {{ strtoupper($engine) }} @endif @endforeach
                </span>
            </div>
        </div>

        <!-- Scores globaux en grid -->
        <div class="row g-4 mb-5">
            <div class="col-md-5 text-center">
                <div class="score-ring mx-auto">
                    @php
                        $overall = $overall_score ?? 0;
                        $circumference = 2 * pi() * 75;
                        $offset = $circumference - ($overall * $circumference);
                        $color = $overall >= 0.6 ? '#ef4444' : ($overall >= 0.4 ? '#f59e0b' : '#10b981');
                    @endphp
                    <svg viewBox="0 0 180 180" width="180" height="180">
                        <circle class="track" cx="90" cy="90" r="75" />
                        <circle class="arc" cx="90" cy="90" r="75"
                                style="stroke: {{ $color }}; stroke-dasharray: {{ $circumference }}; stroke-dashoffset: {{ $circumference }};"
                                data-target="{{ $offset }}" />
                    </svg>
                    <div class="position-absolute top-50 start-50 translate-middle text-center">
                        <div class="display-4 fw-bold" style="color: {{ $color }}" id="scorePercent">0</div>
                        <div class="small text-muted">similarité</div>
                    </div>
                </div>
                @if($plagiarism_detected)
                    <div class="alert alert-danger mt-3 rounded-pill">
                        <i class="bi bi-exclamation-triangle-fill"></i> Plagiat potentiel
                    </div>
                @else
                    <div class="alert alert-success mt-3 rounded-pill">
                        <i class="bi bi-check-circle-fill"></i> Aucun plagiat significatif
                    </div>
                @endif
            </div>

            <div class="col-md-7">
                <canvas id="summaryChart" width="400" height="250" style="max-height: 250px;"></canvas>
                <div class="row text-center mt-3">
                    @php $summary = $content_analysis['summary'] ?? ['critical'=>0,'high'=>0,'medium'=>0,'low'=>0]; @endphp
                    <div class="col-3"><span class="badge bg-danger">Critique</span> <strong>{{ $summary['critical'] }}</strong></div>
                    <div class="col-3"><span class="badge bg-warning text-dark">Élevé</span> <strong>{{ $summary['high'] }}</strong></div>
                    <div class="col-3"><span class="badge bg-info text-dark">Moyen</span> <strong>{{ $summary['medium'] }}</strong></div>
                    <div class="col-3"><span class="badge bg-secondary">Faible</span> <strong>{{ $summary['low'] }}</strong></div>
                </div>
            </div>
        </div>

        <!-- Détail des correspondances texte -->
        <h3 class="fw-bold mb-3"><i class="bi bi-files me-2"></i>Correspondances texte</h3>
        @if(count($text_matches) > 0)
            <div class="vstack gap-3">
                @foreach($text_matches as $match)
                    @php
                        $level = $match['level'] ?? 'low';
                        $score = round(($match['combined_score'] ?? 0) * 100, 1);
                        $borderColor = match($level) {
                            'critical' => '#dc2626', 'high' => '#f97316', 'medium' => '#eab308', default => '#94a3b8'
                        };
                    @endphp
                    <div class="match-card p-3 rounded-4 border bg-light bg-opacity-50" style="border-left-color: {{ $borderColor }};">
                        <div class="d-flex flex-wrap justify-content-between align-items-start">
                            <div>
                                <strong>{{ $match['filename'] }}</strong>
                                <span class="badge-level ms-2" style="background: {{ $borderColor }}20; color: {{ $borderColor }};">{{ ucfirst($level) }}</span>
                            </div>
                            <div class="fw-bold fs-5">{{ $score }}%</div>
                        </div>
                        <div class="progress progress-modern my-2">
                            <div class="progress-bar" style="width: 0%; background: {{ $borderColor }};" data-width="{{ $score }}"></div>
                        </div>
                        <!-- Détail des moteurs -->
                        @if(isset($match['engines']))
                            <button class="engine-toggle btn btn-sm btn-link p-0 mt-2" onclick="toggleEngine(this)">
                                <i class="bi bi-chevron-down"></i> Scores par moteur
                            </button>
                            <div class="engine-details d-none mt-2 small">
                                <table class="table table-sm">
                                    <thead>
                                        <tr><th>Moteur</th><th>Raw</th><th>Poids</th><th>Contribution</th></tr>
                                    </thead>
                                    <tbody>
                                        @foreach($match['engines'] as $eng => $vals)
                                        <tr>
                                            <td>{{ strtoupper($eng) }}</td>
                                            <td>{{ round($vals['raw'] ?? 0, 3) }}</td>
                                            <td>{{ $vals['weight'] ?? 0 }}</td>
                                            <td>{{ round($vals['contribution'] ?? 0, 4) }}</td>
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
            <div class="alert alert-success rounded-4 text-center py-4">
                <i class="bi bi-check-circle-fill fs-1"></i>
                <p class="mt-2 mb-0">Aucune similarité textuelle trouvée.</p>
            </div>
        @endif

        <!-- Analyse d'images -->
        @if(isset($image_analysis) && $image_analysis['analyzed'] && count($image_matches) > 0)
            <h3 class="fw-bold mt-5 mb-3"><i class="bi bi-image me-2"></i>Analyse d'images</h3>
            <div class="row g-3">
                @foreach($image_matches as $img)
                    <div class="col-md-6">
                        <div class="glass-card p-3">
                            <div class="d-flex justify-content-between">
                                <span><i class="bi bi-file-image"></i> Image #{{ $img['new_image_index']+1 }}</span>
                                <span class="badge bg-{{ $img['level'] == 'critical' ? 'danger' : ($img['level'] == 'high' ? 'warning' : 'info') }}">
                                    {{ ucfirst($img['level']) }}
                                </span>
                            </div>
                            <div class="mt-2">Confiance : {{ round($img['confidence'] * 100) }}%</div>
                            <div class="progress progress-modern mt-1">
                                <div class="progress-bar" style="width: {{ $img['confidence'] * 100 }}%;"></div>
                            </div>
                            <div class="small text-muted mt-1">Réf: {{ $img['matched_filename'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Pied de page actions -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mt-5 pt-3 border-top">
            <a href="{{ route('upload.index') }}" class="btn btn-primary-glow text-white">
                <i class="bi bi-arrow-left"></i> Nouvelle analyse
            </a>
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-outline-secondary rounded-pill">
                    <i class="bi bi-printer"></i> Imprimer
                </button>
                <button onclick="downloadJSON()" class="btn btn-outline-secondary rounded-pill">
                    <i class="bi bi-download"></i> JSON
                </button>
            </div>
        </div>
        <div class="text-center text-muted small mt-4">
            <i class="bi bi-shield-lock-fill"></i> Analyse confidentielle – IA anti-plagiat
        </div>
    </div>
</main>

<script>
    (function() {
        // Theme toggler
        const themeToggle = document.getElementById('themeToggle');
        const html = document.documentElement;
        const currentTheme = localStorage.getItem('theme') || 'light';
        if (currentTheme === 'dark') {
            document.body.classList.add('dark');
            html.setAttribute('data-bs-theme', 'dark');
            themeToggle.innerHTML = '<i class="bi bi-sun-fill"></i>';
        }
        themeToggle.addEventListener('click', () => {
            document.body.classList.toggle('dark');
            const isDark = document.body.classList.contains('dark');
            html.setAttribute('data-bs-theme', isDark ? 'dark' : 'light');
            themeToggle.innerHTML = isDark ? '<i class="bi bi-sun-fill"></i>' : '<i class="bi bi-moon-stars-fill"></i>';
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        });

        // Animations
        const scoreRing = document.querySelector('.score-ring .arc');
        if (scoreRing) {
            const target = parseFloat(scoreRing.dataset.target);
            setTimeout(() => { scoreRing.style.strokeDashoffset = target; }, 300);
        }
        const scorePercentSpan = document.getElementById('scorePercent');
        const finalScore = {{ round(($overall_score ?? 0) * 100, 1) }};
        let current = 0;
        const interval = setInterval(() => {
            if (current >= finalScore) { clearInterval(interval); scorePercentSpan.innerText = finalScore; }
            else { current += 1; scorePercentSpan.innerText = Math.min(current, finalScore); }
        }, 20);

        // Progress bars
        document.querySelectorAll('.progress-bar[data-width]').forEach(bar => {
            const w = parseFloat(bar.dataset.width);
            setTimeout(() => bar.style.width = w + '%', 200);
        });

        // Engine toggle
        window.toggleEngine = (btn) => {
            const details = btn.nextElementSibling;
            details.classList.toggle('d-none');
            btn.querySelector('i').classList.toggle('bi-chevron-down');
            btn.querySelector('i').classList.toggle('bi-chevron-up');
        };

        // Chart
        const ctx = document.getElementById('summaryChart')?.getContext('2d');
        if (ctx) {
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Critique', 'Élevé', 'Moyen', 'Faible'],
                    datasets: [{
                        data: [{{ $summary['critical'] }}, {{ $summary['high'] }}, {{ $summary['medium'] }}, {{ $summary['low'] }}],
                        backgroundColor: ['#dc2626', '#f97316', '#eab308', '#94a3b8'],
                        borderWidth: 0,
                        hoverOffset: 8,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        }

        // Download JSON
        window.downloadJSON = () => {
            const data = @json($raw_response ?? []);
            const blob = new Blob([JSON.stringify(data, null, 2)], {type: 'application/json'});
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'plagioscan_report.json';
            a.click();
            URL.revokeObjectURL(url);
        };
    })();
</script>
</body>
</html>
