<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of viewBuzzImageAction
 *
 * @author nirmal
 */
class viewBuzzImageAction extends BaseBuzzAction {

    public function execute($request) {
        $imageId = $request->getParameter('imageId');

        if ($imageId) {
            $buzzImage = $this->getBuzzService()->getPhoto($imageId);
            $contents = $buzzImage->getPhoto();
            $contentType = $buzzImage->getFileType();

            $checksum = md5($contents);

            // Allow client side cache image unless image checksum changes.
            $eTag = $request->getHttpHeader('If-None-Match');

            $response = $this->getResponse();

            if ($eTag == $checksum) {
                $response->setStatusCode('304');
            } else {
                $response->setContentType($contentType);
                $response->setContent($contents);
            }

            $response->setHttpHeader('Pragma', 'Public');
            $response->setHttpHeader('ETag', $checksum);
            $response->addCacheControlHttpHeader('public, max-age=0, must-revalidate');

            $response->send();
        }

        return sfView::NONE;
    }

}
