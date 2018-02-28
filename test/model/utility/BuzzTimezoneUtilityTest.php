<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BuzzTimezoneUtilityTest
 *
 * @author amila
 * @group buzz
 */
class BuzzTimezoneUtilityTest extends PHPUnit_Framework_TestCase {

    private $buzzTimezoneUtility;

    /**
     * Set up method
     */
    protected function setUp() {
        $this->buzzTimezoneUtility = new BuzzTimezoneUtility();
    }

    public function testGetTimeZoneFromClientOffset() {
        $content = array(
            '5.5'=>'+5:30',
            '2.25'=>'+2:15',
            '-5.5'=>'-5:30',
            '+5.75'=>'+5:45',
            );
        foreach ($content as $offset => $expected){
         $this->assertEquals($expected, $this->buzzTimezoneUtility->gettimeZoneFromClientOffset($offset));
        }

        $content = array(
            '5.5'=>'5:30',
        );
        foreach ($content as $offset => $expected){
            $this->assertNotEquals($expected, $this->buzzTimezoneUtility->gettimeZoneFromClientOffset($offset));
        }
    }
}
