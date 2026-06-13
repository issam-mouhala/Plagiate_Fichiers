<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analyse ZIP en cours – PlagioScan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background: linear-gradient(145deg, #f1f5f9 0%, #e2e8f0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
        }
        .card {
            border-radius: 2rem;
            border: none;
            backdrop-filter: blur(8px);
            background: rgba(255,255,255,0.85);
            box-shadow: 0 25px 40px -12px rgba(0,0,0,0.2);
            text-align: center;
            padding: 2rem;
        }
        .spinner {
            width: 60px;
            height: 60px;
            border: 4px solid #e2e8f0;
            border-top-color: #4f46e5;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 1rem auto;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <i class="bi bi-file-zip fs-1 text-primary"></i>
                <h3 class="mt-3">Analyse du ZIP en cours</h3>
                <p class="text-muted">Le traitement peut prendre quelques minutes...</p>
                <div class="spinner"></div>
                <div id="statusMessage" class="mt-3 small text-secondary">Préparation de l'analyse</div>
            </div>
        </div>
    </div>
</div>
<script>
    const taskId = '{{ $taskId }}';
    let attempts = 0;
    const maxAttempts = 180; // 15 minutes si interval 5s

    function checkResult() {
        fetch(`/zip/result/${taskId}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'completed') {
                    window.location.href = `/zip/show/${taskId}`;
                } else if (data.status === 'failed') {
                    document.getElementById('statusMessage').innerHTML = `<span class="text-danger">Erreur : ${data.error}</span><br><a href="/" class="btn btn-sm btn-primary mt-2">Retour</a>`;
                } else if (data.status === 'processing') {
                    document.getElementById('statusMessage').innerHTML = 'Analyse en profondeur...';
                    setTimeout(checkResult, 3000);
                } else if (attempts++ > maxAttempts) {
                    document.getElementById('statusMessage').innerHTML = '<span class="text-warning">Le traitement prend plus de temps que prévu. Rafraîchissez la page plus tard.</span>';
                } else {
                    setTimeout(checkResult, 3000);
                }
            })
            .catch(() => setTimeout(checkResult, 5000));
    }
    checkResult();
</script>
</body>
</html>
