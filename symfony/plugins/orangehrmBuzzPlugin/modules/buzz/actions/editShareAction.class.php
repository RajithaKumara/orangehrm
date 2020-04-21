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
 * Description of likeOnShareAction
 *
 * @author aruna
 */
class editShareAction extends BaseBuzzAction {

    /**
     * @param sfForm $form
     * @return
     */
    protected function setForm(sfForm $form) {
        if (is_null($this->form)) {
            $this->form = $form;
        }
    }

    public function execute($request) {
        try {
            $this->setForm(new DeleteOrEditShareForm());

            if ($request->isMethod('post')) {
                $this->form->bind($request->getParameter($this->form->getName()));
                if ($this->form->isValid()) {
                    $formValues = $this->form->getValues();
                    $this->shareId = $formValues['shareId'];
                    $this->editedContent = $formValues['textShare'];

                    $this->loggedInUser = $this->getLogedInEmployeeNumber();
                    $this->type = 'post';
                    $this->error = 'no';

                    $share = $this->getBuzzService()->getShareById($this->shareId);
                    if ($share != null) {
                        $this->post = $this->saveEditedContent($share);
                    } else {
                        $this->error = 'yes';
                        $this->getUser()->setFlash('error', __("This share has been deleted or you do not have permission to perform this action"));
                    }
                }
            }
        } catch (Exception $ex) {
            $this->redirect('auth/login');
        }
    }

    /**
     * save edited content of post and share
     * @return Post
     */
    public function saveEditedContent($share) {

        if ($share->getEmployeeNumber() == $this->getLogedInEmployeeNumber()) {
            if ($share->getType() == 1) {
                $this->type = 'share';
                $share = $this->saveShare($share);
            } else {

                $share = $this->savePost($share->getPostShared());
            }
        }
        return $share;
    }

    /**
     * save post to the database
     * @param Post $post
     * @return Post
     */
    public function savePost($post) {

        $post->setText($this->editedContent);
        $links = $post->getLinks();
        if($links->count() > 0){
            $url =  $this->getBuzzService()->updateLinks($this->editedContent);
            $urls = $this->getBuzzService()->getUrlsArray($url);
            if($links->count() <= count($urls)){
                $i = 0;
                foreach($links as $link){
                    $link->setLink($urls[$i]);
                    $link->save();
                    $i++;
                }
            }
        }
        return $this->getBuzzService()->savePost($post);
    }

    /**
     * savbe share to the database
     * @param Share $share
     * @return Share
     */
    public function saveShare($share) {
        $share->setText($this->editedContent);
        return $this->getBuzzService()->saveShare($share);
    }

}
