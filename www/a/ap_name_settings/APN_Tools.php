<?php

require_once dirname(dirname(dirname(dirname(__FILE__)))).'/common/string_tools/String_Tools.php';

class APN_Tools
{
    public static function build_post_APN(
        $program_title,
        $secondary_course_name,
        $exceptions,
        $exclusions
    ) {
        foreach ($exclusions as $e) {
            if (strstr($program_title, $e)) {
                $program_title = preg_replace($e, '', $program_title);
            }
            if (strstr($secondary_course_name, $e)) {
                $secondary_course_name = preg_replace($e, '', $secondary_course_name);
            }
        }

        //clean spacing and explode the two strings into arrays of words.
        $program_title = String_Tools::clean_spacing($program_title);
        $secondary_course_name = String_Tools::clean_spacing($secondary_course_name);

        //check if any of the words are on the exception list and properly case them if they are not
        $program_title = String_Tools::prop_case_exceptions(
            String_Tools::clean($program_title),
            String_Tools::clean($exceptions)
        );
        $secondary_course_name = String_Tools::prop_case_exceptions(
            String_Tools::clean($secondary_course_name),
            String_Tools::clean($exceptions)
        );

        //Seperate the two inputs by a " - "
        $apn = implode(' - ', array($program_title, $secondary_course_name));

        //remove multi-spaces
        $apn = String_Tools::remove_multi_space($apn);
        $apn = trim($apn);

        return $apn;
    }

    public static function build_roadmap_APN(
        $apn,
        $exceptions,
        $exclusions
    ) {
        //clean spacing
        $apn = String_Tools::clean_spacing($apn);

        //remove excluded terms from APN
        foreach ($exclusions as $e) {
            $apn = preg_replace($e, '', $apn);
        }

        //properly case all words, keeping exceptions in mind.
        $apn = String_Tools::prop_case_exceptions(
            String_Tools::clean($apn),
            String_Tools::clean($exceptions)
        );

        //Clean up multi spacing for, trim, and return APN
        $apn = String_Tools::remove_multi_space($apn);
        $apn = trim($apn);

        return $apn;
    }
}
