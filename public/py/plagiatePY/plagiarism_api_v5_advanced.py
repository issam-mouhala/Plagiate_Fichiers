"""
Plagiarism Detection API v5 — Advanced Multi-Engine with ML Scoring
====================================================================
Nouveautes v5 :
  1. Embeddings avancés : all-mpnet-base-v2 (384-dim, meilleur que MiniLM)
  2. Paraphrase Detection : paraphrase-MiniLM-L12-v2
  3. Score global ML (XGBoost / LogisticRegression fallback)
  4. Fingerprint Caching : eviter le recalcul des embeddings/fingerprints
  5. ZIP optimisé : ThreadPoolExecutor pour le traitement parallele

Supporte :
  - Texte brut (.txt, .md)
  - Code source (.py, .js, .java, .c, .cpp...)
  - PDF (.pdf) — extraction texte + images
  - Word (.docx) — extraction texte + images
  - Images directes (.png, .jpg, .jpeg, .gif, .bmp, .webp)
  - ZIP contenant plusieurs fichiers (parallèle)

Detection (7 moteurs) :
  - Texte : TF-IDF + Semantic (mpnet) + Paraphrase + Winnowing + LCS + Stop-words FR
  - Code  : TF-IDF + AST + Winnowing + LCS + Semantic + Normalisation identifiants
  - Images: pHash + Feature comparison
  - ML    : XGBoost meta-classifier sur scores bruts

Tout retourne du JSON pour Laravel.

Dependances :
  pip install fastapi uvicorn scikit-learn numpy nltk sentence-transformers
  pip install Pillow imagehash python-multipart
  pip install PyMuPDF python-docx
  pip install xgboost          (optionnel — fallback sur LogisticRegression)
"""

import os
import re
import json
import ast
import hashlib
import difflib
import io
import math
import zipfile
import tempfile
import shutil
import time
import pickle
import threading
from typing import List, Dict, Any, Optional, Tuple
from collections import Counter
from concurrent.futures import ThreadPoolExecutor, as_completed

from fastapi import FastAPI, UploadFile, File, Form, HTTPException
from fastapi.responses import JSONResponse, FileResponse
from fastapi.middleware.cors import CORSMiddleware
import uvicorn

import numpy as np
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity

# ===== PIL / ImageHash =====
try:
    from PIL import Image
    HAS_PIL = True
except ImportError:
    HAS_PIL = False
    print("[WARN] Pillow non installe. pip install Pillow imagehash")

try:
    import imagehash
    HAS_IMAGEHASH = True
except ImportError:
    HAS_IMAGEHASH = False
    print("[WARN] imagehash non installe. pip install imagehash")

# ===== PyMuPDF (PDF) =====
try:
    import fitz  # PyMuPDF
    HAS_FITZ = True
except ImportError:
    HAS_FITZ = False
    print("[WARN] PyMuPDF non installe. pip install PyMuPDF")

# ===== python-docx (Word) =====
try:
    from docx import Document as DocxDocument
    HAS_DOCX = True
except ImportError:
    HAS_DOCX = False
    print("[WARN] python-docx non installe. pip install python-docx")

# ===== XGBoost (optionnel) =====
try:
    import xgboost as xgb
    HAS_XGBOOST = True
except ImportError:
    HAS_XGBOOST = False
    print("[WARN] XGBoost non installe. Fallback sur LogisticRegression.")

# ===========================================================================
# 1. CONFIGURATION
# ===========================================================================
DATA_FILE = "./submissions.json"
UPLOAD_DIR = "uploads"
IMAGES_DIR = os.path.join(UPLOAD_DIR, "images")
CACHE_DIR = "./fingerprint_cache"
MAX_RESULTS = 10
WINNOWING_WINDOW = 4
PARAGRAPH_MIN_LENGTH = 20
MAX_ZIP_FILE_SIZE = 100 * 1024 * 1024  # 100MB max par fichier dans ZIP
MAX_WORKERS = 4  # threads pour traitement parallele ZIP

# Seuils images
IMAGE_HASH_THRESHOLD = 10
IMAGE_FEATURE_THRESHOLD = 0.80

# Poids (7 moteurs maintenant)
WEIGHTS_TEXT = {
    "tfidf": 0.10,
    "semantic": 0.25,       # mpnet (plus fiable que MiniLM)
    "paraphrase": 0.15,     # paraphrase detection
    "winnowing": 0.15,
    "lcs": 0.15,
    "ml_score": 0.20,       # ML meta-score
}
WEIGHTS_CODE = {
    "tfidf": 0.15,
    "winnowing": 0.15,
    "ast": 0.25,
    "lcs": 0.15,
    "semantic": 0.10,
    "ml_score": 0.20,       # ML meta-score
}

LARAVEL_URL = os.environ.get("LARAVEL_URL", "http://localhost:8000")

app = FastAPI(title="Plagiarism Detection API v5 — Advanced", version="5.0.0")
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Créer les dossiers
os.makedirs(UPLOAD_DIR, exist_ok=True)
os.makedirs(IMAGES_DIR, exist_ok=True)
os.makedirs(CACHE_DIR, exist_ok=True)


# ===========================================================================
# 2. FINGERPRINT CACHE — Éviter le recalcul
# ===========================================================================
class FingerprintCache:
    """
    Cache persistant sur disque pour les fingerprints (embeddings, winnowing hashes, etc.).
    Utilise pickle pour une sérialisation rapide.
    Structure : { content_hash: { "semantic_emb": [...], "winnowing_fps": set, ... } }
    """

    def __init__(self, cache_dir: str):
        self.cache_dir = cache_dir
        self._lock = threading.Lock()
        self._memory_cache: Dict[str, Dict] = {}
        self._load()

    def _cache_path(self) -> str:
        return os.path.join(self.cache_dir, "fingerprints.pkl")

    def _load(self):
        """Charge le cache depuis le disque au démarrage."""
        path = self._cache_path()
        if os.path.exists(path):
            try:
                with open(path, "rb") as f:
                    self._memory_cache = pickle.load(f)
                print(f"[INFO] Cache charge : {len(self._memory_cache)} fingerprints")
            except Exception as e:
                print(f"[WARN] Erreur chargement cache : {e}")
                self._memory_cache = {}

    def _save(self):
        """Sauvegarde le cache sur disque."""
        path = self._cache_path()
        try:
            with open(path, "wb") as f:
                pickle.dump(self._memory_cache, f, protocol=pickle.HIGHEST_PROTOCOL)
        except Exception as e:
            print(f"[WARN] Erreur sauvegarde cache : {e}")

    @staticmethod
    def _content_hash(text: str) -> str:
        """Hash SHA-256 tronqué du contenu."""
        return hashlib.sha256(text.encode("utf-8", errors="ignore")).hexdigest()[:32]

    def get(self, text: str, key: str) -> Optional[Any]:
        """Récupère une valeur du cache."""
        ch = self._content_hash(text)
        entry = self._memory_cache.get(ch)
        if entry:
            return entry.get(key)
        return None

    def set(self, text: str, key: str, value: Any):
        """Stocke une valeur dans le cache et sauvegarde."""
        ch = self._content_hash(text)
        if ch not in self._memory_cache:
            self._memory_cache[ch] = {}
        self._memory_cache[ch][key] = value
        # Sauvegarde asynchrone toutes les 100 entrées ou à chaque set
        with self._lock:
            if len(self._memory_cache) % 50 == 0:
                self._save()

    def get_all(self, text: str) -> Optional[Dict]:
        """Récupère toutes les valeurs pour un texte."""
        ch = self._content_hash(text)
        return self._memory_cache.get(ch)

    def has(self, text: str, key: str) -> bool:
        """Vérifie si une clé existe."""
        ch = self._content_hash(text)
        entry = self._memory_cache.get(ch)
        return entry is not None and key in entry

    def size(self) -> int:
        return len(self._memory_cache)

    def clear(self):
        self._memory_cache = {}
        self._save()

    def flush(self):
        self._save()


# Instance globale
fingerprint_cache = FingerprintCache(CACHE_DIR)


# ===========================================================================
# 3. NLP
# ===========================================================================
_nlp_ready = False
_stop_words = set()
_stemmer = None

def init_nlp():
    global _nlp_ready, _stop_words, _stemmer
    if _nlp_ready:
        return
    try:
        import nltk
        try:
            nltk.data.find('corpora/stopwords')
        except LookupError:
            nltk.download('stopwords', quiet=True)
        from nltk.corpus import stopwords
        from nltk.stem import SnowballStemmer
        _stop_words = set(stopwords.words('french')) | set(stopwords.words('english'))
        _stop_words |= set(['le','la','les','un','une','des','du','de','et','est','en',
            'que','qui','dans','ce','il','ne','sur','se','pas','plus','par','je','avec',
            'tout','au','son','cette','mais','sont','aussi','ou','leur','y','a','ete',
            'pour','elle','nous','vous','ils','on','ses','sa','mon','ton','ma','ta',
            'nos','vos','ces','mes','tes','the','is','are','was','were','be','been',
            'have','has','had','do','does','did','will','would','could','should',
            'may','might','must','shall','can','this','that','these','those','it','its'])
        _stemmer = SnowballStemmer('french')
        _nlp_ready = True
        print("[INFO] NLP OK")
    except ImportError:
        print("[WARN] NLTK non installe. pip install nltk")


def stem_text(text):
    return " ".join(_stemmer.stem(w) for w in text.split()) if _stemmer else text

def remove_stop_words(text):
    return " ".join(w for w in text.split() if w.lower() not in _stop_words and len(w)>1) if _stop_words else text


# ===========================================================================
# 4. MODELE SEMANTIQUE AVANCÉ — all-mpnet-base-v2
# ===========================================================================
_semantic_model = None

def get_semantic_model():
    """
    Charge all-mpnet-base-v2 (768-dim, bien supérieur à all-MiniLM-L6-v2).
    - Score MTEB plus élevé
    - Meilleure compréhension sémantique multilingue
    - Embeddings 768-dim (vs 384-dim pour MiniLM)
    """
    global _semantic_model
    if _semantic_model is None:
        try:
            from sentence_transformers import SentenceTransformer
            print("[INFO] Chargement du modele all-mpnet-base-v2 (premier chargement lent)...")
            _semantic_model = SentenceTransformer("all-mpnet-base-v2")
            print("[INFO] Modele semantique all-mpnet-base-v2 OK (768-dim)")
        except ImportError:
            print("[WARN] sentence-transformers non installe.")
            return None
        except Exception as e:
            print(f"[ERROR] modele semantique : {e}")
            # Fallback sur MiniLM
            try:
                from sentence_transformers import SentenceTransformer
                _semantic_model = SentenceTransformer("all-MiniLM-L6-v2")
                print("[INFO] Fallback sur all-MiniLM-L6-v2 (384-dim)")
            except Exception:
                return None
    return _semantic_model


def get_embedding(text: str, cache_text: Optional[str] = None) -> Optional[np.ndarray]:
    """
    Calcule l'embedding d'un texte, avec cache.
    Retourne un vecteur numpy normalisé (768-dim pour mpnet).
    """
    cache_key = cache_text or text
    # Vérifier le cache
    cached = fingerprint_cache.get(cache_key, "semantic_emb")
    if cached is not None:
        return cached

    model = get_semantic_model()
    if not model:
        return None

    try:
        emb = model.encode([text], convert_to_numpy=True, normalize_embeddings=True)
        result = emb[0]
        # Stocker dans le cache
        fingerprint_cache.set(cache_key, "semantic_emb", result)
        return result
    except Exception as e:
        print(f"[ERROR] embedding : {e}")
        return None


def compute_semantic_similarity_advanced(texts: List[str], new_text: str) -> List[float]:
    """
    Similarité sémantique avec cache + batching.
    Si les embeddings existent déjà en cache, les réutilise.
    Sinon, encode par batch pour la performance.
    """
    model = get_semantic_model()
    if not model:
        return [0.0] * len(texts)

    # Embedding du nouveau texte
    new_emb = get_embedding(new_text)
    if new_emb is None:
        return [0.0] * len(texts)

    results = []
    for txt in texts:
        txt_emb = get_embedding(txt)
        if txt_emb is not None:
            sim = float(np.dot(new_emb, txt_emb))
            results.append(sim)
        else:
            results.append(0.0)

    return results


# ===========================================================================
# 5. PARAPHRASE DETECTION — paraphrase-MiniLM-L12-v2
# ===========================================================================
_paraphrase_model = None

def get_paraphrase_model():
    """
    Charge paraphrase-MiniLM-L12-v2.
    Ce modèle est spécialement entraîné pour la détection de paraphrases.
    Score cosine > 0.75 = paraphrase très probable.
    """
    global _paraphrase_model
    if _paraphrase_model is None:
        try:
            from sentence_transformers import SentenceTransformer
            print("[INFO] Chargement du modele paraphrase-MiniLM-L12-v2...")
            _paraphrase_model = SentenceTransformer("paraphrase-MiniLM-L12-v2")
            print("[INFO] Modele paraphrase OK")
        except ImportError:
            print("[WARN] sentence-transformers non installe.")
            return None
        except Exception as e:
            print(f"[ERROR] modele paraphrase : {e}")
            return None
    return _paraphrase_model


def compute_paraphrase_similarity(texts: List[str], new_text: str) -> List[float]:
    """
    Détecte les paraphrases entre new_text et chaque texte de la liste.
    Utilise le modèle paraphrase-MiniLM-L12-v2 avec cache.
    """
    model = get_paraphrase_model()
    if not model:
        return [-1.0] * len(texts)

    # Embedding du nouveau texte avec le modèle paraphrase
    cache_key_new = f"para_{new_text}"
    cached_new = fingerprint_cache.get(cache_key_new, "paraphrase_emb")
    if cached_new is not None:
        new_emb = cached_new
    else:
        try:
            new_emb = model.encode([new_text], convert_to_numpy=True, normalize_embeddings=True)[0]
            fingerprint_cache.set(cache_key_new, "paraphrase_emb", new_emb)
        except Exception:
            return [-1.0] * len(texts)

    results = []
    for txt in texts:
        cache_key_txt = f"para_{txt}"
        cached_txt = fingerprint_cache.get(cache_key_txt, "paraphrase_emb")
        if cached_txt is not None:
            txt_emb = cached_txt
        else:
            try:
                txt_emb = model.encode([txt], convert_to_numpy=True, normalize_embeddings=True)[0]
                fingerprint_cache.set(cache_key_txt, "paraphrase_emb", txt_emb)
            except Exception:
                results.append(-1.0)
                continue

        sim = float(np.dot(new_emb, txt_emb))
        results.append(sim)

    return results


# ===========================================================================
# 6. ML META-CLASSIFIER — XGBoost / LogisticRegression
# ===========================================================================
_ml_model = None
_ml_model_ready = False
_ml_model_type = None  # "xgboost" or "logistic"


def _build_synthetic_training_data():
    """
    Génère des données d'entraînement synthétiques pour le meta-classifier.
    Features : [tfidf, semantic, paraphrase, winnowing, lcs, ast]
    Target   : plagiarism_score normalisé [0, 1]
    """
    np.random.seed(42)
    n_samples = 2000

    X = []
    y = []

    for _ in range(n_samples):
        # Générer des scores aléatoires réalistes
        base = np.random.beta(2, 5)  # plupart des cas : faible plagiat
        is_plagiarized = np.random.random() < 0.25

        if is_plagiarized:
            target = np.random.uniform(0.5, 1.0)
            tfidf = target * np.random.uniform(0.7, 1.0)
            semantic = target * np.random.uniform(0.8, 1.0)
            paraphrase = target * np.random.uniform(0.7, 1.0) if np.random.random() < 0.6 else np.random.uniform(0, 0.3)
            winnowing = target * np.random.uniform(0.6, 1.0)
            lcs = target * np.random.uniform(0.5, 1.0)
        else:
            target = np.random.uniform(0, 0.4)
            tfidf = np.random.uniform(0, 0.3)
            semantic = np.random.uniform(0, 0.35)
            paraphrase = np.random.uniform(0, 0.3)
            winnowing = np.random.uniform(0, 0.25)
            lcs = np.random.uniform(0, 0.3)

        # AST (souvent -1 pour les textes)
        ast_score = target * np.random.uniform(0.5, 1.0) if np.random.random() < 0.4 else -1.0

        features = [tfidf, semantic, paraphrase, winnowing, lcs, ast_score]
        X.append(features)
        y.append(target)

    return np.array(X, dtype=np.float32), np.array(y, dtype=np.float32)


def get_ml_model():
    """
    Entraîne et retourne le meta-classifier ML.
    XGBoost si disponible, sinon LogisticRegression.
    Les features sont : [tfidf, semantic, paraphrase, winnowing, lcs, ast]
    """
    global _ml_model, _ml_model_ready, _ml_model_type

    if _ml_model_ready:
        return _ml_model

    try:
        X, y = _build_synthetic_training_data()

        if HAS_XGBOOST:
            _ml_model = xgb.XGBRegressor(
                n_estimators=100,
                max_depth=4,
                learning_rate=0.1,
                subsample=0.8,
                reg_alpha=0.1,
                reg_lambda=1.0,
                random_state=42,
                verbosity=0,
            )
            _ml_model_type = "xgboost"
        else:
            from sklearn.linear_model import LogisticRegression
            # Binariser y pour LogisticRegression
            y_binary = (y >= 0.5).astype(int)
            _ml_model = LogisticRegression(max_iter=500, random_state=42)
            _ml_model.fit(X, y_binary)
            _ml_model_type = "logistic"
            _ml_model_ready = True
            print("[INFO] ML meta-classifier OK (LogisticRegression)")
            return _ml_model

        _ml_model.fit(X, y)
        _ml_model_ready = True
        print(f"[INFO] ML meta-classifier OK ({_ml_model_type})")

    except Exception as e:
        print(f"[WARN] ML meta-classifier erreur : {e}")
        _ml_model = None
        _ml_model_type = "none"

    return _ml_model


def predict_ml_score(scores: Dict[str, float]) -> float:
    """
    Prédit le score de plagiat avec le meta-classifier ML.
    scores : {"tfidf": 0.8, "semantic": 0.6, "paraphrase": 0.4, "winnowing": 0.3, "lcs": 0.5, "ast": -1.0}
    Retourne : score ML [0, 1]
    """
    model = get_ml_model()
    if model is None:
        return 0.0

    try:
        features = np.array([[
            scores.get("tfidf", 0.0),
            scores.get("semantic", 0.0),
            scores.get("paraphrase", -1.0) if scores.get("paraphrase", -1.0) >= 0 else 0.0,
            scores.get("winnowing", 0.0),
            scores.get("lcs", 0.0),
            scores.get("ast", -1.0) if scores.get("ast", -1.0) >= 0 else 0.0,
        ]], dtype=np.float32)

        if _ml_model_type == "logistic":
            proba = model.predict_proba(features)[0]
            # Probabilité de la classe 1 (plagiat)
            return float(proba[1]) if len(proba) > 1 else float(proba[0])
        else:
            pred = model.predict(features)[0]
            return float(np.clip(pred, 0.0, 1.0))
    except Exception as e:
        return 0.0


# ===========================================================================
# 7. EXTRACTEURS DE FICHIERS — PDF, Word, Images
# ===========================================================================

def extract_pdf(filepath: str) -> Dict:
    if not HAS_FITZ:
        return {"text": "", "images": [], "pages": 0, "error": "PyMuPDF non installe"}
    doc = fitz.open(filepath)
    all_text, all_images = [], []
    num_pages = len(doc)
    for page_num in range(num_pages):
        page = doc[page_num]
        text = page.get_text()
        if text.strip():
            all_text.append(f"--- Page {page_num+1} ---\n{text}")
        for img_idx, img in enumerate(page.get_images(full=True)):
            try:
                xref = img[0]
                base_image = doc.extract_image(xref)
                if base_image:
                    img_bytes = base_image["image"]
                    pil_img = Image.open(io.BytesIO(img_bytes))
                    if pil_img.size[0] > 32 and pil_img.size[1] > 32:
                        all_images.append(pil_img)
            except Exception:
                continue
    doc.close()
    return {"text": "\n\n".join(all_text), "images": all_images, "pages": num_pages}


def extract_docx(filepath: str) -> Dict:
    if not HAS_DOCX:
        return {"text": "", "images": [], "error": "python-docx non installe"}
    doc = DocxDocument(filepath)
    all_text = []
    for para in doc.paragraphs:
        if para.text.strip():
            all_text.append(para.text)
    for table in doc.tables:
        for row in table.rows:
            row_text = [cell.text.strip() for cell in row.cells if cell.text.strip()]
            if row_text:
                all_text.append(" | ".join(row_text))
    all_images = []
    try:
        for rel in doc.part.rels.values():
            if "image" in rel.reltype:
                try:
                    img_data = rel.target_part.blob
                    pil_img = Image.open(io.BytesIO(img_data))
                    if pil_img.size[0] > 32 and pil_img.size[1] > 32:
                        all_images.append(pil_img)
                except Exception:
                    continue
    except Exception:
        pass
    return {"text": "\n\n".join(all_text), "images": all_images}


def extract_file(file_bytes: bytes, filename: str) -> Dict:
    ext = os.path.splitext(filename)[1].lower()
    if ext in ('.pdf',):
        tmp_path = None
        try:
            with tempfile.NamedTemporaryFile(suffix='.pdf', delete=False) as tmp:
                tmp.write(file_bytes); tmp_path = tmp.name
            result = extract_pdf(tmp_path)
            return {"text": result["text"], "images": result.get("images", []),
                    "file_type": "text",
                    "metadata": {"format": "pdf", "pages": result.get("pages", 0),
                                "images_extracted": len(result.get("images", []))}}
        except Exception as e:
            return {"text": "", "images": [], "file_type": "text", "metadata": {"error": str(e)}}
        finally:
            if tmp_path and os.path.exists(tmp_path): os.unlink(tmp_path)
    if ext in ('.docx',):
        tmp_path = None
        try:
            with tempfile.NamedTemporaryFile(suffix='.docx', delete=False) as tmp:
                tmp.write(file_bytes); tmp_path = tmp.name
            result = extract_docx(tmp_path)
            return {"text": result["text"], "images": result.get("images", []),
                    "file_type": "text",
                    "metadata": {"format": "docx", "images_extracted": len(result.get("images", []))}}
        except Exception as e:
            return {"text": "", "images": [], "file_type": "text", "metadata": {"error": str(e)}}
        finally:
            if tmp_path and os.path.exists(tmp_path): os.unlink(tmp_path)
    if ext in ('.png', '.jpg', '.jpeg', '.gif', '.bmp', '.webp', '.tiff', '.tif'):
        if HAS_PIL:
            try:
                pil_img = Image.open(io.BytesIO(file_bytes))
                if pil_img.mode != 'RGB': pil_img = pil_img.convert('RGB')
                return {"text": "", "images": [pil_img], "file_type": "image",
                        "metadata": {"format": ext.lstrip('.'), "width": pil_img.size[0], "height": pil_img.size[1]}}
            except Exception as e:
                return {"text": "", "images": [], "file_type": "image", "metadata": {"error": str(e)}}
        return {"text": "", "images": [], "file_type": "image", "metadata": {"error": "Pillow non installe"}}
    if ext in ('.zip',):
        return extract_zip(file_bytes)
    CODE_EXTS = {'.py','.js','.jsx','.ts','.tsx','.java','.c','.cpp','.h','.hpp',
                 '.cs','.php','.rb','.go','.rs','.swift','.kt','.scala','.r',
                 '.sql','.sh','.bash','.html','.css','.scss','.xml','.yaml','.yml',
                 '.json','.toml','.ini','.cfg','.conf'}
    try:
        text = file_bytes.decode('utf-8', errors='ignore')
    except Exception:
        text = file_bytes.decode('latin-1', errors='ignore')
    file_type = "code" if ext in CODE_EXTS else "text"
    return {"text": text, "images": [], "file_type": file_type, "metadata": {"format": ext.lstrip('.')}}


def extract_zip(zip_bytes: bytes) -> Dict:
    all_text, all_images = [], []
    metadata = {"format": "zip", "files": []}
    try:
        with zipfile.ZipFile(io.BytesIO(zip_bytes)) as zf:
            for name in zf.namelist():
                if name.startswith('__MACOSX') or name.endswith('/'): continue
                try:
                    data = zf.read(name)
                    ext = os.path.splitext(name)[1].lower()
                    if ext in ('.png','.jpg','.jpeg','.gif','.bmp','.webp') and HAS_PIL:
                        try:
                            img = Image.open(io.BytesIO(data))
                            if img.mode != 'RGB': img = img.convert('RGB')
                            all_images.append(img)
                        except Exception: pass
                    elif ext == '.pdf' and HAS_FITZ:
                        result = extract_pdf_from_bytes(data)
                        if result["text"]: all_text.append(f"=== {name} ===\n{result['text']}")
                        all_images.extend(result.get("images", []))
                    elif ext == '.docx' and HAS_DOCX:
                        result = extract_docx_from_bytes(data)
                        if result["text"]: all_text.append(f"=== {name} ===\n{result['text']}")
                        all_images.extend(result.get("images", []))
                    else:
                        try:
                            txt = data.decode('utf-8', errors='ignore')
                            all_text.append(f"=== {name} ===\n{txt}")
                        except Exception: pass
                    metadata["files"].append(name)
                except Exception: continue
    except Exception as e:
        metadata["error"] = str(e)
    return {"text": "\n\n".join(all_text), "images": all_images, "file_type": "text", "metadata": metadata}


def extract_pdf_from_bytes(data: bytes) -> Dict:
    tmp_path = None
    try:
        with tempfile.NamedTemporaryFile(suffix='.pdf', delete=False) as tmp:
            tmp.write(data); tmp_path = tmp.name
        return extract_pdf(tmp_path)
    finally:
        if tmp_path and os.path.exists(tmp_path): os.unlink(tmp_path)

def extract_docx_from_bytes(data: bytes) -> Dict:
    tmp_path = None
    try:
        with tempfile.NamedTemporaryFile(suffix='.docx', delete=False) as tmp:
            tmp.write(data); tmp_path = tmp.name
        return extract_docx(tmp_path)
    finally:
        if tmp_path and os.path.exists(tmp_path): os.unlink(tmp_path)


# ===========================================================================
# 8. IMAGE COMPARISON — pHash + Feature-based
# ===========================================================================

def compute_image_hash(img: Image.Image, hash_size: int = 16) -> Optional[str]:
    if not HAS_IMAGEHASH: return None
    try:
        if img.mode != 'RGB': img = img.convert('RGB')
        return str(imagehash.phash(img, hash_size=hash_size))
    except Exception: return None

def compute_image_features(img: Image.Image, size: tuple = (64, 64)) -> Optional[np.ndarray]:
    if not HAS_PIL: return None
    try:
        if img.mode != 'RGB': img = img.convert('RGB')
        img_small = img.resize(size, Image.LANCZOS)
        arr = np.array(img_small)
        features = []
        for channel in range(3):
            hist, _ = np.histogram(arr[:,:,channel], bins=64, range=(0, 256))
            features.extend(hist.tolist())
        vec = np.array(features, dtype=np.float32)
        norm = np.linalg.norm(vec)
        if norm > 0: vec = vec / norm
        return vec
    except Exception: return None

def compare_images(new_images, existing_image_data) -> List[Dict]:
    if not new_images or not existing_image_data or not HAS_PIL:
        return []
    matches = []
    for new_idx, new_img in enumerate(new_images):
        new_hash = compute_image_hash(new_img)
        new_features = compute_image_features(new_img)
        for existing in existing_image_data:
            ex_hash = existing.get("hash")
            ex_features_bytes = existing.get("features")
            if ex_features_bytes: ex_features = np.array(ex_features_bytes, dtype=np.float32)
            else: ex_features = None
            phash_dist = None; feature_sim = None
            if new_hash and ex_hash and HAS_IMAGEHASH:
                try:
                    h1 = imagehash.hex_to_hash(new_hash, hash_size=16)
                    h2 = imagehash.hex_to_hash(ex_hash, hash_size=16)
                    phash_dist = h1 - h2
                except Exception:
                    try:
                        h1 = imagehash.hex_to_hash(new_hash)
                        h2 = imagehash.hex_to_hash(ex_hash)
                        phash_dist = h1 - h2
                    except Exception: pass
            if new_features is not None and ex_features is not None:
                try:
                    n1, n2 = np.linalg.norm(new_features), np.linalg.norm(ex_features)
                    if n1 > 0 and n2 > 0: feature_sim = float(np.dot(new_features, ex_features) / (n1 * n2))
                    else: feature_sim = 0.0
                except Exception: pass
            is_match = False; level = "none"; confidence = 0.0
            if phash_dist is not None and phash_dist <= 5:
                is_match = True; level = "critical"; confidence = max(confidence, 1.0 - (phash_dist / 64.0))
            elif phash_dist is not None and phash_dist <= IMAGE_HASH_THRESHOLD:
                is_match = True; level = "high"; confidence = max(confidence, 1.0 - (phash_dist / 64.0))
            elif feature_sim is not None and feature_sim >= 0.95:
                is_match = True; level = "critical"; confidence = max(confidence, feature_sim)
            elif feature_sim is not None and feature_sim >= IMAGE_FEATURE_THRESHOLD:
                is_match = True; level = "high" if feature_sim >= 0.90 else "medium"; confidence = max(confidence, feature_sim)
            if is_match:
                matches.append({"new_image_index": new_idx, "matched_filename": existing.get("filename", "?"),
                    "matched_image_index": existing.get("image_index", 0), "level": level,
                    "confidence": round(confidence, 4), "phash_distance": phash_dist,
                    "feature_similarity": round(feature_sim, 4) if feature_sim else None})
    best_per_image = {}
    for m in matches:
        key = m["new_image_index"]
        if key not in best_per_image or m["confidence"] > best_per_image[key]["confidence"]:
            best_per_image[key] = m
    return sorted(best_per_image.values(), key=lambda x: x["confidence"], reverse=True)


# ===========================================================================
# 9. PRETRAITEMENT TEXTE / CODE
# ===========================================================================

def normalize_identifiers(code: str) -> str:
    id_pattern = re.compile(r'\b[a-zA-Z_][a-zA-Z0-9_]*\b')
    reserved = set([
        'if','else','elif','for','while','def','class','return','import','from','as',
        'try','except','finally','with','yield','lambda','and','or','not','in','is',
        'pass','break','continue','raise','assert','del','global','nonlocal','async','await',
        'int','str','float','bool','list','dict','set','tuple','None','True','False',
        'self','super','print','range','len','type','var','let','const','function',
        'new','this','null','undefined','switch','case','default','typeof','instanceof',
        'public','private','protected','static','final','abstract','interface','extends',
        'implements','package','throws','throw','catch','boolean','char','String','System',
        'include','define','ifdef','endif','struct','enum','typedef','sizeof','printf',
        'scanf','cout','cin','endl','main','args','argv','init','append','extend',
        'insert','remove','pop','sort','map','filter','reduce','input','open','close','read','write',
    ])
    id_map = {}; counter = [0]
    for token in id_pattern.findall(code):
        if token not in reserved and token not in id_map:
            id_map[token] = f"ID_{counter[0]}"; counter[0] += 1
    return id_pattern.sub(lambda m: id_map.get(m.group(0), m.group(0)), code)

def preprocess_code(code):
    code = re.sub(r'/\*.*?\*/', '', code, flags=re.DOTALL)
    code = re.sub(r'//.*?$', '', code, flags=re.MULTILINE)
    code = re.sub(r'#.*?$', '', code, flags=re.MULTILINE)
    code = re.sub(r'"""[\s\S]*?"""', ' """STR""" ', code)
    code = re.sub(r"'''[\s\S]*?'''", " '''STR''' ", code)
    code = re.sub(r'"[^"\\]*(\\.[^"\\]*)*"', ' "STR" ', code)
    code = re.sub(r"'[^'\\]*(\\.[^'\\]*)*'", " 'STR' ", code)
    code = re.sub(r'\b\d+\.?\d*\b', 'NUM', code)
    code = normalize_identifiers(code)
    return re.sub(r'\s+', ' ', code).strip()

def preprocess_text(text):
    text = text.lower()
    text = re.sub(r'[^\w\s]', ' ', text)
    if _nlp_ready:
        text = remove_stop_words(text)
        text = stem_text(text)
    return re.sub(r'\s+', ' ', text).strip()


# ===========================================================================
# 10. SPLITTING
# ===========================================================================

def split_into_paragraphs(text):
    paragraphs = re.split(r'\n\s*\n', text.strip())
    if len(paragraphs) <= 1 and len(text) > 200:
        sentences = re.split(r'[.!?]+', text)
        paragraphs, current = [], []
        for sent in sentences:
            current.append(sent.strip())
            if len(' '.join(current)) >= 100:
                paragraphs.append(' '.join(current)); current = []
        if current: paragraphs.append(' '.join(current))
    return [p.strip() for p in paragraphs if len(p.strip()) >= PARAGRAPH_MIN_LENGTH]

def split_code_into_blocks(code):
    blocks = []
    try:
        tree = ast.parse(code)
        ranges = [(n.lineno-1, n.end_lineno or n.lineno) for n in ast.walk(tree)
                  if isinstance(n, (ast.FunctionDef, ast.AsyncFunctionDef, ast.ClassDef))]
        ranges.sort()
        lines = code.split('\n'); covered = set()
        for s, e in ranges:
            b = [lines[i] for i in range(s, min(e, len(lines))) if i not in covered]
            if b: blocks.append('\n'.join(b)); covered.update(range(s, min(e, len(lines))))
        pre = [lines[i] for i in range(ranges[0][0] if ranges else 0) if i not in covered]
        if pre: blocks.insert(0, '\n'.join(pre))
    except (SyntaxError, Exception):
        blocks = [b.strip() for b in re.split(r'\n\s*\n', code) if len(b.strip()) >= 20]
    return blocks or [code]


# ===========================================================================
# 11. AST
# ===========================================================================

def code_to_ast_features(code):
    try: tree = ast.parse(code)
    except (SyntaxError, Exception): return None
    features = []
    class V(ast.NodeVisitor):
        def generic_visit(self, node):
            features.append(f"{node.__class__.__name__}({len(list(ast.iter_child_nodes(node)))})")
            if isinstance(node, ast.Call):
                if isinstance(node.func, ast.Name): features.append(f"CALL({node.func.id})")
                elif isinstance(node.func, ast.Attribute): features.append(f"CALL_ATTR({node.func.attr})")
            if isinstance(node, ast.BinOp): features.append(f"BINOP({node.op.__class__.__name__})")
            if isinstance(node, (ast.For, ast.While)): features.append(f"LOOP({node.__class__.__name__})")
            if isinstance(node, ast.If): features.append("COND(if)")
            if isinstance(node, ast.FunctionDef): features.append(f"FUNC({node.name})")
            if isinstance(node, ast.ClassDef): features.append(f"CLASS({node.name})")
            ast.NodeVisitor.generic_visit(self, node)
    V().visit(tree)
    return " ".join(features)

def code_tokens_sequence(code):
    try: tree = ast.parse(code)
    except (SyntaxError, Exception):
        return " ".join(re.findall(r'\b\w+\b|[+\-*/=<>!&|^~%]+|[{}()\[\];,]', code))
    tokens = []
    class V(ast.NodeVisitor):
        def generic_visit(self, node):
            if isinstance(node, ast.FunctionDef): tokens.extend(["DEF",node.name])
            elif isinstance(node, ast.ClassDef): tokens.extend(["CLASS",node.name])
            elif isinstance(node, ast.For): tokens.append("FOR")
            elif isinstance(node, ast.While): tokens.append("WHILE")
            elif isinstance(node, ast.If): tokens.append("IF")
            elif isinstance(node, ast.Return): tokens.append("RETURN")
            elif isinstance(node, ast.BinOp): tokens.append(f"OP_{node.op.__class__.__name__}")
            elif isinstance(node, ast.Compare): tokens.append("COMPARE")
            elif isinstance(node, ast.Call): tokens.append("CALL")
            elif isinstance(node, ast.Assign): tokens.append("ASSIGN")
            ast.NodeVisitor.generic_visit(self, node)
    V().visit(tree)
    return " ".join(tokens)


# ===========================================================================
# 12. WINNOWING
# ===========================================================================

def ngram_hashes(text, n=WINNOWING_WINDOW):
    if len(text) < n: return [hashlib.md5(text.encode()).hexdigest()] if text else []
    return [hashlib.md5(text[i:i+n].encode()).hexdigest() for i in range(len(text)-n+1)]

def winnow_select(hashes, w=WINNOWING_WINDOW):
    if not hashes or len(hashes) < w: return set(hashes)
    s = set()
    for i in range(len(hashes)-w+1):
        wh = hashes[i:i+w]
        s.add(hashes[i+wh.index(min(wh))])
    return s

def winnowing_similarity(t1, t2):
    # Vérifier le cache
    cache_key_pair = f"wn_{t1[:50]}_{t2[:50]}"
    cached = fingerprint_cache.get(cache_key_pair, "winnowing_sim")
    if cached is not None:
        return cached

    s1, s2 = winnow_select(ngram_hashes(t1)), winnow_select(ngram_hashes(t2))
    if not s1 and not s2: result = 1.0
    elif not s1 or not s2: result = 0.0
    else: result = len(s1&s2)/len(s1|s2)

    fingerprint_cache.set(cache_key_pair, "winnowing_sim", result)
    return result


# ===========================================================================
# 13. LCS
# ===========================================================================

def lcs_ratio(t1, t2):
    if not t1 and not t2: return 1.0
    if not t1 or not t2: return 0.0
    w1, w2 = t1.split()[:2000], t2.split()[:2000]
    m, n = len(w1), len(w2)
    if m*n > 4_000_000: return difflib.SequenceMatcher(None, w1, w2).ratio()
    dp = [[0]*(n+1) for _ in range(m+1)]
    for i in range(1,m+1):
        for j in range(1,n+1):
            dp[i][j] = dp[i-1][j-1]+1 if w1[i-1]==w2[j-1] else max(dp[i-1][j],dp[i][j-1])
    return (2*dp[m][n])/(m+n)


# ===========================================================================
# 14. COMBINAISON AVANCÉE (avec ML et Paraphrase)
# ===========================================================================

def combine_v5(scores: Dict[str, float], weights: Dict[str, float]) -> Tuple[float, Dict]:
    """
    Combine les scores avec pondération, en tenant compte du ML score.
    Si ml_score est disponible, il est utilisé comme feature supplémentaire.
    """
    active, total = {}, 0.0
    for e, w in weights.items():
        if scores.get(e, -1.0) >= 0:
            active[e] = w; total += w
    if not active: return 0.0, scores
    final, detail = 0.0, {}
    for e, w in active.items():
        nw = w/total; c = scores[e]*nw; final += c
        detail[e] = {"raw": round(scores[e],4), "weight": round(nw,3), "contribution": round(c,4)}
    return round(min(final,1.0),4), detail

def classify(score):
    if score >= 0.85: return "critical"
    if score >= 0.65: return "high"
    if score >= 0.45: return "medium"
    if score >= 0.25: return "low"
    return "none"


# ===========================================================================
# 15. PIPELINE COMPLET V5 (avec Paraphrase + ML)
# ===========================================================================

def detect_text_code_v5(processed, ast_features, token_seq, raw, sections, file_type,
                        existing, use_semantic=True, use_winnowing=True,
                        use_ast=True, use_lcs=True, use_paraphrase=True,
                        use_ml=True):
    """
    Pipeline avancé v5 avec 7 moteurs :
    TF-IDF, Semantic (mpnet), Paraphrase, Winnowing, LCS, AST (+ ML meta-score)
    """
    weights = WEIGHTS_CODE if file_type == "code" else WEIGHTS_TEXT
    results = []

    relevant = [s for s in existing if s["file_type"] in ("text", "code")]
    if not relevant:
        return []

    etxts = [s["processed"] for s in relevant]

    # TF-IDF
    try:
        v = TfidfVectorizer(ngram_range=(1,3), min_df=1)
        m = v.fit_transform([processed]+etxts)
        tfidf_scores = cosine_similarity(m[0:1],m[1:]).flatten()
    except ValueError:
        tfidf_scores = np.zeros(len(relevant))

    # Semantic (mpnet)
    sem_scores = compute_semantic_similarity_advanced(etxts, processed) if use_semantic else [0.0]*len(relevant)

    # Paraphrase detection
    para_scores = compute_paraphrase_similarity(etxts, processed) if use_paraphrase else [-1.0]*len(relevant)

    # Winnowing
    wn_scores = [winnowing_similarity(processed, s["processed"]) for s in relevant] if use_winnowing else [0.0]*len(relevant)

    # AST (code uniquement)
    ast_scores = []
    if use_ast and file_type == "code" and ast_features:
        for s in relevant:
            sa = s.get("ast_features", "")
            if sa:
                try:
                    vv = TfidfVectorizer(ngram_range=(1,2), min_df=1)
                    mm = vv.fit_transform([ast_features, sa])
                    ast_scores.append(float(cosine_similarity(mm[0:1],mm[1:])[0][0]))
                except ValueError: ast_scores.append(0.0)
            else: ast_scores.append(-1.0)
    else: ast_scores = [-1.0]*len(relevant)

    # LCS
    lcs_scores = []
    if use_lcs:
        for s in relevant:
            seq = s.get("token_sequence", "")
            lcs_scores.append(lcs_ratio(token_seq, seq) if (token_seq and seq) else lcs_ratio(processed, s["processed"]))
    else: lcs_scores = [0.0]*len(relevant)

    # ML meta-score
    ml_scores = []
    if use_ml:
        for i in range(len(relevant)):
            raw_scores = {
                "tfidf": float(tfidf_scores[i]),
                "semantic": float(sem_scores[i]),
                "paraphrase": float(para_scores[i]) if para_scores[i] >= 0 else -1.0,
                "winnowing": float(wn_scores[i]),
                "lcs": float(lcs_scores[i]),
                "ast": float(ast_scores[i]),
            }
            ml_pred = predict_ml_score(raw_scores)
            ml_scores.append(ml_pred)
    else: ml_scores = [0.0]*len(relevant)

    # Combiner pour chaque paire
    for i, s in enumerate(relevant):
        sd = {
            "tfidf": float(tfidf_scores[i]),
            "semantic": float(sem_scores[i]),
            "paraphrase": float(para_scores[i]) if para_scores[i] >= 0 else -1.0,
            "winnowing": float(wn_scores[i]),
            "lcs": float(lcs_scores[i]),
            "ast": float(ast_scores[i]),
            "ml_score": float(ml_scores[i]) if use_ml and ml_scores[i] > 0 else -1.0,
        }

        combined, detail = combine_v5(sd, weights)

        # Paragraph-level
        p_scores = compute_paragraph_scores(sections, s.get("sections", [])) if sections else []
        suspicious = [{"section_index": idx, "score": sc, "level": classify(sc),
                       "preview": (sections[idx][:200] if idx < len(sections) else "")}
                      for idx, sc in enumerate(p_scores) if sc >= 0.45]

        # Paraphrase detection details
        paraphrase_detected = para_scores[i] >= 0.75 if para_scores[i] >= 0 else False

        results.append({
            "submission_id": s["id"], "filename": s["filename"],
            "file_type": s["file_type"], "combined_score": combined,
            "level": classify(combined), "engines": detail,
            "paragraph_scores": p_scores, "suspicious_sections": suspicious,
            "paraphrase_detected": paraphrase_detected,
            "paraphrase_score": round(para_scores[i], 4) if para_scores[i] >= 0 else None,
            "ml_score": round(ml_scores[i], 4) if use_ml else None,
        })

    results.sort(key=lambda x: x["combined_score"], reverse=True)
    return [r for r in results if r["combined_score"] >= 0.10][:MAX_RESULTS]


# ===========================================================================
# 16. PARAGRAPH-LEVEL
# ===========================================================================

def compute_paragraph_scores(new_secs, existing_secs):
    if not new_secs or not existing_secs:
        return [0.0]*len(new_secs)
    scores = []
    for sec in new_secs:
        best = 0.0
        s1 = preprocess_text(sec)
        for es in existing_secs:
            s2 = preprocess_text(es)
            try:
                v = TfidfVectorizer(ngram_range=(1,2), min_df=1)
                mat = v.fit_transform([s1, s2])
                sim = cosine_similarity(mat[0:1], mat[1:])[0][0]
            except ValueError: sim = 0.0
            if sim > best: best = sim
        scores.append(round(best, 4))
    return scores


# ===========================================================================
# 17. BASE DE DONNÉES
# ===========================================================================

def load_submissions():
    if os.path.exists(DATA_FILE):
        with open(DATA_FILE, 'r', encoding='utf-8') as f:
            return json.load(f)
    return []

def save_submissions(subs):
    with open(DATA_FILE, 'w', encoding='utf-8') as f:
        json.dump(subs, f, indent=2, ensure_ascii=False)

def add_submission(filename, content, file_type, metadata=None,
                   images=None, sections=None, processed=None,
                   ast_features=None, token_sequence=None):
    subs = load_submissions()
    if processed is None:
        if file_type == "code":
            processed = preprocess_code(content)
            ast_features = code_to_ast_features(content)
            token_sequence = code_tokens_sequence(content)
            sections = split_code_into_blocks(content)
        else:
            processed = preprocess_text(content)
            sections = split_into_paragraphs(content)
    else:
        sections = sections or []
    image_data = []
    if images:
        for idx, img in enumerate(images):
            h = compute_image_hash(img)
            feat = compute_image_features(img)
            image_data.append({"hash": h, "features": feat.tolist() if feat is not None else None, "image_index": idx})
    sub = {
        "id": hashlib.md5(f"{filename}_{len(subs)}_{content[:100]}".encode()).hexdigest()[:12],
        "filename": filename, "original_content": content[:2000], "processed": processed,
        "file_type": file_type, "ast_features": ast_features, "token_sequence": token_sequence,
        "sections": sections, "images": image_data,
        "image_count": len(image_data) if image_data else 0,
        "content_length": len(content), "metadata": metadata or {},
    }
    subs.append(sub)
    save_submissions(subs)
    return sub["id"]


# ===========================================================================
# 18. ZIP AVANCÉ — Extraction individuelle + Parallèle
# ===========================================================================

CODE_EXTS_SET = {
    '.py','.js','.jsx','.ts','.tsx','.java','.c','.cpp','.h','.hpp',
    '.cs','.php','.rb','.go','.rs','.swift','.kt','.scala','.r',
    '.sql','.sh','.bash','.html','.css','.scss','.xml','.yaml','.yml',
    '.json','.toml','.ini','.cfg','.conf',
}
TEXT_EXTS_SET = {'.txt', '.md', '.rst', '.csv'}
IMAGE_EXTS_SET = {'.png', '.jpg', '.jpeg', '.gif', '.bmp', '.webp', '.tiff', '.tif'}
DOC_EXTS_SET = {'.pdf', '.docx'}
MIN_CONTENT_LENGTH = 50


def _extract_single_file(name: str, data: bytes) -> Optional[Dict]:
    """Extrait un seul fichier du ZIP. Appelée par ThreadPoolExecutor."""
    ext = os.path.splitext(name)[1].lower()
    basename = os.path.basename(name)

    entry = {
        "filename": name, "basename": basename, "extension": ext,
        "text": "", "images": [], "file_type": "text", "content_length": 0,
    }

    # Ignorer fichiers trop gros
    if len(data) > MAX_ZIP_FILE_SIZE:
        return None

    try:
        # Image
        if ext in IMAGE_EXTS_SET and HAS_PIL:
            img = Image.open(io.BytesIO(data))
            if img.mode != 'RGB': img = img.convert('RGB')
            entry["images"] = [img]; entry["file_type"] = "image"
            entry["content_length"] = len(data)
            return entry

        # PDF
        if ext == '.pdf' and HAS_FITZ:
            tmp = None
            try:
                with tempfile.NamedTemporaryFile(suffix='.pdf', delete=False) as t:
                    t.write(data); tmp = t.name
                result = extract_pdf(tmp)
                entry["text"] = result.get("text", "")
                entry["images"] = result.get("images", [])
                entry["content_length"] = len(entry["text"])
                return entry
            finally:
                if tmp and os.path.exists(tmp): os.unlink(tmp)

        # Word
        if ext == '.docx' and HAS_DOCX:
            tmp = None
            try:
                with tempfile.NamedTemporaryFile(suffix='.docx', delete=False) as t:
                    t.write(data); tmp = t.name
                result = extract_docx(tmp)
                entry["text"] = result.get("text", "")
                entry["images"] = result.get("images", [])
                entry["content_length"] = len(entry["text"])
                return entry
            finally:
                if tmp and os.path.exists(tmp): os.unlink(tmp)

        # Code / Texte brut
        txt = data.decode('utf-8', errors='ignore')
        entry["text"] = txt
        entry["file_type"] = "code" if ext in CODE_EXTS_SET else "text"
        entry["content_length"] = len(txt)
        if entry["text"] or entry["images"]:
            return entry
    except Exception:
        pass

    return None


def extract_zip_individual_parallel(zip_bytes: bytes) -> List[Dict]:
    """
    Extrait les fichiers du ZIP en PARALLÈLE avec ThreadPoolExecutor.
    Accélère significativement le traitement des ZIP volumineux (50MB+).
    """
    files = []
    file_list = []

    try:
        with zipfile.ZipFile(io.BytesIO(zip_bytes)) as zf:
            for name in zf.namelist():
                if name.startswith('__MACOSX') or name.endswith('/'):
                    continue
                basename = os.path.basename(name)
                if basename.startswith('.') and basename != '.':
                    continue
                try:
                    data = zf.read(name)
                    file_list.append((name, data))
                except Exception:
                    continue
    except Exception:
        return []

    # Extraction parallèle
    with ThreadPoolExecutor(max_workers=MAX_WORKERS) as executor:
        futures = {executor.submit(_extract_single_file, name, data): name
                   for name, data in file_list}
        for future in as_completed(futures):
            try:
                result = future.result()
                if result is not None:
                    files.append(result)
            except Exception:
                continue

    return files


# ===========================================================================
# 19. COMPARAISON DEUX FICHIERS V5
# ===========================================================================

def compare_two_files_v5(file_a: Dict, file_b: Dict,
                         use_semantic=True, use_winnowing=True,
                         use_ast=True, use_lcs=True,
                         use_paraphrase=True, use_ml=True) -> Dict:
    text_a = file_a.get("text", "")
    text_b = file_b.get("text", "")
    if not text_a.strip() or not text_b.strip():
        return {"file_a": file_a["filename"], "file_b": file_b["filename"],
                "combined_score": 0.0, "level": "none", "engines": {},
                "paraphrase_detected": False, "paraphrase_score": None, "ml_score": None}
    if file_a["file_type"] != file_b["file_type"]:
        return {"file_a": file_a["filename"], "file_b": file_b["filename"],
                "combined_score": 0.0, "level": "none", "engines": {},
                "paraphrase_detected": False, "paraphrase_score": None, "ml_score": None}

    is_code = file_a["file_type"] == "code"
    weights = WEIGHTS_CODE if is_code else WEIGHTS_TEXT

    if is_code:
        proc_a = preprocess_code(text_a); proc_b = preprocess_code(text_b)
    else:
        proc_a = preprocess_text(text_a); proc_b = preprocess_text(text_b)

    if not proc_a.strip() or not proc_b.strip():
        return {"file_a": file_a["filename"], "file_b": file_b["filename"],
                "combined_score": 0.0, "level": "none", "engines": {},
                "paraphrase_detected": False, "paraphrase_score": None, "ml_score": None}

    # TF-IDF
    try:
        v = TfidfVectorizer(ngram_range=(1,3), min_df=1)
        m = v.fit_transform([proc_a, proc_b])
        tfidf_sim = float(cosine_similarity(m[0:1], m[1:])[0][0])
    except ValueError: tfidf_sim = 0.0

    # Semantic
    if use_semantic:
        model = get_semantic_model()
        if model:
            try:
                emb = model.encode([proc_a, proc_b], convert_to_numpy=True, normalize_embeddings=True)
                sem_sim = float(np.dot(emb[0], emb[1]))
            except Exception: sem_sim = 0.0
        else: sem_sim = 0.0
    else: sem_sim = -1.0

    # Paraphrase
    para_sim = -1.0
    if use_paraphrase:
        para_model = get_paraphrase_model()
        if para_model:
            try:
                para_emb = para_model.encode([proc_a, proc_b], convert_to_numpy=True, normalize_embeddings=True)
                para_sim = float(np.dot(para_emb[0], para_emb[1]))
            except Exception: para_sim = -1.0

    # Winnowing
    wn_sim = winnowing_similarity(proc_a, proc_b) if use_winnowing else -1.0

    # AST
    ast_sim = -1.0
    if use_ast and is_code:
        ast_a = code_to_ast_features(text_a); ast_b = code_to_ast_features(text_b)
        if ast_a and ast_b:
            try:
                vv = TfidfVectorizer(ngram_range=(1,2), min_df=1)
                mm = vv.fit_transform([ast_a, ast_b])
                ast_sim = float(cosine_similarity(mm[0:1], mm[1:])[0][0])
            except ValueError: ast_sim = 0.0

    # LCS
    if use_lcs:
        if is_code:
            tok_a = code_tokens_sequence(text_a); tok_b = code_tokens_sequence(text_b)
            lcs_sim = lcs_ratio(tok_a, tok_b) if (tok_a and tok_b) else lcs_ratio(proc_a, proc_b)
        else: lcs_sim = lcs_ratio(proc_a, proc_b)
    else: lcs_sim = 0.0

    # ML meta-score
    ml_pred = 0.0
    if use_ml:
        raw_scores = {
            "tfidf": tfidf_sim, "semantic": sem_sim,
            "paraphrase": para_sim, "winnowing": wn_sim,
            "lcs": lcs_sim, "ast": ast_sim,
        }
        ml_pred = predict_ml_score(raw_scores)

    # Combiner
    scores = {
        "tfidf": tfidf_sim, "semantic": sem_sim, "paraphrase": para_sim,
        "winnowing": wn_sim, "lcs": lcs_sim, "ast": ast_sim,
        "ml_score": ml_pred if use_ml and ml_pred > 0 else -1.0,
    }
    combined, detail = combine_v5(scores, weights)

    return {
        "file_a": file_a["filename"], "file_b": file_b["filename"],
        "combined_score": combined, "level": classify(combined), "engines": detail,
        "paraphrase_detected": para_sim >= 0.75 if para_sim >= 0 else False,
        "paraphrase_score": round(para_sim, 4) if para_sim >= 0 else None,
        "ml_score": round(ml_pred, 4) if use_ml else None,
    }


# ===========================================================================
# 20. ENDPOINTS — JSON pour Laravel
# ===========================================================================

@app.post("/api/check")
async def api_check(
    file: Optional[UploadFile] = File(None),
    text: Optional[str] = Form(None),
    filename: Optional[str] = Form(None),
    file_type: str = Form("auto"),
    use_semantic: bool = Form(True),
    use_winnowing: bool = Form(True),
    use_ast: bool = Form(True),
    use_lcs: bool = Form(True),
    use_paraphrase: bool = Form(True),
    use_ml: bool = Form(True),
):
    """
    Endpoint principal v5.
    Nouveaux paramètres : use_paraphrase, use_ml
    """
    init_nlp()
    # Pré-charger les modèles en arrière-plan
    get_semantic_model()
    get_paraphrase_model()
    get_ml_model()

    if not file and not text:
        return JSONResponse(status_code=400, content={"success": False, "message": "Fournir 'file' ou 'text'"})

    extracted_images, raw_text, detected_type, extraction_metadata = [], "", file_type, {}

    if file:
        file_bytes = await file.read()
        original_filename = file.filename or "file"
        if file_type == "auto":
            extraction = extract_file(file_bytes, original_filename)
            raw_text = extraction["text"]
            extracted_images = extraction.get("images", [])
            detected_type = extraction["file_type"]
            extraction_metadata = extraction.get("metadata", {})
        else:
            try: raw_text = file_bytes.decode('utf-8', errors='ignore')
            except Exception: raw_text = file_bytes.decode('latin-1', errors='ignore')
    else:
        raw_text = text
        original_filename = filename or "inline.txt"
        detected_type = file_type if file_type != "auto" else "text"

    # Image seule
    if detected_type == "image" and not raw_text:
        existing = load_submissions()
        all_existing_images = []
        for s in existing:
            for img_data in s.get("images", []):
                all_existing_images.append({**img_data, "filename": s["filename"]})
        image_matches = compare_images(extracted_images, all_existing_images)
        max_img_score = max([m["confidence"] for m in image_matches], default=0)
        max_img_level = image_matches[0]["level"] if image_matches else "none"
        return JSONResponse(content={
            "success": True, "data": {
                "filename": original_filename, "detected_type": "image",
                "extraction": extraction_metadata,
                "content_analysis": {"analyzed": False, "reason": "Image seule", "text_matches": []},
                "image_analysis": {"analyzed": True, "images_checked": len(extracted_images),
                    "image_matches": image_matches, "max_score": max_img_score, "max_level": max_img_level},
                "plagiarism_detected": max_img_score >= 0.70,
                "overall_score": max_img_score, "overall_level": max_img_level,
                "api_version": "5.0.0",
            }
        })

    # Prétraiter
    if detected_type == "code":
        processed = preprocess_code(raw_text)
        ast_f = code_to_ast_features(raw_text)
        tok_seq = code_tokens_sequence(raw_text)
        sections = split_code_into_blocks(raw_text)
    else:
        processed = preprocess_text(raw_text)
        ast_f, tok_seq = None, None
        sections = split_into_paragraphs(raw_text)

    existing = load_submissions()
    text_matches = []
    if processed and existing:
        text_matches = detect_text_code_v5(
            processed=processed, ast_features=ast_f, token_seq=tok_seq,
            raw=raw_text, sections=sections, file_type=detected_type,
            existing=existing,
            use_semantic=use_semantic and detected_type == "text",
            use_winnowing=use_winnowing,
            use_ast=use_ast and detected_type == "code",
            use_lcs=use_lcs,
            use_paraphrase=use_paraphrase and detected_type == "text",
            use_ml=use_ml,
        )

    max_text_score = text_matches[0]["combined_score"] if text_matches else 0
    max_text_level = text_matches[0]["level"] if text_matches else "none"

    # Images
    image_matches, max_img_score, max_img_level = [], 0, "none"
    if extracted_images and existing:
        all_existing_images = []
        for s in existing:
            for img_data in s.get("images", []):
                all_existing_images.append({**img_data, "filename": s["filename"]})
        image_matches = compare_images(extracted_images, all_existing_images)
        max_img_score = max([m["confidence"] for m in image_matches], default=0)
        max_img_level = image_matches[0]["level"] if image_matches else "none"

    # Score global
    overall_score = max(max_text_score, max_img_score) if max_text_score > 0 or max_img_score > 0 else max(max_text_score, max_img_score)
    levels_order = {"critical": 4, "high": 3, "medium": 2, "low": 1, "none": 0}
    overall_level = max([max_text_level, max_img_level], key=lambda x: levels_order.get(x, 0))

    # Paraphrase summary
    paraphrase_matches = [m for m in text_matches if m.get("paraphrase_detected", False)]

    levels = Counter(m["level"] for m in text_matches)

    return JSONResponse(content={
        "success": True, "data": {
            "filename": original_filename, "detected_type": detected_type,
            "extraction": extraction_metadata, "content_length": len(raw_text),
            "images_extracted": len(extracted_images), "num_comparisons": len(existing),
            "content_analysis": {
                "analyzed": bool(processed and existing),
                "max_score": max_text_score, "max_level": max_text_level,
                "summary": {"critical": levels.get("critical",0), "high": levels.get("high",0),
                           "medium": levels.get("medium",0), "low": levels.get("low",0)},
                "paragraphs_analysed": len(sections), "text_matches": text_matches,
                "paraphrase_matches_count": len(paraphrase_matches),
            },
            "image_analysis": {
                "analyzed": bool(extracted_images and existing),
                "images_checked": len(extracted_images),
                "image_matches": image_matches,
                "max_score": max_img_score, "max_level": max_img_level,
            },
            "plagiarism_detected": overall_score >= 0.25,
            "overall_score": overall_score, "overall_level": overall_level,
            "api_version": "5.0.0",
            "engines_used": ["tfidf", "semantic_mpnet", "paraphrase", "winnowing", "lcs", "ast", "ml_meta"],
        }
    })


@app.post("/api/add")
async def api_add(file: UploadFile = File(...), file_type: str = Form("auto"),
                  metadata: Optional[str] = Form(None)):
    file_bytes = await file.read()
    filename = file.filename
    extraction = extract_file(file_bytes, filename)
    content = extraction["text"]; images = extraction.get("images", [])
    ftype = extraction["file_type"]; meta = extraction.get("metadata", {})
    if metadata:
        try: meta.update(json.loads(metadata))
        except: pass
    sub_id = add_submission(filename=filename, content=content, file_type=ftype, metadata=meta, images=images)
    return JSONResponse(content={"success": True, "id": sub_id, "filename": filename, "type": ftype, "images_stored": len(images), "content_length": len(content)})


@app.post("/api/add/text")
async def api_add_text(text: str = Form(...), filename: str = Form("text.txt"), metadata: Optional[str] = Form(None)):
    meta = json.loads(metadata) if metadata else {}
    sub_id = add_submission(filename, text, "text", meta)
    return JSONResponse(content={"success": True, "id": sub_id, "filename": filename})


@app.post("/api/add/code")
async def api_add_code(code: str = Form(...), filename: str = Form("code.py"), metadata: Optional[str] = Form(None)):
    meta = json.loads(metadata) if metadata else {}
    sub_id = add_submission(filename, code, "code", meta)
    return JSONResponse(content={"success": True, "id": sub_id, "filename": filename})


@app.get("/api/stats")
async def api_stats():
    subs = load_submissions()
    return JSONResponse(content={"success": True, "data": {
        "total": len(subs), "text": sum(1 for s in subs if s["file_type"]=="text"),
        "code": sum(1 for s in subs if s["file_type"]=="code"),
        "image": sum(1 for s in subs if s["file_type"]=="image"),
        "total_images": sum(s.get("image_count",0) for s in subs),
        "total_chars": sum(s.get("content_length",0) for s in subs),
        "cache_size": fingerprint_cache.size(),
    }})


@app.get("/api/list")
async def api_list(limit: int = 50, file_type: Optional[str] = None):
    subs = load_submissions()
    if file_type: subs = [s for s in subs if s["file_type"] == file_type]
    return JSONResponse(content={"success": True,
        "data": [{"id": s["id"], "filename": s["filename"], "file_type": s["file_type"],
                   "length": s.get("content_length",0), "images": s.get("image_count",0)}
                  for s in subs[:limit]]})


@app.delete("/api/delete/{sid}")
async def api_delete(sid: str):
    subs = load_submissions()
    n = len(subs)
    subs = [s for s in subs if s["id"] != sid]
    if len(subs) == n:
        return JSONResponse(status_code=404, content={"success": False, "message": "Non trouve"})
    save_submissions(subs)
    return JSONResponse(content={"success": True, "id": sid})


@app.get("/api/health")
async def api_health():
    return JSONResponse(content={"success": True, "data": {
        "status": "healthy", "version": "5.0.0", "nlp": _nlp_ready,
        "semantic_model": "all-mpnet-base-v2" if get_semantic_model() else None,
        "paraphrase_model": "paraphrase-MiniLM-L12-v2" if get_paraphrase_model() else None,
        "ml_model": _ml_model_type,
        "pdf_extraction": HAS_FITZ, "word_extraction": HAS_DOCX,
        "image_comparison": HAS_PIL and HAS_IMAGEHASH,
        "corpus_size": len(load_submissions()),
        "cache_size": fingerprint_cache.size(),
    }})


@app.post("/api/cache/clear")
async def api_cache_clear():
    """Vide le cache des fingerprints."""
    fingerprint_cache.clear()
    return JSONResponse(content={"success": True, "message": "Cache vidé", "new_size": 0})


@app.post("/api/cache/flush")
async def api_cache_flush():
    """Force la sauvegarde du cache sur disque."""
    fingerprint_cache.flush()
    return JSONResponse(content={"success": True, "message": "Cache sauvegardé", "size": fingerprint_cache.size()})


# ===========================================================================
# 21. ZIP ENDPOINT V5 — Parallèle + tous les moteurs
# ===========================================================================

@app.post("/api/check-zip")
async def api_check_zip(
    file: UploadFile = File(...),
    use_semantic: bool = Form(True),
    use_winnowing: bool = Form(True),
    use_ast: bool = Form(True),
    use_lcs: bool = Form(True),
    use_paraphrase: bool = Form(True),
    use_ml: bool = Form(True),
    cross_compare: bool = Form(True),
    add_to_database: bool = Form(True),
):
    """
    Endpoint ZIP v5 optimisé :
    - Extraction parallèle (ThreadPoolExecutor)
    - 7 moteurs par fichier (TF-IDF, mpnet, Paraphrase, Winnowing, LCS, AST, ML)
    - Cache des fingerprints
    """
    init_nlp()
    get_semantic_model()
    get_paraphrase_model()
    get_ml_model()

    t_start = time.time()

    zip_bytes = await file.read()
    original_filename = file.filename or "archive.zip"

    try:
        with zipfile.ZipFile(io.BytesIO(zip_bytes)) as zf:
            pass
    except zipfile.BadZipFile:
        return JSONResponse(status_code=400, content={"success": False, "message": "Le fichier n'est pas un ZIP valide."})

    # === 1. Extraction PARALLÈLE ===
    extracted_files = extract_zip_individual_parallel(zip_bytes)

    if not extracted_files:
        return JSONResponse(content={"success": True, "data": {
            "filename": original_filename, "files_extracted": [],
            "cross_file_analysis": [], "per_file_database_analysis": [],
            "image_analysis": {"analyzed": False, "image_matches": []},
            "overall_score": 0.0, "overall_level": "none", "plagiarism_detected": False,
            "message": "Aucun fichier analysable trouvé.", "api_version": "5.0.0",
        }})

    text_code_files = [f for f in extracted_files if f["file_type"] in ("text", "code") and len(f.get("text", "")) >= MIN_CONTENT_LENGTH]
    image_files = [f for f in extracted_files if f["images"]]

    t_extraction = time.time() - t_start

    # === 2. Comparaison CROISÉE parallèle ===
    cross_matches = []
    if cross_compare and len(text_code_files) > 1:
        # Créer toutes les paires
        pairs = []
        for i in range(len(text_code_files)):
            for j in range(i + 1, len(text_code_files)):
                pairs.append((text_code_files[i], text_code_files[j]))

        # Traiter en parallèle
        with ThreadPoolExecutor(max_workers=MAX_WORKERS) as executor:
            futures = [executor.submit(
                compare_two_files_v5, fa, fb,
                use_semantic, use_winnowing, use_ast, use_lcs,
                use_paraphrase, use_ml
            ) for fa, fb in pairs]
            for future in as_completed(futures):
                try:
                    result = future.result()
                    if result["combined_score"] >= 0.10:
                        cross_matches.append(result)
                except Exception:
                    continue

        cross_matches.sort(key=lambda x: x["combined_score"], reverse=True)
        cross_matches = cross_matches[:20]

    t_cross = time.time() - t_start

    # === 3. Comparaison avec BASE DE DONNÉES ===
    existing = load_submissions()
    per_file_results = []

    for f in text_code_files:
        if f["file_type"] == "code":
            processed = preprocess_code(f["text"])
            ast_f = code_to_ast_features(f["text"])
            tok_seq = code_tokens_sequence(f["text"])
            sections = split_code_into_blocks(f["text"])
        else:
            processed = preprocess_text(f["text"])
            ast_f, tok_seq = None, None
            sections = split_into_paragraphs(f["text"])

        matches = detect_text_code_v5(
            processed=processed, ast_features=ast_f, token_seq=tok_seq,
            raw=f["text"], sections=sections, file_type=f["file_type"],
            existing=existing,
            use_semantic=use_semantic and f["file_type"] == "text",
            use_winnowing=use_winnowing,
            use_ast=use_ast and f["file_type"] == "code",
            use_lcs=use_lcs,
            use_paraphrase=use_paraphrase and f["file_type"] == "text",
            use_ml=use_ml,
        )

        max_score = matches[0]["combined_score"] if matches else 0
        max_level = matches[0]["level"] if matches else "none"
        best_match = matches[0]["filename"] if matches else None
        para_detected = any(m.get("paraphrase_detected", False) for m in matches)

        per_file_results.append({
            "filename": f["filename"],
            "file_type": f["file_type"],
            "content_length": len(f["text"]),
            "max_score": max_score,
            "max_level": max_level,
            "best_match": best_match,
            "paraphrase_detected": para_detected,
            "matches_count": len(matches),
            "matches": matches[:5],  # Top 5 par fichier
        })

        # Ajouter à la base si demandé
        if add_to_database:
            try:
                add_submission(
                    filename=f["filename"], content=f["text"],
                    file_type=f["file_type"],
                    metadata={"source_zip": original_filename},
                )
            except Exception:
                pass

    t_total = time.time() - t_start

    # === 4. Image analysis ===
    image_matches_all = []
    # === Save extracted images to disk for dashboard display ===
    saved_new_images = {}  # {0: "img_new_0_filename_0.png", ...}
    saved_existing_images = {}  # {"filename_0": "img_ex_filename_0.png", ...}
    if image_files:
        img_counter = 0
        for f in image_files:
            for local_idx, img in enumerate(f["images"]):
                try:
                    safe_base = re.sub(r'[^a-zA-Z0-9._-]', '_', f.get("filename", "unknown"))
                    safe_name = f"img_new_{img_counter}_{safe_base}_{local_idx}.png"
                    save_path = os.path.join(IMAGES_DIR, safe_name)
                    img_rgb = img.convert('RGB') if img.mode != 'RGB' else img
                    img_rgb.save(save_path, 'PNG')
                    saved_new_images[img_counter] = safe_name
                    img_counter += 1
                except Exception as e:
                    print(f"[WARN] Save new image {img_counter} failed: {e}")
    if existing:
        for s in existing:
            for img_data in s.get("images", []):
                try:
                    safe_base = re.sub(r'[^a-zA-Z0-9._-]', '_', s.get("filename", "unknown"))
                    img_idx = img_data.get("image_index", 0)
                    safe_name = f"img_ex_{safe_base}_{img_idx}.png"
                    save_path = os.path.join(IMAGES_DIR, safe_name)
                    if not os.path.exists(save_path):
                        # Try to reconstruct from raw bytes if stored
                        raw_bytes = img_data.get("image_bytes")
                        if raw_bytes:
                            pil = Image.open(io.BytesIO(raw_bytes))
                            pil_rgb = pil.convert('RGB') if pil.mode != 'RGB' else pil
                            pil_rgb.save(save_path, 'PNG')
                        else:
                            # Create a small placeholder
                            placeholder = Image.new('RGB', (200, 200), (240, 240, 240))
                            from PIL import ImageDraw, ImageFont
                            draw = ImageDraw.Draw(placeholder)
                            draw.text((10, 90), f"Image: {safe_base}", fill=(100, 100, 100))
                            placeholder.save(save_path, 'PNG')
                    saved_existing_images[f"{safe_base}_{img_idx}"] = safe_name
                except Exception as e:
                    print(f"[WARN] Save existing image failed: {e}")

    if image_files and existing:
        all_new_images = []
        for f in image_files:
            for img in f["images"]:
                all_new_images.append(img)
        all_existing_images = []
        for s in existing:
            for img_data in s.get("images", []):
                all_existing_images.append({**img_data, "filename": s["filename"]})
        if all_new_images and all_existing_images:
            image_matches_all = compare_images(all_new_images, all_existing_images)

    # Attach saved filenames to each match for the dashboard
    for match in image_matches_all:
        new_idx = match.get("new_image_index", 0)
        if new_idx in saved_new_images:
            match["new_image_src"] = saved_new_images[new_idx]
        matched_fname = match.get("matched_filename", "")
        matched_img_idx = match.get("matched_image_index", 0)
        safe_matched = re.sub(r'[^a-zA-Z0-9._-]', '_', matched_fname)
        key = f"{safe_matched}_{matched_img_idx}"
        if key in saved_existing_images:
            match["matched_image_src"] = saved_existing_images[key]

    # === 5. Score global ===
    all_scores = [r["max_score"] for r in per_file_results]
    cross_scores = [m["combined_score"] for m in cross_matches]
    img_scores = [m["confidence"] for m in image_matches_all]
    all_combined = all_scores + cross_scores + img_scores
    overall_score = max(all_combined) if all_combined else 0.0
    overall_level = classify(overall_score)

    # Paraphrase summary
    total_para = sum(1 for r in per_file_results if r.get("paraphrase_detected"))
    cross_para = sum(1 for m in cross_matches if m.get("paraphrase_detected"))

    return JSONResponse(content={
        "success": True, "data": {
            "filename": original_filename,
            "files_extracted": [
                {"filename": f["filename"], "file_type": f["file_type"],
                 "content_length": f.get("content_length", 0)}
                for f in extracted_files
            ],
            "files_analyzed": len(text_code_files),
            "timing": {
                "extraction_seconds": round(t_extraction, 2),
                "cross_compare_seconds": round(t_cross, 2),
                "total_seconds": round(t_total, 2),
            },
            "cross_file_analysis": cross_matches,
            "per_file_database_analysis": per_file_results,
            "image_analysis": {
                "analyzed": bool(image_files and existing),
                "images_checked": len(image_files),
                "image_matches": image_matches_all[:10],
                "max_score": max(img_scores) if img_scores else 0,
                "saved_new_images": saved_new_images,
                "saved_existing_images": saved_existing_images,
            },
            "paraphrase_summary": {
                "total_paraphrases_detected": total_para + cross_para,
                "per_file_paraphrases": total_para,
                "cross_file_paraphrases": cross_para,
            },
            "overall_score": overall_score,
            "overall_level": overall_level,
            "plagiarism_detected": overall_score >= 0.25,
            "api_version": "5.0.0",
            "engines_used": ["tfidf", "semantic_mpnet", "paraphrase", "winnowing", "lcs", "ast", "ml_meta"],
        }
    })


# ===========================================================================
# 22. PARAPHRASE CHECK ENDPOINT (nouveau)
# ===========================================================================

@app.post("/api/check-paraphrase")
async def api_check_paraphrase(
    text1: str = Form(...),
    text2: str = Form(...),
):
    """
    Endpoint dédié pour la détection de paraphrase entre deux textes.
    Utilise paraphrase-MiniLM-L12-v2.
    """
    get_paraphrase_model()
    model = get_paraphrase_model()
    if not model:
        return JSONResponse(status_code=503, content={"success": False, "message": "Modele paraphrase non disponible"})

    try:
        emb = model.encode([text1, text2], convert_to_numpy=True, normalize_embeddings=True)
        score = float(np.dot(emb[0], emb[1]))

        # Interprétation
        if score >= 0.85:
            interpretation = "Paraphrase très probable (rewriting significatif)"
            level = "critical"
        elif score >= 0.75:
            interpretation = "Paraphrase probable (reformulation)"
            level = "high"
        elif score >= 0.60:
            interpretation = "Similarité sémantique notable (idées proches)"
            level = "medium"
        elif score >= 0.40:
            interpretation = "Certaines ressemblances (thèmes communs)"
            level = "low"
        else:
            interpretation = "Textes différents"
            level = "none"

        return JSONResponse(content={
            "success": True, "data": {
                "paraphrase_score": round(score, 4),
                "level": level,
                "interpretation": interpretation,
                "is_paraphrase": score >= 0.75,
                "model": "paraphrase-MiniLM-L12-v2",
            }
        })
    except Exception as e:
        return JSONResponse(status_code=500, content={"success": False, "message": str(e)})


# ===========================================================================
# 23. SERVE EXTRACTED IMAGES
# ===========================================================================

@app.get("/api/serve-image/{filename}")
async def serve_image(filename: str):
    """
    Sert une image extraite depuis uploads/images/.
    Utilise par le dashboard Laravel pour afficher les images comparees.
    """
    safe_name = os.path.basename(filename)
    file_path = os.path.join(IMAGES_DIR, safe_name)

    if not os.path.exists(file_path):
        return JSONResponse(status_code=404, content={"success": False, "message": "Image non trouvee"})

    real_path = os.path.realpath(file_path)
    real_dir = os.path.realpath(IMAGES_DIR)
    if not real_path.startswith(real_dir):
        return JSONResponse(status_code=403, content={"success": False, "message": "Acces interdit"})

    media_types = {
        '.png': 'image/png', '.jpg': 'image/jpeg', '.jpeg': 'image/jpeg',
        '.gif': 'image/gif', '.bmp': 'image/bmp', '.webp': 'image/webp',
    }
    ext = os.path.splitext(safe_name)[1].lower()
    return FileResponse(file_path, media_type=media_types.get(ext, 'image/png'))


# ===========================================================================
# 24. ESTIMATION DU TEMPS DE TRAITEMENT
# ===========================================================================

def estimate_processing_time(file_bytes: bytes, filename: str, is_zip: bool = False,
                               use_semantic: bool = True, use_paraphrase: bool = True,
                               cross_compare: bool = True) -> Dict:
    """
    Estime le temps de traitement avant l'analyse.
    Retourne un dict avec estimation détaillée par étape.
    """
    t = time.time()
    estimates = {
        "extraction": 0.0,
        "preprocessing": 0.0,
        "database_comparison": 0.0,
        "cross_comparison": 0.0,
        "image_comparison": 0.0,
        "ml_models": 0.0,
        "total": 0.0,
        "details": [],
    }

    file_size_mb = len(file_bytes) / (1024 * 1024)
    existing = load_submissions()
    corpus_size = len(existing)

    # --- ML models loading penalty ---
    ml_penalty = 0.0
    if use_semantic and _semantic_model is None:
        ml_penalty += 5.0  # mpnet ~5s first load
        estimates["details"].append("Chargement modele semantic (mpnet): ~5s")
    if use_paraphrase and _paraphrase_model is None:
        ml_penalty += 3.0  # paraphrase ~3s first load
        estimates["details"].append("Chargement modele paraphrase: ~3s")
    if not _ml_model_ready:
        ml_penalty += 0.5
        estimates["details"].append("Entrainement ML classifier: ~0.5s")
    estimates["ml_models"] = round(ml_penalty, 1)

    if not is_zip:
        # === SINGLE FILE ===
        ext = os.path.splitext(filename)[1].lower()

        # Extraction
        if ext == '.pdf':
            try:
                with zipfile.ZipFile(io.BytesIO(file_bytes[:1024])):
                    pass
            except Exception:
                pass
            # Estimate pages from file size
            est_pages = max(1, int(file_size_mb * 3))  # ~3 pages per MB
            extraction_time = 0.2 + est_pages * 0.3
            estimates["details"].append(f"PDF extraction: ~{extraction_time:.1f}s (est. {est_pages} pages)")
        elif ext == '.docx':
            extraction_time = 0.3 + file_size_mb * 0.2
            estimates["details"].append(f"DOCX extraction: ~{extraction_time:.1f}s")
        elif ext in ('.png', '.jpg', '.jpeg', '.gif', '.bmp', '.webp'):
            extraction_time = 0.1
            estimates["details"].append(f"Image extraction: ~0.1s")
        else:
            extraction_time = 0.05 + file_size_mb * 0.02
            estimates["details"].append(f"Texte/Code extraction: ~{extraction_time:.1f}s")
        estimates["extraction"] = round(extraction_time, 2)

        # Preprocessing
        estimates["preprocessing"] = 0.1

        # Database comparison
        if corpus_size > 0:
            # Per comparison: TF-IDF(0.01) + Semantic(0.15 cached / 0.5 new) + Winnowing(0.03) + LCS(0.05-0.5)
            per_comp = 0.08  # average with cache
            if use_semantic:
                per_comp += 0.12
            if use_paraphrase:
                per_comp += 0.10
            db_time = corpus_size * per_comp
            estimates["database_comparison"] = round(db_time, 2)
            estimates["details"].append(f"Comparaison corpus ({corpus_size} fichiers): ~{db_time:.1f}s")

        # Cross comparison (N/A for single file)
        estimates["cross_comparison"] = 0.0

        # Image comparison
        estimates["image_comparison"] = 0.0

    else:
        # === ZIP FILE ===
        # Quick scan
        num_files = 0
        num_text = 0
        num_code = 0
        num_pdf = 0
        num_docx = 0
        num_images = 0
        total_text_size = 0

        try:
            with zipfile.ZipFile(io.BytesIO(file_bytes)) as zf:
                for name in zf.namelist():
                    if name.startswith('__MACOSX') or name.endswith('/'):
                        continue
                    num_files += 1
                    ext = os.path.splitext(name)[1].lower()
                    if ext in ('.png', '.jpg', '.jpeg', '.gif', '.bmp', '.webp'):
                        num_images += 1
                    elif ext == '.pdf':
                        num_pdf += 1
                        num_text += 1
                    elif ext == '.docx':
                        num_docx += 1
                        num_text += 1
                    elif ext in CODE_EXTS_SET:
                        num_code += 1
                    elif ext in TEXT_EXTS_SET or ext not in IMAGE_EXTS_SET | DOC_EXTS_SET:
                        num_text += 1
                    try:
                        info = zf.getinfo(name)
                        total_text_size += info.file_size
                    except Exception:
                        pass
        except Exception:
            num_files = max(1, int(file_size_mb * 5))
            num_text = num_files

        # Extraction
        extraction_time = 0.3 + num_files * 0.05  # base + per file
        extraction_time += num_pdf * 0.5  # PDF slower
        extraction_time += num_docx * 0.3
        estimates["extraction"] = round(extraction_time, 2)
        estimates["details"].append(f"Extraction ZIP ({num_files} fichiers, {num_pdf} PDF, {num_docx} DOCX): ~{extraction_time:.1f}s")

        # Preprocessing
        preprocessing_time = num_text * 0.05 + num_code * 0.08
        estimates["preprocessing"] = round(preprocessing_time, 2)

        # Database comparison (per text/code file × corpus)
        text_code_count = num_text + num_code
        if corpus_size > 0 and text_code_count > 0:
            per_comp = 0.08
            if use_semantic:
                per_comp += 0.12
            if use_paraphrase:
                per_comp += 0.10
            db_time = text_code_count * corpus_size * per_comp
            estimates["database_comparison"] = round(db_time, 2)
            estimates["details"].append(f"Comparaison base ({text_code_count} fichiers × {corpus_size} corpus): ~{db_time:.1f}s")

        # Cross comparison
        if cross_compare and text_code_count > 1:
            n_pairs = text_code_count * (text_code_count - 1) // 2
            cross_per_pair = 0.15
            if use_semantic:
                cross_per_pair += 0.10
            if use_paraphrase:
                cross_per_pair += 0.08
            cross_time = n_pairs * cross_per_pair
            estimates["cross_comparison"] = round(cross_time, 2)
            estimates["details"].append(f"Comparaison croisee ({n_pairs} paires): ~{cross_time:.1f}s")

        # Image comparison
        if num_images > 0 and corpus_size > 0:
            # Count existing images
            ex_imgs = sum(len(s.get("images", [])) for s in existing)
            if ex_imgs > 0:
                img_time = num_images * ex_imgs * 0.02  # pHash + features
                estimates["image_comparison"] = round(img_time, 2)
                estimates["details"].append(f"Comparaison images ({num_images} nouvelles × {ex_imgs} existantes): ~{img_time:.1f}s")

    estimates["total"] = round(
        estimates["extraction"] + estimates["preprocessing"] +
        estimates["database_comparison"] + estimates["cross_comparison"] +
        estimates["image_comparison"] + estimates["ml_models"], 1
    )

    # Level
    total = estimates["total"]
    if total <= 5:
        estimates["level"] = "fast"
        estimates["level_label"] = "Rapide"
    elif total <= 15:
        estimates["level"] = "medium"
        estimates["level_label"] = "Moyen"
    elif total <= 60:
        estimates["level"] = "slow"
        estimates["level_label"] = "Lent"
    else:
        estimates["level"] = "very_slow"
        estimates["level_label"] = "Tres lent"

    estimates["computation_time"] = round(time.time() - t, 4)
    return estimates


@app.post("/api/estimate")
async def api_estimate_time(
    file: UploadFile = File(...),
    is_zip: bool = Form(False),
    use_semantic: bool = Form(True),
    use_paraphrase: bool = Form(True),
    cross_compare: bool = Form(True),
):
    """
    Endpoint d'estimation du temps de traitement.
    Scan le fichier/ZIP sans faire l'analyse complete.
    """
    file_bytes = await file.read()
    filename = file.filename or "unknown"

    ext = os.path.splitext(filename)[1].lower()
    actual_is_zip = ext == '.zip' or is_zip

    estimate = estimate_processing_time(
        file_bytes, filename,
        is_zip=actual_is_zip,
        use_semantic=use_semantic,
        use_paraphrase=use_paraphrase,
        cross_compare=cross_compare,
    )

    return JSONResponse(content={
        "success": True,
        "data": {
            "filename": filename,
            "file_size_mb": round(len(file_bytes) / (1024 * 1024), 2),
            "is_zip": actual_is_zip,
            "estimate": estimate,
        }
    })


# ===========================================================================
# LANCEMENT
# ===========================================================================
if __name__ == "__main__":
    print("=" * 60)
    print("  Plagiarism Detection API v5 — Advanced")
    print("  Modeles : all-mpnet-base-v2 + paraphrase-MiniLM-L12-v2")
    print(f"  ML      : {'XGBoost' if HAS_XGBOOST else 'LogisticRegression (fallback)'}")
    print(f"  Cache   : {CACHE_DIR}")
    print("=" * 60)
    uvicorn.run(app, host="0.0.0.0", port=5000)
