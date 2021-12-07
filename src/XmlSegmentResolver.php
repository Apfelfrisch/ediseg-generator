<?php

namespace Apfelfrisch\Segbuilder;

use EDI\Mapping\MappingProvider;
use SimpleXMLElement;

final class XmlSegmentResolver
{
    private array $elementsXmls = [];

    public function __construct(string ...$mappings)
    {
        foreach ($mappings as $mapping) {
            $this->elementsXmls[] = simplexml_load_file(
                (new MappingProvider($mapping))->getSegments()
            );
        }
    }

    /** @return array<Segbuilder> */
    public function get(string $namespace): array
    {
        $builderArray = [];

        foreach ($this->elementsXmls as $elementsXml) {
            foreach ($elementsXml as $elementXml) {
                $currentBuilder = new Segbuilder(
                    $namespace,
                    ucfirst(strtolower($elementXml->attributes()->id)) . 'Segment'
                );

                $segname = strtoupper((string)$elementXml->attributes()->id);
                $currentBuilder->addElement( $segname, $segname, $segname, 'M|a|3');

                foreach ($elementXml as $groupedComponent) {
                    $groupIsNeeded = (bool)$groupedComponent->attributes()->required;
                    $elementKey = (string)$groupedComponent->attributes()->id;

                    if ($groupedComponent->getName() !== 'composite_data_element') {
                        $this->addElementToSegbuilder($currentBuilder, $elementKey, $groupedComponent, $groupIsNeeded);
                    }

                    foreach ($groupedComponent as $component) {
                        $this->addElementToSegbuilder($currentBuilder, $elementKey, $component, $groupIsNeeded);
                    }
                }
                $builderArray[] = $currentBuilder;
            }
        }

        return $builderArray;
    }

    private function addElementToSegbuilder(Segbuilder $segbuilder, string $elementKey, SimpleXMLElement $component, bool $groupIsNeeded): Segbuilder
    {
        if ((bool)$component->attributes()->required) {
            if ($groupIsNeeded) {
                $rule = 'M|' . $component->attributes()->type . '|..' . $component->attributes()->maxlength;
            } else {
                $rule = 'D|' . $component->attributes()->type . '|..' . $component->attributes()->maxlength;
            }
        } else {
            $rule = 'O|' . $component->attributes()->type . '|..' . $component->attributes()->maxlength;
        }

        $segbuilder->addElement(
            $component->attributes()->name,
            $elementKey,
            $component->attributes(),
            $rule
        );

        return $segbuilder;
    }
}
