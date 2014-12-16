<?php use_stylesheet(plugin_web_path('orangehrmBuzzPlugin', 'css/viewBuzzSuccess'));
use_javascript(plugin_web_path('orangehrmBuzzPlugin', 'js/viewBuzzSuccess'));
?>


 
<?php echo stylesheet_tag(plugin_web_path('orangehrmDashboardPlugin', 'css/orangehrmDashboardPlugin.css')); ?>
<style type="text/css">
    .loadmask {
        top:0;
        left:0;
        -moz-opacity: 0.5;
        opacity: .50;
        filter: alpha(opacity=50);
        background-color: #CCC;
        width: 100%;
        height: 100%;
        zoom: 1;
        background: #fbfbfb url("<?php echo plugin_web_path('orangehrmDashboardPlugin', 'images/loading.gif') ?>") no-repeat center;
    }
</style>
 <div id="2"style=" width:50%;height:550px;margin-top: -300px;margin-left: 100%; z-index: 999; position : fixed">
     <iframe src='<?php echo url_for('buzz/viewBuzz'); ?>' width="100%" height="500px" scrolling="yes">
</iframe>  
        </div>

<div class="box" id="bx3">
    <!--    <div class="top">
            <div class="left"></div>
            <div class="right"></div>
            <div class="middle"></div>
        </div>-->
    
    <div class="head">
        <h1><?php echo __('Dashboard'); ?></h1>
    </div>
    
    <div class="inner">
        
       
        <?php if (count($settings) == 0): ?>
            <div id="messagebar" style="margin-left: 16px;width: 700px;">
                <span style="font-weight: bold;">No Groups are Assigned</span>
            </div>
        <?php endif; ?>
        <?php
        foreach ($settings->getRawValue() as $groupKey => $config):
            ?>
            <div class="outerbox <?php echo ($config['type'] == DashboardPanelGroup::$TYPE_MISC) ? "no-border" : "" ?>" style="<?php echo isset($config['attributes']['width']) ? "width:" . ($config['attributes']['width'] + 4) . "px;" : "width:auto" ?>">
                <!--<div class="top"><div class="left"></div><div class="right"></div><div class="middle"></div></div>-->
                <div id="<?php echo "group_" . $groupKey ?>" class="maincontent group-wrapper">
                    <?php
                    if ($config['type'] == DashboardPanelGroup::$TYPE_STD):
                        ?>
                        <div class="mainHeading">
                            <h2 class="paddingLeft"><?php echo isset($config['attributes']['title']) ? $config['attributes']['title'] : "" ?></h2>
                        </div>
                        <?php
                    endif;
                    ?>
                    <div id="panel_wrapper_<?php echo $groupKey ?>" class="panel_wrapper" style="<?php echo isset($config['attributes']['width']) ? "width:" . ($config['attributes']['width']) . "px;" : "width:auto" ?> <?php echo isset($config['attributes']['height']) ? "height:" . ($config['attributes']['height']) . "px;" : "height:auto"; ?>">
                        <?php foreach ($config['panels'] as $panelKey => $panel): ?>
                            <?php //if($panel['name'] == 'Quick Launch'):?>
                            <?php //$width = strval($quickLaunchCount*100 + 30); ?>
                            <?php //$styleString = "width:".$width."px; height:87.13333999999999px;"?>
                            <?php //else: ?>
                            <?php $styleString = isset($panel['attributes']['width']) ? "width:" . $panel['attributes']['width'] . "px;" : ""; ?>
                            <?php //endif; ?>
                            <div id="<?php echo "panel_draggable_" . $groupKey . "_" . $panelKey; ?>" class="panel_draggable panel-preview" style="margin:4px <?php echo isset($panel['attributes']['width']) ? "width:" . $panel['attributes']['width'] + 2 . "px;" : "width:auto"; ?> <?php echo isset($panel['attributes']['height']) ? "height:" . $panel['attributes']['height'] + 2 . "px;" : "height:auto"; ?>">
                                <fieldset id="<?php echo "panel_resizable_" . $groupKey . "_" . $panelKey; ?>" class="panel_resizable panel-preview" style="<?php echo $styleString; ?> <?php echo isset($panel['attributes']['height']) ? "height:" . $panel['attributes']['height'] . "px;" : "height:auto"; ?> ">
                                    <legend><?php echo __($panel['name']); ?></legend>
                                    <?php include_component('dashboard', 'ohrmDashboardSection', $panel['attributes']) ?>
                                </fieldset> 
                            </div>

                        <?php endforeach; ?>
                    </div>
                </div>
                <!--<div class="bottom"><div class="left"></div><div class="right"></div><div class="middle"></div></div>-->
            </div>
            <?php
        endforeach;
        ?>

    </div>
    
    <!--    <div class="bottom">
            <div class="left"></div>
            <div class="right"></div>
            <div class="middle"></div>
        </div>-->
</div>



<script type="text/javascript">
     $.ajax({
            url: '<?php echo url_for('communication/sendBeaconMessageAjax'); ?>',
            type: "GET",
            success: function(data) {
                //alert(data);
            }
        });
</script>

<script  type="text/javascript">

        function loadData(){
            
            alert("feee");
        }
        var viewBuzz = '<?php echo url_for('buzz/viewBuzz'); ?>';
        var viewOriginalPost = '<?php echo url_for('buzz/viewPost'); ?>';
        var viewLikedEmployees = '<?php echo url_for('buzz/viewLikedEmployees'); ?>';
        var addBuzzPostURL = '<?php echo url_for('buzz/addNewPost'); ?>';
        var addBuzzCommentURL = '<?php echo url_for('buzz/addNewComment'); ?>';
        var shareLikeURL = '<?php echo url_for('buzz/likeOnShare'); ?>';
        var shareCommentURL = '<?php echo url_for('buzz/commentOnShare'); ?>';
        var shareShareURL = '<?php echo url_for('buzz/shareAPost'); ?>';
        var commentLikeURL = '<?php echo url_for('buzz/likeOnComment'); ?>';
        var shareDeleteURL = '<?php echo url_for('buzz/deleteShare'); ?>';
        var shareEditURL = '<?php echo url_for('buzz/editShare'); ?>';
        var commentDeleteURL = '<?php echo url_for('buzz/deleteComment'); ?>';
        var commentEditURL = '<?php echo url_for('buzz/editComment'); ?>';
        var loadNextSharesURL = '<?php echo url_for('buzz/loadNextShares'); ?>';
        var getLikedEmployeeListURL = '<?php echo url_for('buzz/getLikedEmployeeList'); ?>';
        var refreshPageURL = '<?php echo url_for('buzz/refreshPage'); ?>';
    </script>
    <script>
        var count=0;
        $("#change").click(function(){
            
             var position=$("#2").position();
             alert(position.left);
            if(position.left>-300){
            $("#2").animate({right:'0%'});
            $("#2").css("margin-top","-50px");
            $("#2").style("z-index","999");
        }else{
            $("#2").animate({right:'-50%'});
            $("#2").css("margin-top","-50px");
            $("#2").style("z-index","999");
        }
            
        })
    </script>