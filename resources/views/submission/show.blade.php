<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail - {{ $submission->filename }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .badge-critical { background: #dc3545; }
        .badge-high { background: #fd7e14; }
        .badge-medium { background: #ffc107; color: #333; }
        .badge-low { background: #20c997; }
        .badge-none { background: #6c757d; }
        .match-card { border-left: 3px solid #dee2e6; transition: all 0.2s; }
        .match-card:hover { background: #f8f9fa; }
        .match-critical { border-left-color: #dc3545; }
        .match-high { border-left-color: #fd7e14; }
        .match-medium { border-left-color: #ffc107; }
        .match-low { border-left-color: #20c997; }
        .img-preview { max-width: 120px; max-height: 80px; border-radius: 4px; cursor: pointer; }
        .json-view { background: #f8f9fa; border-radius: 8px; padding: 16px; max-height: 400px; overflow-y: auto; font-size: 13px; }
        pre { margin: 0; white-space: pre-wrap; word-break: break-all; }
    </style>
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="/">Detection de Plagiat</a>
        <div class="d-flex">
            <a href="/history" class="btn btn-outline-light btn-sm me-2">Historique</a>
            <a href="/" class="btn btn-outline-light btn-sm">Dashboard</a>
        </div>
    </div>
</nav>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">{{ $submission->filename }}</h4>
            <small class="text-muted">
                Soumis le {{ $submission->created_at->format('d/m/Y a H:i') }}
                - {{ $submission->file_size_human }}
                - {{ strtoupper($submission->detected_type) }}
            </small>
        </div>
        <div>
            <span class="badge badge-{{ $submission->overall_level }} fs-6 px-3 py-2">
                {{ strtoupper($submission->overall_level) }}
            </span>
        </div>
    </div>

    <!-- Score principal -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <div class="display-5 fw-bold {{ $submission->overall_score >= 0.7 ? 'text-danger' : ($submission->overall_score >= 0.3 ? 'text-warning' : 'text-success') }}">
                        {{ $submission->score_percent }}
                    </div>
                    <div class="text-muted">Score Global</div>
                    <div class="progress mt-2" style="height: 10px;">
                        <div class="progress-bar {{ $submission->overall_score >= 0.7 ? 'bg-danger' : ($submission->overall_score >= 0.3 ? 'bg-warning' : 'bg-success') }}"
                             style="width: {{ ($submission->overall_score * 100) }}%"></div>
                    </div>
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
        <div class="col-md-2">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <div class="fs-3 fw-bold text-secondary">{{ $submission->num_comparisons }}</div>
                    <div class="text-muted small">Comparaisons</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Engines used -->
    @if($submission->engines_used)
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white fw-bold">Moteurs Utilises</div>
        <div class="card-body">
            @foreach($submission->engines_used as $engine)
                <span class="badge bg-primary me-1 mb-1">{{ $engine }}</span>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Text Matches -->
    @if($submission->text_matches && count($submission->text_matches) > 0)
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white fw-bold">
            Text Matches ({{ count($submission->text_matches) }})
        </div>
        <div class="card-body p-0">
            <div class="list-group list-group-flush">
                @foreach($submission->text_matches as $i => $match)
                <div class="list-group-item match-card match-{{ $match['level'] ?? 'none' }}">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="fw-bold">{{ $match['source'] ?? $match['matched_filename'] ?? 'Match ' . ($i+1) }}</span>
                        <div>
                            <span class="badge badge-{{ $match['level'] ?? 'none' }}">{{ $match['level'] ?? 'none' }}</span>
                            <span class="badge bg-light text-dark ms-1">{{ number_format(($match['similarity'] ?? $match['score'] ?? 0) * 100, 1) }}%</span>
                        </div>
                    </div>
                    @if(isset($match['matched_text']))
                        <div class="bg-light rounded p-2 small text-muted">
                            {{ Str::limit($match['matched_text'], 200) }}
                        </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Image Matches -->
    @if($submission->image_matches && count($submission->image_matches) > 0)
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white fw-bold">
            Image Matches ({{ count($submission->image_matches) }})
        </div>
        <div class="card-body">
            <div class="row g-3">
                @foreach($submission->image_matches as $i => $match)
                <div class="col-md-4">
                    <div class="card match-card match-{{ $match['level'] ?? 'none' }}">
                        <div class="card-body text-center">
                            @if(isset($match['new_image_base64']))
                                <img src="data:image/png;base64,{{ $match['new_image_base64'] }}"
                                     class="img-preview mb-2" alt="Nouvelle image">
                                <div class="small fw-bold mb-1">Nouvelle image</div>
                            @endif
                            <div class="small text-muted">
                                {{ $match['matched_filename'] ?? 'Inconnu' }}<br>
                                pHash: {{ $match['phash_distance'] ?? '-' }} |
                                Similarite: {{ number_format(($match['feature_similarity'] ?? 0) * 100, 1) }}%
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- ZIP Files results -->
    @if($submission->is_zip && $submission->zipFiles->isNotEmpty())
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white fw-bold">
            Fichiers ZIP ({{ $submission->zipFiles->count() }})
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Fichier</th>
                        <th>Type</th>
                        <th>Score Max</th>
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
                        <td class="text-truncate" style="max-width: 200px;">{{ $zf->best_match ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Timing -->
    @if($submission->timing)
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white fw-bold">Temps d'Execution</div>
        <div class="card-body">
            <div class="row g-2 text-center">
                @foreach($submission->timing as $key => $val)
                @if(!in_array($key, ['total', 'total_seconds']))
                <div class="col-3 col-md-2">
                    <div class="fs-5 fw-bold text-primary">{{ number_format($val, 2) }}s</div>
                    <div class="text-muted small">{{ str_replace('_', ' ', ucfirst($key)) }}</div>
                </div>
                @endif
                @endforeach
                <div class="col-3 col-md-2">
                    <div class="fs-5 fw-bold text-dark">{{ number_format($submission->timing['total'] ?? $submission->timing['total_seconds'] ?? 0, 2) }}s</div>
                    <div class="text-muted small">Total</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Raw JSON -->
    @if($submission->raw_response)
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white fw-bold d-flex justify-content-between">
            <span>Reponse API Brute (JSON)</span>
            <button class="btn btn-sm btn-outline-secondary" onclick="toggleJson()">Afficher/Masquer</button>
        </div>
        <div class="card-body d-none" id="jsonBlock">
            <div class="json-view">
                <pre>{{ json_encode($submission->raw_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        </div>
    </div>
    @endif

    <!-- Actions -->
    <div class="mb-4">
        <button onclick="confirmDelete({{ $submission->id }})"
                class="btn btn-outline-danger btn-sm">
            Supprimer cette analyse
        </button>
        <a href="/history" class="btn btn-outline-secondary btn-sm">Retour a l'historique</a>
    </div>
</div>

<form id="deleteForm" method="POST" action="" style="display:none">
    @csrf @method('DELETE')
</form>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleJson() {
    document.getElementById('jsonBlock').classList.toggle('d-none');
}
function confirmDelete(id) {
    if (confirm('Supprimer cette analyse ?')) {
        const form = document.getElementById('deleteForm');
        form.action = '/history/' + id;
        form.submit();
    }
}
</script>
</body>
</html>
