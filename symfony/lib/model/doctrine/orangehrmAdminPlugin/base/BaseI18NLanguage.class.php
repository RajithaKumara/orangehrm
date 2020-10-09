<?php

/**
 * BaseI18NLanguage
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property int                                  $id                                        Type: integer, primary key
 * @property string                               $name                                      Type: string(255)
 * @property string                               $code                                      Type: string(255), unique
 * @property bool                                 $enabled                                   Type: boolean
 * @property string                               $modifiedAt                                Type: datetime, Date and time in ISO-8601 format (YYYY-MM-DD HH:MI)
 * @property Doctrine_Collection|I18NTranslate[]  $I18NTranslate                             
 *  
 * @method int                                    getId()                                    Type: integer, primary key
 * @method string                                 getName()                                  Type: string(255)
 * @method string                                 getCode()                                  Type: string(255), unique
 * @method bool                                   getEnabled()                               Type: boolean
 * @method string                                 getModifiedat()                            Type: datetime, Date and time in ISO-8601 format (YYYY-MM-DD HH:MI)
 * @method Doctrine_Collection|I18NTranslate[]    getI18NTranslate()                         
 *  
 * @method I18NLanguage                           setId(int $val)                            Type: integer, primary key
 * @method I18NLanguage                           setName(string $val)                       Type: string(255)
 * @method I18NLanguage                           setCode(string $val)                       Type: string(255), unique
 * @method I18NLanguage                           setEnabled(bool $val)                      Type: boolean
 * @method I18NLanguage                           setModifiedat(string $val)                 Type: datetime, Date and time in ISO-8601 format (YYYY-MM-DD HH:MI)
 * @method I18NLanguage                           setI18NTranslate(Doctrine_Collection $val) 
 *  
 * @package    orangehrm
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseI18NLanguage extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('ohrm_i18n_language');
        $this->hasColumn('id', 'integer', null, array(
             'type' => 'integer',
             'primary' => true,
             'autoincrement' => true,
             ));
        $this->hasColumn('name', 'string', 255, array(
             'type' => 'string',
             'length' => 255,
             ));
        $this->hasColumn('code', 'string', 255, array(
             'type' => 'string',
             'unique' => true,
             'length' => 255,
             ));
        $this->hasColumn('enabled', 'boolean', null, array(
             'type' => 'boolean',
             ));
        $this->hasColumn('modified_at as modifiedAt', 'datetime', null, array(
             'type' => 'datetime',
             ));
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasMany('I18NTranslate', array(
             'local' => 'id',
             'foreign' => 'languageId'));
    }
}