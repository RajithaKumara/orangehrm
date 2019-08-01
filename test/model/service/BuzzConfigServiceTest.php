<?php

/**
 * OrangeHRM Enterprise is a closed sourced comprehensive Human Resource Management (HRM)
 * System that captures all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 OrangeHRM Inc., http://www.orangehrm.com
 *
 * OrangeHRM Inc is the owner of the patent, copyright, trade secrets, trademarks and any
 * other intellectual property rights which subsist in the Licensed Materials. OrangeHRM Inc
 * is the owner of the media / downloaded OrangeHRM Enterprise software files on which the
 * Licensed Materials are received. Title to the Licensed Materials and media shall remain
 * vested in OrangeHRM Inc. For the avoidance of doubt title and all intellectual property
 * rights to any design, new software, new protocol, new interface, enhancement, update,
 * derivative works, revised screen text or any other items that OrangeHRM Inc creates for
 * Customer shall remain vested in OrangeHRM Inc. Any rights not expressly granted herein are
 * reserved to OrangeHRM Inc.
 *
 * Please refer http://www.orangehrm.com/Files/OrangeHRM_Commercial_License.pdf for the license which includes terms and conditions on using this software.
 *
 */

/**
 * @group buzz
 */
class BuzzConfigServiceTest extends PHPUnit\Framework\TestCase {

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
    public function testgetAllBuzzValues(){
        $returnValue =$this->buzzConfigService->setAllBuzzValues();
        
        $this->assertEquals(TRUE,$returnValue);
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
     * Test getting comment length 
     */
    public function testGetCommentLength(){
        $count =$this->buzzConfigService->getBuzzCommentTextLenth();
        
        $this->assertEquals(250,$count);
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
        
        $this->assertEquals(500098,$cookieTime);
    }
}
