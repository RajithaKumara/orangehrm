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
use_javascript(plugin_web_path('orangehrmBuzzPlugin', 'js/viewBuzzSuccess'));
use_stylesheet(plugin_web_path('orangehrmBuzzPlugin', 'css/loggedInUserDetails'));
//use_stylesheet(plugin_web_path('orangehrmBuzzPlugin', 'css/buzzHeader'));
?>

<div id="buzzDetailContainer">
    <div id="buzzSearchForm">
        <form id="ddd">
            <?php
            $searchForm = new BuzzEmployeeSearchForm();
            $searchForm->getWidget('emp_name')->setLabel(" ");
            echo $searchForm->render();
            ?>
        </form>
        <img id="gotoProfile" height="30px" src="<?php echo plugin_web_path('orangehrmBuzzPlugin', 'images/search.png'); ?>">
    </div>
    <div id="leftSide">
        <div id="userName">
            <a class="name headerEmpName" href="<?php echo url_for("buzz/viewProfile?empNumber=" . $empNumber); ?>"><?php echo $name; ?></a>
        </div>
        <div id="companyPosition" >
            <?php echo $jobtitle; ?>
        </div>
        <div id="links">
            <a class="homeLink name headerLink" href= '<?php echo url_for("buzz/viewBuzz"); ?>' >
                <?php echo __("HOME"); ?>
            </a>
            <a class="ProfileLink name headerLink" href= '<?php echo url_for("buzz/viewProfile?empNumber=" . $empNumber); ?>' >
                <?php echo __("PROFILE"); ?>
            </a>
            <a class="logoutLink name headerLink" href="<?php echo url_for('buzz/logOut'); ?>">
                <?php echo __("LOGOUT"); ?>
            </a>
        </div>
    </div>
    <div id="rightSide">
        <a href="<?php echo url_for("buzz/viewProfile?empNumber=" . $empNumber); ?>">
            <img alt="<?php echo __("Employee Photo"); ?>" 
                 src="<?php echo url_for("buzz/viewPhoto?empNumber=" . $empNumber); ?>" 
                 border="0" id="empPic"/>
        </a>
    </div>
</div>

<script type="text/javascript">
    var profilePage = '<?php echo url_for('buzz/viewProfile?empNumber='); ?>';
</script>
