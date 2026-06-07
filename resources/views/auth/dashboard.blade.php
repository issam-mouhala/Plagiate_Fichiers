
<div class="container">
    <div class="card">
        <div class="card-header">Tableau de bord</div>
        <div class="card-body">
            <p>Bienvenue, {{ Auth::user()->name }} !</p>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-danger">Déconnexion</button>
            </form>
        </div>
    </div>
</div>
