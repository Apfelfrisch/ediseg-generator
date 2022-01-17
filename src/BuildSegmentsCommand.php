<?php

namespace Apfelfrisch\Segbuilder;

use Closure;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use EDI\Mapping\MappingProvider;

class BuildSegmentsCommand extends Command
{
    protected static $defaultName = 'build:segments';

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $classWriter = new FileWriter;

        $classWriter->setSegmentNamespace(
            $this->askForNamespace($input, $output)
        );

        $classWriter->addClassResolver(new XmlSegmentResolver(
            $this->askForEdiStandard($input, $output),
            $this->askForServiceSegmentVersion($input, $output)
        ));

        $classWriter->writeFiles($this->askForFilepath($input, $output));

        return Command::SUCCESS;
    }

    private function askForNamespace(InputInterface $input, OutputInterface $output): string
    {
        $question = new Question('Please enter the Namespace for your Segments: ');

        return $this->getHelper('question')->ask($input, $output, $question);
    }

    private function askForEdiStandard(InputInterface $input, OutputInterface $output): string
    {
        $question = new ChoiceQuestion('Please chose your Edifact Version', $this->getEdiStandards());

        return $this->getHelper('question')->ask($input, $output, $question);
    }

    private function askForServiceSegmentVersion(InputInterface $input, OutputInterface $output): string
    {
        $question = new ChoiceQuestion('Please chose your Service Segment Version', $this->getServiceSegements());

        return $this->getHelper('question')->ask($input, $output, $question);
    }

    private function askForFilepath(InputInterface $input, OutputInterface $output): string
    {
        $question = new Question('Please enter the Filepath for your Segments: ');
        $question->setAutocompleterCallback($this->pathFinder());

        return $this->getHelper('question')->ask($input, $output, $question);
    }

    private function getEdiStandards(): array
    {
        $directories = (new MappingProvider)->listDirectories();

        return array_values(array_filter($directories, static function($directory) {
            return strlen($directory) === 4;
        }));
    }

    private function getServiceSegements(): array
    {
        $directories = (new MappingProvider)->listDirectories();

        return array_values(array_filter($directories, static function($directory) {
            return str_starts_with($directory, 'Service');
        }));
    }

    private function pathFinder(): Closure
    {
        return static function (string $userInput): array {
            // Strip any characters from the last slash to the end of the string
            // to keep only the last directory and generate suggestions for it
            $inputPath = preg_replace('%(/|^)[^/]*$%', '$1', $userInput);
            $inputPath = '' === $inputPath ? '.' : $inputPath;

            $foundFilesAndDirs = @scandir($inputPath) ?: [];

            return array_map(function ($dirOrFile) use ($inputPath) {
                return $inputPath.$dirOrFile;
            }, $foundFilesAndDirs);
        };
    }
}
