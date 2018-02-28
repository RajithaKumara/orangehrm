<?php

/**
 * PluginShare
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class PluginShare extends BaseShare {

    protected $buzzConfigService;
    protected $buzzTimeZoneUtility;

    public function getEmployeeFirstLastName($employee = NULL) {
        $employeeName = "";
        if ($this->getEmployeeNumber() != '') {
            if ($employee == NULL) {
                $employeeName = $this->getEmployeePostShared()->getFirstAndLastNames();
            } else {
                $employeeName = $employee->getFirstAndLastNames();
            }
        } else {
            $employeeName = 'Admin';
        }
        return $employeeName;
    }

    public function getTypeName() {
        $type = $this->getType();
        if ($type == '0') {
            return 'post';
        } else {
            return 'share';
        }
    }

    public function getLikedEmployeeNames() {

        $likeEmployeeNames = array();
        $likes = $this->getLike();
        foreach ($likes as $like) {
            $likeEmployeeNames[] = $like->getEmployeeFirstLastName();
        }
        return $likeEmployeeNames;
    }

    /**
     * Returns the lits of emloyees who liked the share.
     * @return Employee Collection
     */
    public function getLikedEmployees($loggedInUserId) {
        $count = $this->getBuzzConfigService()->getBuzzLikeCount();
        $arrayOfEmployees = array();
        foreach ($this->getLike() as $value) {
            if ($count <= 0) {
                break;
            }
            $empId = $value->getEmployeeNumber();
            $empName = $value->getEmployeeLike()->getFirstAndLastNames();
            if ($empName == " ") {
                $empName = 'Admin';
            }if ($empId == $loggedInUserId) {
                $empName = "you like this";
                $arrayOfEmployees[] = $empName;
                $count--;
            }
        }
        foreach ($this->getLike() as $value) {
            if ($count <= 0) {
                break;
            }
            $empId = $value->getEmployeeNumber();
            $empName = $value->getEmployeeLike()->getFirstAndLastNames();
            if ($empName == " ") {
                $empName = 'Admin';
            }
            if ($empId == $loggedInUserId) {
                
            } else {
                $arrayOfEmployees[] = $empName;
                $count--;
            }
        }

        return $arrayOfEmployees;
    }

    /**
     * Returns the lits of emloyees who liked the share.
     * @return Employee Collection
     */
    public function getSharedEmployees($loggedInUserId) {
        $count = $this->getBuzzConfigService()->getPostShareCount();
        $arrayOfEmployees = array();
        foreach ($this->getLike() as $value) {
            if ($count <= 0) {
                break;
            }
            $empId = $value->getEmployeeNumber();
            $empName = $value->getEmployeeLike()->getFirstAndLastNames();
            if ($empName == " ") {
                $empName = 'Admin';
            }if ($empId == $loggedInUserId) {
                $empName = "you shared this";
                $arrayOfEmployees[] = $empName;
                $count--;
            }
        }
        foreach ($this->getLike() as $value) {
            if ($count <= 0) {
                break;
            }
            $empId = $value->getEmployeeNumber();
            $empName = $value->getEmployeeLike()->getFirstAndLastNames();
            if ($empName == " ") {
                $empName = 'Admin';
            }
            if ($empId == $loggedInUserId) {
                
            } else {
                $arrayOfEmployees[] = $empName;
                $count--;
            }
        }

        return $arrayOfEmployees;
    }

    public function getLikedEmployeeList() {
        $arrayOfEmployees = array();
        foreach ($this->getLike() as $value) {

            $arrayOfEmployees[] = $value->getEmployeeLike();
        }
        return $arrayOfEmployees;
    }

    /**
     * Get Liked Employee List as an array of array
     * @return type
     */
    public function getLikedEmployeeListAsArray() {
        $arrayOfEmployees = array();
        foreach ($this->getLike() as $value) {

            $arrayOfEmployees[] = $value->getEmployeeLike();
        }
        return $arrayOfEmployees;
    }

    /**
     * 
     * @return buzzConfigService
     */
    private function getBuzzConfigService() {
        if (!$this->buzzConfigService) {
            $this->buzzConfigService = new BuzzConfigService();
        }

        return $this->buzzConfigService;
    }

    public function getDate() {
        return set_datepicker_date_format($this->getShareTime());
    }

    public function getTime() {
        $timeFormat = $this->getBuzzConfigService()->getTimeFormat();
        return date($timeFormat, strtotime($this->getShareTime()));
        ;
    }

    public function isUnLike($id) {
        $likes = $this->getUnlike();
        $userId = $id;

        foreach ($likes as $like) {
            if ($like->getEmployeeNumber() == $userId) {

                return 'yes';
            }
        }

        return 'no';

//        if ($userId != "") {
//
//            foreach ($likes as $like) {
//                if ($like->getEmployeeNumber() == $userId) {
//
//                    return 'yes';
//                }
//            }
//
//            return 'no';
//        } else {
//
//            foreach ($likes as $like) {
//                if ($like->getEmployeeNumber() == "") {
//
//                    return 'yes';
//                }
//            }
//            return 'no';
//        }
    }

    /**
     * check loged In User Like this post
     * @param int $id
     * @return string
     */
    public function isLike($id) {
        $likes = $this->getLike();
        $userId = $id;

        foreach ($likes as $like) {

            if ($like->getEmployeeNumber() == $userId) {
                return 'Unlike';
            }
        }
        return 'Like';

//        if ($userId != "") {
//
//            foreach ($likes as $like) {
//
//                if ($like->getEmployeeNumber() == $userId) {
//                    return 'Unlike';
//                }
//            }
//            return 'Like';
//        } else {
//            foreach ($likes as $like) {
//                if ($like->getEmployeeNumber() == "") {
//                    return 'Unlike';
//                }
//            }
//            return 'Like';
//        }
    }

    public function isUnLikeUser($id) {
        $likes = $this->getUnlike();
        $userId = $id;
        if ($userId != "") {
            foreach ($likes as $like) {
                if ($like->getEmployeeNumber() == $userId) {
                    return 'yes';
                }
            }
            return 'no';
        } else {
            foreach ($likes as $like) {
                if ($like->getEmployeeNumber() == "") {
                    return 'yes';
                }
            }
            return 'no';
        }
    }

    public function calShareCount($post = NULL) {
        $shareCount = 0;
        if ($post == NULL) {
            $post = $this->getPostShared();
            $shareCount = count($post->getShare()) - 1;
        } else {
            $shareCount = count($post->getShare()) - 1;
        }
        return $shareCount;
    }

    public function getSharedEmployeeNames() {
        $sharedEmpArray = array();
        $post = $this->getPostShared();
        $sharesList = $post->getShare();
        foreach ($sharesList as $share) {
            array_push($sharedEmpArray, $share->getEmployeePostShared());
        }
        return $sharedEmpArray;
    }

    /**
     * Returns the list of emloyees who liked the share [WEB SERVICES].
     * @return Employee Collection
     */
    public function getShareLikedEmployeeList() {
        $arrayOfEmployees = array();
        foreach ($this->getLike() as $value) {
            $employee = array();
            $empId = $value->getEmployeeNumber();
            $empName = $value->getEmployeeLike()->getFirstAndLastNames();
            $jobTitle = $value->getEmployeeLike()->getJobTitleName();

            if ($empId == null) {
                $empName = "Admin";
                $jobTitle = "Administrator";
            }
            $employee['employee_name'] = $empName;
            $employee['employee_number'] = $empId;
            $employee['employee_job_title'] = $jobTitle;
            $arrayOfEmployees[] = $employee;
        }
        return $arrayOfEmployees;
    }

    /**
     * Returns the list of emloyees who disliked the share [WEB SERVICES].
     * @return Employee Collection
     */
    public function getShareDislikedEmployeeList() {
        $arrayOfEmployees = array();
        foreach ($this->getUnlike() as $value) {
            $employee = array();
            $empId = $value->getEmployeeNumber();
            $empName = $value->getEmployeeUnLike()->getFirstAndLastNames();
            $jobTitle = $value->getEmployeeUnLike()->getJobTitleName();

            if ($empId == null) {
                $empName = "Admin";
                $jobTitle = "Administrator";
            }
            $employee['employee_name'] = $empName;
            $employee['employee_number'] = $empId;
            $employee['employee_job_title'] = $jobTitle;
            $arrayOfEmployees[] = $employee;
        }
        return $arrayOfEmployees;
    }

    /**
     * Returns the list of emloyees who shared the share [WEB SERVICES].
     * @return Employee Collection
     */
    public function getSharePostSharedEmployeeList() {
        $arrayOfEmployees = array();
        foreach ($this->getUnlike() as $value) {
            $employee = array();
            $empId = $value->getEmployeeNumber();
            $empName = $value->getEmployeeUnLike()->getFirstAndLastNames();
            $jobTitle = $value->getEmployeeUnLike()->getJobTitleName();

            if ($empId == null) {
                $empName = "Admin";
                $jobTitle = "Administrator";
            }
            $employee['employee_name'] = $empName;
            $employee['employee_number'] = $empId;
            $employee['employee_job_title'] = $jobTitle;
            $arrayOfEmployees[] = $employee;
        }
        return $arrayOfEmployees;
    }

    /**
     * check loged In User Like this post
     * @param int $id
     * @return string
     */
    public function isShareLike($id) {
        $likes = $this->getLike();
        $userId = $id;

//        foreach ($likes as $like) {
//
//            if ($like->getEmployeeNumber() == $userId) {
//                return 'Like';
//            }
//        }
//        return 'Unlike';

        if ($userId != "") {

            foreach ($likes as $like) {

                if ($like->getEmployeeNumber() == $userId) {
                    return true;
                }
            }
            return false;
        } else {
            foreach ($likes as $like) {
                if ($like->getEmployeeNumber() == "") {
                    return true;
                }
            }
            return false;
        }
    }

    public function isShareUnLike($id) {
        $likes = $this->getUnlike();
        $userId = $id;

//        foreach ($likes as $like) {
//            if ($like->getEmployeeNumber() == $userId) {
//
//                return 'yes';
//            }
//        }
//
//        return 'no';

        if ($userId != "") {

            foreach ($likes as $like) {
                if ($like->getEmployeeNumber() == $userId) {

                    return true;
                }
            }

            return false;
        } else {

            foreach ($likes as $like) {
                if ($like->getEmployeeNumber() == "") {

                    return true;
                }
            }
            return false;
        }
    }

    public function __call($method, $arguments) {
        if ($method == 'getShareTime') {
            $offset = sfContext::getInstance()->getUser()->getUserTimeZoneOffset();
            $shareTime = parent::__call($method, $arguments);
            if($offset==0){
                return $shareTime;
            }
            $date = gmdate('Y-m-d H:i:s',strtotime($shareTime));
            $given = new DateTime($date,new DateTimeZone('+0:00'));
            $given->setTimezone(new DateTimeZone($this->getBuzzTimezoneUtility()->getTimeZoneFromClientOffset($offset)));
            return $given->format("Y-m-d H:i:s");
        }
        return parent::__call($method, $arguments);
    }

    public function getBuzzTimezoneUtility() {
        if(!$this->buzzTimeZoneUtility instanceof BuzzTimezoneUtility) {
            $this->buzzTimeZoneUtility = new BuzzTimezoneUtility();
        }
        return $this->buzzTimeZoneUtility;
    }
}
