<?php
// C:\xampp\htdocs\ClickPharma\scan_ordonnance.php
session_start();

// Protection de la page : accessible uniquement aux patients connectés
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'patient') {
    header('Location: login_patient.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scanner Ordonnance | ClickPharma</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://unpkg.com/html5-qrcode"></script>
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        #reader video { border-radius: 1.5rem !important; object-fit: cover; }
    </style>
</head>
<body class="bg-[#f8fafc] min-h-screen flex flex-col justify-between">

    <nav class="bg-[#00966b] text-white px-6 py-4 shadow-sm flex justify-between items-center">
        <div class="flex items-center gap-3">
            <a href="space_patient.php" class="text-white/80 hover:text-white transition text-sm flex items-center gap-1 font-bold">
                <i class="fa-solid fa-arrow-left"></i> Retour
            </a>
            <span class="text-lg font-extrabold tracking-tight">CLICKPHARMA</span>
        </div>
        <div class="text-xs bg-[#00825c] px-3 py-1.5 rounded-full font-semibold">
            <i class="fa-solid fa-user text-xs mr-1"></i> Espace Patient
        </div>
    </nav>

    <main class="flex-1 max-w-md w-full mx-auto p-6 flex flex-col justify-center items-center my-4">
        
        <div class="w-full text-center space-y-2 mb-6">
            <div class="inline-flex p-3 bg-emerald-50 rounded-2xl text-[#00966b] mb-1 animate-bounce">
                <i class="fa-solid fa-qrcode text-3xl"></i>
            </div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Scannez votre QR Code</h2>
            <p class="text-xs text-slate-500 font-medium px-4">Placez le QR code imprimé sur votre ordonnance au centre du cadre pour être redirigé vers les pharmacies.</p>
        </div>

        <div class="w-full bg-white rounded-[2.5rem] border border-slate-100 shadow-xl p-4 relative overflow-hidden">
            <div id="reader" class="w-full overflow-hidden bg-slate-900 rounded-[1.5rem]"></div>
            
            <div id="scan-status" class="mt-4 text-center text-xs font-bold text-slate-400 py-1 uppercase tracking-wider flex items-center justify-center gap-2">
                <i class="fa-solid fa-circle-notch animate-spin text-[#00966b]"></i> Recherche de la caméra...
            </div>
        </div>

        <div class="mt-6 w-full text-center">
            <span class="text-xs text-slate-400 font-medium">Un problème avec votre caméra ?</span>
            <a href="space_patient.php" class="block text-xs text-[#00966b] font-bold mt-1 hover:underline">
                Sélectionner manuellement mon ordonnance
            </a>
        </div>

    </main>

    <footer class="py-4 text-center text-[11px] font-medium text-slate-400 bg-white border-t border-slate-100">
        &copy; 2026 ClickPharma - Scanner Sécurisé d'Ordonnance
    </footer>

    <script>
    function onScanSuccess(decodedText, decodedResult) {
        // Stopper le scanner immédiatement après détection pour éviter les doubles lectures
        html5QrcodeScanner.clear().then(_ => {
            document.getElementById('scan-status').innerHTML = '<span class="text-emerald-600"><i class="fa-solid fa-circle-check"></i> Code détecté ! Redirection...</span>';
            
            // Sécurité : On vérifie que le texte scanné contient bien notre fichier cible
            if (decodedText.includes('recherche_pharmacie.php')) {
                window.location.href = decodedText;
            } else {
                alert("Le QR Code scanné n'est pas une ordonnance valide ClickPharma.");
                location.reload(); // Relancer si faux code
            }
        }).catch(error => {
            console.warn("Erreur lors de l'arrêt du scanner", error);
        });
    }

    function onScanFailure(error) {
        // Échoué silencieusement (cherche en continu le QR code à chaque frame de la vidéo)
    }

    // Initialisation et configuration du scanner
    const html5QrcodeScanner = new Html5QrcodeScanner(
        "reader", 
        { 
            fps: 15,                  // Nombre d'images analysées par seconde
            qrbox: { width: 230, height: 230 }, // Taille du carré de visée
            aspectRatio: 1.0          // Format carré parfait pour le scanner
        },
        /* verbose= */ false
    );

    // Démarrage du rendu
    html5QrcodeScanner.render(onScanSuccess, onScanFailure);

    // Mettre à jour le statut une fois la caméra prête
    setTimeout(() => {
        const statusDiv = document.getElementById('scan-status');
        if (statusDiv && statusDiv.innerText.includes('Recherche')) {
            statusDiv.innerHTML = '<span class="text-amber-500 animate-pulse"><i class="fa-solid fa-camera"></i> Caméra active : Alignez le QR Code</span>';
        }
    }, 1500);
    </script>

</body>
</html>