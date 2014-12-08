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

    /**
     * From to add a Task for an Emloyee
     *
     * @author aruna
     */
    class CommentForm extends sfForm {

        private $widgets;

        /**
         * Defining widgets and thier default values
         */
        public function configure() {
            $this->widgets = array(
                'comment' => new sfWidgetFormTextarea(
                        array(), array('rows' => '4', 'columns' => '80', 'id' => 'commentBox')
                ),
            );
            $this->setWidgets($this->widgets);
            $this->widgetSchema->setNameFormat('createComment[%s]');
            $this->getWidgetSchema()->setLabels($this->getFormLabels());
            $this->assignValidators();
        }

        /**
         * Defining the validators for the widgets
         */
        public function assignValidators() {
            $this->setValidators(array(
                'comment' => new sfValidatorString(array('required' => true))
            ));
        }

        /**
         * Get the label texts for the form widgets
         * @return array Label Texts
         */
        protected function getFormLabels() {

            $labels = array(
                'comment' => __('Write a comment...')
            );
            return $labels;
        }

    }
    