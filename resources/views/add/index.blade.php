<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PlagioScan – Ajouter une référence</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f0f4fa 0%, #e2e8f0 100%);
            font-family: 'Inter', sans-serif;
            padding: 2rem 0;
        }
        .card-upload {
            border: none;
            border-radius: 2rem;
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 20px 35px -12px rgba(0,0,0,0.15);
        }
        .upload-area {
            border: 2px dashed #cbd5e1;
            border-radius: 1.5rem;
            background: #f8fafc;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .upload-area:hover, .upload-area.dragover {
            border-color: #1e6fdf;
            background: #eef3ff;
        }
        .file-preview {
            background: #f1f5f9;
            border-radius: 1rem;
            padding: 0.5rem 1rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .btn-submit {
            background: linear-gradient(95deg, #1e6fdf 0%, #0a4c8f 100%);
            border: none;
            padding: 12px 28px;
            border-radius: 40px;
            font-weight: 600;
            transition: 0.2s;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -6px rgba(30,111,223,0.4);
        }
        .toast-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1100;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="card-upload p-4 p-md-5">
                <div class="text-center mb-4">
                    <i class="bi bi-database-add fs-1 text-primary"></i>
                    <h2 class="fw-bold mt-2">Ajouter une référence</h2>
                    <p class="text-muted">Enrichissez la base de détection avec un fichier texte ou du code source.</p>
                </div>

                <form id="uploadForm" enctype="multipart/form-data" action="{{route('store.index')}}" method="POST">
                    @csrf
                    <!-- Zone de drop -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Fichier</label>
                        <div id="dropZone" class="upload-area">
                            <i class="bi bi-cloud-upload fs-1 text-secondary"></i>
                            <p class="mt-2 mb-1">Glissez-déposez un fichier ou <strong>cliquez</strong></p>
                            <p class="text-muted small">Formats : .txt, .py, .java, .cpp, .js, .php, .pdf, .docx ,.zip (max 5 Mo)</p>
                            <input type="file" id="fileInput" name="file" accept=".txt,.py,.java,.cpp,.js,.php,.pdf,.docx" style="display: none;">
                        </div>
                        <div id="fileInfo" class="mt-2 text-center"></div>
                        <div class="invalid-feedback d-none" id="fileError">Fichier invalide ou trop volumineux.</div>
                    </div>

                    <!-- Type de fichier -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Type de contenu</label>
                        <div class="row g-2">
                            <div class="col-sm-6">
                                <div class="form-check bg-light p-3 rounded-3">
                                    <input class="form-check-input" type="radio" name="file_type" id="typeText" value="text" checked>
                                    <label class="form-check-label fw-medium" for="typeText">
                                        <i class="bi bi-chat-text"></i> Texte naturel
                                    </label>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-check bg-light p-3 rounded-3">
                                    <input class="form-check-input" type="radio" name="file_type" id="typeCode" value="code">
                                    <label class="form-check-label fw-medium" for="typeCode">
                                        <i class="bi bi-code-slash"></i> Code source
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Métadonnées optionnelles -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Métadonnées (optionnel)</label>
                        <textarea class="form-control" name="metadata" rows="2" placeholder='{"auteur": "Prof. Martin", "cours": "INFO2025"}' style="border-radius: 1rem;"></textarea>
                        <div class="form-text">Format JSON valide. Ces informations seront stockées avec la référence.</div>
                    </div>

                    <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                        <button type="submit" class="btn btn-submit btn-lg px-5 text-white">
                            <i class="bi bi-database-fill-up me-2"></i>Ajouter à la base
                        </button>
                        <button type="button" id="resetBtn" class="btn btn-outline-secondary btn-lg px-4">
                            <i class="bi bi-arrow-repeat"></i> Réinitialiser
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Toast pour notifications -->
<div class="toast-container">
    <div id="liveToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="4000">
        <div class="d-flex">
            <div class="toast-body" id="toastMessage"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    (function() {
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fileInput');
        const fileInfo = document.getElementById('fileInfo');
        const fileError = document.getElementById('fileError');
        const form = document.getElementById('uploadForm');
        const resetBtn = document.getElementById('resetBtn');
        const toastEl = document.getElementById('liveToast');
        const toastMessage = document.getElementById('toastMessage');
        let toast = new bootstrap.Toast(toastEl);
        let selectedFile = null;

        function showToast(message, isError = false) {
            toastEl.classList.remove('bg-success', 'bg-danger');
            toastEl.classList.add(isError ? 'bg-danger' : 'bg-success');
            toastMessage.textContent = message;
            toast.show();
        }

        function resetUI() {
            selectedFile = null;
            fileInput.value = '';
            fileInfo.innerHTML = '';
            dropZone.style.borderColor = '#cbd5e1';
            fileError.classList.add('d-none');
        }

        function handleFile(file) {
            const maxSize = 5 * 1024 * 1024;
            const allowedExt = ['txt', 'py', 'java', 'cpp', 'js', 'php', 'pdf', 'docx',"zip"];
            const ext = file.name.split('.').pop().toLowerCase();
            if (!allowedExt.includes(ext)) {
                fileError.textContent = 'Format non supporté.';
                fileError.classList.remove('d-none');
                resetUI();
                return false;
            }
            if (file.size > maxSize) {
                fileError.textContent = 'Fichier > 5 Mo.';
                fileError.classList.remove('d-none');
                resetUI();
                return false;
            }
            fileError.classList.add('d-none');
            selectedFile = file;
            fileInfo.innerHTML = `<div class="file-preview"><i class="bi bi-file-earmark-check text-success"></i> ${file.name} (${(file.size/1024).toFixed(1)} Ko)</div>`;
            dropZone.style.borderColor = '#1e6fdf';
            return true;
        }

        dropZone.addEventListener('click', () => fileInput.click());
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('dragover');
        });
        dropZone.addEventListener('dragleave', () => dropZone.classList.remove('dragover'));
        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('dragover');
            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                handleFile(e.dataTransfer.files[0]);
            }
        });
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length) handleFile(e.target.files[0]);
            else resetUI();
        });
        resetBtn.addEventListener('click', () => resetUI());

      /*  form.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!selectedFile) {
                showToast('Veuillez sélectionner un fichier.', true);
                return;
            }
            const fileType = document.querySelector('input[name="file_type"]:checked').value;
            let metadata = document.querySelector('textarea[name="metadata"]').value.trim();
            if (metadata && !(metadata.startsWith('{') && metadata.endsWith('}'))) {
                showToast('Les métadonnées doivent être au format JSON valide.', true);
                return;
            }

            const formData = new FormData();
            formData.append('file', selectedFile);
            formData.append('file_type', fileType);
            if (metadata) formData.append('metadata', metadata);
            // Ajout CSRF si nécessaire (Laravel)
            formData.append('_token', document.querySelector('input[name="_token"]').value);

            try {
                // Appel à l'API Python /add (ou via un contrôleur Laravel qui relaye)
                // Ici, on appelle directement l'API Python (attention CORS)
                // Si vous voulez passer par Laravel, remplacez l'URL par '/api/add-reference'
                const response = await axios.post('http://localhost:5000/api/add', formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                });
                if (response.data.status === 'added') {
                    showToast(`Fichier ajouté avec succès (ID: ${response.data.id})`);
                    resetUI();
                    form.reset();
                } else {
                    showToast('Erreur inconnue de l\'API.', true);
                }
            } catch (error) {
                console.error(error);
                let msg = 'Impossible d\'ajouter le fichier. Vérifiez que l\'API Python tourne (port 8000).';
                if (error.response && error.response.data && error.response.data.detail) msg = error.response.data.detail;
                showToast(msg, true);
            }
        });*/
    })();
</script>
</body>
</html>
