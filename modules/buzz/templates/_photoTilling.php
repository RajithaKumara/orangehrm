<?php
$photos = $sf_data->getRaw('originalPost')->getPhotos();
$imgCount = 1;
?>
<?php
if (count($photos) == 1) {
    ?>
    <div class="imageContainer">
        <div class="oneImageOne" style="top: -5px; left: -5px; width: 100%; height: 250px;overflow: hidden">
            <img id="<?php echo $imgCount . "_" . $postId; ?>" class="postPhoto" width="120%" src="data:image/jpeg;base64,<?php echo base64_encode($photos[0]->getPhoto()); ?>"/>
        </div>
    </div>
<?php } else if (count($photos) == 2) {
    ?>
    <div class="imageContainer">
        <div class="twoImageOne" style="top: -5px; left: -5px; width: 50%; height: 300px;overflow: hidden;">
            <img id="<?php echo "1_" . $postId; ?>"  class="postPhoto" height="120%" src="data:image/jpeg;base64,<?php echo base64_encode($photos[0]->getPhoto()); ?>"/>
        </div>
        <div class="twoImageTwo" style="top: -5px; left: 50%; width: 50%; height: 300px ;overflow: hidden;">
            <img id="<?php echo "2_" . $postId; ?>"  class="postPhoto" height="120%" src="data:image/jpeg;base64,<?php echo base64_encode($photos[1]->getPhoto()); ?>"/>
        </div>

    </div>

<?php } else if (count($photos) == 3) {
    ?>
    <div class="imageContainer">
        <div class="threeImageOne" style="top: -5px; left: -5px; width: 60%; height: 300px;overflow: hidden">
            <img id="<?php echo "1_" . $postId; ?>"  class="postPhoto" height="120%" src="data:image/jpeg;base64,<?php echo base64_encode($photos[0]->getPhoto()); ?>"/>
        </div>
        <div class="threeImageTwo" style="top: -5px; left: 60%; width: 40%; height: 115px ;overflow: hidden">
            <img id="<?php echo "2_" . $postId; ?>"  class="postPhoto" width="120%" src="data:image/jpeg;base64,<?php echo base64_encode($photos[1]->getPhoto()); ?>"/>
        </div>
        <div class="threeImageTwo" style="top: 115px; left: 60%; width: 40%; height: 148px ;overflow: hidden">
            <img id="<?php echo "3_" . $postId; ?>"  class="postPhoto" width="120%" src="data:image/jpeg;base64,<?php echo base64_encode($photos[2]->getPhoto()); ?>"/>
        </div>
    </div>
<?php } else if (count($photos) == 4) {
    ?>
    <div class="imageContainer">
        <div class="fourImageOne" style="top: -5px; left: -5px; width: 40%; height: 230px;overflow: hidden">
            <img id="<?php echo "1_" . $postId; ?>"  class="postPhoto" height ="140%" src="data:image/jpeg;base64,<?php echo base64_encode($photos[0]->getPhoto()); ?>"/>
        </div>
        <div class="fourImageTwo"  style="top: -5px; left: 40%; width: 60%; height: 115px ;overflow: hidden">
            <img id="<?php echo "2_" . $postId; ?>"  class="postPhoto" width="120%" src="data:image/jpeg;base64,<?php echo base64_encode($photos[1]->getPhoto()); ?>"/>
        </div>
        <div class="fourImageThree"  style="top: 115px; left: 40%; width: 30%; height: 115px ;overflow: hidden">
            <img id="<?php echo "3_" . $postId; ?>"  class="postPhoto" width="120%" src="data:image/jpeg;base64,<?php echo base64_encode($photos[2]->getPhoto()); ?>"/>
        </div>
        <div class="fourImageFour"  style="top: 115px; left: 70%; width: 30%; height: 115px ;overflow: hidden">
            <img id="<?php echo "4_" . $postId; ?>"  class="postPhoto" width="140%" src="data:image/jpeg;base64,<?php echo base64_encode($photos[3]->getPhoto()); ?>"/>
        </div>
    </div>
<?php } else if (count($photos) == 5) {
    ?>
    <div class="imageContainer">
        <div  style="top: -5px; left: -5px; width: 35%; height: 230px;overflow: hidden">
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
