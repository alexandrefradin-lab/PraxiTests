# Oracle Debug — Handoff

## Symptôme
Le widget Oracle répond toujours : *"Je ne parviens pas à répondre à l'instant — le service est momentanément indisponible."*

## Ce qui a été fait

### 1. Cause initiale identifiée et corrigée
**Bug** : `PromptBuilder::safeProfileText()` et `safeCvStructured()` étaient appelées partout dans le fichier mais **jamais définies** → Fatal Error → catch → message fallback.

**Fix appliqué** dans `app/Core/AI/PromptBuilder.php` : ajout des deux méthodes (lignes ~389-430).

**Déployé sur OVH** :
```bash
cp ~/www/PraxiTests/app/Core/AI/PromptBuilder.php ~/praxiquest/app/Core/AI/PromptBuilder.php
cd ~/praxiquest && php artisan optimize --except=views
```

### 2. Oracle toujours en erreur après le fix
Le fallback persiste. **Nouvelle erreur inconnue** — les logs n'ont pas été re-vérifiés après le déploiement.

## Prochaine étape immédiate

Vérifier la nouvelle erreur dans les logs OVH :
```bash
tail -20 ~/praxiquest/storage/logs/laravel.log | grep -i "oracle\|ERROR"
```

## Contexte technique

| Élément | Valeur |
|---|---|
| Site actif | `~/praxiquest/` (pas `~/www/PraxiTests/`) |
| Repo git | `~/www/PraxiTests/` |
| ANTHROPIC_API_KEY | Présente dans `~/praxiquest/.env` (sk-ant-api03-emR…) |
| PHP | 8.2, OVH cluster121 |
| Composer | `~/composer.phar` |
| Deploy | `cp` fichier par fichier + `php artisan optimize --except=views` |
| Views cache | NE PAS utiliser `php artisan optimize` seul → erreur praximet views manquantes → toujours ajouter `--except=views` |

## Architecture Oracle

```
OracleChatController → OracleChatService::ask()
  → PromptBuilder::oracleChat()        ← appelait safeProfileText() [FIXÉ]
  → AIManager::forTask('oracle_chat')
  → AnthropicDriver::chat()            ← HTTP POST api.anthropic.com/v1/messages
  → catch(\Throwable) → fallback msg   ← on arrive ici, raison inconnue
```

## Pistes si l'erreur persiste

1. **Vérifier les logs** (commande ci-dessus) — c'est la priorité
2. **GlobalGrimoireService** : `OracleChatService` dépend de `GlobalGrimoireService::completedAttempts()` — vérifier que cette classe existe dans `~/praxiquest/`
3. **Autoloader** : si d'autres classes sont manquantes → `cd ~/praxiquest && COMPOSER_MEMORY_LIMIT=-1 php ~/composer.phar dump-autoload --optimize`
4. **AnthropicDriver** : tester l'API directement avec `curl` depuis OVH pour vérifier que la clé fonctionne et que le réseau OVH autorise les appels sortants vers `api.anthropic.com`

```bash
curl -s -o /dev/null -w "%{http_code}" https://api.anthropic.com/v1/models \
  -H "x-api-key: $(grep ANTHROPIC_API_KEY ~/praxiquest/.env | cut -d= -f2)" \
  -H "anthropic-version: 2023-06-01"
```
→ doit retourner `200`
