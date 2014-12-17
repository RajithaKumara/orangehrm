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
        $result = $this->buzzDao->savePost($post);
        $this->assertTrue($result instanceof Post);
        $this->assertEquals('2015-01-10 12:12:12', $result->getPostTime());
    }
    
    /**
     * test save link to the database
     */
    public function testSaveLink() {
        $link = New Link();

        $link->setLink('fdfdfdd.com');
        $link->setPostId(2);
        $link->setDescription('description');
        $result = $this->buzzDao->saveLink($link);
        $this->assertTrue($result instanceof Link);
        $this->assertTrue($result->getId()!=null);
    }

    /**
     * this is function to test get shares from database
     */
    public function testGetShares() {
        $result = $this->buzzDao->getShares(2);
        $this->assertEquals(2, count($result));
        $this->assertTrue($result->getFirst() instanceof Share);
    }

    /**
     * this is function to test get the post from the share
     */
    public function testGetPostFromShare() {
        $result = $this->buzzDao->getShares(2);
        $this->assertTrue($result->getFirst()->getPostShared() instanceof Post);
    }

    /**
     * this is function to test delete post from database
     */
    public function testDeletePost() {
        $result = $this->buzzDao->deletePost('1');

        $this->assertEquals(1, $result);
    }

    /** \
     * this is function to test delete share from database
     */
    public function testDeleteShare() {
        $result = $this->buzzDao->deleteShare(1);

        $this->assertEquals(1, $result);
    }

    /**
     * this is function to test save like on share to database
     */
    public function testLikeOnShare() {
        $like = new LikeOnShare();
        $like->setShareId(1);
        $like->setEmployeeNumber(1);
        $like->setLikeTime('2015-01-10 12:12:12');

        $result = $this->buzzDao->saveLikeForShare($like);
        $this->assertTrue($result->getId() != null);
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
        $r = $this->buzzDao->saveShare($share);
        $this->assertEquals($share->getId() . ' this is updated one', $r->getText());
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
     * this is functoin to test get post by id
     */
    public function testGetPostById() {
        $post = $this->buzzDao->getPostById(1);
        $this->assertTrue($post instanceof Post);
        $this->assertEquals(1, $post->getId());
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
     * this is functoin to test get likeOnComment by id
     */
    public function testGetLikeOnCommentById() {
        $likeOnComment = $this->buzzDao->getLikeOnCommentById(21);
        $this->assertTrue($likeOnComment instanceof LikeOnComment);
        $this->assertEquals(21, $likeOnComment->getId());
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
     * test shares by user Id
     */
    public function testGetNoOfSharesByEmployeeNumber() {
        $result = $this->buzzDao->getNoOfSharesByEmployeeNumber(1);

        $this->assertEquals(1, $result);
    }

    /**
     * test number of comment by id
     */
    public function testGetNoOfSharesByAdmin() {
        $result = $this->buzzDao->getNoOfSharesByEmployeeNumber('');

        $this->assertEquals(1, $result);
    }

    /**
     * test comment by user Id
     */
    public function testGetEmployeesHavingBdaysBetweenTwoDates() {
        $fromDate = '2015-06-03';
        $todate = '2015-06-07';
        $result = $this->buzzDao->getEmployeesHavingBdaysBetweenTwoDates($fromDate, $todate);

        $this->assertEquals(2, count($result));
    }

    /**
     * test comment by user Id
     */
    public function testGetNoOfCommentByEmployeeNumber() {
        $result = $this->buzzDao->getNoOfCommentsByEmployeeNumber(1);

        $this->assertEquals(2, $result);
    }

    /**
     * test number of comment by Admin
     */
    public function testGetNoOfCommentByAdmin() {
        $result = $this->buzzDao->getNoOfCommentsByEmployeeNumber('');

        $this->assertEquals(1, $result);
    }

    /**
     * test comment by user Id
     */
    public function testGetNoOfCommentFor() {
        $result = $this->buzzDao->getNoOfCommentsForEmployeeByEmployeeNumber(1);

        $this->assertEquals(3, $result);
    }

    /**
     * test number of comment by Admin
     */
    public function testGetNoOfCommentForAdmin() {
        $result = $this->buzzDao->getNoOfCommentsForEmployeeByEmployeeNumber('');

        $this->assertEquals(0, $result);
    }

    /**
     * test shares by user Id
     */
    public function testGetNoOfSharesLikeBy() {
        $result = $this->buzzDao->getNoOfShareLikesForEmployeeByEmployeeNumber(1);

        $this->assertEquals(3, $result);
    }

    /**
     * test number of comment by id
     */
    public function testGetNoOfSharesLikeByAdmin() {
        $result = $this->buzzDao->getNoOfShareLikesForEmployeeByEmployeeNumber('');

        $this->assertEquals(0, $result);
    }

    /**
     * test comment by user Id
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
     * this is function to test delete likes on share
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
    public function testDeleteUnLikeOnshareByAdmin() {
        $like = new UnLikeOnShare();
        $like->setId(20);
        $like->setShareId(1);
        //$like->setEmployeeNumber(1);
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

        $result = $this->buzzDao->savePhoto($photo);
        $this->assertTrue($result->getId() != null);
        $this->assertEquals('jpg', $result->getFileType());
    }

    /**
     * test 
     */
    public function testAnivesary() {
        $fromDate = '2015-05-03';
        $result = $this->buzzDao->getEmployeesHavingAnniversaryOnMonth($fromDate);
        $this->assertEquals(1, Count($result));
    }

    /**
     * test 
     */
    public function testMostLikeShares() {

        $result = $this->buzzDao->getMostLikedShares(4);

        $this->assertEquals(1, Count($result));
    }

    /**
     * test 
     */
    public function testMostCommentedShares() {

        $result = $this->buzzDao->getMostCommentedShares(4);

        $this->assertEquals(1, Count($result));
    }

    /**
     * test 
     */
    public function testGetMoreShares() {

        $result = $this->buzzDao->getMoreShares(1, 0);
        $this->assertEquals(0, Count($result));
    }

    /**
     * test 
     */
    public function testGetMoreProfileShares() {
        $result = $this->buzzDao->getMoreEmployeeSharesByEmployeeNumber(1, 0, 1);
        $this->assertEquals(0, Count($result));
    }

    /**
     * test 
     */
    public function testGetMoreProfileSharesByAdmin() {
        $result = $this->buzzDao->getMoreEmployeeSharesByEmployeeNumber(1, 5, '');
        $this->assertEquals(1, Count($result));
    }

    /**
     * test 
     */
    public function testgetSharesByUserId() {
        $result = $this->buzzDao->getSharesByEmployeeNumber(1, 2);
        $this->assertEquals(1, Count($result));
    }

    /**
     * test 
     */
    public function testgetSharesByAdmin() {
        $result = $this->buzzDao->getSharesByEmployeeNumber(1, '');
        $this->assertEquals(1, Count($result));
    }

    /**
     * test 
     */
    public function testgetFrofileShareUptoId() {
        $result = $this->buzzDao->getEmployeeSharesUptoShareId(1, 2);
        $this->assertEquals(2, Count($result));
    }

    /**
     * test 
     */
    public function testgetFrofileShareUptoIdByAdmin() {
        $result = $this->buzzDao->getEmployeeSharesUptoShareId(1, '');
        $this->assertEquals(1, Count($result));
    }

    /**
     * test 
     */
    public function testgetShareUpToId() {
        $result = $this->buzzDao->getSharesUptoId(1);
        $this->assertEquals(4, Count($result));
    }

}
