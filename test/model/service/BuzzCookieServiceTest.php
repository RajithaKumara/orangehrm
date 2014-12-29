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
/**
 * @group buzz
 */
class BuzzCookieServiceTest extends PHPUnit_Framework_TestCase {

    private $buzzCookieService;

    const COOKIE_NAME_EMPLOYE_NUMBER = 'buzzCookieEmployeeNumber';
    const COOKIE_NAME_USER_ROLE = 'buzzCookieUserRole';

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
     * this is function to test get Employee Number
     */
    public function testSaveUserRole() {
        $cookieManager = $this->getMock('CookieManager', array('setCookie'));
        $cookieManager->expects($this->once())
                ->method('setCookie')
                ->with(self::COOKIE_NAME_USER_ROLE)
                ->will($this->returnValue(1));
        $this->buzzCookieService->setCookieManager($cookieManager);

        $result = $this->buzzCookieService->saveUserRole('Admin');
        $this->assertEquals('Admin', $result);
    }

    /**
     * this is function to test get Employee Number
     */
    public function testSaveEmployeeNumber() {
        $cookieManager = $this->getMock('CookieManager', array('setCookie'));
        $cookieManager->expects($this->once())
                ->method('setCookie')
                ->with(self::COOKIE_NAME_EMPLOYE_NUMBER)
                ->will($this->returnValue(1));
        $this->buzzCookieService->setCookieManager($cookieManager);

        $result = $this->buzzCookieService->saveEmployeeNumber(1);
        $this->assertEquals(1, $result);
    }
    
    /**
     * this is function to test get Employee Number
     */
    public function testSaveAdminEmployeeNumber() {
        $cookieManager = $this->getMock('CookieManager', array('setCookie'));
        $cookieManager->expects($this->once())
                ->method('setCookie')
                ->with(self::COOKIE_NAME_EMPLOYE_NUMBER)
                ->will($this->returnValue(1));
        $this->buzzCookieService->setCookieManager($cookieManager);

        $result = $this->buzzCookieService->saveEmployeeNumber(null);
        $this->assertEquals(null, $result);
    }

    /**
     * this is function to test get Employee Number
     */
    public function testGetEmployeeNumber() {
        $cookieManager = $this->getMock('CookieManager', array('readCookie'));
        $cookieManager->expects($this->once())
                ->method('readCookie')
                ->with(self::COOKIE_NAME_EMPLOYE_NUMBER)
                ->will($this->returnValue(1));
        $this->buzzCookieService->setCookieManager($cookieManager);

        $result = $this->buzzCookieService->getEmployeeNumber();
        $this->assertEquals(1,$result);
    }
    
    /**
     * this is function to test get Employee Number
     */
    public function testGetAdminEmployeeNumber() {
        $cookieManager = $this->getMock('CookieManager', array('readCookie'));
        $cookieManager->expects($this->once())
                ->method('readCookie')
                ->with(self::COOKIE_NAME_EMPLOYE_NUMBER)
                ->will($this->returnValue('Admin'));
        $this->buzzCookieService->setCookieManager($cookieManager);

        $result = $this->buzzCookieService->getEmployeeNumber();
        $this->assertEquals(null,$result);
    }

    /**
     * this is function to test get Employee Number
     */
    public function testGetUserRole() {
        $cookieManager = $this->getMock('CookieManager', array('readCookie'));
        $cookieManager->expects($this->once())
                ->method('readCookie')
                ->with(self::COOKIE_NAME_USER_ROLE)
                ->will($this->returnValue('Admin'));
        $this->buzzCookieService->setCookieManager($cookieManager);

        $result = $this->buzzCookieService->getEmployeeUserRole();
        $this->assertEquals('Admin', $result);
    }

    /**
     * this is function to test get Employee Number
     */
    public function testDestroyCookie() {
        $cookieManager = $this->getMock('CookieManager', array('destroyCookie'));

        $cookieManager->expects($this->at(0))
                ->method('destroyCookie')
                ->with(self::COOKIE_NAME_USER_ROLE, "/")
                ->will($this->returnValue(null));
        $cookieManager->expects($this->at(1))
                ->method('destroyCookie')
                ->with(self::COOKIE_NAME_EMPLOYE_NUMBER, "/")
                ->will($this->returnValue(null));
        $this->buzzCookieService->setCookieManager($cookieManager);

        $result = $this->buzzCookieService->destroyCokkies();
        $this->assertTrue($result);
    }
    
    /**
     * this is function to test get Employee Number
     */
    public function testIsSavedCookies() {
        $cookieManager = $this->getMock('CookieManager', array('isCookieSet'));
        $cookieManager->expects($this->once())
                ->method('isCookieSet')
                ->with(self::COOKIE_NAME_USER_ROLE)
                ->will($this->returnValue(true));
        $this->buzzCookieService->setCookieManager($cookieManager);

        $result = $this->buzzCookieService->isSavedCookies();
        $this->assertTrue($result);
    }
    
    /**
     * this is function to test get Employee Number
     */
    public function testSaveCookieValuves() {
        $cookieManager = $this->getMock('CookieManager', array('setCookie'));
        $userRole='Admin';
        $employeeNumber=1;

        $cookieManager->expects($this->at(0))
                ->method('setCookie')
                ->with(self::COOKIE_NAME_USER_ROLE,$userRole )
                ->will($this->returnValue(null));
        $cookieManager->expects($this->at(1))
                ->method('setCookie')
                ->with(self::COOKIE_NAME_EMPLOYE_NUMBER,$employeeNumber)
                ->will($this->returnValue(null));
        $this->buzzCookieService->setCookieManager($cookieManager);

        $result = $this->buzzCookieService->saveCookieValuves($employeeNumber,$userRole);
        $this->assertTrue($result);
    }
    
    /**
     * this is function to test get Employee Number
     */
    public function testIsAdminLoged() {
        $cookieManager = $this->getMock('CookieManager', array('readCookie'));
        $cookieManager->expects($this->once())
                ->method('readCookie')
                ->with(self::COOKIE_NAME_USER_ROLE)
                ->will($this->returnValue('Admin'));
        $this->buzzCookieService->setCookieManager($cookieManager);

        $result = $this->buzzCookieService->isAdminLoged();
        $this->assertTrue($result);
    }
    
    /**
     * this is function to test get Employee Number
     */
    public function testIsAdminLogedFale() {
        $cookieManager = $this->getMock('CookieManager', array('readCookie'));
        $cookieManager->expects($this->once())
                ->method('readCookie')
                ->with(self::COOKIE_NAME_USER_ROLE)
                ->will($this->returnValue('Ess'));
        $this->buzzCookieService->setCookieManager($cookieManager);

        $result = $this->buzzCookieService->isAdminLoged();
        $this->assertTrue(!$result);
    }

}
