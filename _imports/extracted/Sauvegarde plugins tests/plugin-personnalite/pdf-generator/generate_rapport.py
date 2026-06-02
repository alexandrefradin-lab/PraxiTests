#!/usr/bin/env python3
"""
Génération du rapport PDF de personnalité Big Five.
Usage : python3 generate_rapport.py <json_file> <output_pdf>
"""
import sys, json, os, math
from reportlab.lib.pagesizes import A4
from reportlab.lib import colors
from reportlab.lib.units import mm, cm
from reportlab.lib.styles import ParagraphStyle
from reportlab.lib.enums import TA_LEFT, TA_CENTER, TA_RIGHT
from reportlab.platypus import (
    SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle,
    HRFlowable, KeepTogether, PageBreak
)
from reportlab.graphics.shapes import Drawing, Rect, String, Line, Circle
from reportlab.graphics import renderPDF
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont

# ── Couleurs palette ──────────────────────────────────────────────────────────
C1      = colors.HexColor('#4F46E5')   # Indigo principal
C2      = colors.HexColor('#7C3AED')   # Violet secondaire
GREY_BG = colors.HexColor('#F8FAFC')
GREY_BD = colors.HexColor('#E2E8F0')
GREY_TX = colors.HexColor('#64748B')
DARK    = colors.HexColor('#1E293B')
WHITE   = colors.white

DIM_COLORS = {
    'O': colors.HexColor('#7C3AED'),
    'C': colors.HexColor('#4F46E5'),
    'E': colors.HexColor('#D97706'),
    'A': colors.HexColor('#16A34A'),
    'N': colors.HexColor('#DC2626'),
}
DIM_LABELS = {
    'O': "Ouverture",
    'C': "Conscience",
    'E': "Extraversion",
    'A': "Agréabilité",
    'N': "Stabilité émotionnelle",
}

W, H = A4   # 595.27 x 841.89 pts
MARGIN = 20*mm

# ── Styles paragraphes ────────────────────────────────────────────────────────
def make_styles():
    return {
        'title': ParagraphStyle('title',
            fontSize=26, leading=32, textColor=WHITE,
            fontName='Helvetica-Bold', alignment=TA_CENTER),
        'subtitle': ParagraphStyle('subtitle',
            fontSize=14, leading=20, textColor=colors.HexColor('#C7D2FE'),
            fontName='Helvetica', alignment=TA_CENTER),
        'h1': ParagraphStyle('h1',
            fontSize=16, leading=22, textColor=DARK,
            fontName='Helvetica-Bold', spaceBefore=6, spaceAfter=4),
        'h2': ParagraphStyle('h2',
            fontSize=12, leading=16, textColor=C1,
            fontName='Helvetica-Bold', spaceBefore=4, spaceAfter=2),
        'body': ParagraphStyle('body',
            fontSize=9.5, leading=14, textColor=colors.HexColor('#334155'),
            fontName='Helvetica', spaceAfter=4),
        'small': ParagraphStyle('small',
            fontSize=8, leading=11, textColor=GREY_TX,
            fontName='Helvetica'),
        'label': ParagraphStyle('label',
            fontSize=9, leading=12, textColor=DARK,
            fontName='Helvetica-Bold'),
        'center': ParagraphStyle('center',
            fontSize=9.5, leading=14, textColor=colors.HexColor('#334155'),
            fontName='Helvetica', alignment=TA_CENTER),
        'arch_name': ParagraphStyle('arch_name',
            fontSize=22, leading=28, textColor=WHITE,
            fontName='Helvetica-Bold', alignment=TA_CENTER),
        'arch_tag': ParagraphStyle('arch_tag',
            fontSize=12, leading=18, textColor=colors.HexColor('#C7D2FE'),
            fontName='Helvetica', alignment=TA_CENTER),
        'section_title': ParagraphStyle('section_title',
            fontSize=13, leading=18, textColor=DARK,
            fontName='Helvetica-Bold', spaceBefore=8, spaceAfter=6),
        'facette_label': ParagraphStyle('fl',
            fontSize=8.5, leading=11, textColor=DARK, fontName='Helvetica-Bold'),
        'facette_desc': ParagraphStyle('fd',
            fontSize=8, leading=11, textColor=GREY_TX, fontName='Helvetica'),
        'footer': ParagraphStyle('footer',
            fontSize=7.5, leading=10, textColor=GREY_TX,
            fontName='Helvetica', alignment=TA_CENTER),
    }

# ── Drawing helpers ───────────────────────────────────────────────────────────
def progress_bar(pct, color, bar_w=140, bar_h=8):
    d = Drawing(bar_w, bar_h + 2)
    d.add(Rect(0, 1, bar_w, bar_h, fillColor=GREY_BD, strokeColor=None,
               rx=bar_h/2, ry=bar_h/2))
    fill_w = max(4, bar_w * pct / 100)
    d.add(Rect(0, 1, fill_w, bar_h, fillColor=color, strokeColor=None,
               rx=bar_h/2, ry=bar_h/2))
    return d

def radar_chart(scores_dim, size=160):
    """Pentagone radar OCEAN."""
    cx, cy = size/2, size/2
    r_max = size*0.40
    dims  = ['O','C','E','A','N']
    angles = [math.radians(90 - i*72) for i in range(5)]
    d = Drawing(size, size)

    # Grilles concentriques
    for frac in [0.25, 0.5, 0.75, 1.0]:
        pts = []
        for a in angles:
            pts += [cx + r_max*frac*math.cos(a), cy + r_max*frac*math.sin(a)]
        poly_pts = []
        for i in range(0, len(pts), 2):
            poly_pts.append((pts[i], pts[i+1]))
        for i in range(len(poly_pts)):
            x1,y1 = poly_pts[i]; x2,y2 = poly_pts[(i+1)%len(poly_pts)]
            d.add(Line(x1,y1,x2,y2, strokeColor=GREY_BD, strokeWidth=0.5))

    # Axes
    for a in angles:
        d.add(Line(cx, cy, cx+r_max*math.cos(a), cy+r_max*math.sin(a),
                   strokeColor=GREY_BD, strokeWidth=0.5))

    # Polygone profil
    ppts = []
    for dim, a in zip(dims, angles):
        pct = scores_dim.get(dim, {}).get('pct', 50)
        r   = r_max * pct / 100
        ppts.append((cx + r*math.cos(a), cy + r*math.sin(a)))

    from reportlab.graphics.shapes import Polygon
    flat = [v for pt in ppts for v in pt]
    d.add(Polygon(flat, fillColor=colors.HexColor('#4F46E520'),
                  strokeColor=C1, strokeWidth=1.5))

    # Points + labels
    label_offset = 16
    for dim, a, (px,py) in zip(dims, angles, ppts):
        d.add(Circle(px, py, 3, fillColor=DIM_COLORS[dim], strokeColor=WHITE, strokeWidth=1))
        lx = cx + (r_max+label_offset)*math.cos(a)
        ly = cy + (r_max+label_offset)*math.sin(a)
        pct = scores_dim.get(dim, {}).get('pct', 50)
        d.add(String(lx, ly-4, f"{dim} {pct}%",
                     fontSize=7, fillColor=DIM_COLORS[dim],
                     textAnchor='middle', fontName='Helvetica-Bold'))
    return d

# ── Page callbacks (header/footer) ───────────────────────────────────────────
def make_page_callbacks(prenom, site_name, arch_nom, page_count_ref):
    def on_first_page(canvas, doc):
        pass  # Couverture gérée dans le flow

    def on_later_pages(canvas, doc):
        canvas.saveState()
        # En-tête léger
        canvas.setFillColor(C1)
        canvas.rect(0, H-12*mm, W, 12*mm, fill=1, stroke=0)
        canvas.setFillColor(WHITE)
        canvas.setFont('Helvetica-Bold', 8)
        canvas.drawString(MARGIN, H-7.5*mm, f"{prenom} — {arch_nom}")
        canvas.setFont('Helvetica', 8)
        canvas.drawRightString(W-MARGIN, H-7.5*mm, site_name)
        # Pied de page
        canvas.setFillColor(GREY_TX)
        canvas.setFont('Helvetica', 7)
        canvas.drawCentredString(W/2, 8*mm,
            f"Confidentiel — Rapport généré pour {prenom} — p. {doc.page}")
        canvas.setStrokeColor(GREY_BD)
        canvas.line(MARGIN, 14*mm, W-MARGIN, 14*mm)
        canvas.restoreState()

    return on_first_page, on_later_pages

# ── Couverture ────────────────────────────────────────────────────────────────
def build_cover(story, data, styles):
    arch = data.get('archetype', {})
    prenom = data.get('prenom', '')
    site   = data.get('site_name', '')
    date   = data.get('date', '')

    # Bloc gradient simulé avec table pleine largeur
    content_w = W - 2*MARGIN
    cover_tbl = Table([['']], colWidths=[content_w], rowHeights=[90*mm])
    cover_tbl.setStyle(TableStyle([
        ('BACKGROUND', (0,0),(0,0), C1),
        ('ROUNDEDCORNERS', [8]),
        ('TOPPADDING', (0,0),(0,0), 28*mm),
    ]))

    # Emoji + nom archétype
    emoji_str = arch.get('emoji','') + '  ' if arch.get('emoji') else ''
    story.append(Spacer(1, 8*mm))

    # Header card
    inner = [
        Paragraph(emoji_str + arch.get('nom', 'Votre Profil'), styles['arch_name']),
        Spacer(1, 3*mm),
        Paragraph(arch.get('tagline',''), styles['arch_tag']),
        Spacer(1, 5*mm),
        Paragraph(f"Le profil de <b>{prenom}</b>", styles['subtitle']),
    ]
    cover_inner = Table([[inner]], colWidths=[content_w], rowHeights=[85*mm])
    cover_inner.setStyle(TableStyle([
        ('BACKGROUND',   (0,0),(0,0), C1),
        ('ROUNDEDCORNERS',[8]),
        ('VALIGN',       (0,0),(0,0), 'MIDDLE'),
        ('ALIGN',        (0,0),(0,0), 'CENTER'),
        ('TOPPADDING',   (0,0),(0,0), 12*mm),
        ('BOTTOMPADDING',(0,0),(0,0), 12*mm),
    ]))
    story.append(cover_inner)
    story.append(Spacer(1, 6*mm))

    # Rareté
    rarete = arch.get('rarete','')
    if rarete:
        rar_tbl = Table([[
            Paragraph(f"✨  Profil présent chez seulement <b>{rarete}%</b> des personnes",
                      styles['center'])
        ]], colWidths=[content_w])
        rar_tbl.setStyle(TableStyle([
            ('BACKGROUND',   (0,0),(0,0), GREY_BG),
            ('ROUNDEDCORNERS',[6]),
            ('TOPPADDING',   (0,0),(0,0), 6),
            ('BOTTOMPADDING',(0,0),(0,0), 6),
            ('LEFTPADDING',  (0,0),(0,0), 10),
            ('BOX',          (0,0),(0,0), 0.5, GREY_BD),
        ]))
        story.append(rar_tbl)
        story.append(Spacer(1, 4*mm))

    # Traits clés
    traits = arch.get('traits', [])
    if traits:
        story.append(Paragraph("Traits caractéristiques", styles['h2']))
        trait_cells = [[Paragraph(f"• {t}", styles['body']) for t in traits]]
        trait_tbl = Table(trait_cells, colWidths=[content_w/len(traits)]*len(traits))
        trait_tbl.setStyle(TableStyle([
            ('VALIGN', (0,0),(-1,-1), 'TOP'),
            ('LEFTPADDING', (0,0),(-1,-1), 4),
        ]))
        story.append(trait_tbl)
        story.append(Spacer(1, 4*mm))

    # Description archétype
    desc = arch.get('description','')
    if desc:
        desc_tbl = Table([[Paragraph(desc, styles['body'])]],
                          colWidths=[content_w])
        desc_tbl.setStyle(TableStyle([
            ('BACKGROUND',   (0,0),(0,0), GREY_BG),
            ('ROUNDEDCORNERS',[6]),
            ('TOPPADDING',   (0,0),(0,0), 8),
            ('BOTTOMPADDING',(0,0),(0,0), 8),
            ('LEFTPADDING',  (0,0),(0,0), 10),
            ('RIGHTPADDING', (0,0),(0,0), 10),
            ('BOX',          (0,0),(0,0), 0.5, GREY_BD),
        ]))
        story.append(desc_tbl)
        story.append(Spacer(1, 4*mm))

    # Métadonnées
    meta_rows = [
        [Paragraph("<b>Rapport généré le</b>", styles['small']),
         Paragraph(date, styles['small'])],
        [Paragraph("<b>Site</b>", styles['small']),
         Paragraph(site, styles['small'])],
    ]
    meta_tbl = Table(meta_rows, colWidths=[40*mm, content_w-40*mm])
    meta_tbl.setStyle(TableStyle([('TOPPADDING',(0,0),(-1,-1),2),
                                   ('BOTTOMPADDING',(0,0),(-1,-1),2)]))
    story.append(meta_tbl)
    story.append(PageBreak())

# ── Page 2 : Vue d'ensemble OCEAN ────────────────────────────────────────────
def build_ocean_overview(story, data, styles):
    scores_dim     = data.get('scores_dim', {})
    scores_facette = data.get('scores_facette', {})
    facettes_map   = data.get('facettes_map', {})
    content_w      = W - 2*MARGIN

    story.append(Spacer(1, 4*mm))
    story.append(Paragraph("Vue d'ensemble — Les 5 dimensions", styles['section_title']))
    story.append(HRFlowable(width=content_w, thickness=2, color=C1, spaceAfter=6))

    # Radar + barres côte à côte
    radar = radar_chart(scores_dim, size=150)
    bar_rows = []
    for dim in ['O','C','E','A','N']:
        d = scores_dim.get(dim, {})
        pct   = d.get('pct', 50)
        label = d.get('label', '')
        col   = DIM_COLORS[dim]
        bar_rows.append([
            Paragraph(DIM_LABELS[dim], styles['label']),
            progress_bar(pct, col, bar_w=120, bar_h=9),
            Paragraph(f"<b>{pct}%</b>", styles['label']),
            Paragraph(label, styles['small']),
        ])

    bar_tbl = Table(bar_rows, colWidths=[55*mm, 35*mm, 12*mm, 25*mm],
                    rowHeights=[14]*5)
    bar_tbl.setStyle(TableStyle([
        ('VALIGN',       (0,0),(-1,-1), 'MIDDLE'),
        ('LEFTPADDING',  (0,0),(-1,-1), 2),
        ('RIGHTPADDING', (0,0),(-1,-1), 2),
        ('TOPPADDING',   (0,0),(-1,-1), 2),
        ('BOTTOMPADDING',(0,0),(-1,-1), 2),
        ('ROWBACKGROUNDS',(0,0),(-1,-1),[WHITE, GREY_BG]),
    ]))

    overview = Table([[radar, bar_tbl]],
                      colWidths=[55*mm, content_w-55*mm])
    overview.setStyle(TableStyle([
        ('VALIGN', (0,0),(-1,-1), 'MIDDLE'),
        ('LEFTPADDING', (0,0),(-1,-1), 0),
        ('RIGHTPADDING',(0,0),(0,0), 8),
    ]))
    story.append(overview)
    story.append(Spacer(1, 6*mm))

# ── Page 3-4 : Détail par dimension avec 6 facettes ─────────────────────────
def build_dim_detail(story, data, styles):
    scores_dim     = data.get('scores_dim', {})
    scores_facette = data.get('scores_facette', {})
    facettes_map   = data.get('facettes_map', {})
    content_w      = W - 2*MARGIN

    dim_order = ['O','C','E','A','N']
    dim_descs = {
        'O': "Mesure l'ouverture à de nouvelles expériences, la curiosité intellectuelle, la créativité et la sensibilité esthétique.",
        'C': "Reflète le niveau d'organisation, de fiabilité, de persévérance et d'autodiscipline dans l'atteinte des objectifs.",
        'E': "Évalue la sociabilité, l'assertivité, l'énergie dans les interactions sociales et la recherche de stimulations.",
        'A': "Traduit la tendance à la coopération, à l'empathie, à la confiance envers autrui et à l'évitement des conflits.",
        'N': "Indique la fréquence et l'intensité des émotions négatives et la vulnérabilité face au stress (score bas = stable).",
    }

    story.append(PageBreak())
    story.append(Spacer(1, 4*mm))
    story.append(Paragraph("Détail par dimension et par facette", styles['section_title']))
    story.append(HRFlowable(width=content_w, thickness=2, color=C1, spaceAfter=8))

    for dim in dim_order:
        d   = scores_dim.get(dim, {})
        pct = d.get('pct', 50)
        col = DIM_COLORS[dim]
        lbl = DIM_LABELS[dim]

        # En-tête dimension
        header_content = [
            Paragraph(f"{lbl}  —  {pct}%  ({d.get('label','')})",
                      ParagraphStyle('dh', fontSize=11, leading=16,
                                     textColor=WHITE, fontName='Helvetica-Bold')),
            Spacer(1,2),
            Paragraph(dim_descs[dim],
                      ParagraphStyle('dd', fontSize=8, leading=12,
                                     textColor=colors.HexColor('#C7D2FE'),
                                     fontName='Helvetica')),
        ]
        bar_d = progress_bar(pct, WHITE, bar_w=int(content_w*0.55), bar_h=7)
        dim_hdr = Table([[header_content, bar_d]],
                         colWidths=[content_w*0.62, content_w*0.38])
        dim_hdr.setStyle(TableStyle([
            ('BACKGROUND',   (0,0),(-1,-1), col),
            ('ROUNDEDCORNERS',[6]),
            ('TOPPADDING',   (0,0),(-1,-1), 8),
            ('BOTTOMPADDING',(0,0),(-1,-1), 8),
            ('LEFTPADDING',  (0,0),(0,0), 10),
            ('RIGHTPADDING', (0,0),(-1,-1), 10),
            ('VALIGN',       (0,0),(-1,-1), 'MIDDLE'),
        ]))
        story.append(KeepTogether([dim_hdr, Spacer(1, 3*mm)]))

        # Grille 6 facettes (2 colonnes × 3 lignes)
        fac_keys = [k for k,v in facettes_map.items() if v.get('dim')==dim]
        fac_cells = []
        for i in range(0, len(fac_keys), 2):
            row = []
            for j in range(2):
                if i+j < len(fac_keys):
                    fk   = fac_keys[i+j]
                    fs   = scores_facette.get(fk, {})
                    fpct = fs.get('pct', 50)
                    fmap = facettes_map.get(fk, {})
                    cell = [
                        Paragraph(fmap.get('label', fk), styles['facette_label']),
                        progress_bar(fpct, col, bar_w=90, bar_h=6),
                        Paragraph(f"{fpct}% — {fs.get('label','')}",
                                  styles['facette_desc']),
                        Paragraph(fmap.get('desc',''), styles['facette_desc']),
                        Spacer(1, 1*mm),
                    ]
                    row.append(cell)
                else:
                    row.append('')
            fac_cells.append(row)

        half = (content_w - 4*mm) / 2
        fac_tbl = Table(fac_cells, colWidths=[half, half], rowHeights=None)
        fac_tbl.setStyle(TableStyle([
            ('VALIGN',       (0,0),(-1,-1), 'TOP'),
            ('TOPPADDING',   (0,0),(-1,-1), 4),
            ('BOTTOMPADDING',(0,0),(-1,-1), 4),
            ('LEFTPADDING',  (0,0),(-1,-1), 6),
            ('RIGHTPADDING', (0,0),(-1,-1), 6),
            ('COLBACKGROUNDS',(0,0),(-1,-1),[WHITE, GREY_BG]),
            ('BOX',          (0,0),(-1,-1), 0.5, GREY_BD),
            ('INNERGRID',    (0,0),(-1,-1), 0.3, GREY_BD),
        ]))
        story.append(KeepTogether([fac_tbl, Spacer(1, 5*mm)]))

# ── Page 5 : Pistes de développement ────────────────────────────────────────
def build_recommandations(story, data, styles):
    scores_dim = data.get('scores_dim', {})
    content_w  = W - 2*MARGIN
    prenom     = data.get('prenom', '')

    story.append(PageBreak())
    story.append(Spacer(1, 4*mm))
    story.append(Paragraph("Pistes de développement", styles['section_title']))
    story.append(HRFlowable(width=content_w, thickness=2, color=C1, spaceAfter=8))

    reco_map = {
        'O': {
            'bas':  ("Explorateur en devenir",
                     "Votre ancrage dans le concret est une force. Pour élargir votre palette : testez une pratique créative pendant 30 jours, lisez un livre dans un domaine que vous ne connaissez pas, ou assistez à un événement culturel hors de vos habitudes."),
            'haut': ("Canalisez votre curiosité",
                     "Votre richesse imaginative peut parfois disperser votre énergie. Définissez 2-3 projets prioritaires par trimestre. Pratiquez la finalisation avant de lancer de nouvelles idées."),
        },
        'C': {
            'bas':  ("Structurer sans se contraindre",
                     "Un peu plus de structure libère de l'énergie mentale. Essayez une to-do list quotidienne de 3 priorités maximum, ou un ritual de clôture de journée."),
            'haut': ("Lâcher prise sur le contrôle",
                     "Votre fiabilité est reconnue. Veillez à ne pas transformer l'exigence en perfectionnisme paralysant. Pratiquez la délégation et acceptez l'imperfection productive."),
        },
        'E': {
            'bas':  ("Énergie introvertie",
                     "Votre profondeur de réflexion est un atout rare. Pour développer votre impact : préparez vos interventions en réunion, cherchez des formats 1-1 plutôt que de groupe, et valorisez votre écoute comme compétence de leadership."),
            'haut': ("Écoute et présence",
                     "Votre énergie est contagieuse. Travaillez l'écoute active : avant de répondre, reformulez ce que l'autre a dit. Donnez de l'espace aux personnes plus réservées dans vos échanges."),
        },
        'A': {
            'bas':  ("Diplomatie stratégique",
                     "Votre franchise est un atout dans les environnements qui valorisent la clarté. Explorez les techniques de communication non-violente pour maintenir votre direct tout en préservant la relation."),
            'haut': ("Affirmation de soi",
                     "Votre coopérativité crée de la confiance. Veillez à exprimer vos désaccords et besoins — dire non est aussi une compétence relationnelle. Pratiquez l'assertivité : exprimer clairement, sans agressivité."),
        },
        'N': {
            'haut': ("Régulation émotionnelle",
                     "Votre sensibilité est une source de profondeur. Pour gagner en stabilité : identifiez vos déclencheurs de stress, pratiquez une technique de régulation (cohérence cardiaque, journaling, sport régulier) et apprenez à différencier urgence réelle et alarme interne."),
            'bas':  ("Résilience acquise",
                     "Votre stabilité émotionnelle vous permet de performer sous pression. Veillez à rester attentif(ve) aux signaux émotionnels des autres, qui peuvent vous sembler disproportionnés mais sont réels pour eux."),
        },
    }

    rows = []
    for dim in ['O','C','E','A','N']:
        pct  = scores_dim.get(dim, {}).get('pct', 50)
        side = 'haut' if pct >= 50 else 'bas'
        rec  = reco_map[dim][side]
        col  = DIM_COLORS[dim]
        rows.append([
            Table([[Paragraph(DIM_LABELS[dim], ParagraphStyle('dlt',
                       fontSize=8, leading=10, textColor=WHITE,
                       fontName='Helvetica-Bold', alignment=TA_CENTER))]],
                   colWidths=[18*mm], rowHeights=[12*mm],
                   style=[('BACKGROUND',(0,0),(0,0),col),
                           ('ROUNDEDCORNERS',[4]),
                           ('VALIGN',(0,0),(0,0),'MIDDLE')]),
            [Paragraph(rec[0], styles['h2']),
             Paragraph(rec[1], styles['body'])],
        ])

    reco_tbl = Table(rows, colWidths=[22*mm, content_w-22*mm])
    reco_tbl.setStyle(TableStyle([
        ('VALIGN',       (0,0),(-1,-1), 'TOP'),
        ('TOPPADDING',   (0,0),(-1,-1), 6),
        ('BOTTOMPADDING',(0,0),(-1,-1), 6),
        ('LEFTPADDING',  (0,0),(-1,-1), 6),
        ('ROWBACKGROUNDS',(0,0),(-1,-1),[WHITE, GREY_BG]),
        ('LINEBELOW',    (0,0),(-1,-2), 0.3, GREY_BD),
    ]))
    story.append(reco_tbl)

# ── Page finale : CTA ────────────────────────────────────────────────────────
def build_cta(story, data, styles):
    content_w = W - 2*MARGIN
    rdv_url   = data.get('rdv_url', '')
    site      = data.get('site_name', '')
    profil_url= data.get('profil_url', '')

    story.append(PageBreak())
    story.append(Spacer(1, 20*mm))

    cta_inner = [
        Spacer(1, 6*mm),
        Paragraph("Prochaine étape", styles['arch_name']),
        Spacer(1, 3*mm),
        Paragraph("Ce rapport est un point de départ, pas une étiquette.",
                  styles['subtitle']),
        Spacer(1, 4*mm),
        Paragraph(
            "Un entretien de débriefing vous permet de comprendre comment vos résultats "
            "s'appliquent concrètement à votre contexte professionnel — "
            "style de management, collaboration, prise de décision sous pression.",
            ParagraphStyle('ctabody', fontSize=10, leading=15,
                           textColor=colors.HexColor('#C7D2FE'),
                           fontName='Helvetica', alignment=TA_CENTER)),
        Spacer(1, 6*mm),
        Paragraph(f"→ {rdv_url}", ParagraphStyle('ctalink',
                   fontSize=11, leading=16, textColor=WHITE,
                   fontName='Helvetica-Bold', alignment=TA_CENTER)),
        Spacer(1, 4*mm),
        Paragraph(f"Profil en ligne : {profil_url}", ParagraphStyle('ctasmall',
                   fontSize=8, leading=12,
                   textColor=colors.HexColor('#C7D2FE'),
                   fontName='Helvetica', alignment=TA_CENTER)),
        Spacer(1, 6*mm),
    ]
    cta_tbl = Table([[cta_inner]], colWidths=[content_w])
    cta_tbl.setStyle(TableStyle([
        ('BACKGROUND',   (0,0),(0,0), C1),
        ('ROUNDEDCORNERS',[10]),
        ('TOPPADDING',   (0,0),(0,0), 2),
        ('BOTTOMPADDING',(0,0),(0,0), 2),
        ('LEFTPADDING',  (0,0),(0,0), 20),
        ('RIGHTPADDING', (0,0),(0,0), 20),
    ]))
    story.append(cta_tbl)
    story.append(Spacer(1, 6*mm))
    story.append(Paragraph(
        f"Document confidentiel — généré par {site} — ne pas diffuser sans accord.",
        styles['footer']))

# ── MAIN ──────────────────────────────────────────────────────────────────────
def generate(json_path, output_path):
    with open(json_path, 'r', encoding='utf-8') as f:
        data = json.load(f)

    styles = make_styles()
    prenom   = data.get('prenom', 'Profil')
    arch_nom = data.get('archetype', {}).get('nom', 'Votre profil')
    site     = data.get('site_name', '')

    _, on_later = make_page_callbacks(prenom, site, arch_nom, [])

    doc = SimpleDocTemplate(
        output_path,
        pagesize=A4,
        leftMargin=MARGIN, rightMargin=MARGIN,
        topMargin=MARGIN,  bottomMargin=18*mm,
        title=f"Profil de personnalité — {prenom}",
        author=site,
        subject=f"Big Five — {arch_nom}",
    )

    story = []
    build_cover(story, data, styles)
    build_ocean_overview(story, data, styles)
    build_dim_detail(story, data, styles)
    build_recommandations(story, data, styles)
    build_cta(story, data, styles)

    doc.build(story, onFirstPage=on_later, onLaterPages=on_later)
    print(f"OK:{output_path}")

if __name__ == '__main__':
    if len(sys.argv) < 3:
        print("Usage: generate_rapport.py <data.json> <output.pdf>", file=sys.stderr)
        sys.exit(1)
    generate(sys.argv[1], sys.argv[2])
