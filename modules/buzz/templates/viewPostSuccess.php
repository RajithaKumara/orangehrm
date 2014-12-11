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
use_stylesheet(plugin_web_path('orangehrmBuzzPlugin', 'css/viewBuzzSuccess'));
use_stylesheet(plugin_web_path('orangehrmBuzzPlugin', 'css/viewBuzzSuccessComment'));
use_stylesheet(plugin_web_path('orangehrmBuzzPlugin', 'css/tooltip_css/jquery.qtip.min'));
use_javascript(plugin_web_path('orangehrmBuzzPlugin', 'js/viewBuzzSuccess'));
use_javascript(plugin_web_path('orangehrmBuzzPlugin', 'js/tooltip_js/jquery.qtip.min'));

//include_component('buzz', 'viewPost', array('post' => $share));
?>



<div id="photoPage" style="height: 400px;top: 15px;left: 12px;width: 450px;margin-bottom: -20px;position: absolute;overflow-y: auto">
    <div id="postBody" style="margin-top: 0px">

        <div id="postBodyFirstRow">
            <div id="postFirstRowColumnOne">
                <img alt="<?php echo __("Employee Photo"); ?>" src="<?php echo url_for("buzz/viewPhoto?empNumber=" . $employeeID); ?>" border="0" id="empPic" />
            </div>
            <div id="postFirstRowColumnTwo">
                <div id="postEmployeeName" >
                    <a class="name" href= '<?php echo url_for("buzz/viewProfile?empNumber=" . $employeeID); ?>' >
                        <?php echo $postEmployeeName; ?>
                    </a>
                </div>
                <div id="postEmloyeeJobTitle" style="margin-bottom: 0px;margin-top: -10px">
                    <?php echo $postEmployeeJobTitle; ?>
                </div>
                <div id="postDateTime" style="margin-top: 0px">
                    <div id="postDate">
                        <?php echo $postDate; ?>
                    </div>
                    <div id="postTime">
                        <?php echo $postTime; ?>
                    </div>
                </div>                        
            </div>

            <div id="postFirstRowColumnThree">
                <?php if (($employeeID == $loggedInUser) || ($loggedInUser == '')) { ?>
                    <div id="postOptionWidget">
                        <div class="dropdown" style="margin: -70px -22px 0 0;">
                            <a class="account"  id=<?php echo $postId ?> ></a>
                            <div class="submenu" id=<?php echo 'submenu' . $postId ?>>
                                <ul class = "root">
                                    <li ><a href = "javascript:void(0)" class="editShare" id=<?php echo 'editShare_' . $postId ?> ><?php echo __("Edit"); ?></a></li>
                                    <li ><a href = "javascript:void(0)" class="deleteShare" id=<?php echo 'deleteShare_' . $postId ?>><?php echo __("Delete"); ?></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
        <style>
            .likeLinknew{
                background-color: black;
                opacity: 0.8;

                width: 40px;
                margin-left: 0px;
                z-index: 999;
                position: absolute;

            } 
            .unlikeLinknew{
                background-color: black;
                opacity: 0.8;

                width: 40px;
                margin-left: 40px;
                z-index: 999;
                position: absolute;

            }
            .shareLinknew{
                background-color: black;
                opacity: 0.8;

                width: 40px;
                margin-left: 80px;
                z-index: 999;
                position: absolute;

            }
            #postBodyThirdRowNew{
                width: 100%;
                height: 40px;
                margin-left: 59.7%;
                margin-top: -20px;
                margin-bottom: -20px;
            }
            .textTopOfImage{
                color: white;
                font-size: 20px;
                margin-left: 23px;
                margin-top: -27px;
                z-index: 9998;
                position: absolute;

            }
            .imageContainer {
                position: relative;
                width: 100%;
                height: 230px;
                //border: 1px solid white;
                margin: 0 auto;
                margin-top: 10px;
                border-radius: 10px;
                overflow: hidden;
                //max-width: 510px;
            }
            .imageContainer div {
                position: absolute;
                background: #ccc;
                border: 5px solid white;
            }
             #postBodySecondRowPop{
    padding: 5px;
    margin-top: -3px;
    text-align: justify;
    background-color: white;
    border-radius: 0 0 4px 4px;
    line-height: 1.5;
    font-family: 'SourceSansProLight';
    font-size: 16px;
    
}
        </style>

        <!--Old Code of like, unlike and share buttons-->
        <div hidden="true" id="postBodyThirdRowNew">
            <div class="likeLinknew"  id="<?php echo 'postLikebody_' . $postId ?>" style="background-color: <?php
                if ($isLike == 'Unlike') {
                    echo 'orange';
                }
                ?>">
                <a href="javascript:void(0)" class="<?php echo $isLike . ' postLike'; ?>" id='<?php echo 'postLike_' . $postId ?>'> <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like.png"); ?>" border="0" id='<?php echo 'postLike_' . $postId ?>'
                                                                                                                                          class="<?php echo $isLike . ' postLike'; ?>" height="40" width="40"/></a>
                <div class="textTopOfImage" id='<?php echo 'postLiketext_' . $postId ?>'><?php echo $postNoOfLikes ?></div>
            </div>
            <div class="unlikeLinknew" id='<?php echo 'postUnLikebody_' . $postId ?>' style="background-color: <?php
                 if ($isUnlike == 'yes') {
                     echo 'red';
                 }
                ?>">
                <a href="javascript:void(0)" class="postUnlike2" id=<?php echo 'postUnlike_' . $postId ?>> <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/un-like.png"); ?>" border="0" id='<?php echo 'postLike_' . $postId ?>' 
                                                                                                                 height="40" width="40"/></a>
                <div class="textTopOfImage" id='<?php echo 'postUnLiketext_' . $postId ?>'><?php echo $postUnlike ?></div>
            </div>

            <div class="shareLinknew" id='<?php echo 'postSharebody_' . $postId ?>' style="background-color: <?php
                 if ($shareCount > 0) {
                     echo 'green';
                 }
                ?>">
                <a href="javascript:void(0)" class="postShare" id=<?php echo 'postShare_' . $postId ?>> <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/share.png"); ?>" border="0" id='<?php echo 'postLike_' . $postId ?>'
                                                                                                              height="40" width="40"/></a>
                <div class="textTopOfImage"><?php echo $shareCount ?></div>
            </div>

        </div>



        <div id="postBodySecondRowPop" >
            <div id='<?php echo 'postContent_' . $postId ?>'>
                <?php echo BuzzTextParserService::parseText($postContent); ?>
                <?php
                if ($postType == '1') {
                    ?>
                    <!--SUB POST START-->
                    <div id="sharedPostBody">

                        <div id="postBodyFirstRow">
                            <div id="postFirstRowColumnOne">
                                <img alt="<?php echo __("Employee Photo"); ?>" src="<?php echo url_for("buzz/viewPhoto?empNumber=" . $originalPostEmpNumber); ?>" border="0" id="empPic" height="40" width="30"/>
                            </div>
                            <div id="postFirstRowColumnTwo">
                                <div id="postEmployeeName" >
                                    <a class="originalPostView" href="javascript:void(0);" id='<?php echo 'postView_' . $postId . '_' . $originalPostId ?>' >
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

                        <div id="postBodySecondRowPop">
                            <div id="postContent">
                                <?php echo BuzzTextParserService::parseText($originalPostContent); ?>
                            </div>
                        </div>
                    </div>
                    <!--SUB POST END-->

                    <?php
                } else {
                    echo BuzzTextParserService::parseText($originalPostContent);
                }
                ?>
            </div>
        </div>

        <?php if (count($originalPost->getLinks()) > 0) { ?>
            <?php foreach ($originalPost->getLinks() as $link) { ?>
                <?php if ($link->getType() == 1) { ?>
                    <iframe src="<?php echo $link->getLink(); ?>" width="100%" height="250" style="margin-top: 5px " frameborder="0" allowfullscreen></iframe >

                <?php } ?>
                <?php if ($link->getType() == 0) { ?>
                    <div id="postBodySecondRow">
                        <div id="postContent">
                            <p>
                                <a id="linkTitle" href="<?php echo $link->getLink(); ?>">
                                    <?php echo $link->getTitle(); ?></a> 
                            </p>
                            <p>
                            <div id="linkText"><?php echo BuzzTextParserService::parseText($link->getDescription()); ?></div>
                            </p>

                        </div>
                    </div>
                <?php } ?>
            <?php } ?>    
        <?php } ?>  

        <?php
        $photos = $sf_data->getRaw('originalPost')->getPhotos();

//        var_dump(count($photos));die;
        $imgCount = 1;
        ?>

        <?php
        if (count($photos) == 1) {
            ?>
            <div class="imageContainer">
                <div style="top: -5px; left: -5px; width: 100%; height: 250px;overflow: hidden">
                    <img id="<?php echo $imgCount . "_" . $postId; ?>" class="postPhoto" width="120%" src="data:image/jpeg;base64,<?php echo base64_encode($photos[0]->getPhoto()); ?>"/>
                </div>
            </div>
        <?php } else if (count($photos) == 2) {
            ?>
            <div class="imageContainer">
                <div style="top: -5px; left: -5px; width: 50%; height: 300px;overflow: hidden;">
                    <img id="<?php echo "1_" . $postId; ?>"  class="postPhoto" height="120%" src="data:image/jpeg;base64,<?php echo base64_encode($photos[0]->getPhoto()); ?>"/>
                </div>
                <div style="top: -5px; left: 50%; width: 50%; height: 300px ;overflow: hidden;">
                    <img id="<?php echo "2_" . $postId; ?>"  class="postPhoto" height="120%" src="data:image/jpeg;base64,<?php echo base64_encode($photos[1]->getPhoto()); ?>"/>
                </div>

            </div>

        <?php } else if (count($photos) == 3) {
            ?>
            <div class="imageContainer">
                <div style="top: -5px; left: -5px; width: 60%; height: 300px;overflow: hidden">
                    <img id="<?php echo "1_" . $postId; ?>"  class="postPhoto" height="120%" src="data:image/jpeg;base64,<?php echo base64_encode($photos[0]->getPhoto()); ?>"/>
                </div>
                <div style="top: -5px; left: 60%; width: 40%; height: 115px ;overflow: hidden">
                    <img id="<?php echo "2_" . $postId; ?>"  class="postPhoto" width="120%" src="data:image/jpeg;base64,<?php echo base64_encode($photos[1]->getPhoto()); ?>"/>
                </div>
                <div style="top: 115px; left: 60%; width: 40%; height: 148px ;overflow: hidden">
                    <img id="<?php echo "3_" . $postId; ?>"  class="postPhoto" width="120%" src="data:image/jpeg;base64,<?php echo base64_encode($photos[2]->getPhoto()); ?>"/>
                </div>
            </div>
        <?php } else if (count($photos) == 4) {
            ?>
            <div class="imageContainer">
                <div style="top: -5px; left: -5px; width: 40%; height: 230px;overflow: hidden">
                    <img id="<?php echo "1_" . $postId; ?>"  class="postPhoto" height ="140%" src="data:image/jpeg;base64,<?php echo base64_encode($photos[0]->getPhoto()); ?>"/>
                </div>
                <div style="top: -5px; left: 40%; width: 60%; height: 115px ;overflow: hidden">
                    <img id="<?php echo "2_" . $postId; ?>"  class="postPhoto" width="120%" src="data:image/jpeg;base64,<?php echo base64_encode($photos[1]->getPhoto()); ?>"/>
                </div>
                <div style="top: 115px; left: 40%; width: 30%; height: 115px ;overflow: hidden">
                    <img id="<?php echo "3_" . $postId; ?>"  class="postPhoto" width="120%" src="data:image/jpeg;base64,<?php echo base64_encode($photos[2]->getPhoto()); ?>"/>
                </div>
                <div style="top: 115px; left: 70%; width: 30%; height: 115px ;overflow: hidden">
                    <img id="<?php echo "4_" . $postId; ?>"  class="postPhoto" width="140%" src="data:image/jpeg;base64,<?php echo base64_encode($photos[3]->getPhoto()); ?>"/>
                </div>
            </div>
        <?php } else if (count($photos) == 5) {
            ?>
            <div class="imageContainer">
                <div style="top: -5px; left: -5px; width: 35%; height: 230px;overflow: hidden">
                    <img id="<?php echo "1_" . $postId; ?>"  class="postPhoto" height="140%" src="data:image/jpeg;base64,<?php echo base64_encode($photos[0]->getPhoto()); ?>"/>
                </div>
                <div style="top: -5px; left: 35%; width: 35%; height: 230px ;overflow: hidden">
                    <img id="<?php echo "2_" . $postId; ?>"  class="postPhoto" height="160%" src="data:image/jpeg;base64,<?php echo base64_encode($photos[1]->getPhoto()); ?>"/>
                </div>
                <div style="top: -5px; left: 70%; width: 30%; height: 78px ;overflow: hidden">
                    <img id="<?php echo "3_" . $postId; ?>"  class="postPhoto" width="120%" src="data:image/jpeg;base64,<?php echo base64_encode($photos[2]->getPhoto()); ?>"/>
                </div>
                <div style="top: 78px; left: 70%; width: 30%; height: 78px ;overflow: hidden">
                    <img id="<?php echo "4_" . $postId; ?>"  class="postPhoto" width="140%" src="data:image/jpeg;base64,<?php echo base64_encode($photos[3]->getPhoto()); ?>"/>
                </div>
                <div style="top: 156px; left: 70%; width: 30%; height: 75px ;overflow: hidden">
                    <img id="<?php echo "5_" . $postId; ?>"  class="postPhoto" width="140%" src="data:image/jpeg;base64,<?php echo base64_encode($photos[4]->getPhoto()); ?>"/>
                </div>
            </div>
            <?php
        }
        ?>
        <style type="text/css">
            #photoPage{

                background-color: white;
                -moz-border-radius: 10px;
                -webkit-border-radius: 10px;
                border-radius: 10px; /* future proofing */
                -khtml-border-radius: 5px; /* for old Konqueror browsers */
            }
            .imageNextBtn{

                background-color: transparent; /* make the button transparent */
                background-repeat: no-repeat;  /* make the background image appear only once */
                background-position: 0px 0px;  /* equivalent to 'top left' */
                border: none;           /* assuming we don't want any borders */
                cursor: pointer;        /* make the cursor like hovering over an <a> element */
                height: 50px;
                position: absolute;/* make this the size of your image */
                top: 0;
                bottom: 0;
                right: 0;
                margin: auto;
                z-index: 99999;
                vertical-align: middle; 
            }
            .imagePrevBtn{

                background-color: transparent; /* make the button transparent */
                background-repeat: no-repeat;  /* make the background image appear only once */
                background-position: 0px 0px;  /* equivalent to 'top left' */
                border: none;           /* assuming we don't want any borders */
                cursor: pointer;        /* make the cursor like hovering over an <a> element */
                height: 50px;
                position: absolute;/* make this the size of your image */
                top: 0;
                bottom: 0;
                left: 0;
                z-index: 99999;
                margin: auto;
                vertical-align: middle; 
            }
            .photoViewEmp{
                height: 60px;
                padding: 10px;
                margin-top: 10px;
                margin-bottom: 10px;
                background-color: white;
                -moz-border-radius: 10px;
                -webkit-border-radius: 10px;
                border-radius: 10px; /* future proofing */
                -khtml-border-radius: 5px; /* for old Konqueror browsers */
            }
            #modalnewlook{
                -moz-border-radius: 10px;
                -webkit-border-radius: 10px;
                border-radius: 10px; /* future proofing */
                -khtml-border-radius: 5px; /* for old Konqueror browsers */
            }
        </style>
        <script type="text/javascript">{
                $(".hidePhotoPopUp").click(function (e) {
                    var id = e.target.id;
                    $("#showPhotos" + id.split("_")[1]).modal('hide');
                });
            }
        </script>







        <div id="postBodyThirdRow">
            <div id="noOfLikesLinknew" style="margin-top: 5px;">
                <a class="postNoofLikesTooltip" href="javascript:void(0)" id='<?php echo 'postNoOfLikes_' . $postId ?>' style="
                   color: #232323;
                   font-family: 'SourceSansProLight';
                   text-decoration: none;"><?php echo $postNoOfLikes . " " . __("people "); ?>
                    <img  style="vertical-align: middle; padding-left: 5px; padding-right: 5px;"src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like-this.png"); ?>" border="0" id='<?php echo 'commentLike_' . $commentId ?>' 
                          height="16" width="16"/><?php echo __(" this"); ?>
                </a>
            </div>
        </div>




    </div>
</div>
<div id="photoPageComment" style=" ;top: 5px;left: 467px;width: 320px;margin-bottom: -20px;position: absolute;background-color: gray">
    <div id="postBodyFirstRow photo" class="photoViewEmp">
        <div id="postFirstRowColumnOne" style="width: 50px;height: 50px;overflow: hidden">
            <img alt="<?php echo __("Employee Photo"); ?>" src="<?php echo url_for("buzz/viewPhoto?empNumber=" . $employeeID); ?>" border="0" id="empPic" />
        </div>
        <div id="postFirstRowColumnTwo" style="width: 60%;">
            <div id="postEmployeeName" >
                <a class="name" href= '<?php echo url_for("buzz/viewProfile?empNumber=" . $employeeID); ?>' >
                    <?php echo $postEmployeeName; ?>
                </a>
            </div>
            <div id="postDateTime">
                <div id="postDate">
                    <?php echo $postDate; ?>
                </div>
                <div id="postTime">
                    <?php echo $postTime; ?>
                </div>
            </div>                        
        </div>
    </div>
    <?php include_component('buzz', 'commentPreview', array('commentList' => $commentList, 'editForm' => $editForm, 'loggedInUser' => $loggedInUser, 'postId' => $postId, 'commentForm' => $commentForm)); ?>

</div>
