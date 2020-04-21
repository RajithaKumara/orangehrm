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
?>
<?php if ($error == 'no') { ?>
    <div id='<?php echo 'postContent_' . $post->getId() ?>'>
        <?php echo BuzzTextParserService::parseText($post->getText());
        ?>
        <?php
        if ($type == 'share') {
            $originalPost = $post->getPostShared();
            $originalPostId = $originalPost->getId();
            $originalPostEmpNumber = $originalPost->getEmployeeNumber();
            $originalPostSharerName = $originalPost->getEmployeeFirstLastName();
            $originalPostDate = $originalPost->getDate();
            $originalPostTime = $originalPost->getTime();
            $originalPostContent = $originalPost->getText();
            ?>
            <!--SUB POST START-->
            <div id="sharedPostBody">

                <div id="postBodyFirstRow">
                    <div id="postFirstRowColumnOne">
                        <img alt="<?php echo __("Employee Photo"); ?>" src="<?php echo url_for("pim/viewPhoto?empNumber=" . $originalPostEmpNumber); ?>" border="0" id="empPic" height="40" width="30"/>
                    </div>
                    <div id="postFirstRowColumnTwo">
                        <div id="postEmployeeName" >
                            <a class="name" href="javascript:void(0);" id='<?php echo 'postView_' . $post->getId() . '_' . $originalPostId ?>'>
                                <?php echo $originalPostSharerName; ?>
                            </a>
                        </div>
                        <div id="postDateTime">
                            <div id="postDate">
                                <?php echo $originalPostDate; ?>
                            </div>
                            <div id="postTime">
                                <?php echo $originalPostTime; ?>
                            </div>
                        </div>                        
                    </div>
                </div>

                <div id="postBodySecondRow">
                    <div id="postContent">
                        <?php echo BuzzTextParserService::parseText($originalPostContent); ?>
                    </div>
                </div>
            </div>
            <!--SUB POST END-->
            <div class="modal hide" id='<?php echo 'postViewOriginal_' . $post->getId() ?>'>

                <div class="modal-header">
                    <a class="close" data-dismiss="modal">×</a>
                </div>

                <div class="modal-body">

                    <div class="postView" id='<?php echo 'postViewContent_' . $post->getId() ?>'>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
<?php } else { ?>
    <div id ='errorFirstRow'>
        <?php
        include_partial('global/flash_messages');
//        echo __("This share has been deleted or you do not have permission to perform this action"); 
        ?>
    </div>
<?php } ?>