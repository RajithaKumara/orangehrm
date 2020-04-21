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
 * Description of viewPostPhotoAction
 *
 */
class viewPostPhotoAction extends BaseBuzzAction {
    const SECONDS_IN_YEAR = 31536000;
    
    public function execute($request) {
        $id = $request->getParameter('id');
        $photo = $this->getBuzzService()->getPhoto($id);
        $response = $this->getResponse();

        if (!empty($photo)) {
            $contents = $photo->photo;
            $contentType = $photo->file_type;
            $fileSize = $empPicture->size;
            $fileName = $empPicture->filename;
        } else {
            $response->setStatusCode('404');
            return sfView::NONE;
        }

        $checksum = md5($contents);

        // Allow client side cache image unless image checksum changes.
        $eTag = $request->getHttpHeader('If-None-Match');

        

        if ($eTag == $checksum) {
            $response->setStatusCode('304');
        } else {
            $response->setContentType($contentType);
            $response->setContent($contents);
        }

        $response->setHttpHeader('Pragma', 'Public');
        $response->setHttpHeader('ETag', $checksum);
        
        $date = new DateTime();
        $date->modify('+1 Year');
        
        $response->setHttpHeader('Expires', gmdate('D, d M Y H:i:s', $date->getTimestamp()) . ' GMT');
        
        $response->addCacheControlHttpHeader('public, max-age=' . self::SECONDS_IN_YEAR . ', must-revalidate');

        $response->send();

        return sfView::NONE;
    }

}
