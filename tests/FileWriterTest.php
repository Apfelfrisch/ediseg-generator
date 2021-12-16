<?php

declare(strict_types = 1);

namespace Apfelfrisch\Segbuilder\Test;

use Apfelfrisch\Segbuilder\FileWriter;
use Apfelfrisch\Segbuilder\XmlSegmentResolver;

final class FileWriterTest extends TestCase
{
    private string $path;

    public function setUp(): void
    {
        $this->path = __DIR__ . '/tmp/';
        if (is_dir($this->path)) {
            $this->cleanPath();
        } else {
            mkdir($this->path);
        }
    }

    public function tearDown(): void
    {
        $this->cleanPath();

        rmdir($this->path);
    }

    private function cleanPath(): void
    {
        foreach (glob($this->path . '*.php') as $segmentClassFile) {
            unlink($segmentClassFile);
        }
    }

    /**
     * @covers FileWriter
     */
    public function test_write_files(): void
    {
        $segmentPath = __DIR__ . '/tmp/';

        $writer = new FileWriter();
        $writer->setSegmentNamespace('Apfelfrisch\Edifact\TestSegments');
        $writer->addClassResolver(new XmlSegmentResolver('D11A', 'Service_V4'));
        $writer->writeFiles($segmentPath);

        $files = glob(__DIR__ . '/tmp/*.php');

        $this->assertCount(190, $files);
    }
}
