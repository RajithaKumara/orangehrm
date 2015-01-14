<?php
use_stylesheet(plugin_web_path('orangehrmBuzzPlugin', 'css/photoTiling'));
$photos = $sf_data->getRaw('originalPost')->getPhotos();
$imgCount = 1;
?>
<?php
if (count($photos) == 1) {
    ?>
    <div class="imageContainer">
        <div class="oneImageOne">
            <img id="<?php echo $imgCount . "_" . $postId; ?>" class="postPhoto" 
                 src="data:image/jpeg;base64,<?php echo base64_encode($photos[0]->getPhoto()); ?>"/>
        </div>
    </div>
<?php } else if (count($photos) == 2) {
    ?>
    <div class="imageContainer">
        <div class="twoImageOne">
            <img id="<?php echo "1_" . $postId; ?>"  class="postPhoto" height="120%" 
                 src="data:image/jpeg;base64,<?php echo base64_encode($photos[0]->getPhoto()); ?>"/>
        </div>
        <div class="twoImageTwo">
            <img id="<?php echo "2_" . $postId; ?>"  class="postPhoto" height="120%" 
                 src="data:image/jpeg;base64,<?php echo base64_encode($photos[1]->getPhoto()); ?>"/>
        </div>

    </div>

<?php } else if (count($photos) == 3) {
    ?>
    <div class="imageContainer">
        <div class="threeImageOne">
            <img id="<?php echo "1_" . $postId; ?>"  class="postPhoto" height="120%" 
                 src="data:image/jpeg;base64,<?php echo base64_encode($photos[0]->getPhoto()); ?>"/>
        </div>
        <div class="threeImageTwo">
            <img id="<?php echo "2_" . $postId; ?>"  class="postPhoto" width="120%" 
                 src="data:image/jpeg;base64,<?php echo base64_encode($photos[1]->getPhoto()); ?>"/>
        </div>
        <div class="threeImageThree">
            <img id="<?php echo "3_" . $postId; ?>"  class="postPhoto" width="120%" 
                 src="data:image/jpeg;base64,<?php echo base64_encode($photos[2]->getPhoto()); ?>"/>
        </div>
    </div>
<?php } else if (count($photos) == 4) {
    ?>
    <div class="imageContainer">
        <div class="fourImageOne">
            <img id="<?php echo "1_" . $postId; ?>"  class="postPhoto" height ="140%" 
                 src="data:image/jpeg;base64,<?php echo base64_encode($photos[0]->getPhoto()); ?>"/>
        </div>
        <div class="fourImageTwo">
            <img id="<?php echo "2_" . $postId; ?>"  class="postPhoto" width="120%" 
                 src="data:image/jpeg;base64,<?php echo base64_encode($photos[1]->getPhoto()); ?>"/>
        </div>
        <div class="fourImageThree">
            <img id="<?php echo "3_" . $postId; ?>"  class="postPhoto" width="120%" 
                 src="data:image/jpeg;base64,<?php echo base64_encode($photos[2]->getPhoto()); ?>"/>
        </div>
        <div class="fourImageFour">
            <img id="<?php echo "4_" . $postId; ?>"  class="postPhoto" width="140%" 
                 src="data:image/jpeg;base64,<?php echo base64_encode($photos[3]->getPhoto()); ?>"/>
        </div>
    </div>
<?php } else if (count($photos) == 5) {
    ?>
    <div class="imageContainer">
        <div class="fiveImageOne">
            <img id="<?php echo "1_" . $postId; ?>"  class="postPhoto" height="140%" 
                 src="data:image/jpeg;base64,<?php echo base64_encode($photos[0]->getPhoto()); ?>"/>
        </div>
        <div class="fiveImageTwo">
            <img id="<?php echo "2_" . $postId; ?>"  class="postPhoto" height="160%" 
                 src="data:image/jpeg;base64,<?php echo base64_encode($photos[1]->getPhoto()); ?>"/>
        </div>
        <div class="fiveImageThree">
            <img id="<?php echo "3_" . $postId; ?>"  class="postPhoto" width="120%" 
                 src="data:image/jpeg;base64,<?php echo base64_encode($photos[2]->getPhoto()); ?>"/>
        </div>
        <div class="fiveImageFour">
            <img id="<?php echo "4_" . $postId; ?>"  class="postPhoto" width="140%" 
                 src="data:image/jpeg;base64,<?php echo base64_encode($photos[3]->getPhoto()); ?>"/>
        </div>
        <div class="fiveImageFive">
            <img id="<?php echo "5_" . $postId; ?>"  class="postPhoto" width="140%" 
                 src="data:image/jpeg;base64,<?php echo base64_encode($photos[4]->getPhoto()); ?>"/>
        </div>
    </div>
    <?php
}
