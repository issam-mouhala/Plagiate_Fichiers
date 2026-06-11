{{-- resources/views/submission-detail/index.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $submission->filename }} - Resultats</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .badge-critical { background: #dc3545; }
        .badge-high { background: #fd7e14; }
        .badge-medium { background: #ffc107; color: #333; }
        .badge-low { background: #20c997; }
        .badge-none { background: #6c757d; }
        .badge-processing { background: #0d6efd; animation: pulse 1.5s infinite; }
        .badge-error { background: #dc3545; }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .match-card { border-left: 3px solid #dee2e6; }
        .match-critical { border-left-color: #dc3545; }
        .match-high { border-left-color: #fd7e14; }
        .match-medium { border-left-color: #ffc107; }
        .match-low { border-left-color: #20c997; }
    </style>
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="/">Detection de Plagiat</a>
        <div class="d-flex">
            <a href="/history" class="btn btn-outline-light btn-sm me-2">Historique</a>
            <a href="/upload" class="btn btn-outline-light btn-sm">Nouvelle analyse</a>
        </div>
    </div>
</nav>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">{{ $submission->filename }}</h4>
            <small class="text-muted">
                ID: {{ $submission->id }} |
                {{ $submission->created_at->format('d/m/Y H:i') }} |
                {{ strtoupper($submission->detected_type) }}
            </small>
        </div>
        <div>
            <span class="badge badge-{{ $submission->overall_level }} fs-6 px-3 py-2">
                @if($submission->overall_level === 'processing')
                    <span class="spinner-border spinner-border-sm me-1"></span> En cours...
                @elseif($submission->overall_level === 'error')
                    Erreur
                @else
                    {{ strtoupper($submission->overall_level) }}
                @endif
            </span>
        </div>
    </div>

    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show">
            {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {!! session('success') !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($submission->overall_level === 'processing')
        <!-- EN COURS - Animation + Auto-refresh -->
        <div class="card shadow-sm mb-4 text-center">
            <div class="card-body py-5">
                <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
                <h5>Analyse en cours...</h5>
                <p class="text-muted">
                    Votre fichier est analyse par les moteurs de detection.<br>
                    Cette page se rafraichira automatiquement quand les resultats seront prets.
                </p>
                <div class="progress mt-3" style="max-width: 400px; margin: 0 auto;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%"></div>
                </div>
            </div>
        </div>
    @elseif($submission->overall_level === 'error')
        <div class="alert alert-danger">
            <h5>Erreur lors de l'analyse</h5>
            <p>Verifiez les logs Laravel pour plus de details.</p>
            <a href="/upload" class="btn btn-primary btn-sm">Reessayer</a>
        </div>
    @else
        <!-- RESULTATS -->

        <!-- Score principal -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card text-center shadow-sm">
                    <div class="card-body">
                        <div class="display-5 fw-bold {{ $submission->overall_score >= 0.7 ? 'text-danger' : ($submission->overall_score >= 0.3 ? 'text-warning' : 'text-success') }}">
                            {{ number_format($submission->overall_score * 100, 1) }}%
                        </div>
                        <div class="text-muted">Score Global</div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-center shadow-sm">
                    <div class="card-body">
                        <div class="fs-3 fw-bold text-primary">{{ $submission->text_matches_count }}</div>
                        <div class="text-muted small">Text Matches</div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-center shadow-sm">
                    <div class="card-body">
                        <div class="fs-3 fw-bold text-info">{{ $submission->image_matches_count }}</div>
                        <div class="text-muted small">Image Matches</div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-center shadow-sm">
                    <div class="card-body">
                        <div class="fs-3 fw-bold text-warning">{{ $submission->cross_matches_count }}</div>
                        <div class="text-muted small">Cross Matches</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center shadow-sm">
                    <div class="card-body">
                        <div class="fs-4 fw-bold text-secondary">
                            {{ $submission->timing['total'] ?? $submission->timing['total_seconds'] ?? '-' }}s
                        </div>
                        <div class="text-muted small">Duree</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Engines -->
        @if($submission->engines_used && count($submission->engines_used) > 0)
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white fw-bold">Moteurs Utilises</div>
            <div class="card-body">
                @foreach($submission->engines_used as $engine)
                    <span class="badge bg-primary me-1">{{ $engine }}</span>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Text Matches -->
        @if($submission->text_matches && count($submission->text_matches) > 0)
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white fw-bold">Text Matches ({{ count($submission->text_matches) }})</div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @foreach($submission->text_matches as $i => $match)
                    <div class="list-group-item match-card match-{{ $match['level'] ?? 'none' }}">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-bold">{{ $match['source'] ?? ('Match ' . ($i+1)) }}</span>
                            <div>
                                <span class="badge badge-{{ $match['level'] ?? 'none' }}">{{ $match['level'] ?? 'none' }}</span>
                                <span class="badge bg-light text-dark ms-1">{{ number_format(($match['similarity'] ?? $match['score'] ?? 0) * 100, 1) }}%</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Image Matches -->
        @if($submission->image_matches && count($submission->image_matches) > 0)
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white fw-bold">Image Matches ({{ count($submission->image_matches) }})</div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($submission->image_matches as $match)
                    <div class="col-md-4">
                        <div class="card match-card match-{{ $match['level'] ?? 'none' }}">
                            <div class="card-body text-center">
                                @if(isset($match['new_image_base64']))
                                    <img src="data:image/png;base64,{{ $match['new_image_base64'] }}"
                                         style="max-width:120px;max-height:80px;border-radius:4px;" class="mb-2">
                                @endif
                                <div class="small">
                                    <strong>{{ $match['matched_filename'] ?? 'Inconnu' }}</strong><br>
                                    <span class="text-muted">
                                        pHash: {{ $match['phash_distance'] ?? '-' }} |
                                        Sim: {{ number_format(($match['feature_similarity'] ?? 0) * 100, 1) }}%
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- ZIP Files -->
        @if($submission->is_zip && $submission->zipFiles && $submission->zipFiles->isNotEmpty())
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white fw-bold">Fichiers ZIP ({{ $submission->zipFiles->count() }})</div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Fichier</th>
                            <th>Type</th>
                            <th>Score</th>
                            <th>Niveau</th>
                            <th>Best Match</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($submission->zipFiles as $zf)
                        <tr>
                            <td class="fw-bold">{{ $zf->filename }}</td>
                            <td>{{ strtoupper($zf->file_type) }}</td>
                            <td>{{ number_format($zf->max_score * 100, 1) }}%</td>
                            <td><span class="badge badge-{{ $zf->max_level }}">{{ $zf->max_level }}</span></td>
                            <td>{{ $zf->best_match ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Raw JSON -->
        @if($submission->raw_response)
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white fw-bold d-flex justify-content-between">
                <span>Reponse API (JSON)</span>
                <button class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('jsonBlock').classList.toggle('d-none')">Afficher/Masquer</button>
            </div>
            <div class="card-body d-none" id="jsonBlock">
                <pre style="max-height:400px;overflow:auto;font-size:13px;background:#f8f9fa;padding:16px;border-radius:8px;">{{ json_encode($submission->raw_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        </div>
        @endif
    @endif

    <!-- Actions -->
    <div class="mb-4">
        <a href="/history" class="btn btn-outline-secondary btn-sm">Retour</a>
        <button onclick="if(confirm('Supprimer ?')){document.getElementById('delForm').action='/history/{{ $submission->id }}';document.getElementById('delForm').submit();}"
                class="btn btn-outline-danger btn-sm">Supprimer</button>
    </div>
</div>

<form id="delForm" method="POST" action="" style="display:none">
    @csrf @method('DELETE')
</form>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// AUTO-REFRESH quand l'analyse est en cours
const status = '{{ $submission->overall_level }}';
const submissionId = {{ $submission->id }};

if (status === 'processing') {
    const pollInterval = setInterval(() => {
        fetch('/api/submission/' + submissionId + '/status')
            .then(r => r.json())
            .then(data => {
                if (data.status !== 'processing') {
                    clearInterval(pollInterval);
                    window.location.reload();
                }
            })
            .catch(() => {});
    }, 3000);
}
</script>
</body>
</html>
