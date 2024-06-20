<?php

namespace Enhavo\Bundle\RoutingBundle\Router\Strategy;

use Enhavo\Bundle\RoutingBundle\Router\AbstractStrategy;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ConfigurationStrategy extends AbstractStrategy
{
    public function generate($resource , $parameters = [], $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH, $options = [])
    {
        foreach ($options['properties'] as $key => $value) {
            $parameters[$key] = $this->getProperty($resource, $value);
        }

        return $this->getRouter()->generate($options['route'], $parameters, $referenceType);
    }

    public function getType()
    {
        return 'configuration';
    }

    public function configureOptions(OptionsResolver $optionsResolver)
    {
        parent::configureOptions($optionsResolver);

        $optionsResolver->setRequired([
            'route',
            'properties'
        ]);
    }
}
