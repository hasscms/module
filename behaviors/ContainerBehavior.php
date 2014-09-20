<?php
/**
 * HassCMS (http://www.hassium.org/)
 *
 * @link      http://github.com/hasscms for the canonical source repository
 * @copyright Copyright (c) 2014-2099  Hassium  Software LLC.
 * @license   http://www.hassium.org/license/new-bsd New BSD License
 */

namespace hasscms\module\behaviors;

use yii\base\Behavior;
use hasscms\module\ModuleEvent;
use yii\di\Container;
use yii\base\ErrorException;
use yii\helpers\ArrayHelper;


/**
 *
 *
 * @author zhepama <zhepama@gmail.com>
 * @date 2014-9-20 下午11:22:21
 * @since 1.0
 */
class ContainerBehavior extends Behavior
{
	protected $defaultServiceManager;

	/**
	 *
	 * @var array
	 */
	protected $containers = array();

	public function init(){
		if (!$this->defaultServiceManager)
		{
			$this->defaultServiceManager = \Yii::$container;
		}
	}

	public function addContainer($container, $key, $moduleInterface, $method)
	{
		if (is_string($container)) {
			$smKey = $container;
		} elseif ($container instanceof Container) {
			$smKey = spl_object_hash($container);
		} else {
			throw new ErrorException(sprintf('Invalid containers provided, expected ServiceManager or string, %s provided', (string) $container));
		}

		$this->containers[$smKey] = array(
			'container' => $container,
			'config_key' => $key,
			'module_class_interface' => $moduleInterface,
			'module_class_method' => $method,
			'configuration' => array()
		);
		return $this;
	}

	public function attach($owner)
	{
		$this->owner = $owner;
		$owner->on(ModuleEvent::EVENT_LOAD_MODULE, array(
			$this,
			'onLoadModule'
		));
		$owner->on(ModuleEvent::EVENT_LOAD_MODULES_POST, array(
			$this,
			'onLoadModulesPost'
		));
	}

	public function onLoadModule(ModuleEvent $e)
	{
		$module = $e->getModule();

		foreach ($this->containers as $key => $sm) {

			if (! $module instanceof $sm['module_class_interface'] && ! method_exists($module, $sm['module_class_method'])) {
				continue;
			}

			$config = $module->{$sm['module_class_method']}();

			if (! is_array($config)) {
				// If we don't have an array by this point, nothing left to do.
				continue;
			}

			// We're keeping track of which modules provided which configuration to which service managers.
			// The actual merging takes place later. Doing it this way will enable us to provide more powerful
			// debugging tools for showing which modules overrode what.
			$fullname = $e->getModuleName() . '::' . $sm['module_class_method'] . '()';
			$this->containers[$key]['configuration'][$fullname] = $config;
		}
	}

	/**
	 * Use merged configuration to configure service manager
	 *
	 * If the merged configuration has a non-empty, array 'container'
	 * key, it will be passed to a ServiceManager Config object, and
	 * used to configure the service manager.
	 *
	 * @param ModuleEvent $e
	 * @throws Exception\RuntimeException
	 * @return void
	 */
	public function onLoadModulesPost(ModuleEvent $e)
	{
		$moduleConfig = $e->getModuleConfig();
		$config = $moduleConfig->getMergedConfig(false);

		foreach ($this->containers as $key => $sm) {
			if (isset($config[$sm['config_key']]) && is_array($config[$sm['config_key']]) && ! empty($config[$sm['config_key']])) {
				$this->containers[$key]['configuration']['merged_config'] = $config[$sm['config_key']];
			}

			// Merge all of the things!
			$smConfig = array();
			foreach ($this->containers[$key]['configuration'] as $configs) {
				if (isset($configs['configuration_classes'])) {
					foreach ($configs['configuration_classes'] as $class) {
						$configs = ArrayHelper::merge($configs, $this->serviceConfigToArray($class));
					}
				}
				$smConfig = ArrayHelper::merge($smConfig, $configs);
			}

			if (! $sm['container'] instanceof Container) {
				$instance = $this->defaultServiceManager->get($sm['container']);
				if (! $instance instanceof Container) {
					throw new ErrorException(sprintf('Could not find a valid ServiceManager for %s', $sm['container']));
				}
				$sm['container'] = $instance;
			}

			$this->configureContainer($sm['container'],$smConfig);
		}
	}

	/**
	 * 待改进 未测试
	 * @param \yii\di\Container $container
	 * @param array $serviceConfig
	 */
	public function configureContainer($container,$serviceConfig)
	{
			foreach ($serviceConfig as $class =>$definition)
			{
				$container->set($class, $definition);
			}
	}

}

?>