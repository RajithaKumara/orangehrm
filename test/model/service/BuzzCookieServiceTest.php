<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BuzzCookieServiceTest
 *
 * @author dewmal
 */
class BuzzCookieServiceTest extends PHPUnit_Framework_TestCase {

    private $buzzCookieService;

    /**
     * Set up method
     */
    protected function setUp() {
        $this->buzzCookieService = new BuzzCookieService();
    }
    
    
    public function testGetCookieManager() {
        $cookieManager = $this->buzzCookieService->getCookieManager();
        $this->assertTrue($cookieManager instanceof CookieManager);
    }
    
    public function testGetBuzzConfigService() {
        $buzzConfigService = $this->buzzCookieService->getBuzzConfigService();
        $this->assertTrue($buzzConfigService instanceof BuzzConfigService);
    }

    /**
     * this is function to test saving post in the database
     */
    public function testSavePost() {
        $post = New Post();
        $buzzDao = $this->getMock('buzzDao', array('savePost'));
        $buzzDao->expects($this->once())
                ->method('savePost')
                ->with($post)
                ->will($this->returnValue($post));
        $this->buzzService->setBuzzDao($buzzDao);

        $result = $this->buzzService->savePost($post);
        $this->assertTrue($result instanceof Post);
    }

}
