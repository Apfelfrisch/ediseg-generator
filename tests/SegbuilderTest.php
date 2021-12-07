<?php

declare(strict_types = 1);

namespace Apfelfrisch\Segbuilder\Test;

use Apfelfrisch\Segbuilder\Segbuilder;
use Spatie\Snapshots\MatchesSnapshots;

final class SegbuilderTest extends TestCase
{
    use MatchesSnapshots;

    /**
     * @covers Segbuilder
     */
    public function test_(): void
    {
        $segbuilder = new Segbuilder('Apfelfrisch\Segment', 'Ajt');
        $segbuilder->addElement('code', '4465', '4465', 'M|an|..3');
        $segbuilder->addElement('qualifier', '4467', '4463', 'M|an|..3');

        $this->assertMatchesSnapshot($segbuilder->build()->__toString());
    }
}
