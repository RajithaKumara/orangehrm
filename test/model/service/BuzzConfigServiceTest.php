<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * @group buzz
 */
class BuzzConfigServiceTest extends PHPUnit_Framework_TestCase {

    private $buzzConfigService;

    /**
     * Set up method
     */
    protected function setUp() {

        $this->buzzConfigService = new BuzzConfigService();
        TestDataService::populate(sfConfig::get('sf_plugins_dir') . '/orangehrmBuzzPlugin/test/fixtures/OrangeBuzz.yml');
    }
    /**
     * test ititial share count onfiguration
     */
    public function testGetInitialShareCount(){
        $count =$this->buzzConfigService->getBuzzShareCount();
        
        $this->assertEquals(10,$count);
    }
    
    /**
     * test ititial comment count onfiguration
     */
    public function testGetInitialCommentCount(){
        $count =$this->buzzConfigService->getBuzzInitialCommentCount();
        
        $this->assertEquals(5,$count);
    }
    
    /**
     * test ititial comment count onfiguration
     */
    public function testGetInitialLikeCount(){
        $count =$this->buzzConfigService->getBuzzLikeCount();
        
        $this->assertEquals(5,$count);
    }
    
    /**
     * test ititial refresh Configuration
     */
    public function testGetRefreshTime(){
        $count =$this->buzzConfigService->getRefreshTime();
        
        $this->assertEquals(60000,$count);
    }
    
     /**
     * test ititial comment count onfiguration
     */
    public function testGetInitialTextLenth(){
        $count =$this->buzzConfigService->getBuzzPostTextLenth();
        
        $this->assertEquals(500,$count);
    }
    
    /**
     * test ititial refresh Configuration
     */
    public function testGetTextLines(){
        $count =$this->buzzConfigService->getBuzzPostTextLines();
        
        $this->assertEquals(5,$count);
    }
    
    /**
     * test ititial more comment Configuration
     */
    public function testGetViewMoreComment(){
        $count =$this->buzzConfigService->getBuzzViewCommentCount();
        
        $this->assertEquals(5,$count);
    }
    
    /**
     * test ititial more comment Configuration
     */
    public function testGetTimeFormal(){
        $result =$this->buzzConfigService->getTimeFormat();
        
        $this->assertEquals('h:i',$result);
    }
    
    /**
     * test ititial more comment Configuration
     */
    public function testGetMostLikePostCount(){
        $count =$this->buzzConfigService->getMostLikePostCount();
        
        $this->assertEquals(5,$count);
    }
    
    /**
     * test ititial more comment Configuration
     */
    public function testGetMostLikeShareCount(){
        $count =$this->buzzConfigService->getMostLikeShareCount();
        
        $this->assertEquals(5,$count);
    }
    
     /**
     * test ititial more comment Configuration
     */
    public function testGetPostShareCount(){
        $count =$this->buzzConfigService->getPostShareCount();
        
        $this->assertEquals(5,$count);
    }
    
    /**
     * test cookie valid time Configuration
     */
    public function testGetCookieValidTime(){
        $cookieTime =$this->buzzConfigService->getCookieValidTime();
        
        $this->assertEquals(5000,$cookieTime);
    }
}
