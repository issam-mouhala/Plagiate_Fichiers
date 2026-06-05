<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PlagioScan - Détection intelligente de plagiat</title>
  <!-- Bootstrap 5 CSS + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <!-- Google Fonts (Poppins) -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,600;14..32,700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Inter', sans-serif;
      background: #f8fafc;
    }
    .hero {
      background: linear-gradient(135deg, #0B2B5E 0%, #1A4A7A 100%);
      color: white;
      padding: 80px 0 100px;
      border-bottom-left-radius: 2rem;
      border-bottom-right-radius: 2rem;
    }
    .feature-icon {
      font-size: 2.5rem;
      color: #1e6fdf;
    }
    .card-hover {
      transition: transform 0.2s ease, box-shadow 0.2s ease;
      border: none;
      border-radius: 1rem;
    }
    .card-hover:hover {
      transform: translateY(-5px);
      box-shadow: 0 1rem 2rem rgba(0,0,0,0.08);
    }
    .step-circle {
      background: #1e6fdf;
      width: 48px;
      height: 48px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 60px;
      color: white;
      font-weight: bold;
      font-size: 1.4rem;
      margin-bottom: 1rem;
    }
    .btn-primary {
      background-color: #1e6fdf;
      border-color: #1e6fdf;
      padding: 12px 32px;
      font-weight: 600;
      border-radius: 40px;
    }
    .btn-outline-light {
      border-radius: 40px;
      padding: 12px 28px;
    }
    .footer {
      background-color: #0e2a3b;
      color: #adb5bd;
    }
    .stat-number {
      font-size: 2.5rem;
      font-weight: 700;
      color: #1e6fdf;
    }
    @media (max-width: 768px) {
      .hero {
        padding: 60px 0 70px;
      }
    }
  </style>
</head>
<body>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-dark bg-transparent position-absolute w-100" style="z-index: 10;">
  <div class="container">
    <a class="navbar-brand fw-bold fs-4" href="#">
      <i class="bi bi-shield-shaded me-2"></i>PlagioScan
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link active" href="#">Accueil</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Fonctionnalités</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Tarifs</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Contact</a></li>
        <li class="nav-item ms-lg-3"><a class="btn btn-outline-light btn-sm" href="#">Connexion</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- Hero section -->
<section class="hero">
  <div class="container pt-5">
    <div class="row align-items-center">
      <div class="col-lg-6 text-center text-lg-start">
        <h1 class="display-4 fw-bold mb-4">Détection automatique de plagiat <br><span class="text-warning">texte & code source</span></h1>
        <p class="lead mb-4">Analysez les similarités sémantiques, le copier-coller malin et les paraphrases. Idéal pour enseignants, formateurs et entreprises.</p>
        <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center justify-content-lg-start">
          <a href="/upload" class="btn btn-primary btn-lg"><i class="bi bi-cloud-upload me-2"></i>Téléverser un document</a>
          <a href="#" class="btn btn-outline-light btn-lg"><i class="bi bi-play-circle me-2"></i>Voir démo</a>
        </div>
      </div>
      <div class="col-lg-6 mt-5 mt-lg-0 text-center">
        <img src="https://placehold.co/550x400/ffffff/1e6fdf?text=Dashboard+Preview" alt="Aperçu" class="img-fluid rounded-4 shadow-lg" style="max-width: 100%;">
      </div>
    </div>
  </div>
</section>

<!-- Statistiques / crédibilité -->
<div class="container position-relative mt-4" style="margin-top: -2rem !important;">
  <div class="row bg-white rounded-4 shadow-sm p-4 mx-1">
    <div class="col-md-3 text-center border-end-md">
      <div class="stat-number">98%</div>
      <p class="text-muted">Précision de détection</p>
    </div>
    <div class="col-md-3 text-center border-end-md">
      <div class="stat-number">15+</div>
      <p class="text-muted">Langages supportés</p>
    </div>
    <div class="col-md-3 text-center border-end-md">
      <div class="stat-number">2.5M</div>
      <p class="text-muted">Soumissions analysées</p>
    </div>
    <div class="col-md-3 text-center">
      <div class="stat-number">24/7</div>
      <p class="text-muted">Analyse automatique</p>
    </div>
  </div>
</div>

<!-- Fonctionnalités principales -->
<section class="container py-5 my-5">
  <div class="text-center mb-5">
    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">Fonctionnalités avancées</span>
    <h2 class="display-6 fw-bold mt-3">Pourquoi choisir PlagioScan ?</h2>
    <p class="lead text-muted">Une solution tout-en-un basée sur l'IA pour garantir l'authenticité des travaux.</p>
  </div>
  <div class="row g-4">
    <div class="col-md-4">
      <div class="card card-hover h-100 p-4">
        <i class="bi bi-file-code-fill feature-icon"></i>
        <h5 class="mt-3 fw-bold">Détection de code source</h5>
        <p class="text-muted">Analyse les similarités structurelles dans Python, Java, C++, JavaScript, PHP et plus de 15 langages. Ignore les différences de mise en forme.</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card card-hover h-100 p-4">
        <i class="bi bi-chat-text-fill feature-icon"></i>
        <h5 class="mt-3 fw-bold">Similarité sémantique</h5>
        <p class="text-muted">Notre modèle NLP détecte les paraphrases et les reformulations intelligentes, pas seulement les chaînes exactes.</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card card-hover h-100 p-4">
        <i class="bi bi-graph-up feature-icon"></i>
        <h5 class="mt-3 fw-bold">Rapport détaillé</h5>
        <p class="text-muted">Visualisation des passages similaires, pourcentage de plagiat, sources suspectes et interface enseignant intuitive.</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card card-hover h-100 p-4">
        <i class="bi bi-cloud-arrow-up feature-icon"></i>
        <h5 class="mt-3 fw-bold">Soumission groupée</h5>
        <p class="text-muted">Téléversement de dossiers complets (projets d’étudiants). Comparaison croisée automatique entre tous les fichiers.</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card card-hover h-100 p-4">
        <i class="bi bi-shield-lock feature-icon"></i>
        <h5 class="mt-3 fw-bold">Confidentialité totale</h5>
        <p class="text-muted">Les documents sont chiffrés et supprimés automatiquement après analyse. Pas de réutilisation des données.</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card card-hover h-100 p-4">
        <i class="bi bi-robot feature-icon"></i>
        <h5 class="mt-3 fw-bold">IA entrainable</h5>
        <p class="text-muted">Adaptez le modèle à votre domaine (code académique, rapports techniques, thèses) pour une pertinence accrue.</p>
      </div>
    </div>
  </div>
</section>

<!-- Comment ça marche -->
<section class="bg-white py-5">
  <div class="container py-4">
    <div class="text-center mb-5">
      <h2 class="display-6 fw-bold">Fonctionnement en 3 étapes</h2>
      <p class="lead text-muted">Une interface simple, une analyse puissante.</p>
    </div>
    <div class="row text-center">
      <div class="col-md-4">
        <div class="step-circle mx-auto">1</div>
        <h5 class="fw-bold">Déposez vos fichiers</h5>
        <p class="text-muted">Upload de documents (PDF, DOCX, .txt, .py, .java, .zip) ou collez directement du texte/code.</p>
      </div>
      <div class="col-md-4">
        <div class="step-circle mx-auto">2</div>
        <h5 class="fw-bold">Analyse automatique</h5>
        <p class="text-muted">Notre moteur Python / IA compare les soumissions entre elles et avec une base de référence (optionnel).</p>
      </div>
      <div class="col-md-4">
        <div class="step-circle mx-auto">3</div>
        <h5 class="fw-bold">Rapport & action</h5>
        <p class="text-muted">Visualisez les similitudes, exportez le rapport PDF et prenez les mesures pédagogiques nécessaires.</p>
      </div>
    </div>
    <div class="text-center mt-5">
      <a href="#" class="btn btn-primary px-5">Commencer gratuitement</a>
      <p class="text-muted mt-2 small">Sans carte de crédit – essai de 14 jours</p>
    </div>
  </div>
</section>

<!-- Témoignage / Citation -->
<section class="container py-5 my-4">
  <div class="row align-items-center bg-light rounded-4 p-5 shadow-sm">
    <div class="col-md-3 text-center">
      <i class="bi bi-quote display-1 text-primary opacity-50"></i>
    </div>
    <div class="col-md-9">
      <p class="fs-4 fst-italic">"PlagioScan a réduit de 70% le temps que je passais à vérifier les projets de mes étudiants. L'interface Laravel est fluide et la détection de similarité dans le code est bluffante."</p>
      <div class="d-flex align-items-center gap-3 mt-3">
        <img src="https://randomuser.me/api/portraits/women/68.jpg" class="rounded-circle" width="50" height="50" alt="avatar">
        <div>
          <strong>Prof. Sophie Lemaître</strong><br>
          <span class="text-muted">Université de Technologie, Département Informatique</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Appel à l'action final -->
<div class="container mb-5">
  <div class="bg-primary bg-gradient rounded-4 p-5 text-white text-center">
    <h3 class="fw-bold">Prêt à garantir l'intégrité académique ?</h3>
    <p class="lead mb-4">Intégrez notre solution dans votre plateforme Laravel en quelques minutes via API Python.</p>
    <a href="#" class="btn btn-light btn-lg px-5 rounded-pill fw-semibold text-primary">Demander une démo</a>
  </div>
</div>

<!-- Footer -->
<footer class="footer pt-5 pb-4">
  <div class="container">
    <div class="row">
      <div class="col-md-4 mb-4">
        <i class="bi bi-shield-shaded fs-3"></i>
        <h5 class="text-white mt-2">PlagioScan</h5>
        <p>Détection de plagiat par IA pour textes et codes sources. Propulsé par Laravel & Python.</p>
      </div>
      <div class="col-md-2 mb-4">
        <h6 class="text-white">Produit</h6>
        <ul class="list-unstyled">
          <li><a href="#" class="text-decoration-none text-secondary">Fonctionnalités</a></li>
          <li><a href="#" class="text-decoration-none text-secondary">Tarifs</a></li>
          <li><a href="#" class="text-decoration-none text-secondary">API</a></li>
        </ul>
      </div>
      <div class="col-md-2 mb-4">
        <h6 class="text-white">Ressources</h6>
        <ul class="list-unstyled">
          <li><a href="#" class="text-decoration-none text-secondary">Documentation</a></li>
          <li><a href="#" class="text-decoration-none text-secondary">Blog</a></li>
          <li><a href="#" class="text-decoration-none text-secondary">Support</a></li>
        </ul>
      </div>
      <div class="col-md-4 mb-4">
        <h6 class="text-white">Contact</h6>
        <p class="text-secondary"><i class="bi bi-envelope me-2"></i> contact@plagioscan.com</p>
        <div class="mt-3">
          <a href="#" class="text-secondary me-3"><i class="bi bi-twitter-x"></i></a>
          <a href="#" class="text-secondary me-3"><i class="bi bi-linkedin"></i></a>
          <a href="#" class="text-secondary"><i class="bi bi-github"></i></a>
        </div>
      </div>
    </div>
    <hr class="bg-secondary">
    <div class="text-center text-secondary small">
      © 2026 PlagioScan – Projet ingénieur (Laravel + Python + IA)
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
