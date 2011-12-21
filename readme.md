### Cal File Parser

Parser for Calendar files (.ical, .vcal, .ics). Reads event information and outputs data into an Array or JSON.

*Supports parsing iCal and vCal files*

## Initialiing Class
	
	include('../CalFileParser.php');

	$cal = new CalFileParser();
	
## Public Functions

* parse($file = '', $output = '') 

* set_base_path($path) // defaults to './'
* set_file_name($filename) // local file or URL
* set_output($output) // 'array' or 'json'

* get_base_path();
* get_file_name();
* get_output();
	
## Using CalFileParser

**Parse file into Array**

	$example1 = $cal->parse('schedule.ical');

**Parse file into JSON**

	$example2 = $cal->parse('schedule.ical', 'json');
	
**Parse remote file**

	$example3 = $cal->parse('http://mywebsite.com/events.ical');
	
**alternative method to choose file**

	$cal->set_file_name('icalexample.ics');
	
	$example4 = $cal->parse();
	
**alternative methods to select path and output**

	$cal->set_base_path('./');
	$cal->set_file_name('icalexample.ics');
	$cal->set_output('json');
	$example5 = $cal->parse();
	
# License (MIT)

Copyright (c) 2011 Michael Mottola <mikemottola@gmail.com>

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
"Software"), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

