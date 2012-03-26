<?php use_stylesheet('executeDbChangeSuccess.css') ?>
<?php use_javascript('jquery.js') ?>
<?php use_javascript('executeDbChangeSuccess.js') ?>
<div>
    <h2><?php echo __('Upgrade Current Database')?></h2>
    <p><?php echo __('Change database')?></p>
</div>

<div id="divProgressBarContainer" class="statusValue">
    <span style="width:200px; display: block; float: left; height: 10px; border: solid 1px #000000;">
        <span id="progressBar" style="width: 0%;">&nbsp;</span>
    </span>
    &nbsp;<span id="spanProgressPercentage">0%</span>
</div>
<div>
    <form action="<?php echo url_for('upgrade/executeDbChange');?>" name="databaseChangeForm" method="post">
        <?php echo $form->renderHiddenFields();?>
        <table>
            <tbody>
                <tr>
                    <td>
                        <input type="button" name="dbChangeStartBtn" id="dbChangeStartBtn" value="<?php echo __('Start')?>"/>
                        <input type="submit" name="dbChangeProceedBtn" id="dbChangeProceedBtn" value="<?php echo __('Proceed')?>"/>
                    </td>
                </tr>
            </tbody>
        </table>
    </form>
</div>

<script type="text/javascript">
    var upgraderControllerUrl = '<?php echo url_for('upgrade/dbChangeControl');?>';
    var lang_failedToUpdate = '<?php echo __('Falid to Update')?>';
    var tasks = new Array();
    <?php
        for($i=0; $i < count($schemaIncremantArray) ; $i++)
                {
            echo "tasks[$i]='".$schemaIncremantArray[$i]."';\n";
        }
     ?>
</script>
