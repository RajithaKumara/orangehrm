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
            $q = Doctrine_Manager::getInstance()->getCurrentConnection();
            if ($employeeNumber == '') {
                $result = $q->execute("SELECT * FROM ohrm_buzz_share WHERE employee_number is NULL");
                return $result->rowCount();
            }
            $result = $q->execute("SELECT * FROM ohrm_buzz_share WHERE employee_number=" . $employeeNumber);
            return $result->rowCount();
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
            $q = Doctrine_Manager::getInstance()->getCurrentConnection();
            if ($employeeNumber == '') {
                $result = $q->execute("SELECT * FROM ohrm_buzz_comment WHERE employee_number is NULL");
                return $result->rowCount();
            }
            $result = $q->execute("SELECT * FROM ohrm_buzz_comment WHERE employee_number=" . $employeeNumber);
            return $result->rowCount();
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
                $queryLastBlock = "is NULL";
            } else {
                $queryLastBlock = "= " . $employeeNumber;
            }

            $shareLikesPerEmployee = $q->execute(
                    "SELECT *
                FROM ohrm_buzz_share
                INNER JOIN ohrm_buzz_like_on_share ON ohrm_buzz_like_on_share.share_id=ohrm_buzz_share.id
                WHERE ohrm_buzz_share.employee_number " . $queryLastBlock
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
                $queryLastBlock = "is NULL";
            } else {
                $queryLastBlock = "=" . $employeeNumber;
            }
            $result = $q->execute(
                    "SELECT *
                FROM ohrm_buzz_comment
                INNER JOIN ohrm_buzz_like_on_comment ON ohrm_buzz_like_on_comment.comment_id=ohrm_buzz_comment.id
                WHERE ohrm_buzz_comment.employee_number " . $queryLastBlock
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
                $queryLastBlock = "is NULL";
            } else {
                $queryLastBlock = "= " . $employeeNumber;
            }
            $result = $q->execute(
                    "SELECT *
                FROM ohrm_buzz_share
                INNER JOIN ohrm_buzz_comment ON ohrm_buzz_comment.share_id=ohrm_buzz_share.id
                WHERE ohrm_buzz_share.employee_number " . $queryLastBlock
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
            $q = Doctrine_Manager::getInstance()->getCurrentConnection();
            $result = $q->execute("SELECT * FROM hs_hr_employee WHERE MONTH(emp_birthday) = " . date('m', strtotime($fromDate)) . " AND "
                    . " DAY(emp_birthday) >= " . date('d', strtotime($fromDate)));

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
            $q = Doctrine_Manager::getInstance()->getCurrentConnection();
            $result = $q->execute("SELECT * FROM hs_hr_employee WHERE MONTH(joined_date) = " . date('m', strtotime($date)) . " AND "
                    . " DAY(joined_date) >= " . date('d', strtotime($date)));
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
                    ->where('id < ' . $fromId)
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
                    ->andWhere('id < ' . $fromId)
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
            if (!$employeeNumber) {
                $queryBlock = 'employee_number is NULL';
            } else {
                $queryBlock = 'employee_number=' . $employeeNumber;
            }
            $q = Doctrine_Query::create()
                    ->from('Share')
                    ->where($queryBlock)
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
     * get employee shares by employeeNumber
     * @param int $lastId
     * @param int $employeeNumber
     * @return array Share
     * @throws DaoException
     */
    public function getEmployeeSharesUptoShareId($lastId, $employeeNumber) {
        try {
            if (!$employeeNumber) {
                $queryBlock = 'employee_number is NULL';
            } else {
                $queryBlock = 'employee_number=' . $employeeNumber;
            }
            $q = Doctrine_Query::create()
                    ->select('*')
                    ->from('Share')
                    ->andWhere('id >= ' . $lastId)
                    ->andWhere($queryBlock)
                    ->orderBy('share_time DESC');
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
                    ->where('id >= ' . $lastId)
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
            $result = Doctrine::getTable('LikeOnComment')->find($id);

            return $result;
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
            if ($like->getEmployeeNumber() == '') {

                $queryBlock = 'employee_number is NULL';
            } else {
                $queryBlock = 'employee_number=' . $like->getEmployeeNumber();
            }
            $q = Doctrine_Query::create()
                    ->delete('LikeOnShare')
                    ->andWhere('share_id =' . $like->getShareId())
                    ->andWhere($queryBlock);

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
            if ($like->getEmployeeNumber() == '') {

                $queryBlock = 'employee_number is NULL';
            } else {
                $queryBlock = 'employee_number=' . $like->getEmployeeNumber();
            }
            $q = Doctrine_Query::create()
                    ->delete('UnLikeOnShare')
                    ->andWhere('share_id =' . $like->getShareId())
                    ->andWhere($queryBlock);

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
            if ($like->getEmployeeNumber() == '') {

                $queryBlock = 'employee_number is NULL';
            } else {
                $queryBlock = 'employee_number=' . $like->getEmployeeNumber();
            }
            $q = Doctrine_Query::create()
                    ->delete('LikeOnComment')
                    ->andWhere('comment_id =' . $like->getCommentId())
                    ->andWhere($queryBlock);

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
            if ($like->getEmployeeNumber() == '') {

                $queryBlock = 'employee_number is NULL';
            } else {
                $queryBlock = 'employee_number=' . $like->getEmployeeNumber();
            }
            $q = Doctrine_Query::create()
                    ->delete('UnLikeOnComment')
                    ->andWhere('comment_id =' . $like->getCommentId())
                    ->andWhere($queryBlock);

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
                    ->where('id=' . $shareId);
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
                    ->where('id=' . $postId);
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

}
