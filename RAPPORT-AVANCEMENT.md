# Rapport d'avancement — PraxiQuest

> **Date :** 2 juin 2026 · **Version :** 1.0.0-alpha · **Dernier commit :** `bb7a4a1`

## En une phrase

Le cœur applicatif et les 5 tests sont **fonctionnellement livrés** ; le projet est désormais en phase de **durcissement** (audit de sécurité/qualité réalisé, correctifs à appliquer) avant une mise en production sereine.

## État global

| Domaine | Statut | Détail |
|---|---|---|
| Core Laravel | ✅ Livré | Auth, 13 migrations, 15 modèles, controllers candidat/admin/auth, rôles Spatie |
| Système de plugins | ✅ Livré | Contrat + manager + registry + hooks + auto-discovery + commandes Artisan |
| Moteur de tests | ✅ Livré | TestEngine, scoring, interprétation par normes |
| IA | ✅ Livré | 4 drivers (Anthropic/OpenAI/Mistral/Ollama), synthèse profil + 15 métiers + extraction CV |
| Gamification | ✅ Livré | XP, niveaux, badges, narration |
| Neuromarketing + Emailing | ✅ Livré | Optimiseur 8 biais, campagnes, séquences |
| Frontend Vue/Inertia | ✅ Livré | 24 pages |
| Installeur web | ✅ Livré | Single-form, verrou post-install |
| Déploiement OVH | ✅ Opérationnel | GitHub Actions → zip → FileZilla, sans SSH |
| **Audit qualité** | ⚠️ Fait, **non corrigé** | 5 passes : sécurité, perf, qualité PHP, frontend, tests |
| Multi-tenant / Billing / RGPD | ⏳ Non démarré | Sprint 3+ |

## Les 5 tests (plugins convertis depuis WordPress)

| Plugin | Test | Questions | Scoring |
|---|---|---|---|
| `praximet` | RIASEC | 84 | code Holland 3 lettres |
| `praxivaleurs` | Valeurs de Schwartz | 40 | top 5 valeurs |
| `praxicare` | Karasek + MBI | 48 | 5 profils |
| `praxiemo` | Intelligence émotionnelle | 80+6 | EQ-i 16 dimensions |
| `praximum` | Big Five OCEAN | 128 | T normé · 30 facettes · 16 archétypes |

## Volumétrie

5 728 lignes PHP (app + plugins) · 24 pages Vue · 54 tests automatisés · 13 migrations · 5 plugins.

## Points d'attention prioritaires (issus de l'audit du 2 juin)

L'audit a relevé des problèmes **critiques non encore corrigés**. À traiter avant toute exposition publique :

**Sécurité (2 critiques) :**

- `install.php` contournable via `?force=1` → wipe + réinstall sans authentification (SEC-01).
- Bloc AJAX de l'installeur exécuté avant le guard → migrate/seed déclenchables (SEC-02).
- Plus : absence de rate-limiting sur login/register, XSS stocké dans les emails de campagne, rôle `professional` trop permissif.

**Qualité / psychométrie (5 critiques) :**

- Race conditions sur l'attribution d'XP et la création de tentatives (double incrément / double tentative).
- Big Five : conversion T→percentile linéaire incorrecte et incohérence de clés de normes (`sd` vs `std_dev`) → scores faussés.
- EQ-i : logique de détection de biais de désirabilité potentiellement inversée.

**Tests :** 4 zones critiques sans couverture (notamment les correctifs ci-dessus).

## Bilan Git — action recommandée

Le dépôt n'a **pas été commité depuis le 11 mai**. Le travail récent (audit, documentation, index de performance, configuration) représente **219 fichiers modifiés + 21 nouveaux fichiers** en attente. Premier réflexe de la prochaine session : committer et pousser cet état pour ne rien perdre et relancer le build GitHub Actions.

## Prochaines étapes suggérées

1. **Committer/pousser** l'état actuel (priorité immédiate).
2. **Corriger les 2 critiques de sécurité de l'installeur** (rapide, fort impact).
3. **Corriger les race conditions** (XP, tentatives) via UPDATE atomiques / transactions.
4. **Corriger les bugs psychométriques Big Five & EQ-i** + ajouter les tests manquants.
5. Puis attaquer le Sprint 3 (multi-tenant, billing Stripe, RGPD).
