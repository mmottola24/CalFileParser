<?php

/*
 * CalFileParser
 *
 * @author Michael Mottola <info@michaelencode.com>
 * @license MIT
 * @version 1.0
 */

class CalFileParser {

    private $_base_path = './';
    
    function __construct() {
        
    }

    function set_base_path($path) {
        $this->_base_path = $path;
    }

    function get_base_path($path) {
        return $this->_base_path;
    }

    /**
     * Read File
     *
     * @param $file
     * @return string
     *
     * @example
     *  read_file('schedule.vcal')
     *  read_file('../2011-08/'schedule.vcal');
     *  read_file('http://michaelencode.com/example.vcal');
     */
    protected function read_file($file) {

        if (file_exists($this->_base_path . $file)) {

            $file_contents = file_get_contents($this->_base_path . $file);

            return $file_contents;
            
        } else {
            return false;
        }


    }

    /**
     * Parse
     * Parses iCal or vCal file and returns data of a type that is specified
     * @param $file
     * @param string $output
     * @return mixed|string
     */
    public function parse($file, $output = 'array') {
        
        $file_contents = $this->read_file($file);

        if ($file_contents === false) {
            return 'Error: File Could not be read';
        }

        $events_arr = array();

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

            //replace first and last delimiter
            $event_str = substr($event_str, 2, -2);

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

        if (!empty($event_key_pairs)) {

            $num_key_pairs = count($event_key_pairs);

            for ($i = 0; $i < $num_key_pairs; $i++) {

                $tmp_arr =  explode(':',$event_key_pairs[$i]);

                $key = trim($tmp_arr[0]);
                $value = trim($tmp_arr[1]);

                $event[$key] = $value;

            }
        }

        return $event;

    }

}
?> 