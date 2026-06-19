<?php
/**
 * Rapport de restitution 360.
 * Variables : $campaign (obj), $data (array Praxis360_Scoring::compute), $open_answers (array).
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$dims     = $data['dimensions'];          // key => label
$dim_keys = array_keys( $dims );
$self     = $data['self'];
$others   = $data['others'];
$gaps     = $data['gaps'];
$scores   = $data['scores'];
$counts   = $data['counts'];
$rel_lbl  = Praxis360_Items::relations();

// Y a-t-il au moins une réponse d'évaluateur ?
$has_others = false;
foreach ( $others as $v ) { if ( null !== $v ) { $has_others = true; break; } }

/** Construit les points d'un polygone radar pour un jeu de valeurs. */
if ( ! function_exists( 'praxis360_radar_points' ) ) {
    function praxis360_radar_points( $values, $dim_keys, $cx, $cy, $R ) {
        $n   = count( $dim_keys );
        $pts = array();
        $i   = 0;
        foreach ( $dim_keys as $k ) {
            $val   = isset( $values[ $k ] ) && null !== $values[ $k ] ? $values[ $k ] : 0;
            $ratio = $val / 5;
            $angle = -M_PI / 2 + ( 2 * M_PI * $i / $n );
            $x     = $cx + ( $R * $ratio ) * cos( $angle );
            $y     = $cy + ( $R * $ratio ) * sin( $angle );
            $pts[] = round( $x, 1 ) . ',' . round( $y, 1 );
            $i++;
        }
        return implode( ' ', $pts );
    }
}
$cx = 200; $cy = 200; $R = 150;
?>
<div class="p360-report">

    <p class="p360-legend">
        <strong>Comment lire ce rapport.</strong> Ce 360° n'est pas une note. Un <strong>écart</strong> entre votre perception
        et celle des autres n'est ni bon ni mauvais&nbsp;: c'est une information précieuse pour grandir.
        Concentrez-vous sur 2&nbsp;à&nbsp;3 axes, pas sur tout à la fois.
    </p>

    <?php if ( ! $has_others ) : ?>
        <p>Aucune réponse d'évaluateur n'est encore disponible. Le rapport complet s'affichera dès que les évaluateurs auront répondu.</p>
    <?php endif; ?>

    <h2>Vue d'ensemble</h2>
    <div class="p360-radar-wrap">
        <svg viewBox="0 0 400 420" width="400" height="420" role="img" aria-label="Radar des dimensions">
            <?php
            // Grille (cercles concentriques 1..5).
            for ( $g = 1; $g <= 5; $g++ ) {
                $rr = $R * $g / 5;
                echo '<circle cx="' . $cx . '" cy="' . $cy . '" r="' . $rr . '" fill="none" stroke="#e2e2e2" stroke-width="1" />';
            }
            // Axes + labels.
            $n = count( $dim_keys ); $i = 0;
            foreach ( $dim_keys as $k ) {
                $angle = -M_PI / 2 + ( 2 * M_PI * $i / $n );
                $x  = $cx + $R * cos( $angle );
                $y  = $cy + $R * sin( $angle );
                $lx = $cx + ( $R + 24 ) * cos( $angle );
                $ly = $cy + ( $R + 24 ) * sin( $angle );
                echo '<line x1="' . $cx . '" y1="' . $cy . '" x2="' . round( $x, 1 ) . '" y2="' . round( $y, 1 ) . '" stroke="#e2e2e2" stroke-width="1" />';
                $anchor = ( abs( cos( $angle ) ) < 0.3 ) ? 'middle' : ( cos( $angle ) > 0 ? 'start' : 'end' );
                echo '<text x="' . round( $lx, 1 ) . '" y="' . round( $ly, 1 ) . '" font-size="10" fill="#002345" text-anchor="' . $anchor . '">' . esc_html( $dims[ $k ] ) . '</text>';
                $i++;
            }
            // Polygone "autres" (orange).
            if ( $has_others ) {
                $po = praxis360_radar_points( $others, $dim_keys, $cx, $cy, $R );
                echo '<polygon points="' . esc_attr( $po ) . '" fill="rgba(233,90,0,0.18)" stroke="#E95A00" stroke-width="2" />';
            }
            // Polygone "auto" (bleu nuit).
            $ps = praxis360_radar_points( $self, $dim_keys, $cx, $cy, $R );
            echo '<polygon points="' . esc_attr( $ps ) . '" fill="rgba(0,35,69,0.12)" stroke="#002345" stroke-width="2" stroke-dasharray="5,4" />';
            ?>
            <!-- Légende -->
            <rect x="60" y="398" width="14" height="6" fill="#002345" />
            <text x="80" y="404" font-size="11" fill="#212934">Auto-évaluation</text>
            <rect x="220" y="398" width="14" height="6" fill="#E95A00" />
            <text x="240" y="404" font-size="11" fill="#212934">Regard des autres</text>
        </svg>
    </div>

    <h2>Scores par dimension</h2>
    <table class="p360-table">
        <thead>
            <tr>
                <th>Dimension</th>
                <th>Auto</th>
                <?php foreach ( array( 'manager', 'peer', 'report', 'client' ) as $rel ) :
                    if ( isset( $scores[ $rel ] ) ) : ?>
                        <th><?php echo esc_html( $rel_lbl[ $rel ] ); ?><br><small>(n=<?php echo (int) $counts[ $rel ]; ?>)</small></th>
                    <?php endif;
                endforeach; ?>
                <th>Ens. autres</th>
                <th>Écart</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ( $dim_keys as $k ) :
            $gap = $gaps[ $k ];
            $gap_class = ( null === $gap ) ? '' : ( $gap >= 0 ? 'p360-gap-pos' : 'p360-gap-neg' );
            ?>
            <tr>
                <td><?php echo esc_html( $dims[ $k ] ); ?></td>
                <td><?php echo null !== $self[ $k ] ? esc_html( number_format( $self[ $k ], 1 ) ) : '—'; ?></td>
                <?php foreach ( array( 'manager', 'peer', 'report', 'client' ) as $rel ) :
                    if ( isset( $scores[ $rel ] ) ) :
                        $v = $scores[ $rel ][ $k ]; ?>
                        <td><?php echo null !== $v ? esc_html( number_format( $v, 1 ) ) : '—'; ?></td>
                    <?php endif;
                endforeach; ?>
                <td><?php echo null !== $others[ $k ] ? esc_html( number_format( $others[ $k ], 1 ) ) : '—'; ?></td>
                <td class="<?php echo esc_attr( $gap_class ); ?>"><?php echo null !== $gap ? esc_html( ( $gap > 0 ? '+' : '' ) . number_format( $gap, 1 ) ) : '—'; ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <p style="font-size:13px;color:var(--praxis-text-soft);">
        Les catégories de moins de <?php echo (int) Praxis360_Scoring::ANON_THRESHOLD; ?> répondants ne sont pas affichées séparément, pour préserver l'anonymat.
    </p>

    <?php if ( $has_others ) : ?>
    <h2>Lecture rapide</h2>
    <div class="p360-cards">
        <div class="p360-block strengths">
            <h3>Vos forces</h3>
            <ul>
                <?php foreach ( $data['strengths'] as $k ) : ?>
                    <li><?php echo esc_html( $dims[ $k ] ); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="p360-block improve">
            <h3>Axes de progrès</h3>
            <ul>
                <?php foreach ( $data['improvements'] as $k ) : ?>
                    <li><?php echo esc_html( $dims[ $k ] ); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="p360-block blind">
            <h3>Angles morts</h3>
            <?php if ( empty( $data['blindspots'] ) ) : ?>
                <p style="font-size:14px;">Pas d'écart marqué&nbsp;: votre perception est globalement alignée avec celle des autres.</p>
            <?php else : ?>
                <ul>
                    <?php foreach ( $data['blindspots'] as $k ) : ?>
                        <li><?php echo esc_html( $dims[ $k ] ); ?> <small>(les autres vous voient plus fort que vous-même)</small></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if ( ! empty( $open_answers ) ) : ?>
    <h2>Verbatims</h2>
    <?php
    $oq_labels = array(
        'open_1' => 'Points forts à conserver',
        'open_2' => 'Axes de progrès',
        'open_3' => 'Conseils libres',
    );
    foreach ( $oq_labels as $qkey => $qlabel ) :
        $rows = array_filter( $open_answers, function ( $r ) use ( $qkey ) { return $r->question_key === $qkey; } );
        if ( empty( $rows ) ) { continue; } ?>
        <h3 style="color:var(--praxis-navy);"><?php echo esc_html( $qlabel ); ?></h3>
        <?php foreach ( $rows as $r ) : ?>
            <div class="p360-verbatim">
                <div class="rel"><?php echo esc_html( isset( $rel_lbl[ $r->relation ] ) ? $rel_lbl[ $r->relation ] : $r->relation ); ?></div>
                <?php echo esc_html( $r->answer_text ); ?>
            </div>
        <?php endforeach; ?>
    <?php endforeach; ?>
    <?php endif; ?>

    <p class="p360-no-print" style="margin-top:32px;">
        <button class="p360-btn" onclick="window.print()">Imprimer / enregistrer en PDF</button>
    </p>
</div>
