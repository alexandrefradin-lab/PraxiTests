<script setup>
import { Link, Head } from '@inertiajs/vue3'

const props = defineProps({
    legal:    { type: Object, default: () => ({}) },
    contact:  { type: Object, default: () => ({}) },
    products: { type: Array,  default: () => [] },
})

const version = '1.0'
const date = '22 août 2026'

const euros = (cents) => (cents / 100).toLocaleString('fr-FR', { minimumFractionDigits: 0 }) + ' €'
</script>

<template>
    <Head title="Conditions Générales de Vente" />

    <div style="min-height:100vh; background:var(--bg-base); font-family:var(--font-body); color:var(--text-primary)">

        <!-- Header -->
        <header style="background:var(--color-accent); padding:1.25rem 2rem; display:flex; align-items:center; gap:12px">
            <Link href="/" style="display:flex; align-items:center; gap:10px; text-decoration:none">
                <div style="width:36px; height:36px; border-radius:8px; background:var(--color-primary); display:flex; align-items:center; justify-content:center">
                    <span style="font-family:var(--font-display); font-size:17px; font-weight:700; color:var(--bg-base)">P</span>
                </div>
                <span style="font-family:var(--font-display); font-size:16px; font-weight:600; color:var(--bg-base)">PraxiQuest</span>
            </Link>
            <span style="color:rgba(240,232,212,.4); margin-left:auto; font-family:var(--font-data); font-size:12px">v{{ version }} — {{ date }}</span>
        </header>

        <!-- Contenu -->
        <main style="max-width:860px; margin:0 auto; padding:3rem 2rem 5rem">

            <h1 style="font-family:var(--font-display); font-size:2rem; font-weight:700; letter-spacing:-0.02em; color:var(--text-primary); margin-bottom:0.5rem">
                Conditions Générales de Vente — Particuliers
            </h1>
            <p style="font-family:var(--font-data); font-size:13px; color:var(--text-muted); margin-bottom:3rem">
                Version {{ version }} — Entrée en vigueur le {{ date }}
            </p>

            <!-- 1 -->
            <section class="cgu-section">
                <h2>1. Identification du vendeur</h2>
                <p>
                    Les présentes Conditions Générales de Vente (« CGV ») régissent les ventes conclues entre
                    <strong>{{ legal.editor_name || 'Praxis Accompagnement' }}</strong>
                    ({{ legal.editor_status || 'Entrepreneur individuel' }}<span v-if="legal.editor_siret">, SIRET {{ legal.editor_siret }}</span>),
                    éditeur de la plateforme PraxiQuest (ci-après « le Vendeur »), et toute personne physique agissant
                    à des fins n'entrant pas dans le cadre de son activité professionnelle (ci-après « le Client »).
                </p>
                <p>
                    Contact : <a :href="`mailto:${contact.email || 'contact@praxiquest.fr'}`">{{ contact.email || 'contact@praxiquest.fr' }}</a>.
                    Les CGV complètent les <Link href="/cgu">Conditions Générales d'Utilisation</Link> et la
                    <Link href="/confidentialite">Politique de confidentialité</Link>, qui restent applicables.
                </p>
            </section>

            <!-- 2 -->
            <section class="cgu-section">
                <h2>2. Nature de l'offre</h2>
                <p>
                    PraxiQuest propose aux particuliers un <strong>parcours d'évaluation et d'orientation professionnelle en ligne</strong> :
                    des questionnaires d'auto-évaluation fondés sur des modèles psychométriques reconnus, une synthèse générée par
                    intelligence artificielle, des suggestions de pistes métiers et un rapport téléchargeable au format PDF.
                </p>
                <p>
                    <strong>Ce service est un outil d'auto-évaluation fourni à titre informatif.</strong> Il ne constitue pas un
                    bilan de compétences au sens des articles L6313-1 et suivants du Code du travail (dispositif encadré, réalisé
                    avec un prestataire certifié et comportant des entretiens individuels), n'est pas éligible au Compte Personnel
                    de Formation (CPF), et ne remplace pas l'accompagnement d'un professionnel qualifié
                    (cf. l'avertissement des <Link href="/cgu">CGU</Link>).
                </p>
            </section>

            <!-- 3 -->
            <section class="cgu-section">
                <h2>3. Offres et prix</h2>
                <p>Les offres proposées à l'achat sont les suivantes :</p>
                <ul>
                    <li v-for="p in products" :key="p.key">
                        <strong>{{ p.name }} — {{ euros(p.price) }} TTC</strong> (paiement unique) : {{ p.description }}
                        <span v-if="!p.available"> <em>(offre à venir, non commercialisée à ce jour)</em></span>
                    </li>
                </ul>
                <p>
                    Les prix sont indiqués en euros, toutes taxes comprises. TVA non applicable, article 293 B du Code général
                    des impôts (franchise en base). Le Vendeur peut modifier ses prix à tout moment ; le prix applicable est
                    celui affiché au moment de la commande.
                </p>
                <p>
                    L'inscription sur la plateforme et l'épreuve de découverte restent gratuites et sans engagement.
                </p>
            </section>

            <!-- 4 -->
            <section class="cgu-section">
                <h2>4. Commande et paiement</h2>
                <p>
                    La commande s'effectue en ligne, depuis l'espace personnel du Client, après acceptation expresse des
                    présentes CGV (case à cocher). Le paiement est exigible immédiatement et s'effectue par carte bancaire
                    via <strong>Stripe</strong>, prestataire de paiement sécurisé. Le Vendeur n'a jamais accès aux données
                    complètes de carte bancaire du Client.
                </p>
                <p>
                    La vente est réputée conclue à la confirmation du paiement par Stripe. Un reçu est adressé au Client
                    par email par le prestataire de paiement.
                </p>
            </section>

            <!-- 5 -->
            <section class="cgu-section">
                <h2>5. Livraison — contenu numérique</h2>
                <p>
                    L'accès au parcours complet (épreuves, relecture globale, rapport PDF) est <strong>débloqué immédiatement</strong>
                    après confirmation du paiement, dans l'espace personnel du Client. Aucun bien matériel n'est expédié.
                </p>
                <p>
                    En cas de difficulté d'accès après paiement, le Client contacte le support à l'adresse
                    <a :href="`mailto:${contact.email || 'contact@praxiquest.fr'}`">{{ contact.email || 'contact@praxiquest.fr' }}</a> ;
                    le Vendeur s'engage à rétablir l'accès dans les meilleurs délais.
                </p>
            </section>

            <!-- 6 -->
            <section class="cgu-section cgu-warning" style="background:rgba(166,117,32,0.05); border:1px solid rgba(166,117,32,0.25); border-left:4px solid var(--color-primary); border-radius:8px; padding:1.5rem 1.75rem">
                <h2 style="margin-top:0">6. Droit de rétractation</h2>
                <p>
                    Conformément à l'article L221-18 du Code de la consommation, le Client dispose en principe d'un délai de
                    quatorze (14) jours pour se rétracter d'un achat à distance.
                </p>
                <p>
                    <strong>Toutefois</strong>, s'agissant d'un contenu numérique fourni immédiatement sur un support immatériel,
                    le Client, en cochant la case prévue à cet effet lors de la commande, <strong>demande l'exécution immédiate
                    du service et reconnaît expressément renoncer à son droit de rétractation</strong> dès le déblocage de l'accès
                    (article L221-28, 1° et 13° du Code de la consommation).
                </p>
                <p style="margin-bottom:0">
                    Si le Client refuse cette renonciation, il lui suffit de ne pas finaliser la commande : aucun accès n'est
                    débloqué et aucun paiement n'est prélevé.
                </p>
            </section>

            <!-- 7 -->
            <section class="cgu-section">
                <h2>7. Garanties légales</h2>
                <p>
                    Le Client bénéficie de la garantie légale de conformité applicable aux contenus et services numériques
                    (articles L224-25-1 et suivants du Code de la consommation) et de la garantie des vices cachés
                    (articles 1641 et suivants du Code civil). En cas de défaut de conformité, le Client peut exiger la mise
                    en conformité du service, et à défaut une réduction du prix ou la résolution de la vente, dans les
                    conditions prévues par la loi.
                </p>
            </section>

            <!-- 8 -->
            <section class="cgu-section">
                <h2>8. Données personnelles</h2>
                <p>
                    Les traitements de données liés au parcours (réponses aux questionnaires, synthèses, rapport) sont décrits
                    dans la <Link href="/confidentialite">Politique de confidentialité</Link>. Les données de paiement sont
                    traitées par Stripe en qualité de prestataire de paiement.
                </p>
            </section>

            <!-- 9 -->
            <section class="cgu-section">
                <h2>9. Médiation de la consommation et litiges</h2>
                <p>
                    Conformément aux articles L611-1 et suivants du Code de la consommation, le Client peut recourir
                    gratuitement à un médiateur de la consommation en cas de litige non résolu directement avec le Vendeur.
                    Le Client peut également utiliser la plateforme européenne de règlement en ligne des litiges :
                    <a href="https://ec.europa.eu/consumers/odr" target="_blank" rel="noopener">ec.europa.eu/consumers/odr</a>.
                </p>
                <p>
                    Préalablement, toute réclamation est adressée au support :
                    <a :href="`mailto:${contact.email || 'contact@praxiquest.fr'}`">{{ contact.email || 'contact@praxiquest.fr' }}</a>.
                    Les présentes CGV sont régies par le droit français.
                </p>
            </section>

            <!-- Contact -->
            <section class="cgu-section" style="border-left:3px solid var(--color-primary); padding-left:1.25rem; border-bottom:none">
                <h2 style="margin-top:0">Contact</h2>
                <p>
                    <strong>{{ legal.editor_name || 'Praxis Accompagnement' }}</strong> —
                    <a :href="`mailto:${contact.email || 'contact@praxiquest.fr'}`">{{ contact.email || 'contact@praxiquest.fr' }}</a>
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
    line-height: 1.75;
    color: var(--text-secondary);
    margin-bottom: 0.35rem;
}
.cgu-section a {
    color: var(--color-primary);
    text-decoration: none;
}
.cgu-section a:hover { text-decoration: underline; }
</style>
