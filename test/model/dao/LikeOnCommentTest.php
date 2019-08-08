<?php

/*
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

/**
 * @group buzzPlugin
 */
class LikeOnCommentTest extends PHPUnit\Framework\TestCase {
    /**
     * Set up method
     */
    protected function setUp(): void {
        $this->buzzDao = new BuzzDao();

        $this->employeeService = new EmployeeService();
        $this->fixture = sfConfig::get('sf_plugins_dir') . '/orangehrmBuzzPlugin/test/fixtures/LikeOnComment.yml';
        TestDataService::populate($this->fixture);
    }

    public function testGetEmployeeFirstLastName() {
        $likeOnComment = $this->buzzDao->getLikeOnCommentById(1);
        $result = $likeOnComment->getEmployeeFirstLastName();

        $this->assertEquals('amila wick',$result, "Liked employee is incorrect.");

        $likeOnComment = $this->buzzDao->getLikeOnCommentById(2);
        $result = $likeOnComment->getEmployeeFirstLastName();

        $this->assertEquals('damith dan (Deleted Employee)',$result, "Liked employee is incorrect when soft deleted");
    }
}
