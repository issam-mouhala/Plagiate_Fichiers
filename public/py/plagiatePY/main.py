import os
import re
import json
import hashlib
from typing import List, Dict, Any, Optional
from fastapi import FastAPI, UploadFile, File, Form, HTTPException
from fastapi.responses import JSONResponse
import uvicorn
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity
import numpy as np

# ========== Configuration ==========
DATA_FILE = "submissions.json"      # Stockage des soumissions existantes
MAX_RESULTS = 10                    # Nombre max de similarités retournées
SIMILARITY_THRESHOLD = 0.3          # Seuil en dessous duquel on ignore

app = FastAPI(title="Plagiarism Detection API", description="Détection de plagiat texte/code")

# ========== Prétraitement spécifique pour le code ==========
def preprocess_code(code: str) -> str:
    """
    Nettoie du code source : enlève les commentaires, normalise les espaces,
    remplace les littéraux et (optionnellement) les noms d'identifiants.
    """
    # Enlève les commentaires multilignes /* ... */
    code = re.sub(r'/\*.*?\*/', '', code, flags=re.DOTALL)
    # Enlève les commentaires ligne // ou #
    code = re.sub(r'//.*?$', '', code, flags=re.MULTILINE)
    code = re.sub(r'#.*?$', '', code, flags=re.MULTILINE)
    # Remplace les chaînes de caractères et nombres par des tokens génériques
    code = re.sub(r'"[^"\\]*(\\.[^"\\]*)*"', '"STR"', code)
    code = re.sub(r"'[^'\\]*(\\.[^'\\]*)*'", "'STR'", code)
    code = re.sub(r'\b\d+\b', 'NUM', code)
    # Normalise les espaces
    code = re.sub(r'\s+', ' ', code).strip()
    return code

def preprocess_text(text: str) -> str:
    """Nettoyage basique pour le texte naturel."""
    text = text.lower()
    text = re.sub(r'[^\w\s]', ' ', text)   # supprime ponctuation
    text = re.sub(r'\s+', ' ', text).strip()
    return text

# ========== Gestion de la base de soumissions ==========
def load_submissions() -> List[Dict]:
    if os.path.exists(DATA_FILE):
        with open(DATA_FILE, 'r', encoding='utf-8') as f:
            return json.load(f)
    return []

def save_submissions(submissions: List[Dict]):
    with open(DATA_FILE, 'w', encoding='utf-8') as f:
        json.dump(submissions, f, indent=2, ensure_ascii=False)

def add_submission(filename: str, content: str, file_type: str, metadata: dict = None):
    """Ajoute une soumission à la base après prétraitement."""
    submissions = load_submissions()
    # Prétraitement selon le type
    if file_type == "code":
        processed = preprocess_code(content)
    else:
        processed = preprocess_text(content)
    
    submission = {
        "id": hashlib.md5(f"{filename}_{len(submissions)}".encode()).hexdigest()[:8],
        "filename": filename,
        "original_content": content[:500] + "..." if len(content) > 500 else content,
        "processed": processed,
        "file_type": file_type,
        "metadata": metadata or {}
    }
    submissions.append(submission)
    save_submissions(submissions)
    return submission["id"]

# ========== Calcul de similarité ==========
def compute_similarities(new_processed: str, existing_submissions: List[Dict]) -> List[Dict]:
    """Compare le nouveau document prétraité avec tous ceux de la base."""
    if not existing_submissions:
        return []
    
    # Récupérer tous les textes prétraités existants
    existing_texts = [sub["processed"] for sub in existing_submissions]
    all_texts = [new_processed] + existing_texts
    
    # Vectorisation TF-IDF (n-grammes pour capturer un peu de structure)
    vectorizer = TfidfVectorizer(ngram_range=(1, 3), min_df=1, stop_words=None)
    tfidf_matrix = vectorizer.fit_transform(all_texts)
    
    # Similarité cosinus entre le premier (new) et les autres
    cos_sim = cosine_similarity(tfidf_matrix[0:1], tfidf_matrix[1:]).flatten()
    
    results = []
    for i, sim in enumerate(cos_sim):
        if sim >= SIMILARITY_THRESHOLD:
            results.append({
                "submission_id": existing_submissions[i]["id"],
                "filename": existing_submissions[i]["filename"],
                "similarity": round(float(sim), 4),
                "type": existing_submissions[i]["file_type"]
            })
    # Trier par similarité décroissante
    results.sort(key=lambda x: x["similarity"], reverse=True)
    return results[:MAX_RESULTS]

# ========== Endpoints API ==========
@app.post("/check")
async def check_plagiarism(
    file: Optional[UploadFile] = File(None),
    text: Optional[str] = Form(None),
    filename: Optional[str] = Form(None),
    file_type: str = Form("text")  # "text" ou "code"
):
    """
    Reçoit un fichier ou du texte, le compare à toutes les soumissions existantes.
    Retourne la liste des similarités.
    """
    if not file and not text:
        raise HTTPException(status_code=400, detail="Either 'file' or 'text' must be provided")
    
    # Lire le contenu
    if file:
        content = (await file.read()).decode("utf-8", errors="ignore")
        original_filename = file.filename
    else:
        content = text
        original_filename = filename or "inline_text.txt"
    
    # Prétraiter selon le type
    if file_type == "code":
        processed = preprocess_code(content)
    else:
        processed = preprocess_text(content)
    
    # Charger les soumissions existantes
    existing = load_submissions()
    
    # Calculer les similarités
    results = compute_similarities(processed, existing)
    
    # Option : ajouter automatiquement cette soumission à la base ?
    # (selon besoin, on peut le faire ou laisser l'utilisateur décider)
    # Ici on ne l'ajoute pas automatiquement pour ne pas polluer.
    
    return JSONResponse({
        "filename": original_filename,
        "file_type": file_type,
        "num_comparisons": len(existing),
        "possible_plagiarism": len(results) > 0,
        "top_matches": results
    })

@app.post("/add")
async def add_to_database(
    file: UploadFile = File(...),
    file_type: str = Form("text"),
    metadata: Optional[str] = Form(None)
):
    """Ajoute une soumission à la base de référence (corpus)."""
    content = (await file.read()).decode("utf-8", errors="ignore")
    meta = json.loads(metadata) if metadata else {}
    sub_id = add_submission(file.filename, content, file_type, meta)
    return JSONResponse({"status": "added", "id": sub_id})

@app.get("/stats")
async def stats():
    submissions = load_submissions()
    return {
        "total_submissions": len(submissions),
        "types": {
            "text": sum(1 for s in submissions if s["file_type"] == "text"),
            "code": sum(1 for s in submissions if s["file_type"] == "code")
        }
    }

# ========== Lancement ==========
if __name__ == "__main__":
    uvicorn.run(app, host="0.0.0.0", port=5000)