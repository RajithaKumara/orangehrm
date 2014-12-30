<?php

/**
 * Description of ActionValidatingForm
 *
 * @author dewmal
 */
class ActionValidatingForm extends BaseForm {

    public function configure() {
        $this->widgetSchema->setNameFormat('actionValidatingForm[%s]');
    }

}
