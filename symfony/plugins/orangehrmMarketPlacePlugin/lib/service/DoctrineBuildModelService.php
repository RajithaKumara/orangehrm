<?php
/**
 * OrangeHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 OrangeHRM Inc., http://www.orangehrm.com
 *
 * OrangeHRM is free software; you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation; either
 * version 2 of the License, or (at your option) any later version.
 *
 * OrangeHRM is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with this program;
 * if not, write to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor,
 * Boston, MA 02110-1301, USA
 */

class DoctrineBuildModelService extends BaseExecService
{
    protected $config = [];
    protected $builderOptions = [
        'generateBaseClasses' => true,
        'generateTableClasses' => true,
        'packagesPrefix' => 'Plugin',
        'suffix' => '.class.php',
        'baseClassesDirectory' => 'base',
        'baseClassName' => 'sfDoctrineRecord',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->config = [
            'data_fixtures_path' => [
                sfConfig::get('sf_data_dir') . DIRECTORY_SEPARATOR . "fixtures",
            ],
            'models_path' => sfConfig::get('sf_lib_dir') . DIRECTORY_SEPARATOR . "model" . DIRECTORY_SEPARATOR . "doctrine",
            'migrations_path' => sfConfig::get('sf_lib_dir') . DIRECTORY_SEPARATOR . "migration" . DIRECTORY_SEPARATOR . "doctrine",
            'sql_path' => sfConfig::get('sf_data_dir') . DIRECTORY_SEPARATOR . "sql",
            'yaml_schema_path' => sfConfig::get('sf_config_dir') . DIRECTORY_SEPARATOR . "doctrine",
        ];

    }

    /**
     * @throws Doctrine_Import_Exception
     * Ref: symfony/lib/vendor/symfony/lib/plugins/sfDoctrinePlugin/lib/task/sfDoctrineBuildModelTask.class.php
     */
    public function buildModel()
    {
        $this->logInfo("Start building doctrine models.");

        $config = $this->config;
        $builderOptions = $this->builderOptions;

        $stubFinder = sfFinder::type('file')->prune('base')->name('*' . $builderOptions['suffix']);
        $before = $stubFinder->in($config['models_path']);

        $schema = $this->prepareSchemaFile($config['yaml_schema_path']);

        $import = new Doctrine_Import_Schema();
        $import->setOptions($builderOptions);
        $import->importSchema($schema, 'yml', $config['models_path']);

        foreach (sfYaml::load($schema) as $model => $definition) {

            $file = sprintf('%s%s/%s/Base%s%s', $config['models_path'], isset($definition['package']) ? '/' . substr($definition['package'], 0, strpos($definition['package'], '.')) : '', $builderOptions['baseClassesDirectory'], $model, $builderOptions['suffix']);
            $code = file_get_contents($file);

            // introspect the model without loading the class
            if (preg_match_all('/@property (\w+) \$(\w+)/', $code, $matches, PREG_SET_ORDER)) {
                $properties = array();
                foreach ($matches as $match) {
                    $properties[$match[2]] = $match[1];
                }

                $typePad = max(array_map('strlen', array_merge(array_values($properties), array($model))));
                $namePad = max(array_map('strlen', array_keys(array_map(array('sfInflector', 'camelize'), $properties))));
                $setters = array();
                $getters = array();

                foreach ($properties as $name => $type) {
                    $camelized = sfInflector::camelize($name);
                    $collection = 'Doctrine_Collection' == $type;

                    $getters[] = sprintf('@method %-' . $typePad . 's %s%-' . ($namePad + 2) . 's Returns the current record\'s "%s" %s', $type, 'get', $camelized . '()', $name, $collection ? 'collection' : 'value');
                    $setters[] = sprintf('@method %-' . $typePad . 's %s%-' . ($namePad + 2) . 's Sets the current record\'s "%s" %s', $model, 'set', $camelized . '()', $name, $collection ? 'collection' : 'value');
                }

                // use the last match as a search string
                $code = str_replace($match[0], $match[0] . PHP_EOL . ' * ' . PHP_EOL . ' * ' . implode(PHP_EOL . ' * ', array_merge($getters, $setters)), $code);
                var_dump("here");

                file_put_contents($file, $code);
            }
        }

        $properties = parse_ini_file(sfConfig::get('sf_config_dir') . '/properties.ini', true);
        $tokens = array(
            '##PACKAGE##' => isset($properties['symfony']['name']) ? $properties['symfony']['name'] : 'orangehrm',
            '##SUBPACKAGE##' => 'model',
            '##NAME##' => isset($properties['symfony']['author']) ? $properties['symfony']['author'] : 'OrangeHRM',
            ' <##EMAIL##>' => '',
            "{\n\n}" => "{\n}\n",
        );

        // cleanup new stub classes
        $after = $stubFinder->in($config['models_path']);
        $this->getFilesystem()->replaceTokens(array_diff($after, $before), '', '', $tokens);

        // cleanup base classes
        $baseFinder = sfFinder::type('file')->name('Base*' . $builderOptions['suffix']);
        $baseDirFinder = sfFinder::type('dir')->name('base');
        $this->getFilesystem()->replaceTokens($baseFinder->in($baseDirFinder->in($config['models_path'])), '', '', $tokens);

        $this->logInfo("Finish building doctrine models.\n");
    }

    protected function prepareSchemaFile($yamlSchemaPath)
    {
        $refDoctrineBaseTask = new ReflectionClass(sfDoctrineBaseTask::class);
        $refMethodPrepareSchemaFile = $refDoctrineBaseTask->getMethod('prepareSchemaFile');
        $refMethodPrepareSchemaFile->setAccessible(true);
        $eventDispatcher = ProjectConfiguration::getApplicationConfiguration(sfConfig::get('sf_app'), sfConfig::get('sf_environment'), sfConfig::get('sf_debug'))->getEventDispatcher();
        $doctrineBuildModelObj = new DoctrineBuildModelDummy($eventDispatcher, new sfFormatter());
        $doctrineBuildModelObj->setConfiguration($this->configuration);

        return $refMethodPrepareSchemaFile->invoke($doctrineBuildModelObj, $yamlSchemaPath);
    }

    /**
     * @inheritDoc
     */
    public function checkPrerequisites()
    {
        $result = array("status" => true, "errors" => array());
        $modelDir = sfConfig::get('sf_lib_dir') . DIRECTORY_SEPARATOR . "model";
        if (!is_writable($modelDir)) {
            $result['status'] = false;
            array_push($result['errors'], __("File write permission required to %dir% directory.", array('%dir%' => '`symfony/lib/model`')));
        }

        $sysTempDir = sys_get_temp_dir();
        if (!is_writable($sysTempDir)) {
            $result['status'] = false;
            array_push($result['errors'], __("File write permission required to system temp directory (%tempDir%).", array('%tempDir%' => $sysTempDir)));
        }

        $pluginDir = sfConfig::get('sf_plugins_dir');
        if (!is_readable($pluginDir)) {
            $result['status'] = false;
            array_push($result['errors'], __("File read permission required to %dir% directory.", array('%dir%' => '`symfony/plugins`')));
        }

        if (!is_readable(sfConfig::get('sf_lib_dir'))) {
            $result['status'] = false;
            array_push($result['errors'], __("File read permission required to %dir% directory.", array('%dir%' => '`symfony/lib`')));
        }

        if (!is_readable(sfConfig::get('sf_data_dir'))) {
            $result['status'] = false;
            array_push($result['errors'], __("File read permission required to %dir% directory.", array('%dir%' => '`symfony/data`')));
        }

        if (!is_readable(sfConfig::get('sf_config_dir'))) {
            $result['status'] = false;
            array_push($result['errors'], __("File read permission required to %dir% directory.", array('%dir%' => '`symfony/config`')));
        }
        return $result;
    }

    /**
     * @inheritDoc
     */
    public function exec()
    {
        try {
            $this->buildModel();
        } catch (Exception $e) {
            throw new DoctrineBuildModelException('', 0, $e);
        }
    }
}
