<?php
require_once('../CalFileParser.php');
//require_once('PHPUnit.php');

class CalFileParserTest extends PHPUnit_Framework_TestCase {

    public $parser;

    function setUp() {
        $this->parser = new CalFileParser();
    }

    function tearDown() {
        unset($this->parser);
    }

    /**
     * @dataProvider base_path_provider
     */
    public function testSetBasePath($path) {
        
        $this->assertNotEmpty($path, 'Path is Not Empty');
        
        $this->parser->set_base_path('/my_path/');
    }
    
    public function base_path_provider() {
        return array(
            array('path'),
            array('/my_path'),
            array('')
        );
    }

    /**
     * @depends testSetBasePath
     */
    function testGetBasePath() {
        $result = $this->parser->get_base_path();

        $this->assertEquals($result, '/my_path/', 'Base Path was not Set');

        return true;
    }
}
