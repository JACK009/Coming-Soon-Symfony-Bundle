<?php

declare(strict_types=1);

namespace Jack009\ComingSoonBundle;

use Jack009\ComingSoonBundle\DependencyInjection\ComingSoonExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * ComingSoonBundle - A Symfony bundle to display a "Coming Soon" page.
 *
 * Enable or disable the coming soon page via configuration:
 *
 *   coming_soon:
 *       enabled: true
 */
class ComingSoonBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        if (null === $this->extension) {
            $this->extension = new ComingSoonExtension();
        }

        return $this->extension;
    }
}
