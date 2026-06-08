<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PlagioScan – Détection de plagiat</title>
  <!-- Bootstrap 5 CSS + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #f5f7fc 0%, #e9eef5 100%);
      font-family: 'Inter', sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem;
    }
    .card-plagiarism {
      border: none;
      border-radius: 2rem;
      backdrop-filter: blur(2px);
      background: rgba(255, 255, 255, 0.98);
      box-shadow: 0 25px 45px -12px rgba(0, 0, 0, 0.2);
      transition: transform 0.3s ease;
    }
    .card-plagiarism:hover {
      transform: translateY(-5px);
    }
    .upload-area {
      border: 2px dashed #cbd5e1;
      border-radius: 1.5rem;
      background: #f8fafc;
      padding: 2rem;
      text-align: center;
      cursor: pointer;
      transition: all 0.25s ease;
    }
    .upload-area:hover {
      border-color: #1e6fdf;
      background: #f1f5f9;
    }
    .upload-area.dragover {
      border-color: #1e6fdf;
      background: #e6f0ff;
      transform: scale(0.98);
    }
    .file-name {
      font-size: 0.9rem;
      font-weight: 500;
      color: #0f3b6f;
    }
    .btn-check-plagiarism {
      background: linear-gradient(95deg, #1e6fdf 0%, #0a4c8f 100%);
      border: none;
      padding: 12px 28px;
      border-radius: 40px;
      font-weight: 600;
      transition: all 0.3s;
      box-shadow: 0 8px 14px -6px rgba(30,111,223,0.4);
    }
    .btn-check-plagiarism:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 20px -8px rgba(30,111,223,0.5);
      background: linear-gradient(95deg, #0f5bc2 0%, #083d6e 100%);
    }
    .form-select, .form-control {
      border-radius: 1rem;
      border: 1px solid #e2e8f0;
      padding: 0.7rem 1rem;
      font-size: 0.95rem;
    }
    .form-select:focus, .form-control:focus {
      border-color: #1e6fdf;
      box-shadow: 0 0 0 3px rgba(30,111,223,0.2);
    }
    .icon-bg {
      background: #eef3ff;
      width: 48px;
      height: 48px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 60px;
      color: #1e6fdf;
      font-size: 1.5rem;
    }
    footer {
      font-size: 0.8rem;
      color: #6c757d;
      text-align: center;
      margin-top: 2rem;
    }
    @media (max-width: 576px) {
      body { padding: 1rem; }
      .card-plagiarism { border-radius: 1.5rem; }
    }
  </style>
</head>

<body>
<!-- /resources/views/post/create.blade.php -->




<!-- Create Post Form -->
<div class="container">
  <div class="row justify-content-center">
    <div class="col-lg-8 col-md-10">
      <div class="card card-plagiarism p-4 p-md-5 shadow-sm">
        <div class="text-center mb-4">
          <div class="icon-bg mx-auto mb-3">
            <i class="bi bi-shield-check fs-1"></i>
          </div>
          <h1 class="fw-bold" style="color: #0B2B5E;">PlagioScan</h1>
          <p class="text-muted">Détection automatique de plagiat (texte & code) – basée sur l’IA</p>
        </div>

        <!-- Formulaire principal -->
        <form id="plagiarismForm"
        enctype="multipart/form-data"
        action="{{ route('analyse.index') }}"
        method="POST">          @csrf <!-- Si Laravel, sinon supprimer -->

          <!-- Zone de drop / upload -->
          <div class="mb-4">
            <label class="form-label fw-semibold"><i class="bi bi-cloud-upload me-2"></i>Déposer votre fichier</label>
            <div id="dropZone" class="upload-area">
              <i class="bi bi-file-earmark-code fs-1 text-secondary"></i>
              <p class="mt-2 mb-1">Glissez-déposez votre fichier ici ou <strong>cliquez pour parcourir</strong></p>
              <p class="text-muted small mb-0">Formats acceptés : .txt, .py, .java, .cpp, .js, .php, .docx, .pdf (max 20 Mo)</p>
              <input type="file" id="fileInput" name="submission" accept=".txt,.py,.java,.cpp,.js,.php,.docx,.pdf" style="display: none;">
            </div>
            <div id="fileNameDisplay" class="file-name mt-2 text-center"></div>
            <div class="invalid-feedback d-none" id="fileError">Veuillez sélectionner un fichier valide.</div>
          </div>

          <!-- Type de fichier -->
          <div class="mb-4">
            <label class="form-label fw-semibold"><i class="bi bi-filetype-txt me-2"></i>Type de contenu</label>
            <div class="row g-2">
              <div class="col-sm-6">
                <div class="form-check bg-light p-3 rounded-3">
                  <input class="form-check-input" type="radio" name="file_type" id="typeText" value="text" checked>
                  <label class="form-check-label fw-medium" for="typeText">
                    <i class="bi bi-chat-text me-1"></i> Texte naturel
                  </label>
                  <div class="small text-muted">Articles, dissertations, rapports</div>
                </div>
              </div>
              <div class="col-sm-6">
                <div class="form-check bg-light p-3 rounded-3">
                  <input class="form-check-input" type="radio" name="file_type" id="typeCode" value="code">
                  <label class="form-check-label fw-medium" for="typeCode">
                    <i class="bi bi-code-slash me-1"></i> Code source
                  </label>
                  <div class="small text-muted">Python, Java, C++, PHP, JS...</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Bouton d'envoi -->
          <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
            <button type="submit" class="btn btn-check-plagiarism btn-lg px-5">
              <i class="bi bi-search-heart me-2"></i>Détecter le plagiat
            </button>
            <button type="button" class="btn btn-outline-secondary btn-lg px-4" id="resetBtn">
              <i class="bi bi-arrow-repeat"></i> Réinitialiser
            </button>
          </div>
        </form>


        <footer>
          <i class="bi bi-shield-lock-fill me-1"></i> Analyse confidentielle • Aucun fichier n’est conservé après vérification
        </footer>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap JS + Axios (pour la requête AJAX) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>
  // Éléments DOM
  const dropZone = document.getElementById('dropZone');
  const fileInput = document.getElementById('fileInput');
  const fileNameDisplay = document.getElementById('fileNameDisplay');
  const form = document.getElementById('plagiarismForm');
  const resetBtn = document.getElementById('resetBtn');
  const resultArea = document.getElementById('resultArea');
  const resultContent = document.getElementById('resultContent');
  const fileError = document.getElementById('fileError');
  const loadingSpinner = document.getElementById('loadingSpinner');

  let selectedFile = null;

  // Ouvrir l'explorateur de fichiers
  dropZone.addEventListener('click', () => fileInput.click());

  // Gestion du drag & drop
  dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.classList.add('dragover');
  });
  dropZone.addEventListener('dragleave', () => {
    dropZone.classList.remove('dragover');
  });
  dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('dragover');
    if (e.dataTransfer.files.length) {
      fileInput.files = e.dataTransfer.files;
      handleFileSelect(fileInput.files[0]);
    }
  });

  // Changement manuel via input
  fileInput.addEventListener('change', (e) => {
    if (e.target.files.length) handleFileSelect(e.target.files[0]);
    else resetFileSelection();
  });

  function handleFileSelect(file) {
    const maxSize = 200 * 1024 * 1024; // 5 Mo
    const allowedTypes = ['text/plain', 'application/pdf','application/image', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/x-python', 'text/x-java', 'text/javascript', 'text/x-c++src', 'application/x-php'];
    // Vérification extension (plus souple)
    const ext = file.name.split('.').pop().toLowerCase();
    const allowedExt = ['txt', 'py', 'java', 'cpp', 'c', 'js', 'php', 'pdf', 'docx',"png"];
    if (!allowedExt.includes(ext)) {
      showError('Format non supporté. Utilisez .txt, .py, .java, .cpp, .js, .php, .pdf, .docx');
      resetFileSelection();
      return;
    }
    if (file.size > maxSize) {
      showError('Le fichier dépasse 5 Mo.');
      resetFileSelection();
      return;
    }
    selectedFile = file;
    fileNameDisplay.innerHTML = `<i class="bi bi-file-earmark-check text-success"></i> ${file.name} (${(file.size / 1024).toFixed(1)} Ko)`;
    fileError.classList.add('d-none');
    dropZone.style.borderColor = '#1e6fdf';
  }

  function resetFileSelection() {
    selectedFile = null;
    fileInput.value = '';
    fileNameDisplay.innerHTML = '';
    dropZone.style.borderColor = '#cbd5e1';
    fileError.classList.add('d-none');
  }

  function showError(msg) {
    fileError.textContent = msg;
    fileError.classList.remove('d-none');
  }

  resetBtn.addEventListener('click', () => {
    resetFileSelection();
    resultArea.classList.add('d-none');
    resultContent.innerHTML = '';
  });
 </script>
    <!--    <script>

  // Soumission du formulaire
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!selectedFile) {
      showError('Veuillez sélectionner un fichier.');
      return;
    }
    fileError.classList.add('d-none');

    // Récupérer le type sélectionné
    const fileType = document.querySelector('input[name="file_type"]:checked').value;

    // Préparer FormData
    const formData = new FormData();
    formData.append('submission', selectedFile);
    formData.append('file_type', fileType);
    // Ajouter CSRF token si Laravel
    // formData.append('_token', document.querySelector('input[name="_token"]').value);

    // Afficher zone résultat avec loading
    resultArea.classList.remove('d-none');
    resultContent.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Analyse en cours...</p></div>';
    loadingSpinner?.classList.remove('d-none');

    try {
      // Appel à votre endpoint Laravel (ou directement à l'API Python si exposée)
      // Ici on appelle la route Laravel qui relaye vers Python
      const response = await axios.post('http://localhost:5000/check', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      displayResults(response.data);
    } catch (error) {
      let errorMsg = 'Erreur lors de la détection. Vérifiez que le service est disponible.';
      if (error.response && error.response.data && error.response.data.error) {
        errorMsg = error.response.data.error;
      } else if (error.response && error.response.status === 422) {
        errorMsg = 'Fichier invalide ou trop volumineux.';
      }
      resultContent.innerHTML = `<div class="alert alert-danger">${errorMsg+error}</div>`;
    } finally {
      loadingSpinner?.classList.add('d-none');
    }
  });

  function displayResults(data) {
    // data correspond au JSON renvoyé par Laravel (ou Python directement)
    // Structure attendue: { filename, file_type, num_comparisons, possible_plagiarism, top_matches }
    if (!data) return;
    let html = `<div class="mb-3"><strong>Fichier analysé :</strong> ${escapeHtml(data.filename)}</div>`;
    html += `<div><strong>Type :</strong> ${data.file_type === 'code' ? 'Code source' : 'Texte'}</div>`;
    html += `<div><strong>Comparaisons effectuées :</strong> ${data.num_comparisons}</div>`;
    html += `<hr>`;
    if (data.possible_plagiarism && data.top_matches && data.top_matches.length) {
      html += `<div class="alert alert-warning"><i class="bi bi-exclamation-triangle-fill me-2"></i> ⚠️ Plagiat potentiel détecté !</div>`;
      html += `<h6>Correspondances les plus proches :</h6><ul class="list-group">`;
      data.top_matches.forEach(m => {
        const percent = (m.similarity * 100).toFixed(1);
        html += `<li class="list-group-item d-flex justify-content-between align-items-center">
                  <span><i class="bi bi-file-text me-2"></i>${escapeHtml(m.filename)}</span>
                  <span class="badge bg-primary rounded-pill">${percent}%</span>
                </li>`;
      });
      html += `</ul>`;
    } else {
      html += `<div class="alert alert-success"><i class="bi bi-check-circle-fill me-2"></i> ✅ Aucune similarité inquiétante détectée.</div>`;
    }
    resultContent.innerHTML = html;
  }

  function escapeHtml(str) {
    return str.replace(/[&<>]/g, function(m) {
      if (m === '&') return '&amp;';
      if (m === '<') return '&lt;';
      if (m === '>') return '&gt;';
      return m;
    });
  }
</script>-->
</body>
</html>
