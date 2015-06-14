Form component for selecting date and time values.

In PHP this addon works with DateTime objects, in the browser it uses jqueryUI calendar with timepicker addon. Look at some exmaples at the [demo page](http://date-input.vodacek.eu/).


JS dependencies
----------
 * [jQuery](http://jquery.com/) and [jQueryUI](http://jqueryui.com/)
 * [Timepicker addon](http://trentrichardson.com/examples/timepicker/) version 1.1.0 or newer

Installation
---------

`$ composer require voda/date-input:~1.0.0`

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
        datetime: {
            dateFormat: 'd.m.yy',
            timeFormat: 'H:mm',
            options: { // options for type=datetime
                changeYear: true
            }
        },
        'datetime-local': {
            dateFormat: 'd.m.yy',
            timeFormat: 'H:mm'
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
