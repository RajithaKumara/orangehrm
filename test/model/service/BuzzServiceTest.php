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
    }

    public function testGetBuzzDao() {
        $buzzDao = $this->buzzService->getBuzzDao();
        $this->assertTrue($buzzDao instanceof BuzzDao);
    }

    /**
     * this is function to test saving post in the database
     */
    public function testGetShareCount() {
        $buzzDao = $this->getMock('buzzDao', array('getSharesCount'));
        $buzzDao->expects($this->once())
                ->method('getSharesCount')
                ->will($this->returnValue(4));
        $this->buzzService->setBuzzDao($buzzDao);

        $resultShareCount = $this->buzzService->getSharesCount();
        $this->assertEquals(4, $resultShareCount);
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
     * this is function to test saving link in the database
     */
    public function testSaveLink() {
        $link = New Link();
        $buzzDao = $this->getMock('buzzDao', array('saveLink'));
        $buzzDao->expects($this->once())
                ->method('saveLink')
                ->with($link)
                ->will($this->returnValue($link));
        $this->buzzService->setBuzzDao($buzzDao);
        $resultLink = $this->buzzService->saveLink($link);

        $this->assertTrue($resultLink instanceof Link);
    }

    /**
     * this is function to test getting shares data from the database
     */
    public function testGetShares() {
        $buzzDao = $this->getMock('buzzDao', array('getShares'));
        $shareArray = array(
            new Share(),
            new Share(),
            new Share()
        );
        $buzzDao->expects($this->once())
                ->method('getShares')
                ->with(2)
                ->will($this->returnValue($shareArray));
        $this->buzzService->setBuzzDao($buzzDao);
        $resultShares = $this->buzzService->getShares(2);

        $this->assertTrue(is_array($resultShares));
        $this->assertEquals(3, count($resultShares));
    }

    /**
     * this is function to test getting post from share
     */
    public function testGetPostFromShare() {
        $post = new Post();
        $share = new Share();
        $share->setPostShared($post);
        $buzzDao = $this->getMock('buzzDao', array('getShareById'));
        $buzzDao->expects($this->once())
                ->method('getShareById')
                ->with(1)
                ->will($this->returnValue($share));
        $this->buzzService->setBuzzDao($buzzDao);
        $resultShare = $this->buzzService->getShareById(1);

        $this->assertTrue($resultShare->getPostShared() instanceof Post);
    }

    /**
     * this is function to test delete post
     */
    public function testDeletePost() {
        $buzzDao = $this->getMock('buzzDao', array('deletePost'));
        $buzzDao->expects($this->once())
                ->method('deletePost')
                ->with('1')
                ->will($this->returnValue(1));
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
        $like->setEmployeeNumber(1);
        $like->setLikeTime('2015-01-10 12:12:12');
        $share = new Share();
        $share->setId(5);
        $share->setPostId(2);
        $share->setNumberOfLikes(1);
        $like->setShareLike($share);

        $buzzDao = $this->getMock('buzzDao', array('saveLikeForShare', 'saveShare'));
        $buzzDao->expects($this->once())
                ->method('saveLikeForShare')
                ->with($like)
                ->will($this->returnValue($like));
        $buzzDao->expects($this->once())
                ->method('saveShare')
                ->with($share)
                ->will($this->returnValue(null));
        $this->buzzService->setBuzzDao($buzzDao);
        $result = $this->buzzService->saveLikeForShare($like);

        $this->assertEquals('2015-01-10 12:12:12', $result->getLikeTime());
    }

    /**
     * this is function to test delete likes on share
     */
    public function testDeleteLikeOnshare() {
        $like = new LikeOnShare();
        $like->setId(20);
        $share = new Share();
        $share->setNumberOfLikes(1);
        $like->setShareLike($share);
        $buzzDao = $this->getMock('buzzDao', array('deleteLikeForShare', 'saveShare'));

        $buzzDao->expects($this->once())
                ->method('deleteLikeForShare')
                ->with($like)
                ->will($this->returnValue(1));
        $buzzDao->expects($this->once())
                ->method('saveShare')
                ->with($share)
                ->will($this->returnValue(null));
        $this->buzzService->setBuzzDao($buzzDao);
        $result = $this->buzzService->deleteLikeForShare($like);

        $this->assertEquals('1', $result);
    }

    /**
     * this is function to test save comment on share to database
     */
    public function testCommentOnShare() {
        $comment = new Comment();
        $comment->setCommentTime('2015-01-10 12:12:12');
        $share = new Share();
        $share->setId(7);
        $share->setPostId(2);
        $share->setNumberOfLikes(1);
        $comment->setShareComment($share);

        $buzzDao = $this->getMock('buzzDao', array('saveCommentShare', 'saveShare'));

        $buzzDao->expects($this->once())
                ->method('saveCommentShare')
                ->with($comment)
                ->will($this->returnValue($comment));

        $buzzDao->expects($this->once())
                ->method('saveShare')
                ->with($share)
                ->will($this->returnValue(null));

        $this->buzzService->setBuzzDao($buzzDao);
        $result = $this->buzzService->saveCommentShare($comment);

        $this->assertEquals('2015-01-10 12:12:12', $result->getCommentTime());
    }

    /**
     * this is function to test delete Comment on share
     */
    public function testDeleteCommentOnShare() {
        $comment = new Comment();
        $share = new Share();
        $share->setId(6);
        $share->setPostId(2);
        $share->setNumberOfLikes(1);
        $comment->setShareComment($share);
        $buzzDao = $this->getMock('buzzDao', array('deleteCommentForShare', 'saveShare'));

        $buzzDao->expects($this->once())
                ->method('deleteCommentForShare')
                ->with($comment)
                ->will($this->returnValue(1));
        $buzzDao->expects($this->once())
                ->method('saveShare')
                ->with($share)
                ->will($this->returnValue(null));
        $this->buzzService->setBuzzDao($buzzDao);

        $result = $this->buzzService->deleteCommentForShare($comment);
        $this->assertEquals(1, $result);
    }

    /**
     * this is function to test save likes on comment
     */
    public function testLikeOnComment() {
        $like = new LikeOnComment();
        $like->setLikeTime('2015-01-10 12:12:12');
        $comment = new Comment();
        $comment->setNumberOfLikes(3);
        $like->setCommentLike($comment);
        $buzzDao = $this->getMock('buzzDao', array('saveLikeForComment', 'saveCommentShare'));

        $buzzDao->expects($this->once())
                ->method('saveLikeForComment')
                ->with($like)
                ->will($this->returnValue($like));
        $buzzDao->expects($this->once())
                ->method('saveCommentShare')
                ->with($comment);

        $this->buzzService->setBuzzDao($buzzDao);

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
        $comment = new Comment();
        $comment->setNumberOfLikes(3);
        $like->setCommentLike($comment);
        $buzzDao = $this->getMock('buzzDao', array('deleteLikeForComment', 'saveCommentShare'));

        $buzzDao->expects($this->once())
                ->method('deleteLikeForComment')
                ->with($like)
                ->will($this->returnValue(1));
        $buzzDao->expects($this->once())
                ->method('saveCommentShare')
                ->with($comment);
        $this->buzzService->setBuzzDao($buzzDao);

        $result = $this->buzzService->deleteLikeForComment($like);
        $this->assertEquals(1, $result);
    }

    /**
     * this is functoin to test get share by id
     */
    public function testGetShareById() {

        $share = new Share();

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
        $post = new Post();

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
        $comment = new Comment();
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
        $like = New LikeOnComment();
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
        $like = New LikeOnShare();
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
        $unlikeOnShare = new UnLikeOnShare();
        $unlikeOnShare->setShareId(1);
        $unlikeOnShare->setEmployeeNumber(1);
        $unlikeOnShare->setLikeTime('2015-01-10 12:12:12');
        $share = new Share();
        $share->setNumberOfLikes(1);
        $unlikeOnShare->setShareUnLike($share);
        $buzzDao = $this->getMock('buzzDao', array('saveUnLikeForShare', 'saveShare'));

        $buzzDao->expects($this->once())
                ->method('saveUnLikeForShare')
                ->with($unlikeOnShare)
                ->will($this->returnValue($unlikeOnShare));
        $buzzDao->expects($this->once())
                ->method('saveShare')
                ->with($share)
                ->will($this->returnValue(null));
        $this->buzzService->setBuzzDao($buzzDao);

        $resultUnlikeOnShare = $this->buzzService->saveUnLikeForShare($unlikeOnShare);
        $this->assertEquals('2015-01-10 12:12:12', $resultUnlikeOnShare->getLikeTime());
    }

    /**
     * this is function to test delete likes on share
     */
    public function testDeleteUnLikeOnshare() {
        $unlikeOnShare = new UnLikeOnShare();
        $unlikeOnShare->setId(20);
        $unlikeOnShare->setShareId(1);
        $unlikeOnShare->setEmployeeNumber(1);
        $unlikeOnShare->setLikeTime('2015-01-10 12:12:12');
        $share = new Share();
        $share->setNumberOfLikes(1);
        $unlikeOnShare->setShareUnLike($share);
        $buzzDao = $this->getMock('buzzDao', array('deleteUnLikeForShare', 'saveShare'));

        $buzzDao->expects($this->once())
                ->method('deleteUnLikeForShare')
                ->with($unlikeOnShare)
                ->will($this->returnValue(1));
        $buzzDao->expects($this->once())
                ->method('saveShare')
                ->with($share)
                ->will($this->returnValue(null));
        $this->buzzService->setBuzzDao($buzzDao);

        $resultDeleteCount = $this->buzzService->deleteUnLikeForShare($unlikeOnShare);
        $this->assertEquals(1, $resultDeleteCount);
    }

    /**
     * this is function to test save likes on comment
     */
    public function testUnLikeOnComment() {
        $like = new UnLikeOnComment();
        $like->setCommentId(1);
        $like->setEmployeeNumber(1);
        $like->setLikeTime('2015-01-10 12:12:12');

        $comment = new Comment();
        $comment->setNumberOfLikes(1);
        $like->setCommentUnLike($comment);
        $buzzDao = $this->getMock('buzzDao', array('saveUnLikeForComment', 'saveCommentShare'));

        $buzzDao->expects($this->once())
                ->method('saveUnLikeForComment')
                ->with($like)
                ->will($this->returnValue($like));
        $buzzDao->expects($this->once())
                ->method('saveCommentShare')
                ->with($comment)
                ->will($this->returnValue(null));
        $this->buzzService->setBuzzDao($buzzDao);

        $resultUnlikeOnComment = $this->buzzService->saveUnLikeForComment($like);
        $this->assertEquals('2015-01-10 12:12:12', $resultUnlikeOnComment->getLikeTime());
    }

    /**
     * this is function to test delete likes on the comment
     */
    public function testDeletUnLikeOnComment() {
        $like = new UnLikeOnComment();
        $comment = new Comment();
        $comment->setNumberOfLikes(1);
        $like->setCommentUnLike($comment);
        $buzzDao = $this->getMock('buzzDao', array('deleteUnLikeForComment', 'saveCommentShare'));

        $buzzDao->expects($this->once())
                ->method('deleteUnLikeForComment')
                ->with($like)
                ->will($this->returnValue(1));
        $buzzDao->expects($this->once())
                ->method('saveCommentShare')
                ->with($comment)
                ->will($this->returnValue(null));
        $this->buzzService->setBuzzDao($buzzDao);

        $resultDeleteCount = $this->buzzService->deleteUnLikeForComment($like);
        $this->assertEquals(1, $resultDeleteCount);
    }

    /**
     * test get more shares 
     */
    public function testGetMoreShares() {
        $buzzDao = $this->getMock('buzzDao', array('getMoreShares'));

        $shareArray = array(
            new Share(),
            new Share(),
            new Share()
        );
        $buzzDao->expects($this->once())
                ->method('getMoreShares')
                ->with(1, 0)
                ->will($this->returnValue($shareArray));

        $this->buzzService->setBuzzDao($buzzDao);
        $resultShares = $this->buzzService->getMoreShares(1, 0);
        $this->assertEquals(3, Count($resultShares));
    }

    /**
     * test test get more employee shares
     */
    public function testGetMoreEmployeeShares() {
        $buzzDao = $this->getMock('buzzDao', array('getMoreEmployeeSharesByEmployeeNumber'));

        $shareArray = array(
            new Share(),
            new Share(),
            new Share()
        );
        $buzzDao->expects($this->once())
                ->method('getMoreEmployeeSharesByEmployeeNumber')
                ->with(1, 0, 1)
                ->will($this->returnValue($shareArray));

        $this->buzzService->setBuzzDao($buzzDao);
        $resultShares = $this->buzzService->getMoreEmployeeSharesByEmployeeNumber(1, 0, 1);
        $this->assertEquals(3, Count($resultShares));
    }

    /**
     * test share by emplyee 
     */
    public function testgetSharesByEmployeeNumber() {
        $buzzDao = $this->getMock('buzzDao', array('getSharesByEmployeeNumber'));

        $shareArray = array(
            new Share(),
            new Share(),
            new Share()
        );
        $buzzDao->expects($this->once())
                ->method('getSharesByEmployeeNumber')
                ->with(1, 2)
                ->will($this->returnValue($shareArray));

        $this->buzzService->setBuzzDao($buzzDao);
        $resultShares = $this->buzzService->getSharesByEmployeeNumber(1, 2);

        $this->assertEquals(3, count($resultShares));
        $this->assertTrue(is_array($resultShares));
    }

    /**
     * test employee shares up to share id
     */
    public function testgetEmployeeShareUptoShareId() {
        $buzzDao = $this->getMock('buzzDao', array('getEmployeeSharesUptoShareId'));

        $shareArray = array(
            new Share(),
            new Share(),
            new Share()
        );
        $buzzDao->expects($this->once())
                ->method('getEmployeeSharesUptoShareId')
                ->with(1, 2)
                ->will($this->returnValue($shareArray));

        $this->buzzService->setBuzzDao($buzzDao);
        $resultShares = $this->buzzService->getEmployeeSharesUptoShareId(1, 2);
        $this->assertEquals(3, Count($resultShares));
    }

    /**
     * test get shares upto share id
     */
    public function testgetShareUpToShareId() {
        $buzzDao = $this->getMock('buzzDao', array('getSharesUptoId'));

        $shareArray = array(
            new Share(),
            new Share(),
            new Share()
        );
        $buzzDao->expects($this->once())
                ->method('getSharesUptoId')
                ->with(1)
                ->will($this->returnValue($shareArray));

        $this->buzzService->setBuzzDao($buzzDao);
        $resultShares = $this->buzzService->getSharesUptoId(1);
        $this->assertTrue(is_array($resultShares));
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

        $resultPhoto = $this->buzzService->savePhoto($photo);
        $this->assertTrue($resultPhoto instanceof Photo);
    }

    /**
     * test shares by user Id
     */
    public function testGetNoOfSharesBy() {
        $buzzDao = $this->getMock('buzzDao', array('getNoOfSharesByEmployeeNumber'));

        $buzzDao->expects($this->once())
                ->method('getNoOfSharesByEmployeeNumber')
                ->with(1)
                ->will($this->returnValue(1));

        $this->buzzService->setBuzzDao($buzzDao);
        $resultNoOfShares = $this->buzzService->getNoOfSharesByEmployeeNumber(1);

        $this->assertEquals(1, $resultNoOfShares);
    }

    /**
     * test comment by user Id
     */
    public function testGetNoOfCommentBy() {
        $buzzDao = $this->getMock('buzzDao', array('getNoOfCommentsByEmployeeNumber'));

        $buzzDao->expects($this->once())
                ->method('getNoOfCommentsByEmployeeNumber')
                ->with(1)
                ->will($this->returnValue(2));

        $this->buzzService->setBuzzDao($buzzDao);
        $resultNoOfComments = $this->buzzService->getNoOfCommentsByEmployeeNumber(1);

        $this->assertEquals(2, $resultNoOfComments);
    }

    /**
     * test comment by user Id
     */
    public function testGetNoOfCommentFor() {
        $buzzDao = $this->getMock('buzzDao', array('getNoOfCommentsForEmployeeByEmployeeNumber'));

        $buzzDao->expects($this->once())
                ->method('getNoOfCommentsForEmployeeByEmployeeNumber')
                ->with(1)
                ->will($this->returnValue(2));

        $this->buzzService->setBuzzDao($buzzDao);
        $resultNoOfComments = $this->buzzService->getNoOfCommentsForEmployeeByEmployeeNumber(1);

        $this->assertEquals(2, $resultNoOfComments);
    }

    /**
     * test shares by user Id
     */
    public function testGetNoOfShareLikesForEmployeeByEmployeeNumber() {
        $buzzDao = $this->getMock('buzzDao', array('getNoOfShareLikesForEmployeeByEmployeeNumber'));

        $buzzDao->expects($this->once())
                ->method('getNoOfShareLikesForEmployeeByEmployeeNumber')
                ->with(1)
                ->will($this->returnValue(2));

        $this->buzzService->setBuzzDao($buzzDao);
        $resultCount = $this->buzzService->getNoOfShareLikesForEmployeeByEmployeeNumber(1);

        $this->assertEquals(2, $resultCount);
    }

    /**
     * test comment by user Id
     */
    public function testGetNoOfCommentLikeBy() {
        $buzzDao = $this->getMock('buzzDao', array('getNoOfCommentLikesForEmployeeByEmployeeNumber'));

        $buzzDao->expects($this->once())
                ->method('getNoOfCommentLikesForEmployeeByEmployeeNumber')
                ->with(1)
                ->will($this->returnValue(2));

        $this->buzzService->setBuzzDao($buzzDao);
        $resultCount = $this->buzzService->getNoOfCommentLikesForEmployeeByEmployeeNumber(1);

        $this->assertEquals(2, $resultCount);
    }

    /**
     * test get most like shares ids
     */
    public function testMostLikeShares() {
        $shareIds = array(1, 2);

        $buzzDao = $this->getMock('buzzDao', array('getMostLikedShares'));

        $buzzDao->expects($this->once())
                ->method('getMostLikedShares')
                ->with(2)
                ->will($this->returnValue($shareIds));

        $this->buzzService->setBuzzDao($buzzDao);
        $resultShareIds = $this->buzzService->getMostLikedShares(2);
        $this->assertEquals(2, Count($resultShareIds));
    }

    /**
     * test get most commented shares
     */
    public function testMostCommentedShares() {
        $shareIds = array(1, 2);
        $buzzDao = $this->getMock('buzzDao', array('getMostCommentedShares'));

        $buzzDao->expects($this->once())
                ->method('getMostCommentedShares')
                ->with(2)
                ->will($this->returnValue($shareIds));

        $this->buzzService->setBuzzDao($buzzDao);
        $resultShareIds = $this->buzzService->getMostCommentedShares(2);
        $this->assertEquals(2, Count($resultShareIds));
    }

    /**
     * test get employee birthdays
     */
    public function testGetEmployeesHavingBdaysBetweenTwoDates() {
        $fromDate = '2015-06-03';
        $todate = '2015-06-07';
        $buzzDao = $this->getMock('buzzDao', array('getEmployeesHavingBdaysBetweenTwoDates'));

        $employeeArray = array(
            new Employee(),
            new Employee(),
            new Employee()
        );
        $buzzDao->expects($this->once())
                ->method('getEmployeesHavingBdaysBetweenTwoDates')
                ->with($fromDate, $todate)
                ->will($this->returnValue($employeeArray));

        $this->buzzService->setBuzzDao($buzzDao);
        $resultEmployees = $this->buzzService->getEmployeesHavingBdaysBetweenTwoDates($fromDate, $todate);
        $this->assertTrue(is_array($resultEmployees));
    }

    /**
     * test get employee anivesary
     */
    public function testAnivesary() {
        $date = '2012-05-15';
        $buzzDao = $this->getMock('buzzDao', array('getEmployeesHavingAnniversaryOnMonth'));

        $employeeArray = array(
            new Employee(),
            new Employee(),
            new Employee()
        );
        $buzzDao->expects($this->once())
                ->method('getEmployeesHavingAnniversaryOnMonth')
                ->with($date)
                ->will($this->returnValue($employeeArray));

        $this->buzzService->setBuzzDao($buzzDao);
        $resultEmployees = $this->buzzService->getEmployeesHavingAnniversaryOnMonth($date);
        $this->assertTrue(is_array($resultEmployees));
    }

    /**
     * test save shares
     */
    public function testSaveShare() {
        $share = new Share();
        $buzzDao = $this->getMock('buzzDao', array('saveShare'));

        $buzzDao->expects($this->once())
                ->method('saveShare')
                ->with($share)
                ->will($this->returnValue($share));

        $this->buzzService->setBuzzDao($buzzDao);
        $resultShares = $this->buzzService->saveShare($share);
        $this->assertTrue($resultShares instanceof Share);
    }

}
