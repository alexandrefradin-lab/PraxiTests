<?php
/**
 * PraxiQuest — installeur désactivé.
 *
 * Ce fichier est conservé pour éviter un 404 trompeur, mais l'installation
 * doit se faire via CLI (php artisan migrate --seed) et non via ce script.
 *
 * Recommandation audit (risque critique #1) : supprimer ce fichier du
 * build de production ou le protéger par IP / basic-auth côté serveur.
 */
http_response_code(403);
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'ok'  => false,
    'msg' => 'Installer disabled. Use CLI to manage this application.',
]);
exit;
