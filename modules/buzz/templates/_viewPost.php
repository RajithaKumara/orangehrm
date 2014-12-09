<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use_stylesheet(plugin_web_path('orangehrmBuzzPlugin', 'css/viewBuzzSuccess_1'));
//use_javascript(plugin_web_path('orangehrmBuzzPlugin', 'js/viewBuzzSuccess_1'));
?>
<style type="text/css">
    .imageNextBtn:hover{
        background-color: white;
        opacity: .5;
    }
    .imagePrevBtn:hover{
        background-color: white;
        opacity: .5;
    }
    .likeLinknew{
        background-color: transparent;
        opacity: 0.8;

        width: 35px;
        margin-left: 0px;
        z-index: 1;
        position: absolute;

    } 
    .unlikeLinknew{
        background-color: transparent;
        opacity: 0.8;

        width: 35px;
        margin-left: 35px;
        z-index: 1;
        position: absolute;

    }
    .shareLinknew{
        background-color: transparent;
        opacity: 0.8;

        width: 35px;
        margin-left: 70px;
        z-index: 1;
        position: absolute;

    }
    #postBodyThirdRowNew{
                width: 110px;
                height: 35px;
                float: right;
                margin-top: -35px;
                margin-bottom: -0px;
            }
     #postBodyViewMore{
                width: 150px;
                height: 35px;
                float: right;
                margin-top: -65px;
                margin-bottom: -0px;
                margin-right: -60px;
     }
    
    .textTopOfImage{
        color: white;
        font-size: 20px;
        margin-left: 23px;
        margin-top: -27px;
        z-index: 9998;
        position: absolute;

    }
    #noOfLikesLinknew{
        margin-left: 1%;
        font-size: 14px;
    }
    #noOfUnLikesLinknew{
        margin-left: 170px;
        font-size: 14px;
    }
    #noOfSharesLinknew{
        margin-left:130px;
        font-size: 14px;
    }

    .likeCommentnew{
        background-color: transparent;
        opacity: 0.8;

        width: 30px;
        margin-left: 0px;
        z-index: 999;
        position: absolute;

    } 
    .unlikeCommentnew{
        background-color: transparent;
        opacity: 0.8;

        width: 30px;
        margin-left: 40px;
        z-index: 999;
        position: absolute;

    }

    #commentBodyThirdRowNew{
        width: 70px;
        height: 30px;
        
        margin-top: -25px;
        margin-bottom: -0px;
        float:right;
    }
    .textTopOfImageComment{
        color: white;
        font-size: 15px;
        margin-left: 17px;
        margin-top: -21px;
        z-index: 9999;
        position: absolute;
        border-radius: 100px;
        background-color: black;
        padding-left: 2px;
        padding-right: 2px;

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

</style>
<li id=<?php echo "post" . $postId; ?>>

    <div id="postBody">

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
                <div id="postEmloyeeJobTitle">
                    <?php echo $postEmployeeJobTitle; ?>
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

            <div id="postFirstRowColumnThree">
                <?php if (($employeeID == $loggedInUser) || ($loggedInUser == '')) { ?>
                    <div id="postOptionWidget">
                        <div class="dropdown">
                            <a class="account" id=<?php echo $postId ?>></a>
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
        <div class="viewMorveShare"  id="postBodyViewMore">
            
                    <img  class="viewMoreShare" src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/icons/readmore-icon.png"); ?>" border="0" id='<?php echo 'shareViewMore_' . $postId ?>'
                          style="z-index: 9999"     height="30" width="30"/>
        </div>
        <!-- pop up-->
        <div class="modal hide" style="width: 800px;height: 700px;left: 40%;top:50%;overflow-x: hidden" id='<?php echo 'shareViewMoreMod_' . $postId ?>'>



                        <div class="modal-body" style="height: 530px;background-color: gray;overflow-x: hidden;overflow-y: auto">
                            <div class="hideModalPopUp" id='<?php echo 'shareViewMoreMod_' . $postId ?>'
                                 style="top: 2px;right: 2px;position: absolute;z-index: 99999;border: 2px solid;border-radius: 250px;background-color: white"><img 
                                    class="hideModalPopUp" id='<?php echo 'shareViewMoreMod_' . $postId ?>' 
                                    src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/close.png"); ?>" height="20" width="20"
                                     /></div>
                
                            <div class="shareView" id='<?php echo 'shareViewContent_' . $postId ?>'>
                            </div>
                        </div>
                    </div>
        <!--new Code of like, unlike and share buttons-->
        <div id="postBodyThirdRowNew">
            <div class="likeLinknew"  id="<?php echo 'postLikebody_' . $postId ?>" > 
                <?php
            if ($isLike == 'Unlike') {?>
                <a href="javascript:void(0)" class="<?php echo $isLike . ' postLike'; ?>" id='<?php echo 'postLikeyes_' . $postId ?>'> 
                    <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/icons.png"); ?>" border="0" id='<?php echo 'postLike_' . $postId ?>'
                                  class="<?php echo $isLike . ' postLike'; ?>" height="30" width="30"/></a>
                <a hidden="true" href="javascript:void(0)" class="<?php echo $isLike . ' postLike'; ?>" id='<?php echo 'postLikeno_' . $postId ?>'> 
                    <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/like.png"); ?>" border="0" id='<?php echo 'postLike_' . $postId ?>'
                                  class="<?php echo $isLike . ' postLike'; ?>" height="30" width="30"/></a>
            <?php 
            } else{
            ?>
                <a hidden="true" href="javascript:void(0)" class="<?php echo $isLike . ' postLike'; ?>" id='<?php echo 'postLikeyes_' . $postId ?>'> 
                    <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/icons.png"); ?>" border="0" id='<?php echo 'postLike_' . $postId ?>'
                                  class="<?php echo $isLike . ' postLike'; ?>" height="30" width="30"/></a>
                <a href="javascript:void(0)" class="<?php echo $isLike . ' postLike'; ?>" id='<?php echo 'postLikeno_' . $postId ?>'> 
                    <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/like.png"); ?>" border="0" id='<?php echo 'postLike_' . $postId ?>'
                                  class="<?php echo $isLike . ' postLike'; ?>" height="30" width="30"/></a>
            <?php
            }
            ?>
                
                <!--<div class="textTopOfImage" id='<?php echo 'postLiketext_' . $postId ?>'><?php echo $postNoOfLikes ?></div>-->
            </div>
            <div class="unlikeLinknew" id='<?php echo 'postUnLikebody_' . $postId ?>' >
                <?php
            if ($isUnlike == 'yes') {?>
                 <a hidden="true" href="javascript:void(0)" class="postUnlike2" id=<?php echo 'postUnlikeno_' . $postId ?>> 
                    <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/unlike.png"); ?>" 
                          border="0" id='<?php echo 'postLike_' . $postId ?>'  height="30" width="30"/></a>
                <a  href="javascript:void(0)" class="postUnlike2" id=<?php echo 'postUnlikeyes_' . $postId ?>> 
                    <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/unlike2.png"); ?>" 
                          border="0" id='<?php echo 'postLike_' . $postId ?>'  height="30" width="30"/></a>
            <?php 
            } else{?>
                <a href="javascript:void(0)" class="postUnlike2" id=<?php echo 'postUnlikeno_' . $postId ?>> 
                    <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/unlike.png"); ?>" 
                          border="0" id='<?php echo 'postLike_' . $postId ?>'  height="30" width="30"/></a>
                <a  hidden="true" href="javascript:void(0)" class="postUnlike2" id=<?php echo 'postUnlikeyes_' . $postId ?>> 
                    <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/unlike2.png"); ?>" 
                          border="0" id='<?php echo 'postLike_' . $postId ?>'  height="30" width="30"/></a>
            <?php
            }
            ?>
                
               
                <!--<div class="textTopOfImage" id='<?php echo 'postUnLiketext_' . $postId ?>'><?php echo $postUnlike ?></div>-->
            </div>

            <div class="shareLinknew" id='<?php echo 'postSharebody_' . $postId ?>' >
                <?php
            if ($shareCount > 0) { ?>
                <a href="javascript:void(0)" class="postShare" id=<?php echo 'postShareyes_' . $postId ?>> 
                    <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/share2.png"); ?>" 
                          border="0" id='<?php echo 'postLike_' . $postId ?>'height="30" width="30"/></a>
                <a hidden="true" href="javascript:void(0)" class="postShare" id=<?php echo 'postShareno_' . $postId ?>> 
                    <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/share.png"); ?>" 
                          border="0" id='<?php echo 'postLike_' . $postId ?>'height="30" width="30"/></a>
                
            <?php } else{?>
                <a hidden="true" href="javascript:void(0)" class="postShare" id=<?php echo 'postShareyes_' . $postId ?>> 
                    <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/share2.png"); ?>" 
                          border="0" id='<?php echo 'postLike_' . $postId ?>'height="30" width="30"/></a>
                <a href="javascript:void(0)" class="postShare" id=<?php echo 'postShareno_' . $postId ?>> 
                    <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/share.png"); ?>" 
                          border="0" id='<?php echo 'postLike_' . $postId ?>'height="30" width="30"/></a>
            <?php
            
            }
            ?>
                
                <!--<div class="textTopOfImage"><?php echo $shareCount ?></div>-->
            </div>

        </div>

        <div hidden="true"id="postBodyThirdRowNew">
            <a style="color: #F07C00; margin-right: 10px;" href="javascript:void(0)" class="<?php echo $isLike . ' postLike'; ?>" id='<?php echo 'postLike_' . $postId ?>'>Like</a>
            <a style="color: #F07C00; margin-right: 10px;" href="javascript:void(0)" class="postUnlike2" id=<?php echo 'postUnlike_' . $postId ?>>Unlike</a>
            <a style="color: #F07C00; margin-right: 10px;" href="javascript:void(0)" class="postShare" id=<?php echo 'postShare_' . $postId ?>>Share</a>
        </div>

        <div id="postBodySecondRow" >
            <div id='<?php echo 'postContent_' . $postId ?>'>
                <?php echo BuzzTextParserService::parseText($postContent); 
                ?>
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

                        <div id="postBodySecondRow">
                            <div id="postContent">
                                <?php echo BuzzTextParserService::parseText($originalPostContent); 
                               
                                ?>
                            </div>
                        </div>
                    </div>
                    <!--SUB POST END-->
                    <div class="modal hide" style="width: 800px;height: 700px;left: 40%;top:50%;overflow-x: hidden" id='<?php echo 'postViewOriginal_' . $postId ?>'>



                        <div class="modal-body" style="height: 530px;background-color: gray;overflow-x: hidden;overflow-y: auto">
                            <div class="hideModalPopUp" id='<?php echo 'postViewOriginal_' . $postId ?>'
                                 style="top: 2px;right: 2px;position: absolute;z-index: 99999;border: 2px solid;border-radius: 250px;background-color: white"><img 
                                    class="hideModalPopUp" id='<?php echo 'postViewOriginal_' . $postId ?>' 
                                    src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/close.png"); ?>" height="20" width="20"
                                     /></div>
                
                            <div class="postView" id='<?php echo 'postViewContent_' . $postId ?>'>
                            </div>
                        </div>
                    </div>
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
         <?php include_component('buzz', 'photoTilling', array('photos' => $photos,'originalPost'=>$originalPost,'postId'=>$postId)); ?>

        
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

        <div class="modal hide" style="width: 800px;height: 600px;left: 40%;top: 60%" id='<?php echo "showPhotos" . $postId; ?>'>
            <div class="modal-body" style="height: 530px;background-color: gray" id="modalnewlook">

                <div class="hideModalPopUp" id='<?php echo "showPhotos" . $postId; ?>'
                                 style="top: 2px;right: 2px;position: absolute;z-index: 99999;border: 2px solid;border-radius: 250px;background-color: white"><img 
                                    class="hideModalPopUp" id='<?php echo "showPhotos" . $postId; ?>' 
                                    src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/close.png"); ?>" height="20" width="20"
                                     /></div>
                <div id="photoPage" style="height: 400px;top: 15px;left: 12px;width: 450px;margin-bottom: -20px;position: absolute">
                    <?php
                    $imgCount = 1;
                    foreach ($photos as $photo) {
                        ?>


                        <img class="postPhotoPrev" hidden="true" id="<?php echo "img_" . $imgCount . "_" . $postId; ?>" 
                             style="<?php
                             if ($photo->getHeight() / $photo->getWidth() > (400 / 450)) {
                                 echo 'height:99%';
                             } else {
                                 echo 'width:99%';
                             }
                             ?>;position:absolute;top:0;bottom:0;right: 0;left: 0;margin:auto;" src="data:image/jpeg;base64,<?php echo base64_encode($photo->getPhoto()); ?>"/>

                        <?php
                        $imgCount++;
                    }
                    ?>
                    <button class="imageNextBtn" disabled="true" id="imageNextBtn<?php echo $postId; ?>">
                        <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/next.png"); ?>" border="0" height="100%" /></button>
                    <button class="imagePrevBtn" disabled="true" id="imagePrevBtn<?php echo $postId; ?>">
                        <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/prev.png"); ?>" border="0" height="100%" /></button>
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
            </div>

        </div>

        <!-- start share post popup window-->
        <div class="modal hide" style="width: 550px;height: 600px;left: 50%;top: 60%"  id='<?php echo 'posthide_' . $postId ?>'>
            
            <div class="modal-body" style="height: 530px;background-color: gray">
                <div class="hideModalPopUp" id='<?php echo 'posthide_' . $postId ?>' style="top: 2px;right: 2px;position: absolute;z-index: 99999;border: 2px solid;border-radius: 250px;background-color: white">x</div>
                <div style="height: 380px;top: 15px;left: 12px;width: 500px;margin-bottom: -20px;position: absolute;background-color: white;border-radius: 10px;overflow-y: auto;padding: 10px; ">
                    <form id="frmCreateComment" method="" action="" style="margin-top: 10px;"
                      enctype="multipart/form-data">
                          <?php
                          $placeholder = 'Whats on your mind';
                          echo $commentForm['comment']->render(array('id' => "shareBox_" . $postId,
                              'class' => 'commentBox', 'style' => 'width: 95%', 'rows' => '2', 'placeholder' => $placeholder));
                          ?>

                </form>
                <div id="sharedPostBody">

                    <div id="postBodyFirstRow">
                        <div id="postFirstRowColumnOne">
                            <img alt="<?php echo __("Employee Photo"); ?>" src="<?php echo url_for("buzz/viewPhoto?empNumber=" . $originalPostEmpNumber); ?>" border="0" id="empPic" height="40" width="30"/>
                        </div>
                        <div id="postFirstRowColumnTwo">
                            <div id="postEmployeeName" >
                                <a class="name" href="javascript:void(0);">
                                    <?php echo $originalPostSharerName; ?>
                                </a>
                            </div>
                            <div id="postDateTime">
                                <div id="postDate">
                                    <?php echo $originalPostDate; ?>
                                </div>
                                <div id="postTime">
                                    <?php echo $postTime; ?>
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
                <?php if (count($originalPost->getLinks()) > 0) { ?>
            <?php foreach ($originalPost->getLinks() as $link) { ?>
                <?php if ($link->getType() == 1) { ?>
                    <div style="height: 150px;width: 60%;margin: 0 auto;overflow: hidden">
                    <iframe src="<?php echo $link->getLink(); ?>" width="100%" height="150" style="margin-top: 5px;margin: 0 auto; " frameborder="0" allowfullscreen></iframe >
                    </div>
                <?php } ?>
                <?php if ($link->getType() == 0) { ?>
                    <div id="postBodySecondRow">
                        <div id="postContent" >
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
        if(count($photos)>0){
        ?>
                    <div style="height: 160px;width: 60%;margin: 0 auto;overflow: hidden">
         <?php include_component('buzz', 'photoTilling', array('photos' => $photos,'originalPost'=>$originalPost,'postId'=>$postId)); ?>
</div>
        <?php }?>
                    <input type="button" class="btnShare" name="btnSaveDependent" style="float: right;margin-top: 20px;" id='<?php echo 'btnShare_' . $postId . "_" . $originalPostId ?>' value="<?php echo __("Share"); ?>"/>

                </div>
            </div>
        </div>
        <!-- end share post pop up window-->
        <!-- start edit post popup window-->
        <div class="modal hide" style="width: 800px;height: 700px;left: 40%;top:50%;overflow-x: hidden" id='<?php echo 'editposthide_' . $postId ?>'>
            
            <div class="modal-body" style="background-color: gray;overflow-x: hidden;overflow-y: auto">
                <div class="hideModalPopUp" id='<?php echo 'editposthide_' . $postId ?>' style="top: 2px;right: 2px;position: absolute;z-index: 99999;border: 2px solid;border-radius: 250px;background-color: white">x</div>
                
                <div id="postBodySecondRow" style="border-radius: 5px;padding: 20px;">
                    
                    <h3><?php echo __('Edit your post'); ?></h3>
                    
                    <?php
                    if ($postType == '1') {
                        ?>
                        <form id="frmCreateComment" method="" action="" 
                              enctype="multipart/form-data">
                                  <?php
                                  $editForm->setDefault('comment', $postContent);
                                  echo $editForm['comment']->render(array('id' => "editshareBox_" . $postId,
                                      'class' => 'commentBox', 'style' => 'width: 95%', 'rows' => '2'));
                                  ?>

                        </form>
                    <?php } else { ?>
                        <form id="frmCreateComment" method="" action="" 
                              enctype="multipart/form-data">
                                  <?php
                                  $editForm->setDefault('comment', $originalPostContent);
                                  echo $editForm['comment']->render(array('id' => "editshareBox_" . $postId,
                                      'class' => 'commentBox', 'style' => 'width: 95%', 'rows' => '2'));
                                  ?>

                        </form>

                    <?php } ?>
                    <div style="padding: 20px;background-color: white;margin-bottom: 20px;">
                    <input type="button" style="float: right;" class="btnEditShare" name="btnSaveDependent" id='<?php echo 'btnEditShare_' . $postId ?>' value="<?php echo __("Save"); ?>"/>
                    </div>
                </div>
            </div>
        </div>
        <!-- end edit post pop up window-->
        <!-- start like window popup window-->
        <div class="modal hide" id='<?php echo 'postlikehide_' . $postId ?>'>
            
            <div class="modal-body" style="height: 530px;background-color: gray;overflow-x: hidden;overflow-y: auto">
                 <div class="hideModalPopUp" id='<?php echo 'postlikehide_' . $postId ?>'
                                 style="top: 2px;right: 2px;position: absolute;z-index: 99999;border: 2px solid;border-radius: 250px;background-color: white"><img 
                                    class="hideModalPopUp" id='<?php echo 'postlikehide_' . $postId ?>' 
                                    src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/close.png"); ?>" height="20" width="20"
                                     /></div>
                <div class=""  id='<?php echo 'postlikehidebody_' . $postId ?>'></div>




            </div>
        </div>
        <!-- end like window pop up window-->


        <div id="postBodyThirdRow">
            <div id="noOfLikesLinknew" style="margin-top: 5px;">
                <a class="postNoofLikesTooltip" href="javascript:void(0)" id='<?php echo 'postNoOfLikes_' . $postId ?>' style="
                   color: #232323;
                   font-family: 'SourceSansProLight';
                   text-decoration: none;">
                    <span id="<?php echo 'noOfLikes_' . $postId;?>"><?php echo $postNoOfLikes; ?></span><?php echo " " . __("people "); ?>
                    <img  style="vertical-align: middle; padding-left: 5px; padding-right: 5px;"src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like-this.png"); ?>" border="0" id='<?php echo 'commentLike_' . $postId ?>' 
                          height="16" width="16"/><?php echo __(" this"); ?>
                </a>
            </div>
            <div id="noOfSharesLinknew" style="margin-top: 5px;">
                <a class="postNoofSharesTooltip" href="javascript:void(0)" id='<?php echo 'postNoOfShares_' . $postId ?>' style="
                   color: #232323;
                   font-family: 'SourceSansProLight';
                   text-decoration: none;">
                    <span id="<?php echo 'noOfShares_' . $postId;?>"><?php echo $postShareCount; ?></span><?php echo " " . __("people "); ?>
                    <img  style="vertical-align: middle; padding-left: 5px; padding-right: 5px;"src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/share2.png"); ?>" border="0"  
                          height="16" width="16"/><?php echo __(" this"); ?>
                </a>
            </div>
            <div id="noOfUnLikesLinknew" style="margin-top: 5px;">
                <a class="postNoofLikesTooltip" href="javascript:void(0)" id='<?php echo 'postNoOfLikes_' . $postId ?>' style="
                   color: #232323;
                   font-family: 'SourceSansProLight';
                   text-decoration: none;">
                    <span id="<?php echo 'noOfUnLikes_' . $postId;?>"><?php echo $postUnlike; ?></span><?php echo " " . __("people "); ?>
                    <img  style="vertical-align: middle; padding-left: 5px; padding-right: 5px;"src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/unlike2.png"); ?>" border="0" id='<?php echo 'commentLike_' . $commentId ?>' 
                          height="16" width="16"/><?php echo __(" this"); ?>
                </a>
            </div>
        </div>

        <div id="postBodyFourthRow">
            <a href="javascript:void(0)" class="postViewMoreCommentsLink" id=<?php echo 'postViewMoreCommentsLink_' . $postId ?>>
                <?php
                $commentCount = $post->getComment()->count();
                if ($commentCount > $initialcommentCount) {
                    echo __("View") . " " . ($commentCount - $initialcommentCount) . " ";
                    echo __("more comments");
                    echo __(" out of" . " " . $commentCount . " " . "comments");
                }
                ?>
            </a>
        </div>

        <div id="postFifthRow" class="postRow">
            <div class="postCommentBox" id=<?php echo "postCommentTextBox_" . $postId; ?>>
                <form class="<?php echo $postId; ?>" id="frmCreateComment" method="" action="" 
                      enctype="multipart/form-data">
                          <?php
                          $placeholderd = __("Add your comment");
                          echo $commentForm['comment']->render(array('id' => "commentBoxNew_" . $postId,
                              'class' => 'commentBox', 'rows' => '1', 'style' => 'font-size: 16px; font-family: "SourceSansProLight"; border-radius: 5px 5px 5px 5px; min-width: 99.5%; padding: 10px 0 10px 10px;', 'placeholder' => $placeholderd));
                          ?>
                    <button id="postSubmitBtn" class="commentSubmitBtn submitBtn">Comment</button>
                </form>
            </div>
        </div>

    </div>

    <div id="commentListContainer">
        <ul class="commentList" id='<?php echo 'commentListNew_' . $postId ?>'>
            <?php
            $count = 0;
            $display = 'block';
            foreach ($commentList as $comment) {
                $commentId = $comment->getId();
                $commentEmployeeJobTitle = $comment->getEmployeeComment()->getJobTitleName();
                $commentPostId = $comment->getShareId();
                $commentContent = $comment->getCommentText();
                $commentEmployeeName = $comment->getEmployeeFirstLastName();
                $commentEmployeeId = $comment->getEmployeeNumber();
                $commentNoOfLikes = $comment->getNumberOfLikes();
                $commentNoOfUnLikes = $comment->getNumberOfUnlikes();
                 $isUnlikeComment = 'no';
                if ($comment->isUnLike($loggedInUser)=='yes') {
                    $isUnlikeComment = 'yes';
                }
                $commentDate = $comment->getDate();
                $commentTime = $comment->getTime();
                $isLikeComment = $comment->isLike($loggedInUser);
                $commentLikeEmployes = $comment->getLikedEmployeeList();
                $peopleLikeArray = $comment->getLikedEmployees();
//                            $peopleLikeArray = array("Aruna Tebel", "Dewmal Anicitus");
                if ($count >= $initialcommentCount) {
                    $display = 'none';
                }
                $count++;
                ?>

                <!-- start edit comment popup window-->
                <div class="modal hide" style="width: 800px;height: 700px;left: 40%;top:50%;overflow-x: hidden" id='<?php echo 'editcommenthideNew2_' . $commentId ?>'>
                    
                    <div class="modal-body" style="background-color: gray;overflow-x: hidden;overflow-y: auto">
                        <div class="hideModalPopUp" id='<?php echo 'editcommenthideNew2_' . $commentId ?>' style="top: 2px;right: 2px;position: absolute;z-index: 99999;border: 2px solid;border-radius: 250px;background-color: white">x</div>
                
                        
                        <div id="postBodySecondRow" style="border-radius: 5px;padding: 20px;">
                            <h3><?php echo __('Edit your comment'); ?></h3>
                            <form id="frmCreateComment" method="" action="" 
                                  enctype="multipart/form-data">
                                      <?php
                                      $editForm->setDefault('comment', $commentContent);
                                      echo $editForm['comment']->render(array('id' => "editcommentBoxNew2_" . $commentId,
                                          'class' => 'commentBox', 'style' => 'width: 95%', 'rows' => '2'));
                                      ?>

                            </form>

                            <div style="padding: 20px;background-color: white;margin-bottom: 20px;">
                                <input type="button" style="float: right" class="btnEditCommentNew" name="btnSaveDependent" id='<?php echo 'btnEditComment_' . $commentId ?>' value="<?php echo __("Save"); ?>"/>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end edit comment pop up window-->
                <!-- start like window popup window-->
                <div class="modal hide" id='<?php echo 'commentlikehide_' . $commentId ?>'>
                    <div class="modal-header" >
                        <a class="close" data-dismiss="modal">×</a>
                        <h3><?php echo __('People who like this post'); ?></h3>
                    </div>
                    <div class="modal-body" style="height: 500px;overflow-y: auto;overflow-x: hidden;width: 380px;border-bottom-left-radius:   5px;border-bottom-right-radius:   5px;">
                        <div class="" id='<?php echo 'commentlikehidebody_' . $commentId ?>'></div>


                    </div>
                </div>
                <!-- end like window pop up window-->
                <li id="<?php echo "commentNew_" . $commentId; ?>" style="display: <?php echo $display; ?>" class="<?php echo $commentPostId; ?>" >
                    <div id="commentBody">
                        <div id="commentRowOne">
                            <div id="commentColumnOne">
                                <img alt="<?php echo __("Employee Photo"); ?>" src="<?php echo url_for("buzz/viewPhoto?empNumber=" . $commentEmployeeId); ?>" border="0" id="empPic"/>
                            </div>
                            <div id="commentColumnTwo">
                                <div id="commentEmployeeName">
                                    <a class="name" href="javascript:void(0);"><?php echo $commentEmployeeName; ?></a>
                                </div>
                                <div id="commentEmployeeJobTitle">
                                    <?php echo $commentEmployeeJobTitle; ?>
                                </div>
                                <div id="commentColumnTwoRowThree">
                                    <div id="commentDate">
                                        <?php echo $commentDate; ?>
                                    </div>
                                    <div id="commentTime">
                                        <?php echo $commentTime; ?>
                                    </div>
                                </div>
                            </div>
                            <div id="commentColumnThree">
                                <?php if (($commentEmployeeId == $loggedInUser) || ($loggedInUser == '')) { ?>
                                    <div id="commentOptionWidget">
                                        <div class="dropdown">
                                            <a class="commentAccount" id=<?php echo 'cnew' . $commentId ?>></a>
                                            <div class="submenu" id=<?php echo 'submenucnew' . $commentId ?>>
                                                <ul class = "root">
                                                    <li ><a href = "javascript:void(0)" class="editComment" id=<?php echo 'editComment_' . $commentId ?> ><?php echo __("Edit"); ?></a></li>
                                                    <li ><a href = "javascript:void(0)" class="deleteComment" id=<?php echo 'deleteComment_' . $commentId ?>><?php echo __("Delete"); ?></a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <div  id="commentBodyThirdRowNew">
                                <div class="likeCommentnew"  id="<?php echo 'commentLikebody_' . $commentId ?>" style="background-color: ">
                                    <?php if ($isLikeComment == 'Unlike') {?>
                                    <a hidden="true" href="javascript:void(0)" class="<?php echo $isLikeComment . ' commentLike'; ?>" id='<?php echo 'commentLikeno_' . $commentId ?>'> 
                                            <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/like.png"); ?>" border="0" id='<?php echo 'commentLike_' . $commentId ?>'
                                                    class="<?php echo $isLikeComment . ' commentLike'; ?>" height="20" width="20"/></a>
                                        <a href="javascript:void(0)" class="<?php echo $isLikeComment . ' commentLike'; ?>" id='<?php echo 'commentLikeyes_' . $commentId ?>'> 
                                            <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/icons.png"); ?>" border="0" id='<?php echo 'commentLike_' . $commentId ?>'
                                                    class="<?php echo $isLikeComment . ' commentLike'; ?>" height="20" width="20"/></a>
                                    <?php } else {?>
                                    <a href="javascript:void(0)" class="<?php echo $isLikeComment . ' commentLike'; ?>" id='<?php echo 'commentLikeno_' . $commentId ?>'> 
                                            <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/like.png"); ?>" border="0" id='<?php echo 'commentLike_' . $commentId ?>'
                                                    class="<?php echo $isLikeComment . ' commentLike'; ?>" height="22" width="22"/></a>
                                        <a hidden="true" href="javascript:void(0)" class="<?php echo $isLikeComment . ' commentLike'; ?>" id='<?php echo 'commentLikeyes_' . $commentId ?>'> 
                                            <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/icons.png"); ?>" border="0" id='<?php echo 'commentLike_' . $commentId ?>'
                                                    class="<?php echo $isLikeComment . ' commentLike'; ?>" height="22" width="22"/></a>
                                    <?php } ?>
                                    
                                    <div class="textTopOfImageComment" id='<?php echo 'commentNoOfLiketext_' . $commentId ?>'><?php echo $commentNoOfLikes ?></div>
                                </div>
                                <div class="unlikeCommentnew" id='<?php echo 'commentUnLikebody_' . $commentId ?>' >
                                    <?php if ($isUnlikeComment == 'yes') {?>
                                    <a hidden="true" href="javascript:void(0)" class="commentUnlike2" id=<?php echo 'commentUnLikeno_' . $commentId ?>>
                                        <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/unlike.png"); ?>" border="0" id='<?php echo 'commentLike_' . $commentId ?>' height="20" width="20"/></a>
                                    <a  href="javascript:void(0)" class="commentUnlike2" id=<?php echo 'commentUnLikeyes_' . $commentId ?>>
                                        <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/unlike2.png"); ?>" border="0" id='<?php echo 'commentLike_' . $commentId ?>' height="20" width="20"/></a>
                                    <?php } else {?>
                                    <a  href="javascript:void(0)" class="commentUnlike2" id=<?php echo 'commentUnLikeno_' . $commentId ?>>
                                        <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/unlike.png"); ?>" border="0" id='<?php echo 'commentLike_' . $commentId ?>' height="22" width="22"/></a>
                                    <a hidden="true" href="javascript:void(0)" class="commentUnlike2" id=<?php echo 'commentUnLikeyes_' . $commentId ?>>
                                        <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/unlike2.png"); ?>" border="0" id='<?php echo 'commentLike_' . $commentId ?>' height="22" width="22"/></a>
                                    <?php } ?>
                                    
                                                                                                                                              
                                    <div class="textTopOfImageComment" id='<?php echo 'commentNoOfUnLiketext_' . $commentId ?>'><?php echo $commentNoOfUnLikes ?></div>
                                </div>
                            </div>
                        <div id="commentRowTwo">
                            <div class="commentContent"id='<?php echo 'commentContentNew_' . $commentId ?>'>
                                <?php echo BuzzTextParserService::parseText($commentContent); ?>
                            </div>
                        </div>

                        <div  id="commentColumnTwo">
                            
                        </div>
                    </div>
                </li>
            <?php } ?>
        </ul>
        <div class="commentLoadingBox"  id='<?php echo 'commentLoadingBox'.$postId;?>' style="display: none">
            <div id="commentBody">

                <img id="img-spinner"   src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/loading2.gif"); ?>" height="20" style="margin-left: 40%; margin-top: 15px" />
            </div>
        </div>

    </div>
    <div class="lastLoadedPost" id=<?php echo $postId; ?>></div>
</li>
