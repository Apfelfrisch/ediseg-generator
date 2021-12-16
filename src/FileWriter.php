<?php

namespace Apfelfrisch\Segbuilder;

use Exception;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\Printer;
use Nette\PhpGenerator\PsrPrinter;

final class FileWriter
{
    private string $namespace = '';
    private array $classResolver = [];

    public function __construct(
        private Printer|null $printer = null
    ) {
        $this->printer ??= new PsrPrinter;
    }

    public function setSegmentNamespace(string $namespace): void
    {
        $this->namespace = $namespace;
    }

    public function addClassResolver(XmlSegmentResolver $classResolver): void
    {
        $this->classResolver[] = $classResolver;
    }

    public function writeFiles(string $path = __DIR__): void
    {
        if (substr($path, -1) === DIRECTORY_SEPARATOR) {
            $path = substr($path, 0, -1);
        }

        foreach ($this->classResolver as $classResolver) {
            foreach ($classResolver->get($this->namespace) as $segBuilder) {
                $classNames = array_keys($segBuilder->build()->getClasses());

                if (count($classNames) !== 1) {
                    throw new Exception("Only one Class per Namespace allowed.");
                }

                file_put_contents(
                    $path . DIRECTORY_SEPARATOR . $classNames[0] . '.php',
                    $this->generateFilecontent($segBuilder)
                );
            }
        }
    }

    private function generateFilecontent(Segbuilder $segbuilder): string
    {
        $file = new PhpFile;
        $file->addNamespace($segbuilder->build());

        return $this->printer->printFile($file);
    }
}
