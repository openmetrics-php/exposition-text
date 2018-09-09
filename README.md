[![Build Status](https://travis-ci.org/openmetrics-php/exposition-text.svg?branch=master)](https://travis-ci.org/openmetrics-php/exposition-text)
[![Latest Stable Version](https://poser.pugx.org/openmetrics-php/exposition-text/v/stable)](https://packagist.org/packages/openmetrics-php/exposition-text) 
[![Total Downloads](https://poser.pugx.org/openmetrics-php/exposition-text/downloads)](https://packagist.org/packages/openmetrics-php/exposition-text) 
[![Coverage Status](https://coveralls.io/repos/github/openmetrics-php/exposition-text/badge.svg?branch=master)](https://coveralls.io/github/openmetrics-php/exposition-text?branch=master)

# Exposition\Text

## Description

Implementation of the text exposition formats of OpenMetrics

## Installation

```bash
composer require openmetrics-php/exposition-text
```

## Usage

### Creating labels from name and value

```php
<?php declare(strict_types=1);

use OpenMetricsPhp\Exposition\Text\Label;

$label1 = Label::fromNameAndValue('group_label', 'Some value');
$label2 = Label::fromNameAndValue('group_label', ' Value with surrounding whitespaces are trimmed ');
$label3 = Label::fromNameAndValue(' labels_with_surrounding_whitespace_are_trimmed ', 'Some value');
$label4 = Label::fromNameAndValue('group_label', "Value\nwith\nlinebreak");
$label5 = Label::fromNameAndValue('group_label', 'Value with double " quote');

echo $label1->asLabelString(); # group_label="Some value"
echo $label2->asLabelString(); # group_label="Value with surrounding whitespaces are trimmed"
echo $label3->asLabelString(); # labels_with_surrounding_whitespace_are_trimmed="Some value"
echo $label4->asLabelString(); # group_label="Value\nwith\nlinebreak"
echo $label5->asLabelString(); # group_label="Value with double \" quote"
```

### Creating labels from label string

```php
<?php declare(strict_types=1);

use OpenMetricsPhp\Exposition\Text\Label;

$label1 = Label::fromLabelString('group_label="Some value"');
$label2 = Label::fromLabelString('group_label="Value\nwith\nlinebreak"');
$label3 = Label::fromLabelString('group_label="Value with double \" quote"');

echo $label1->getName(), ' / ', $label1->getValue(); # group_label / Some value
echo $label2->getName(), ' / ', $label2->getValue(); # group_label / Value
                                                     #               with
                                                     #               linebreak
echo $label3->getName(), ' / ', $label3->getValue(); # group_label / Value with double " quote
```

## Contributing

Contributions are welcome and will be fully credited. Please see the [contribution guide](.github/CONTRIBUTING.md) for details.


