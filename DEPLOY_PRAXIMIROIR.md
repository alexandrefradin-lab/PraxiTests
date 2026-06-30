# Déploiement praximiroir — Checklist

## Contexte
Le domaine `praxiquest.decisionpro.fr` sert depuis **`~/praxiquest`** (pas `~/www`).
Tous les déploiements précédents allaient dans le mauvais dossier.

---

## Étapes

### 1. Windows PowerShell
```powershell
.\deploy-ovh.ps1
```

### 2. SSH OVH — 1er run
```bash
cd ~/praxiquest && bash deploy-server.sh
```

### 3. SSH OVH — 2e run (bash buffering)
```bash
bash deploy-server.sh
```

### 4. SSH OVH — Activer praximiroir
```bash
php artisan praxiquest:plugins:activate praximiroir
```

---

✅ Vérification : aller sur `/salle-du-tresor` — la carte "La Forge de l'Identité" doit afficher l'icône empreinte et le bouton "Ouvrir le trésor →".
