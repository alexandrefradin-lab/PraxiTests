<?php

namespace Praxis\Plugins\PraxiValeurs\Data;

class Values
{
    /** 40 questions Likert 1-6, dimensions Schwartz. */
    public static function questions(): array
    {
        return [
            ['id'=>1,  'texte'=>"Indépendance dans mes choix",                    'dim'=>'autonomie'],
            ['id'=>2,  'texte'=>"Entraide et soutien aux autres",                 'dim'=>'bienveillance'],
            ['id'=>3,  'texte'=>"Stabilité et sécurité dans ma vie",              'dim'=>'securite'],
            ['id'=>4,  'texte'=>"Ambition et désir de réussir",                   'dim'=>'reussite'],
            ['id'=>5,  'texte'=>"Égalité et justice pour tous",                   'dim'=>'universalisme'],
            ['id'=>6,  'texte'=>"Plaisir et épanouissement personnel",            'dim'=>'hedonisme'],
            ['id'=>7,  'texte'=>"Leadership et capacité à influencer",            'dim'=>'pouvoir'],
            ['id'=>8,  'texte'=>"Respect des cadres et règles établis",           'dim'=>'conformite'],
            ['id'=>9,  'texte'=>"Aventure et goût du risque",                     'dim'=>'stimulation'],
            ['id'=>10, 'texte'=>"Fidélité aux traditions familiales",             'dim'=>'tradition'],
            ['id'=>11, 'texte'=>"Créativité et originalité",                      'dim'=>'autonomie'],
            ['id'=>12, 'texte'=>"Honnêteté et intégrité",                         'dim'=>'bienveillance'],
            ['id'=>13, 'texte'=>"Fiabilité et prévisibilité",                     'dim'=>'securite'],
            ['id'=>14, 'texte'=>"Performance et excellence",                      'dim'=>'reussite'],
            ['id'=>15, 'texte'=>"Être reconnu et estimé dans son domaine",        'dim'=>'pouvoir'],
            ['id'=>16, 'texte'=>"Tolérance et ouverture aux différences",         'dim'=>'universalisme'],
            ['id'=>17, 'texte'=>"Bien-être et confort de vie",                    'dim'=>'hedonisme'],
            ['id'=>18, 'texte'=>"Discipline et maîtrise de soi",                  'dim'=>'conformite'],
            ['id'=>19, 'texte'=>"Curiosité et soif d'apprendre",                  'dim'=>'autonomie'],
            ['id'=>20, 'texte'=>"Innovation et changement",                       'dim'=>'stimulation'],
            ['id'=>21, 'texte'=>"Protection de l'environnement",                  'dim'=>'universalisme'],
            ['id'=>22, 'texte'=>"Loyauté envers mes proches",                     'dim'=>'bienveillance'],
            ['id'=>23, 'texte'=>"Prudence et anticipation des risques",           'dim'=>'securite'],
            ['id'=>24, 'texte'=>"Efficacité et résultats concrets",               'dim'=>'reussite'],
            ['id'=>25, 'texte'=>"Influence et impact sur mon entourage",          'dim'=>'pouvoir'],
            ['id'=>26, 'texte'=>"Joie de vivre au quotidien",                     'dim'=>'hedonisme'],
            ['id'=>27, 'texte'=>"Discrétion et modestie dans mon rôle",           'dim'=>'tradition'],
            ['id'=>28, 'texte'=>"Variété et nouveauté dans mes activités",        'dim'=>'stimulation'],
            ['id'=>29, 'texte'=>"Générosité et don de soi",                       'dim'=>'bienveillance'],
            ['id'=>30, 'texte'=>"Liberté de penser et d'agir",                    'dim'=>'autonomie'],
            ['id'=>31, 'texte'=>"Modération et équilibre de vie",                 'dim'=>'tradition'],
            ['id'=>32, 'texte'=>"Dépasser mes limites et progresser",             'dim'=>'reussite'],
            ['id'=>33, 'texte'=>"Ordre et organisation",                          'dim'=>'conformite'],
            ['id'=>34, 'texte'=>"Prise de risque calculée",                       'dim'=>'stimulation'],
            ['id'=>35, 'texte'=>"Sécurité financière et matérielle",              'dim'=>'securite'],
            ['id'=>36, 'texte'=>"Prestige et statut social",                      'dim'=>'pouvoir'],
            ['id'=>37, 'texte'=>"Épanouissement et réalisation de soi",           'dim'=>'hedonisme'],
            ['id'=>38, 'texte'=>"Respect des engagements",                        'dim'=>'conformite'],
            ['id'=>39, 'texte'=>"Attachement à mes racines",                      'dim'=>'tradition'],
            ['id'=>40, 'texte'=>"Sens du collectif et du bien commun",            'dim'=>'universalisme'],
        ];
    }

    public static function dimensions(): array
    {
        return [
            'autonomie'     => ['label'=>'Autonomie',     'color'=>'#3B4F8C', 'court'=>'Indépendance, liberté de choisir, créativité',          'definition'=>"Besoin de penser et d'agir de façon indépendante. Valorise la créativité, l'exploration intellectuelle et la liberté de fixer ses propres buts."],
            'stimulation'   => ['label'=>'Stimulation',   'color'=>'#E8A838', 'court'=>'Nouveauté, défis, goût du changement',                  'definition'=>"Besoin d'excitation, de nouveauté et de challenge. Apprécie la variété, le risque maîtrisé et les environnements qui évoluent rapidement."],
            'hedonisme'     => ['label'=>'Hédonisme',     'color'=>'#6B8F71', 'court'=>'Plaisir, bien-être, qualité de vie',                    'definition'=>"Recherche du plaisir et de la gratification sensuelle. Accorde de l'importance au confort, à la joie de vivre et à l'épanouissement au quotidien."],
            'reussite'      => ['label'=>'Réussite',      'color'=>'#C0392B', 'court'=>'Performance, excellence, ambition',                     'definition'=>"Désir de démontrer sa compétence et d'obtenir des résultats reconnus. Se dépasser, atteindre des objectifs ambitieux et être valorisé pour ses performances."],
            'pouvoir'       => ['label'=>'Pouvoir',       'color'=>'#8E44AD', 'court'=>'Influence, leadership, reconnaissance',                 'definition'=>"Aspiration à exercer une influence sur les autres et à détenir un statut social élevé. Valorise le prestige, le leadership et la capacité à orienter les décisions."],
            'securite'      => ['label'=>'Sécurité',      'color'=>'#2980B9', 'court'=>'Stabilité, ordre, protection',                          'definition'=>"Besoin de sécurité, d'harmonie et de stabilité dans sa vie et ses relations. Valorise la prévisibilité, la protection et les environnements organisés et fiables."],
            'conformite'    => ['label'=>'Conformité',    'color'=>'#16A085', 'court'=>'Respect des règles, discipline, fiabilité',             'definition'=>"Volonté de respecter les normes, les règles et les attentes sociales. Valorise la discipline, la maîtrise de soi et le respect des engagements pris envers les autres."],
            'tradition'     => ['label'=>'Tradition',     'color'=>'#795548', 'court'=>'Fidélité, continuité, attachement aux racines',         'definition'=>"Respect et attachement aux coutumes, à la culture et aux traditions familiales ou religieuses. Valorise la continuité, la modération et la fidélité à ses racines."],
            'bienveillance' => ['label'=>'Bienveillance', 'color'=>'#1ABC9C', 'court'=>'Soin des autres, honnêteté, entraide',                  'definition'=>"Préserver et renforcer le bien-être des personnes proches. Valorise l'honnêteté, la loyauté, l'entraide et la générosité dans les relations du quotidien."],
            'universalisme' => ['label'=>'Universalisme', 'color'=>'#27AE60', 'court'=>'Justice, égalité, utilité sociale',                     'definition'=>"Compréhension, tolérance et protection du bien-être de tous et de la nature. Valorise la justice sociale, l'égalité, la paix et la protection de l'environnement."],
        ];
    }
}
