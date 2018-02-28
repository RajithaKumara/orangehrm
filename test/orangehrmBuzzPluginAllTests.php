<?php

class orangehrmBuzzPluginAllTests {

    public static function suite() {

        $suite = new PHPUnit_Framework_TestSuite('orangehrmBuzzPluginAllTest');

        // plugin class function test
        $suite->addTestFile(dirname(__FILE__) . '/model/dao/LikeOnCommentTest.php');

        $suite->addTestFile(dirname(__FILE__) . '/model/dao/BuzzDaoTest.php');
        $suite->addTestFile(dirname(__FILE__) . '/model/service/BuzzServiceTest.php');
        $suite->addTestFile(dirname(__FILE__) . '/model/service/BuzzConfigServiceTest.php');
        $suite->addTestFile(dirname(__FILE__) . '/model/service/BuzzTextParserServiceTest.php');
           
        // utility test cases
        //$suite->addTestFile(dirname(__FILE__) . '/model/utility/BuzzWebServiceHelperTest.php');
        //$suite->addTestFile(dirname(__FILE__) . '/model/utility/BuzzObjectBuilderTest.php');
        $suite->addTestFile(dirname(__FILE__) . '/model/utility/BuzzTimezoneUtilityTest.php');

        return $suite;
    }
}

