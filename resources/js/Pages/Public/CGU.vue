<script setup>
import { Link } from '@inertiajs/vue3'
import { defineProps } from 'vue'

defineProps({ branding: { type: Object, default: () => ({ name: 'PraxiQuest' }) } })

const version = '1.0'
const date = '18 juin 2026'
</script>

<template>
    <Head title="Conditions Générales d'Utilisation" />

    <div style="min-height:100vh; background:var(--pt-cream, #f8f5f0); font-family:'Inter',sans-serif; color:#1a1a2e">

        <!-- Header -->
        <header style="background:var(--pt-navy, #0d1b3e); padding:1.25rem 2rem; display:flex; align-items:center; gap:12px">
            <Link href="/" style="display:flex; align-items:center; gap:10px; text-decoration:none">
                <div style="width:36px; height:36px; border-radius:8px; background:var(--pt-gold, #b8913a); display:flex; align-items:center; justify-content:center">
                    <span style="font-family:'Playfair Display',serif; font-size:17px; font-weight:700; color:white">P</span>
                </div>
                <span style="font-size:16px; font-weight:600; color:white">{{ branding?.name ?? 'PraxiQuest' }}</span>
            </Link>
            <span style="color:rgba(255,255,255,.35); margin-left:auto; font-size:13px">v{{ version }} — {{ date }}</span>
        </header>

        <!-- Contenu -->
        <main style="max-width:860px; margin:0 auto; padding:3rem 2rem 5rem">

            <h1 style="font-family:'Playfair Display',serif; font-size:2rem; font-weight:700; color:var(--pt-navy, #0d1b3e); margin-bottom:0.5rem">
                Conditions Générales d'Utilisation
            </h1>
            <p style="font-size:14px; color:#666; margin-bottom:3rem">
                Version {{ version }} — Entrée en vigueur le {{ date }}
            </p>

            <!-- 1 -->
            <section class="cgu-section">
                <h2>1. Présentation du service</h2>
                <p>
                    <strong>{{ branding?.name ?? 'PraxiQuest' }}</strong> (ci-après « la Plateforme ») est un service en ligne d'évaluation psychométrique et d'orientation professionnelle édité par <strong>Praxis Accompagnement</strong>, auto-entrepreneur, dont l'adresse de contact est : <a href="mailto:alexandre.fradin@gmail.com">alexandre.fradin@gmail.com</a>.
                </p>
                <p>
                    La Plateforme permet à ses utilisateurs (ci-après « l'Utilisateur ») de passer des tests psychométriques, d'obtenir une synthèse générée par intelligence artificielle, de recevoir des suggestions d'orientations professionnelles et de bénéficier de fonctionnalités d'invitation, de suivi et de restitution.
                </p>
                <p>
                    L'accès à la Plateforme implique l'acceptation sans réserve des présentes CGU. Toute utilisation de la Plateforme vaut acceptation pleine et entière.
                </p>
            </section>

            <!-- 2 -->
            <section class="cgu-section">
                <h2>2. Accès et inscription</h2>
                <p>
                    L'utilisation des fonctionnalités principales est soumise à la création d'un compte. L'Utilisateur s'engage à fournir des informations exactes, complètes et à les maintenir à jour.
                </p>
                <p>
                    L'Utilisateur est seul responsable de la confidentialité de ses identifiants. Toute connexion effectuée à partir de son compte est présumée effectuée par lui. En cas de compromission suspectée, il doit en informer l'éditeur immédiatement.
                </p>
                <p>
                    L'éditeur se réserve le droit de suspendre ou supprimer tout compte en cas de violation des présentes CGU, de comportement frauduleux ou de tentative de nuire à la Plateforme ou à d'autres utilisateurs, sans préavis ni indemnité.
                </p>
            </section>

            <!-- 3 -->
            <section class="cgu-section">
                <h2>3. Données collectées et finalité</h2>
                <p>Dans le cadre du service, les données suivantes sont collectées :</p>
                <ul>
                    <li><strong>Données d'identité :</strong> nom, prénom, adresse e-mail ;</li>
                    <li><strong>Données de profil professionnel :</strong> statut (salarié, entrepreneur, demandeur d'emploi), ancienneté dans ce statut ;</li>
                    <li><strong>Curriculum vitae (CV) :</strong> document téléversé avant la passation de tests, utilisé comme contexte pour la génération de la synthèse IA ;</li>
                    <li><strong>Réponses aux tests :</strong> données psychométriques saisies au cours des sessions d'évaluation ;</li>
                    <li><strong>Données de navigation et de connexion :</strong> adresse IP, date et heure de connexion, navigateur utilisé.</li>
                </ul>
                <p>
                    Ces données sont collectées aux fins suivantes : fourniture du service, génération des synthèses, envoi des résultats, lutte contre la fraude et amélioration de la Plateforme. Elles ne sont en aucun cas revendues à des tiers à des fins commerciales.
                </p>
            </section>

            <!-- 4 -->
            <section class="cgu-section">
                <h2>4. Intelligence artificielle et synthèse des résultats</h2>
                <p>
                    La Plateforme utilise des systèmes d'intelligence artificielle (ci-après « IA ») pour générer des synthèses de résultats et suggérer des pistes d'orientation professionnelle, incluant une liste indicative de quinze (15) métiers.
                </p>
                <p>
                    <strong>Ces suggestions constituent des indications à titre purement informatif et non contraignant.</strong> Elles ne sauraient en aucun cas être assimilées à un conseil professionnel, psychologique, médical, juridique ou financier. L'éditeur ne garantit pas l'exactitude, la complétude ou l'adéquation des suggestions générées par l'IA aux situations individuelles de chaque utilisateur.
                </p>
                <p>
                    L'Utilisateur est seul responsable des décisions qu'il prend à la lecture de ces synthèses. L'éditeur décline expressément toute responsabilité quant aux conséquences — directes ou indirectes — de l'utilisation des résultats générés par l'IA.
                </p>
                <p>
                    Le CV et les réponses aux tests peuvent être traités par des fournisseurs tiers d'IA (notamment des API d'inférence de modèles de langage). Ces traitements sont encadrés par des contrats de sous-traitance conformes au RGPD. Les données transmises à ces API ne sont pas utilisées pour entraîner les modèles du fournisseur.
                </p>
            </section>

            <!-- 5 -->
            <section class="cgu-section">
                <h2>5. Données personnelles et RGPD</h2>
                <p>
                    Conformément au Règlement (UE) 2016/679 (RGPD) et à la loi Informatique et Libertés, l'Utilisateur dispose des droits suivants sur ses données personnelles :
                </p>
                <ul>
                    <li><strong>Droit d'accès</strong> (art. 15 RGPD) : obtenir confirmation que ses données sont traitées et en recevoir une copie ;</li>
                    <li><strong>Droit de rectification</strong> (art. 16 RGPD) : faire corriger des données inexactes ;</li>
                    <li><strong>Droit à l'effacement</strong> (art. 17 RGPD) : demander la suppression de ses données dans les conditions prévues par la loi ;</li>
                    <li><strong>Droit à la limitation du traitement</strong> (art. 18 RGPD) ;</li>
                    <li><strong>Droit à la portabilité</strong> (art. 20 RGPD) : recevoir ses données dans un format structuré et lisible ;</li>
                    <li><strong>Droit d'opposition</strong> (art. 21 RGPD) : s'opposer à certains traitements.</li>
                </ul>
                <p>
                    Ces droits s'exercent par e-mail à <a href="mailto:alexandre.fradin@gmail.com">alexandre.fradin@gmail.com</a>, en joignant une preuve d'identité. L'éditeur s'engage à répondre dans un délai de trente (30) jours.
                </p>
                <p>
                    Les données sont conservées pendant la durée du compte actif, puis pendant une durée de trois (3) ans après la dernière connexion aux fins de prospection commerciale, sauf demande de suppression anticipée. Les données de connexion (logs) sont conservées douze (12) mois conformément à la loi.
                </p>
                <p>
                    En cas de difficulté, l'Utilisateur peut adresser une réclamation à la <strong>CNIL</strong> (www.cnil.fr).
                </p>
            </section>

            <!-- 6 -->
            <section class="cgu-section">
                <h2>6. CV et données sensibles</h2>
                <p>
                    Le CV téléversé par l'Utilisateur peut contenir des données à caractère personnel sensibles (situation familiale, état de santé, handicap, appartenance syndicale, etc.). L'Utilisateur reconnaît téléverser son CV volontairement et s'engage à ne pas y faire figurer de données sensibles au sens de l'article 9 du RGPD qui ne seraient pas strictement nécessaires au service.
                </p>
                <p>
                    L'éditeur traite le CV uniquement dans le cadre de la génération de la synthèse IA. Il ne l'exploite pas à d'autres fins sans le consentement explicite de l'Utilisateur.
                </p>
            </section>

            <!-- 7 -->
            <section class="cgu-section">
                <h2>7. Système d'invitation par e-mail</h2>
                <p>
                    La Plateforme dispose d'un système permettant à des tiers (recruteurs, conseillers, professionnels RH) d'inviter des candidats à passer des tests via e-mail. En acceptant une invitation, l'Utilisateur accepte que ses résultats puissent être partagés avec l'émetteur de l'invitation dans les conditions définies lors de l'envoi de celle-ci.
                </p>
                <p>
                    L'émetteur de l'invitation est responsable des données qu'il collecte et transmet dans ce cadre. L'éditeur de la Plateforme ne peut être tenu responsable d'un usage abusif des invitations par un tiers.
                </p>
            </section>

            <!-- 8 -->
            <section class="cgu-section">
                <h2>8. Propriété intellectuelle</h2>
                <p>
                    L'ensemble des éléments constituant la Plateforme (code source, interfaces, textes, algorithmes de scoring, questions de tests, graphismes, marques, logos) est la propriété exclusive de l'éditeur ou de ses concédants de licence. Toute reproduction, représentation, adaptation ou exploitation non autorisée est strictement interdite et expose son auteur à des poursuites civiles et pénales.
                </p>
                <p>
                    Les plugins tiers intégrés à la Plateforme restent soumis à leurs licences respectives. L'éditeur ne concède à l'Utilisateur aucun droit de propriété intellectuelle sur le service, autre qu'un droit d'utilisation personnel, non exclusif et non cessible, dans le cadre des présentes CGU.
                </p>
            </section>

            <!-- 9 -->
            <section class="cgu-section">
                <h2>9. Responsabilités et limitation de garantie</h2>
                <p>
                    La Plateforme est fournie <strong>« en l'état »</strong>. L'éditeur ne garantit pas (i) l'absence d'interruptions, d'erreurs ou de bugs ; (ii) l'adéquation du service aux attentes spécifiques de l'Utilisateur ; (iii) la pérennité du service.
                </p>
                <p>
                    Dans les limites permises par la loi applicable, la responsabilité de l'éditeur est strictement limitée aux dommages directs prouvés causés par une faute lourde ou intentionnelle. En aucun cas l'éditeur ne saurait être tenu responsable des dommages indirects (perte de chance, préjudice financier, atteinte à l'image, etc.).
                </p>
                <p>
                    L'éditeur ne peut être tenu responsable des décisions professionnelles ou personnelles prises par l'Utilisateur sur la base des résultats de tests ou des suggestions d'orientation générées par l'IA.
                </p>
            </section>

            <!-- 10 -->
            <section class="cgu-section">
                <h2>10. Comportements interdits</h2>
                <p>L'Utilisateur s'interdit notamment de :</p>
                <ul>
                    <li>tenter de contourner, pirater ou altérer la Plateforme ou ses systèmes de sécurité ;</li>
                    <li>soumettre de fausses informations ou créer des comptes fictifs ;</li>
                    <li>utiliser des outils automatisés (bots, scrapers) pour accéder au service ;</li>
                    <li>vendre, louer ou transférer son compte à un tiers ;</li>
                    <li>porter atteinte aux droits de propriété intellectuelle de l'éditeur ou de tiers ;</li>
                    <li>utiliser le service à des fins illicites ou contraires à l'ordre public.</li>
                </ul>
            </section>

            <!-- 11 -->
            <section class="cgu-section">
                <h2>11. Cookies et traceurs</h2>
                <p>
                    La Plateforme utilise des cookies strictement nécessaires à son fonctionnement (session, sécurité, préférences). Aucun cookie publicitaire ou de profilage tiers n'est déposé sans consentement préalable. L'Utilisateur peut configurer son navigateur pour refuser les cookies, au risque d'altérer certaines fonctionnalités.
                </p>
            </section>

            <!-- 12 -->
            <section class="cgu-section">
                <h2>12. Modifications des CGU</h2>
                <p>
                    L'éditeur se réserve le droit de modifier les présentes CGU à tout moment. Les utilisateurs seront informés par e-mail ou par une notification lors de leur prochaine connexion. Toute poursuite d'utilisation de la Plateforme après notification vaut acceptation des nouvelles CGU.
                </p>
            </section>

            <!-- 13 -->
            <section class="cgu-section">
                <h2>13. Résiliation</h2>
                <p>
                    L'Utilisateur peut fermer son compte à tout moment en adressant une demande à <a href="mailto:alexandre.fradin@gmail.com">alexandre.fradin@gmail.com</a>. La fermeture entraîne la suppression des données conformément à l'article 5. L'éditeur peut résilier un compte sans préavis en cas de violation grave des présentes CGU.
                </p>
            </section>

            <!-- 14 -->
            <section class="cgu-section">
                <h2>14. Droit applicable et juridiction</h2>
                <p>
                    Les présentes CGU sont régies par le droit français. En cas de litige, les parties s'engagent à rechercher une solution amiable avant tout recours judiciaire. À défaut d'accord, les tribunaux compétents sont ceux du ressort du siège de l'éditeur, sauf disposition impérative contraire.
                </p>
                <p>
                    Conformément aux articles L.611-1 et suivants du Code de la consommation, l'Utilisateur peut recourir gratuitement à un médiateur de la consommation.
                </p>
            </section>

            <!-- Contact -->
            <section class="cgu-section" style="border-left:3px solid var(--pt-gold, #b8913a); padding-left:1.25rem; border-bottom:none">
                <h2 style="margin-top:0">Contact</h2>
                <p>
                    Pour toute question relative aux présentes CGU ou à vos données personnelles :<br>
                    <strong>Praxis Accompagnement</strong> — <a href="mailto:alexandre.fradin@gmail.com">alexandre.fradin@gmail.com</a>
                </p>
            </section>

        </main>

    </div>
</template>

<style scoped>
.cgu-section {
    margin-bottom: 2.5rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid rgba(0,0,0,.07);
}
.cgu-section h2 {
    font-family: 'Playfair Display', serif;
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--pt-navy, #0d1b3e);
    margin-bottom: 1rem;
    margin-top: 0;
}
.cgu-section p {
    font-size: 0.9375rem;
    line-height: 1.75;
    color: #333;
    margin-bottom: 0.85rem;
}
.cgu-section p:last-child { margin-bottom: 0; }
.cgu-section ul {
    padding-left: 1.5rem;
    margin-bottom: 0.85rem;
}
.cgu-section li {
    font-size: 0.9375rem;
    line-height: 1.75;
    color: #333;
    margin-bottom: 0.35rem;
}
.cgu-section a {
    color: var(--pt-gold, #b8913a);
    text-decoration: none;
}
.cgu-section a:hover { text-decoration: underline; }
</style>
