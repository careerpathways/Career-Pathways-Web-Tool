<?php
require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/common/string_tools/String_Tools.php');

class String_ToolsTest extends PHPUnit_Framework_TestCase
{

    public function test_clean(){
        //Remove special chars
        $err_message = "special characters causing error";
        $result = String_Tools::clean("wo_+-.,!@#$%^&*();\/|<>\"'rd");
        $this->assertEquals("word", $result, $err_message);
    }

    public function test_clean_spacing(){
        //Remove special chars
        $err_message = "spacing not cleaned correctly";
        $result = String_Tools::clean_spacing("word word-word_word");
        $this->assertEquals("word word word word", $result, $err_message);
    }

    public function test_prop_case(){
        $err_message = "Proper casing single word";
        $result = String_Tools::prop_case("pROPER");
        $this->assertEquals("Proper", $result, $err_message);

        $err_message = "Proper casing multi word";
        $result = String_Tools::prop_case("pROPER cASE");
        $this->assertEquals("Proper Case", $result, $err_message);
    }

    public function test_prop_case_exeptions(){
        $exceptions = array("ADV", "AG", "CAD", "CADD", "CTE");

        //Proper casing no exceptions
        $err_message = "Proper casing error (not exception)";
        $result = String_Tools::prop_case_exceptions("pROPER CAsing", $exceptions);
        $this->assertEquals("Proper Casing", $result, $err_message);

        //Proper casing with exceptions
        $err_message = "Proper casing error (exception)";
        $result = String_Tools::prop_case_exceptions("Secondary Course Name CTE", $exceptions);
        $this->assertEquals("Secondary Course Name CTE", $result, $err_message);
    }

    public function test_remove_multi_space() {
        //Proper casing
        $err_message = "Multi space not handled correctly";
        $result = String_Tools::remove_multi_space("word     word");
        $this->assertEquals("word word", $result, $err_message);
    }

    public function test_remove_spaces() {
        //Proper casing
        $err_message = "Spaces not removed";
        $result = String_Tools::remove_spaces("word     word");
        $this->assertEquals("wordword", $result, $err_message);
    }

    public function search_string_alphanumerics() {
        //Proper casing
        $err_message = "Search string (alphanumerics) failed";
        $result = String_Tools::remove_multi_space("a a_a+a-a.a,a!a@a#a$a%a^a&a*a(a)a;a\a/a|a<a>a\"a'a", "aaaaaaaaaaaaaaaaaaaaaaaaa");
        $this->assertEquals(true, $result, $err_message);
    }



}
