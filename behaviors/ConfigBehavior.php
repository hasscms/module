<?php
/**
 * HassCMS (http://www.hassium.org/)
 *
 * @link      http://github.com/hasscms for the canonical source repository
 * @copyright Copyright (c) 2014-2099  Hassium  Software LLC.
 * @license   http://www.hassium.org/license/new-bsd New BSD License
 */

namespace hasscms\module\behaviors;

use hasscms\module\ModuleEvent;
use yii\base\Behavior;
use hasscms\module\feature\ConfigProviderInterface;
use yii\base\ErrorException;
use yii\helpers\ArrayHelper;


/**
 *
 *
 * @author zhepama <zhepama@gmail.com>
 * @date 2014-9-20 下午11:22:04
 * @since 1.0
 */
class ConfigBehavior extends Behavior
{
	/**
	 *
	 * @var array
	 */
	protected $configs = array();

	/**
	 *
	 * @var bool
	 */
	protected $skipConfig = false;


	public function init()
	{}

	/**
	 * {@inheritDoc}
	 */
	public function attach($owner)
	{
		$this->owner = $owner;
		$moduleConfig = $this->owner->getConfig();
		/*@var $moduleConfig  \hasscms\module\ModuleConfig */
		if ($moduleConfig->hasCachedConfig()) {
			$this->skipConfig = true;
			$moduleConfig->setMergedConfigFromCache();
			return;
		}

		$owner->on(ModuleEvent::EVENT_LOAD_MODULE, array(
			$this,
			'onLoadModule'
		));
		$owner->on(ModuleEvent::EVENT_LOAD_MODULES, array(
			$this,
			'onLoadModules'
		));
		$owner->on(ModuleEvent::EVENT_MERGE_CONFIG, array(
			$this,
			'onMergeConfig'
		));
		return $this;
	}

	/**
	 * Merge the config for each module
	 *
	 * @param ModuleEvent $e
	 * @return ConfigListener
	 */
	public function onLoadModule(ModuleEvent $e)
	{
		$module = $e->getModule();

		if (! $module instanceof ConfigProviderInterface && ! is_callable(array(
			$module,
			'getConfig'
		))) {
			return $this;
		}

		$config = $module->getConfig();
		$this->addConfig($e->getModuleName(), $config);

		return $this;
	}

	/**
	 * Merge all config files matched by the given glob()s
	 *
	 * This is only attached if config is not cached.
	 *
	 * @param ModuleEvent $e
	 * @return ConfigListener
	 */
	public function onMergeConfig(ModuleEvent $e)
	{
		$moduleConfig = $this->owner->getConfig();
		/*@var $moduleConfig  \hasscms\module\ModuleConfig */

		// Merge all of the collected configs
		$mergedConfig = $moduleConfig->getExtraConfig() ?  : array();
		foreach ($this->configs as $config) {
			$mergedConfig = ArrayHelper::merge($mergedConfig, $config);
		}
		$moduleConfig->setMergedConfig($mergedConfig);
		return $this;
	}

	/**
	 * Optionally cache merged config
	 *
	 * This is only attached if config is not cached.
	 *
	 * @param ModuleEvent $e
	 * @return ConfigListener
	 */
	public function onLoadModules(ModuleEvent $e)
	{
		$moduleConfig = $this->owner->getConfig();
		/*@var $moduleConfig  \hasscms\module\ModuleConfig */
		// Trigger MERGE_CONFIG event. This is a hook to allow the merged application config to be
		// modified before it is cached (In particular, allows the removal of config keys)
		$e->getTarget()
			->getEventManager()
			->trigger(ModuleEvent::EVENT_MERGE_CONFIG, $e->getTarget(), $e);

		// If enabled, update the config cache
		if ($moduleConfig->getConfigCacheEnabled() && false === $this->skipConfig) {
			$moduleConfig->writeMergedConfigToFile();
		}

		return $this;
	}

	/**
	 *
	 * @param string $key
	 * @param array|Traversable $config
	 * @throws Exception\InvalidArgumentException
	 * @return ConfigListener
	 */
	protected function addConfig($key, $config)
	{
		if (! is_array($config)) {
			throw new ErrorException(sprintf('Config being merged must be an array, ' . 'implement the Traversable interface, or be an ' . 'instance of Zend\Config\Config. %s given.', gettype($config)));
		}

		$this->configs[$key] = $config;

		return $this;
	}

}

?>