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
                    $this->getUser()->setFlash('error', __("This url is not a valid url of a video or it is not supported by the system"));
                }
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
            return 'not';
        }

        $temp = split("youtu.be/", $url);

        if (count($temp) > 1) {
            $embededUrl = "https://www.youtube.com/embed/" . $temp[1] . "?rel=0";
            return $embededUrl;
        }

        $temp2 = split("v=", $url);
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

        $temp3 = split("//vimeo.com/", $url);
        if (count($temp3) > 1) {
            $urlParts = explode("/", parse_url($temp3[1], PHP_URL_PATH));
            $videoId = (int) $urlParts[count($urlParts) - 1];
            $embededUrl = "http://player.vimeo.com/video/" . $videoId;
            return $embededUrl;
        }

        $temp4 = split("yahoo.com/", $url);
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

    function urlExist($url) {
        $handle = curl_init($url);
        if (false === $handle) {
            return 'not';
        }
        curl_setopt($handle, CURLOPT_HEADER, false);
        curl_setopt($handle, CURLOPT_FAILONERROR, true);  // this works
        curl_setopt($handle, CURLOPT_HTTPHEADER, Array("User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.15) Gecko/20080623 Firefox/2.0.0.15")); // request as if Firefox
        curl_setopt($handle, CURLOPT_NOBODY, true);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, false);
        $connectable = curl_exec($handle);
        ##print $connectable;
        curl_close($handle);
        var_dump($connectable);
        die;
        return $connectable;
    }

}
