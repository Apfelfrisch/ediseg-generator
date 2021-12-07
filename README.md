# Segment generator for Apfelfrisch/Edifact

## Usage

```php
use Apfelfrisch\Segbuilder\FileWriter;
use Apfelfrisch\Segbuilder\XmlSegmentResolver;

$writer = new FileWriter();
$writer->setSegmentNamespace('Your\Segment\Namespace');
$writer->addClassResolver(new XmlSegmentResolver('D11A', 'Service_V4'));
$writer->writeFiles('path/to/segments/');
```
