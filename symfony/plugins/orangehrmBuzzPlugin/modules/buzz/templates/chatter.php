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
<style type="text/css">

    #buzzHeader{
        font-size: 30px; 
        position: fixed; 
        width: 100%;  
        background-color: #F07C00;
        padding-top: 8.5px;
        padding-bottom: 7px;
        z-index: 9999; 
        color: white;
        height: 68px;
    }

    #buzzDetailContainer {
        width: 980px;
        height: 68px;
        margin: 0 auto;
        overflow: hidden;
    }

    .help_widget {
        position: fixed;
        top: 33%;
        right: -6px;
        max-width: 150px;
        white-space: nowrap;
        text-overflow: ellipsis;
        cursor: pointer;
        overflow: hidden;
        padding: 5px 10px 10px;
        transform: rotate(-90deg);
        transform-origin: bottom right;
        border-radius: 6px 6px 0px 0px;
        opacity: 0.9;
        -webkit-tap-highlight-color: transparent;
        color: #fff;
        display: inline-block;
    }

    .help-button-global {
        background-color: #f88400;
    }

</style>
<?php include_partial('global/header'); ?>

</head>
<body>
    <div id="buzzHeader">
        <?php include_component('buzz', 'loggedInUserDetails', array()); ?>
    </div>

    <div id="wrapper">

        <div id="content">

            <?php echo $sf_content ?>
            <?php include_component('help','helpButton'); ?>
        </div> <!-- content -->

    </div> <!-- wrapper -->
</body>
