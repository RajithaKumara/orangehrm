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

        $this->buzzDao = new buzzDao();
        TestDataService::populate(sfConfig::get('sf_plugins_dir') . '/orangehrmBuzzPlugin/test/fixtures/Post.yml');
    }

    /**
     * this is function to test save post to the database
     */
    public function testSavePost() {
        $post = New Post();

        $post->setEmployeeNumber('1');
        $post->setText('this is test Post');
        $post->setPostTime('2015-01-10 12:12:12');
        $result = $this->buzzDao->savePost($post);
        $this->assertEquals('2015-01-10 12:12:12', $result->getPostTime());
    }

    /**
     * this is function to test get shares from database
     */
    public function testGetShares() {
        $result = $this->buzzDao->getShares(2);
        $this->assertEquals(2, count($result));
    }

    /**
     * this is function to test get the post from the share
     */
    public function testGetPostFromShare() {
        $result = $this->buzzDao->getShares(2);
        $this->assertEquals(2, $result->getFirst()->getPostShared()->getId());
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
        $result = $this->buzzDao->deleteShare('1');

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
        $this->assertEquals('2015-01-10 12:12:12', $result->getLikeTime());
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

        $result = $this->buzzDao->saveCommentShare($comment);
        $this->assertEquals('2015-01-10 12:12:12', $result->getCommentTime());
    }

    /**
     * this is function to test delete comment onshare
     */
    public function testDeleteCommentOnShare() {
        $comment = new Comment();
        $comment->setId(1);
        $comment->setShareId(1);
        $comment->setEmployeeNumber(1);
        $comment->setCommentTime('2015-01-10 12:12:12');
        $comment->setCommentText('this is the first comment');

        $result = $this->buzzDao->deleteCommentForShare($comment);
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

        $result = $this->buzzDao->saveLikeForComment($like);
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
        $this->assertEquals('1', $result);
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

        $this->assertEquals(1, $share->getId());
    }

    /**
     * this is functoin to test get post by id
     */
    public function testGetPostById() {
        $post = $this->buzzDao->getPostById(1);

        $this->assertEquals(1, $post->getId());
    }

    /**
     * this is functoin to test get Comment by id
     */
    public function testGetCommentById() {
        $comment = $this->buzzDao->getCommentById(1);

        $this->assertEquals(1, $comment->getId());
    }

    /**
     * this is functoin to test get likeOnComment by id
     */
    public function testGetLikeOnCommentById() {
        $likeOnComment = $this->buzzDao->getLikeOnCommentById(21);

        $this->assertEquals(21, $likeOnComment->getId());
    }

    /**
     * this is functoin to test get likeOnShare by id
     */
    public function testGetLikeOnshareById() {
        $likeOnShare = $this->buzzDao->getLikeOnShareById(21);

        $this->assertEquals(21, $likeOnShare->getId());
    }

}
