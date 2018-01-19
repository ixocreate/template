<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-platesrenderer for the canonical source repository
 * @copyright Copyright (c) 2016-2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-platesrenderer/blob/master/LICENSE.md New BSD License
 */
declare(strict_types=1);

namespace KiwiSuite\Template\Plates;

use KiwiSuite\ServiceManager\ServiceManagerInterface;
use League\Plates\Engine as PlatesEngine;
use League\Plates\Extension\ExtensionInterface;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Helper;
use Zend\Expressive\Plates\Exception\InvalidExtensionException;
use Zend\Expressive\Plates\Extension\EscaperExtension;
use Zend\Expressive\Plates\Extension\EscaperExtensionFactory;
use Zend\Expressive\Plates\Extension\UrlExtension;
use Zend\Expressive\Plates\Extension\UrlExtensionFactory;

/**
 * Create and return a Plates engine instance.
 *
 * Optionally uses the service 'config', which should return an array. This
 * factory consumes the following structure:
 *
 * <code>
 * 'plates' => [
 *     'extensions' => [
 *         // extension instances, or
 *         // service names that return extension instances, or
 *         // class names of directly instantiable extensions.
 *     ]
 * ]
 * </code>
 *
 * By default, this factory attaches the Extension\UrlExtension
 * and Extension\EscaperExtension to the engine. You can override
 * the functions that extension exposes by providing an extension
 * class in your extensions array, or providing an alternative
 * Zend\Expressive\Plates\Extension\UrlExtension service.
 */
class PlatesEngineFactory
{
    public function __invoke(ServiceManagerInterface $container): PlatesEngine
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $config = isset($config['plates']) ? $config['plates'] : [];

        // Create the engine instance:
        $engine = new PlatesEngine();

        $this->injectUrlExtension($container, $engine);
        $this->injectEscaperExtension($container, $engine);

        if (isset($config['extensions']) && \is_array($config['extensions'])) {
            $this->injectExtensions($container, $engine, $config['extensions']);
        }

        return $engine;
    }

    /**
     * Inject the URL/ServerUrl extensions provided by this package.
     *
     * If a service by the name of the UrlExtension class exists, fetches
     * and loads it.
     *
     * Otherwise, instantiates the UrlExtensionFactory, and invokes it with
     * the container, loading the result into the engine.
     */
    private function injectUrlExtension(ServiceManagerInterface $container, PlatesEngine $engine): void
    {
        if ($container->has(UrlExtension::class)) {
            $engine->loadExtension($container->get(UrlExtension::class));
            return;
        }

        // If the extension was not explicitly registered, load it only if both helpers were registered
        if (!$container->has(Helper\UrlHelper::class) || !$container->has(Helper\ServerUrlHelper::class)) {
            return;
        }

        $extensionFactory = new UrlExtensionFactory();
        $engine->loadExtension($extensionFactory($container));
    }

    /**
     * Inject the Escaper extension provided by this package.
     *
     * If a service by the name of the EscaperExtension class exists, fetches
     * and loads it.
     *
     * Otherwise, instantiates the EscaperExtensionFactory, and invokes it with
     * the container, loading the result into the engine.
     */
    private function injectEscaperExtension(ServiceManagerInterface $container, PlatesEngine $engine): void
    {
        if ($container->has(EscaperExtension::class)) {
            $engine->loadExtension($container->get(EscaperExtension::class));
            return;
        }

        $extensionFactory = new EscaperExtensionFactory();
        $engine->loadExtension($extensionFactory($container));
    }

    /**
     * Inject all configured extensions into the engine.
     */
    private function injectExtensions(ContainerInterface $container, PlatesEngine $engine, array $extensions): void
    {
        foreach ($extensions as $extension) {
            $this->injectExtension($container, $engine, $extension);
        }
    }

    /**
     * Inject an extension into the engine.
     *
     * Valid extension specifications include:
     *
     * - ExtensionInterface instances
     * - String service names that resolve to ExtensionInterface instances
     * - String class names that resolve to ExtensionInterface instances
     *
     * If anything else is provided, an exception is raised.
     *
     * @param ServiceManagerInterface   $container
     * @param PlatesEngine              $engine
     * @param ExtensionInterface|string $extension
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function injectExtension(ServiceManagerInterface $container, PlatesEngine $engine, $extension): void
    {
        if ($extension instanceof ExtensionInterface) {
            $engine->loadExtension($extension);
            return;
        }

        if (!\is_string($extension)) {
            throw new InvalidExtensionException(\sprintf(
                '%s expects extension instances, service names, or class names; received %s',
                __CLASS__,
                (\is_object($extension) ? \get_class($extension) : \gettype($extension))
            ));
        }

        if (!$container->has($extension) && !\class_exists($extension)) {
            throw new InvalidExtensionException(\sprintf(
                '%s expects extension service names or class names; "%s" does not resolve to either',
                __CLASS__,
                $extension
            ));
        }

        $extension = $container->has($extension)
            ? $container->get($extension)
            : new $extension();

        if (!$extension instanceof ExtensionInterface) {
            throw new InvalidExtensionException(\sprintf(
                '%s expects extension services to implement %s ; received %s',
                __CLASS__,
                ExtensionInterface::class,
                (\is_object($extension) ? \get_class($extension) : \gettype($extension))
            ));
        }

        $engine->loadExtension($extension);
    }
}
