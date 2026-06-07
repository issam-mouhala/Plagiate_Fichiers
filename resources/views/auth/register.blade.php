<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription | PlagioScan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0B2B5E 0%, #1A4A7A 100%);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .card-register {
            border: none;
            border-radius: 2rem;
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 25px 45px -12px rgba(0, 0, 0, 0.25);
            transition: transform 0.3s;
        }
        .card-register:hover { transform: translateY(-5px); }
        .btn-register {
            background: linear-gradient(95deg, #1e6fdf 0%, #0a4c8f 100%);
            border: none;
            padding: 12px;
            border-radius: 40px;
            font-weight: 600;
        }
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(30,111,223,0.4);
        }
        .form-control {
            border-radius: 1rem;
            padding: 0.75rem 1rem;
            border: 1px solid #e2e8f0;
        }
        .form-control:focus {
            border-color: #1e6fdf;
            box-shadow: 0 0 0 3px rgba(30,111,223,0.2);
        }
        .icon-bg {
            background: #eef3ff;
            width: 64px;
            height: 64px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 60px;
            font-size: 1.8rem;
            color: #1e6fdf;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card-register p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="icon-bg mx-auto mb-3"><i class="bi bi-person-plus"></i></div>
                    <h1 class="fw-bold" style="color: #0B2B5E;">Inscription</h1>
                    <p class="text-muted">Créez votre compte PlagioScan</p>
                </div>

                @if(session('success'))
                    <div class="alert alert-success rounded-pill">{{ session('success') }}</div>
                @endif

                <form method="POST" action="{{ route('auth.register') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold"><i class="bi bi-person me-2"></i>Nom complet</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required autofocus>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold"><i class="bi bi-envelope me-2"></i>Email</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold"><i class="bi bi-lock me-2"></i>Mot de passe</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold"><i class="bi bi-lock-fill me-2"></i>Confirmer le mot de passe</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-register w-100 text-white"><i class="bi bi-check-circle me-2"></i>S'inscrire</button>
                </form>

                <div class="text-center mt-4">
                    <p class="mb-0">Déjà inscrit ? <a href="{{ route('login') }}" class="text-primary fw-semibold text-decoration-none">Connectez-vous</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
