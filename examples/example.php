<?php

include('../CalFileParser.php');

$cal = new CalFileParser();

//Example 1 - Parse file into array
$example1 = $cal->parse('schedule.ical');
echo '<h2>Example 1</h2>';
pr($example1);

//Example 2 - outputting json
$example2 = $cal->parse('schedule.vcal', 'json');
echo '<h2>Example 2</h2>';
pr($example2);

//Example 3 - parsing remote file
$example3 = $cal->parse('http://bhistc.goalline.ca/subscribe_ical.php?cal=98063-201102-201203-1806-184');
echo '<h2>Example 3</h2>';
pr($example3);

//Example 4 - alternative method to choose file
$cal->set_file_name('icalexample.ics');
$example4 = $cal->parse();
echo '<h2>Example 4</h2>';
pr($example4);

//Example 5 - alternative methods to select path and output
$cal->set_base_path('./');
$cal->set_file_name('icalexample.ics');
$cal->set_output('json');
$example5 = $cal->parse();
echo '<h2>Example 5</h2>';
pr($example5);


function pr($arr) {
    echo '<pre>';

    print_r("--------------------------\n");

    print_r($arr);

    print_r("\n--------------------------");

    echo '</pre>';
}

?>
