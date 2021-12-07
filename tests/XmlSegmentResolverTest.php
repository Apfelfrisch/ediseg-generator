<?php

declare(strict_types = 1);

namespace Apfelfrisch\Segbuilder\Test;

use Apfelfrisch\Segbuilder\Segbuilder;
use Apfelfrisch\Segbuilder\XmlSegmentResolver;

final class XmlSegmentResolverTest extends TestCase
{
    /**
     * @covers XmlBuilder
     */
    public function test_resoving_segment_from_folder_stucture(): void
    {
        $builder = new XmlSegmentResolver('D11A');

        $segBuilder = $builder->get('Apfelfrisch\Edifact\Segments');

        $this->assertCount(156, $segBuilder);
        $this->assertInstanceOf(Segbuilder::class, $segBuilder[0]);
    }
}
