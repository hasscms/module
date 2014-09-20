<?php

namespace hasscms\module;

use yii\base\Component;
use yii\base\Module;
use yii\base\ErrorException;

/**
 *
 * @author zhepama
 *
 */
class ModuleManager extends Component {

	protected $modulesAreLoaded = false;

	protected $event;

	protected $_config;

	public function init()
	{
		parent::init();
	}

	/**
	 * Load the provided modules.
	 *
	 * @triggers loadModules
	 * @triggers loadModules.post
	 *
	 * @return ModuleManager
	 */
	public function loadModules() {
		if (true === $this->modulesAreLoaded) {
			return $this;
		}

		$this->trigger ( ModuleEvent::EVENT_LOAD_MODULES, $this->getEvent () );

		$this->onLoadModules ();

		$this->trigger ( ModuleEvent::EVENT_LOAD_MODULES_POST, $this->getEvent () );

		return $this;
	}

	/**
	 * Handle the loadModules event
	 *
	 * @return void
	 */
	public function onLoadModules() {
		if (true === $this->modulesAreLoaded) {
			return $this;
		}

		foreach ( \Yii::$app->modules as $moduleName => $module ) {

			if(is_object($module) && $module instanceof Module)
			{
				continue;
			}

			if(($traits = class_uses($module["class"])) && in_array('\hasscms\module\feature\DeferLoadTrait', $traits) )//延迟...
			{
				continue;
			}
			$this->loadModule($moduleName);
		}

		$this->modulesAreLoaded = true;
	}

	/**
	 *
	 * @param array $module
	 * @return Ambigous <\yii\base\Module, mixed>
	 */
	public function loadModule($moduleName)
	{
		$event = $this->getEvent ();
		$event->setModuleName ( $moduleName );
      $this->trigger(ModuleEvent::EVENT_LOAD_MODULE_RESOLVE, $event);

      $module = $event->getModule();

		if(!$module)
		{
			throw new ErrorException("$moduleName 解析失败");
		}
		$this->trigger ( ModuleEvent::EVENT_LOAD_MODULE, $event );
	}

	/**
	 * Get the module event
	 *
	 * @return ModuleEvent
	 */
	public function getEvent() {
		if (! $this->event instanceof ModuleEvent) {
			$this->setEvent ( new ModuleEvent () );
		}
		return $this->event;
	}

	/**
	 * Set the module event
	 *
	 * @param ModuleEvent $event
	 * @return ModuleManager
	 */
	public function setEvent(ModuleEvent $event) {
		if(!$event->getModuleConfig())
		{
			$event->setModuleConfig($this->getConfig());
		}
		$this->event = $event;
		return $this;
	}

	/**
	 * 可以是数组,ModuleConfig
	 * @param unknown $config
	 */
	public function setConfig($config)
	{
		if(is_array($config))
		{
			$config = new ModuleConfig($config);
		}

		if($config instanceof ModuleConfig)
		{
			$this->_config = $config;
		}
	}

	public function getConfig()
	{
		if (! $this->_config instanceof ModuleConfig) {
			$this->setConfig ( new ModuleConfig () );
		}
		return $this->_config;
	}

}

?>