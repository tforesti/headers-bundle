<?php

declare(strict_types=1);

namespace Batch\HeadersBundle\EventListener;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

/**
 * Adds the configured headers to the matching responses.
 */
final class ResponseListener
{
    /** @var ExpressionLanguage */
    private $expressionLanguage;

    /** @var array */
    private $headers;

    public function __construct(ExpressionLanguage $expressionLanguage, array $headers)
    {
        $this->expressionLanguage = $expressionLanguage;
        $this->headers = $headers;
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $response = $event->getResponse();
        $evaluationValues = [
            'request' => $event->getRequest(),
            'response' => $event->getResponse(),
        ];

        foreach ($this->headers as $header) {
            if (isset($header['condition'])) {
                if ($this->expressionLanguage->evaluate($header['condition'], $evaluationValues) !== true) {
                    continue;
                }
            }

            $response->headers->set($header['name'], $header['value'], $header['replace'] ?? true);
        }
    }
}
