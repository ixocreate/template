<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Template\Factory;

use Ixocreate\ServiceManager\FactoryInterface;
use Ixocreate\ServiceManager\ServiceManagerInterface;
use Ixocreate\Template\Config\TemplateConfig;
use Ixocreate\Template\Extension\ExtensionMapping;
use Ixocreate\Template\Extension\ExtensionSubManager;
use Ixocreate\Template\Renderer;
use League\Plates\Engine;
use Zend\Expressive\Plates\PlatesRenderer;

final class TemplateRendererFactory implements FactoryInterface
{
    /**
     * @param ServiceManagerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @return mixed
     */
    public function __invoke(ServiceManagerInterface $container, $requestedName, array $options = null)
    {
        /** @var TemplateConfig $templateConfig */
        $templateConfig = $container->get(TemplateConfig::class);

        $plates = new Engine();
        $plates->setFileExtension($templateConfig->getFileExtension());
        foreach ($templateConfig->getDirectories() as $templateDirectory) {
            $plates->addFolder($templateDirectory['name'], $templateDirectory['directory']);
        }

        /** @var ExtensionSubManager $extensionManager */
        $extensionManager = $container->get(ExtensionSubManager::class);

        $extensionMapping = $container->get(ExtensionMapping::class)->getMapping();
        foreach ($extensionMapping as $name => $extension) {
            $plates->registerFunction($name, function (...$arguments) use ($extensionManager, $extension) {
                return \call_user_func_array($extensionManager->get($extension), $arguments);
            });
        }

        return new Renderer(new PlatesRenderer($plates));
    }
}
