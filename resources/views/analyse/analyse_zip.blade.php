{{-- resources/views/analyse_zip.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PlagioScan – Analyse de projet ZIP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f0f4fa 0%, #e2e8f0 100%);
            font-family: 'Inter', sans-serif;
            padding: 2rem 0;
        }
        .report-card {
            border: none;
            border-radius: 2rem;
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 20px 35px -12px rgba(0,0,0,0.15);
        }
        .stat-card {
            background: white;
            border-radius: 1.5rem;
            transition: transform 0.2s;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .stat-card:hover { transform: translateY(-3px); }
        .level-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 2rem;
            font-weight: 600;
            font-size: 0.75rem;
        }
        .level-critical { background: #dc2626; color: white; }
        .level-high { background: #f97316; color: white; }
        .level-medium { background: #eab308; color: black; }
        .level-low { background: #3b82f6; color: white; }
        .level-none { background: #94a3b8; color: white; }
        .progress-custom { height: 8px; border-radius: 1rem; background-color: #e2e8f0; }
        .match-card {
            border-left: 4px solid;
            transition: 0.2s;
        }
        .match-card:hover { transform: translateX(5px); }
        .section-suspect {
            background: #fff5f5;
            border-left: 3px solid #dc2626;
            padding: 0.75rem;
            border-radius: 0.75rem;
            font-size: 0.85rem;
            margin-top: 0.5rem;
        }
        .engine-badge {
            background: #f1f5f9;
            padding: 0.2rem 0.6rem;
            border-radius: 1rem;
            font-size: 0.7rem;
            font-weight: 500;
        }
        .table-engines td { padding: 0.4rem; font-size: 0.8rem; }
        .btn-back {
            background: #1e293b;
            border: none;
            border-radius: 2rem;
            padding: 0.6rem 1.6rem;
            font-weight: 500;
            color: white;
        }
        .btn-back:hover { background: #0f172a; transform: translateY(-2px); }
        footer { font-size: 0.75rem; color: #475569; }
        .overall-score {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            background: conic-gradient(#dc2626 0deg {{ $analysis['overall_score'] * 360 }}deg, #e9ecef {{ $analysis['overall_score'] * 360 }}deg 360deg);
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .score-inner {
            width: 110px;
            height: 110px;
            background: white;
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 2rem;
        }
        @media (max-width: 768px) {
            .overall-score { width: 110px; height: 110px; }
            .score-inner { width: 85px; height: 85px; font-size: 1.5rem; }
        }
    </style>
</head>
<body>
<div class="container py-3">
    <div class="report-card p-4 p-md-5">
        <!-- En-tête -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
            <div>
                <i class="bi bi-file-zip fs-1 text-primary me-2"></i>
                <span class="fw-bold fs-3" style="color: #0B2B5E;">PlagioScan ZIP</span>
                <span class="badge bg-secondary ms-2">Analyse groupée</span>
            </div>
            <div><i class="bi bi-calendar3 me-1"></i> {{ now()->format('d/m/Y H:i') }}</div>
        </div>

        <!-- Info ZIP -->
        @php $zip = $analysis['zip_info'] ?? []; @endphp
        <div class="row g-3 mb-5">
            <div class="col-md-6">
                <div class="stat-card p-3 h-100">
                    <i class="bi bi-archive text-primary fs-4"></i>
                    <h5 class="mt-2">{{ $analysis['filename'] }}</h5>
                    <p class="text-muted mb-0">Fichiers analysables : {{ $zip['text_code_files'] ?? 0 }} / {{ $zip['total_files'] ?? 0 }}</p>
                    <p class="small">Images ignorées : {{ $zip['image_files'] ?? 0 }}</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stat-card p-3 h-100">
                    <i class="bi bi-diagram-3 text-info fs-4"></i>
                    <h5 class="mt-2">Comparaisons croisées</h5>
                    <p class="mb-0">{{ $analysis['cross_file_analysis']['pairs_compared'] ?? 0 }} paires analysées</p>
                    <p class="small">Matches trouvés : {{ $analysis['cross_file_analysis']['matches_found'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <!-- Score global -->
        @php $overall = $analysis['overall_score'] ?? 0; $level = $analysis['overall_level'] ?? 'none'; @endphp
        <div class="row align-items-center mb-5">
            <div class="col-md-4 text-center">
                <div class="overall-score mx-auto">
                    <div class="score-inner">
                        {{ round($overall * 100) }}<small>%</small>
                    </div>
                </div>
                <h4 class="mt-3">Score global</h4>
                <span class="badge level-{{ $level }} level-badge fs-6 px-3 py-2">{{ strtoupper($level) }}</span>
            </div>
            <div class="col-md-8">
                <div class="stat-card p-4">
                    <h5><i class="bi bi-bar-chart-steps me-2"></i>Résumé des similarités</h5>
                    @php $summary = $analysis['cross_file_analysis']['summary'] ?? ['critical'=>0,'high'=>0,'medium'=>0,'low'=>0]; @endphp
                    <div class="row text-center mt-3">
                        <div class="col-3"><span class="badge bg-danger fs-6 mb-1">Critique</span><h4 class="mb-0">{{ $summary['critical'] }}</h4></div>
                        <div class="col-3"><span class="badge bg-warning text-dark fs-6 mb-1">Élevé</span><h4 class="mb-0">{{ $summary['high'] }}</h4></div>
                        <div class="col-3"><span class="badge bg-info text-dark fs-6 mb-1">Moyen</span><h4 class="mb-0">{{ $summary['medium'] }}</h4></div>
                        <div class="col-3"><span class="badge bg-secondary fs-6 mb-1">Faible</span><h4 class="mb-0">{{ $summary['low'] }}</h4></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Liste des fichiers extraits -->
        <h3 class="fw-bold mt-4"><i class="bi bi-files me-2"></i>Fichiers extraits</h3>
        <div class="table-responsive mb-5">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr><th>Nom</th><th>Type</th><th>Taille</th><th>Images</th></tr>
                </thead>
                <tbody>
                    @foreach(($analysis['files_extracted'] ?? []) as $file)
                    <tr>
                        <td>{{ $file['filename'] }}</td>
                        <td><span class="engine-badge">{{ strtoupper($file['file_type']) }}</span></td>
                        <td>{{ number_format($file['content_length'] / 1024, 1) }} Ko</td>
                        <td>{{ $file['images_count'] ?? 0 }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Analyse croisée (matches entre fichiers du ZIP) -->
        @if(!empty($analysis['cross_file_analysis']['matches']))
        <h3 class="fw-bold mt-4"><i class="bi bi-arrow-left-right me-2"></i>Similarités entre fichiers du ZIP</h3>
        <div class="row g-4 mb-5">
            @foreach($analysis['cross_file_analysis']['matches'] as $match)
            @php
                $score = $match['combined_score'];
                $level = $match['level'];
                $borderColor = match($level) {
                    'critical' => '#dc2626', 'high' => '#f97316', 'medium' => '#eab308', default => '#94a3b8'
                };
            @endphp
            <div class="col-12">
                <div class="card match-card shadow-sm rounded-4" style="border-left: 5px solid {{ $borderColor }};">
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between align-items-start">
                            <div>
                                <i class="bi bi-file-text-fill me-1"></i> <strong>{{ $match['file_a'] }}</strong>
                                <i class="bi bi-arrow-right-short fs-4"></i>
                                <i class="bi bi-file-text-fill me-1"></i> <strong>{{ $match['file_b'] }}</strong>
                                <span class="badge level-{{ $level }} ms-2 level-badge">{{ ucfirst($level) }}</span>
                            </div>
                            <div><span class="fw-bold fs-5">{{ round($score * 100, 1) }}%</span></div>
                        </div>
                        <div class="progress progress-custom mt-2 mb-3">
                            <div class="progress-bar bg-{{ match($level){'critical'=>'danger','high'=>'warning','medium'=>'info',default='secondary'} }}" style="width: {{ $score*100 }}%;"></div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-borderless engine-table">
                                <thead><tr><th>Moteur</th><th>Raw</th><th>Poids</th><th>Contribution</th></tr></thead>
                                <tbody>
                                    @foreach($match['engines'] as $engine => $vals)
                                    <tr>
                                        <td><span class="engine-badge">{{ strtoupper($engine) }}</span></td>
                                        <td>{{ round($vals['raw'] ?? 0, 3) }}</td>
                                        <td>{{ $vals['weight'] ?? 0 }}</td>
                                        <td>{{ round($vals['contribution'] ?? 0, 4) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <!-- Analyse par fichier avec la base -->
        @if(!empty($analysis['per_file_database_analysis']['results']))
        <h3 class="fw-bold mt-4"><i class="bi bi-database me-2"></i>Comparaison avec la base de référence</h3>
        @foreach($analysis['per_file_database_analysis']['results'] as $res)
        <div class="card mb-4 shadow-sm rounded-4">
            <div class="card-header bg-transparent fw-bold">
                <i class="bi bi-file-earmark-text me-2"></i> {{ $res['filename'] }}
                <span class="float-end">
                    <span class="badge level-{{ $res['max_level'] }} level-badge">{{ ucfirst($res['max_level']) }}</span>
                    Score max : {{ round($res['max_score'] * 100, 1) }}%
                </span>
            </div>
            <div class="card-body">
                @foreach($res['matches'] as $matchIdx => $match)
                <div class="match-card p-3 mb-3 rounded-3 border-start border-3" style="border-left-color: {{ match($match['level']){'critical'=>'#dc2626','high'=>'#f97316','medium'=>'#eab308',default='#94a3b8'} }};">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div><strong>{{ $match['filename'] }}</strong> (ID: {{ $match['submission_id'] }})</div>
                        <div><span class="badge level-{{ $match['level'] }}">{{ ucfirst($match['level']) }}</span> {{ round($match['combined_score'] * 100, 1) }}%</div>
                    </div>
                    <div class="progress progress-custom my-2">
                        <div class="progress-bar bg-{{ match($match['level']){'critical'=>'danger','high'=>'warning','medium'=>'info',default='secondary'} }}" style="width: {{ $match['combined_score']*100 }}%;"></div>
                    </div>
                    <!-- Détail des sections suspectes -->
                    @if(!empty($match['suspicious_sections']))
                    <button class="btn btn-sm btn-link p-0 text-decoration-none" type="button" data-bs-toggle="collapse" data-bs-target="#suspects{{ $loop->parent->index }}_{{ $matchIdx }}">
                        <i class="bi bi-eye"></i> Voir sections suspectes ({{ count($match['suspicious_sections']) }})
                    </button>
                    <div class="collapse mt-2" id="suspects{{ $loop->parent->index }}_{{ $matchIdx }}">
                        @foreach($match['suspicious_sections'] as $sec)
                        <div class="section-suspect mb-2">
                            <div class="fw-bold">Section {{ $sec['section_index'] }} – Score {{ round($sec['score'] * 100) }}% <span class="badge bg-danger">critique</span></div>
                            <pre class="mb-0 small" style="white-space: pre-wrap;">{{ $sec['preview'] }}</pre>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
        @endif

        <!-- Analyse d'images -->
        @if(!empty($analysis['image_analysis']['image_matches']))
        <h3 class="fw-bold mt-4"><i class="bi bi-image me-2"></i>Analyse d'images</h3>
        <div class="row g-3 mb-5">
            @foreach($analysis['image_analysis']['image_matches'] as $imgMatch)
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body">
                        <i class="bi bi-image fs-2 text-primary float-end"></i>
                        <p><strong>Image #{{ $imgMatch['new_image_index']+1 }}</strong> similaire à <strong>{{ $imgMatch['matched_filename'] }}</strong></p>
                        <p>Confiance : {{ round($imgMatch['confidence'] * 100, 1) }}%<br>
                        Niveau : <span class="badge level-{{ $imgMatch['level'] }}">{{ ucfirst($imgMatch['level']) }}</span></p>
                        <div class="progress progress-custom">
                            <div class="progress-bar bg-{{ match($imgMatch['level']){'critical'=>'danger','high'=>'warning','medium'=>'info',default='secondary'} }}" style="width: {{ $imgMatch['confidence']*100 }}%;"></div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <!-- Actions -->
        <div class="d-flex justify-content-between mt-5 pt-3">
            <a href="{{ route('plagiat.form') }}" class="btn btn-back"><i class="bi bi-arrow-left me-2"></i>Nouvelle analyse</a>
            <button onclick="window.print();" class="btn btn-outline-secondary rounded-pill px-4"><i class="bi bi-printer me-2"></i>Imprimer</button>
        </div>
        <footer class="text-center mt-5">
            <i class="bi bi-shield-lock-fill"></i> Analyse confidentielle • Algorithmes : TF‑IDF, Sémantique, Winnowing, LCS
        </footer>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
