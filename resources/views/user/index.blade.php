{{-- resources/views/user-dashboard/index.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Tableau de Bord</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card-stat { transition: transform 0.2s; border: none; }
        .card-stat:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
        .badge-critical { background: #dc3545; }
        .badge-high { background: #fd7e14; }
        .badge-medium { background: #ffc107; color: #333; }
        .badge-low { background: #20c997; }
        .badge-none { background: #28a745; }
        .badge-processing { background: #0d6efd; }
        .badge-error { background: #dc3545; }
        .progress-level { height: 24px; border-radius: 8px; }
        .level-bar { height: 100%; border-radius: 8px; transition: width 0.8s; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold; color: white; }
        .file-card { border-left: 4px solid #dee2e6; }
        .file-card.level-critical { border-left-color: #dc3545; }
        .file-card.level-high { border-left-color: #fd7e14; }
        .file-card.level-medium { border-left-color: #ffc107; }
        .file-card.level-low { border-left-color: #20c997; }
        .file-card.level-none { border-left-color: #28a745; }
        .file-card.level-processing { border-left-color: #0d6efd; }
        .activity-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }
        .activity-dot.processing { background: #0d6efd; animation: pulse 1.5s infinite; }
        .activity-dot.error { background: #dc3545; }
        .activity-dot.critical { background: #dc3545; }
        .activity-dot.high { background: #fd7e14; }
        .activity-dot.medium { background: #ffc107; }
        .activity-dot.low { background: #20c997; }
        .activity-dot.none { background: #28a745; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }
    </style>
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="/">Detection de Plagiat</a>
        <div class="d-flex align-items-center">
            <span class="text-light me-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-circle me-1" viewBox="0 0 16 16"><path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/><path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.758 1.226 5.468 2.37A7 7 0 0 0 8 1z"/></svg>
                {{ auth()->user()->name ?? 'Utilisateur' }}
            </span>
            <a href="/upload" class="btn btn-outline-light btn-sm me-2">Nouvelle analyse</a>
            <a href="/history" class="btn btn-outline-light btn-sm">Historique</a>
        </div>
    </div>
</nav>

<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Mon Tableau de Bord</h4>
            <small class="text-muted">Vue d'ensemble de vos analyses</small>
        </div>
        <a href="/upload" class="btn btn-primary btn-sm">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-plus-lg me-1" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2z"/></svg>
            Analyser un fichier
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {!! session('success') !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- ════════════════ CARDS STATS ════════════════ -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card card-stat text-center shadow-sm bg-primary text-white">
                <div class="card-body py-3">
                    <div class="fs-2 fw-bold">{{ $stats['total'] }}</div>
                    <div class="small opacity-75">Total Analyses</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card card-stat text-center shadow-sm">
                <div class="card-body py-3">
                    <div class="fs-2 fw-bold text-info">{{ $stats['today'] }}</div>
                    <div class="text-muted small">Aujourd'hui</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card card-stat text-center shadow-sm">
                <div class="card-body py-3">
                    <div class="fs-2 fw-bold text-secondary">{{ $stats['this_week'] }}</div>
                    <div class="text-muted small">Cette semaine</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card card-stat text-center shadow-sm">
                <div class="card-body py-3">
                    <div class="fs-2 fw-bold text-success">{{ $stats['clean'] }}</div>
                    <div class="text-muted small">Propres</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card card-stat text-center shadow-sm">
                <div class="card-body py-3">
                    <div class="fs-2 fw-bold text-warning">{{ $stats['plagiarized'] }}</div>
                    <div class="text-muted small">Plagiats</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card card-stat text-center shadow-sm">
                <div class="card-body py-3">
                    <div class="fs-2 fw-bold text-danger">{{ $stats['critical'] }}</div>
                    <div class="text-muted small">Critiques</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">

        <!-- ══════════════ COLONNE GAUCHE ══════════════ -->
        <div class="col-lg-8">

            <!-- ─── En cours ─── -->
            @if($processingJobs->isNotEmpty())
            <div class="card shadow-sm mb-4 border border-primary">
                <div class="card-header bg-primary text-white fw-bold">
                    <span class="spinner-border spinner-border-sm me-2"></span>
                    Analyses en cours ({{ $processingJobs->count() }})
                </div>
                <div class="card-body p-0">
                    @foreach($processingJobs as $job)
                    <a href="/history/{{ $job->id }}" class="list-group-item list-group-item-action d-flex align-items-center file-card level-processing">
                        <div class="me-3">
                            <span class="badge badge-processing">En cours</span>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold">{{ $job->filename }}</div>
                            <small class="text-muted">{{ $job->created_at->diffForHumans() }}</small>
                        </div>
                        <div>
                            <span class="spinner-border spinner-border-sm"></span>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- ─── Erreurs ─── -->
            @if($errorJobs->isNotEmpty())
            <div class="card shadow-sm mb-4 border border-danger">
                <div class="card-header bg-danger text-white fw-bold">
                    Analyses en erreur ({{ $errorJobs->count() }})
                </div>
                <div class="card-body p-0">
                    @foreach($errorJobs as $job)
                    <div class="list-group-item list-group-item-action d-flex align-items-center file-card level-error">
                        <div class="me-3"><span class="badge badge-error">Erreur</span></div>
                        <div class="flex-grow-1">
                            <div class="fw-bold">{{ $job->filename }}</div>
                            <small class="text-muted">{{ $job->created_at->diffForHumans() }}</small>
                        </div>
                        <a href="/upload" class="btn btn-sm btn-outline-danger">Réessayer</a>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- ─── Répartition par niveau (barre) ─── -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white fw-bold">Répartition des Résultats</div>
                <div class="card-body">
                    <div class="progress-level mb-2">
                        <div class="level-bar" style="width: {{ $levelStats['none_percent'] }}%; background: #28a745;">
                            {{ $levelStats['none_percent'] }}% Propre
                        </div>
                    </div>
                    <div class="progress-level mb-2">
                        <div class="level-bar" style="width: {{ $levelStats['low_percent'] }}%; background: #20c997;">
                            {{ $levelStats['low_percent'] }}% Faible
                        </div>
                    </div>
                    <div class="progress-level mb-2">
                        <div class="level-bar" style="width: {{ $levelStats['medium_percent'] }}%; background: #ffc107; color: #333;">
                            {{ $levelStats['medium_percent'] }}% Moyen
                        </div>
                    </div>
                    <div class="progress-level mb-2">
                        <div class="level-bar" style="width: {{ $levelStats['high_percent'] }}%; background: #fd7e14;">
                            {{ $levelStats['high_percent'] }}% Élevé
                        </div>
                    </div>
                    <div class="progress-level">
                        <div class="level-bar" style="width: {{ $levelStats['critical_percent'] }}%; background: #dc3545;">
                            {{ $levelStats['critical_percent'] }}% Critique
                        </div>
                    </div>

                    <div class="row mt-3 text-center">
                        <div class="col"><span class="badge bg-secondary fs-6 p-2">{{ $stats['none'] }}</span><br><small>Propre</small></div>
                        <div class="col"><span class="badge badge-low fs-6 p-2">{{ $stats['low'] }}</span><br><small>Faible</small></div>
                        <div class="col"><span class="badge badge-medium fs-6 p-2">{{ $stats['medium'] }}</span><br><small>Moyen</small></div>
                        <div class="col"><span class="badge badge-high fs-6 p-2">{{ $stats['high'] }}</span><br><small>Élevé</small></div>
                        <div class="col"><span class="badge badge-critical fs-6 p-2">{{ $stats['critical'] }}</span><br><small>Critique</small></div>
                    </div>
                </div>
            </div>

            <!-- ─── Activité récente ─── -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between">
                    <span class="fw-bold">Activité Récente</span>
                    <a href="/history?user_id={{ auth()->id() }}" class="btn btn-sm btn-outline-secondary">Voir tout</a>
                </div>
                <div class="card-body p-0">
                    @if($recentActivity->isEmpty())
                        <div class="text-center text-muted py-5">
                            Aucune analyse pour le moment.
                        </div>
                    @else
                    <div class="list-group list-group-flush">
                        @foreach($recentActivity as $a)
                        <a href="/history/{{ $a->id }}" class="list-group-item list-group-item-action d-flex align-items-center file-card level-{{ $a->overall_level }}">
                            <div class="me-3">
                                <span class="activity-dot {{ $a->overall_level }}"></span>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold text-truncate">{{ $a->filename }}</div>
                                <small class="text-muted">
                                    {{ strtoupper($a->detected_type) }}
                                    @if($a->is_zip) <span class="badge bg-secondary ms-1">ZIP</span> @endif
                                    - {{ $a->created_at->diffForHumans() }}
                                </small>
                            </div>
                            <div class="text-end me-3">
                                @if($a->overall_level === 'processing')
                                    <span class="badge badge-processing">En cours</span>
                                @elseif($a->overall_level === 'error')
                                    <span class="badge badge-error">Erreur</span>
                                @else
                                    <span class="badge badge-{{ $a->overall_level }}">{{ $a->overall_level }}</span>
                                    <div class="fw-bold">{{ number_format($a->overall_score * 100, 1) }}%</div>
                                @endif
                            </div>
                        </a>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            <!-- ─── Top 5 plagiats ─── -->
            @if($topPlagiarized->isNotEmpty())
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white fw-bold">Top 5 Fichiers les Plus Plagiés</div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Fichier</th>
                                <th>Type</th>
                                <th>Score</th>
                                <th>Niveau</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topPlagiarized as $i => $t)
                            <tr>
                                <td class="fw-bold text-danger">{{ $i + 1 }}</td>
                                <td class="fw-bold">{{ Str::limit($t->filename, 35) }}</td>
                                <td>{{ strtoupper($t->detected_type) }}</td>
                                <td>
                                    <span class="fw-bold text-danger">{{ number_format($t->overall_score * 100, 1) }}%</span>
                                </td>
                                <td><span class="badge badge-{{ $t->overall_level }}">{{ $t->overall_level }}</span></td>
                                <td><small>{{ $t->created_at->format('d/m/Y') }}</small></td>
                                <td><a href="/history/{{ $t->id }}" class="btn btn-sm btn-outline-primary">Voir</a></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>

        <!-- ══════════════ COLONNE DROITE ══════════════ -->
        <div class="col-lg-4">

            <!-- ─── Score moyen ─── -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white fw-bold">Score Moyen</div>
                <div class="card-body text-center">
                    <div class="fs-1 fw-bold {{ ($stats['avg_score'] ?? 0) >= 0.5 ? 'text-danger' : 'text-success' }}">
                        {{ number_format(($stats['avg_score'] ?? 0) * 100, 1) }}%
                    </div>
                    <small class="text-muted">Moyenne de toutes vos analyses</small>
                </div>
            </div>

            <!-- ─── Stats rapides ─── -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white fw-bold">Résumé</div>
                <div class="card-body">
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Fichiers analysés</span>
                        <span class="fw-bold">{{ $stats['total'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Fichiers dans ZIP</span>
                        <span class="fw-bold">{{ $stats['total_files'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Ce mois</span>
                        <span class="fw-bold">{{ $stats['this_month'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">En cours</span>
                        <span class="fw-bold text-primary">{{ $stats['processing'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Erreurs</span>
                        <span class="fw-bold text-danger">{{ $stats['error'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2">
                        <span class="text-muted">Espace utilisé</span>
                        <span class="fw-bold">{{ number_format($stats['total_size'] / 1024 / 1024, 2) }} MB</span>
                    </div>
                </div>
            </div>

            <!-- ─── Répartition par type ─── -->
            @if(!empty($typeStats))
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white fw-bold">Types de Fichiers</div>
                <div class="card-body">
                    @foreach($typeStats as $type => $count)
                    @php $percent = round(($count / $stats['total']) * 100); @endphp
                    <div class="mb-2">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted text-uppercase">{{ $type }}</span>
                            <span class="fw-bold">{{ $count }} ({{ $percent }}%)</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-primary" style="width: {{ $percent }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- ─── Activité 7 jours ─── -->
            @if($dailyScores->isNotEmpty())
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white fw-bold">7 Derniers Jours</div>
                <div class="card-body">
                    @foreach($dailyScores as $day)
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <div>
                            <div class="fw-bold small">{{ \Carbon\Carbon::parse($day->date)->format('d/m') }}</div>
                            <small class="text-muted">{{ $day->count }} analyse(s)</small>
                        </div>
                        <div class="fw-bold {{ ($day->avg_score ?? 0) >= 0.5 ? 'text-danger' : 'text-success' }}">
                            {{ number_format(($day->avg_score ?? 0) * 100, 1) }}%
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- ─── Actions rapides ─── -->
            <div class="card shadow-sm">
                <div class="card-header bg-white fw-bold">Actions Rapides</div>
                <div class="card-body d-grid gap-2">
                    <a href="/upload" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-upload me-1" viewBox="0 0 16 16"><path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/><path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708l3-3z"/></svg>
                        Analyser un fichier
                    </a>
                    <a href="/add" class="btn btn-outline-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-plus-circle me-1" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/><path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/></svg>
                        Ajouter au corpus
                    </a>
                    <a href="/history?user_id={{ auth()->id() }}" class="btn btn-outline-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-clock-history me-1" viewBox="0 0 16 16"><path d="M8.515 1.94a.5.5 0 0 1 0 .706L5.067 6.094a.5.5 0 0 1-.706 0l-1.5-1.5a.5.5 0 1 1 .708-.708L4.5 5.386l3.024-3.025a.5.5 0 0 1 .706 0l.285.285z"/><path d="M8.5 14a6.5 6.5 0 1 0 0-13 6.5 6.5 0 0 0 0 13zm0 1a7.5 7.5 0 1 0 0-15 7.5 7.5 0 0 0 0 15z"/><path d="M7.462 12.414a.5.5 0 0 1-.42-.57l.75-4.5a.5.5 0 0 1 .986.164l-.75 4.5a.5.5 0 0 1-.566.406z"/></svg>
                        Mon historique
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Auto-refresh si jobs en cours -->
@if($processingJobs->isNotEmpty())
<script>
setTimeout(() => { window.location.reload(); }, 5000);
</script>
@endif

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
