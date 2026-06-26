<script setup>
import { Link } from '@inertiajs/vue3'
import { defineProps } from 'vue'

defineProps({ branding: { type: Object, default: () => ({ name: 'PraxiQuest' }) } })

const version = '1.0'
const date = '23 juin 2026'
</script>

<template>
    <Head title="Politique de confidentialité" />

    <div style="min-height:100vh; background:var(--bg-base); font-family:var(--font-body); color:var(--text-primary)">

        <!-- Header -->
        <header style="background:var(--color-accent); padding:1.25rem 2rem; display:flex; align-items:center; gap:12px">
            <Link href="/" style="display:flex; align-items:center; gap:10px; text-decoration:none">
                <div style="width:36px; height:36px; border-radius:8px; background:var(--color-primary); display:flex; align-items:center; justify-content:center">
                    <span style="font-family:var(--font-display); font-size:17px; font-weight:700; color:var(--bg-base)">P</span>
                </div>
                <span style="font-family:var(--font-display); font-size:16px; font-weight:600; color:var(--bg-base)">{{ branding?.name ?? 'PraxiQuest' }}</span>
            </Link>
            <span style="color:rgba(240,232,212,.4); margin-left:auto; font-family:var(--font-data); font-size:12px">v{{ version }} — {{ date }}</span>
        </header>

        <!-- Contenu -->
        <main style="max-width:860px; margin:0 auto; padding:3rem 2rem 5rem">

            <h1 style="font-family:var(--font-display); font-size:2rem; font-weight:700; letter-spacing:-0.02em; color:var(--text-primary); margin-bottom:0.5rem">
                Politique de confidentialité
            </h1>
            <p style="font-family:var(--font-data); font-size:13px; color:var(--text-muted); margin-bottom:3rem">
                Version {{ version }} — Dernière mise à jour le {{ date }}
            </p>

            <section class="cgu-section">
                <h2>1. Responsable du traitement</h2>
                <p>
                    Le responsable du traitement des données collectées sur <strong>{{ branding?.name ?? 'PraxiQuest' }}</strong>
                    (ci-après « la Plateforme ») est <strong>Praxis Accompagnement</strong>.
                    Pour toute question relative à vos données :
                    <a href="mailto:alexandre.fradin@gmail.com">alexandre.fradin@gmail.com</a>.
                </p>
            </section>

            <section class="cgu-section">
                <h2>2. Données collectées</h2>
                <ul>
                    <li><strong>Identité et compte :</strong> nom, prénom, adresse e-mail, mot de passe (stocké chiffré) ;</li>
                    <li><strong>Profil professionnel :</strong> statut (salarié, entrepreneur, demandeur d'emploi), ancienneté, poste et secteur (facultatifs), problématique d'orientation ;</li>
                    <li><strong>CV :</strong> document téléversé ou informations saisies manuellement ;</li>
                    <li><strong>Réponses aux tests :</strong> données psychométriques saisies pendant les évaluations ;</li>
                    <li><strong>Synthèses et résultats :</strong> contenus générés à partir de vos réponses ;</li>
                    <li><strong>Données techniques :</strong> adresse IP, date/heure de connexion, navigateur (logs).</li>
                </ul>
            </section>

            <section class="cgu-section">
                <h2>3. Finalités et bases légales</h2>
                <ul>
                    <li><strong>Fourniture du service</strong> (passation des tests, restitution, espace personnel) — exécution du contrat ;</li>
                    <li><strong>Génération des synthèses et pistes de métiers par IA</strong> — exécution du contrat et consentement recueilli lors de l'onboarding ;</li>
                    <li><strong>Facturation et abonnements</strong> — exécution du contrat et obligation légale ;</li>
                    <li><strong>Communications marketing</strong> (le cas échéant) — consentement, retirable à tout moment ;</li>
                    <li><strong>Sécurité et lutte contre la fraude</strong> — intérêt légitime.</li>
                </ul>
            </section>

            <section class="cgu-section">
                <h2>4. Destinataires et sous-traitants</h2>
                <p>
                    Vos données ne sont jamais revendues. Elles sont traitées par des sous-traitants strictement
                    nécessaires au service, encadrés par des contrats conformes au RGPD :
                </p>
                <ul>
                    <li><strong>Hébergement</strong> (serveurs situés dans l'Union européenne) ;</li>
                    <li><strong>Fournisseurs d'IA</strong> : votre CV et vos réponses peuvent être transmis à des API
                        d'inférence de modèles de langage pour générer les synthèses. Ces données ne sont pas utilisées
                        pour entraîner les modèles du fournisseur ;</li>
                    <li><strong>Paiement</strong> : Stripe, pour la gestion des abonnements (aucune donnée de carte
                        bancaire n'est stockée par la Plateforme) ;</li>
                    <li><strong>Envoi d'e-mails</strong> : prestataire d'acheminement des messages transactionnels.</li>
                </ul>
            </section>

            <section class="cgu-section">
                <h2>5. Durées de conservation</h2>
                <p>
                    Les données de compte et de tests sont conservées tant que le compte est actif, puis jusqu'à
                    trois (3) ans après la dernière connexion, sauf demande de suppression anticipée. Les logs de
                    connexion sont conservés douze (12) mois. Les données de facturation sont conservées dix (10) ans
                    conformément aux obligations comptables.
                </p>
            </section>

            <section class="cgu-section">
                <h2>6. Données sensibles (CV)</h2>
                <p>
                    Votre CV peut contenir des données sensibles (art. 9 RGPD). Vous le téléversez volontairement et
                    êtes invité·e à ne pas y faire figurer d'informations sensibles non nécessaires à l'orientation.
                    Le CV est utilisé uniquement pour contextualiser la synthèse IA.
                </p>
            </section>

            <section class="cgu-section">
                <h2>7. Vos droits</h2>
                <p>Conformément au RGPD, vous disposez des droits d'accès, de rectification, d'effacement, de limitation,
                    de portabilité et d'opposition. Une partie de ces droits est accessible directement depuis votre
                    espace, rubrique <em>Mes données (RGPD)</em> (export et suppression du compte).</p>
                <p>
                    Vous pouvez aussi exercer vos droits par e-mail à
                    <a href="mailto:alexandre.fradin@gmail.com">alexandre.fradin@gmail.com</a>.
                    Réponse sous trente (30) jours. En cas de difficulté, vous pouvez saisir la <strong>CNIL</strong>
                    (www.cnil.fr).
                </p>
            </section>

            <section class="cgu-section">
                <h2>8. Communications et désinscription</h2>
                <p>
                    Si vous avez consenti aux e-mails marketing, chaque message contient un lien de désinscription
                    fonctionnel. Vous continuerez à recevoir les e-mails strictement nécessaires au service
                    (sécurité, facturation, résultats).
                </p>
            </section>

            <section class="cgu-section">
                <h2>9. Cookies</h2>
                <p>
                    La Plateforme n'utilise que des cookies strictement nécessaires à son fonctionnement (session,
                    sécurité). Aucun cookie publicitaire ou de profilage tiers n'est déposé sans votre consentement.
                </p>
            </section>

            <section class="cgu-section" style="border-left:3px solid var(--color-primary); padding-left:1.25rem; border-bottom:none">
                <h2 style="margin-top:0">Contact</h2>
                <p>
                    <strong>Praxis Accompagnement</strong> — <a href="mailto:alexandre.fradin@gmail.com">alexandre.fradin@gmail.com</a><br>
                    Voir aussi nos <Link href="/cgu" style="color:var(--color-primary); font-weight:600">Conditions Générales d'Utilisation</Link>.
                </p>
            </section>

        </main>

    </div>
</template>

<style scoped>
.cgu-section {
    margin-bottom: 2.5rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid var(--border-light);
}
.cgu-section h2 {
    font-family: var(--font-display);
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 1rem;
    margin-top: 0;
}
.cgu-section p {
    font-size: 0.9375rem;
    line-height: 1.75;
    color: var(--text-secondary);
    margin-bottom: 0.85rem;
}
.cgu-section p:last-child { margin-bottom: 0; }
.cgu-section ul {
    padding-left: 1.5rem;
    margin-bottom: 0.85rem;
}
.cgu-section li {
    font-size: 0.9375rem;
    line-height: 1.7;
    color: var(--text-secondary);
    margin-bottom: 0.4rem;
}
.cgu-section a { color: var(--color-primary); }
</style>
