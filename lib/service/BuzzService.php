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

class BuzzService extends BaseService {

    static $lastLoadedPostId = 0;

    /**
     * this is function to get buzzDao 
     * @return buzzDao
     */
    public function getBuzzDao() {
        if (!$this->buzzDao) {
            $this->buzzDao = new buzzDao();
        }
        return $this->buzzDao;
    }
    
    public function setBuzzDao($buzzDao){
        $this->buzzDao=$buzzDao;
    }

    /**
     * get most resent share by giving limit
     * 
     * @param int $limit
     * @return share collection
     * @throws DaoException
     */
    public function getShares($limit) {
        try {
            $shares=$this->getBuzzDao()->getShares($limit);
           if(count($shares)>0){
                BuzzService::$lastLoadedPostId = $shares->getLast()->getId();
            }
            return $shares;
// @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
// @codeCoverageIgnoreEnd
    }

    public function getEmployee($empNumber) {
        return $this->getBuzzDao()->getEmployee($empNumber);
    }

    public function getEmployeesHavingBdaysBetween($fromDate, $toDate) {
        return $this->getBuzzDao()->getEmployeesHavingBdaysBetween($fromDate, $toDate);
    }

    public function getEmployeesHavingAnniversaryOn($month) {
        $empList = $this->getBuzzDao()->getEmployeesHavingAnniversaryOn($month);
        $empArray = array();
        $today = date("d");
        foreach ($empList as $employee) {
            $today = strtotime($today);
            $joined_date = date('d', strtotime($employee['joined_date']));

            if ($joined_date > $today) {
                array_push($empArray, $employee);
            }
        }
        return $empArray;
    }

    public function getNoOfSharesBy($userId) {
        return $this->getBuzzDao()->getNoOfSharesBy($userId);
    }

    public function getNoOfCommentsBy($userId) {
        return $this->getBuzzDao()->getNoOfCommentsBy($userId);
    }

    public function getNoOfShareLikesFor($userId) {
        return $this->getBuzzDao()->getNoOfShareLikesFor($userId);
    }

    public function getEmployeeList() {
        return $this->getBuzzDao()->getEmployeeList();
    }

    public function getMostLikedShares($shareCount) {
        return $this->getBuzzDao()->getMostLikedShares($shareCount);
    }
    
    public function getMostCommentedShares($shareCount) {
        return $this->getBuzzDao()->getMostCommentedShares($shareCount);
    }

    public function getNoOfCommentLikesFor($userId) {
        return $this->getBuzzDao()->getNoOfCommentLikesFor($userId);
    }

    public function getNoOfCommentsFor($userId) {
        return $this->getBuzzDao()->getNoOfCommentsFor($userId);
    }

    public function getEmployeePicture($empNumber) {
        return $this->getBuzzDao()->getEmployeePicture($empNumber);
    }

    public function getMoreShares($limit, $fromId) {
        try {
            $shares = $this->getBuzzDao()->getMoreShares($limit, $fromId);
            // @codeCoverageIgnoreStart
            if(count($shares)>0){
            $this->lastLoadedPostId = $shares->getLast()->getId();
            }
            // @codeCoverageIgnoreEnd
            return $shares;
// @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
// @codeCoverageIgnoreEnd
    }

    public function getMoreProfileShares($limit, $fromId, $userId) {
        try {
            $shares = $this->getBuzzDao()->getMoreProfileShares($limit, $fromId, $userId);
            // @codeCoverageIgnoreStart
            if(count($shares)>0){
            $this->lastLoadedPostId = $shares->getLast()->getId();
            }
            // @codeCoverageIgnoreEnd
            return $shares;
// @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
// @codeCoverageIgnoreEnd
    }

    public function getProfileSharesUptoId($lastId, $useId) {
        try {
            $shares = $this->getBuzzDao()->getProfileSharesUptoId($lastId, $useId);
            return $shares;
// @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
// @codeCoverageIgnoreEnd
    }

    public function getSharesUptoId($lastId) {
        try {
            $shares = $this->getBuzzDao()->getSharesUptoId($lastId);
            return $shares;
// @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
// @codeCoverageIgnoreEnd
    }

    /**
     * get share By \Id
     * 
     * @param int $limit
     * @return Share
     * @throws DaoException
     */
    public function getShareById($shareId) {
        try {
            return $this->getBuzzDao()->getShareById($shareId);
// @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new Exception("Share Not Found");
        }
// @codeCoverageIgnoreEnd
    }

    public function getSharesByUserId($limit, $UserId) {
        try {
            return $this->getBuzzDao()->getSharesByUserId($limit, $UserId);
// @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
// @codeCoverageIgnoreEnd
    }

    /**
     * get Post BY Id
     * 
     * @param int $postId
     * @return share collection
     * @throws DaoException
     */
    public function getPostById($postId) {
        try {
            return $this->getBuzzDao()->getPostById($postId);
//            $this->getBuzzDao()->getPostById($postId)->getText(); die;
// @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new Exception("Post Not Found");
        }
// @codeCoverageIgnoreEnd
    }

    /**
     * get comment by It Id
     * 
     * @param int $limit
     * @return share collection
     * @throws DaoException
     */
    public function getCommentById($commentId) {
        try {
            return $this->getBuzzDao()->getCommentById($commentId);
// @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new Exception("Comment Not Found");
        }
// @codeCoverageIgnoreEnd
    }

    /**
     * get comment by It Id
     * 
     * @param int $limit
     * @return share collection
     * @throws DaoException
     */
    public function getLikeOnCommentById($likeId) {
        try {
            return $this->getBuzzDao()->getLikeOnCommentById($likeId);
// @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new Exception("Like Not Found");
        }
// @codeCoverageIgnoreEnd
    }

    /**
     * get comment by It Id
     * 
     * @param int $limit
     * @return share collection
     * @throws DaoException
     */
    public function getLikeOnShareById($likeId) {
        try {
            return $this->getBuzzDao()->getLikeOnShareById($likeId);
// @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new Exception("Like Not Found");
        }
// @codeCoverageIgnoreEnd
    }

    /**
     * save likes that employee did
     * 
     * @param Like $like
     * @return Like
     * @throws DaoException
     */
    public function saveLikeForShare($like) {
       // try {
            $share = $like->getShareLike();
            $numberOfLikes = $share->getNumberOfLikes() + 1;
            $share->setNumberOfLikes($numberOfLikes);
            $this->getBuzzDao()->saveShare($share);
            return $this->getBuzzDao()->saveLikeForShare($like);
// @codeCoverageIgnoreStart
//        } catch (Exception $e) {
//            throw new Exception("Share Not Found");
//        }
// @codeCoverageIgnoreEnd
    }

    /**
     * delete employee likes for shares
     * 
     * @param Like $like
     * @return string number of deletions
     * @throws DaoException
     */
    public function deleteLikeForShare($like) {
        try {
            $share = $like->getShareLike();
            $numberOfLikes = $share->getNumberOfLikes() - 1;
            $share->setNumberOfLikes($numberOfLikes);
            $this->getBuzzDao()->saveShare($share);
            return $this->getBuzzDao()->deleteLikeForShare($like);
// @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new Exception("Share Not Found");
        }
// @codeCoverageIgnoreEnd
    }
    
    public function saveUnLikeForShare($like) {
        try {
            $share = $like->getShareUnLike();
            $numberOfLikes = $share->getNumberOfUnlikes() + 1;
            $share->setNumberOfUnlikes($numberOfLikes);
            $this->getBuzzDao()->saveShare($share);
            return $this->getBuzzDao()->saveUnLikeForShare($like);
// @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new Exception("Shareggg Found");
        }
// @codeCoverageIgnoreEnd
    }
    
    public function deleteUnLikeForShare($like) {
        try {
            $share = $like->getShareUnLike();
            $numberOfLikes = $share->getNumberOfUnlikes() - 1;
            $share->setNumberOfUnlikes($numberOfLikes);
            $this->getBuzzDao()->saveShare($share);
            return $this->getBuzzDao()->deleteUnLikeForShare($like);
// @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new Exception("Share Not Found");
        }
// @codeCoverageIgnoreEnd
    }

    /**
     * save employee like on comments
     * 
     * @param LikeOnComment $like
     * @return LikeOnComment
     * @throws DaoException
     */
    public function saveLikeForComment($like) {
        try {
            $comment = $like->getCommentLike();
            $numberOfLikes = ($comment->getNumberOfLikes()) + 1;
            $comment->setNumberOfLikes($numberOfLikes);
            $this->getBuzzDao()->saveCommentShare($comment);
            return $this->getBuzzDao()->saveLikeForComment($like);
// @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new Exception("Comment Not Found");
        }
// @codeCoverageIgnoreEnd
    }

    /**
     * 
     * @param LikeOnComment $like
     * @return string number of dlete items
     * @throws DaoException
     */
    public function deleteLikeForComment($like) {
        try {
            $comment = $like->getCommentLike();
            $numberOfLikes = ($comment->getNumberOfLikes()) - 1;
            $comment->setNumberOfLikes($numberOfLikes);
            $this->getBuzzDao()->saveCommentShare($comment);
            return $this->getBuzzDao()->deleteLikeForComment($like);
// @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new Exception("Comment Not Found");
        }
// @codeCoverageIgnoreEnd
    }
    
    public function saveUnLikeForComment($like) {
        try {
            $comment = $like->getCommentUnLike();
            $numberOfLikes = ($comment->getNumberOfUnlikes()) + 1;
            $comment->setNumberOfUnlikes($numberOfLikes);
            $this->getBuzzDao()->saveCommentShare($comment);
            return $this->getBuzzDao()->saveUnLikeForComment($like);
// @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new Exception("Comment Not Found");
        }
// @codeCoverageIgnoreEnd
    }
    
    public function deleteUnLikeForComment($like) {
        try {
            $comment = $like->getCommentUnLike();
            $numberOfLikes = ($comment->getNumberOfUnlikes()) - 1;
            $comment->setNumberOfUnlikes($numberOfLikes);
            $this->getBuzzDao()->saveCommentShare($comment);
            return $this->getBuzzDao()->deleteUnLikeForComment($like);
// @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new Exception("Comment Not Found");
        }
// @codeCoverageIgnoreEnd
    }

    /**
     * save comment for share
     * 
     * @param comment $comment
     * @return Comment
     * @throws DaoException
     */
    public function saveCommentShare($comment) {
        try {
            $share = $comment->getShareComment();
            $numberOfComments = $share->getNumberOfComments() + 1;
            $share->setNumberOfComments($numberOfComments);
            $this->getBuzzDao()->saveShare($share);
            return $this->getBuzzDao()->saveCommentShare($comment);
// @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new Exception("Comment Not Found");
        }
// @codeCoverageIgnoreEnd
    }

    /**
     * delete comment for share
     * 
     * @param Comment $comment
     * @return string
     * @throws DaoException
     */
    public function deleteCommentForShare($comment) {
        try {
            $share = $comment->getShareComment();
            $numberOfComments = $share->getNumberOfComments() - 1;
            $share->setNumberOfComments($numberOfComments);
            $this->getBuzzDao()->saveShare($share);
            return $this->getBuzzDao()->deleteCommentForShare($comment);
// @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new Exception("Comment Not Found");
        }
// @codeCoverageIgnoreEnd
    }

    /**
     * save employees share to the database
     * 
     * @param Share $share
     * @return Share
     * @throws DaoException
     */
    public function saveShare($share) {
        try {
            return $this->getBuzzDao()->saveShare($share);
// @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
// @codeCoverageIgnoreEnd
    }

    /**
     * delete share by its id
     * 
     * @param int $shareId
     * @return string number of deleted shares
     * @throws DaoException
     */
    public function deleteShare($shareId) {
        try {
            return $this->getBuzzDao()->deleteShare($shareId);
// @codeCoverageIgnoreStart
        } catch (Exception $ex) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
// @codeCoverageIgnoreEnd
    }

    /**
     * save employee Post
     * 
     * @param Post $post
     * @return Post
     * 
     * @throws DaoException
     */
    public function savePost($post) {
        try {
            return $this->getBuzzDao()->savePost($post);
// @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
// @codeCoverageIgnoreEnd
    }

    /**
     * delete post by podtId
     * 
     * @param int $postId
     * @return deleteresult
     * @throws DaoException
     */
    public function deletePost($postId) {
        try {
            return $this->getBuzzDao()->deletePost($postId);
// @codeCoverageIgnoreStart
        } catch (Exception $ex) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
// @codeCoverageIgnoreEnd
    }

    /**
     * delete post by podtId
     * 
     * @param Photo $photo
     * @return Photo
     * @throws DaoException
     */
    public function savePhoto($photo) {
        try {
            return $this->getBuzzDao()->savePhoto($photo);
// @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
// @codeCoverageIgnoreEnd
    }

}
