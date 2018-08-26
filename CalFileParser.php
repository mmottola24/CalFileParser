<?php

/*
 * CalFileParser
 *
 * Parser for iCal and vCal files. Reads event information and
 * outputs data into an Array or JSON
 *
 * @author Michael Mottola <mikemottola@gmail.com>
 * @license MIT
 * @version 1.0
 *
 */

class CalFileParser {

    private $_base_path = './';
    private $_file_name = '';
    private $_output = 'array';
    private $DTfields = array('DTSTART', 'DTEND', 'DTSTAMP', 'CREATED', 'EXDATE', 'LAST-MODIFIED');
    private $_user_timezone = null;
    private $_file_timezone = null;

    function __construct() {
        $this->_default_output = $this->_output;
    }

    public function set_base_path($path) {
        if (isset($path)) {
            $this->_base_path = $path;
        }
    }

    public function set_file_name($filename) {
        if (!empty($filename)) {
            $this->_file_name = $filename;
        }
    }

    public function set_output($output) {
        if (!empty($output)) {
            $this->_output = $output;
        }
    }

    public function set_timezone($timezone) {
        if (!empty($timezone)) {
            $this->_user_timezone = $timezone;
        }
    }

    public function get_base_path() {
        return $this->_base_path;
    }

    public function get_file_name() {
        return $this->_file_name;
    }

    public function get_output() {
        return $this->_output;
    }

    /**
     * Read File
     *
     * @param string $file
     * @return string
     *
     * @example
     *  read_file('schedule.vcal')
     *  read_file('../2011-08/'schedule.vcal');
     *  read_file('http://michaelencode.com/example.vcal');
     */
    public function read_file($file = '') {

        if (empty($file)) {
            $file = $this->_file_name;
        }

        // check to see if file path is a url
        if (preg_match('/^(http|https):/', $file) === 1) {
            return $this->read_remote_file($file);
        }

        //empty base path if file starts with forward-slash
        if (substr($file, 0, 1) === '/') {
            $this->set_base_path('');
        }

        if (!empty($file) && file_exists($this->_base_path . $file)) {
            $file_contents = file_get_contents($this->_base_path . $file);
            return $file_contents;
        } else {
            return false;
        }
    }

    /**
     * Read Remote File
     * @param $file
     * @return bool|string
     */
    public function read_remote_file($file) {
        if (!empty($file)) {
            $data = file_get_contents($file);
            if ($data !== false) {
                return $data;
            }
        }
        return false;
    }

    /**
     * Parse
     * Parses iCal or vCal file and returns data of a type that is specified
     * @param string $file
     * @param string $output
     * @return mixed|string
     */
    public function parse($file = '', $output = '') {
        $file_contents = $this->read_file($file);

        if ($file_contents === false) {
            return 'Error: File Could not be read';
        }

        if (empty($output)) {
            $output = $this->_output;
        }

        if (empty($output)) {
            $output = $this->_default_output;
        }

        $events_arr = array();


        // fetch timezone to create datetime object
        if (preg_match('/X-WR-TIMEZONE:(.+)/i', $file_contents, $timezone) === 1) {
            $this->_file_timezone = trim($timezone[1]);
            if ($this->_user_timezone == null) {
                $this->_user_timezone = $this->_file_timezone;
            }
        } else {
            $this->_file_timezone = $this->_user_timezone;
        }

        // tell user if setting timezone is necessary
        if ($this->_user_timezone == null) {
            return 'Error: no timezone set or found';
        }

        //put contains between start and end of VEVENT into array called $events
        preg_match_all('/(BEGIN:VEVENT.*?END:VEVENT)/si', $file_contents, $events);

        if (!empty($events)) {
            foreach ($events[0] as $event_str) {

                //remove begin and end "tags"
                $event_str = trim(str_replace(array('BEGIN:VEVENT','END:VEVENT'),'',$event_str));

                //convert string of entire event into an array with elements containing string of 'key:value'
                $event_key_pairs = $this->convert_event_string_to_array($event_str);

                //convert array of 'key:value' strings to an array of key => values
                $events_arr[] = $this->convert_key_value_strings($event_key_pairs);
            }
        }

        $this->_output = $this->_default_output;

        return $this->output($events_arr, $output);
    }

    /**
     * Output
     * outputs data in the format specified
     *
     * @param $events_arr
     * @param string $output
     * @return mixed
     */
    private function output($events_arr, $output = 'array') {
        switch ($output) {
            case 'json' :
                return json_encode($events_arr);
                break;
            default :
                return $events_arr;
                break;
        }
    }

    /**
     * Convert event string to array
     * accepts a string of calendar event data and produces array of 'key:value' strings
     * See convert_key_value_strings() to convert strings to
     * @param string $event_str
     * @return array
     */
    private function convert_event_string_to_array($event_str = '') {
        if (!empty($event_str)) {
            //replace new lines with a custom delimiter
            $event_str = preg_replace("/[\r\n]/", "%%" ,$event_str);

            // take care of line wrapping
            $event_str = preg_replace("/%%%% /", "" ,$event_str);

            if (strpos(substr($event_str, 2), '%%') == '0') { //if this code is executed, then file consisted of one line causing previous tactic to fail
                $tmp_piece = explode(':',$event_str);
                $num_pieces = count($tmp_piece);

                $event_str = '';
                foreach ($tmp_piece as $key => $item_str) {

                    if ($key != ($num_pieces -1) ) {

                        //split at spaces
                        $tmp_pieces = preg_split('/\s/',$item_str);

                        //get the last whole word in the string [item]
                        $last_word = end($tmp_pieces);

                        //adds delimiter to front and back of item string, and also between each new key
                        $item_str = trim(str_replace(array($last_word,' %%' . $last_word),array('%%' . $last_word . ':', '%%' . $last_word), $item_str));
                    }

                    //build the event string back together, piece by piece
                    $event_str .= trim($item_str);
                }
            }

            //perform some house cleaning just in case
            $event_str = str_replace('%%%%','%%', $event_str);

            if (substr($event_str, 0, 2) == '%%') {
                $event_str = substr($event_str, 2);
            }

            //break string into array elements at custom delimiter
            $return = explode('%%',$event_str);
        } else {
            $return = array();
        }

        return $return;
    }

    /**
     * Parse Key Value String
     * accepts an array of strings in the format of 'key:value' and returns an array of keys and values
     * @param array $event_key_pairs
     * @return array
     */
    private function convert_key_value_strings($event_key_pairs = array()) {
        $event = array();
        $event_alarm = array();
        $event_alarms = array();
        $inside_alarm = false;

        if (!empty($event_key_pairs)) {
            foreach ($event_key_pairs as $line) {

                if (empty($line)) continue;

                $line_data = explode(':', $line, 2);
                $key = trim((isset($line_data[0])) ? $line_data[0] : "");
                $value = trim((isset($line_data[1])) ? $line_data[1] : "");

                // we are parsing an alarm for this event
                if ($key == "BEGIN" && $value == "VALARM") {
                    $inside_alarm = true;
                    $event_alarm = array();
                    continue;
                }

                // we finished parsing an alarm for this event
                if ($key == "END" && $value == "VALARM") {
                    $inside_alarm = false;
                    $event_alarms[] = $event_alarm;
                    continue;
                }

                // autoconvert datetime fields to DateTime object
                $date_key = (strstr($key,";")) ? strstr($key,";", true) : $key;
                $date_format = (strstr($key,";")) ? strstr($key,";") : ";VALUE=DATE-TIME";

                if (in_array($date_key, $this->DTfields)) {

                    // set date key without format
                    $key = $date_key;

                    $timezone = $this->_file_timezone;

                    // found time zone in date format info
                    if (strstr($date_format,"TZID")) {
                        $strstr = strstr($date_format,"TZID");
                        $timezone = substr($strstr, 5);
                    }
                    
                    // process all dates if there are more then one and comma seperated
                    $processed_value = array();
                    foreach(explode(",", $value) AS $date_value) {

                        // this is simply a date
                        if ($date_format == ";VALUE=DATE") $date_value .= "T000000";

                        // date-time in UTC
                        if (substr($date_value, -1) == "Z") $timezone = "UTC";

                        // format date
                        $date = DateTime::createFromFormat('Ymd\THis', str_replace('Z', '', $date_value), new DateTimeZone($timezone));
                        if ($date !== false) $date->setTimezone(new DateTimeZone($this->_user_timezone));

                        if ($date !== false) $processed_value[] = $date;
                    }

                    // we have more then one date value then return it as an array
                    if (count($processed_value) > 1) {
                        $value = $processed_value;
                    } else {
                        if ($date !== false) $value = $date;
                    }
                }

                // check if current key was already set
                // if this is the case then add value data and turn it into an array
                $value_current_key = false;
                if ($inside_alarm) {
                    if (isset($event_alarm[$key])) $value_current_key = $event_alarm[$key];
                } else {
                    if (isset($event[$key])) $value_current_key = $event[$key];
                }

                // this current key already has data add more
                if ($value_current_key !== false) {

                    // check if data is array and merge
                    if (is_array($value_current_key)) {
                        if (is_array($value)) {
                            $value = array_merge($value_current_key, $value);
                        } else {
                            $value = array_merge($value_current_key, array($value));
                        }
                    } else {
                        if (is_array($value)) {
                            $value = array_merge(array($value_current_key), $value);
                        } else {
                            $value = array($value_current_key, $value);
                        }
                    }
                }

                if ($inside_alarm) {
                    $event_alarm[$key] = $value;
                } else {
                    $event[$key] = $value;
                }
            }
        }

        // add alarm data
        $event["VALARM"] = $event_alarms;

        // unescape every element if string.
        return array_map(function($value) {
            return (is_string($value) ? stripcslashes($value) : $value);
        }, $event);
    }
}
