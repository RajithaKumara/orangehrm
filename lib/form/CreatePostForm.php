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
 * From to add a Task for an Emloyee
 *
 * @author aruna
 */
class CreatePostForm extends sfForm {

    private $widgets;

    /**
     * Defining widgets and thier default values
     */
    public function configure() {
        $textArea = new sfWidgetFormTextarea();
        $textArea->setAttributes(array('placeholder' => 'What\'s on your mind?', 'rows' => '2', 'cols' => '40'));

        $linkAddress = new sfWidgetFormTextarea();
        $linkAddress->setAttribute('hidden', 'hidden');
        $linkAddress->setAttribute('class', 'linkAddress');

        $linkTitle = new sfWidgetFormTextarea();
        $linkTitle->setAttribute('hidden', 'hidden');
        $linkTitle->setAttribute('class', 'linkTitle');

        $linkText = new sfWidgetFormTextarea();
        $linkText->setAttribute('hidden', 'hidden');
        $linkText->setAttribute('class', 'linkText');

        $this->widgets = array(
            'content' => $textArea,
            'linkAddress' => $linkAddress,
            'linkTitle' => $linkTitle,
            'linkText' => $linkText
        );
        $this->setWidgets($this->widgets);
        $this->widgetSchema->setNameFormat('createPost[%s]');
        $this->getWidgetSchema()->setLabels($this->getFormLabels());
        $this->assignValidators();
    }

    /**
     * Defining the validators for the widgets
     */
    public function assignValidators() {
        $this->setValidators(array(
            'content' => new sfValidatorString(array('required' => true)),
            'linkAddress' => new sfValidatorString(array('required' => false)),
            'linkTitle' => new sfValidatorString(array('required' => false)),
            'linkText' => new sfValidatorString(array('required' => false))
        ));
    }

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

    /**
     * Get the label texts for the form widgets
     * @return array Label Texts
     */
    protected function getFormLabels() {

        $labels = array(
            'content' => __(' '),
            'linkAddress' => __(' '),
            'linkTitle' => __(' '),
            'linkText' => __(' ')
        );
        return $labels;
    }

    /**
     * save share and post to database
     * @param int $logeInUserId
     * @return Share
     */
    public function save($logeInUserId) {
        $post = $this->savePost($logeInUserId);
        $share = $this->saveShare($post);
        if (strlen($this->getValue('linkAddress')) > 0) {
            $link = $this->setLink($share);
            $this->saveLink($link);
        }
        return $share;
    }

    /**
     * save post to the database
     * @return Post
     */
    public function savePost($userId) {
        $post = new Post();
        $post->setEmployeeNumber($userId);
        $post->setText($this->getValue('content'));
        $post->setPostTime(date("Y-m-d H:i:s"));

        return $this->getBuzzService()->savePost($post);
    }

    /**
     * save share to the database
     * @param Post $post
     * @return share
     */
    public function saveShare($post) {
        $share = $this->setShare($post);
        return $this->getBuzzService()->saveShare($share);
    }

    /**
     * set share details
     * @param post $post
     * @return Share
     */
    public function setShare($post) {
        $share = new Share();
        $share->setPostId($post->getId());
        $share->setEmployeeNumber($post->getEmployeeNumber());
        $share->setNumberOfComments(0);
        $share->setNumberOfLikes(0);
        $share->setNumberOfUnlikes(0);
        $share->setShareTime(date("Y-m-d H:i:s"));
        $share->setType(0);
        return $share;
    }

    /**
     * create link 
     * @param type $share
     * @param type $postLinkAddress
     * @param type $linkTitle
     * @param type $linkText
     * @return \Link
     */
    private function setLink($share) {
        $link = new Link();
        $link->setPostId($share->getPostId());
        $link->setLink($this->getValue('linkAddress'));
        $link->setType(0);
        $link->setTitle($this->getValue('linkTitle'));
        $link->setDescription($this->getValue('linkText'));
        return $link;
    }

    /**
     * save links to database
     * @param type $link
     * @return type
     */
    private function saveLink($link) {
        return $this->getBuzzService()->saveLink($link);
    }

}
