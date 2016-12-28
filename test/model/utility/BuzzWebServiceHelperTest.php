<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BuzzWebServiceHelperUtilityTest
 *
 * @author nirmal
 * @group buzz
 */
class BuzzWebServiceHelperTest extends PHPUnit_Framework_TestCase {

    private $buzzWebServiceHelper;

    /**
     * Set up method
     */
    protected function setUp() {
        $this->buzzWebServiceHelper = new BuzzWebServiceHelper();
    }

    /**
     * @covers BuzzWebServiceHelper::getBuzzService
     */
    public function testGetBuzzService() {
        $buzzService = $this->buzzWebServiceHelper->getBuzzService();
        $this->assertTrue($buzzService instanceof BuzzService);
    }

    /**
     * @covers BuzzWebServiceHelper::getBuzzService
     */
    public function testGetBuzzObjectBuilder() {
        $buzzObjectBuilder = $this->buzzWebServiceHelper->getBuzzObjectBuilder();
        $this->assertTrue($buzzObjectBuilder instanceof BuzzObjectBuilder);
    }

    /**
     * @covers BuzzWebServiceHelper::getBuzzShares
     */
    public function testGetBuzzSharesWithLimit() {
        $shares = array(
            new share()
        );

        $shareArray = array(
            array()
        );

        $shareArray = array(
            array()
        );

        $limit = 1;

        $buzzServiceMock = $this->getMock('BuzzService', array('getShares'));
        $buzzServiceMock->expects($this->once())
                ->method('getShares')
                ->with($limit)
                ->will($this->returnValue($shares));

        $buzzObjectBuilderMock = $this->getMock('BuzzObjectBuilder', array('getShareCollectionArray'));
        $buzzObjectBuilderMock->expects($this->once())
                ->method('getShareCollectionArray')
                ->with($shares)
                ->will($this->returnValue($shareArray));

        $this->buzzWebServiceHelper->setBuzzService($buzzServiceMock);
        $this->buzzWebServiceHelper->setBuzzObjectBuilder($buzzObjectBuilderMock);

        $resultSharesArray = $this->buzzWebServiceHelper->getBuzzShares($limit);
        $this->assertEquals(1, count($resultSharesArray));
    }

    /**
     * @covers BuzzWebServiceHelper::getBuzzShares
     */
    public function testGetBuzzSharesWithoutLimit() {
        $shares = array(
            new share(),
            new share()
        );

        $shareArray = array(
            array(),
            array()
        );

        $buzzServiceMock = $this->getMock('BuzzService', array('getShares'));
        $buzzServiceMock->expects($this->once())
                ->method('getShares')
                ->will($this->returnValue($shares));

        $buzzObjectBuilderMock = $this->getMock('BuzzObjectBuilder', array('getShareCollectionArray'));
        $buzzObjectBuilderMock->expects($this->once())
                ->method('getShareCollectionArray')
                ->with($shares)
                ->will($this->returnValue($shareArray));

        $this->buzzWebServiceHelper->setBuzzService($buzzServiceMock);
        $this->buzzWebServiceHelper->setBuzzObjectBuilder($buzzObjectBuilderMock);

        $shareCollection = $this->buzzWebServiceHelper->getBuzzShares();
        $this->assertEquals(2, count($shareCollection));
    }

    /**
     * @covers BuzzWebServiceHelper::getLatestBuzzShares
     */
    public function testGetLatestBuzzShares() {
        $shares = array(
            new share(),
            new share()
        );

        $shareArray = array(
            array(),
            array()
        );

        $latestShareId = 1;

        $buzzServiceMock = $this->getMock('BuzzService', array('getSharesUptoId'));
        $buzzServiceMock->expects($this->once())
                ->method('getSharesUptoId')
                ->will($this->returnValue($shares));

        $buzzObjectBuilderMock = $this->getMock('BuzzObjectBuilder', array('getShareCollectionArray'));
        $buzzObjectBuilderMock->expects($this->once())
                ->method('getShareCollectionArray')
                ->with($shares)
                ->will($this->returnValue($shareArray));

        $this->buzzWebServiceHelper->setBuzzService($buzzServiceMock);
        $this->buzzWebServiceHelper->setBuzzObjectBuilder($buzzObjectBuilderMock);

        $shareCollection = $this->buzzWebServiceHelper->getLatestBuzzShares($latestShareId);
        $this->assertEquals(2, count($shareCollection));
    }

    
    /**
     * @covers BuzzWebServiceHelper::getMoreBuzzShares
     */
    public function testGetMoreBuzzSharesWithLimit() {
        $shares = array(
            new share()
        );

        $shareArray = array(
            array()
        );

        $lastShareId = 1;
        $limit = 1;

        $buzzServiceMock = $this->getMock('BuzzService', array('getMoreShares'));
        $buzzServiceMock->expects($this->once())
                ->method('getMoreShares')
                ->with($limit)
                ->will($this->returnValue($shares));

        $buzzObjectBuilderMock = $this->getMock('BuzzObjectBuilder', array('getShareCollectionArray'));
        $buzzObjectBuilderMock->expects($this->once())
                ->method('getShareCollectionArray')
                ->with($shares)
                ->will($this->returnValue($shareArray));

        $this->buzzWebServiceHelper->setBuzzService($buzzServiceMock);
        $this->buzzWebServiceHelper->setBuzzObjectBuilder($buzzObjectBuilderMock);

        $resultSharesArray = $this->buzzWebServiceHelper->getMoreBuzzShares($lastShareId, $limit);
        $this->assertEquals(1, count($resultSharesArray));
    }

    /**
     * @covers BuzzWebServiceHelper::getMoreBuzzShares
     */
    public function testGetMoreBuzzSharesWithoutLimit() {
        $shares = array(
            new share(),
            new share()
        );

        $shareArray = array(
            array(),
            array()
        );
        $lastShareId = 1;
        $limit = 1;

        $buzzServiceMock = $this->getMock('BuzzService', array('getMoreShares'));
        $buzzServiceMock->expects($this->once())
                ->method('getMoreShares')
                ->will($this->returnValue($shares));

        $buzzObjectBuilderMock = $this->getMock('BuzzObjectBuilder', array('getShareCollectionArray'));
        $buzzObjectBuilderMock->expects($this->once())
                ->method('getShareCollectionArray')
                ->with($shares)
                ->will($this->returnValue($shareArray));

        $this->buzzWebServiceHelper->setBuzzService($buzzServiceMock);
        $this->buzzWebServiceHelper->setBuzzObjectBuilder($buzzObjectBuilderMock);

        $shareCollection = $this->buzzWebServiceHelper->getMoreBuzzShares($lastShareId, $limit);
        $this->assertEquals(2, count($shareCollection));
    }

    /**
     * @covers BuzzWebServiceHelper::getShareAndPostDetailsByShareId
     */
    public function testGetShareAndPostDetailsByShareId() {
        $shareId = 1;
        $postId = 1;

        $post = new Post();
        $post->setId($postId);
        $post->setPostTime('2015-02-10 00:00:00');

        $share = new Share();
        $share->setId($shareId);
        $share->setPostShared($post);
        $share->setShareTime('2015-02-20 00:00:00');

        $postPhoto = new Photo();
        $postPhoto->setId(1);
        $postPhoto->setFilename('abc.jpg');

        $photos = array(
            $postPhoto
        );

        $shareDetailsArray = array(
            'share' => array('details' => $share->toArray()),
            'post' => array('details' => $post->toArray())
        );

        $shareDetailsArray = array(
            'share' => array('details' => $share->toArray()),
            'post' => array('details' => $post->toArray())
        );

        $buzzServiceMock = $this->getMock('BuzzService', array('getShareById', 'getPostPhotos'));
        $buzzServiceMock->expects($this->once())
                ->method('getShareById')
                ->with($shareId)
                ->will($this->returnValue($share));
        $buzzServiceMock->expects($this->once())
                ->method('getPostPhotos')
                ->with($postId)
                ->will($this->returnValue($photos));

        $buzzObjectBuilderMock = $this->getMock('BuzzObjectBuilder', array('getShareDetailsAsArray'));
        $buzzObjectBuilderMock->expects($this->once())
                ->method('getShareDetailsAsArray')
                ->with($share, $post, $photos)
                ->will($this->returnValue($shareDetailsArray));

        $buzzObjectBuilderMock = $this->getMock('BuzzObjectBuilder', array('getShareDetailsAsArray'));
        $buzzObjectBuilderMock->expects($this->once())
                ->method('getShareDetailsAsArray')
                ->with($share, $post, $photos)
                ->will($this->returnValue($shareDetailsArray));

        $this->buzzWebServiceHelper->setBuzzService($buzzServiceMock);
        $this->buzzWebServiceHelper->setBuzzObjectBuilder($buzzObjectBuilderMock);

        $shareWithPostDetails = $this->buzzWebServiceHelper->getShareAndPostDetailsByShareId($shareId);
        $this->assertEquals('2015-02-20 00:00:00', $shareWithPostDetails['share']['details']['share_time']);
        $this->assertEquals('2015-02-10 00:00:00', $shareWithPostDetails['post']['details']['post_time']);
    }

    /**
     * @covers BuzzWebServiceHelper::postContentOnFeed
     */
    public function testPostContentOnFeedWithoutImages() {
        $shareId = 1;
        $postId = 1;

        $employeeNumber = 1;
        $content = 'Test content';
        $postAndShareDateTime = '2015-02-10 00:00:00';

        $post = new Post();
        $post->setId($postId);
        $post->setEmployeeNumber($employeeNumber);
        $post->setText($content);
        $post->setPostTime($postAndShareDateTime);

        $share = new Share();
        $share->setId($shareId);
        $share->setPostShared($post);
        $share->setEmployeeNumber($employeeNumber);
        $share->setShareTime($postAndShareDateTime);

        $buzzObjectBuilderMock = $this->getMock('BuzzObjectBuilder', array('createPost', 'createShare'));
        $buzzObjectBuilderMock->expects($this->once())
                ->method('createPost')
                ->with($employeeNumber, $content, $postAndShareDateTime)
                ->will($this->returnValue($post));
        $buzzObjectBuilderMock->expects($this->once())
                ->method('createShare')
                ->with($post, $postAndShareDateTime)
                ->will($this->returnValue($share));

        $buzzServiceMock = $this->getMock('BuzzService', array('saveShare'));
        $buzzServiceMock->expects($this->once())
                ->method('saveShare')
                ->will($this->returnValue($share));

        $this->buzzWebServiceHelper->setBuzzService($buzzServiceMock);
        $this->buzzWebServiceHelper->setBuzzObjectBuilder($buzzObjectBuilderMock);

        $returnShare = $this->buzzWebServiceHelper->postContentOnFeed($employeeNumber, $content, $postAndShareDateTime);
        $this->assertEquals($postAndShareDateTime, $returnShare->getShareTime());
    }

    /**
     * @covers BuzzWebServiceHelper::postContentOnFeed
     */
    public function testPostContentOnFeedWithImages() {
        $shareId = 1;
        $postId = 1;

        $imageDataArray = array(
            array(
                BuzzObjectBuilder::KEY_IMAGE_STRING_ENCODED => null,
                BuzzObjectBuilder::KEY_IMAGE_NAME => 'test_image',
                BuzzObjectBuilder::KEY_IMAGE_TYPE => 'jpg'
            )
        );

        $extraPostOptions = array(
            BuzzObjectBuilder::KEY_IMAGE_DATA => json_encode($imageDataArray)
        );

        $photo = new Photo();
        $photo->setFilename('test_image');
        $photo->setFileType('jpg');

        $imagesArray = array(
            $photo
        );

        $employeeNumber = 1;
        $content = 'Test content';
        $postAndShareDateTime = '2015-02-10 00:00:00';

        $post = new Post();
        $post->setId($postId);
        $post->setEmployeeNumber($employeeNumber);
        $post->setText($content);
        $post->setPostTime($postAndShareDateTime);

        $share = new Share();
        $share->setId($shareId);
        $share->setPostShared($post);
        $share->setEmployeeNumber($employeeNumber);
        $share->setShareTime($postAndShareDateTime);
        $share->setPostId($postId);

        $buzzObjectBuilderMock = $this->getMock('BuzzObjectBuilder', array('createPost', 'createShare', 'extractImagesForPost'));
        $buzzObjectBuilderMock->expects($this->once())
                ->method('createPost')
                ->with($employeeNumber, $content, $postAndShareDateTime)
                ->will($this->returnValue($post));
        $buzzObjectBuilderMock->expects($this->once())
                ->method('createShare')
                ->with($post, $postAndShareDateTime)
                ->will($this->returnValue($share));
        $buzzObjectBuilderMock->expects($this->once())
                ->method('extractImagesForPost')
                ->with($extraPostOptions, $share->getPostId())
                ->will($this->returnValue($imagesArray));

        $buzzServiceMock = $this->getMock('BuzzService', array('saveShare', 'savePhoto'));
        $buzzServiceMock->expects($this->once())
                ->method('saveShare')
                ->will($this->returnValue($share));
        $buzzServiceMock->expects($this->once())
                ->method('savePhoto')
                ->with($photo)
                ->will($this->returnValue($photo));

        $this->buzzWebServiceHelper->setBuzzService($buzzServiceMock);
        $this->buzzWebServiceHelper->setBuzzObjectBuilder($buzzObjectBuilderMock);

        $returnShare = $this->buzzWebServiceHelper->postContentOnFeed($employeeNumber, $content, $postAndShareDateTime, $extraPostOptions);
        $this->assertEquals($postAndShareDateTime, $returnShare->getShareTime());
    }

    /**
     * @covers BuzzWebServiceHelper::commentOnShare
     */
    public function testCommentOnShare() {
        $employeeNumber = 1;
        $content = 'Test content';
        $postAndShareDateTime = '2015-02-10 00:00:00';
        $shareId = 1;

        $comment = new Comment();
        $comment->setShareId($shareId);
        $comment->setEmployeeNumber($employeeNumber);
        $comment->setCommentText($content);
        $comment->setCommentTime($postAndShareDateTime);
        $comment->setNumberOfLikes(0);
        $comment->setNumberOfUnlikes(0);

        $buzzObjectBuilderMock = $this->getMock('BuzzObjectBuilder', array('createCommentOnShare'));
        $buzzObjectBuilderMock->expects($this->once())
                ->method('createCommentOnShare')
                ->with($shareId, $employeeNumber, $content, $postAndShareDateTime)
                ->will($this->returnValue($comment));

        $buzzServiceMock = $this->getMock('BuzzService', array('saveCommentShare'));
        $buzzServiceMock->expects($this->once())
                ->method('saveCommentShare')
                ->will($this->returnValue($comment));

        $this->buzzWebServiceHelper->setBuzzService($buzzServiceMock);
        $this->buzzWebServiceHelper->setBuzzObjectBuilder($buzzObjectBuilderMock);

        $returnComment = $this->buzzWebServiceHelper->commentOnShare($shareId, $employeeNumber, $content, $postAndShareDateTime);
        $this->assertEquals($postAndShareDateTime, $returnComment->getCommentTime());
    }

    /**
     * This method tests the scenario where an employee who has disliked before liking now on a share
     * @covers BuzzWebServiceHelper::likeOnShare
     */
    public function testLikeOnShare() {
        $employeeNumber = 1;
        $testDateTime = '2015-02-10 00:00:00';
        $shareId = 1;

        $likeOnShare = new LikeOnShare();
        $likeOnShare->setShareId($shareId);
        $likeOnShare->setEmployeeNumber($employeeNumber);
        $likeOnShare->setLikeTime($testDateTime);

        $dislikeOnShare = new UnLikeOnShare();
        $dislikeOnShare->setShareId($shareId);
        $dislikeOnShare->setEmployeeNumber($employeeNumber);
        $dislikeOnShare->setLikeTime($testDateTime);

        $likesOnShare = new Doctrine_Collection('LikeOnShare');
        $likesOnShare->add($likeOnShare);

        $dislikesOnShare = new Doctrine_Collection('UnLikeOnShare');
        $dislikesOnShare->add($dislikeOnShare);

        $share = new Share();
        $share->setUnlike($dislikesOnShare);
        $share->setNumberOfUnlikes(1);
        $share->setNumberOfLikes(0);

        $shareUpdated = new Share();
        $shareUpdated->setLike($likesOnShare);
        $shareUpdated->setNumberOfUnlikes(0);
        $shareUpdated->setNumberOfLikes(1);

        $buzzObjectBuilderMock = $this->getMock('BuzzObjectBuilder', array('createLikeOnShare', 'createDislikeOnShare'));
        $buzzObjectBuilderMock->expects($this->once())
                ->method('createLikeOnShare')
                ->with($shareId, $employeeNumber, $testDateTime)
                ->will($this->returnValue($likeOnShare));
        $buzzObjectBuilderMock->expects($this->once())
                ->method('createDislikeOnShare')
                ->with($shareId, $employeeNumber, $testDateTime)
                ->will($this->returnValue($dislikeOnShare));

        $buzzServiceMock = $this->getMock('BuzzService', array('getShareById', 'deleteUnLikeForShare', 'saveLikeForShare'));
        $buzzServiceMock->expects($this->any())
                ->method('getShareById')
                ->with($shareId)
                ->will($this->onConsecutiveCalls($share, $shareUpdated));
        $buzzServiceMock->expects($this->any())
                ->method('deleteUnLikeForShare')
                ->with($dislikeOnShare)
                ->will($this->returnValue(1));
        $buzzServiceMock->expects($this->any())
                ->method('saveLikeForShare')
                ->with($likeOnShare)
                ->will($this->returnValue($likeOnShare));

        $this->buzzWebServiceHelper->setBuzzService($buzzServiceMock);
        $this->buzzWebServiceHelper->setBuzzObjectBuilder($buzzObjectBuilderMock);

        $resultShare = $this->buzzWebServiceHelper->likeOnShare($shareId, $employeeNumber, $testDateTime);
        $this->assertTrue($resultShare instanceof Share);
        $this->assertEquals(1, $resultShare->getNumberOfLikes());
    }

    /**
     * This method tests the scenario where an employee who has liked before disliking now on a share
     * @covers BuzzWebServiceHelper::dislikeOnShare
     */
    public function testDislikeOnShare() {
        $employeeNumber = 1;
        $testDateTime = '2015-02-10 00:00:00';
        $shareId = 1;

        $likeOnShare = new LikeOnShare();
        $likeOnShare->setShareId($shareId);
        $likeOnShare->setEmployeeNumber($employeeNumber);
        $likeOnShare->setLikeTime($testDateTime);

        $dislikeOnShare = new UnLikeOnShare();
        $dislikeOnShare->setShareId($shareId);
        $dislikeOnShare->setEmployeeNumber($employeeNumber);
        $dislikeOnShare->setLikeTime($testDateTime);

        $likesOnShare = new Doctrine_Collection('LikeOnShare');
        $likesOnShare->add($likeOnShare);

        $dislikesOnShare = new Doctrine_Collection('UnLikeOnShare');
        $dislikesOnShare->add($dislikeOnShare);

        $share = new Share();
        $share->setLike($likesOnShare);
        $share->setNumberOfUnlikes(0);
        $share->setNumberOfLikes(1);

        $shareUpdated = new Share();
        $shareUpdated->setUnlike($dislikesOnShare);
        $shareUpdated->setNumberOfUnlikes(1);
        $shareUpdated->setNumberOfLikes(0);

        $buzzObjectBuilderMock = $this->getMock('BuzzObjectBuilder', array('createLikeOnShare', 'createDislikeOnShare'));
        $buzzObjectBuilderMock->expects($this->once())
                ->method('createLikeOnShare')
                ->with($shareId, $employeeNumber, $testDateTime)
                ->will($this->returnValue($likeOnShare));
        $buzzObjectBuilderMock->expects($this->once())
                ->method('createDislikeOnShare')
                ->with($shareId, $employeeNumber, $testDateTime)
                ->will($this->returnValue($dislikeOnShare));

        $buzzServiceMock = $this->getMock('BuzzService', array('getShareById', 'deleteLikeForShare', 'saveUnLikeForShare'));
        $buzzServiceMock->expects($this->any())
                ->method('getShareById')
                ->with($shareId)
                ->will($this->onConsecutiveCalls($share, $shareUpdated));
        $buzzServiceMock->expects($this->any())
                ->method('deleteLikeForShare')
                ->with($likeOnShare)
                ->will($this->returnValue(1));
        $buzzServiceMock->expects($this->any())
                ->method('saveUnLikeForShare')
                ->with($dislikeOnShare)
                ->will($this->returnValue($dislikeOnShare));

        $this->buzzWebServiceHelper->setBuzzService($buzzServiceMock);
        $this->buzzWebServiceHelper->setBuzzObjectBuilder($buzzObjectBuilderMock);

        $resultShare = $this->buzzWebServiceHelper->dislikeOnShare($shareId, $employeeNumber, $testDateTime);
        $this->assertTrue($resultShare instanceof Share);
        $this->assertEquals(1, $resultShare->getNumberOfUnlikes());
    }

    /**
     * This method tests the scenario where an employee who has disliked before liking now on a comment
     * @covers BuzzWebServiceHelper::likeOnComment
     */
    public function testLikeOnComment() {
        $employeeNumber = 1;
        $testDateTime = '2015-02-10 00:00:00';
        $commentId = 1;

        $likeOnComment = new LikeOnComment();
        $likeOnComment->setCommentId($commentId);
        $likeOnComment->setEmployeeNumber($employeeNumber);
        $likeOnComment->setLikeTime($testDateTime);

        $dislikeOnComment = new UnLikeOnComment();
        $dislikeOnComment->setCommentId($commentId);
        $dislikeOnComment->setEmployeeNumber($employeeNumber);
        $dislikeOnComment->setLikeTime($testDateTime);

        $likesOnComment = new Doctrine_Collection('LikeOnComment');
        $likesOnComment->add($likeOnComment);

        $dislikesOnComment = new Doctrine_Collection('UnLikeOnComment');
        $dislikesOnComment->add($dislikeOnComment);

        $comment = new Comment();
        $comment->setUnlike($dislikesOnComment);
        $comment->setNumberOfUnlikes(1);
        $comment->setNumberOfLikes(0);

        $commentUpdated = new Comment();
        $commentUpdated->setLike($likesOnComment);
        $commentUpdated->setNumberOfUnlikes(0);
        $commentUpdated->setNumberOfLikes(1);

        $buzzObjectBuilderMock = $this->getMock('BuzzObjectBuilder', array('createLikeOnComment', 'createDislikeOnComment'));
        $buzzObjectBuilderMock->expects($this->once())
                ->method('createLikeOnComment')
                ->with($commentId, $employeeNumber, $testDateTime)
                ->will($this->returnValue($likeOnComment));
        $buzzObjectBuilderMock->expects($this->once())
                ->method('createDislikeOnComment')
                ->with($commentId, $employeeNumber, $testDateTime)
                ->will($this->returnValue($dislikeOnComment));

        $buzzServiceMock = $this->getMock('BuzzService', array('getCommentById', 'deleteUnLikeForComment', 'saveLikeForComment'));
        $buzzServiceMock->expects($this->any())
                ->method('getCommentById')
                ->with($commentId)
                ->will($this->onConsecutiveCalls($comment, $commentUpdated));
        $buzzServiceMock->expects($this->once())
                ->method('deleteUnLikeForComment')
                ->with($dislikeOnComment)
                ->will($this->returnValue(1));
        $buzzServiceMock->expects($this->once())
                ->method('saveLikeForComment')
                ->with($likeOnComment)
                ->will($this->returnValue($likeOnComment));

        $this->buzzWebServiceHelper->setBuzzService($buzzServiceMock);
        $this->buzzWebServiceHelper->setBuzzObjectBuilder($buzzObjectBuilderMock);

        $resultComment = $this->buzzWebServiceHelper->likeOnComment($commentId, $employeeNumber, $testDateTime);
        $this->assertTrue($resultComment instanceof Comment);
        $this->assertEquals(1, $resultComment->getNumberOfLikes());
    }

    /**
     * This method tests the scenario where an employee who has liked before disliking now on a comment 
     * @covers BuzzWebServiceHelper::dislikeOnComment
     */
    public function testDislikeOnComment() {
        $employeeNumber = 1;
        $testDateTime = '2015-02-10 00:00:00';
        $commentId = 1;

        $likeOnComment = new LikeOnComment();
        $likeOnComment->setCommentId($commentId);
        $likeOnComment->setEmployeeNumber($employeeNumber);
        $likeOnComment->setLikeTime($testDateTime);

        $dislikeOnComment = new UnLikeOnComment();
        $dislikeOnComment->setCommentId($commentId);
        $dislikeOnComment->setEmployeeNumber($employeeNumber);
        $dislikeOnComment->setLikeTime($testDateTime);

        $likesOnComment = new Doctrine_Collection('LikeOnComment');
        $likesOnComment->add($likeOnComment);

        $dislikesOnComment = new Doctrine_Collection('UnLikeOnComment');
        $dislikesOnComment->add($dislikeOnComment);

        $comment = new Comment();
        $comment->setLike($likesOnComment);
        $comment->setNumberOfUnlikes(0);
        $comment->setNumberOfLikes(1);

        $commentUpdated = new Comment();
        $commentUpdated->setUnlike($dislikesOnComment);
        $commentUpdated->setNumberOfUnlikes(1);
        $commentUpdated->setNumberOfLikes(0);

        $buzzObjectBuilderMock = $this->getMock('BuzzObjectBuilder', array('createLikeOnComment', 'createDislikeOnComment'));
        $buzzObjectBuilderMock->expects($this->once())
                ->method('createLikeOnComment')
                ->with($commentId, $employeeNumber, $testDateTime)
                ->will($this->returnValue($likeOnComment));
        $buzzObjectBuilderMock->expects($this->once())
                ->method('createDislikeOnComment')
                ->with($commentId, $employeeNumber, $testDateTime)
                ->will($this->returnValue($dislikeOnComment));

        $buzzServiceMock = $this->getMock('BuzzService', array('getCommentById', 'deleteLikeForComment', 'saveUnLikeForComment'));
        $buzzServiceMock->expects($this->any())
                ->method('getCommentById')
                ->with($commentId)
                ->will($this->onConsecutiveCalls($comment, $commentUpdated));
        $buzzServiceMock->expects($this->once())
                ->method('deleteLikeForComment')
                ->with($likeOnComment)
                ->will($this->returnValue(1));
        $buzzServiceMock->expects($this->once())
                ->method('saveUnLikeForComment')
                ->with($dislikeOnComment)
                ->will($this->returnValue($dislikeOnComment));

        $this->buzzWebServiceHelper->setBuzzService($buzzServiceMock);
        $this->buzzWebServiceHelper->setBuzzObjectBuilder($buzzObjectBuilderMock);

        $resultComment = $this->buzzWebServiceHelper->dislikeOnComment($commentId, $employeeNumber, $testDateTime);
        $this->assertTrue($resultComment instanceof Comment);
        $this->assertEquals(1, $resultComment->getNumberOfUnlikes());
    }
    
    public function testGetBuzzForEmployee() {
        $empNum = 1;
                
        $shareOne = new Share();
        $shareOne->setId(1);
        $shareOne->setEmployeeNumber($empNum);

        $shareTwo = new Share();
        $shareTwo->setId(2);
        $shareTwo->setEmployeeNumber($empNum);
        $shareArray = array($shareOne, $shareTwo);
        
        $buzzServiceMock = $this->getMock('BuzzService', array('getSharesFromEmployeeNumber'));
        $buzzServiceMock->expects($this->once())
                ->method('getSharesFromEmployeeNumber')
                ->with($empNum)
                ->will($this->returnValue($shareArray));

        $buzzObjectBuilderMock = $this->getMock('BuzzObjectBuilder', array('getShareCollectionArray'));
        $buzzObjectBuilderMock->expects($this->any())
                ->method('getShareCollectionArray')
                ->with($shareArray)
                ->will($this->returnValue($shareArray));

        $this->buzzWebServiceHelper->setBuzzService($buzzServiceMock);
        $this->buzzWebServiceHelper->setBuzzObjectBuilder($buzzObjectBuilderMock);

        $returnShareCollection = $this->buzzWebServiceHelper->getBuzzForEmployee($empNum, 1);
        $this->assertEquals(2, count($returnShareCollection));
        $this->assertTrue(is_array($returnShareCollection));
    }

    public function testDeleteShareWithExistingShareId() {
        $shareId = 1;
        $loggedInEmployeeNumber = 1;

        $share = new Share();
        $share->setId($shareId);
        $share->setEmployeeNumber($loggedInEmployeeNumber);

        $mockBuzzService = $this->getMock('buzzService', array('getShareById', 'deleteShare'));
        $mockBuzzService->expects($this->once())
                ->method('getShareById')
                ->with($shareId)
                ->will($this->returnValue($share));

        $mockBuzzService->expects($this->once())
                ->method('deleteShare')
                ->with($shareId)
                ->will($this->returnValue(1));
        $this->buzzWebServiceHelper->setBuzzService($mockBuzzService);

        $responseArray = $this->buzzWebServiceHelper->deleteShare($shareId, $loggedInEmployeeNumber);

        $this->assertTrue(is_array($responseArray));
        $this->assertTrue($responseArray['success']);
    }

    public function testDeleteShareWithNonExistingShareId() {
        $shareId = 1;
        $loggedInEmployeeNumber = 1;

        $mockBuzzService = $this->getMock('buzzService', array('getShareById'));
        $mockBuzzService->expects($this->once())
                ->method('getShareById')
                ->with($shareId)
                ->will($this->returnValue(false));
        $this->buzzWebServiceHelper->setBuzzService($mockBuzzService);

        $responseArray = $this->buzzWebServiceHelper->deleteShare($shareId, $loggedInEmployeeNumber);
        $this->assertTrue(is_array($responseArray));
        $this->assertFalse($responseArray['success']);
    }

    public function testDeleteShareWithExistingShareIdThatDoNotBelongToCurrentEmployee() {
        $shareId = 1;
        $loggedInEmployeeNumber = 1;
        $share = new Share();
        $share->setId($shareId);
        $share->setEmployeeNumber(12);

        $mockBuzzService = $this->getMock('buzzService', array('getShareById'));
        $mockBuzzService->expects($this->once())
                ->method('getShareById')
                ->with($shareId)
                ->will($this->returnValue($share));
        $this->buzzWebServiceHelper->setBuzzService($mockBuzzService);

        $responseArray = $this->buzzWebServiceHelper->deleteShare($shareId, $loggedInEmployeeNumber);
        $this->assertTrue(is_array($responseArray));
        $this->assertFalse($responseArray['success']);
    }

    public function testDeleteCommentWithExistingCommentId() {
        $commentId = 1;
        $loggedInEmployeeNumber = 1;

        $comment = new Comment();
        $comment->setId($commentId);
        $comment->setEmployeeNumber($loggedInEmployeeNumber);

        $mockBuzzService = $this->getMock('buzzService', array('getCommentById', 'deleteCommentForShare'));
        $mockBuzzService->expects($this->once())
                ->method('getCommentById')
                ->with($commentId)
                ->will($this->returnValue($comment));

        $mockBuzzService->expects($this->once())
                ->method('deleteCommentForShare')
                ->with($comment)
                ->will($this->returnValue(1));
        $this->buzzWebServiceHelper->setBuzzService($mockBuzzService);

        $responseArray = $this->buzzWebServiceHelper->deleteCommentForShare($commentId, $loggedInEmployeeNumber);

        $this->assertTrue(is_array($responseArray));
        $this->assertTrue($responseArray['success']);
    }

    public function testDeleteCommentWithNonExistingComentId() {
        $commentId = 1;
        $loggedInEmployeeNumber = 1;

        $mockBuzzService = $this->getMock('buzzService', array('getCommentById'));
        $mockBuzzService->expects($this->once())
                ->method('getCommentById')
                ->with($commentId)
                ->will($this->returnValue(false));
        $this->buzzWebServiceHelper->setBuzzService($mockBuzzService);

        $responseArray = $this->buzzWebServiceHelper->deleteCommentForShare($commentId, $loggedInEmployeeNumber);
        $this->assertTrue(is_array($responseArray));
        $this->assertFalse($responseArray['success']);
    }

    public function testDeleteCommentWithExistingComentIdThatDoNotBelongToCurrentEmployee() {
        $commentId = 1;
        $loggedInEmployeeNumber = 1;

        $comment = new Comment();
        $comment->setId($commentId);
        $comment->setEmployeeNumber(14);

        $mockBuzzService = $this->getMock('buzzService', array('getCommentById'));
        $mockBuzzService->expects($this->once())
                ->method('getCommentById')
                ->with($commentId)
                ->will($this->returnValue($comment));

        $this->buzzWebServiceHelper->setBuzzService($mockBuzzService);

        $responseArray = $this->buzzWebServiceHelper->deleteCommentForShare($commentId, $loggedInEmployeeNumber);
        $this->assertTrue(is_array($responseArray));
        $this->assertFalse($responseArray['success']);
    }

}
