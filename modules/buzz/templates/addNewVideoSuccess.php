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
use_stylesheet(plugin_web_path('orangehrmBuzzPlugin', 'css/viewBuzzSuccess_1'));
use_javascript(plugin_web_path('orangehrmBuzzPlugin', 'js/viewBuzzSuccess_1'));
?>
<!--A single post-->
<div id="videoPostArea">
    <?php if ($isSuccess == 'yes') { ?>
        <div id="tempVideoBlock">
            <div id="postBody">
                <form id="frmCreateComment" method="" action="" 
                      enctype="multipart/form-data">
                          <?php
                          $videoForm->setDefault('content', $url);
                          $placeholder = 'Write something about this video';
                          echo $videoForm['content']->render(array('id' => "shareVideo",
                              'class' => 'commentBox', 'style' => 'width: 95%', 'rows' => '2', 'placeholder' => $placeholder));
                          ?>

                </form>
                <div id="yuoutubeVideoId" hidden="true"><?php echo $code; ?></div>


                <div style="text-align: center; margin-bottom: 10px;">
                    <iframe src="<?php echo $code; ?>" width="80%" height="225px" frameborder="0" allowfullscreen></iframe >
                </div>

                <p>
                    <button type="submit" id='<?php echo 'btnSaveVideo_', $code; ?>' class="submitBtn btnSaveVideo">
                        <?php echo __("Save Video"); ?>
                    </button>
                </p>


            </div>
        </div>

    <?php } else if ($isSuccess == 'posted') { ?>
        <?php include_component('buzz', 'viewPost', array('post' => $post)); ?>


    <?php } else if ($error == 'redirect') { ?>

    <?php } else if ($isSuccess == 'notVideo') { ?>
                <div id="tempVideoBlock">
                <div id="postBody">
                 <form id="frmUploadVideo" method="POST" action="" 
                          enctype="multipart/form-data">
                        <fieldset>
                            <ol>
                                <?php echo $videoForm->render(); ?>            
                            </ol>
                        </fieldset>
                        <p>

                        </p>
                    </form>
                

                <p>
                    <?php echo 'not a correct video'?>
                </p>


            </div>
        </div>
    <?php } else if ($isSuccess == 'posted') { ?>

    <?php } ?>

</div>
<!--Single post end-->
