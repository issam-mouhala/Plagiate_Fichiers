PlagioScan - Système de Détection Anti-Plagiat

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat-square&logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/Python-3.9+-3776AB?style=flat-square&logo=python&logoColor=white" alt="Python">
  <img src="https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=flat-square&logo=bootstrap&logoColor=white" alt="Bootstrap">
  <img src="https://img.shields.io/badge/License-MIT-green?style=flat-square" alt="License">
</p>

**PlagioScan** est une application web complète de détection de plagiat qui combine une interface Laravel/Blade moderne avec un moteur d'analyse Python puissant. Le système prend en charge l'analyse de fichiers texte, code source, PDF, DOCX et images pour détecter les similarités avec une base de documents de référence.

---

## Table des matières

-   [Fonctionnalités](#-fonctionnalités)
-   [Architecture du système](#-architecture-du-système)
-   [Prérequis](#-prérequis)
-   [Installation](#-installation)
-   [Lancement rapide](#-lancement-rapide)
-   [API Python - Moteur d'analyse](#-api-python---moteur-danalyse)
-   [Bibliothèques Python requises](#-bibliothèques-python-requises)
-   [Endpoints API](#-endpoints-api)
-   [Structure du projet](#-structure-du-projet)
-   [Technologies utilisées](#-technologies-utilisées)
-   [Captures d'écran](#-captures-décran)
-   [Licence](#-licence)

---

## Fonctionnalités

-   **Analyse de texte** : Détection de plagiat textuel avec algorithmes TF-IDF, Winnowing et LCS (Longest Common Subsequence)
-   **Analyse sémantique BERT** : Détection des reformulations intelligentes et paraphrases grâce aux embeddings sémantiques
-   **Analyse de code source** : Support de 15+ langages (Python, Java, C++, JavaScript, PHP, etc.) avec détection de similarité structurelle
-   **Analyse de documents** : Extraction et analyse de fichiers PDF et DOCX (texte + images embarquées)
-   **Analyse d'images** : Comparaison par perceptual hashing (pHash) et similarité de features pour les images extraites
-   **Rapports détaillés** : Visualisation interactive avec score global, répartition par niveau de sévérité, détails par moteur d'analyse
-   **Interface moderne** : Design professionnel responsive avec Bootstrap 5, animations fluides et navigation sticky

---

## Architecture du système

```
┌─────────────────────────────────────────────────────────────────────┐
│                           NAVIGATEUR                                │
│  ┌─────────────┐   ┌───────────── ┐   ┌────────────────────────┐    │
│  │ Page Accueil│─▶│ Page Upload  │──▶│  Page Résultats        │    │
│  │  (/)        │   │  (/upload)  │    │  (/analyse)            │    │
│  └─────────────┘   └──────┬───────┘   └────────▲───────────────┘    │
│                           │                       │                 │
└───────────────────────────┼───────────────────────┼─────────────────┘
                            │  HTTP POST (fichier)   │  JSON réponse
                            ▼                       │
┌───────────────────────────────────────────────────┴──────────────────┐
│                    LARAVEL 12 (Port 8000)                            │
│                    PlagiatController                                  │
│                                                                      │
│  - Réception du fichier uploadé                                      │
│  - Forward vers l'API Python                                        │
│  - Mise en forme des données                                         │
│  - Rendu de la vue Blade avec résultats                              │
└───────────────────────────┬──────────────────────────────────────────┘
                            │  HTTP POST
                            ▼
┌─────────────────────────────────────────────────────────────────────┐
│                    API PYTHON (Port 5000)                             │
│                    Moteur d'analyse                                   │
│                                                                      │
│  ┌─────────────┐  ┌──────────────┐  ┌─────────────┐  ┌──────────┐  │
│  │   TF-IDF    │  │  BERT NLP    │  │ Winnowing   │  │  pHash   │  │
│  │  Vectoriser │  │  Sémantique  │  │  + LCS      │  │  Image   │  │
│  └─────────────┘  └──────────────┘  └─────────────┘  └──────────┘  │
│                                                                      │
│  ┌─────────────────────────────────────────────────────────────┐    │
│  │              Base de documents de référence                   │    │
│  │        (fichiers texte, code, images indexés)                │    │
│  └─────────────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────────────┘
```

> Le projet est composé de **deux services** qui communiquent via HTTP : l'application Laravel (ce dépôt) et le moteur d'analyse Python (service séparé).

---

## Prérequis

Assurez-vous d'avoir les éléments suivants installés sur votre machine :

### Pour l'application Laravel (ce dépôt)

| Outil        | Version minimale | Lien                                        |
| ------------ | ---------------- | ------------------------------------------- |
| **PHP**      | 8.2+             | [php.net](https://www.php.net/)             |
| **Composer** | 2.x              | [getcomposer.org](https://getcomposer.org/) |
| **Node.js**  | 18+              | [nodejs.org](https://nodejs.org/)           |
| **npm**      | 9+               | (inclus avec Node.js)                       |
| **SQLite**   | 3.x              | (souvent inclus avec PHP)                   |

### Pour le moteur d'analyse Python (service séparé)

| Outil                   | Version minimale | Lien                                      |
| ----------------------- | ---------------- | ----------------------------------------- |
| **Python**              | 3.9+             | [python.org](https://www.python.org/)     |
| **pip**                 | 23+              | (inclus avec Python)                      |
| **Git LFS** (optionnel) | 3.x              | Pour les fichiers de référence volumineux |

---

## Installation

### 1. Cloner le dépôt

```bash
git clone https://github.com/issam-mouhala/Plagiate_Fichiers.git
cd Plagiate_Fichiers
```

### 2. Installer les dépendances PHP

```bash
composer install
```

### 3. Configurer l'environnement

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Initialiser la base de données

```bash
# Créer le fichier SQLite
touch database/database.sqlite

# Exécuter les migrations
php artisan migrate
```

> **Note** : Par défaut, le projet utilise SQLite. Pour MySQL ou PostgreSQL, modifiez `DB_CONNECTION` et les paramètres associés dans le fichier `.env`.

### 5. Installer les dépendances frontend

```bash
npm install
```

### 6. Compiler les assets

```bash
# En développement (avec rechargement automatique)
npm run dev

# Ou en production
npm run build
```

---

## Lancement rapide

L'application nécessite **deux serveurs** pour fonctionner :

### Terminal 1 — Démarrer le moteur d'analyse Python

```bash
# Naviguer vers le répertoire de l'API Python (service séparé)
cd /chemin/vers/lapi-python

# Activer l'environnement virtuel (recommandé)
python -m venv venv
source venv/bin/activate      # Linux / macOS
# venv\Scripts\activate       # Windows

# Installer les dépendances Python
pip install -r requirements.txt

# Lancer l'API
python app.py
# ou : flask run --host=0.0.0.0 --port=5000
```

L'API sera accessible sur `http://localhost:5000`.

### Terminal 2 — Démarrer l'application Laravel

```bash
cd /chemin/vers/Plagiate_Fichiers

# Lancer le serveur Laravel
php artisan serve
```

L'application sera accessible sur `http://localhost:8000`.

### Vérification

```bash
# Vérifier que l'API Python est en marche
curl http://localhost:5000/api/health

# Réponse attendue : {"status": "ok"} ou similaire
```

### Utilisation alternative (concurrent)

```bash
# Lancer tous les services en parallèle (depuis le répertoire du projet)
composer dev
```

---

## API Python - Moteur d'analyse

Le moteur d'analyse Python est un **service séparé** qui gère le traitement des fichiers et la comparaison avec la base de documents de référence. Ce service n'est pas inclus dans ce dépôt.

### Rôle du moteur Python

Le moteur Python effectue les opérations suivantes lors de la réception d'un fichier :

1. **Extraction du contenu** : Parse le texte, le code, les PDF (PyPDF2/pdfplumber) et DOCX (python-docx)
2. **Extraction d'images** : Récupère les images embarquées dans les documents
3. **Analyse textuelle** : Compare avec la base de référence via TF-IDF, Winnowing, LCS
4. **Analyse sémantique** : Utilise un modèle BERT (sentence-transformers) pour la similarité sémantique
5. **Analyse de code** : Détecte la similarité structurelle entre fichiers source
6. **Analyse d'images** : Compare via perceptual hashing et feature matching (imagehash, OpenCV/sklearn)
7. **Agrégation** : Combine les scores de chaque moteur avec un système de pondération
8. **Retour** : Envoie un rapport JSON détaillé à l'application Laravel

---

## Bibliothèques Python requises

Voici les bibliothèques Python nécessaires pour le moteur d'analyse. Créez un fichier `requirements.txt` dans le répertoire de l'API Python :

```txt
# ===== Framework web =====
flask==3.1.*
flask-cors==5.0.*

# ===== NLP & Sémantique (BERT) =====
sentence-transformers==3.3.*
torch>=2.1.0
transformers>=4.40.0

# ===== Traitement de texte =====
nltk==3.9.*
scikit-learn==1.6.*
numpy>=1.26.0,<3.0.0

# ===== Extraction de documents =====
PyPDF2==3.0.*
pdfplumber==0.11.*
python-docx==1.1.*
python-pptx==1.0.*

# ===== Traitement d'images =====
Pillow==11.1.*
imagehash==4.3.*
opencv-python-headless==4.10.*

# ===== Analyse de code =====
tree-sitter==0.24.*
tree-sitter-languages==1.10.*

# ===== Utilitaires =====
python-multipart==0.0.20
requests==2.32.*
tqdm==4.67.*
```

### Installation rapide

```bash
# Créer un environnement virtuel
python -m venv venv
source venv/bin/activate      # Linux / macOS
# venv\Scripts\activate       # Windows

# Installer toutes les dépendances
pip install -r requirements.txt

# Télécharger les modèles NLTK nécessaires
python -c "import nltk; nltk.download('punkt'); nltk.download('stopwords'); nltk.download('wordnet')"

# Télécharger le modèle BERT (au premier lancement, téléchargement automatique)
# sentence-transformers/all-MiniLM-L6-v2 (~80 MB)
```

### Explication des dépendances

| Bibliothèque                            | Rôle dans PlagioScan                                                 |
| --------------------------------------- | -------------------------------------------------------------------- |
| `flask` + `flask-cors`                  | Serveur HTTP pour l'API REST (port 5000)                             |
| `sentence-transformers` + `torch`       | Modèle BERT pour la similarité sémantique (détection de paraphrases) |
| `nltk`                                  | Tokenization, stopwords, lemmatisation pour le prétraitement texte   |
| `scikit-learn`                          | TF-IDF vectorizer, calcul de similarité cosinus                      |
| `PyPDF2` + `pdfplumber`                 | Extraction de texte et images depuis les fichiers PDF                |
| `python-docx`                           | Extraction de texte depuis les fichiers Word (.docx)                 |
| `python-pptx`                           | Extraction de texte depuis les fichiers PowerPoint (.pptx)           |
| `Pillow` + `imagehash`                  | Perceptual hashing (pHash) pour la comparaison d'images              |
| `opencv-python-headless`                | Extraction de features visuelles pour la similarité d'images         |
| `tree-sitter` + `tree-sitter-languages` | Parsing syntaxique pour l'analyse de similarité de code source       |
| `python-multipart`                      | Gestion des uploads de fichiers multipart/form-data                  |
| `numpy`                                 | Calculs numériques, opérations sur les vecteurs et matrices          |

---

## Endpoints API

Le contrôleur Laravel (`PlagiatController`) communique avec l'API Python via les endpoints suivants :

### `GET /api/health`

Vérifie que le moteur d'analyse est opérationnel.

```bash
curl http://localhost:5000/api/health
```

**Réponse attendue :**

```json
{
    "status": "ok",
    "version": "1.0.0"
}
```

### `POST /api/check`

Soumet un fichier pour analyse de plagiat.

```bash
curl -X POST http://localhost:5000/api/check \
  -F "file=@mon_document.pdf"
```

**Réponse :**

```json
{
    "data": {
        "filename": "mon_document.pdf",
        "detected_type": "pdf",
        "extraction": { "format": "pdf", "pages": 12 },
        "content_length": 45230,
        "images_extracted": 3,
        "num_comparisons": 150,
        "plagiarism_detected": true,
        "overall_score": 0.45,
        "overall_level": "medium",
        "content_analysis": {
            "text_matches": [
                {
                    "filename": "reference_001.pdf",
                    "combined_score": 0.72,
                    "level": "high",
                    "engines": {
                        "tfidf": {
                            "raw": 0.65,
                            "weight": 0.3,
                            "contribution": 0.195
                        },
                        "bert": {
                            "raw": 0.78,
                            "weight": 0.4,
                            "contribution": 0.312
                        },
                        "winnowing": {
                            "raw": 0.55,
                            "weight": 0.2,
                            "contribution": 0.11
                        },
                        "lcs": {
                            "raw": 0.5,
                            "weight": 0.1,
                            "contribution": 0.05
                        }
                    },
                    "suspicious_sections": [
                        {
                            "level": "high",
                            "score": 0.85,
                            "preview": "Texte suspect..."
                        }
                    ]
                }
            ],
            "max_score": 0.72,
            "max_level": "high",
            "paragraphs_analysed": 15,
            "summary": { "critical": 0, "high": 2, "medium": 5, "low": 8 }
        },
        "image_analysis": {
            "analyzed": true,
            "images_checked": 3,
            "images_in_database": 200,
            "image_matches": [
                {
                    "matched_filename": "ref_image_042.png",
                    "new_image_index": 0,
                    "confidence": 0.82,
                    "level": "critical",
                    "phash_distance": 5,
                    "feature_similarity": 0.91
                }
            ],
            "summary": { "critical": 1, "high": 0, "medium": 0, "low": 0 }
        }
    }
}
```

### `POST /api/add`

Ajoute un fichier à la base de documents de référence.

```bash
curl -X POST http://localhost:5000/api/add \
  -F "file=@nouveau_reference.pdf"
```

---

## Structure du projet

```
Plagiate_Fichiers/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── PlagiatController.php    # Contrôleur principal
│   └── Models/
│       └── User.php
├── bootstrap/
├── config/
├── database/
│   └── migrations/
├── public/
│   └── index.php                        # Point d'entrée web
├── resources/
│   └── views/
│       ├── accueil/index.blade.php      # Page d'accueil
│       ├── upload/index.blade.php       # Page d'upload de fichiers
│       └── analyse/index.blade.php      # Page de résultats d'analyse
├── routes/
│   └── web.php                          # Routes de l'application
├── .env.example                         # Variables d'environnement
├── artisan                              # CLI Laravel
├── composer.json                        # Dépendances PHP
├── package.json                         # Dépendances frontend
└── vite.config.js                       # Configuration Vite
```

### Routes principales

| Méthode | URL        | Description                                      |
| ------- | ---------- | ------------------------------------------------ |
| `GET`   | `/`        | Page d'accueil                                   |
| `GET`   | `/accueil` | Page d'accueil (alias)                           |
| `GET`   | `/upload`  | Formulaire d'upload de fichier                   |
| `POST`  | `/analyse` | Traitement du fichier et affichage des résultats |

---

## Technologies utilisées

| Couche                   | Technologie                     | Version   |
| ------------------------ | ------------------------------- | --------- |
| **Backend**              | Laravel                         | 12.x      |
| **PHP**                  | PHP                             | 8.2+      |
| **Base de données**      | SQLite                          | 3.x       |
| **Template**             | Blade                           | (Laravel) |
| **CSS Framework**        | Bootstrap                       | 5.3.x     |
| **Icônes**               | Bootstrap Icons                 | 1.11.x    |
| **Typographie**          | Inter (Google Fonts)            | -         |
| **Build frontend**       | Vite                            | 7.x       |
| **HTTP Client**          | Axios                           | 1.11+     |
| **Moteur d'analyse**     | Python (Flask)                  | 3.9+      |
| **NLP**                  | Sentence-Transformers (BERT)    | 3.3.x     |
| **Traitement texte**     | scikit-learn, NLTK              | -         |
| **Traitement documents** | PyPDF2, pdfplumber, python-docx | -         |
| **Traitement images**    | Pillow, imagehash, OpenCV       | -         |

---

## Captures d'écran

> _[Ajoutez vos captures d'écran ici]_

| Page                                         | Description                                     |
| -------------------------------------------- | ----------------------------------------------- |
| ![Accueil](docs/screenshots/accueil.png)     | Page d'accueil avec présentation de l'outil     |
| ![Upload](docs/screenshots/upload.png)       | Formulaire d'upload multi-fichiers              |
| ![Résultats](docs/screenshots/resultats.png) | Rapport d'analyse avec score et correspondances |
| ![Détails](docs/screenshots/details.png)     | Détails par moteur d'analyse                    |

---

## Licence

Ce projet est sous licence [MIT](LICENSE). Vous êtes libre de l'utiliser, le modifier et le distribuer.

---

<p align="center">
  Développé par <strong>Issam Mouhala</strong> — 2025
</p>
