<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of getFormCsrfTokenAction
 *
 * @author dewmal
 */
class getLogedToBuzzAction extends BuzzBaseAction {

    public function execute($request) {
        try {
            $this->getUserId();
            $postForm = new CreatePostForm();
            $token = $postForm->getCSRFToken();

            $imageForm = new UploadPhotoForm();
            $token2 = $imageForm->getCSRFToken();

            $arr = array('state' => 'loged');

            echo json_encode($arr);
            die();
        } catch (Exception $ex) {
            $arr = array('state' => 'refresh');

            echo json_encode($arr);
            die();
        }

        return sfView::NONE;
    }

//put your code here
}
