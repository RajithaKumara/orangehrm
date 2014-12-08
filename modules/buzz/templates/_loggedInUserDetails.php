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
use_javascript(plugin_web_path('orangehrmBuzzPlugin', 'js/viewBuzzSuccess_1'));
?>

<style type="text/css">
    #leftSide, #rightSide{
        float: left;
        margin-left: 110px;
    }

    #links{
        float: right;
    }

    #userName{
        font-size: 0.7em;
        margin-bottom: 5px;
        text-align: right;
    }

    #companyPosition{
        font-size: 0.4em;
        text-align: right;
        margin-bottom: 8px;
    }

    #rightSide{
        width: 62px;
        height: 62px;
        margin-top: -2px;
        overflow: hidden;
        border: 3px solid white;
        vertical-align: middle;
        text-align: center;
        margin-left: 10px;
        border-radius: 5px;
    }

    #rightSide img{
        width: 100%;
        position:relative;
        top:0;
        bottom:0;
        margin:auto;
    }
    .headerLink{
        font-size: 0.45em;
        margin-left: 6px;
        color: white;
    }

    #buzzSearchForm, #ddd, #gotoProfile{
        float: left;
    }

    #buzzSearchForm{
        margin-top: 18px;
        position: fixed;
        right: 38%;
    }

    #gotoProfile{
        font-size: 12px;
        margin-top: 7px;
    }
    
    #gotoProfile:hover{
        cursor: pointer;
    }

    #ddd input{
        width: 400px;
        border-radius: 5px 0 0 5px;
        border: none;
        padding: 7px;
    }

    .logoutLink{
        padding: 2px 5px;
        background-color: #6B6B6B;
        border-radius: 5px;
    }
    
    
    @media all and (min-width: 900px) { #ddd input { width: 100%;  } }
    @media all and (min-width: 1000px) { #ddd input { width: 100%;  } }
    @media all and (min-width: 1100px) { #ddd input { width: 100%;  } }
    @media all and (min-width: 1200px) { #ddd input { width: 100%;  } }
    @media all and (min-width: 1300px) { #ddd input { width: 400px;  } }
    @media all and (min-width: 1400px) { #ddd input { width: 400px;  } }
    @media all and (min-width: 1500px) { #ddd input { width: 400px;  } }
    @media all and (min-width: 1500px) { #ddd input { width: 400px;  } }
    @media all and (min-width: 1600px) { #ddd input { width: 400px;  } }
    @media all and (min-width: 1700px) { #ddd input { width: 400px;  } }

</style>

<div id="buzzDetailContainer">
    <div id="buzzSearchForm">
        <form id="ddd">
            <?php
            $searchForm = new BuzzEmployeeSearchForm();
            $searchForm->getWidget('emp_name')->setLabel(" ");
            echo $searchForm->render();
            ?>
        </form>
        <img id="gotoProfile" height="29px" style="background-color: white;margin-top: 7px; border-radius: 0 5px 5px 0;" src="<?php echo plugin_web_path('orangehrmBuzzPlugin', 'images/search.png'); ?>">
        <!--<button id="gotoProfile" class="submitBtn" >Go!</button>-->
    </div>
    <div id="leftSide">
        <div id="userName">
            <?php echo $name; ?>
        </div>
        <div id="companyPosition">
            <?php echo "Chief Technical Officer"; ?>
        </div>
        <div id="links">
            <a class="name headerLink" href= '<?php echo url_for("buzz/viewBuzz"); ?>' >
                <?php echo __("HOME"); ?>
            </a>
            <a class="name headerLink" href= '<?php echo url_for("buzz/viewProfile?empNumber=" . $empNumber); ?>' >
                <?php echo __("PROFILE"); ?>
            </a>
            <a class="logoutLink name headerLink" href="<?php echo url_for('buzz/logOut'); ?>">
                LOGOUT
            </a>
        </div>
    </div>
    <div id="rightSide">
        <img alt="<?php echo __("Employee Photo"); ?>" 
             src="<?php echo url_for("buzz/viewPhoto?empNumber=" . $empNumber); ?>" 
             border="0" id="empPic"/>
    </div>
</div>

<script type="text/javascript">
    var profilePage = '<?php echo url_for('buzz/viewProfile?empNumber='); ?>';
</script>