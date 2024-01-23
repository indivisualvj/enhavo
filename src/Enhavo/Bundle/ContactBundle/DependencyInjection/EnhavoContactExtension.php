<?php

namespace Enhavo\Bundle\ContactBundle\DependencyInjection;

use Sylius\Bundle\ResourceBundle\DependencyInjection\Extension\AbstractResourceExtension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Yaml\Yaml;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class EnhavoContactExtension extends AbstractResourceExtension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configs = $this->processConfiguration(new Configuration(), $configs);
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $this->registerResources('enhavo_contact', $configs['driver'], $configs['resources'], $container);

        $container->setParameter('enhavo_contact.contact.provider', $configs['contact']['forms']);

        $configFiles = array(
            'services/services.yaml',
            'services/contact.yaml',
        );

        foreach ($configFiles as $configFile) {
            $loader->load($configFile);
        }
    }

    /**
     * @inheritDoc
     */
    public function prepend(ContainerBuilder $container): void
    {
        $configs = Yaml::parse(file_get_contents(__DIR__ . '/../Resources/config/app/config.yaml'));
        foreach ($configs as $name => $config) {
            if (is_array($config)) {
                $container->prependExtensionConfig($name, $config);
            }
        }
    }
}
