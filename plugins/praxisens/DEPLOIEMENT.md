# Mettre en ligne le test « Le Radar des Sens » (praxisens)

## Étape 1 — Sur ton ordinateur (Windows)
Clic droit sur `deploy-ovh.ps1` → **Exécuter avec PowerShell**.
→ Compile le test et l'envoie sur GitHub. Attends le message vert « Pousse terminé ».

## Étape 2 — Sur le serveur OVH (en SSH)
```bash
cd ~/praxiquest
bash deploy-server.sh
```
Puis publie le test (questions + restitution) :
```bash
php artisan praxiquest:plugins:discover --sync
php artisan praxiquest:plugins:activate praxisens
```

## Étape 3 — Vérifier
Ouvre https://praxiquest.decisionpro.fr → page des tests.
« Le Radar des Sens — Hypersensibilité » doit apparaître et être passable.

---

### En cas de souci
- Test absent de la liste → revérifie que `plugins:activate praxisens` s'est lancé sans erreur.
- Page blanche après le test → un asset n'a pas été compilé : relance l'étape 1 (le build), repousse, puis sur OVH `git checkout -- public/build` est déjà géré par `deploy-server.sh`.
- Erreur 500 → sur OVH : `grep "production.ERROR" storage/logs/laravel.log | tail -1`

### Ce que fait l'activation
`plugins:activate praxisens` lance 2 remplissages automatiques :
1. **Questions** : crée le test, ses 3 sections (Sur-stimulation / Sensibilité esthétique / Seuil sensoriel) et les 18 affirmations.
2. **Normes** : prépare l'étalonnage (calculé automatiquement dès ~50 passations).
