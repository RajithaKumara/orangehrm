<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of getFormCsrfTokenAction
 *
 * @author dewmal
 */
class getLogedToBuzzAction extends BaseBuzzAction {

    protected $loggedInEmployeeNum;

    public function execute($request) {
        try {
            $this->loggedInEmployeeNum = $this->getLogedInEmployeeNumber();

            $arr = array('state' => 'loged', 'empNum' => $this->loggedInEmployeeNum);
        } catch (Exception $ex) {
            $arr = array('state' => 'refresh');
        }
        echo json_encode($arr);
        die();

        return sfView::NONE;
    }

//put your code here
}
