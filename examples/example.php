<?php

include('../cal_file_parser.php');

$cal = new CalFileParser();

//Example 1 - Parse file into array
$example1 = $cal->parse('schedule.ical');
pr($example1);

//Example 2 - outputting json
$example2 = $cal->parse('schedule.vcal', 'json');
pr($example2);

//Example 3 - alternative method to choose file
$cal->set_file_name('icalexample.ics');
$example3 = $cal->parse();
pr($example3);

//Example 4 - alternative methods to select path and output
$cal->set_base_path('./');
$cal->set_file_name('icalexample.ics');
$cal->set_output('json');
$example4 = $cal->parse();
pr($example4);


function pr($arr) {
    echo '<pre>';

    print_r("--------------------------\n");

    print_r($arr);

    print_r("\n--------------------------");

    echo '</pre>';
}

?>