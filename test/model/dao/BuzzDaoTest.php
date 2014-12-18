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
class BuzzDaoTest extends PHPUnit_Framework_TestCase {

    private $buzzDao;

    /**
     * Set up method
     */
    protected function setUp() {
        $this->buzzDao = new BuzzDao();
        TestDataService::populate(sfConfig::get('sf_plugins_dir') . '/orangehrmBuzzPlugin/test/fixtures/OrangeBuzz.yml');
    }

    /**
     * test save post to the database
     */
    public function testSavePost() {
        $post = New Post();

        $post->setEmployeeNumber('1');
        $post->setText('this is test Post');
        $post->setPostTime('2015-01-10 12:12:12');
        $resultPost = $this->buzzDao->savePost($post);
        $this->assertTrue($resultPost instanceof Post);
        $this->assertTrue($resultPost->getId() != null);
        $this->assertEquals('2015-01-10 12:12:12', $resultPost->getPostTime());
    }

    /**
     * test save link to the database
     */
    public function testSaveLink() {
        $link = New Link();

        $link->setLink('fdfdfdd.com');
        $link->setPostId(2);
        $link->setDescription('description');
        $resultLink = $this->buzzDao->saveLink($link);
        $this->assertTrue($resultLink instanceof Link);
        $this->assertTrue($resultLink->getId() != null);
    }

    /**
     * this is function to test get shares from database
     */
    public function testGetShares() {
        $resultShares = $this->buzzDao->getShares(2);
        $this->assertEquals(2, count($resultShares));
        $this->assertTrue($resultShares->getFirst() instanceof Share);
    }

    /**
     * this is function to test get shares from database
     */
    public function testGetSharesOverLimit() {
        $limit = 50;
        $resultShares = $this->buzzDao->getShares($limit);
        $this->assertEquals(4, count($resultShares));
        $this->assertTrue($resultShares->getFirst() instanceof Share);
    }

    /**
     * this is function to test get the post from the share
     */
    public function testGetPostFromShare() {
        $resultShares = $this->buzzDao->getShares(2);
        $this->assertTrue($resultShares->getFirst()->getPostShared() instanceof Post);
    }

    /**
     * this is function to test delete post from database
     */
    public function testDeletePost() {
        $result = $this->buzzDao->deletePost('1');

        $this->assertEquals(1, $result);
    }

    /**
     * this is function to test delete post from database
     */
    public function testDeletePostWithIncorrectId() {
        $result = $this->buzzDao->deletePost(100);

        $this->assertEquals(0, $result);
    }

    /** \
     * this is function to test delete share from database
     */
    public function testDeleteShare() {
        $result = $this->buzzDao->deleteShare(1);

        $this->assertEquals(1, $result);
    }

    /** \
     * this is function to test delete share from database
     */
    public function testDeleteShareWithIncorrectId() {
        $result = $this->buzzDao->deleteShare(100);

        $this->assertEquals(0, $result);
    }

    /**
     * this is function to test save like on share to database
     */
    public function testLikeOnShare() {
        $like = new LikeOnShare();
        $like->setShareId(1);
        $like->setEmployeeNumber(1);
        $like->setLikeTime('2015-01-10 12:12:12');

        $resultLike = $this->buzzDao->saveLikeForShare($like);
        $this->assertTrue($resultLike->getId() != null);
    }

    /**
     * this is function to test save like on share to database
     */
    public function testLikeOnShareByAdmin() {
        $like = new LikeOnShare();
        $like->setShareId(1);
        $like->setLikeTime('2015-01-10 12:12:12');

        $resultLike = $this->buzzDao->saveLikeForShare($like);
        $this->assertTrue($resultLike->getId() != null);
    }

    /**
     * this is function to test delete like on share saving to database 
     */
    public function testDeleteLikeOnshare() {
        $like = new LikeOnShare();
        $like->setId(20);
        $like->setShareId(1);
        $like->setEmployeeNumber(1);
        $like->setLikeTime('2015-01-10 12:12:12');

        $result = $this->buzzDao->deleteLikeForShare($like);
        $this->assertEquals(1, $result);
    }

    /**
     * test deleting admin like on share
     */
    public function testDeleteLikeOnshareByAdmin() {
        $like = new LikeOnShare();
        $like->setId(20);
        $like->setShareId(1);
        $like->setLikeTime('2015-01-10 12:12:12');

        $result = $this->buzzDao->deleteLikeForShare($like);
        $this->assertEquals(1, $result);
    }

    /**
     *  test save comment on share to database
     */
    public function testCommentOnShare() {
        $comment = new Comment();
        $comment->setShareId(1);
        $comment->setEmployeeNumber(1);
        $comment->setCommentTime('2015-01-10 12:12:12');
        $comment->setCommentText('this is the first comment');

        $result = $this->buzzDao->saveCommentShare($comment);
        $this->assertTrue($result->getId() != null);
        $this->assertEquals('2015-01-10 12:12:12', $result->getCommentTime());
    }

    /**
     *  test delete comment onshare
     */
    public function testDeleteCommentOnShare() {
        $comment = new Comment();
        $comment->setId(1);
        $comment->setShareId(1);
        $comment->setEmployeeNumber(1);
        $comment->setCommentTime('2015-01-10 12:12:12');
        $comment->setCommentText('this is the first comment');

        $result = $this->buzzDao->deleteCommentForShare($comment);
        $this->assertEquals(1, $result);
    }

    /**
     * this is function to test save likes on comment
     */
    public function testLikeOnComment() {
        $like = new LikeOnComment();
        $like->setCommentId(1);
        $like->setEmployeeNumber(1);
        $like->setLikeTime('2015-01-10 12:12:12');

        $result = $this->buzzDao->saveLikeForComment($like);
        $this->assertTrue($result->getId() != null);
        $this->assertEquals('2015-01-10 12:12:12', $result->getLikeTime());
    }

    /**
     * this is function to test save deletion of like on comment to database
     */
    public function testDeletLikeOnComment() {
        $like = new LikeOnComment();
        $like->setId(20);
        $like->setCommentId(1);
        $like->setEmployeeNumber(1);
        $like->setLikeTime('2015-01-10 12:12:12');

        $result = $this->buzzDao->deleteLikeForComment($like);
        $this->assertEquals(1, $result);
    }

    /**
     * this is function to test save deletion of like on comment to database
     */
    public function testDeletLikeOnCommentByAdmin() {
        $like = new LikeOnComment();
        $like->setId(20);
        $like->setCommentId(1);
        $like->setLikeTime('2015-01-10 12:12:12');

        $result = $this->buzzDao->deleteLikeForComment($like);
        $this->assertEquals(1, $result);
    }

    /**
     * this is function to test updates in the database
     */
    public function testUpdate() {
        $result = $this->buzzDao->getShares(2);
        $share = $result->getFirst();
        $share->setText($share->getId() . ' this is updated one');
        $resultShare = $this->buzzDao->saveShare($share);

        $this->assertEquals($share->getId() . ' this is updated one', $resultShare->getText());
    }

    /**
     * this is functoin to test get share by id
     */
    public function testGetShareById() {
        $share = $this->buzzDao->getShareById(1);

        $this->assertTrue($share instanceof Share);
        $this->assertEquals(1, $share->getId());
    }

    /**
     * this is functoin to test get share by id
     */
    public function testGetShareByIdWithIncorrectId() {

        $share = $this->buzzDao->getShareById(100);

        $this->assertTrue($share == null);
    }

    /**
     * this is functoin to test get post by id
     */
    public function testGetPostById() {
        $post = $this->buzzDao->getPostById(1);

        $this->assertTrue($post instanceof Post);
        $this->assertEquals(1, $post->getId());
    }

    /**
     * this is functoin to test get post by id
     */
    public function testGetPostByIdWithIncorrectId() {
        $post = $this->buzzDao->getPostById(100);

        $this->assertTrue($post == null);
    }

    /**
     * this is functoin to test get Comment by id
     */
    public function testGetCommentById() {
        $comment = $this->buzzDao->getCommentById(1);

        $this->assertTrue($comment instanceof Comment);
        $this->assertEquals(1, $comment->getId());
    }

    /**
     * this is functoin to test get Comment by id
     */
    public function testGetCommentByIdWithIncorrectId() {
        $comment = $this->buzzDao->getCommentById(100);

        $this->assertTrue($comment == null);
    }

    /**
     * this is functoin to test get likeOnComment by id
     */
    public function testGetLikeOnCommentById() {
        $likeOnComment = $this->buzzDao->getLikeOnCommentById(21);
        $this->assertTrue($likeOnComment instanceof LikeOnComment);
        $this->assertEquals(21, $likeOnComment->getId());
    }

    /**
     * this is functoin to test get likeOnComment by id
     */
    public function testGetLikeOnCommentByIdWithIncorrectId() {
        $likeOnComment = $this->buzzDao->getLikeOnCommentById(210);
        $this->assertTrue($likeOnComment == null);
    }

    /**
     * this is functoin to test get likeOnShare by id
     */
    public function testGetLikeOnshareById() {
        $likeOnShare = $this->buzzDao->getLikeOnShareById(21);
        $this->assertTrue($likeOnShare instanceof LikeOnShare);
        $this->assertEquals(21, $likeOnShare->getId());
    }

    /**
     * this is functoin to test get likeOnShare by id
     */
    public function testGetLikeOnshareByIdWithIncorrectId() {
        $likeOnShare = $this->buzzDao->getLikeOnShareById(210);
        $this->assertTrue($likeOnShare == null);
    }

    /**
     * test shares by employee number
     */
    public function testGetNoOfSharesByEmployeeNumber() {
        $resultShareCount = $this->buzzDao->getNoOfSharesByEmployeeNumber(1);

        $this->assertEquals(1, $resultShareCount);
    }

    /**
     * test shares by employee number
     */
    public function testGetNoOfSharesByEmployeeNumberWithIncorrectId() {
        $resultShareCount = $this->buzzDao->getNoOfSharesByEmployeeNumber(100);

        $this->assertEquals(0, $resultShareCount);
    }

    /**
     * test number of comment by id
     */
    public function testGetNoOfSharesByAdmin() {
        $resultShareCount = $this->buzzDao->getNoOfSharesByEmployeeNumber('');

        $this->assertEquals(1, $resultShareCount);
    }

    /**
     * test comment by employee number
     */
    public function testGetEmployeesHavingBdaysBetweenTwoDatesGivingCorrectResult() {
        $fromDate = '2015-05-03';
        $todate = '2015-05-30';
        $expectedEmployeeNumber = 1;
        $resultEmployees = $this->buzzDao->getEmployeesHavingBdaysBetweenTwoDates($fromDate, $todate);

        $this->assertEquals(1, count($resultEmployees));
        $this->assertEquals($expectedEmployeeNumber, $resultEmployees[0]['emp_number']);
    }

    /**
     * test get employees birthdays 
     */
    public function testGetEmployeesHavingBdaysBetweenTwoDatesGivingAllResults() {
        $fromDate = '2015-06-03';
        $todate = '2015-06-30';
        $resultEmployees = $this->buzzDao->getEmployeesHavingBdaysBetweenTwoDates($fromDate, $todate);

        $this->assertEquals(2, count($resultEmployees));
    }

    /**
     * test number of comment  by employee 
     */
    public function testGetNoOfCommentByEmployeeNumber() {
        $resultCommentCount = $this->buzzDao->getNoOfCommentsByEmployeeNumber(1);

        $this->assertEquals(2, $resultCommentCount);
    }

    /**
     * test number of comment  by employee 
     */
    public function testGetNoOfCommentByEmployeeNumberWithIncorrectId() {
        $resultCommentCount = $this->buzzDao->getNoOfCommentsByEmployeeNumber(100);

        $this->assertEquals(0, $resultCommentCount);
    }

    /**
     * test number of comment by Admin
     */
    public function testGetNoOfCommentByAdmin() {
        $resultCommentCount = $this->buzzDao->getNoOfCommentsByEmployeeNumber('');

        $this->assertEquals(1, $resultCommentCount);
    }

    /**
     * test comment by employee number
     */
    public function testGetNoOfCommentFor() {
        $resultCommentCount = $this->buzzDao->getNoOfCommentsForEmployeeByEmployeeNumber(1);

        $this->assertEquals(2, $resultCommentCount);
    }

    /**
     * test number of comment by Admin
     */
    public function testGetNoOfCommentForAdmin() {
        $result = $this->buzzDao->getNoOfCommentsForEmployeeByEmployeeNumber('');

        $this->assertEquals(0, $result);
    }

    /**
     * test number of likes on shares by employee number
     */
    public function testGetNoOfSharesLikeByEmployee() {
        $result = $this->buzzDao->getNoOfShareLikesForEmployeeByEmployeeNumber(1);

        $this->assertEquals(2, $result);
    }

    /**
     * test number of likes on shares by employee number
     */
    public function testGetNoOfSharesLikeByEmployeeWithIncorrectId() {
        $result = $this->buzzDao->getNoOfShareLikesForEmployeeByEmployeeNumber(100);

        $this->assertEquals(0, $result);
    }

    /**
     * test number of comment by id
     */
    public function testGetNoOfSharesLikeByAdmin() {
        $result = $this->buzzDao->getNoOfShareLikesForEmployeeByEmployeeNumber('');

        $this->assertEquals(0, $result);
    }

    /**
     * test comment by employee number
     */
    public function testGetNoOfCommentLikeBy() {
        $result = $this->buzzDao->getNoOfCommentLikesForEmployeeByEmployeeNumber(1);

        $this->assertEquals(3, $result);
    }

    /**
     * test number of comment by Admin
     */
    public function testGetNoOfCommentLikeByAdmin() {
        $result = $this->buzzDao->getNoOfCommentLikesForEmployeeByEmployeeNumber('');

        $this->assertEquals(0, $result);
    }

    /**
     * this is function to test save likes on share to database
     */
    public function testUnLikeOnShare() {
        $like = new UnLikeOnShare();
        $like->setShareId(1);
        $like->setEmployeeNumber(1);
        $like->setLikeTime('2015-01-10 12:12:12');

        $result = $this->buzzDao->saveUnLikeForShare($like);
        $this->assertTrue($result->getId() != null);
        $this->assertEquals('2015-01-10 12:12:12', $result->getLikeTime());
    }

    /**
     * this is function to test delete Unlikes on share
     */
    public function testDeleteUnLikeOnshare() {
        $like = new UnLikeOnShare();
        $like->setId(20);
        $like->setShareId(1);
        $like->setEmployeeNumber(1);
        $like->setLikeTime('2015-01-10 12:12:12');

        $result = $this->buzzDao->deleteUnLikeForShare($like);
        $this->assertEquals(1, $result);
    }

    /**
     * this is function to test delete likes on share
     */
    public function testDeleteUnLikeOnshareWithIncorrectInput() {
        $unlikeOnShare = new UnLikeOnShare();
        $unlikeOnShare->setId(200);
        $unlikeOnShare->setShareId(10);
        $unlikeOnShare->setEmployeeNumber(10);
        $unlikeOnShare->setLikeTime('2015-01-10 12:12:12');

        $resultUnlikeOnShare = $this->buzzDao->deleteUnLikeForShare($unlikeOnShare);
        $this->assertEquals(0, $resultUnlikeOnShare);
    }

    /**
     * this is function to test delete likes on share
     */
    public function testDeleteUnLikeOnshareByAdmin() {
        $like = new UnLikeOnShare();
        $like->setId(20);
        $like->setShareId(1);
        $like->setLikeTime('2015-01-10 12:12:12');

        $result = $this->buzzDao->deleteUnLikeForShare($like);
        $this->assertEquals(0, $result);
    }

    /**
     * this is function to test save likes on comment
     */
    public function testUnLikeOnComment() {
        $like = new UnLikeOnComment();
        $like->setCommentId(1);
        $like->setEmployeeNumber(1);
        $like->setLikeTime('2015-01-10 12:12:12');

        $result = $this->buzzDao->saveUnLikeForComment($like);
        $this->assertTrue($result->getId() != null);
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

        $result = $this->buzzDao->deleteUnLikeForComment($like);
        $this->assertEquals(1, $result);
    }

    /**
     * this is function to test delete likes on the comment
     */
    public function testDeletUnLikeOnCommentWithIcorrectValues() {
        $like = new UnLikeOnComment();
        $like->setId(20);
        $like->setCommentId(1);
        $like->setEmployeeNumber(1);
        $like->setLikeTime('2015-01-10 12:12:12');

        $result = $this->buzzDao->deleteUnLikeForComment($like);
        $this->assertEquals(1, $result);
    }

    /**
     * this is function to test save likes on comment
     */
    public function testAdminUnLikeOnComment() {
        $like = new UnLikeOnComment();
        $like->setCommentId(1);
        //$like->setEmployeeNumber('');
        $like->setLikeTime('2015-01-10 12:12:12');

        $result = $this->buzzDao->saveUnLikeForComment($like);
        $this->assertTrue($result->getId() != null);
        $this->assertEquals('2015-01-10 12:12:12', $result->getLikeTime());
    }

    /**
     * this is function to test delete likes on the comment
     */
    public function testDeletAdminUnLikeOnComment() {
        $like = new UnLikeOnComment();

        $like->setCommentId(1);
        $like->setEmployeeNumber('');


        $result = $this->buzzDao->deleteUnLikeForComment($like);
        $this->assertEquals(0, $result);
    }

    /**
     * test saving photo
     */
    public function testSavingPhoto() {
        $photo = new Photo();
        $photo->setFileType('jpg');
        $photo->setPostId(1);
        $photo->setFilename('test/photo.jpg');
        $resultPhoto = $this->buzzDao->savePhoto($photo);

        $this->assertTrue($resultPhoto->getId() != null);
        $this->assertEquals('jpg', $resultPhoto->getFileType());
    }

    /**
     * test getting anivesary from data base
     */
    public function testEmployeesHavingAnniversaryOnMonthGivingAllResutls() {
        $fromDate = '2015-05-03';
        $resultEmployees = $this->buzzDao->getEmployeesHavingAnniversaryOnMonth($fromDate);

        $this->assertEquals(2, Count($resultEmployees));
    }

    /**
     * test getting anivesary from data base
     */
    public function testEmployeesHavingAnniversaryOnMonthGivingCorrectResutl() {
        $fromDate = '2015-06-03';
        $expectedEmployeeNumber = 2;
        $resultEmployees = $this->buzzDao->getEmployeesHavingAnniversaryOnMonth($fromDate);

        $this->assertEquals(1, Count($resultEmployees));
        $this->assertEquals($expectedEmployeeNumber, $resultEmployees[0]['emp_number']);
    }

    /**
     * test getting anivesary from data base
     */
    public function testEmployeesHavingAnniversaryOnMonthGivingNull() {
        $fromDate = '2015-10-03';
        $resultEmployees = $this->buzzDao->getEmployeesHavingAnniversaryOnMonth($fromDate);

        $this->assertEquals(0, Count($resultEmployees));
    }

    /**
     * test get Most Like shares 
     */
    public function testMostLikeShares() {

        $resultShares = $this->buzzDao->getMostLikedShares(2);

        $this->assertEquals(2, Count($resultShares));
    }

    /**
     * test get Most Like shares 
     */
    public function testMostLikeSharesWhithLageLimit() {

        $resultShares = $this->buzzDao->getMostLikedShares(200);

        $this->assertEquals(2, Count($resultShares));
    }

    /**
     * test get Most Like shares 
     */
    public function testMostLikeSharesGivingCorrectResult() {
        $expectedShareId = 1;
        $resultShares = $this->buzzDao->getMostLikedShares(2);

        $this->assertEquals($expectedShareId, $resultShares[0]['share_id']);
    }

    /**
     * test get most commented shares
     */
    public function testMostCommentedShares() {

        $result = $this->buzzDao->getMostCommentedShares(2);

        $this->assertEquals(2, Count($result));
    }

    /**
     * test get most commented shares with large limit
     */
    public function testMostCommentedSharesWhithLargeLimit() {

        $result = $this->buzzDao->getMostCommentedShares(200);

        $this->assertEquals(2, Count($result));
    }

    /**
     * test get most commented shares result correct
     */
    public function testMostCommentedSharesGivingCorrectResult() {
        $expectedShareId = 1;
        $result = $this->buzzDao->getMostCommentedShares(2);

        $this->assertEquals($expectedShareId, $result[0]['share_id']);
    }

    /**
     * test get more shares
     */
    public function testGetMoreSharesNull() {
        $fromId = 0;
        $limit = 1;
        $resultShares = $this->buzzDao->getMoreShares($limit, $fromId);

        $this->assertEquals(0, Count($resultShares));
    }

    /**
     * test get more shares giving up to limit
     */
    public function testGetMoreSharesGivingLimt() {
        $fromId = 4;
        $limit = 2;
        $resultShares = $this->buzzDao->getMoreShares($limit, $fromId);

        $this->assertTrue($resultShares->getFirst() instanceof Share);
        $this->assertEquals(2, Count($resultShares));
    }

    /**
     * test get more shares giving all when limit large
     */
    public function testGetMoreSharesWithLargeLimit() {
        $fromId = 3;
        $limit = 200;
        $resultShares = $this->buzzDao->getMoreShares($limit, $fromId);

        $this->assertEquals(2, Count($resultShares));
    }

    /**
     * test get more profoile shares Giving null result
     */
    public function testGetMoreEmployeeSharesNullResult() {
        $fromId = 0;
        $limit = 2;
        $employeeNumber = 1;
        $resultShares = $this->buzzDao->getMoreEmployeeSharesByEmployeeNumber($limit, $fromId, $employeeNumber);

        $this->assertEquals(0, Count($resultShares));
    }

    /**
     * test get more profoile shares Giving null result
     */
    public function testGetMoreEmployeeSharesCorrectLimit() {
        $fromId = 5;
        $limit = 2;
        $employeeNumber = 2;
        $resultShares = $this->buzzDao->getMoreEmployeeSharesByEmployeeNumber($limit, $fromId, $employeeNumber);

        $this->assertTrue($resultShares->getFirst() instanceof Share);
        $this->assertEquals(2, Count($resultShares));
    }

    /**
     * test get more profoile shares Giving all when large limit gives
     */
    public function testGetMoreEmployeeSharesWithLargeLimit() {
        $fromId = 5;
        $limit = 200;
        $employeeNumber = 2;
        $resultShares = $this->buzzDao->getMoreEmployeeSharesByEmployeeNumber($limit, $fromId, $employeeNumber);

        $this->assertEquals(2, Count($resultShares));
    }

    /**
     * test get admins shares
     */
    public function testGetMoreSharesByAdmin() {
        $fromId = 5;
        $limit = 2;
        $employeeNumber = '';
        $resultShares = $this->buzzDao->getMoreEmployeeSharesByEmployeeNumber($limit, $fromId, $employeeNumber);

        $this->assertTrue($resultShares->getFirst() instanceof Share);
        $this->assertEquals(1, Count($resultShares));
    }

    /**
     * test get shares by employee number
     */
    public function testgetSharesByEmployeeNumber() {
        $limit = 1;
        $employeeNumber = 2;
        $resultShares = $this->buzzDao->getSharesByEmployeeNumber($limit, $employeeNumber);

        $this->assertEquals(1, Count($resultShares));
    }

    /**
     * test get shares by admin
     */
    public function testgetSharesByAdmin() {
        $limit = 1;
        $employeeNumber = '';
        $resultShares = $this->buzzDao->getSharesByEmployeeNumber($limit, $employeeNumber);

        $this->assertEquals(1, Count($resultShares));
    }

    /**
     * test get employee shares up to share Id
     */
    public function testGetEmployeeShareUptoId() {
        $lastId = 1;
        $employeeNumber = 2;
        $resultShares = $this->buzzDao->getEmployeeSharesUptoShareId($lastId, $employeeNumber);

        $this->assertEquals(2, Count($resultShares));
    }

    /**
     * test get employee shares up to share Id
     */
    public function testGetEmployeeShareUptoIdWithIncorectId() {
        $lastId = 1;
        $employeeNumber = 20;
        $resultShares = $this->buzzDao->getEmployeeSharesUptoShareId($lastId, $employeeNumber);

        $this->assertEquals(0, Count($resultShares));
    }

    /**
     * test get admin shares upto Id
     */
    public function testGetEmployeeShareUptoIdByAdmin() {
        $lastId = 1;
        $employeeNumber = '';
        $resultShares = $this->buzzDao->getEmployeeSharesUptoShareId($lastId, $employeeNumber);

        $this->assertEquals(1, Count($resultShares));
    }

    /**
     * test get shares up to share Id
     */
    public function testGetShareUpToId() {
        $lastId = 1;
        $resultShares = $this->buzzDao->getSharesUptoId($lastId);


        $this->assertEquals(4, Count($resultShares));
    }

    /**
     * test get shares up to share Id with not existing Id
     */
    public function testGetShareUpToIdWithLageId() {
        $lastId = 100;
        $resultShares = $this->buzzDao->getSharesUptoId($lastId);

        $this->assertEquals(0, Count($resultShares));
    }

}
