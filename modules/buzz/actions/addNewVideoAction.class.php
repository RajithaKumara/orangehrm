<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of addNewVideoAction
 *
 * @author dewmal
 */
class addNewVideoAction extends BaseBuzzAction {

    /**
     * 
     * @param CommentForm $form
     */
    private function setCommentForm($form) {
        $this->commentForm = $form;
    }

    /**
     * 
     * @return CommentForm
     */
    private function getCommentForm() {
        if (!$this->commentForm) {
            $this->setCommentForm(new CommentForm());
        }
        return $this->commentForm;
    }

    /**
     * 
     * @return CreateVideoForm
     */
    private function getVideoForm() {
        if (!($this->videoForm instanceof CreateVideoForm)) {
            $this->videoForm = new CreateVideoForm();
        }
        return $this->videoForm;
    }
    
     /**
     * 
     * @return CommentForm
     */
    private function getEditForm() {
        if (!($this->editForm instanceof CommentForm)) {
            $this->editForm = new CommentForm();
        }
        return $this->editForm;
    }

    public function execute($request) {
        try {
            $this->loggedInUser = $this->getLogedInEmployeeNumber();
            $this->url = $request->getParameter('url');
            $this->action = $request->getParameter('actions');

            if ($this->action == 'paste') {
                $this->isSuccess = 'yes';
                $this->videoFeedUrl = $this->getVideoFeedLinkFromUrl($this->url);
                if ($this->videoFeedUrl === 'not') {
                    $this->isSuccess = 'notVideo';
                }
                $this->videoForm = $this->getVideoForm();
            } else {
                $this->isSuccess = 'notPosted';
                $this->videoFeedUrl = $this->url;
                $this->text = $request->getParameter('text');

                $post = $this->savePost($this->getLogedInEmployeeNumber(), $this->text);
                $this->post = $this->saveShare($post);
                $this->saveVideo($post);
                $this->setShare($this->post);
                $this->isSuccess = 'posted';
                $this->loggedInUser = $this->getLogedInEmployeeNumber();
            }
            $this->commentForm = $this->getCommentForm();
            $this->editForm = $this->getEditForm();
        } catch (Exception $ex) {
            $this->error = 'redirect';
        }
    }

    /**
     * set parameters share to view
     * @param Post $post
     * @return share
     */
    public function setShare($share) {

        $this->postId = $share->getId();
        $this->originalPostId = $share->getPostId();
        $this->postEmployeeName = $share->getEmployeeFirstLastName();
        $this->employeeId = $share->getEmployeeNumber();
        $this->postDate = $share->getDate();
        $this->postTime = $share->getTime();
        $this->noOfLikes = $share->getNumberOfLikes();
        $this->isLike = $share->isLike($this->getLogedInEmployeeNumber());
        $this->postContent = $share->getPostShared()->getText();
    }

    /**
     * this is function to save Video to database
     * @param type $post
     */
    private function saveVideo($post) {
        $link = new Link();
        $link->setType(1);
        $link->setLink($this->videoFeedUrl);
        $link->setPostId($post->getId());
        $this->getBuzzService()->saveLink($link);
    }

    private function getVideoFeedLinkFromUrl($url) {
        $temp = split("youtu.be/", $url);

        if (count($temp) > 1) {
            $embededUrl = "http://www.youtube.com/embed/" . $temp[1] . "?rel=0";
            return $embededUrl;
        }

        $temp2 = split("v=", $url);
        if (count($temp2) > 1) {
            $embededUrl = "http://www.youtube.com/embed/" . $temp2[1] . "?rel=0";
            return $embededUrl;
        }

        $temp3 = split("//vimeo.com/", $url);
        if (count($temp3) > 1) {
            $embededUrl = "//player.vimeo.com/video/" . $temp3[1];
            return $embededUrl;
        }

        $temp4 = split("screen.yahoo.com/", $url);
        if (count($temp4) > 1) {
            $lstCode = split("/", $temp4[1]);
            $last = count($lstCode) - 1;
            $embededUrl = "https://screen.yahoo.com/" . $lstCode[$last] . "?format=embed";
            return $embededUrl;
        }

        $temp5 = split("dailymotion.com/", $url);
        if (count($temp5) > 1) {
            $lstCode = split("/", $temp5[1]);
            $last = count($lstCode) - 1;
            $codeFirst = split("_", $lstCode[$last]);
            $embededUrl = "//www.dailymotion.com/embed/video/" . $codeFirst[0];
            return $embededUrl;
        }

        $temp6 = split("http://dai.ly/", $url);
        if (count($temp6) > 1) {

            $embededUrl = "//www.dailymotion.com/embed/video/" . $temp6[1];
            return $embededUrl;
        }

        $temp7 = split("vube.com/", $url);
        if (count($temp7) > 1) {
            $lstCode = split("/", $temp7[1]);

            $last = count($lstCode) - 1;
            $vube = split("t=s", $lstCode[$last]);

            $embededUrl = "http://vube.com/embed/video/" . $vube[0];
            return $embededUrl;
        }

        $temp8 = split("http://www.metacafe.com/watch/", $url);
        if (count($temp8) > 1) {
            $lstCode = split("/", $temp8[1]);



            $embededUrl = "http://www.metacafe.com/embed/" . $lstCode[0];
            return $embededUrl;
        }

        $temp9 = split("www.ustream.tv/recorded/", $url);
        if (count($temp9) > 1) {
            $embededUrl = "http://www.ustream.tv/embed/recorded/" . $temp9[1] . "?v=3&amp;wmode=direct";
            return $embededUrl;
        }


        return 'not';
    }

    /**
     * save post to the database
     * @return Post
     */
    public function savePost($userId, $text) {
        $post = new Post();
        $post->setEmployeeNumber($userId);
        $post->setText($text);
        $post->setPostTime(date("Y-m-d H:i:s"));

        return $this->getBuzzService()->savePost($post);
    }

    /**
     * 
     * @param type $link
     * @return type
     */
    private function saveLink($link) {
        return $this->getBuzzService()->saveLink($link);
    }

    /**
     * save share to the database
     * @param Post $post
     * @return share
     */
    public function saveShare($post) {
        $share = $this->setShares($post);
        return $this->getBuzzService()->saveShare($share);
    }

    /**
     * set share details
     * @param post $post
     * @return Share
     */
    public function setShares($post) {
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

//put your code here
}
