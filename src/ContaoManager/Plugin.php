<?php

declare(strict_types=1);

namespace ContaoBootstrap\Tab\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use ContaoBootstrap\Core\ContaoBootstrapCoreBundle;
use ContaoBootstrap\Grid\ContaoBootstrapGridBundle;
use ContaoBootstrap\Tab\ContaoBootstrapTabBundle;

use function class_exists;

final class Plugin implements BundlePluginInterface
{
    /**
     * {@inheritDoc}
     */
    public function getBundles(ParserInterface $parser): array
    {
        $loadAfter = [ContaoCoreBundle::class, ContaoBootstrapCoreBundle::class];

        if (class_exists(ContaoBootstrapGridBundle::class)) {
            $loadAfter[] = ContaoBootstrapGridBundle::class;
        }

        return [BundleConfig::create(ContaoBootstrapTabBundle::class)->setLoadAfter($loadAfter)];
    }
}
