<?php declare(strict_types = 1);

namespace lkovace18\EntityFactoryBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class EntityFactoryExtension extends Extension
{
    public function getAlias()
    {
        return 'entity_factory';
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        // Auto-Detection
        if ($config['auto_detection']) {
            $this->autodetectDirectories($container);
        }

        // Locale
        if ($locale = $config['locale']) {
            $this->setLocale($container, $locale);
        }
    }

    /**
     * @param ContainerBuilder $container
     *
     * @return mixed
     */
    private function autodetectDirectories(ContainerBuilder $container)
    {
        $projectRoot = $container->getParameter('kernel.project_dir');
        $directories = $projectRoot . '/EntityDefinitions';

        $container
            ->getDefinition('entity_factory.config_provider.config_loader')
            ->replaceArgument(0, $directories)
        ;
    }

    /**
     * @param ContainerBuilder $container
     * @param                  $locale
     */
    private function setLocale(ContainerBuilder $container, $locale)
    {
        $container
            ->getDefinition('entity_factory.data_provider.faker_data_provider')
            ->addArgument($locale)
        ;
    }
}
