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

<?php
use_stylesheet(plugin_web_path('orangehrmBuzzPlugin', 'css/photoTiling'));
$originalPost = $sf_data->getRaw('originalPost');
$photos = null;
if ($originalPost) {
    $photos = $originalPost->getPhotos();
}
$imgCount = 1;
?>
<?php
if (count($photos) == 1) {
    ?>
    <div class="imageContainer">
        <div class="oneImageOne">
            <img id="<?php echo $imgCount . "_" . $postId; ?>" class="postPhoto" width="100%"
                 src="<?php echo url_for("buzz/viewPostPhoto?id=" . $photos[0]->getId()); ?>"/>
        </div>
    </div>
<?php } else if (count($photos) == 2) {
    ?>
    <div class="imageContainer">
        <div class="twoImageOne">
            <img id="<?php echo "1_" . $postId; ?>"  class="postPhoto" height="120%" 
                 src="<?php echo url_for("buzz/viewPostPhoto?id=" . $photos[0]->getId()); ?>"/>
        </div>
        <div class="twoImageTwo">
            <img id="<?php echo "2_" . $postId; ?>"  class="postPhoto" height="120%" 
                 src="<?php echo url_for("buzz/viewPostPhoto?id=" . $photos[1]->getId()); ?>"/>
        </div>

    </div>

<?php } else if (count($photos) == 3) {
    ?>
    <div class="imageContainer">
        <div class="threeImageOne">
            <img id="<?php echo "1_" . $postId; ?>"  class="postPhoto" height="120%" 
                 src="<?php echo url_for("buzz/viewPostPhoto?id=" . $photos[0]->getId()); ?>"/>
        </div>
        <div class="threeImageTwo">
            <img id="<?php echo "2_" . $postId; ?>"  class="postPhoto" width="120%" 
                 src="<?php echo url_for("buzz/viewPostPhoto?id=" . $photos[1]->getId()); ?>"/>
        </div>
        <div class="threeImageThree">
            <img id="<?php echo "3_" . $postId; ?>"  class="postPhoto" width="120%" 
                 src="<?php echo url_for("buzz/viewPostPhoto?id=" . $photos[2]->getId()); ?>"/>
        </div>
    </div>
<?php } else if (count($photos) == 4) {
    ?>
    <div class="imageContainer">
        <div class="fourImageOne">
            <img id="<?php echo "1_" . $postId; ?>"  class="postPhoto" height ="140%" 
                 src="<?php echo url_for("buzz/viewPostPhoto?id=" . $photos[0]->getId()); ?>"/>
        </div>
        <div class="fourImageTwo">
            <img id="<?php echo "2_" . $postId; ?>"  class="postPhoto" width="120%" 
                 src="<?php echo url_for("buzz/viewPostPhoto?id=" . $photos[1]->getId()); ?>"/>
        </div>
        <div class="fourImageThree">
            <img id="<?php echo "3_" . $postId; ?>"  class="postPhoto" width="120%" 
                 src="<?php echo url_for("buzz/viewPostPhoto?id=" . $photos[2]->getId()); ?>"/>
        </div>
        <div class="fourImageFour">
            <img id="<?php echo "4_" . $postId; ?>"  class="postPhoto" width="140%" 
                 src="<?php echo url_for("buzz/viewPostPhoto?id=" . $photos[3]->getId()); ?>"/>
        </div>
    </div>
<?php } else if (count($photos) == 5) {
    ?>
    <div class="imageContainer">
        <div class="fiveImageOne">
            <img id="<?php echo "1_" . $postId; ?>"  class="postPhoto" height="140%" 
                 src="<?php echo url_for("buzz/viewPostPhoto?id=" . $photos[0]->getId()); ?>"/>
        </div>
        <div class="fiveImageTwo">
            <img id="<?php echo "2_" . $postId; ?>"  class="postPhoto" height="160%" 
                 src="<?php echo url_for("buzz/viewPostPhoto?id=" . $photos[1]->getId()); ?>"/>
        </div>
        <div class="fiveImageThree">
            <img id="<?php echo "3_" . $postId; ?>"  class="postPhoto" width="120%" 
                 src="<?php echo url_for("buzz/viewPostPhoto?id=" . $photos[2]->getId()); ?>"/>
        </div>
        <div class="fiveImageFour">
            <img id="<?php echo "4_" . $postId; ?>"  class="postPhoto" width="140%" 
                 src="<?php echo url_for("buzz/viewPostPhoto?id=" . $photos[3]->getId()); ?>"/>
        </div>
        <div class="fiveImageFive">
            <img id="<?php echo "5_" . $postId; ?>"  class="postPhoto" width="140%" 
                 src="<?php echo url_for("buzz/viewPostPhoto?id=" . $photos[4]->getId()); ?>"/>
        </div>
    </div>
    <?php
}
