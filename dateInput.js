/*
 * Author: Ondřej Vodáček <ondrej.vodacek@gmail.com>
 * License: New BSD License
 *
 * Copyright (c) 2011, Ondřej Vodáček
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *     * Neither the name of the Ondřej Vodáček nor the
 *       names of its contributors may be used to endorse or promote products
 *       derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL Ondřej Vodáček BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 *
 * Sources:
 * http://stackoverflow.com/questions/2208480/jquery-date-picker-to-show-month-year-only
 * http://stackoverflow.com/questions/2224135/can-i-highlight-an-entire-week-in-the-standard-jquery-ui-date-picker
 * http://en.wikipedia.org/wiki/ISO_week_date#Calculating_a_date_given_the_year.2C_week_number_and_weekday
 */
(function($) {
	/***************************************************************************
	 * override datepicker formatDate method for week formating support
	 **************************************************************************/
	var proxied = $.datepicker.formatDate;
	$.datepicker.formatDate = function(format, date, settings) {
		// mostly from jquery.ui.datepicker.js
		if (!date)
			return '';

		// Check whether a format character is doubled
		var lookAhead = function(match) {
			var matches = (iFormat + 1 < format.length && format.charAt(iFormat + 1) == match);
			if (matches)
				iFormat++;
			return matches;
		};
		// Format a number, with leading zero if necessary
		var formatNumber = function(match, value, len) {
			var num = '' + value;
			if (lookAhead(match))
				while (num.length < len)
					num = '0' + num;
			return num;
		};
		var calculateWeek = (settings ? settings.calculateWeek : null) || this._defaults.calculateWeek;
		var output = '';
		var literal = false;
		for (var iFormat = 0; iFormat < format.length; iFormat++) {
			if (literal) {
				if (format.charAt(iFormat) == "'") {
					literal = false;
				}
				output += format.charAt(iFormat);
			} else {
				switch (format.charAt(iFormat)) {
					case 'w':
						output += formatNumber('w', calculateWeek(date), 2);
						break;
					case "'":
						output += "'";
						literal = true;
						break;
					default:
						output += format.charAt(iFormat);
				}
			}
		};
		arguments[0] = output;
		return proxied.apply(this, arguments);
	}
	/***************************************************************************
	 * date parsing functions
	 **************************************************************************/
	var parseWeek = function(date) {
		// 2011-W05
		if (date.length  != 8) {
			return null;
		}
		var year = parseInt(date.substr(0,4), 10);
		var week = parseInt(date.substr(6,2), 10);

		var correctionDate = new Date(year, 0, 4);
		var correction = correctionDate.getDay();
		if (correction == 0) {
			correction = 7;
		}
		correction += 3;
		// let the Date object do the math for calculating the correct month and day
		var day = new Date(year, 0, (week * 7) + 1 - correction);
		return day;
	}
	var parseTime = function(time) {
		// 12:12[:12]
		if (time.length < 5) {
			return null;
		}
		var d = new Date();
		d.setHours(
			parseInt(time.substr(0, 2), 10), //hour
			parseInt(time.substr(3, 2), 10), //minute
			(time.lengt >= 8) ? parseInt(time.substr(6, 2), 10) : 0 //second
			);
		return d;
	}
	var parseDate = function(datetime) {
		// 2011-05-08
		if (datetime.length < 10) {
			return null;
		}
		return new Date(
			parseInt(datetime.substr(0, 4), 10), //year
			parseInt(datetime.substr(5, 2), 10) - 1, //month
			parseInt(datetime.substr(8, 2), 10) //day
			);
	}
	var parseDateTime = function(datetime) {
		// 2011-05-08T12:12[:12]
		if (datetime.length < 19) {
			return null;
		}
		var date = parseDate(datetime)
		var time = parseTime(datetime.substr(11));
		date.setHours(
			time.getHours(),
			time.getMinutes(),
			time.getSeconds()
			);
		return date;
	}
	var parseMonth = function(date) {
		// 2011-05
		return parseDate(date + '-01');
	}
	var globalSettings = {
		datetime: {
			parseFunction: parseDateTime,
			create: function(object, settings) {
				return object.datetimepicker(settings);
			},
			dateFormat: 'yy-mm-dd',
			timeFormat: 'hh:mm',
			validFormat: '\\d{4}-\\d{2}-\\d{2}'
		},
		date: {
			parseFunction: parseDate,
			create: function(object, settings) {
				return object.datepicker(settings);
			},
			dateFormat: 'yy-mm-dd',
			validFormat: '\\d{4}-\\d{2}-\\d{2}'
		},
		month: {
			parseFunction: parseMonth,
			create: function(object, settings) {
				return object.datepicker(settings);
			},
			dateFormat: 'yy-mm',
			validFormat: '\\d{4}-\\d{2}'
		},
		week: {
			parseFunction: parseWeek,
			create: function(object, settings) {
				return object.datepicker(settings);
			},
			dateFormat: 'yy-Www',
			validFormat: '\\d{4}-W\\d{2}'
		},
		time: {
			parseFunction: parseTime,
			create: function(object, settings) {
				return object.timepicker(settings);
			},
			timeFormat: 'hh:mm',
			validFormat: '\\d{2}:\\d{2}'
		}
	};
	globalSettings['datetime-local'] = globalSettings.datetime;
	globalSettings['datetime-local'].validFormat += '.*'; // timezone

	$.fn.dateinput = function(userSettings) {
		this.each(function() {
			var t = $(this);
			var type = t.attr('data-dateinput-type');
			var settings = globalSettings[type];
			userSettings = userSettings || {};
			$.extend(settings, userSettings[type] || {});

			// create alt field
			this.type = 'text';
			var alt = t.clone().removeAttr('id');
			try {
				alt.get(0).type = 'hidden';
			} catch (exception) {
				// fix for: http://webbugtrack.blogspot.com/2007/09/bug-237-type-is-readonly-attribute-in.html
				alt = $(alt.get(0).outerHTML.replace(/ type=(['"]?)[a-z-]+\1/, ' type="hidden"'));
			}
			t.removeAttr('name');
			t.val(null);
			t.after(alt);
			t.data('altField', alt);

			var pickerSettings = {};

			// min and max date
			var min = alt.attr('min');
			if (min) {
				pickerSettings['minDate'] = settings.parseFunction(min);
			}
			var max = alt.attr('max');
			if (max) {
				pickerSettings['maxDate'] = settings.parseFunction(max);
			}
			var selectedDate = null;
			var date = alt.val();
			if (date) {
				selectedDate = settings.parseFunction(date);
			}
			if (settings.dateFormat) {
				pickerSettings.dateFormat = settings.dateFormat;
			}
			if (settings.timeFormat) {
				pickerSettings.timeFormat = settings.timeFormat;
			}

			//
			switch (type) {
				case 'datetime':
				case 'datetime-local':
					$.extend(pickerSettings, {
						stepHour: 1,
						stepMinute: 1,
						showButtonPanel: true,
						onSelect: function(dateText, inst) {
							if (!selectedDate) {
								selectedDate = new Date();
								selectedDate.setHours(0, 0, 0, 0);
							}
							if (inst.hour !== undefined) {
								selectedDate.setHours(inst.hour, inst.minute, inst.second);
							} else {
								selectedDate.setFullYear(inst.selectedYear, inst.selectedMonth, inst.selectedDay);
							}
							var tp = {
								hour: selectedDate.getHours(),
								minute: selectedDate.getMinutes(),
								second: selectedDate.getSeconds()
							};
							var value = $.datepicker.formatDate('yy-mm-dd', selectedDate) + 'T' + $.timepicker._formatTime(tp, 'hh:mm:ss', false);
							if (type == 'datetime') {
								value += 'Z';
							}
							alt.val(value);
						}
					});
					break;
				case 'date':
					$.extend(pickerSettings, {
						altField: alt,
						altFormat: 'yy-mm-dd',
						showButtonPanel: true,
						showOtherMonths: true
					});
					break;
				case 'month':
					$.extend(pickerSettings, {
						altField: alt,
						altFormat: 'yy-mm',
						showButtonPanel: true,
						showOtherMonths: true,
						beforeShow: function(input, inst) {
							if (selectedDate) {
								$(this).datepicker('option', 'defaultDate', selectedDate);
								$(this).datepicker('setDate', selectedDate);
							}
						},
						beforeShowDay: function(day) {
							var c = 'ui-datepicker-month';
							if (selectedDate && selectedDate.getFullYear() == day.getFullYear() && selectedDate.getMonth() == day.getMonth()) {
								c += ' ui-datepicker-month-selected';
							}
							var now = new Date();
							if (day.getFullYear() == now.getFullYear() && day.getMonth() == now.getMonth()) {
								c += ' ui-datepicker-month-current';
							}
							return [true, c];
						},
						onSelect: function(day, inst) {
							selectedDate = new Date(inst.selectedYear, inst.selectedMonth, 1);
							t.datepicker('setDate', selectedDate);
							inst.input.blur();
						}
					});
					if (pickerSettings['maxDate']) {
						pickerSettings['maxDate'].setMonth(pickerSettings['maxDate'].getMonth() + 1, 0);
					}
					break;
				case 'week':
					$.extend(pickerSettings, {
						altField: alt,
						altFormat: 'yy-Www',
						showButtonPanel: true,
						showOtherMonths: true,
						showWeek: true,
						beforeShow: function() {
							if (selectedDate) {
								t.datepicker('option', 'defaultDate', selectedDate);
								t.datepicker('setDate', selectedDate);
							}
						},
						beforeShowDay: function(day) {
							var c = 'ui-datepicker-week';
							if (selectedDate && $.datepicker.iso8601Week(selectedDate) == $.datepicker.iso8601Week(day) && selectedDate.getFullYear() == day.getFullYear()) {
								c += ' ui-datepicker-week-selected';
							}
							var now = new Date();
							if ($.datepicker.iso8601Week(day) == $.datepicker.iso8601Week(now) && day.getFullYear() == now.getFullYear()) {
								c += ' ui-datepicker-week-current';
							}
							return [true, c];
						},
						onSelect: function(day, inst) {
							selectedDate = new Date(inst.selectedYear, inst.selectedMonth, inst.selectedDay);
							// move to monday
							selectedDate.setDate(selectedDate.getDate() - (selectedDate.getDay() + 6) % 7);
							t.datepicker('setDate', selectedDate);
							inst.input.blur();
						}
					});
					if (pickerSettings['maxDate']) {
						pickerSettings['maxDate'].setDate(pickerSettings['maxDate'].getDate() + 6); // move from monday to sunday
					}
					break;
				case 'time':
					$.extend(pickerSettings, {
						stepHour: 1,
						stepMinute: 1,
						showButtonPanel: true,
						onSelect: function(day, inst) {
							alt.val($.timepicker._formatTime(inst, 'hh:mm:ss', false));
						}
					});
					break;
			}

			settings.create(t, pickerSettings);

			if (selectedDate) {
				t.datepicker('setDate', selectedDate);
			}
		});
		return this;
	};

	// Nette validators
	Nette.validators.dateInputValid = function(elem, arg, val) {
		var el = $(elem);
		var type = el.attr('data-dateinput-type');
		var format = globalSettings[type].validFormat;
		val = el.data('altField').val();
		return (new RegExp('^(' + format + ')$')).test(val);
	};
	Nette.validators.dateInputRange = function(elem, arg, val) {
		var el = $(elem);
		val = el.data('altField').val();
		return Nette.isArray(arg) ? ((arg[0] === null || val >= arg[0]) && (arg[1] === null || val <= arg[1])) : null;
	};
})(jQuery);
