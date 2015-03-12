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
     * @covers BuzzWebServiceHelper::getLatestBuzzShares
     */
    public function testGetLatestBuzzSharesWithLimit() {
        $shares = array(
            new share()
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

        $buzzObjetBuilderMock = $this->getMock('BuzzObjectBuilder', array('getShareCollectionArray'));
        $buzzObjetBuilderMock->expects($this->once())
                ->method('getShareCollectionArray')
                ->with($shares)
                ->will($this->returnValue($shareArray));

        $this->buzzWebServiceHelper->setBuzzService($buzzServiceMock);
        $this->buzzWebServiceHelper->setBuzzObjectBuilder($buzzObjetBuilderMock);

        $resultSharesArray = $this->buzzWebServiceHelper->getLatestBuzzShares($limit);
        $this->assertEquals(1, count($resultSharesArray));
    }

    /**
     * @covers BuzzWebServiceHelper::getLatestBuzzShares
     */
    public function testGetLatestBuzzSharesWithoutLimit() {
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

        $buzzObjetBuilderMock = $this->getMock('BuzzObjectBuilder', array('getShareCollectionArray'));
        $buzzObjetBuilderMock->expects($this->once())
                ->method('getShareCollectionArray')
                ->with($shares)
                ->will($this->returnValue($shareArray));

        $this->buzzWebServiceHelper->setBuzzService($buzzServiceMock);
        $this->buzzWebServiceHelper->setBuzzObjectBuilder($buzzObjetBuilderMock);

        $shareCollection = $this->buzzWebServiceHelper->getLatestBuzzShares();
        $this->assertEquals(2, count($shareCollection));
    }

    /**
     * @covers BuzzWebServiceHelper::getLatestBuzzShares
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

        $buzzServiceMock = $this->getMock('BuzzService', array('getShareById', 'getPostPhotos'));
        $buzzServiceMock->expects($this->once())
                ->method('getShareById')
                ->with($shareId)
                ->will($this->returnValue($share));
        $buzzServiceMock->expects($this->once())
                ->method('getPostPhotos')
                ->with($postId)
                ->will($this->returnValue($photos));

        $buzzObjetBuilderMock = $this->getMock('BuzzObjectBuilder', array('getShareDetailsAsArray'));
        $buzzObjetBuilderMock->expects($this->once())
                ->method('getShareDetailsAsArray')
                ->with($share, $post, $photos)
                ->will($this->returnValue($shareDetailsArray));

        $this->buzzWebServiceHelper->setBuzzService($buzzServiceMock);
        $this->buzzWebServiceHelper->setBuzzObjectBuilder($buzzObjetBuilderMock);

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

        $buzzObjetBuilderMock = $this->getMock('BuzzObjectBuilder', array('createPost', 'createShare'));
        $buzzObjetBuilderMock->expects($this->once())
                ->method('createPost')
                ->with($employeeNumber, $content, $postAndShareDateTime)
                ->will($this->returnValue($post));
        $buzzObjetBuilderMock->expects($this->once())
                ->method('createShare')
                ->with($post, $postAndShareDateTime)
                ->will($this->returnValue($share));

        $buzzServiceMock = $this->getMock('BuzzService', array('saveShare'));
        $buzzServiceMock->expects($this->once())
                ->method('saveShare')
                ->will($this->returnValue($share));

        $this->buzzWebServiceHelper->setBuzzService($buzzServiceMock);
        $this->buzzWebServiceHelper->setBuzzObjectBuilder($buzzObjetBuilderMock);

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
            BuzzObjectBuilder::KEY_IMAGE_DATA =>  json_encode($imageDataArray)
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

        $buzzObjetBuilderMock = $this->getMock('BuzzObjectBuilder', array('createPost', 'createShare', 'extractImagesForPost'));
        $buzzObjetBuilderMock->expects($this->once())
                ->method('createPost')
                ->with($employeeNumber, $content, $postAndShareDateTime)
                ->will($this->returnValue($post));
        $buzzObjetBuilderMock->expects($this->once())
                ->method('createShare')
                ->with($post, $postAndShareDateTime)
                ->will($this->returnValue($share));
        $buzzObjetBuilderMock->expects($this->once())
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
        $this->buzzWebServiceHelper->setBuzzObjectBuilder($buzzObjetBuilderMock);

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

        $buzzObjetBuilderMock = $this->getMock('BuzzObjectBuilder', array('createCommentOnShare'));
        $buzzObjetBuilderMock->expects($this->once())
                ->method('createCommentOnShare')
                ->with($shareId, $employeeNumber, $content, $postAndShareDateTime)
                ->will($this->returnValue($comment));

        $buzzServiceMock = $this->getMock('BuzzService', array('saveCommentShare'));
        $buzzServiceMock->expects($this->once())
                ->method('saveCommentShare')
                ->will($this->returnValue($comment));

        $this->buzzWebServiceHelper->setBuzzService($buzzServiceMock);
        $this->buzzWebServiceHelper->setBuzzObjectBuilder($buzzObjetBuilderMock);

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

        $buzzObjetBuilderMock = $this->getMock('BuzzObjectBuilder', array('createLikeOnShare', 'createDislikeOnShare'));
        $buzzObjetBuilderMock->expects($this->once())
                ->method('createLikeOnShare')
                ->with($shareId, $employeeNumber, $testDateTime)
                ->will($this->returnValue($likeOnShare));
        $buzzObjetBuilderMock->expects($this->once())
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
        $this->buzzWebServiceHelper->setBuzzObjectBuilder($buzzObjetBuilderMock);

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

        $buzzObjetBuilderMock = $this->getMock('BuzzObjectBuilder', array('createLikeOnShare', 'createDislikeOnShare'));
        $buzzObjetBuilderMock->expects($this->once())
                ->method('createLikeOnShare')
                ->with($shareId, $employeeNumber, $testDateTime)
                ->will($this->returnValue($likeOnShare));
        $buzzObjetBuilderMock->expects($this->once())
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
        $this->buzzWebServiceHelper->setBuzzObjectBuilder($buzzObjetBuilderMock);

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

        $buzzObjetBuilderMock = $this->getMock('BuzzObjectBuilder', array('createLikeOnComment', 'createDislikeOnComment'));
        $buzzObjetBuilderMock->expects($this->once())
                ->method('createLikeOnComment')
                ->with($commentId, $employeeNumber, $testDateTime)
                ->will($this->returnValue($likeOnComment));
        $buzzObjetBuilderMock->expects($this->once())
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
        $this->buzzWebServiceHelper->setBuzzObjectBuilder($buzzObjetBuilderMock);

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

        $buzzObjetBuilderMock = $this->getMock('BuzzObjectBuilder', array('createLikeOnComment', 'createDislikeOnComment'));
        $buzzObjetBuilderMock->expects($this->once())
                ->method('createLikeOnComment')
                ->with($commentId, $employeeNumber, $testDateTime)
                ->will($this->returnValue($likeOnComment));
        $buzzObjetBuilderMock->expects($this->once())
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
        $this->buzzWebServiceHelper->setBuzzObjectBuilder($buzzObjetBuilderMock);

        $resultComment = $this->buzzWebServiceHelper->dislikeOnComment($commentId, $employeeNumber, $testDateTime);
        $this->assertTrue($resultComment instanceof Comment);
        $this->assertEquals(1, $resultComment->getNumberOfUnlikes());
    }

}
