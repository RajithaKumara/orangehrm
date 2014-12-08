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
    /**
     * check loged In User Like this post
     * @param int $id
     * @return string
     */
    public function isLike($id) {
        $likes = $this->getLike();
        $userId = $id;
        if ($userId) {
            foreach ($likes as $like) {
                if ($like->getEmployeeNumber() == $userId) {
                    return 'Unlike';
                }
            }
            return 'Like';
        } else {
            foreach ($likes as $like) {
                if ($like->getEmployeeNumber() == null) {
                    return 'Unlike';
                }
            }
            return 'Like';
        }
    }

    public function getEmployeeFirstLastName() {
        if ($this->getEmployeeNumber() != '') {
            return $this->getEmployeePostShared()->getFirstAndLastNames();
        } else {
            return 'Admin';
        }
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
        $count=  $this->getBuzzConfigService()->getBuzzLikeCount();
        $arrayOfEmployees = array();
        foreach ($this->getLike() as $value) {
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
            $empId = $value->getEmployeeNumber();
            $empName = $value->getEmployeeLike()->getFirstAndLastNames();
            if ($empName == " ") {
                $empName = 'Admin';
            }
            if ($empId == $loggedInUserId) {
                
            }else{
            $arrayOfEmployees[] = $empName;
            $count--;
            }
            if($count<=0){
                break;
            }
        }
        
        return $arrayOfEmployees;
    }
    
    public function getLikedEmployeeList(){
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
            $this->buzzConfigService= new BuzzConfigService();
        }
        
        return $this->buzzConfigService;
    }
    
    public function getDate(){
        return set_datepicker_date_format($this->getShareTime());
    }
    
    public function getTime(){
        $timeFormat=  $this->getBuzzConfigService()->getTimeFormat();
        return date($timeFormat, strtotime($this->getShareTime()));;
    }
    
    public function isUnLike($id) {
        $likes = $this->getUnlike();
        $userId = $id;
        if ($userId) {
            foreach ($likes as $like) {
                if ($like->getEmployeeNumber() == $userId) {
                    return 'yes';
                }
            }
            return 'no';
        } else {
            foreach ($likes as $like) {
                if ($like->getEmployeeNumber() == null) {
                    return 'yes';
                }
            }
            return 'no';
        }
    }
    
    public function calShareCount(){
        $post=  $this->getPostShared();
        return count($post->getShare())-1;
    }


}
