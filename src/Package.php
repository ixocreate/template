<?php
/**
 * kiwi-suite/template (https://github.com/kiwi-suite/template)
 *
 * @package kiwi-suite/template
 * @link https://github.com/kiwi-suite/template
 * @copyright Copyright (c) 2010 - 2018 kiwi suite GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace KiwiSuite\Template;

use KiwiSuite\Contract\Application\ConfiguratorRegistryInterface;
use KiwiSuite\Contract\Application\PackageInterface;
use KiwiSuite\Contract\Application\ServiceRegistryInterface;
use KiwiSuite\Contract\ServiceManager\ServiceManagerInterface;
use KiwiSuite\Template\BootstrapItem\TemplateBootstrapItem;

final class Package implements PackageInterface
{

    /**
     * @param ConfiguratorRegistryInterface $configuratorRegistry
     */
    public function configure(ConfiguratorRegistryInterface $configuratorRegistry): void
    {
    }

    /**
     * @param ServiceRegistryInterface $serviceRegistry
     */
    public function addServices(ServiceRegistryInterface $serviceRegistry): void
    {
    }

    /**
     * @return array|null
     */
    public function getBootstrapItems(): ?array
    {
        return [
            TemplateBootstrapItem::class,
        ];
    }

    /**
     * @return array|null
     */
    public function getConfigProvider(): ?array
    {
        return null;
    }

    /**
     * @param ServiceManagerInterface $serviceManager
     */
    public function boot(ServiceManagerInterface $serviceManager): void
    {
    }

    /**
     * @return null|string
     */
    public function getBootstrapDirectory(): ?string
    {
        return __DIR__ . '/../bootstrap';
    }

    /**
     * @return null|string
     */
    public function getConfigDirectory(): ?string
    {
        return null;
    }

    /**
     * @return array|null
     */
    public function getDependencies(): ?array
    {
        return null;
    }
}
