<?php

declare(strict_types=1);

namespace Jack009\ComingSoonBundle\Tests\DependencyInjection;

use Jack009\ComingSoonBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends TestCase
{
    private Processor $processor;
    private Configuration $configuration;

    protected function setUp(): void
    {
        $this->processor = new Processor();
        $this->configuration = new Configuration();
    }

    public function testDefaultConfiguration(): void
    {
        $config = $this->processor->processConfiguration($this->configuration, []);

        $this->assertFalse($config['enabled']);
        $this->assertSame('@ComingSoon/coming_soon.html.twig', $config['template']);
        $this->assertSame(503, $config['status_code']);
        $this->assertSame([], $config['whitelisted_ips']);
        $this->assertSame([], $config['excluded_routes']);
        $this->assertSame([], $config['excluded_paths']);
    }

    public function testEnabledConfiguration(): void
    {
        $config = $this->processor->processConfiguration($this->configuration, [
            ['enabled' => true],
        ]);

        $this->assertTrue($config['enabled']);
    }

    public function testCustomTemplate(): void
    {
        $config = $this->processor->processConfiguration($this->configuration, [
            ['template' => '@MyBundle/custom_coming_soon.html.twig'],
        ]);

        $this->assertSame('@MyBundle/custom_coming_soon.html.twig', $config['template']);
    }

    public function testCustomStatusCode(): void
    {
        $config = $this->processor->processConfiguration($this->configuration, [
            ['status_code' => 200],
        ]);

        $this->assertSame(200, $config['status_code']);
    }

    public function testWhitelistedIps(): void
    {
        $config = $this->processor->processConfiguration($this->configuration, [
            ['whitelisted_ips' => ['127.0.0.1', '192.168.1.100']],
        ]);

        $this->assertSame(['127.0.0.1', '192.168.1.100'], $config['whitelisted_ips']);
    }

    public function testExcludedRoutes(): void
    {
        $config = $this->processor->processConfiguration($this->configuration, [
            ['excluded_routes' => ['app_health', 'app_admin']],
        ]);

        $this->assertSame(['app_health', 'app_admin'], $config['excluded_routes']);
    }

    public function testExcludedPaths(): void
    {
        $config = $this->processor->processConfiguration($this->configuration, [
            ['excluded_paths' => ['/admin', '/api']],
        ]);

        $this->assertSame(['/admin', '/api'], $config['excluded_paths']);
    }

    public function testFullConfiguration(): void
    {
        $config = $this->processor->processConfiguration($this->configuration, [
            [
                'enabled'         => true,
                'template'        => '@ComingSoon/coming_soon.html.twig',
                'status_code'     => 503,
                'whitelisted_ips' => ['127.0.0.1'],
                'excluded_routes' => ['app_health'],
                'excluded_paths'  => ['/admin'],
            ],
        ]);

        $this->assertTrue($config['enabled']);
        $this->assertSame('@ComingSoon/coming_soon.html.twig', $config['template']);
        $this->assertSame(503, $config['status_code']);
        $this->assertSame(['127.0.0.1'], $config['whitelisted_ips']);
        $this->assertSame(['app_health'], $config['excluded_routes']);
        $this->assertSame(['/admin'], $config['excluded_paths']);
    }
}
