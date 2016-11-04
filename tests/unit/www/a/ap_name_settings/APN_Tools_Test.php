<?php
require_once(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/www/a/ap_name_settings/APN_Tools.php');

class APN_ToolsTest extends PHPUnit_Framework_TestCase
{
    //build_post_APN
    public function test_build_post_APN(){
        $exceptions = array("ADV", "AG", "CAD", "CADD", "CTE");
        $excluded_terms = array('(SW)');
        //General
        $err_message = "POST: fundamental error";
        $result = APN_Tools::build_post_APN("Program Title", "Secondary Course Name", $exceptions, $excluded_terms);
        $this->assertEquals("Program Title - Secondary Course Name", $result, $err_message);
    }

    public function test_build_post_APN_casing(){
        $exceptions = array("ADV", "AG", "CAD", "CADD", "CTE");
        $excluded_terms = array('(SW)');
        //Proper casing
        $err_message = "POST: build APN casing error";
        $result = APN_Tools::build_post_APN("PROGRAM TITLE", "SECONDARY COURSE NAME", $exceptions, $excluded_terms);
        $this->assertEquals("Program Title - Secondary Course Name", $result, $err_message);
    }

    public function test_build_post_APN_exceptions(){
        $exceptions = array("ADV", "AG", "CAD", "CADD", "CTE");
        $excluded_terms = array('(SW)');
        //Respect the exceptions for proper casing.
        $err_message = "POST: exception not respected";
        $result = APN_Tools::build_post_APN("Course ADV", "Secondary Course Name CTE", $exceptions, $excluded_terms);
        $this->assertEquals("Course ADV - Secondary Course Name CTE", $result, $err_message);
    }

    public function test_build_post_APN_excluded_terms(){
        $exceptions = array("ADV", "AG", "CAD", "CADD", "CTE");
        $excluded_terms = array('(SW)');
        //Respect the exceptions for proper casing.
        $err_message = "POST: Excluded terms not removed (possibly double spacing issue)";
        $result = APN_Tools::build_post_APN("Course (SW)", "Secondary (SW) Course Name", $exceptions, $excluded_terms);
        $this->assertEquals("Course - Secondary Course Name", $result, $err_message);

        //Respect the exceptions for proper casing.
        $err_message = "POST: Excluded terms not removed when term is not seperated by whitespace";
        $result = APN_Tools::build_post_APN("Course(SW)", "Secondary (SW)Course Name", $exceptions, $excluded_terms);
        $this->assertEquals("Course - Secondary Course Name", $result, $err_message);
    }

    public function test_build_post_APN_spacing(){
        $exceptions = array("ADV", "AG", "CAD", "CADD", "CTE");
        $excluded_terms = array('(SW)');
        //Treat spaces, underscores, and dashes the same.
        $err_message = "POST: Needs to be trimmed";
        $result = APN_Tools::build_post_APN(" Primary", "Secondary ", $exceptions, $excluded_terms);
        $this->assertEquals("Primary - Secondary", $result, $err_message);

        //Treat spaces, underscores, and dashes the same.
        $err_message = "POST: Underscores, dashes, and spaces are not treated equally";
        $result = APN_Tools::build_post_APN("Computer Aided-Drafting_Course", "Secondary Course-Name_Name", $exceptions, $excluded_terms);
        $this->assertEquals("Computer Aided Drafting Course - Secondary Course Name Name", $result, $err_message);

        //Treat spaces, underscores, and dashes the same.
        $err_message = "POST: Multi spacing not handled";
        $result = APN_Tools::build_post_APN("Computer   Drafting", "Secondary    Course", $exceptions, $excluded_terms);
        $this->assertEquals("Computer Drafting - Secondary Course", $result, $err_message);
    }

    public function test_build_post_APN_spec_chars(){
        $exceptions = array("ADV", "AG", "CAD", "CADD", "CTE");
        $excluded_terms = array('(SW)');

        //Ignore special characters when matching APN exceptions.
        $err_message = "POST: Dots in the exception";
        $result = APN_Tools::build_post_APN("Program Title C.A.D.", "Secondary Course Name C.T.E.", $exceptions, $excluded_terms);
        $this->assertEquals("Program Title CAD - Secondary Course Name CTE", $result, $err_message);

        //Ignore special characters when matching APN exceptions.
        $err_message = "POST: Astrisks in the exception";
        $result = APN_Tools::build_post_APN("Program Title C*A*D*", "Secondary Course Name C*T*E*", $exceptions, $excluded_terms);
        $this->assertEquals("Program Title CAD - Secondary Course Name CTE", $result, $err_message);
    }

    //build_roadmap_APN
    public function test_build_roadmap_APN(){
        $exceptions = array("AAA");
        $excluded_terms = array('(BBB)');
        //General
        $err_message = "Roadmap: fundamental error";
        $result = APN_Tools::build_roadmap_APN("Administrative Office Professional", $exceptions, $excluded_terms);
        $this->assertEquals("Administrative Office Professional", $result, $err_message);
    }

    public function test_build_roadmap_APN_casing(){
        $exceptions = array("AAA");
        $excluded_terms = array('(BBB)');
        //General
        $err_message = "Roadmap: Casing error";
        $result = APN_Tools::build_roadmap_APN("aDMINISTRATIVE oFFICE pROFESSIONAL", $exceptions, $excluded_terms);
        $this->assertEquals("Administrative Office Professional", $result, $err_message);
    }

    public function test_build_roadmap_APN_exceptions(){
        $exceptions = array("AAA");
        $excluded_terms = array('(BBB)');
        //General
        $err_message = "Roadmap: Exception error";
        $result = APN_Tools::build_roadmap_APN("AAA Admin", $exceptions, $excluded_terms);
        $this->assertEquals("AAA Admin", $result, $err_message);
    }

    public function test_build_roadmap_APN_excluded_terms(){
        $exceptions = array("AAA");
        $excluded_terms = array('(BBB)');
        //General
        $err_message = "Roadmap: Excluded terms error";
        $result = APN_Tools::build_roadmap_APN("Admin (BBB)", $exceptions, $excluded_terms);
        $this->assertEquals("Admin", $result, $err_message);
    }

    public function test_build_roadmap_APN_spacing(){
        $exceptions = array("AAA");
        $excluded_terms = array('(BBB)');
        //General
        $err_message = "Roadmap: Needs to be trimmed";
        $result = APN_Tools::build_roadmap_APN(" Admin ", $exceptions, $excluded_terms);
        $this->assertEquals("Admin", $result, $err_message);

        $err_message = "Roadmap: Underscores, dashes, and spaces are not treated equally";
        $result = APN_Tools::build_roadmap_APN("Admin Admin-Admin_Admin", $exceptions, $excluded_terms);
        $this->assertEquals("Admin Admin Admin Admin", $result, $err_message);

        $err_message = "Roadmap: Multi spacing not handled";
        $result = APN_Tools::build_roadmap_APN("Admin   Admin", $exceptions, $excluded_terms);
        $this->assertEquals("Admin Admin", $result, $err_message);
    }

    public function test_build_roadmap_APN_special_characters(){
        $exceptions = array("AAA");
        $excluded_terms = array('(BBB)');
        //General
        $err_message = "Roadmap: Dots in the exception";
        $result = APN_Tools::build_roadmap_APN("A.A.A.", $exceptions, $excluded_terms);
        $this->assertEquals("AAA", $result, $err_message);

        $err_message = "Roadmap: Astrisks in the exception";
        $result = APN_Tools::build_roadmap_APN("A*A*A*", $exceptions, $excluded_terms);
        $this->assertEquals("AAA", $result, $err_message);
    }

}

