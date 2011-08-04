<?php

include('../cal_file_parser.php');

$cal = new CalFileParser();

$data = $cal->parse('schedule.ical','array');


echo '<pre>';
    print_r($data);
echo '</pre>';
