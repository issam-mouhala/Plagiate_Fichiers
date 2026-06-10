# ── Dockerfile optimise pour Hugging Face Spaces ──
# Usage: Plagiarism Detection API (FastAPI)

FROM python:3.11-slim

WORKDIR /app

# 1. Install system dependencies first (layer cache)
RUN apt-get update && apt-get install -y --no-install-recommends \
    build-essential \
    git \
    && rm -rf /var/lib/apt/lists/*

# 2. Install PyTorch FIRST with CPU-only (saves 2GB vs GPU version)
# This is the critical fix: use CPU-only torch for HF Spaces
RUN pip install --no-cache-dir \
    torch==2.5.1+cpu \
    --index-url https://download.pytorch.org/whl/cpu \
    --extra-index-url https://pypi.org/simple/

# 3. Install other dependencies (cached separately)
COPY requirements.txt .
RUN pip install --no-cache-dir \
    -r requirements.txt \
    --extra-index-url https://download.pytorch.org/whl/cpu

# 4. Copy application
COPY app.py .

# 5. Create directories needed by the API
RUN mkdir -p uploads corpus saved_images

# 6. Expose port 7860 (HF Spaces default)
EXPOSE 7860

# 7. Health check
HEALTHCHECK --interval=30s --timeout=10s --retries=3 \
    CMD curl -f http://localhost:7860/api/health || exit 1

# 8. Start
CMD ["uvicorn", "app:app", "--host", "0.0.0.0", "--port", "7860"]
