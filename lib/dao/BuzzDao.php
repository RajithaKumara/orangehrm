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

class BuzzDao extends BaseDao {

    /**
     * get share count
     * 
     * @param int $limit
     * @return share collection
     * @throws DaoException
     */
    public function getSharesCount() {
       try {            
            $q = Doctrine_Query :: create()
                    ->select('id')
                    ->from('Share');
            
            return $q->count();
            // @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }
    
    /**
     * get most recent share by giving limit
     * 
     * @param int $limit
     * @return share collection
     * @throws DaoException
     */
    public function getShares($limit) {
        try {
            $q = Doctrine_Query::create()
                    ->from('Share')
                    ->limit($limit)
                    ->orderBy('share_time DESC');
            return $q->execute();
            // @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * get number of shares by employee
     * @param INT  $employeeNumber
     * @return INT
     * @throws DaoException
     */
    public function getNoOfSharesByEmployeeNumber($employeeNumber) {
        try {
            $q = Doctrine_Query :: create()
                    ->from('Share');
            
            if (!empty($employeeNumber)) {
                $q->andWhere('employee_number = ?', $employeeNumber);
            } else {
                $q->andWhere('employee_number is NULL');
            }
            
            return $q->count();
            // @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * get number of comment by employee
     * @param INT  $employeeNumber
     * @return INT
     * @throws DaoException
     */
    public function getNoOfCommentsByEmployeeNumber($employeeNumber) {
        try {
            $q = Doctrine_Query :: create()
                    ->from('Comment');
            
            if (!empty($employeeNumber)) {
                $q->andWhere('employee_number = ?', $employeeNumber);
            } else {
                $q->andWhere('employee_number is NULL');
            }
            
            return $q->count();
            // @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * get number of likes for shares by employee
     * @param INT  $employeeNumber
     * @return INT
     * @throws DaoException
     */
    public function getNoOfShareLikesForEmployeeByEmployeeNumber($employeeNumber) {
        try {
            $q = Doctrine_Manager::getInstance()->getCurrentConnection();
            if ($employeeNumber == '') {
                $queryLastBlock = "WHERE ohrm_buzz_share.employee_number is NULL";
                $params = array();
            } else {
                $queryLastBlock = "WHERE ohrm_buzz_share.employee_number = :empNumber";
                $params = array(':empNumber' => $employeeNumber);
            }

            $shareLikesPerEmployee = $q->execute(
                    "SELECT *
                FROM ohrm_buzz_share
                INNER JOIN ohrm_buzz_like_on_share ON ohrm_buzz_like_on_share.share_id=ohrm_buzz_share.id
                $queryLastBlock", $params
            );
            return $shareLikesPerEmployee->rowCount();
            // @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * get number of like for comments by employee
     * @param INT  $employeeNumber
     * @return INT
     * @throws DaoException
     */
    public function getNoOfCommentLikesForEmployeeByEmployeeNumber($employeeNumber) {
        try {
            $q = Doctrine_Manager::getInstance()->getCurrentConnection();
            if ($employeeNumber == '') {
                $queryLastBlock = "WHERE ohrm_buzz_comment.employee_number is NULL";
                $params = array();
            } else {
                $queryLastBlock = "WHERE ohrm_buzz_comment.employee_number =:empNumber";
                $params = array(':empNumber' => $employeeNumber);
            }
            $result = $q->execute(
                    "SELECT *
                FROM ohrm_buzz_comment
                INNER JOIN ohrm_buzz_like_on_comment ON ohrm_buzz_like_on_comment.comment_id=ohrm_buzz_comment.id
                $queryLastBlock" , $params
            );
            return $result->rowCount();
            // @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * get number of comments by employee
     * @param INT  $employeeNumber
     * @return INT
     * @throws DaoException
     */
    public function getNoOfCommentsForEmployeeByEmployeeNumber($employeeNumber) {
        try {
            $q = Doctrine_Manager::getInstance()->getCurrentConnection();
            if ($employeeNumber == '') {
                $queryLastBlock = "WHERE ohrm_buzz_share.employee_number is NULL";
                $params = array();
            } else {
                $queryLastBlock = "WHERE ohrm_buzz_share.employee_number =:empNumber";
                $params = array(':empNumber' => $employeeNumber);
            }
            $result = $q->execute(
                    "SELECT *
                FROM ohrm_buzz_share
                INNER JOIN ohrm_buzz_comment ON ohrm_buzz_comment.share_id=ohrm_buzz_share.id
                $queryLastBlock", $params
            );

            return $result->rowCount();
            // @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * get employees who having birhdays on month
     * @param Date $fromDate 
     * @return array Employee 
     * @throws DaoException
     */
    public function getEmployeesHavingBdaysBetweenTwoDates($fromDate, $toDate) {
        try {
            $whereClause = "WHERE deleted_at IS NULL AND joined_date <= :date AND datediff( MAKEDATE(YEAR(:date) , DAYOFYEAR(emp_birthday)) , :date) " . 
                                       " BETWEEN 1 AND 30 ".
                                       " OR datediff( MAKEDATE(YEAR(:date)+1 , DAYOFYEAR(emp_birthday)) , :date) " .
                                       " BETWEEN 1 AND 30 ";
            $params = array(':date' => $fromDate);
            $q = Doctrine_Manager::getInstance()->getCurrentConnection();
            $result = $q->execute("SELECT * FROM hs_hr_employee $whereClause ORDER BY MONTH(joined_date) ASC, DAY(joined_date) ASC", $params);
            
            return $result->fetchAll();
            // @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * get employees who having aniversary on month
     * @param INT Month 
     * @return array Employee 
     * @throws DaoException
     */
    public function getEmployeesHavingAnniversaryOnMonth($date) {
        try {
            $whereClause = "WHERE deleted_at IS NULL AND joined_date <= :date AND datediff( MAKEDATE(YEAR(:date) , DAYOFYEAR(joined_date)) , :date) " . 
                                       " BETWEEN 1 AND 30 ".
                                       " OR datediff( MAKEDATE(YEAR(:date)+1 , DAYOFYEAR(joined_date)) , :date) " .
                                       " BETWEEN 1 AND 30 ";
            $params = array(':date' => $date);
            
            $q = Doctrine_Manager::getInstance()->getCurrentConnection();
            $result = $q->execute("SELECT * FROM hs_hr_employee $whereClause ORDER BY MONTH(joined_date) ASC, DAY(joined_date) ASC", $params);
            return $result->fetchAll();
            // @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * get most like shares
     * @param type $shareCount
     * @return type
     * @throws DaoException
     */
    public function getMostLikedShares($shareCount) {
        try {
            $q = Doctrine_Manager::getInstance()->getCurrentConnection();
            $result = $q->execute(
                    "SELECT  share_id , COUNT(share_id) AS  no_of_likes 
                FROM  ohrm_buzz_like_on_share 
                GROUP BY  share_id 
                ORDER BY  no_of_likes DESC 
                LIMIT " . $shareCount
            );
            return $result->fetchAll();
            // @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * get Most Commented Shares
     * @param type $shareCount
     * @return array Share
     * @throws DaoException
     */
    public function getMostCommentedShares($shareCount) {
        try {
            $q = Doctrine_Manager::getInstance()->getCurrentConnection();
            $result = $q->execute(
                    "SELECT share_id, COUNT( share_id ) AS no_of_comments
                FROM ohrm_buzz_comment
                GROUP BY share_id
                ORDER BY no_of_comments DESC 
                LIMIT " . $shareCount
            );
            return $result->fetchAll();
            // @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * get More shares when scrolling 
     * @param INT limit 
     * @return array Share 
     * @throws DaoException
     */
    public function getMoreShares($limit, $fromId) {
        try {
            $q = Doctrine_Query::create()
                    ->select('*')
                    ->from('Share')
                    ->where('id < ?' , $fromId)
                    ->limit($limit)
                    ->orderBy('share_time DESC');
            return $q->execute();
            // @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * get more shsres posted by Employee By Employee Number
     * @param type $limit
     * @param type $fromId
     * @param type $employeeNumber
     * @return array Share
     * @throws DaoException
     */
    public function getMoreEmployeeSharesByEmployeeNumber($limit, $fromId, $employeeNumber) {
        try {
            if (!$employeeNumber) {
                $queryBlock = 'employee_number is NULL';
            } else {
                $queryBlock = 'employee_number=' . $employeeNumber;
            }
            $q = Doctrine_Query::create()
                    ->select('*')
                    ->from('Share')
                    ->andWhere('id < ?' , $fromId)
                    ->andWhere($queryBlock)
                    ->limit($limit)
                    ->orderBy('share_time DESC');
            return $q->execute();

            // @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }

    public function getSharesByEmployeeNumber($limit, $employeeNumber) {
        try {
            
            $q = Doctrine_Query::create()
                    ->from('Share')
                    ->limit($limit)
                    ->orderBy('share_time DESC');
            if (!$employeeNumber) {
                $q->addWhere('employee_number is NULL');
            } else {
                $q->addWhere('employee_number = ?', $employeeNumber);
            }
            return $q->execute();
            // @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * get employee shares by employeeNumber
     * @param int $lastId
     * @param int $employeeNumber
     * @return array Share
     * @throws DaoException
     */
    public function getEmployeeSharesUptoShareId($lastId, $employeeNumber) {
        try {
            
            $q = Doctrine_Query::create()
                    ->select('*')
                    ->from('Share')
                    ->andWhere('id >= ?' , $lastId)
                    ->orderBy('share_time DESC');
            if (!$employeeNumber) {
                $q->andWhere('employee_number is NULL');
            } else {
                $q->andWhere('employee_number =?', $employeeNumber);
            }
            return $q->execute();
            // @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * returning shares up to given Id
     * @param Int $lastId
     * @return Share array
     * @throws DaoException
     */
    public function getSharesUptoId($lastId) {
        try {
            $q = Doctrine_Query::create()
                    ->select('*')
                    ->from('Share')
                    ->where('id >= ?' , $lastId)
                    ->orderBy('share_time DESC');
            return $q->execute();
            // @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }
    
    /**
     * Get shares for posts/comments added/changed since the given time
     * 
     * @param DateTime $dateTime
     * @return array Shares
     */
    public function getSharesChangedSince(DateTime $dateTime) {
        try {
            
            
            $timeStamp = $dateTime->format('Y-m-d H:i:s');
            //var_dump($timeStamp);
            $q = Doctrine_Query::create()
                    ->select('s.*')
                    ->from('Share s')
                    ->leftJoin('s.PostShared p')
                    ->leftJoin('s.comment c')
                    ->leftJoin('s.Like l')
                    ->leftJoin('s.Unlike u')
                    ->leftJoin('c.Like cl')
                    ->leftJoin('c.Unlike cu')
                    ->where('s.share_time > ?',$timeStamp)
                    ->orWhere('s.updated_at > ?', $timeStamp)
                    ->orWhere('p.post_time > ?', $timeStamp)                    
                    ->orWhere('p.updated_at > ?', $timeStamp)
                    ->orWhere('c.comment_time > ?', $timeStamp)
                    ->orWhere('c.updated_at > ?', $timeStamp)
                    ->orWhere('l.like_time > ?', $timeStamp)
                    ->orWhere('u.like_time > ?', $timeStamp)
                    ->orWhere('cl.like_time > ?', $timeStamp)
                    ->orWhere('cu.like_time > ?', $timeStamp)                    
                    ->orderBy('share_time DESC');
            return $q->execute();
            // @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd        
    }    

    /**
     * get share By ID 
     * 
     * @param int $id
     * @return Share
     * @throws DaoException
     */
    public function getShareById($id) {
        try {
            $result = Doctrine::getTable('Share')->find($id);

            return $result;
            // @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * get post by its id
     * @param type $id
     * @return type
     * @throws DaoException
     */
    public function getPostById($id) {
        try {
            $result = Doctrine::getTable('Post')->find($id);

            return $result;
            // @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * get comments by its id
     * @param type $id
     * @return type
     * @throws DaoException
     */
    public function getCommentById($id) {
        try {
            $result = Doctrine::getTable('Comment')->find($id);
            return $result;
            // @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * get like on share by its id
     * @param type $id
     * @return type
     * @throws DaoException
     */
    public function getLikeOnShareById($id) {
        try {
            $result = Doctrine::getTable('LikeOnShare')->find($id);

            return $result;
            // @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * get like on comment by its id
     * @param type $id
     * @return type
     * @throws DaoException
     */
    public function getLikeOnCommentById($id) {
        try {

            return Doctrine::getTable('LikeOnComment')->find($id);
            // @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * save likes that employee did
     * 
     * @param LikeOnShare $like
     * @return LikeOnShare
     * @throws DaoException
     */
    public function saveLikeForShare($like) {

        try {
            $like->save();
            return $like;
            // @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * delete employee likes for shares
     * 
     * @param LikeOnShare $like
     * @return string number of deletions
     * @throws DaoException
     */
    public function deleteLikeForShare($like) {
        try {
            $q = Doctrine_Query::create()
                    ->delete('LikeOnShare')
                    ->andWhere('share_id = ?' , $like->getShareId());
            
            if ($like->getEmployeeNumber() == '') {
                $q->andWhere('employee_number is NULL');
            } else {
                $q->andWhere('employee_number =?', $like->getEmployeeNumber());
            }
            return $q->execute();
            // @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * save unlie to the database
     * @param UnlikeOnShare $like
     * @return UnlikeOnShare
     * @throws DaoException
     */
    public function saveUnLikeForShare($like) {
        try {
            $like->save();
            return $like;
            // @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }

    public function deleteUnLikeForShare($like) {
        try {
            $q = Doctrine_Query::create()
                    ->delete('UnLikeOnShare')
                    ->andWhere('share_id = ?' , $like->getShareId());

            if ($like->getEmployeeNumber() == '') {
                $q->andWhere('employee_number is NULL');
            } else {
                $q->andWhere('employee_number = ?', $like->getEmployeeNumber());
            }
            return $q->execute();
            // @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
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
            $like->save();
            return $like;
            // @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * delete Like for comment
     * @param LikeOnComment $like
     * @return string number of dlete items
     * @throws DaoException
     */
    public function deleteLikeForComment($like) {
        try {
            $q = Doctrine_Query::create()
                    ->delete('LikeOnComment')
                    ->andWhere('comment_id = ?', $like->getCommentId());

            if ($like->getEmployeeNumber() == '') {
                $q->andWhere('employee_number is NULL');
            } else {
                $q->andWhere('employee_number= ?', $like->getEmployeeNumber());
            }
            return $q->execute();
            // @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }

    public function saveUnLikeForComment($like) {
        try {
            $like->save();
            return $like;
            // @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }

    public function deleteUnLikeForComment($like) {
        try {
            $q = Doctrine_Query::create()
                    ->delete('UnLikeOnComment')
                    ->andWhere('comment_id = ?', $like->getCommentId());

            if ($like->getEmployeeNumber() == '') {
                $q->andWhere('employee_number is NULL');
            } else {
                $q->andWhere('employee_number = ?', $like->getEmployeeNumber());
            }
            return $q->execute();
            // @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
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
            $comment->save();
            return $comment;
            // @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
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
            $q = Doctrine_Query::create()
                    ->delete('Comment')
                    ->where('id =' . $comment->getId());
            return $q->execute();
            // @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
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
            $share->save();
            return $share;
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
            $q = Doctrine_Query::create()
                    ->delete('Share')
                    ->where('id= ?' , $shareId);
            return $q->execute();
            // @codeCoverageIgnoreStart
        } catch (Exception $e) {
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
            $post->save();
            return $post;
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
            $q = Doctrine_Query::create()
                    ->delete('Post')
                    ->where('id= ?' , $postId);
            return $q->execute();
            // @codeCoverageIgnoreStart
        } catch (Exception $e) {
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
            $photo->save();
            return $photo;
            // @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }
    
    /**
     * Get photo by id
     * @param int $id
     * @return Photo object
     */
    public function getPhoto($id) {
        try {
            return Doctrine :: getTable('Photo')->find($id);
            // @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }      
    
    /**
     * Get all photos in buzz
     * @return DoctrineCollection of Photo objects
     * @throws DaoException
     */
    public function getAllPhotos() {
        try {
            $q = Doctrine_Query::create()
                    ->select('p.id, p.width, p.height, p.size, p.filename')
                    ->from('Photo p');
            return $q->execute();
            // @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd        
    }
    
    /**
     * Get photos related to given post. Does not load the actual photo blob
     * 
     * @param int $postId Post ID
     * @return Array of Post objects
     */
    public function getPostPhotos($postId) {
        try {
            $q = Doctrine_Query::create()
                    ->select('p.id, p.post_id, p.width, p.height, p.filename, p.size')
                    ->from('Photo p')
                    ->where('p.post_id = ? ', $postId)
                    ->orderBy('p.id ASC');
            return $q->execute();
            // @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }    

    /**
     * save link to data base
     * 
     * @param Link $photo
     * @return Link
     * @throws DaoException
     */
    public function saveLink($link) {
        try {
            $link->save();
            return $link;
            // @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }

}
