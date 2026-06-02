# PraxiQuest

> **Plateforme SaaS d'évaluation et d'orientation professionnelle**
> augmentée par l'IA, le neuromarketing et la gamification.

[![Version](https://img.shields.io/badge/version-1.0.0--alpha-indigo)](.)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20)](https://laravel.com)
[![License](https://img.shields.io/badge/license-Proprietary-gray)](LICENSE)

---

## Présentation

PraxiQuest permet à des **organismes de formation, cabinets RH, écoles et consultants en orientation** de déployer une plateforme d'évaluation clé en main — et à leurs bénéficiaires de découvrir leur profil professionnel en quelques dizaines de minutes.

Le candidat passe un ou plusieurs tests psychométriques (RIASEC, Big Five, intelligence émotionnelle, valeurs professionnelles, souffrance au travail), fournit son CV, et reçoit :

- une **synthèse IA personnalisée** de son profil (Claude / GPT-4 / Mistral / Ollama)
- **15 métiers** réalistes et adaptés à son contexte
- un **rapport PDF** téléchargeable
- une progression gamifiée (XP, badges, narration)

---

## Fonctionnalités

### Parcours candidat
- Onboarding structuré : statut professionnel, ancienneté, upload CV
- Extraction intelligente du CV par IA → structuration automatique des expériences et compétences
- Passage de tests en ligne avec progression en temps réel
- Restitution richement visualisée (radars, graphiques, quadrant Karasek)
- Synthèse IA narrative + suggestions métiers
- Export PDF du rapport complet
- Historique de tous les tests passés

### Administration
- Tableau de bord avec métriques en temps réel (cache 60 s)
- Éditeur de tests complet (sections, questions, types, scoring)
- Gestion des campagnes d'invitation par email
- Suivi des leads et des comptes professionnels
- Gestion des plugins (activation, désactivation, logs)
- Configuration globale (clés IA, SMTP, marque)

### Intelligence artificielle
- Abstraction multi-drivers : Anthropic Claude, OpenAI GPT, Mistral, Ollama (local)
- Extraction et structuration automatique du CV
- Synthèse de profil en français, bienveillante et actionnelle
- Proposition de 15 métiers avec secteur, score de compatibilité et prochaine étape concrète
- Personnalisation des emails par profil candidat

### Gamification
- Points XP à chaque action (réponse, section complétée, test terminé, CV uploadé)
- 5 niveaux : Curieux → Explorateur → Analyste → Stratège → Visionnaire
- Système de badges (Premier pas, Complétiste, Analyste…)
- Narration progressive pendant le test
- Insights débloquables à mesure de l'avancement

### Emailing & neuromarketing
- Campagnes d'invitation avec segmentation par statut ou profil
- Séquences email automatisées avec conditions et délais
- Optimisation neuromarketing des ob