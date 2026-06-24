<?php

declare(strict_types=1);

namespace Jack009\ComingSoonBundle\Tests\EventListener;

use Jack009\ComingSoonBundle\EventListener\ComingSoonListener;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Twig\Environment;

class ComingSoonListenerTest extends TestCase
{
    private Environment&MockObject $twig;
    private HttpKernelInterface&MockObject $kernel;

    protected function setUp(): void
    {
        $this->twig = $this->createMock(Environment::class);
        $this->kernel = $this->createMock(HttpKernelInterface::class);
    }

    private function createListener(
        bool $enabled = true,
        string $template = '@ComingSoon/coming_soon.html.twig',
        int $statusCode = 503,
        array $whitelistedIps = [],
        array $excludedRoutes = [],
        array $excludedPaths = [],
    ): ComingSoonListener {
        return new ComingSoonListener(
            $this->twig,
            $enabled,
            $template,
            $statusCode,
            $whitelistedIps,
            $excludedRoutes,
            $excludedPaths,
        );
    }

    private function createEvent(Request $request, bool $isMainRequest = true): RequestEvent
    {
        $requestType = $isMainRequest ? HttpKernelInterface::MAIN_REQUEST : HttpKernelInterface::SUB_REQUEST;

        return new RequestEvent($this->kernel, $request, $requestType);
    }

    public function testDoesNothingWhenDisabled(): void
    {
        $listener = $this->createListener(enabled: false);
        $request = Request::create('/');
        $event = $this->createEvent($request);

        $this->twig->expects($this->never())->method('render');

        $listener->onKernelRequest($event);

        $this->assertFalse($event->hasResponse());
    }

    public function testSetsResponseWhenEnabled(): void
    {
        $listener = $this->createListener(enabled: true);
        $request = Request::create('/');
        $event = $this->createEvent($request);

        $this->twig
            ->expects($this->once())
            ->method('render')
            ->with('@ComingSoon/coming_soon.html.twig', ['status_code' => 503])
            ->willReturn('<html>Coming Soon</html>');

        $listener->onKernelRequest($event);

        $this->assertTrue($event->hasResponse());
        $this->assertSame(503, $event->getResponse()->getStatusCode());
        $this->assertSame('<html>Coming Soon</html>', $event->getResponse()->getContent());
        $this->assertSame('3600', $event->getResponse()->headers->get('Retry-After'));
    }

    public function testDoesNothingForSubRequests(): void
    {
        $listener = $this->createListener(enabled: true);
        $request = Request::create('/');
        $event = $this->createEvent($request, isMainRequest: false);

        $this->twig->expects($this->never())->method('render');

        $listener->onKernelRequest($event);

        $this->assertFalse($event->hasResponse());
    }

    public function testWhitelistedIpBypassesComingSoon(): void
    {
        $listener = $this->createListener(
            enabled: true,
            whitelistedIps: ['192.168.1.1'],
        );

        $request = Request::create('/');
        $request->server->set('REMOTE_ADDR', '192.168.1.1');
        $event = $this->createEvent($request);

        $this->twig->expects($this->never())->method('render');

        $listener->onKernelRequest($event);

        $this->assertFalse($event->hasResponse());
    }

    public function testNonWhitelistedIpSeesComingSoon(): void
    {
        $listener = $this->createListener(
            enabled: true,
            whitelistedIps: ['192.168.1.1'],
        );

        $request = Request::create('/');
        $request->server->set('REMOTE_ADDR', '10.0.0.1');
        $event = $this->createEvent($request);

        $this->twig->expects($this->once())->method('render')->willReturn('<html>Coming Soon</html>');

        $listener->onKernelRequest($event);

        $this->assertTrue($event->hasResponse());
    }

    public function testExcludedRouteBypassesComingSoon(): void
    {
        $listener = $this->createListener(
            enabled: true,
            excludedRoutes: ['app_health'],
        );

        $request = Request::create('/health');
        $request->attributes->set('_route', 'app_health');
        $event = $this->createEvent($request);

        $this->twig->expects($this->never())->method('render');

        $listener->onKernelRequest($event);

        $this->assertFalse($event->hasResponse());
    }

    public function testNonExcludedRouteSeesComingSoon(): void
    {
        $listener = $this->createListener(
            enabled: true,
            excludedRoutes: ['app_health'],
        );

        $request = Request::create('/about');
        $request->attributes->set('_route', 'app_about');
        $event = $this->createEvent($request);

        $this->twig->expects($this->once())->method('render')->willReturn('<html>Coming Soon</html>');

        $listener->onKernelRequest($event);

        $this->assertTrue($event->hasResponse());
    }

    public function testExcludedPathBypassesComingSoon(): void
    {
        $listener = $this->createListener(
            enabled: true,
            excludedPaths: ['/admin'],
        );

        $request = Request::create('/admin/dashboard');
        $event = $this->createEvent($request);

        $this->twig->expects($this->never())->method('render');

        $listener->onKernelRequest($event);

        $this->assertFalse($event->hasResponse());
    }

    public function testNonExcludedPathSeesComingSoon(): void
    {
        $listener = $this->createListener(
            enabled: true,
            excludedPaths: ['/admin'],
        );

        $request = Request::create('/shop');
        $event = $this->createEvent($request);

        $this->twig->expects($this->once())->method('render')->willReturn('<html>Coming Soon</html>');

        $listener->onKernelRequest($event);

        $this->assertTrue($event->hasResponse());
    }

    public function testCustomStatusCode(): void
    {
        $listener = $this->createListener(enabled: true, statusCode: 200);
        $request = Request::create('/');
        $event = $this->createEvent($request);

        $this->twig
            ->expects($this->once())
            ->method('render')
            ->with('@ComingSoon/coming_soon.html.twig', ['status_code' => 200])
            ->willReturn('<html>Coming Soon</html>');

        $listener->onKernelRequest($event);

        $this->assertSame(200, $event->getResponse()->getStatusCode());
    }

    public function testCustomTemplate(): void
    {
        $listener = $this->createListener(
            enabled: true,
            template: '@MyBundle/custom.html.twig',
        );
        $request = Request::create('/');
        $event = $this->createEvent($request);

        $this->twig
            ->expects($this->once())
            ->method('render')
            ->with('@MyBundle/custom.html.twig', ['status_code' => 503])
            ->willReturn('<html>Custom</html>');

        $listener->onKernelRequest($event);

        $this->assertTrue($event->hasResponse());
    }
}
