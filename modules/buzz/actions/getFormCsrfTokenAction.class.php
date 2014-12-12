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
class getFormCsrfTokenAction extends BaseBuzzAction{
    
    public function execute($request) {
        try{
            $this->getUserId();
            $postForm= new CreatePostForm();
        $token= $postForm->getCSRFToken();
        
        $imageForm= new UploadPhotoForm();
        $token2= $imageForm->getCSRFToken();
        
        $arr = array ('post'=>$token,'image'=>$token2,'c'=>3,'d'=>4,'e'=>5);

    echo json_encode($arr);
    die();
        } catch (Exception $ex) {
            return sfView::NONE;
        }
        
    return sfView::NONE;
    }

//put your code here
}
