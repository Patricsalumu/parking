<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Parking Manager</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #0d6efd, #0a58ca);
            color: white;
        }
        .hero {
            padding: 100px 0;
            text-align: center;
        }
        .feature-box {
            background: white;
            color: black;
            border-radius: 10px;
            padding: 30px;
            transition: 0.3s;
        }
        .feature-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        .navbar {
            background: transparent !important;
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark px-4">
    <a class="navbar-brand fw-bold" href="#">🚗 ParkingApp</a>
    <div class="ms-auto">
        <a href="#" class="btn btn-outline-light me-2">Connexion</a>
        <a href="#" class="btn btn-light text-primary">S'inscrire</a>
    </div>
</nav>

<!-- HERO -->
<section class="hero">
    <div class="container">
        <h1 class="display-4 fw-bold">Gestion intelligente de parking</h1>
        <p class="lead mt-3">
            Gérez vos véhicules, entrées, sorties et paiements facilement avec une solution moderne.
        </p>
        <a href="#" class="btn btn-light btn-lg mt-4">Commencer</a>
    </div>
</section>

<!-- FEATURES -->
<section class="container mb-5">
    <div class="row g-4">

        <div class="col-md-4">
            <div class="feature-box text-center">
                <h4>🚘 Gestion véhicules</h4>
                <p>Enregistrement rapide des voitures, motos et camions.</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="feature-box text-center">
                <h4>⏱️ Entrée & Sortie</h4>
                <p>Suivi en temps réel des mouvements du parking.</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="feature-box text-center">
                <h4>💰 Paiements</h4>
                <p>Calcul automatique des frais de stationnement.</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="feature-box text-center">
                <h4>📊 Statistiques</h4>
                <p>Visualisez vos revenus et performances.</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="feature-box text-center">
                <h4>🔐 Sécurité</h4>
                <p>Gestion des accès et utilisateurs sécurisée.</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="feature-box text-center">
                <h4>📱 Simple & Rapide</h4>
                <p>Interface intuitive adaptée à tous les écrans.</p>
            </div>
        </div>

    </div>
</section>

<!-- FOOTER -->
<footer class="text-center py-4">
    <p>© {{ date('Y') }} ParkingApp - Tous droits réservés</p>
</footer>

</body>
</html>