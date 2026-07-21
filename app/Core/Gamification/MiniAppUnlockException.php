<?php

namespace Praxis\Core\Gamification;

/**
 * Refus de déblocage d'une mini-app. Le message est destiné au candidat
 * (flash 'error'), déjà accordé au parcours visuel (medieval/corporate).
 */
class MiniAppUnlockException extends \RuntimeException
{
}
