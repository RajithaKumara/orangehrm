<?php

/**
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
 * Description of uploadImage
 *
 * @author aruna
 */
class uploadImageAction extends BuzzBaseAction{

    protected $photos = array();

    /**
     * this is function to get buzzService
     * @return BuzzService 
     */
    public function getBuzzService() {
        if (!$this->buzzService) {
            $this->buzzService = new BuzzService();
        }
        return $this->buzzService;
    }


    public function execute($request) {
        try{
            $this->loggedInUser=  $this->getUserId();
             
        } catch (Exception $ex) {
            $this->redirect('auth/login');
        }
        $this->files = $request->getFiles();
        $postContent = $request->getParameter('postContent');
        $this->savePost($postContent);
        foreach ($this->files as $file) {
            $photo = $this->getPhoto($file);
            $this->savePhoto($photo);
        }
        $this->saveShare();
    }

    private function savePhoto($photo) {
        $service = $this->getBuzzService();
        $service->savePhoto($photo);
    }

    private function getPhoto($file) {
        $photo = new Photo();
        
        $photo->photo = file_get_contents($file['tmp_name']);
        $photo->filename = $file['name'];
        $photo->file_type = $file['type'];
        list($width, $height) = getimagesize($file['tmp_name']);
        $photo->setHeight($height);
        $photo->setWidth($width);
        $photo->size = $file['size'];
        $photo->post_id = $this->post->getId();
        return $photo;
    }

    private function savePost($postContent) {
        $post = new Post();
        $post->setEmployeeNumber($this->getUserId());
        $post->setText($postContent);
        $post->setPostTime(date("Y-m-d H:i:s"));
        $service = $this->getBuzzService();
        $this->post = $service->savePost($post);
    }

    /**
     * 
     * @param Doctrine_Collection $photos
     */
    private function saveShare() {
        $share = new Share();
        $share->setEmployeeNumber($this->getUserId());
        $share->setShareTime(date("Y-m-d H:i:s"));
        $share->setType(0);
        $share->setPostId($this->post->getId());
        $service = $this->getBuzzService();
        $service->saveShare($share);
        $this->share = $share;
    }

}
