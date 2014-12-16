<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * @group buzz
 */
class BuzzTextParserServiceTest extends PHPUnit_Framework_TestCase{
    protected function setUp() {

        $this->buzzTextParserService = new BuzzTextParserService();
        
    }
    
    /**
     * testing image url is correct
     */
    public function testImageUrl(){
        $url='http://www.jpl.nasa.gov/spaceimages/images/mediumsize/PIA17011_ip.jpg';
        $result=BuzzTextParserService::isImage($url);
        $this->assertEquals(true,$result);
    }
    
    /**
     * testing image url is correct
     */
    public function testTextWithUrl(){
        $url='http://www.jpl.nasa.gov/spaceimages/images/mediumsize/';
        $result=BuzzTextParserService::parseText($url);
        $trueResult= "<a href=\"{$url}\" target=\"_blank\">{$url}</a> ";
        
        $this->assertEquals($result,$trueResult);
    }
    
    /**
     * testing image url is correct
     */
    public function testTextWithEmoticals(){
        $url=':)';
        $result=BuzzTextParserService::parseText($url);
        $trueResult= '<img src="' .
                    plugin_web_path('orangehrmBuzzPlugin', 'images/emoticons/') . 'smile.ico' .
                    '" height="18" width="18" />';
        
        $this->assertEquals($result,$trueResult);
    }
    
     /**
     * testing image url is correct
     */
    public function testTextWithImageUrl(){
        $url='http://www.jpl.nasa.gov/spaceimages/images/mediumsize/PIA17011_ip.jpg';
        $result=BuzzTextParserService::parseText($url);
        $trueResult= "<img src=\"".$url."\" height=\"100px\" >";
        
        $this->assertEquals($result,$trueResult);
    }
    
    public function testImageUrlErro(){
        $url='http://www.jpl.nasa.gov/spaceimages/images/mediumsize/PIA17011_';
        $result=BuzzTextParserService::isImage($url);
        $this->assertEquals(false,$result);
    }
}
