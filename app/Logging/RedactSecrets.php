<?php

namespace App\Logging;

use Illuminate\Log\Logger;
use Monolog\Handler\FormattableHandlerInterface;

/**
 * Masque les clés d'API dans les logs.
 *
 * Les appels aux API (YouTube, Last.fm, Fanart...) passent leur clé dans l'URL. Quand un
 * file_get_contents échoue (429, timeout...), l'erreur contient l'URL complète, clé comprise.
 * On masque donc le texte final écrit par chaque handler : message, exception et trace.
 *
 * Utilisé via 'tap' => [RedactSecrets::class] dans config/logging.php.
 */
class RedactSecrets
{
    public function __invoke(Logger $logger): void
    {
        foreach ($logger->getLogger()->getHandlers() as $handler) {
            if ($handler instanceof FormattableHandlerInterface) {
                $handler->setFormatter(new RedactingFormatter($handler->getFormatter()));
            }
        }
    }

    /**
     * Remplace la valeur des paramètres sensibles (key, api_key, token...) par ***.
     */
    public static function redact(string $text): string
    {
        return preg_replace(
            '/([?&](?:amp;)?(?:key|api_key|apikey|access_token|token|client_secret|secret|password)=)[^&\s"\'<>)]+/i',
            '$1***',
            $text
        ) ?? $text;
    }
}
