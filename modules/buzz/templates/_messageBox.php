<?php
/**
 * OrangeHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 OrangeHRM Inc., http://www.orangehrm.com
 *
 * OrangeHRM is free software; you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation; either
 * version 2 of the License, or (at your option) any later version.
 *
 * OrangeHRM is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with this program;
 * if not, write to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor,
 * Boston, MA  02110-1301, USA
 */
use_stylesheet(plugin_web_path('orangehrmBuzzPlugin', 'css/messageBoxStyles'));
?>
<div class="message-box">
    <div class="mb-heading">
        <?php echo __($messageHeading); ?>
    </div>
    <div class="mb-body">
        <?php echo __($messageBody); ?>
    </div>
    <div class="mb-button-panel <?php echo $mbBtnPanelClass; ?>">
        <button class="submitBtn noButton <?php echo $noBtnClass; ?>"  id="<?php echo $noBtnId; ?>">
            <?php echo __("No"); ?>
        </button>
        <button class="submitBtn yesButton <?php echo $yesBtnClass; ?>"  id="<?php echo $yesBtnId; ?>">
            <?php echo __("Yes"); ?>
        </button>
        <button class="submitBtn okButton <?php echo $okBtnClass; ?>" id="<?php echo $okBtnId; ?>">
            <?php echo __("Ok"); ?>
        </button>
    </div>
</div>


