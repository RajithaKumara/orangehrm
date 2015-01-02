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
            $this->videoForm = $this->getVideoForm();
            if ($this->action == 'paste') {
                $this->isSuccessfullyPastedUrl = true;
                $this->videoFeedUrl = $this->getVideoFeedLinkFromUrl($this->url);
                if ($this->videoFeedUrl === 'not') {
                    $this->isSuccessfullyPastedUrl = false;
                }
                 $arr = array('url' => $this->url, 'videoFeedUrl' => $this->videoFeedUrl);

        echo json_encode($arr);
        die();
            } else {
                $this->isSuccessFullyPosted = false;
                $this->videoForm->bind($request->getParameter($this->videoForm->getName()));
                if ($this->videoForm->isValid()) {
                    $this->postSaved = $this->videoForm->save($this->loggedInUser);
                    $this->isSuccessFullyPosted = true;
                }
            }
        } catch (Exception $ex) {
            
        }
    }

    /**
     * function to check url is video url and get feed url from it
     * @param type $url
     * @return string
     */
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

}
