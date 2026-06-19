<?php

namespace Praxis\Core\Mailing;

/**
 * SEC-07 — Assainissement HTML léger pour les emails de campagne.
 *
 * N'utilise pas HTMLPurifier (dépendance externe) mais applique
 * une approche DOM-safe :
 *  1. strip_tags() pour ne garder que les balises autorisées
 *  2. Suppression via DOMDocument de tous les attributs dangereux
 *     (on*, style, src sur non-images, href avec javascript:, data:)
 *
 * Suffisant pour le contexte emails admin/pro où l'entrée est déjà restreinte.
 * Pour un risque plus élevé (UGC public), préférer ezyang/htmlpurifier.
 */
class HtmlSanitizer
{
    /** Balises conservées après strip_tags() */
    private const ALLOWED_TAGS = '<p><br><a><b><i><strong><em><ul><ol><li><h2><h3><blockquote><span>';

    /** Attributs autorisés par balise (liste blanche stricte) */
    private const ALLOWED_ATTRS = [
        'a'          => ['href', 'title', 'target'],
        'blockquote' => ['cite'],
        '*'          => [],   // toutes autres balises : aucun attribut
    ];

    /**
     * Assainit un fragment HTML pour usage dans un email de campagne.
     */
    public static function clean(string $html): string
    {
        if ($html === '') {
            return '';
        }

        // 1. Retirer toutes les balises non autorisées
        $html = strip_tags($html, self::ALLOWED_TAGS);

        // 2. Passer par DOMDocument pour nettoyer les attributs
        $doc = new \DOMDocument('1.0', 'UTF-8');
        // Éviter que DOMDocument ajoute un DOCTYPE et enveloppe dans <html><body>
        $wrapped = '<div>' . $html . '</div>';

        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8">' . $wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $xpath = new \DOMXPath($doc);

        // Supprimer tous les attributs non autorisés sur chaque élément
        foreach ($xpath->query('//*') as $node) {
            /** @var \DOMElement $node */
            $tag = strtolower($node->nodeName);

            $allowed = self::ALLOWED_ATTRS[$tag] ?? self::ALLOWED_ATTRS['*'];

            // Collecter les attributs à supprimer (ne pas modifier pendant l'itération)
            $toRemove = [];
            foreach ($node->attributes as $attr) {
                $name = strtolower($attr->name);
                if (!in_array($name, $allowed, true)) {
                    $toRemove[] = $name;
                }
            }
            foreach ($toRemove as $name) {
                $node->removeAttribute($name);
            }

            // Validation supplémentaire pour href : interdire javascript: et data:
            if ($node->hasAttribute('href')) {
                $href = trim($node->getAttribute('href'));
                if (preg_match('/^(javascript|data|vbscript)\s*:/i', $href)) {
                    $node->removeAttribute('href');
                }
            }

            // Forcer target="_blank" + rel="noopener noreferrer" sur les liens externes
            if ($tag === 'a' && $node->hasAttribute('href')) {
                $node->setAttribute('target', '_blank');
                $node->setAttribute('rel', 'noopener noreferrer');
            }
        }

        // Extraire le contenu du <div> wrapper en évitant les balises HTML/body ajoutées
        $body = $doc->getElementsByTagName('body')->item(0);
        if ($body && $body->firstChild) {
            // Premier enfant est notre <div> wrapper
            $inner = '';
            foreach ($body->firstChild->childNodes as $child) {
                $inner .= $doc->saveHTML($child);
            }
            return $inner;
        }

        // Fallback : retourner le HTML avec juste les balises strippées
        return strip_tags($html, self::ALLOWED_TAGS);
    }
}
