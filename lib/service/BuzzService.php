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

    protected $buzzDao;
    
    /**
     * this is function to get buzzDao 
     * @return BuzzDao
     */
    public function getBuzzDao() {
        if (!($this->buzzDao instanceof BuzzDao)) {
            $this->buzzDao = new BuzzDao();
        }
        return $this->buzzDao;
    }

    public function setBuzzDao(BuzzDao $buzzDao) {
        $this->buzzDao = $buzzDao;
    }

    /**
     * get most resent share by giving limit
     * 
     * @param int $limit
     * @return share collection
     * @throws DaoException
     */
    public function getSharesCount() {

        return $this->getBuzzDao()->getSharesCount();
    }
    
    /**
     * get most resent share by giving limit
     * 
     * @param int $limit
     * @return share collection
     * @throws DaoException
     */
    public function getShares($limit) {

        return $this->getBuzzDao()->getShares($limit);
    }

    /**
     * get Employees who having bithday between two days
     * @param type $fromDate
     * @param type $toDate
     * @return array Employee
     */
    public function getEmployeesHavingBdaysBetweenTwoDates($fromDate, $toDate) {

        return $this->getBuzzDao()->getEmployeesHavingBdaysBetweenTwoDates($fromDate, $toDate);
    }
    
    /**
     * Get employee having birthdays next year
     * @param type $date
     * @return type
     */
    Public function getEmployeesHavingBdaysOnNextYear($date){
        return $this->getBuzzDao()->getEmployeesHavingBdaysOnNextYear($date);
    }

    /**
     * get employee having aniversary on month from this date 
     * @param type $date
     * @return array Employee
     */
    public function getEmployeesHavingAnniversaryOnMonth($date) {

        return $this->getBuzzDao()->getEmployeesHavingAnniversaryOnMonth($date);
    }
    
    
    /**
     * get Employees Having Anniversaries Next Year
     * @param type $date
     * @return type
     */
    public function getEmployeesHavingAnniversariesNextYear($date) {

        return $this->getBuzzDao()->getEmployeesHavingAnniversariesNextYear($date);
    }

    /**
     * get number of shares by employee
     * @param type $employeeNumber
     * @return type
     */
    public function getNoOfSharesByEmployeeNumber($employeeNumber) {

        return $this->getBuzzDao()->getNoOfSharesByEmployeeNumber($employeeNumber);
    }

    /**
     * get number of comment by employee
     * @param type $employeeNumber
     * @return Int
     */
    public function getNoOfCommentsByEmployeeNumber($employeeNumber) {

        return $this->getBuzzDao()->getNoOfCommentsByEmployeeNumber($employeeNumber);
    }

    /**
     * get number of likes on shares like by employee
     * @param type $employeeNumber
     * @return type
     */
    public function getNoOfShareLikesForEmployeeByEmployeeNumber($employeeNumber) {

        return $this->getBuzzDao()->getNoOfShareLikesForEmployeeByEmployeeNumber($employeeNumber);
    }

    /**
     * get Most Like Shares whith giving limit
     * @param type $shareCount
     * @return array Shares
     */
    public function getMostLikedShares($shareCount) {

        return $this->getBuzzDao()->getMostLikedShares($shareCount);
    }

    /**
     * get most commented shares by giving limit
     * @param type $shareCount
     * @return array share
     */
    public function getMostCommentedShares($shareCount) {

        return $this->getBuzzDao()->getMostCommentedShares($shareCount);
    }

    /**
     * get no of likes on comment by employee
     * @param type $employeeNumber
     * @return Int
     */
    public function getNoOfCommentLikesForEmployeeByEmployeeNumber($employeeNumber) {

        return $this->getBuzzDao()->getNoOfCommentLikesForEmployeeByEmployeeNumber($employeeNumber);
    }

    /**
     * get number of comments commented by employee
     * @param type $employeeNumber
     * @return type
     */
    public function getNoOfCommentsForEmployeeByEmployeeNumber($employeeNumber) {

        return $this->getBuzzDao()->getNoOfCommentsForEmployeeByEmployeeNumber($employeeNumber);
    }

    /**
     * get more shares from This share Id whith limit
     * @param type $limit
     * @param type $fromId
     * @return type
     */
    public function getMoreShares($limit, $fromId) {

        return $this->getBuzzDao()->getMoreShares($limit, $fromId);
    }

    /**
     * get employee shares from this share ID up to This limit 
     * @param type $limit
     * @param type $fromId
     * @param type $employeeNumber
     * @return array shares
     */
    public function getMoreEmployeeSharesByEmployeeNumber($limit, $fromId, $employeeNumber) {

        return $this->getBuzzDao()->getMoreEmployeeSharesByEmployeeNumber($limit, $fromId, $employeeNumber);
    }

    /**
     * 
     * @param type $lastId
     * @param type $employeeNumber
     * @return array Share
     */
    public function getEmployeeSharesUptoShareId($lastId, $employeeNumber) {

        return $this->getBuzzDao()->getEmployeeSharesUptoShareId($lastId, $employeeNumber);
    }

    /**
     * get Shares up to this Share Id
     * @param type $lastId
     * @return array Shares
     */
    public function getSharesUptoId($lastId) {

        return $this->getBuzzDao()->getSharesUptoId($lastId);
    }

    /**
     * Get shares for posts/comments added/changed since the given time
     * 
     * @param DateTime $dateTime
     * @return array Shares
     */
    public function getSharesChangedSince(DateTime $dateTime) {
        return $this->getBuzzDao()->getSharesChangedSince($dateTime);
    }
    
    /**
     * get share By \Id
     * 
     * @param int Id
     * @return Share
     */
    public function getShareById($shareId) {

        return $this->getBuzzDao()->getShareById($shareId);
    }

    /**
     * 
     * @param type $limit
     * @param type $employeeNumber
     * @return t
     */
    public function getSharesByEmployeeNumber($limit, $employeeNumber) {

        return $this->getBuzzDao()->getSharesByEmployeeNumber($limit, $employeeNumber);
    }

    /**
     * get Post BY Id
     * 
     * @param int $postId
     * @return share collection
     */
    public function getPostById($postId) {

        return $this->getBuzzDao()->getPostById($postId);
    }

    /**
     * get comment by It Id
     * 
     * @param int $limit
     * @return share collection
     */
    public function getCommentById($commentId) {

        return $this->getBuzzDao()->getCommentById($commentId);
    }

    /**
     * get likeOncomment by It Id
     * 
     * @param int $id
     * @return share collection
     */
    public function getLikeOnCommentById($likeId) {

        return $this->getBuzzDao()->getLikeOnCommentById($likeId);
    }

    /**
     * get likeOnShare by It Id
     * 
     * @param int $limit
     * @return share collection
     * @throws DaoException
     */
    public function getLikeOnShareById($likeId) {

        return $this->getBuzzDao()->getLikeOnShareById($likeId);
    }

    /**
     * save likes that employee did
     * 
     * @param Like $like
     * @return Like
     * @throws DaoException
     */
    public function saveLikeForShare($like) {

        $share = $like->getShareLike();
        $numberOfLikes = $share->getNumberOfLikes() + 1;
        $share->setNumberOfLikes($numberOfLikes);
        $this->getBuzzDao()->saveShare($share);

        return $this->getBuzzDao()->saveLikeForShare($like);
    }

    /**
     * delete employee likes for shares
     * 
     * @param Like $like
     * @return string number of deletions
     * @throws DaoException
     */
    public function deleteLikeForShare($like) {

        $share = $like->getShareLike();
        $numberOfLikes = $share->getNumberOfLikes() - 1;
        $share->setNumberOfLikes($numberOfLikes);
        $this->getBuzzDao()->saveShare($share);

        return $this->getBuzzDao()->deleteLikeForShare($like);
    }

    public function saveUnLikeForShare($like) {

        $share = $like->getShareUnLike();
        $numberOfLikes = $share->getNumberOfUnlikes() + 1;
        $share->setNumberOfUnlikes($numberOfLikes);
        $this->getBuzzDao()->saveShare($share);

        return $this->getBuzzDao()->saveUnLikeForShare($like);
    }

    public function deleteUnLikeForShare($like) {

        $share = $like->getShareUnLike();
        $numberOfLikes = $share->getNumberOfUnlikes() - 1;
        $share->setNumberOfUnlikes($numberOfLikes);
        $this->getBuzzDao()->saveShare($share);

        return $this->getBuzzDao()->deleteUnLikeForShare($like);
    }

    /**
     * save employee like on comments
     * 
     * @param LikeOnComment $like
     * @return LikeOnComment
     * @throws DaoException
     */
    public function saveLikeForComment($like) {

        $comment = $like->getCommentLike();
        $numberOfLikes = ($comment->getNumberOfLikes()) + 1;
        $comment->setNumberOfLikes($numberOfLikes);
        $this->getBuzzDao()->saveCommentShare($comment);

        return $this->getBuzzDao()->saveLikeForComment($like);
    }

    /**
     * 
     * @param LikeOnComment $like
     * @return string number of dlete items
     * @throws DaoException
     */
    public function deleteLikeForComment($like) {

        $comment = $like->getCommentLike();
        $numberOfLikes = ($comment->getNumberOfLikes()) - 1;
        $comment->setNumberOfLikes($numberOfLikes);
        $this->getBuzzDao()->saveCommentShare($comment);

        return $this->getBuzzDao()->deleteLikeForComment($like);
    }

    /**
     * save unlike for comment
     * @param type $like
     * @return UnlikeOnComment
     */
    public function saveUnLikeForComment($like) {

        $comment = $like->getCommentUnLike();
        $numberOfLikes = ($comment->getNumberOfUnlikes()) + 1;
        $comment->setNumberOfUnlikes($numberOfLikes);
        $this->getBuzzDao()->saveCommentShare($comment);

        return $this->getBuzzDao()->saveUnLikeForComment($like);
    }

    /**
     * delete Unlike On comment
     * @param type $like
     * @return Int
     */
    public function deleteUnLikeForComment($like) {

        $comment = $like->getCommentUnLike();
        $numberOfLikes = ($comment->getNumberOfUnlikes()) - 1;
        $comment->setNumberOfUnlikes($numberOfLikes);
        $this->getBuzzDao()->saveCommentShare($comment);

        return $this->getBuzzDao()->deleteUnLikeForComment($like);
    }

    /**
     * save comment for share
     * 
     * @param comment $comment
     * @return Comment
     * @throws DaoException
     */
    public function saveCommentShare($comment) {

        $share = $comment->getShareComment();
        $numberOfComments = $share->getNumberOfComments() + 1;
        $share->setNumberOfComments($numberOfComments);
        $this->getBuzzDao()->saveShare($share);

        return $this->getBuzzDao()->saveCommentShare($comment);
    }

    /**
     * delete comment for share
     * 
     * @param Comment $comment
     * @return string
     * @throws DaoException
     */
    public function deleteCommentForShare($comment) {
        $share = $comment->getShareComment();
        $numberOfComments = $share->getNumberOfComments() - 1;
        $share->setNumberOfComments($numberOfComments);
        $this->getBuzzDao()->saveShare($share);
        return $this->getBuzzDao()->deleteCommentForShare($comment);
    }

    /**
     * save employees share to the database
     * 
     * @param Share $share
     * @return Share
     * @throws DaoException
     */
    public function saveShare($share) {

        return $this->getBuzzDao()->saveShare($share);
    }

    /**
     * delete share by its id
     * 
     * @param int $shareId
     * @return string number of deleted shares
     * @throws DaoException
     */
    public function deleteShare($shareId) {

        return $this->getBuzzDao()->deleteShare($shareId);
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

        return $this->getBuzzDao()->savePost($post);
    }

    /**
     * delete post by podtId
     * 
     * @param int $postId
     * @return deleteresult
     * @throws DaoException
     */
    public function deletePost($postId) {

        return $this->getBuzzDao()->deletePost($postId);
    }

    /**
     * delete post by podtId
     * 
     * @param Photo $photo
     * @return Photo
     * @throws DaoException
     */
    public function savePhoto($photo) {

        return $this->getBuzzDao()->savePhoto($photo);
    }
    
    /**
     * Get photo by id
     * @param int $id
     * @return Photo object
     */
    public function getPhoto($id) {
        return $this->getBuzzDao()->getPhoto($id);
    }    

    /**
     * Get photos related to given post. Does not load the actual photo blob
     * 
     * @param int $postId Post ID
     * @return Array of Post objects
     */
    public function getPostPhotos($postId) {
        return $this->getBuzzDao()->getPostPhotos($postId);
    }
    
    /**
     * save Link to database
     * 
     * @param Link $link
     * @return Link
     * @throws DaoException
     */
    public function saveLink($link) {

        return $this->getBuzzDao()->saveLink($link);
    }

    public function updateLinks ($url) {
            $isValidUrl = true;
            $allowedDomains = array("www.youtube.com", "www.vimeo.com", "vimeo.com", "www.yahoo.com", "www.dailymotion.com", "www.metacafe.com", "www.ustream.tv");
            $url = preg_replace('~(?#!js YouTubeId Rev:20160125_1800)
        # Match non-linked youtube URL in the wild. (Rev:20130823)
        https?://          # Required scheme. Either http or https.
        (?:[0-9A-Z-]+\.)?  # Optional subdomain.
        (?:                # Group host alternatives.
          youtu\.be/       # Either youtu.be,
        | youtube          # or youtube.com or
          (?:-nocookie)?   # youtube-nocookie.com
          \.com            # followed by
          \S*?             # Allow anything up to VIDEO_ID,
          [^\w\s-]         # but char before ID is non-ID char.
        )                  # End host alternatives.
        ([\w-]{11})        # $1: VIDEO_ID is exactly 11 chars.
        (?=[^\w-]|$)       # Assert next char is non-ID or EOS.
        (?!                # Assert URL is not pre-linked.
          [?=&+%\w.-]*     # Allow URL (query) remainder.
          (?:              # Group pre-linked alternatives.
            [\'"][^<>]*>   # Either inside a start tag,
          | </a>           # or inside <a> element text contents.
          )                # End recognized pre-linked alts.
        )                  # End negative lookahead assertion.
        [?=&+%\w.-]*       # Consume any URL (query) remainder.
        ~ix', 'http://www.youtube.com/watch?v=$1',
                $url);


            $parsed = parse_url($url);

            if ($parsed['scheme'] != 'http' && $parsed['scheme'] != 'https') {
                $isValidUrl = false;
            }

            if (!in_array($parsed['host'], $allowedDomains)) {
                $isValidUrl = false;
            }

            if (!$isValidUrl) {
                return $url;
            }

            $temp = explode("youtu.be/", $url);

            if (count($temp) > 1) {
                $embededUrl = "https://www.youtube.com/embed/" . $temp[1] . "?rel=0";
                return $embededUrl;
            }

            $temp2 = explode("v=", $url);
            if (count($temp2) > 1) {
                $videoJson = "https://www.youtube.com/oembed?url=$url&format=json";
                $headers = get_headers($videoJson);
                $responeCode = substr($headers[0], 9, 3);
                if ($responeCode != "200") {
                    return 'not';
                }
                $embededUrl = "https://www.youtube.com/embed/" . $temp2[1] . "?rel=0";
                return $embededUrl;
            }

            $temp3 = explode("//vimeo.com/", $url);
            if (count($temp3) > 1) {
                $urlParts = explode("/", parse_url($temp3[1], PHP_URL_PATH));
                $videoId = (int) $urlParts[count($urlParts) - 1];
                $embededUrl = "http://player.vimeo.com/video/" . $videoId;
                return $embededUrl;
            }

            $temp4 = explode("yahoo.com/", $url);
            if (count($temp4) > 1) {
                $lstCode = explode("/", $temp4[1]);
                $last = count($lstCode) - 1;
                $embededUrl = "https://screen.yahoo.com/" . $lstCode[$last] . "?format=embed";
                return $embededUrl;
            }

            $temp5 = explode("dailymotion.com/", $url);
            if (count($temp5) > 1) {
                $lstCode = explode("/", $temp5[1]);
                $last = count($lstCode) - 1;
                $codeFirst = explode("_", $lstCode[$last]);
                $embededUrl = "//www.dailymotion.com/embed/video/" . $codeFirst[0];
                return $embededUrl;
            }

            $temp6 = explode("http://dai.ly/", $url);
            if (count($temp6) > 1) {

                $embededUrl = "//www.dailymotion.com/embed/video/" . $temp6[1];
                return $embededUrl;
            }

            $temp7 = explode("vube.com/", $url);
            if (count($temp7) > 1) {
                $lstCode = explode("/", $temp7[1]);

                $last = count($lstCode) - 1;
                $vube = explode("t=s", $lstCode[$last]);

                $embededUrl = "http://vube.com/embed/video/" . $vube[0];
                return $embededUrl;
            }

            $temp8 = explode("http://www.metacafe.com/watch/", $url);
            if (count($temp8) > 1) {
                $lstCode = explode("/", $temp8[1]);



                $embededUrl = "http://www.metacafe.com/embed/" . $lstCode[0];
                return $embededUrl;
            }

            $temp9 = explode("www.ustream.tv/recorded/", $url);
            if (count($temp9) > 1) {
                $embededUrl = "http://www.ustream.tv/embed/recorded/" . $temp9[1] . "?v=3&amp;wmode=direct";
                return $embededUrl;
            }


            return $url;
        }

    public function getUrlsArray($text)
    {
        $reg_exUrl = "#(www\.|https?://)?[a-z0-9]+\.[a-z0-9]{2,4}\S*#i";

        $machedUrl = array();
        if (preg_match_all($reg_exUrl, $text, $url, PREG_PATTERN_ORDER)) {
            $machedUrl =  $url[0];

        }
        return $machedUrl;
    }
  
    /**
     * 
     * @param type $share
     * @return type
     */
    public function getSharePost($postId, $loggedInEmployeeNumber, $newText) {
        $shareDetails = new Share();
        $shareDetails->setPostId($postId);
        $shareDetails->setEmployeeNumber($loggedInEmployeeNumber);
        if($loggedInEmployeeNumber != "" && !is_null($loggedInEmployeeNumber)) {
            $loggedInEmployee = $this->getEmployeeService()->getEmployee($loggedInEmployeeNumber);
            if ($loggedInEmployee instanceof Employee) {
                $shareDetails->setEmployeeName($loggedInEmployee->getFirstAndLastNames());
            }
        }
        $shareDetails->setNumberOfComments(0);
        $shareDetails->setNumberOfLikes(0);
        $shareDetails->setNumberOfUnlikes(0);
        $shareDetails->setText($newText);
        $shareDetails->setShareTime(date("Y-m-d H:i:s"));
        $shareDetails->setType('1');
        return $shareDetails;
    }

    /**
     * 
     * @return EmployeeService
     */
    public function getEmployeeService() {
        if(!isset($this->employeeService)) {
            $this->employeeService = new EmployeeService();
        }
        return $this->employeeService;
    }
    
    /**
     * 
     * @param type employeeService
     */
    public function setEmployeeService(EmployeeService $employeeService) {
        $this->employeeService = $employeeService;
    }
    
    /**
     * 
     * @param Share $shared
     * @return type
     */
    public function getSharedEmployeeNames(Share $shared) {
        $sharedEmpArray = array();
        $post = $shared->getPostShared();
        
        $isOriginalPost = $shared->getType();
        $isAdminShare = true;
        $empIdList = array();
        foreach ($post->getShare() as $share) {
            $sharedEmployeeList = array();
            if ($isOriginalPost != 0) {
                $empId = $share->getEmployeeNumber();
                if ($empId == null) {
                    if ($isAdminShare) {
                        $sharedEmployeeList['employee_number'] = null;
                        $sharedEmployeeList['employee_name'] = "Admin";
                        $sharedEmployeeList['employee_job_title'] = "Administrator";
                        $sharedEmpArray[] = $sharedEmployeeList;
                        $isAdminShare = false;
                    }
                } else {
                    $employee = $share->getEmployeePostShared();
                    if (!in_array($empId, $empIdList)) {
                        $empName = $employee->getFirstAndLastNames();
                        $jobTitle = $employee->getJobTitleName();
                        array_push($empIdList, $empId);
                        $sharedEmployeeList['employee_number'] = $empId;
                        $sharedEmployeeList['employee_name'] = $empName;
                        $sharedEmployeeList['employee_job_title'] = $jobTitle;
                        $sharedEmpArray[] = $sharedEmployeeList;
                    }
                }
            }
        }
        return $sharedEmpArray;
    }
    
    /**
     * Gets Shares for a given employee number
     * If the employee number is zero, it will be considered as shares by Admin
     * 
     * @param type $empNum
     * @return type
     */
    public function getSharesFromEmployeeNumber($empNum) {
        return $this->getBuzzDao()->getSharesFromEmployeeNumber($empNum);
    }
}
