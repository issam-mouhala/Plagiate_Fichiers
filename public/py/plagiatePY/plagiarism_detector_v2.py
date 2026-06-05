"""
Plagiarism Detection API v2 — Multi-Engine with ML/AI
=======================================================
Améliorations par rapport à v1 :
  1. Sentence-Transformers (embeddings sémantiques BERT)
  2. AST-based code comparison (résistant au renommage)
  3. Winnowing / Document Fingerprinting (copier-coller exact)
  4. Longest Common Subsequence (similarité structurelle)
  5. Score combiné multi-moteurs pondérés
  6. Pipeline de prétraitement amélioré

Dépendances :
  pip install fastapi uvicorn scikit-learn numpy sentence-transformers torch difflib
"""

import os
import re
import json
import ast
import hashlib
import difflib
import math
from typing import List, Dict, Any, Optional, Tuple
from collections import Counter, defaultdict

from fastapi import FastAPI, UploadFile, File, Form, HTTPException
from fastapi.responses import JSONResponse
from fastapi.middleware.cors import CORSMiddleware
import uvicorn

import numpy as np
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity

# ===========================================================================
# 1. CONFIGURATION
# ===========================================================================
DATA_FILE = "submissions.json"
MAX_RESULTS = 10
WINNOWING_WINDOW = 4           # taille de la fenêtre glissante pour le hachage
WINNOWING_THRESHOLD = 0.20      # seuil winnowing (rapport d'empreintes communes)
SEMANTIC_THRESHOLD = 0.55       # seuil similarité cosinus sur embeddings
TFIDF_THRESHOLD = 0.25          # seuil TF-IDF (abaissé car complété par d'autres moteurs)
AST_THRESHOLD = 0.40            # seuil comparaison AST
LCS_THRESHOLD = 0.30            # seuil LCS normalisé

# Poids des moteurs pour le score combiné final
WEIGHTS = {
    "tfidf": 0.20,
    "semantic": 0.35,
    "winnowing": 0.20,
    "ast": 0.15,
    "lcs": 0.10,
}

app = FastAPI(
    title="Plagiarism Detection API v2",
    description="Détection de plagiat texte/code — Multi-engine ML/AI",
    version="2.0.0"
)
app.add_middleware(CORSMiddleware, allow_origins=["*"], allow_methods=["*"], allow_headers=["*"])

# ===========================================================================
# 2. SENTENCE-TRANSFORMERS — Chargement paresseux du modèle
# ===========================================================================
_semantic_model = None
_semantic_vectorizer = None


def get_semantic_model():
    """
    Charge le modèle sentence-transformers au premier appel (lazy loading).
    Utilise 'all-MiniLM-L6-v2' : rapide, multilingue, 384 dimensions.
    """
    global _semantic_model
    if _semantic_model is None:
        try:
            from sentence_transformers import SentenceTransformer
            _semantic_model = SentenceTransformer("all-MiniLM-L6-v2")
            print("[INFO] Modèle sentence-transformers chargé (all-MiniLM-L6-v2)")
        except ImportError:
            print("[WARN] sentence-transformers non installé. Moteur sémantique désactivé.")
            print("       Installez avec : pip install sentence-transformers")
            return None
        except Exception as e:
            print(f"[ERROR] Impossible de charger le modèle : {e}")
            return None
    return _semantic_model


# ===========================================================================
# 3. PRETRAITEMENT — Code et Texte
# ===========================================================================

def normalize_identifiers(code: str) -> str:
    """
    Remplace les identifiants (noms de variables, fonctions) par des tokens
    génériques pour résister au renommage. Par exemple : 'myVar' → 'ID_0'.
    """
    # Pattern pour capturer les identifiants (mots commençant par une lettre, éventuellement _)
    id_pattern = re.compile(r'\b[a-zA-Z_][a-zA-Z0-9_]*\b')
    # Mots-clés réservés qu'on ne remplace PAS
    reserved = set([
        'if', 'else', 'elif', 'for', 'while', 'def', 'class', 'return', 'import',
        'from', 'as', 'try', 'except', 'finally', 'with', 'yield', 'lambda',
        'and', 'or', 'not', 'in', 'is', 'pass', 'break', 'continue', 'raise',
        'assert', 'del', 'global', 'nonlocal', 'async', 'await',
        # Types
        'int', 'str', 'float', 'bool', 'list', 'dict', 'set', 'tuple', 'None',
        'True', 'False', 'self', 'super', 'print', 'range', 'len', 'type',
        # JS
        'var', 'let', 'const', 'function', 'new', 'this', 'null', 'undefined',
        'switch', 'case', 'default', 'do', 'typeof', 'instanceof', 'void',
        # Java
        'public', 'private', 'protected', 'static', 'final', 'abstract',
        'interface', 'extends', 'implements', 'package', 'throws', 'throw',
        'catch', 'new', 'void', 'boolean', 'char', 'byte', 'short', 'long',
        'double', 'String', 'System',
        # C
        'include', 'define', 'ifdef', 'endif', 'struct', 'enum', 'union',
        'typedef', 'sizeof', 'unsigned', 'signed', 'extern', 'register',
        'volatile', 'inline', 'return', 'goto', 'sizeof',
        # Built-ins courants
        'printf', 'scanf', 'cout', 'cin', 'endl', 'main', 'args', 'argv',
        'init', 'append', 'extend', 'insert', 'remove', 'pop', 'sort',
        'map', 'filter', 'reduce', 'input', 'open', 'close', 'read', 'write',
    ])
    counter = [0]

    def replace_id(match):
        token = match.group(0)
        if token in reserved:
            return token
        return f"ID_{counter[0]}"

    # Premier passe : comptage sans remplacement pour construire la map
    ids_found = id_pattern.findall(code)
    unique_ids = set()
    id_map = {}
    counter = [0]
    for token in ids_found:
        if token not in reserved and token not in id_map:
            id_map[token] = f"ID_{counter[0]}"
            counter[0] += 1

    def _replace(match):
        token = match.group(0)
        return id_map.get(token, token)

    return id_pattern.sub(_replace, code)


def preprocess_code(code: str) -> str:
    """
    Nettoyage avancé du code source :
    - Suppression des commentaires
    - Remplacement des littéraux
    - Normalisation des identifiants (anti-renommage)
    - Normalisation des espaces
    """
    # Suppression commentaires multilignes
    code = re.sub(r'/\*.*?\*/', '', code, flags=re.DOTALL)
    # Suppression commentaires ligne
    code = re.sub(r'//.*?$', '', code, flags=re.MULTILINE)
    code = re.sub(r'#.*?$', '', code, flags=re.MULTILINE)
    # Remplacement littéraux
    code = re.sub(r'"""[\s\S]*?"""', ' """STR""" ', code)
    code = re.sub(r"'''[\s\S]*?'''", " '''STR''' ", code)
    code = re.sub(r'"[^"\\]*(\\.[^"\\]*)*"', ' "STR" ', code)
    code = re.sub(r"'[^'\\]*(\\.[^'\\]*)*'", " 'STR' ", code)
    code = re.sub(r'\b\d+\.?\d*\b', 'NUM', code)
    # Normalisation identifiants (anti-renommage)
    code = normalize_identifiers(code)
    # Normalisation espaces
    code = re.sub(r'\s+', ' ', code).strip()
    return code


def preprocess_text(text: str) -> str:
    """
    Nettoyage du texte naturel avec normalisation avancée :
    - Minuscules
    - Suppression de la ponctuation
    - Normalisation des espaces
    """
    text = text.lower()
    # Suppression des caractères spéciaux mais on garde les lettres, chiffres et espaces
    text = re.sub(r'[^\w\s]', ' ', text)
    # Suppression des mots trop courts (< 2 chars) sauf tokens importants
    text = re.sub(r'\s+', ' ', text).strip()
    return text


# ===========================================================================
# 4. AST — Code Structural Comparison
# ===========================================================================

def code_to_ast_features(code: str) -> Optional[str]:
    """
    Extrait une signature structurelle du code via son AST.
    Résiste au renommage de variables, commentaires, espacement.
    Retourne une chaîne représentant la structure de l'AST.
    """
    try:
        tree = ast.parse(code)
    except SyntaxError:
        # Si ce n'est pas du Python valide, fallback sur le code prétraité
        return None
    except Exception:
        return None

    features = []

    class ASTVisitor(ast.NodeVisitor):
        def generic_visit(self, node):
            # Pour chaque noeud, on note son type et le nombre d'enfants
            children_count = len(list(ast.iter_child_nodes(node)))
            features.append(f"{node.__class__.__name__}({children_count})")
            # Pour les appels de fonction : on note le nom (peut être anonymisé)
            if isinstance(node, ast.Call):
                if isinstance(node.func, ast.Name):
                    features.append(f"CALL({node.func.id})")
                elif isinstance(node.func, ast.Attribute):
                    features.append(f"CALL_ATTR({node.func.attr})")
            # Pour les opérations binaires
            if isinstance(node, ast.BinOp):
                features.append(f"BINOP({node.op.__class__.__name__})")
            # Pour les boucles
            if isinstance(node, (ast.For, ast.While)):
                features.append(f"LOOP({node.__class__.__name__})")
            # Pour les conditions
            if isinstance(node, ast.If):
                features.append("COND(if)")
            elif isinstance(node, ast.IfExp):
                features.append("COND(ternary)")
            # Types de retour
            if isinstance(node, ast.FunctionDef):
                features.append(f"FUNC({node.name})")
            if isinstance(node, ast.ClassDef):
                features.append(f"CLASS({node.name})")
            # Traverser les enfants via la méthode parente (pas self !)
            ast.NodeVisitor.generic_visit(self, node)

    visitor = ASTVisitor()
    visitor.visit(tree)

    # Sérialiser les features en une chaîne pour comparaison
    return " ".join(features)


def code_tokens_sequence(code: str) -> str:
    """
    Extrait la séquence de tokens d'un code (mots-clés + opérateurs + structure).
    Utilisé pour le calcul LCS (Longest Common Subsequence).
    """
    try:
        tree = ast.parse(code)
    except SyntaxError:
        # Fallback : tokenizer basique
        tokens = re.findall(r'\b\w+\b|[+\-*/=<>!&|^~%]+|[{}()\[\];,]', code)
        return " ".join(tokens)
    except Exception:
        return code

    tokens = []

    class TokenExtractor(ast.NodeVisitor):
        def generic_visit(self, node):
            # Extraire les mots-clés et opérateurs structurels
            if isinstance(node, ast.FunctionDef):
                tokens.append("DEF")
                tokens.append(node.name)
            elif isinstance(node, ast.ClassDef):
                tokens.append("CLASS")
                tokens.append(node.name)
            elif isinstance(node, ast.For):
                tokens.append("FOR")
            elif isinstance(node, ast.While):
                tokens.append("WHILE")
            elif isinstance(node, ast.If):
                tokens.append("IF")
            elif isinstance(node, ast.Return):
                tokens.append("RETURN")
            elif isinstance(node, ast.BinOp):
                tokens.append(f"OP_{node.op.__class__.__name__}")
            elif isinstance(node, ast.Compare):
                tokens.append("COMPARE")
            elif isinstance(node, ast.Call):
                tokens.append("CALL")
            elif isinstance(node, ast.Assign):
                tokens.append("ASSIGN")
            elif isinstance(node, ast.AugAssign):
                tokens.append("AUG_ASSIGN")
            # Traverser les enfants via la méthode parente (pas self !)
            ast.NodeVisitor.generic_visit(self, node)

    ext = TokenExtractor()
    ext.visit(tree)
    return " ".join(tokens)


# ===========================================================================
# 5. WINNOWING — Document Fingerprinting (anti copier-coller exact)
# ===========================================================================

def ngram_hashes(text: str, n: int = WINNOWING_WINDOW) -> List[int]:
    """
    Génère les empreintes (hashes) des n-grammes de caractères.
    Le Winnowing sélectionne un sous-ensemble de ces empreintes comme signature.
    """
    if len(text) < n:
        if text:
            return [hash(text) % (2 ** 32)]
        return []

    grams = [text[i:i + n] for i in range(len(text) - n + 1)]
    hashes = [hashlib.md5(g.encode('utf-8')).hexdigest() for g in grams]
    return hashes


def winnow_select(hashes: List[str], window: int = WINNOWING_WINDOW) -> set:
    """
    Algorithme de Winnowing : sélectionne les empreintes minimales dans chaque
    fenêtre glissante. Le résultat est un ensemble de hachages formant la
    signature du document.
    """
    if not hashes or len(hashes) < window:
        return set(hashes)

    selected = set()
    # Trouver la position du hash minimum dans chaque fenêtre
    min_idx = 0
    for i in range(len(hashes) - window + 1):
        window_hashes = hashes[i:i + window]
        # Index du minimum dans cette fenêtre
        local_min_pos = i + window_hashes.index(min(window_hashes))
        selected.add(hashes[local_min_pos])

    return selected


def winnowing_similarity(text1: str, text2: str) -> float:
    """
    Calcule la similarité entre deux textes via Winnowing.
    Retourne le rapport Jaccard entre les ensembles d'empreintes.
    """
    h1 = ngram_hashes(text1)
    h2 = ngram_hashes(text2)
    s1 = winnow_select(h1)
    s2 = winnow_select(h2)

    if not s1 and not s2:
        return 1.0  # Deux textes vides sont identiques
    if not s1 or not s2:
        return 0.0

    intersection = len(s1 & s2)
    union = len(s1 | s2)
    return intersection / union if union > 0 else 0.0


# ===========================================================================
# 6. LCS — Longest Common Subsequence (similarité structurelle)
# ===========================================================================

def lcs_ratio(text1: str, text2: str) -> float:
    """
    Calcule le ratio de la plus longue sous-séquence commune (LCS)
    normalisé par la longueur moyenne des deux textes.
    """
    if not text1 and not text2:
        return 1.0
    if not text1 or not text2:
        return 0.0

    tokens1 = text1.split()
    tokens2 = text2.split()

    # Limiter pour la performance sur les longs textes
    if len(tokens1) > 2000:
        tokens1 = tokens1[:2000]
    if len(tokens2) > 2000:
        tokens2 = tokens2[:2000]

    m, n = len(tokens1), len(tokens2)

    # Si les textes sont trop longs, utiliser SequenceMatcher (plus rapide)
    if m * n > 4_000_000:
        matcher = difflib.SequenceMatcher(None, tokens1, tokens2)
        return matcher.ratio()

    # DP classique pour LCS
    dp = [[0] * (n + 1) for _ in range(m + 1)]
    for i in range(1, m + 1):
        for j in range(1, n + 1):
            if tokens1[i - 1] == tokens2[j - 1]:
                dp[i][j] = dp[i - 1][j - 1] + 1
            else:
                dp[i][j] = max(dp[i - 1][j], dp[i][j - 1])

    lcs_len = dp[m][n]
    return (2.0 * lcs_len) / (m + n) if (m + n) > 0 else 0.0


# ===========================================================================
# 7. SIMILARITÉ SÉMANTIQUE — Sentence-Transformers
# ===========================================================================

def compute_semantic_similarity(texts: List[str], new_text: str) -> List[float]:
    """
    Calcule la similarité cosinus entre le nouveau texte et tous les textes
    existants en utilisant les embeddings du modèle sentence-transformers.
    """
    model = get_semantic_model()
    if model is None:
        return [0.0] * len(texts)

    all_texts = [new_text] + texts

    # Encoder tous les textes en embeddings
    embeddings = model.encode(all_texts, convert_to_numpy=True, normalize_embeddings=True)

    # Similarité cosinus (embeddings déjà normalisés → simple produit scalaire)
    similarities = np.dot(embeddings[0], embeddings[1:].T)
    return similarities.tolist()


# ===========================================================================
# 8. COMBINAISON MULTI-MOTEURS
# ===========================================================================

def compute_combined_score(scores: Dict[str, float]) -> Tuple[float, Dict[str, float]]:
    """
    Combine les scores de tous les moteurs en un score final pondéré.
    Les moteurs non disponibles (score = -1) sont ignorés et les poids
    sont redistribués proportionnellement.
    """
    active_weights = {}
    total_weight = 0.0

    for engine, weight in WEIGHTS.items():
        score = scores.get(engine, -1.0)
        if score >= 0:  # Moteur disponible
            active_weights[engine] = weight
            total_weight += weight

    if total_weight == 0:
        return 0.0, scores

    # Normaliser les poids
    final_score = 0.0
    normalized_scores = {}
    for engine, weight in active_weights.items():
        normalized_weight = weight / total_weight
        contribution = scores[engine] * normalized_weight
        final_score += contribution
        normalized_scores[engine] = {
            "raw_score": round(scores[engine], 4),
            "weight": round(normalized_weight, 3),
            "contribution": round(contribution, 4)
        }

    return round(min(final_score, 1.0), 4), normalized_scores


def classify_plagiarism(combined_score: float) -> str:
    """
    Classification du niveau de plagiat selon le score combiné.
    """
    if combined_score >= 0.85:
        return "critical"      # Plagiat quasi certain
    elif combined_score >= 0.65:
        return "high"          # Forte suspicion
    elif combined_score >= 0.45:
        return "medium"        # Suspicion modérée
    elif combined_score >= 0.25:
        return "low"           # Faible similarité
    else:
        return "none"          # Pas de plagiat détecté


# ===========================================================================
# 9. SIMILARITÉ COMPLÈTE — Pipeline multi-moteurs
# ===========================================================================

def compute_full_similarity(
    new_processed: str,
    new_ast_features: Optional[str],
    new_token_seq: Optional[str],
    new_raw: str,
    existing_submissions: List[Dict],
    use_semantic: bool = True,
    use_winnowing: bool = True,
    use_ast: bool = True,
    use_lcs: bool = True,
) -> List[Dict]:
    """
    Pipeline complet de détection : compare le nouveau document avec tous ceux
    de la base en utilisant les moteurs activés.
    """
    if not existing_submissions:
        return []

    results = []

    # ---- Moteur 1 : TF-IDF + Cosine ----
    existing_texts = [sub["processed"] for sub in existing_submissions]
    all_texts = [new_processed] + existing_texts

    vectorizer = TfidfVectorizer(ngram_range=(1, 3), min_df=1, stop_words=None)
    try:
        tfidf_matrix = vectorizer.fit_transform(all_texts)
        tfidf_scores = cosine_similarity(tfidf_matrix[0:1], tfidf_matrix[1:]).flatten()
    except ValueError:
        tfidf_scores = np.zeros(len(existing_submissions))

    # ---- Moteur 2 : Semantic (Sentence-Transformers) ----
    semantic_scores = None
    if use_semantic:
        semantic_scores = compute_semantic_similarity(
            [sub["processed"] for sub in existing_submissions],
            new_processed
        )

    # ---- Moteur 3 : Winnowing ----
    winnow_scores = []
    if use_winnowing:
        for sub in existing_submissions:
            w = winnowing_similarity(new_processed, sub["processed"])
            winnow_scores.append(w)

    # ---- Moteur 4 : AST comparison ----
    ast_scores = []
    if use_ast and new_ast_features:
        for sub in existing_submissions:
            sub_ast = sub.get("ast_features", "")
            if sub_ast:
                # Comparaison TF-IDF sur les features AST
                try:
                    v = TfidfVectorizer(ngram_range=(1, 2), min_df=1)
                    mat = v.fit_transform([new_ast_features, sub_ast])
                    score = cosine_similarity(mat[0:1], mat[1:])[0][0]
                except ValueError:
                    score = 0.0
            else:
                score = -1.0  # AST non disponible
            ast_scores.append(score)
    elif not new_ast_features:
        ast_scores = [-1.0] * len(existing_submissions)
    else:
        ast_scores = [-1.0] * len(existing_submissions)

    # ---- Moteur 5 : LCS ----
    lcs_scores = []
    if use_lcs and new_token_seq:
        for sub in existing_submissions:
            sub_seq = sub.get("token_sequence", "")
            if sub_seq:
                score = lcs_ratio(new_token_seq, sub_seq)
            else:
                score = lcs_ratio(new_processed, sub["processed"])
            lcs_scores.append(score)
    elif not new_token_seq:
        # Pour le texte, utiliser LCS sur le texte prétraité directement
        for sub in existing_submissions:
            score = lcs_ratio(new_processed, sub["processed"])
            lcs_scores.append(score)
    else:
        lcs_scores = [0.0] * len(existing_submissions)

    # ---- Combinaison des scores ----
    for i, sub in enumerate(existing_submissions):
        scores_dict = {
            "tfidf": float(tfidf_scores[i]),
            "semantic": float(semantic_scores[i]) if semantic_scores else -1.0,
            "winnowing": float(winnow_scores[i]) if use_winnowing else -1.0,
            "ast": float(ast_scores[i]),
            "lcs": float(lcs_scores[i]),
        }

        combined, breakdown = compute_combined_score(scores_dict)
        level = classify_plagiarism(combined)

        results.append({
            "submission_id": sub["id"],
            "filename": sub["filename"],
            "file_type": sub["file_type"],
            "combined_score": combined,
            "level": level,
            "engines": breakdown,
        })

    # Trier par score combiné décroissant
    results.sort(key=lambda x: x["combined_score"], reverse=True)

    # Filtrer : ne garder que les résultats avec un score > seuil minimal
    min_score = min(TFIDF_THRESHOLD, WINNOWING_THRESHOLD, LCS_THRESHOLD) * 0.5
    results = [r for r in results if r["combined_score"] >= min_score]

    return results[:MAX_RESULTS]


# ===========================================================================
# 10. GESTION DE LA BASE DE SOUMISSIONS
# ===========================================================================

def load_submissions() -> List[Dict]:
    if os.path.exists(DATA_FILE):
        with open(DATA_FILE, 'r', encoding='utf-8') as f:
            return json.load(f)
    return []


def save_submissions(submissions: List[Dict]):
    with open(DATA_FILE, 'w', encoding='utf-8') as f:
        json.dump(submissions, f, indent=2, ensure_ascii=False)


def add_submission(filename: str, content: str, file_type: str,
                   metadata: dict = None) -> str:
    """
    Ajoute une soumission à la base avec toutes les signatures précalculées.
    """
    submissions = load_submissions()

    # Prétraitement principal
    if file_type == "code":
        processed = preprocess_code(content)
        # Extraire les features AST
        ast_features = code_to_ast_features(content)
        # Extraire la séquence de tokens
        token_seq = code_tokens_sequence(content)
    else:
        processed = preprocess_text(content)
        ast_features = None
        token_seq = None

    # Empreintes Winnowing (précalculées pour accélérer les recherches futures)
    winnow_hashes = ngram_hashes(processed)
    winnow_fingerprint = list(winnow_select(winnow_hashes))

    submission = {
        "id": hashlib.md5(f"{filename}_{len(submissions)}_{content[:100]}".encode()).hexdigest()[:12],
        "filename": filename,
        "original_content": content[:500] + "..." if len(content) > 500 else content,
        "processed": processed,
        "file_type": file_type,
        "ast_features": ast_features,
        "token_sequence": token_seq,
        "winnow_fingerprint": winnow_fingerprint,
        "content_length": len(content),
        "metadata": metadata or {},
    }
    submissions.append(submission)
    save_submissions(submissions)
    return submission["id"]


# ===========================================================================
# 11. ENDPOINTS API
# ===========================================================================

@app.post("/check")
async def check_plagiarism(
    file: Optional[UploadFile] = File(None),
    text: Optional[str] = Form(None),
    filename: Optional[str] = Form(None),
    file_type: str = Form("text"),
    use_semantic: bool = Form(True),
    use_winnowing: bool = Form(True),
    use_ast: bool = Form(True),
    use_lcs: bool = Form(True),
):
    """
    Endpoint principal : vérifie un texte/code contre toute la base.

    Paramètres :
    - file          : fichier upload (optionnel, text requis sinon)
    - text          : texte/code en ligne (optionnel, file requis sinon)
    - file_type     : "text" ou "code"
    - use_semantic  : activer le moteur sémantique (BERT)
    - use_winnowing : activer le fingerprinting
    - use_ast       : activer la comparaison AST (code uniquement)
    - use_lcs       : activer LCS

    Retourne :
    - Liste des matchs triés par score combiné décroissant
    - Détail par moteur pour chaque match
    """
    if not file and not text:
        raise HTTPException(
            status_code=400,
            detail="Fournir soit 'file' soit 'text'"
        )

    # Lecture du contenu
    if file:
        content = (await file.read()).decode("utf-8", errors="ignore")
        original_filename = file.filename
    else:
        content = text
        original_filename = filename or "inline_text.txt"

    # Prétraitement
    if file_type == "code":
        processed = preprocess_code(content)
        ast_features = code_to_ast_features(content)
        token_seq = code_tokens_sequence(content)
    else:
        processed = preprocess_text(content)
        ast_features = None
        token_seq = None

    # Chargement de la base
    existing = load_submissions()

    if not existing:
        return JSONResponse({
            "filename": original_filename,
            "file_type": file_type,
            "num_comparisons": 0,
            "possible_plagiarism": False,
            "top_matches": [],
            "message": "Base de référence vide. Ajoutez des documents via /add."
        })

    # Pipeline complet de similarité
    results = compute_full_similarity(
        new_processed=processed,
        new_ast_features=ast_features,
        new_token_seq=token_seq,
        new_raw=content,
        existing_submissions=existing,
        use_semantic=use_semantic and file_type == "text",
        use_winnowing=use_winnowing,
        use_ast=use_ast and file_type == "code",
        use_lcs=use_lcs,
    )

    # Statistiques rapides
    levels = Counter(r["level"] for r in results)
    max_score = results[0]["combined_score"] if results else 0.0

    return JSONResponse({
        "filename": original_filename,
        "file_type": file_type,
        "content_length": len(content),
        "num_comparisons": len(existing),
        "possible_plagiarism": max_score >= TFIDF_THRESHOLD,
        "max_score": max_score,
        "summary": {
            "critical": levels.get("critical", 0),
            "high": levels.get("high", 0),
            "medium": levels.get("medium", 0),
            "low": levels.get("low", 0),
        },
        "engines_used": {
            "tfidf": True,
            "semantic": use_semantic,
            "winnowing": use_winnowing,
            "ast": use_ast and file_type == "code",
            "lcs": use_lcs,
        },
        "top_matches": results,
    })


@app.post("/check/text-only")
async def check_text_only(
    text: str = Form(...),
    filename: Optional[str] = Form(None),
):
    """
    Endpoint simplifié pour vérifier du texte uniquement.
    Active automatiquement tous les moteurs pertinents.
    """
    return await check_plagiarism(
        text=text,
        filename=filename,
        file_type="text",
        use_semantic=True,
        use_winnowing=True,
        use_ast=False,
        use_lcs=True,
    )


@app.post("/check/code-only")
async def check_code_only(
    code: str = Form(...),
    filename: Optional[str] = Form(None),
):
    """
    Endpoint simplifié pour vérifier du code uniquement.
    Active TF-IDF + AST + Winnowing + LCS.
    """
    return await check_plagiarism(
        text=code,
        filename=filename,
        file_type="code",
        use_semantic=False,
        use_winnowing=True,
        use_ast=True,
        use_lcs=True,
    )


@app.post("/add")
async def add_to_database(
    file: UploadFile = File(...),
    file_type: str = Form("text"),
    metadata: Optional[str] = Form(None),
):
    """
    Ajoute une soumission à la base de référence (corpus).
    """
    content = (await file.read()).decode("utf-8", errors="ignore")
    meta = json.loads(metadata) if metadata else {}
    sub_id = add_submission(file.filename, content, file_type, meta)
    return JSONResponse({"status": "added", "id": sub_id, "filename": file.filename})


@app.post("/add/text")
async def add_text_to_database(
    text: str = Form(...),
    filename: str = Form("inline_text.txt"),
    metadata: Optional[str] = Form(None),
):
    """
    Ajoute du texte en ligne à la base de référence.
    """
    meta = json.loads(metadata) if metadata else {}
    sub_id = add_submission(filename, text, "text", meta)
    return JSONResponse({"status": "added", "id": sub_id, "filename": filename})


@app.post("/add/code")
async def add_code_to_database(
    code: str = Form(...),
    filename: str = Form("inline_code.py"),
    metadata: Optional[str] = Form(None),
):
    """
    Ajoute du code en ligne à la base de référence.
    """
    meta = json.loads(metadata) if metadata else {}
    sub_id = add_submission(filename, code, "code", meta)
    return JSONResponse({"status": "added", "id": sub_id, "filename": filename})


@app.get("/stats")
async def stats():
    """
    Statistiques de la base de référence.
    """
    submissions = load_submissions()
    code_count = sum(1 for s in submissions if s["file_type"] == "code")
    text_count = sum(1 for s in submissions if s["file_type"] == "text")
    total_size = sum(s.get("content_length", 0) for s in submissions)

    return {
        "total_submissions": len(submissions),
        "types": {"text": text_count, "code": code_count},
        "total_characters": total_size,
        "avg_length": total_size // len(submissions) if submissions else 0,
    }


@app.get("/list")
async def list_submissions(
    limit: int = 50,
    file_type: Optional[str] = None,
):
    """
    Liste les soumissions de la base (sans le contenu complet).
    """
    submissions = load_submissions()
    if file_type:
        submissions = [s for s in submissions if s["file_type"] == file_type]

    return [
        {
            "id": s["id"],
            "filename": s["filename"],
            "file_type": s["file_type"],
            "content_length": s.get("content_length", len(s["original_content"])),
            "metadata": s.get("metadata", {}),
        }
        for s in submissions[:limit]
    ]


@app.delete("/delete/{submission_id}")
async def delete_submission(submission_id: str):
    """
    Supprime une soumission de la base par son ID.
    """
    submissions = load_submissions()
    original_len = len(submissions)
    submissions = [s for s in submissions if s["id"] != submission_id]

    if len(submissions) == original_len:
        raise HTTPException(status_code=404, detail=f"Soumission {submission_id} non trouvée")

    save_submissions(submissions)
    return {"status": "deleted", "id": submission_id}


@app.get("/health")
async def health():
    """
    Vérifie l'état du service et des modèles chargés.
    """
    model_ok = get_semantic_model() is not None
    submissions = load_submissions()

    return {
        "status": "healthy",
        "semantic_model_loaded": model_ok,
        "semantic_model": "all-MiniLM-L6-v2",
        "corpus_size": len(submissions),
        "engines": {
            "tfidf": True,
            "semantic": model_ok,
            "winnowing": True,
            "ast": True,
            "lcs": True,
        }
    }


# ===========================================================================
# 12. LANCEMENT
# ===========================================================================

if __name__ == "__main__":
    print("=" * 60)
    print("  Plagiarism Detection API v2 — Multi-Engine ML/AI")
    print("=" * 60)
    print()
    print("Moteurs disponibles :")
    print("  [1] TF-IDF + Cosine Similarity       (toujours actif)")
    print("  [2] Sentence-Transformers (BERT)      (si installé)")
    print("  [3] Winnowing (Document Fingerprinting)")
    print("  [4] AST-based Code Comparison          (code uniquement)")
    print("  [5] LCS (Longest Common Subsequence)")
    print()
    print("Endpoints :")
    print("  POST /check          — Vérification complète")
    print("  POST /check/text-only — Vérification texte")
    print("  POST /check/code-only — Vérification code")
    print("  POST /add            — Ajouter un fichier")
    print("  POST /add/text       — Ajouter du texte")
    print("  POST /add/code       — Ajouter du code")
    print("  GET  /stats          — Statistiques")
    print("  GET  /list           — Lister les soumissions")
    print("  GET  /health         — État du service")
    print()

    # Précharger le modèle sémantique (optionnel, lazy loading sinon)
    get_semantic_model()

    uvicorn.run(app, host="0.0.0.0", port=5000)
