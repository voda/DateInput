DateInput
=========
Nette forms component for selecting date and time values.

In PHP this addon works with DateTime objects, in the browser it uses jqueryUI calendar with timepicker addon. Look at some examples at the [demo page](http://date-input.vodacek.eu/).


[![Build Status](https://travis-ci.org/voda/DateInput.svg?branch=master)](https://travis-ci.org/voda/DateInput)
[![Latest Stable Version](https://poser.pugx.org/voda/date-input/v/stable)](https://packagist.org/packages/voda/date-input)
[![Total Downloads](https://poser.pugx.org/voda/date-input/downloads)](https://packagist.org/packages/voda/date-input)
[![License](https://poser.pugx.org/voda/date-input/license)](https://packagist.org/packages/voda/date-input)


JS dependencies
---------------
 * [jQuery](http://jquery.com/) and [jQueryUI](http://jqueryui.com/)
 * [Timepicker addon](http://trentrichardson.com/examples/timepicker/) version 1.1.0 or newer

Installation
------------

`$ composer require voda/date-input`

package can be also installed using bower: `$ bower install voda-date-input --save`

insert required javascript and style files into your layout (order of scripts is important):
```html
<script type='text/javascript' src="{$basePath}/scripts/jquery-ui-timepicker-addon.js"></script>
<script type='text/javascript' src="{$basePath}/scripts/dateInput.js"></script>
<link rel="stylesheet" type="text/css" href="{$basePath}/styles/jquery-ui-timepicker-addon.css">
<link rel="stylesheet" type="text/css" href="{$basePath}/styles/dateInput.css">
```
register the addon in your bootstrap.php:
```
Vodacek\Forms\Controls\DateInput::register();
```
initialize the calendar using javascript:
```js
$(document).ready(function() {
    $('input[data-dateinput-type]').dateinput({
        'datetime-local': {
            dateFormat: 'd.m.yy',
            timeFormat: 'H:mm',
            options: { // options for type=datetime-local
                changeYear: true
            }
        },
        date: {
            dateFormat: 'd.m.yy'
        },
        month: {
            dateFormat: 'MM yy'
        },
        week: {
            dateFormat: "w. 'week of' yy"
        },
        time: {
            timeFormat: 'H:mm'
        },
        options: { // global options
            closeText: "Close"
        }
    });
});
```

Usage
-----
```php
$form->addDate('datetimeLocal', 'Local datetime', DateInput::TYPE_DATETIME_LOCAL)
        ->setRequired()
        ->setDefaultValue(new DateTimeImmutable())
        ->addRule(Form::RANGE, null, array(new DateTimeImmutable('-2 years'), new DateTimeImmutable('+2 years')));
```
