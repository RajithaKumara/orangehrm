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
    table.hovertable {
        border-width: 1px;
        border-color: #999999;
        border-collapse: collapse;
    }
    table.hovertable th {
        color: #EBEBEB;
        background-color: #6B6B6B;
        border-width: 1px;
        font-size: 17px;
        padding: 8px;
        border-style: solid;
        border-color: #a9c6c9;
    }
    table.hovertable tr {
        background-color:#EBEBEB;
    }
    table.hovertable td {
        border-width: 1px;
        padding: 8px;
        border-style: solid;
        border-color: #a9c6c9;
    }
</style>

<div id="statTable">
    <table class="hovertable" style="width: 101%">
        <tr>
            <th colspan="2">Your statistics on OrangeHRM BuZz</th>
        </tr>
        <tr onmouseover="this.style.backgroundColor = '#F07C00';" onmouseout="this.style.backgroundColor = '#EBEBEB';">
            <td><?php echo __("No of shares"); ?></td><td><?php echo $noOfShares; ?></td>
        </tr>
        <tr onmouseover="this.style.backgroundColor = '#F07C00';" onmouseout="this.style.backgroundColor = '#EBEBEB';">
            <td><?php echo __("No of comments"); ?></td><td><?php echo $noOfComments; ?></td>
        </tr>
        <tr onmouseover="this.style.backgroundColor = '#F07C00';" onmouseout="this.style.backgroundColor = '#EBEBEB';">
            <td><?php echo __("No of likes for shares"); ?></td><td><?php echo $noOfShareLikesRecieved; ?></td>
        </tr>
        <tr onmouseover="this.style.backgroundColor = '#F07C00';" onmouseout="this.style.backgroundColor = '#EBEBEB';">
            <td><?php echo __("No of likes for comments"); ?></td><td><?php echo $noOfCommentLikesRecieved; ?></td>
        </tr>
        <tr onmouseover="this.style.backgroundColor = '#F07C00';" onmouseout="this.style.backgroundColor = '#EBEBEB';">
            <td><?php echo __("No of comments for shares"); ?></td><td><?php echo $noOfCommentsRecieved; ?></td>
        </tr>
    </table>
</div>