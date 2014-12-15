<?php

/*
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
class BuzzServiceTest extends PHPUnit_Framework_TestCase {

    private $buzzService;

    /**
     * Set up method
     */
    protected function setUp() {

        $this->buzzService = new BuzzService();
        TestDataService::populate(sfConfig::get('sf_plugins_dir') . '/orangehrmBuzzPlugin/test/fixtures/Post.yml');
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

    /**
     * this is function to test getting shares data from the database
     */
    public function testGetShares() {
        $buzzDao = $this->getMock('buzzDao', array('getShares'));

        $buzzDao->expects($this->once())
                ->method('getShares')
                ->with(2)
                ->will($this->returnValue(array()));

         $this->buzzService->setBuzzDao($buzzDao);  
        $result = $this->buzzService->getShares(2);
        $this->assertTrue(is_array($result));
    }

    /**
     * this is function to test getting post from share
     */
    public function testGetPostFromShare() {
        $post= new Post();
        $share= new Share();
        $share->setPostShared($post);
        $buzzDao = $this->getMock('buzzDao', array('getShareById'));

        $buzzDao->expects($this->once())
                ->method('getShareById')
                ->with(1)
                ->will($this->returnValue($share));

         $this->buzzService->setBuzzDao($buzzDao);     
        $result = $this->buzzService->getShareById(1);
        $this->assertTrue($result->getPostShared() instanceof Post);
    }

    /**
     * this is function to test delete post
     */
    public function testDeletePost() {
        $buzzDao = $this->getMock('buzzDao', array('deletePost'));

        $buzzDao->expects($this->once())
                ->method('deletePost')
                ->with('1')
                ->will($this->returnValue('1'));
        $this->buzzService->setBuzzDao($buzzDao);
        $result = $this->buzzService->deletePost('1');

        $this->assertEquals('1', $result);
    }

    /**
     * this is function to test delete shares from the database
     */
    public function testDeleteShare() {
        $buzzDao = $this->getMock('buzzDao', array('deleteShare'));

        $buzzDao->expects($this->once())
                ->method('deleteShare')
                ->with('1')
                ->will($this->returnValue('1'));
        $this->buzzService->setBuzzDao($buzzDao);
        $result = $this->buzzService->deleteShare('1');

        $this->assertEquals(1, $result);
    }

    /**
     * this is function to test save likes on share to database
     */
    public function testLikeOnShare() {
        $like = new LikeOnShare();
        $like->setShareId(1);
        $like->setEmployeeNumber(1);
        $like->setLikeTime('2015-01-10 12:12:12');

        $result = $this->buzzService->saveLikeForShare($like);
        $this->assertEquals('2015-01-10 12:12:12', $result->getLikeTime());
    }

    /**
     * this is function to test delete likes on share
     */
    public function testDeleteLikeOnshare() {
        $like = new LikeOnShare();
        $like->setId(20);
        $like->setShareId(1);
        $like->setEmployeeNumber(1);
        $like->setLikeTime('2015-01-10 12:12:12');

        $result = $this->buzzService->deleteLikeForShare($like);
        $this->assertEquals('1', $result);
    }

    /**
     * this is function to test save comment on share to database
     */
    public function testCommentOnShare() {
        $comment = new Comment();
        $comment->setShareId(1);
        $comment->setEmployeeNumber(1);
        $comment->setCommentTime('2015-01-10 12:12:12');
        $comment->setCommentText('this is the first comment');

        $result = $this->buzzService->saveCommentShare($comment);
        $this->assertEquals('2015-01-10 12:12:12', $result->getCommentTime());
    }

    /**
     * this is function to test delete Comment on share
     */
    public function testDeleteCommentOnShare() {
        $comment = new Comment();
        $comment->setId(1);
        $comment->setShareId(1);
        $comment->setEmployeeNumber(1);
        $comment->setCommentTime('2015-01-10 12:12:12');
        $comment->setCommentText('this is the first comment');

        $result = $this->buzzService->deleteCommentForShare($comment);
        $this->assertEquals('1', $result);
    }

    /**
     * this is function to test save likes on comment
     */
    public function testLikeOnComment() {
        $like = new LikeOnComment();
        $like->setCommentId(1);
        $like->setEmployeeNumber(1);
        $like->setLikeTime('2015-01-10 12:12:12');

        $result = $this->buzzService->saveLikeForComment($like);
        $this->assertEquals('2015-01-10 12:12:12', $result->getLikeTime());
    }

    /**
     * this is function to test delete likes on the comment
     */
    public function testDeletLikeOnComment() {
        $like = new LikeOnComment();
        $like->setId(20);
        $like->setCommentId(1);
        $like->setEmployeeNumber(1);
        $like->setLikeTime('2015-01-10 12:12:12');

        $result = $this->buzzService->deleteLikeForComment($like);
        $this->assertEquals('1', $result);
    }

    /**
     * this is functoin to test get share by id
     */
    public function testGetShareById() {
        
        $share= new Share();
       
        $buzzDao = $this->getMock('buzzDao', array('getShareById'));

        $buzzDao->expects($this->once())
                ->method('getShareById')
                ->with(1)
                ->will($this->returnValue($share));

         $this->buzzService->setBuzzDao($buzzDao);    
        $result = $this->buzzService->getShareById(1);

        $this->assertTrue($result instanceof Share);
    }

    /**
     * this is functoin to test get post by id
     */
    public function testGetPostById() {
        $post= new Post();
       
        $buzzDao = $this->getMock('buzzDao', array('getPostById'));

        $buzzDao->expects($this->once())
                ->method('getPostById')
                ->with(1)
                ->will($this->returnValue($post));

         $this->buzzService->setBuzzDao($buzzDao);    
        $result = $this->buzzService->getPostById(1);

        $this->assertTrue($result instanceof Post);
        
    }

    /**
     * this is functoin to test get Comment by id
     */
    public function testGetCommentById() {
        $comment= new Comment();
        $buzzDao = $this->getMock('buzzDao', array('getCommentById'));

        $buzzDao->expects($this->once())
                ->method('getCommentById')
                ->with(1)
                ->will($this->returnValue($comment));

         $this->buzzService->setBuzzDao($buzzDao); 
        $result = $this->buzzService->getCommentById(1);

        $this->assertTrue($result instanceof Comment);
    }

    /**
     * this is functoin to test get likeOnComment by id
     */
    public function testGetLikeOnCommentById() {
        $like= New LikeOnComment();
        $buzzDao = $this->getMock('buzzDao', array('getLikeOnCommentById'));

        $buzzDao->expects($this->once())
                ->method('getLikeOnCommentById')
                ->with(1)
                ->will($this->returnValue($like));

         $this->buzzService->setBuzzDao($buzzDao); 
        $result = $this->buzzService->getLikeOnCommentById(1);
       

        $this->assertTrue($result instanceof LikeOnComment);
    }

    /**
     * this is functoin to test get likeOnShare by id
     */
    public function testGetLikeOnshareById() {
        $like= New LikeOnShare();
        $buzzDao = $this->getMock('buzzDao', array('getLikeOnShareById'));

        $buzzDao->expects($this->once())
                ->method('getLikeOnShareById')
                ->with(1)
                ->will($this->returnValue($like));

         $this->buzzService->setBuzzDao($buzzDao); 
        $result = $this->buzzService->getLikeOnShareById(1);
       

        $this->assertTrue($result instanceof LikeOnShare);
       
    }
    
    
    /**
     * this is function to test save likes on share to database
     */
    public function testUnLikeOnShare() {
        $like = new UnLikeOnShare();
        $like->setShareId(1);
        $like->setEmployeeNumber(1);
        $like->setLikeTime('2015-01-10 12:12:12');

        $result = $this->buzzService->saveUnLikeForShare($like);
        $this->assertEquals('2015-01-10 12:12:12', $result->getLikeTime());
    }

    /**
     * this is function to test delete likes on share
     */
    public function testDeleteUnLikeOnshare() {
        $like = new UnLikeOnShare();
        $like->setId(20);
        $like->setShareId(1);
        $like->setEmployeeNumber(1);
        $like->setLikeTime('2015-01-10 12:12:12');

        $result = $this->buzzService->deleteUnLikeForShare($like);
        $this->assertEquals('1', $result);
    }
    
    /**
     * this is function to test save likes on comment
     */
    public function testUnLikeOnComment() {
        $like = new UnLikeOnComment();
        $like->setCommentId(1);
        $like->setEmployeeNumber(1);
        $like->setLikeTime('2015-01-10 12:12:12');

        $result = $this->buzzService->saveUnLikeForComment($like);
        $this->assertEquals('2015-01-10 12:12:12', $result->getLikeTime());
    }

    /**
     * this is function to test delete likes on the comment
     */
    public function testDeletUnLikeOnComment() {
        $like = new UnLikeOnComment();
        $like->setId(20);
        $like->setCommentId(1);
        $like->setEmployeeNumber(1);
        $like->setLikeTime('2015-01-10 12:12:12');

        $result = $this->buzzService->deleteUnLikeForComment($like);
        $this->assertEquals('1', $result);
    }
    
     /**
     * this is function to test save likes on comment
     */
    public function testAdminUnLikeOnComment() {
        $like = new UnLikeOnComment();
        $like->setCommentId(1);
        //$like->setEmployeeNumber('');
        $like->setLikeTime('2015-01-10 12:12:12');

        $result = $this->buzzService->saveUnLikeForComment($like);
        $this->assertEquals('2015-01-10 12:12:12', $result->getLikeTime());
    }

    /**
     * this is function to test delete likes on the comment
     */
    public function testDeletAdminUnLikeOnComment() {
        $like = new UnLikeOnComment();
        $like->setId(20);
        $like->setCommentId(1);
        $like->setEmployeeNumber('');
        $like->setLikeTime('2015-01-10 12:12:12');

        $result = $this->buzzService->deleteUnLikeForComment($like);
        $this->assertEquals('0', $result);
    }
    
     /**
     * test 
     */
    public function testGetEmployeeList(){
       
        $result = $this->buzzService->getEmployeeList();
        $this->assertEquals('3', Count($result));
    }
    
     /**
     * test 
     */
    public function testGetEmployeePicture(){
       
        $result = $this->buzzService->getEmployeePicture(1);
        $this->assertTrue($result instanceof EmpPicture);
    }
    
    /**
     * test 
     */
    public function testGetMoreShares(){
       $buzzDao = $this->getMock('buzzDao', array('getMoreShares'));

        $buzzDao->expects($this->once())
                ->method('getMoreShares')
                ->with(1,0)
                ->will($this->returnValue(array()));

         $this->buzzService->setBuzzDao($buzzDao); 
        $result = $this->buzzService->getMoreShares(1,0);
        $this->assertEquals('0', Count($result));
    }
    /**
     * test 
     */
    public function testGetMoreProfileShares() {
        $buzzDao = $this->getMock('buzzDao', array('getMoreProfileShares'));

        $buzzDao->expects($this->once())
                ->method('getMoreProfileShares')
                ->with(1,0,1)
                ->will($this->returnValue(array()));

         $this->buzzService->setBuzzDao($buzzDao); 
        $result = $this->buzzService->getMoreProfileShares(1,0,1);
        $this->assertEquals('0', Count($result));
    }
   
    /**
     * test 
     */
    public function testgetSharesByUserId(){
        $buzzDao = $this->getMock('buzzDao', array('getSharesByUserId'));

        $buzzDao->expects($this->once())
                ->method('getSharesByUserId')
                ->with(1,2)
                ->will($this->returnValue(array()));

         $this->buzzService->setBuzzDao($buzzDao); 
        $result = $this->buzzService->getSharesByUserId(1,2);
        $this->assertEquals('0', Count($result));
    }
    
        
     /**
     * test 
     */
    public function testgetFrofileShareUptoId(){
        $buzzDao = $this->getMock('buzzDao', array('getProfileSharesUptoId'));

        $buzzDao->expects($this->once())
                ->method('getProfileSharesUptoId')
                ->with(1,2)
                ->will($this->returnValue(array()));

         $this->buzzService->setBuzzDao($buzzDao); 
        $result = $this->buzzService->getProfileSharesUptoId(1,2);
        $this->assertEquals('0', Count($result));
    }
    
     /**
     * test 
     */
    public function testgetShareUpToId(){
        $buzzDao = $this->getMock('buzzDao', array('getSharesUptoId'));

        $buzzDao->expects($this->once())
                ->method('getSharesUptoId')
                ->with(1)
                ->will($this->returnValue(array()));

         $this->buzzService->setBuzzDao($buzzDao); 
        $result = $this->buzzService->getSharesUptoId(1);
        $this->assertEquals('0', Count($result));
    }
    
     /**
     * test saving photo
     */
     public function testSavingPhoto() {
        $photo = new Photo();
        $buzzDao = $this->getMock('buzzDao', array('savePhoto'));

        $buzzDao->expects($this->once())
                ->method('savePhoto')
                ->with($photo)
                ->will($this->returnValue($photo));

         $this->buzzService->setBuzzDao($buzzDao); 

        $result = $this->buzzService->savePhoto($photo);
        $this->assertTrue($result instanceof Photo);
    }
      
    /**
     * this is functoin to test get Employee by id
     */
    public function testGetEmployeeByNumber() {
        $employee = $this->buzzService->getEmployee(1);

        $this->assertEquals(1, $employee->getEmpNumber());
    }
    /**
     * test shares by user Id
     */
    public function testGetNoOfSharesBy(){
        $buzzDao = $this->getMock('buzzDao', array('getNoOfSharesBy'));

        $buzzDao->expects($this->once())
                ->method('getNoOfSharesBy')
                ->with(1)
                ->will($this->returnValue(1));

         $this->buzzService->setBuzzDao($buzzDao);
        $result = $this->buzzService->getNoOfSharesBy(1);

        $this->assertEquals(1, $result);
    }
      
    /**
     * test comment by user Id
     */
    public function testGetNoOfCommentBy(){
        $buzzDao = $this->getMock('buzzDao', array('getNoOfCommentsBy'));

        $buzzDao->expects($this->once())
                ->method('getNoOfCommentsBy')
                ->with(1)
                ->will($this->returnValue(2));

         $this->buzzService->setBuzzDao($buzzDao);
        $result = $this->buzzService->getNoOfCommentsBy(1);

        $this->assertEquals(2, $result);
    }
    
        /**
     * test comment by user Id
     */
    public function testGetNoOfCommentFor(){
        $buzzDao = $this->getMock('buzzDao', array('getNoOfCommentsFor'));

        $buzzDao->expects($this->once())
                ->method('getNoOfCommentsFor')
                ->with(1)
                ->will($this->returnValue(2));

         $this->buzzService->setBuzzDao($buzzDao);
        $result = $this->buzzService->getNoOfCommentsFor(1);

        $this->assertEquals(2, $result);
    }
    
    /**
     * test shares by user Id
     */
    public function testGetNoOfSharesLikeBy(){
        $buzzDao = $this->getMock('buzzDao', array('getNoOfShareLikesFor'));

        $buzzDao->expects($this->once())
                ->method('getNoOfShareLikesFor')
                ->with(1)
                ->will($this->returnValue(2));

         $this->buzzService->setBuzzDao($buzzDao);
        $result = $this->buzzService->getNoOfShareLikesFor(1);

        $this->assertEquals(2, $result);
    }
   
      /**
     * test comment by user Id
     */
    public function testGetNoOfCommentLikeBy(){
        $buzzDao = $this->getMock('buzzDao', array('getNoOfCommentLikesFor'));

        $buzzDao->expects($this->once())
                ->method('getNoOfCommentLikesFor')
                ->with(1)
                ->will($this->returnValue(2));

         $this->buzzService->setBuzzDao($buzzDao);
        $result = $this->buzzService->getNoOfCommentLikesFor(1);

        $this->assertEquals(2, $result);
    }
    
      /**
     * test 
     */
    public function testMostLikeShares(){
        $shareIds= array(1,2);
                
       $buzzDao = $this->getMock('buzzDao', array('getMostLikedShares'));

        $buzzDao->expects($this->once())
                ->method('getMostLikedShares')
                ->with(2)
                ->will($this->returnValue($shareIds));

         $this->buzzService->setBuzzDao($buzzDao);
        $result = $this->buzzService->getMostLikedShares(2);
        $this->assertEquals('2', Count($result));
    }
    
     /**
     * test 
     */
    public function testMostCommentedShares(){
        $shareIds= array(1,2);
       $buzzDao = $this->getMock('buzzDao', array('getMostCommentedShares'));

        $buzzDao->expects($this->once())
                ->method('getMostCommentedShares')
                ->with(2)
                ->will($this->returnValue($shareIds));

         $this->buzzService->setBuzzDao($buzzDao);
        $result = $this->buzzService->getMostCommentedShares(2);
        $this->assertEquals('2', Count($result));
    }
    
      
    /**
     * test 
     */
    public function testAnivesary(){
       
        $buzzDao = $this->getMock('buzzDao', array('getEmployeesHavingAnniversaryOn'));

        $buzzDao->expects($this->once())
                ->method('getEmployeesHavingAnniversaryOn')
                ->with(4)
                ->will($this->returnValue(array()));

         $this->buzzService->setBuzzDao($buzzDao);
        $result = $this->buzzService->getEmployeesHavingAnniversaryOn(4);
        $this->assertEquals('0', Count($result));
    }

    
}
