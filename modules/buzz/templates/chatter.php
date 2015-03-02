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
//use_stylesheet(plugin_web_path('orangehrmBuzzPlugin', 'css/viewBuzzSuccess'));
?>
<style type="text/css">

    #buzzHeader{
        font-size: 30px; 
        position: fixed; 
        width: 100%;  
        background-color: #F07C00;
        padding: 10px; 
        z-index: 9999; 
        color: white;
        height: 58.5px;
    }

    #logo{
        float: left;
    }

    #logo{
        width: 24%;

        margin-left: -10px;
    }

    #buzzHeaderRight{
        //position: absolute;
        float: right;
        top: 10%;
    }
    #dashBoardHeaderBuzz{
        width: 960px;
        margin: 0 auto;
    }

</style>
<?php include_partial('global/header'); ?>

</head>
<body>
    <div id="buzzHeader">
        <div id="dashBoardHeaderBuzz">
            <div id="logo">
                <a href="<?php echo url_for("buzz/viewBuzz"); ?>">
                    <img id="buzz-logo" height="60px" src="<?php echo plugin_web_path('orangehrmBuzzPlugin', 'images/buzz_logo_small.png'); ?>">
                </a>
            </div>
            <div id="buzzHeaderRight">
                <div id="buzzHeaderDetails">
                    <?php include_component('buzz', 'loggedInUserDetails', array()); ?>
                </div>
            </div>
        </div>
    </div>

    <div id="wrapper">

        <div id="content">

            <?php echo $sf_content ?>

        </div> <!-- content -->

    </div> <!-- wrapper -->

    <?php // require_once '_footer.php'; ?>
