<?php

declare(strict_types = 1);

namespace Apfelfrisch\Segbuilder;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;

final class Segbuilder
{
    private const NAMESPACE_ELEMENTS = 'Apfelfrisch\Edifact\Segment\Elements';
    private const NAMESPACE_ABSTRACT_SEGMENT = 'Apfelfrisch\Edifact\Segment\AbstractSegment';
    private const PROBERTY_BLUEPRINT = 'blueprint';
    private const METHOD_BLUEPRINT = self::PROBERTY_BLUEPRINT;
    private const TYPE_ONIN_NULL = '|null';
    private const TYPE_NULL_OR_STRING = 'string' . self::TYPE_ONIN_NULL;

    private PhpNamespace $namespace;
    private ClassType $class;
    private array $elements = [];
    private array $getter = [];
    private array $componentKeys = [];

    public function __construct(string $namespace, string $segname)
    {
        $this->namespace = new PhpNamespace($namespace);
        $this->namespace->addUse(self::NAMESPACE_ELEMENTS);
        $this->namespace->addUse(self::NAMESPACE_ABSTRACT_SEGMENT);

        $this->class = $this->namespace->addClass($this->normalize($segname));
        $this->class->setExtends(self::NAMESPACE_ABSTRACT_SEGMENT);
        $this->class->addProperty(self::PROBERTY_BLUEPRINT, null)
            ->setPrivate()
            ->setStatic(true)
            ->setType(self::NAMESPACE_ELEMENTS.self::TYPE_ONIN_NULL);
    }

    public function addElement(string $getter, string $elementKey, string $componentKey, string $rule): void
    {
        $this->elements[] = [$this->prepareGetter($getter), $elementKey, $this->prepareComponentKey($componentKey), $rule];
    }

    public function build(): PhpNamespace
    {
        $this->buildBlueprintMethod();
        $this->buildStaticConstructorMethods();
        $this->buildGetterMethods();

        return $this->namespace;
    }

    private function buildBlueprintMethod(): void
    {
        $string = array_reduce($this->elements, static fn(string $result, array $element): string
            => $result . "\t\t->addValue('" . implode("', '", array_slice($element, 1)) . "')\n"
        , '') . "\t\t;";

        $body = 'if (self::$blueprint === null) {'
            ."\n\t" . 'self::$blueprint = (new Elements)'
            ."\n" . $string
            ."\n" . '}'
            ."\n".'return self::$blueprint;';

        $this->class->addMethod(self::METHOD_BLUEPRINT)
            ->setReturnType(self::NAMESPACE_ELEMENTS)
            ->setBody($body)
            ->setStatic();
    }

    private function buildStaticConstructorMethods(): void
    {
        $method = $this->class->addMethod('fromAttributes')->setReturnType('self');

        [$getter, $elementKey, $componentKey] = $this->elements[0];

        $body = 'return new self((new Elements)' . "\n";
        $body .= "\t" . '->addValue(\'' . $elementKey . '\', \'' . $componentKey . '\', \'' . $getter . '\')' . "\n";

        foreach (array_slice($this->elements, 1) as $element) {
            [$getter, $elementKey, $componentKey] = $element;

            $method->addParameter($getter, null)->setType('string|null');

            $body .= "\t" . '->addValue(\'' . $elementKey . '\', \'' . $componentKey . '\', $' . $getter . ')' . "\n";
        }

        $method->setBody($body . ');')->setStatic();
    }

    private function buildGetterMethods(): void
    {
        foreach (array_slice($this->elements, 1) as $element) {
            [$getter, $elementKey, $componentKey] = $element;

            $this->class->addMethod($getter)->setReturnType(self::TYPE_NULL_OR_STRING)
                ->setBody(
                    'return $this->elements->getValue(\'' . $elementKey . '\', \'' . $componentKey . '\');'
                );
        }
    }

    private function prepareGetter(string $getter): string
    {
        $this->getter[] = $getter;

        $count = array_count_values($this->getter)[$getter];

        if ($count > 1) {
            return $this->normalize($getter . (string)$count);
        }

        return $this->normalize($getter);
    }

    private function prepareComponentKey(string $componentKey): string
    {
        $this->componentKeys[] = $componentKey;

        $count = array_count_values($this->componentKeys)[$componentKey];

        if ($count > 1) {
            return $this->normalize($componentKey) . ':' . (string)$count;
        }

        return $this->normalize($componentKey);
    }

    private function normalize(string $string): string
    {
        return preg_replace('/[^a-zA-Z0-9]/', '', $string);
    }
}
