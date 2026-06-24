<?php

declare(strict_types=1);

namespace Jack009\ComingSoonBundle\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Twig\Environment;

/**
 * Listens to every incoming request and, when the Coming Soon mode is enabled,
 * renders the configured template and returns it as a Response — unless the
 * request matches a whitelisted IP, an excluded route, or an excluded path.
 */
class ComingSoonListener
{
    public function __construct(
        private readonly Environment $twig,
        private readonly bool $enabled,
        private readonly string $template,
        private readonly int $statusCode,
        private readonly array $whitelistedIps,
        private readonly array $excludedRoutes,
        private readonly array $excludedPaths,
        private readonly bool $debug = false,
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        if (!$this->enabled) {
            return;
        }

        $request = $event->getRequest();

        // Allow whitelisted IPs to pass through.
        // getClientIp() respects Symfony's trusted-proxy configuration, so
        // ensure your application's trusted_proxies setting is correct when
        // running behind a load balancer or reverse proxy.
        if ($this->isIpWhitelisted($request->getClientIp())) {
            return;
        }

        // Allow excluded routes to pass through.
        $currentRoute = $request->attributes->get('_route');
        if ($currentRoute !== null && in_array($currentRoute, $this->excludedRoutes, true)) {
            return;
        }

        // Allow excluded path prefixes to pass through.
        $pathInfo = $request->getPathInfo();
        foreach ($this->excludedPaths as $excludedPath) {
            if (str_starts_with($pathInfo, $excludedPath)) {
                return;
            }
        }

        // In debug mode allow the Symfony web debug toolbar and profiler through.
        if ($this->debug) {
            foreach (['/_wdt', '/_profiler'] as $debugPath) {
                if (str_starts_with($pathInfo, $debugPath)) {
                    return;
                }
            }
        }

        $content = $this->twig->render($this->template, [
            'status_code' => $this->statusCode,
        ]);

        $response = new Response($content, $this->statusCode);
        $response->headers->set('Retry-After', '3600');

        $event->setResponse($response);
    }

    private function isIpWhitelisted(?string $ip): bool
    {
        if ($ip === null || $this->whitelistedIps === []) {
            return false;
        }

        return in_array($ip, $this->whitelistedIps, true);
    }
}
