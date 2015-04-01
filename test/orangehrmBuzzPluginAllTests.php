<?php

class orangehrmBuzzPluginAllTests {

    protected function setUp() {
        
    }

    public static function suite() {

        $suite = new PHPUnit_Framework_TestSuite('orangehrmBuzzPluginAllTest');


        $suite->addTestFile(dirname(__FILE__) . '/model/dao/BuzzDaoTest.php');
       $suite->addTestFile(dirname(__FILE__) . '/model/service/BuzzServiceTest.php');
       $suite->addTestFile(dirname(__FILE__) . '/model/service/BuzzConfigServiceTest.php');
       $suite->addTestFile(dirname(__FILE__) . '/model/service/BuzzTextParserServiceTest.php');
       $suite->addTestFile(dirname(__FILE__) . '/model/service/BuzzCookieServiceTest.php');
<<<<<<< .working
       
       // utility test cases
       $suite->addTestFile(dirname(__FILE__) . '/model/utility/BuzzWebServiceHelperTest.php');
       $suite->addTestFile(dirname(__FILE__) . '/model/utility/BuzzObjectBuilderTest.php');
       
=======
       
       // utility service test
       $suite->addTestFile(dirname(__FILE__) . '/model/utility/BuzzWebServiceHelperTest.php');
       $suite->addTestFile(dirname(__FILE__) . '/model/utility/BuzzObjectBuilderTest.php');
       
>>>>>>> .merge-right.r63046
        return $suite;
    }

    public static function main() {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

}

if (PHPUnit_MAIN_METHOD == 'orangehrmBuzzPluginAllTests::main') {
    orangehrmBuzzPluginAllTests::main();
}

