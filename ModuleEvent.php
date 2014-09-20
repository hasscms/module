<?php
/**
 * HassCMS (http://www.hassium.org/)
 *
 * @link      http://github.com/hasscms for the canonical source repository
 * @copyright Copyright (c) 2014-2099  Hassium  Software LLC.
 * @license   http://www.hassium.org/license/new-bsd New BSD License
 */
namespace hasscms\module;

use \yii\base\Event;

/**
 * @author zhepama <zhepama@gmail.com>
 * @date 2014-9-20 下午11:27:00
 * @since 1.0
 */
class ModuleEvent extends Event
{
    /**
     * Module events triggered by eventmanager
     */
    const EVENT_MERGE_CONFIG        = 'mergeConfig';
    const EVENT_LOAD_MODULES        = 'loadModules';
    const EVENT_LOAD_MODULE_RESOLVE = 'loadModule.resolve';
    const EVENT_LOAD_MODULE         = 'loadModule';
    const EVENT_LOAD_MODULES_POST   = 'loadModules.post';

    /**
     * @var mixed
     */
    protected $module;

    /**
     * @var string
     */
    protected $moduleName;

    protected $moduleConfig;

    /**
     * Get the name of a given module
     *
     * @return string
     */
    public function getModuleName()
    {
        return $this->moduleName;
    }

    /**
     * Set the name of a given module
     *
     * @param  string $moduleName
     * @throws Exception\InvalidArgumentException
     * @return ModuleEvent
     */
    public function setModuleName($moduleName)
    {

        $this->moduleName = $moduleName;

        return $this;
    }

    /**
     * Get module object
     *
     * @return \yii\base\Module
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Set module object to compose in this event
     *
     * @param  object $module
     * @throws Exception\InvalidArgumentException
     * @return ModuleEvent
     */
    public function setModule($module)
    {

        $this->module = $module;

        return $this;
    }
    /**
     * Get the config listener
     *
     * @return null|Listener\ConfigMergerInterface
     */
    public function getModuleConfig()
    {
    	return $this->moduleConfig;
    }

    /**
     * Set module object to compose in this event
     *
     * @param  Listener\ConfigMergerInterface $configListener
     * @return ModuleEvent
     */
    public function setModuleConfig(ModuleConfig $moduleConfig)
    {
    	$this->moduleConfig = $moduleConfig;

    	return $this;
    }
}
