<?php
/**
 * PraxiMet – Template rapport PDF détaillé
 * Variables disponibles : $lead, $prenom, $nom, $email, $date, $site,
 *                         $code, $scores, $resultat, $libelles, $questions
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$rangs          = ['Profil dominant','Profil secondaire','Profil tertiaire'];
$rang_classes   = ['dominant','secondaire','tertiaire'];
$lettres_code   = str_split( $code );

// Sous-domaines : regroupés par type avec score cumulé
$sd_par_type = [];
foreach ( $questions as $q ) {
    $t  = $q['type'];
    $sd = $q['sous_domaine'];
    if ( ! isset($sd_par_type[$t][$sd]) ) $sd_par_type[$t][$sd] = ['score' => 0, 'max' => 0];
    $sd_par_type[$t][$sd]['max']++;
    // Score estimé proportionnellement depuis le score total du type
    $nb_questions_type = count( array_filter($questions, function($x) use ($t) { return $x['type'] === $t; }) );
    // On répartit le score du type selon les sous-domaines (proportionnel au nb de questions)
}
// Recalcul propre : on ne peut pas savoir exactement, on distribue le score total
// de façon équitable entre les sous-domaines d'un même type
foreach ( $sd_par_type as $type => $sds ) {
    $nb_total = array_sum(array_column($sds, 'max'));
    foreach ( $sds as $nom_sd => $info ) {
        $ratio = $info['max'] / $nb_total;
        $sd_par_type[$type][$nom_sd]['score'] = round( $scores[$type] * $ratio, 1 );
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Rapport RIASEC – <?php echo $prenom . ' ' . $nom; ?></title>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html { -webkit-print-color-adjust: exact; print-color-adjust: exact; }

body {
    font-family: 'Segoe UI', Arial, Helvetica, sans-serif;
    font-size: 12px;
    color: #1a202c;
    background: #e8edf3;
    padding: 28px 20px;
}

/* ── Barre impression ── */
.print-bar {
    max-width: 820px;
    margin: 0 auto 16px;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}
.btn-print {
    background: #1e3a5f;
    color: #fff;
    border: none;
    padding: 10px 24px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 2px 8px rgba(30,58,95,.25);
}
.btn-print:hover { background: #2d5a8e; }
.btn-close {
    background: #fff;
    color: #1e3a5f;
    border: 1.5px solid #c8d8ec;
    padding: 10px 18px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
}

/* ── Page ── */
.page {
    background: #fff;
    max-width: 820px;
    margin: 0 auto;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 12px 48px rgba(0,0,0,.14);
}

/* ── En-tête ── */
.header {
    background: linear-gradient(135deg, #1e3a5f 0%, #2d5a8e 100%);
    padding: 32px 40px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
}
.header-left h1 {
    color: #fff;
    font-size: 22px;
    font-weight: 800;
    margin-bottom: 4px;
    letter-spacing: -.3px;
}
.header-left p { color: rgba(255,255,255,.6); font-size: 12px; }
.header-right { text-align: right; flex-shrink: 0; }
.badge {
    display: inline-block;
    background: #e86c2f;
    color: #fff;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 2px;
    text-transform: uppercase;
    padding: 5px 12px;
    border-radius: 5px;
    margin-bottom: 8px;
}
.header-meta { color: rgba(255,255,255,.5); font-size: 11px; line-height: 1.7; }

/* ── Candidat ── */
.candidat {
    background: #f0f5fb;
    padding: 18px 40px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 2px solid #e2e8f0;
}
.candidat-name  { font-size: 20px; font-weight: 800; color: #1e3a5f; }
.candidat-email { font-size: 11px; color: #64748b; margin-top: 3px; }
.candidat-date  { font-size: 11px; color: #94a3b8; text-align: right; }

/* ── Corps ── */
.body { padding: 32px 40px; }

/* ── Code RIASEC ── */
.code-section { text-align: center; margin-bottom: 36px; }
.code-label {
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 2.5px;
    color: #94a3b8;
    font-weight: 700;
    margin-bottom: 16px;
}
.code-letters { display: flex; justify-content: center; gap: 14px; }
.code-letter {
    width: 76px; height: 76px;
    background: #1e3a5f;
    color: #fff;
    border-radius: 14px;
    font-size: 36px;
    font-weight: 900;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 6px 20px rgba(30,58,95,.28);
    letter-spacing: -1px;
}

/* ── Radar + Scores ── */
.radar-scores {
    display: flex;
    gap: 28px;
    align-items: flex-start;
    margin-bottom: 36px;
    padding: 24px;
    background: #f8fafc;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
}
.radar-block { flex: 0 0 240px; }
.scores-block { flex: 1; }
.section-title {
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 2px;
    color: #94a3b8;
    margin-bottom: 14px;
    padding-bottom: 8px;
    border-bottom: 2px solid #e8f0fb;
}
.score-row {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 7px;
    padding: 5px 8px;
    border-radius: 6px;
    transition: background .1s;
}
.score-row.dominant { background: #e8f0fb; }
.score-lettre { width: 18px; font-size: 12px; font-weight: 900; color: #1e3a5f; flex-shrink: 0; }
.score-label  { width: 110px; font-size: 11px; color: #475569; flex-shrink: 0; }
.score-bar    { flex: 1; height: 7px; background: #e2e8f0; border-radius: 4px; overflow: hidden; }
.score-fill   { height: 100%; background: #1e3a5f; border-radius: 4px; }
.score-fill.dim { background: #c8d8ec; }
.score-val    { font-size: 11px; font-weight: 700; color: #94a3b8; width: 34px; text-align: right; flex-shrink: 0; }

/* ── Séparateur ── */
.sep { border: none; border-top: 2px solid #f0f5fb; margin: 28px 0; }

/* ── Profils ── */
.profil-card {
    display: flex;
    gap: 0;
    margin-bottom: 14px;
    border-radius: 10px;
    overflow: hidden;
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 4px rgba(0,0,0,.05);
}
.profil-side {
    width: 10px;
    flex-shrink: 0;
}
.profil-side.dominant   { background: #1e3a5f; }
.profil-side.secondaire { background: #2d5a8e; }
.profil-side.tertiaire  { background: #c8d8ec; }
.profil-content { padding: 16px 20px; flex: 1; background: #fafbfd; }
.profil-rang  { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; color: #94a3b8; margin-bottom: 3px; }
.profil-label { font-size: 15px; font-weight: 800; color: #1e3a5f; margin-bottom: 6px; }
.profil-desc  { font-size: 11px; color: #475569; line-height: 1.7; }

/* ── Sous-domaines ── */
.sd-section { margin-bottom: 36px; }
.sd-bloc {
    margin-bottom: 18px;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
}
.sd-bloc.dominant { border-color: #c8d8ec; }
.sd-head {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 16px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}
.sd-bloc.dominant .sd-head { background: #e8f0fb; }
.sd-lettre {
    width: 28px; height: 28px;
    background: #94a3b8;
    color: #fff;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.sd-bloc.dominant .sd-lettre { background: #1e3a5f; }
.sd-type-label { font-size: 13px; font-weight: 700; color: #64748b; }
.sd-bloc.dominant .sd-type-label { color: #1e3a5f; }
.sd-body { padding: 12px 16px; }
.sd-row { margin-bottom: 10px; }
.sd-row:last-child { margin-bottom: 0; }
.sd-row-top { display: flex; justify-content: space-between; margin-bottom: 4px; }
.sd-nom { font-size: 11px; font-weight: 600; color: #475569; }
.sd-pct { font-size: 11px; font-weight: 700; color: #94a3b8; }
.sd-bar-track { height: 6px; background: #e2e8f0; border-radius: 3px; overflow: hidden; }
.sd-bar-fill  { height: 100%; background: #c8d8ec; border-radius: 3px; }
.sd-bloc.dominant .sd-bar-fill { background: #1e3a5f; }



/* ── Pied de page ── */
.footer {
    background: #f8fafc;
    border-top: 2px solid #e8f0fb;
    padding: 16px 40px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 10px;
    color: #94a3b8;
}
.footer strong { color: #1e3a5f; }

/* ── Print ── */
@media print {
    body { background: #fff; padding: 0; }
    .page { box-shadow: none; border-radius: 0; max-width: 100%; }
    .print-bar { display: none !important; }
    .profil-card,
    .sd-bloc,
    .header,
    .code-letter,
    .score-fill,
    .sd-bar-fill,
    .profil-side { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
}
</style>
</head>
<body>

<!-- Barre impression -->
<div class="print-bar">
    <button class="btn-close" onclick="window.close()">✕ Fermer</button>
    <button class="btn-print" onclick="window.print()">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
            <polyline points="6 9 6 2 18 2 18 9"/>
            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
            <rect x="6" y="14" width="12" height="8"/>
        </svg>
        Enregistrer en PDF
    </button>
</div>

<div class="page">

    <!-- En-tête -->
    <div class="header">
        <div class="header-left">
            <h1>Rapport de personnalité professionnelle</h1>
            <p>Test d'orientation basé sur le modèle RIASEC de Holland</p>
        </div>
        <div class="header-right">
            <div class="badge">PraxiMet</div>
            <div class="header-meta">
                <?php echo $site; ?><br>
                Généré le <?php echo $date; ?>
            </div>
        </div>
    </div>

    <!-- Candidat -->
    <div class="candidat">
        <div>
            <div class="candidat-name"><?php echo $prenom . ' ' . $nom; ?></div>
            <div class="candidat-email"><?php echo $email; ?></div>
        </div>
        <div class="candidat-date">Test réalisé le <?php echo $date; ?></div>
    </div>

    <div class="body">

        <!-- Code RIASEC -->
        <div class="code-section">
            <div class="code-label">Votre code de personnalité professionnelle</div>
            <div class="code-letters">
                <?php foreach ( $lettres_code as $l ) : ?>
                <div class="code-letter"><?php echo esc_html($l); ?></div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Radar + Scores globaux -->
        <div class="radar-scores">
            <div class="radar-block">
                <?php echo PraxiMet_PDF_Generator::svg_radar( $scores, $code, 240 ); ?>
            </div>
            <div class="scores-block">
                <div class="section-title">Scores par dimension</div>
                <?php foreach ( $scores as $lettre => $score ) :
                    $pct      = min(100, round(($score / 14) * 100));
                    $dominant = strpos($code, $lettre) !== false;
                ?>
                <div class="score-row <?php echo $dominant ? 'dominant' : ''; ?>">
                    <div class="score-lettre"><?php echo esc_html($lettre); ?></div>
                    <div class="score-label"><?php echo esc_html($libelles[$lettre] ?? ''); ?></div>
                    <div class="score-bar">
                        <div class="score-fill <?php echo !$dominant ? 'dim' : ''; ?>" style="width:<?php echo $pct; ?>%"></div>
                    </div>
                    <div class="score-val"><?php echo $score; ?>/14</div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <hr class="sep">

        <!-- Profils dominants -->
        <div class="section-title">Votre profil en détail</div>
        <?php foreach ( $resultat['profil'] as $i => $type ) :
            $rang_class = $rang_classes[$i] ?? 'tertiaire';
        ?>
        <div class="profil-card">
            <div class="profil-side <?php echo $rang_class; ?>"></div>
            <div class="profil-content">
                <div class="profil-rang"><?php echo esc_html($rangs[$i] ?? ''); ?></div>
                <div class="profil-label"><?php echo esc_html($type['label']); ?></div>
                <div class="profil-desc"><?php echo esc_html($type['description']); ?></div>
            </div>
        </div>
        <?php endforeach; ?>

        <hr class="sep">

        <!-- Scores par sous-domaine -->
        <div class="section-title">Détail par sous-domaine</div>
        <div class="sd-section">
            <?php foreach ( ['R','I','A','S','E','C'] as $type ) :
                $dominant   = strpos($code, $type) !== false;
                $sds        = $sd_par_type[$type] ?? [];
                $label_type = $libelles[$type] ?? $type;
            ?>
            <div class="sd-bloc <?php echo $dominant ? 'dominant' : ''; ?>">
                <div class="sd-head">
                    <div class="sd-lettre"><?php echo esc_html($type); ?></div>
                    <div class="sd-type-label"><?php echo esc_html($label_type); ?></div>
                </div>
                <div class="sd-body">
                    <?php foreach ( $sds as $nom_sd => $info ) :
                        $pct_sd = min(100, round(($info['score'] / $info['max']) * 100));
                    ?>
                    <div class="sd-row">
                        <div class="sd-row-top">
                            <span class="sd-nom"><?php echo esc_html($nom_sd); ?></span>
                            <span class="sd-pct"><?php echo round($info['score'],1); ?>/<?php echo $info['max']; ?></span>
                        </div>
                        <div class="sd-bar-track">
                            <div class="sd-bar-fill" style="width:<?php echo $pct_sd; ?>%"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>


    </div><!-- .body -->

    <!-- Pied de page -->
    <div class="footer">
        <span>Rapport généré par <strong>PraxiMet</strong> — <?php echo $site; ?></span>
        <span>Document confidentiel — <?php echo $prenom . ' ' . $nom; ?> — <?php echo $date; ?></span>
    </div>

</div><!-- .page -->

<script>
window.addEventListener('load', function() {
    setTimeout(function() { window.print(); }, 700);
});
</script>
</body>
</html>
