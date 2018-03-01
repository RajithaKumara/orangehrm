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
        $this->defualtUser = sfContext::getInstance()->getUser();
        $this->defualtServerTimeZone = date_default_timezone_get();
    }

    public function tearDown(){
        sfContext::getInstance()->set('user',$this->defualtUser);
        date_default_timezone_set($this->defualtServerTimeZone);
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

    public function testGetShareTime(){
        date_default_timezone_set('America/Los_Angeles');
        sfContext::getInstance()->getUser()->setAttribute('system.timeZoneOffset', 1);
        $share = new Share();
        $share->setShareTime('2018-03-01 11:17:18');
        $shareTime = $share->getShareTime();
        $this->assertEquals('2018-03-01 20:17:18', $shareTime, 'share time for client timezone is incorrect');
    }

    public function testGetShareTimeForGMT(){
        date_default_timezone_set('America/Los_Angeles');
        sfContext::getInstance()->getUser()->setAttribute('system.timeZoneOffset', 0);
        $share = new Share();
        $share->setShareTime('2018-03-01 11:17:18');
        $shareTime = $share->getShareTime();
        $this->assertEquals('2018-03-01 19:17:18', $shareTime, 'share time for client timezone is incorrect');
    }

    public function testGetShareTimeForMinusOffset(){
        date_default_timezone_set('America/Los_Angeles');
        sfContext::getInstance()->getUser()->setAttribute('system.timeZoneOffset', -4);
        $share = new Share();
        $share->setShareTime('2018-03-01 11:17:18');
        $shareTime = $share->getShareTime();
        $this->assertEquals('2018-03-01 15:17:18', $shareTime, 'share time for client timezone is incorrect');
    }

    public function testGetCommentTime(){
        date_default_timezone_set('America/Los_Angeles');
        sfContext::getInstance()->getUser()->setAttribute('system.timeZoneOffset', 1);
        $comment = new Comment();
        $comment->setCommentTime('2018-03-01 11:17:18');
        $commentTime = $comment->getCommentTime();
        $this->assertEquals('2018-03-01 20:17:18', $commentTime, 'comment time for client timezone is incorrect');
    }

    public function testGetCommentTimeForGMT(){
        date_default_timezone_set('America/Los_Angeles');
        sfContext::getInstance()->getUser()->setAttribute('system.timeZoneOffset', 0);
        $comment = new Comment();
        $comment->setCommentTime('2018-03-01 11:17:18');
        $commentTime = $comment->getCommentTime();
        $this->assertEquals('2018-03-01 19:17:18', $commentTime, 'comment time for client timezone is incorrect');
    }

    public function testGetCommentTimeMinusOffset(){
        date_default_timezone_set('America/Los_Angeles');
        sfContext::getInstance()->getUser()->setAttribute('system.timeZoneOffset', -4);
        $comment = new Comment();
        $comment->setCommentTime('2018-03-01 11:17:18');
        $commentTime = $comment->getCommentTime();
        $this->assertEquals('2018-03-01 15:17:18', $commentTime, 'comment time for client timezone is incorrect');
    }
}
