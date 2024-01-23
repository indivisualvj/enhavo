<?php

namespace Enhavo\Bundle\ContactBundle\DependencyInjection;

use App\Model\Contact;
use Enhavo\Bundle\ContactBundle\Form\Type\ContactType;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('enhavo_contact');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('contact')
                    ->addDefaultsIfNotSet()
                ->end()
                ->arrayNode('forms')
                    ->arrayPrototype()
                        ->children()
                            ->variableNode('type')->end()
                            ->scalarNode('model')->defaultValue(Contact::class)->end()
                            ->arrayNode('form')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->variableNode('class')->defaultValue(ContactType::class)->end()
                                    ->variableNode('options')->defaultValue([])->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end()
        ;

        return $treeBuilder;
    }
}
