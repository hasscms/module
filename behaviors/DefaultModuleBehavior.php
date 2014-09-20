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
use yii\base\Event;
use yii\base\ErrorException;
use hasscms\module\ModuleEvent;
use hasscms\module\feature\AutoloaderProviderInterface;
use hasscms\module\feature\DependencyIndicatorInterface;
use hasscms\module\feature\InitProviderInterface;
use hasscms\module\feature\BootstrapListenerInterface;
use hasscms\module\feature\CompoentProviderInterface;


/**
 *
 *
 * @author zhepama <zhepama@gmail.com>
 * @date 2014-9-20 下午11:22:34
 * @since 1.0
 */
class DefaultModuleBehavior extends Behavior {
	// TODO - Insert your code here
	public $checkDependencies = true;

	public $appClass = '\yii\base\Application';

	public $appBootstarpEventName;

	public function init()
	{
			if(!$this->appBootstarpEventName)
			{
				$this->appBootstarpEventName = \yii\base\Application::EVENT_BEFORE_REQUEST;
			}
	}
	/**
	 *
	 * @param $owner \hasscms\module\ModuleManager
	 * @see \yii\base\Behavior::attach()
	 */
	public function attach($owner) {
		$this->owner = $owner;

		$owner->on(ModuleEvent::EVENT_LOAD_MODULES,[$this,"moduleLoader"]);

		$owner->on(ModuleEvent::EVENT_LOAD_MODULE_RESOLVE,[$this,"moduleResolver"]);

		if ($this->checkDependencies) {
			$owner->on ( ModuleEvent::EVENT_LOAD_MODULE, [
					$this,
					"moduleDependencyChecker"
			], null, false );
		}

		$owner->on ( ModuleEvent::EVENT_LOAD_MODULE, [
				$this,
				"getCompoentConfig"
		], null, false);

		$owner->on ( ModuleEvent::EVENT_LOAD_MODULE, [
				$this,
				"getAutoloaderConfig"
		], null, false );


		$owner->on ( ModuleEvent::EVENT_LOAD_MODULE, [
				$this,
				"initTrigger"
		]);

		$owner->on ( ModuleEvent::EVENT_LOAD_MODULE, [
				$this,
				"onBootstrap"
		] );
	}

	/**
	 *	注册模块路径
	 * @param ModuleEvent $e
	 */
	public function moduleLoader(ModuleEvent $e)
	{
		$moduleConfig = $e->getModuleConfig();
		/*@var $moduleConfig  \hasscms\module\ModuleConfig */
		foreach ($moduleConfig->getModulePaths() as $alias=>$path)
		{
			\Yii::setAlias ( $alias, $path );
		}
	}

	/**
	 *  实例化模块
	 * @param ModuleEvent $e
	 */
	public function moduleResolver(ModuleEvent $e)
	{
		$module = \Yii::$app->getModule($e->getModuleName());
		$e->setModule($module);
	}

	/**
	 * 获取自动配置
	 *
	 * @param ModuleEvent $e
	 */
	public function getAutoloaderConfig(ModuleEvent $e) {
		$module = $e->getModule ();
		if (! $module instanceof AutoloaderProviderInterface && ! method_exists ( $module, 'getAutoloaderConfig' )) {
			return;
		}
		$autoloaderConfig = $module->getAutoloaderConfig ();

		foreach ( $autoloaderConfig as $alias => $path ) {
			\Yii::setAlias ( $alias, $path );
		}
	}

	/**
	 * 获取模块组件
	 * @param ModuleEvent $e
	 */
	public function getCompoentConfig(ModuleEvent $e)
	{
		$module = $e->getModule ();
		if (! $module instanceof CompoentProviderInterface && ! method_exists ( $module, 'getCompoentConfig' )) {
			return;
		}
		$compoentConfig = $module->getCompoentConfig ();
		\Yii::$app->setComponents($compoentConfig);
	}

	/**
	 * 检查依赖的模块是否被安装
	 *
	 * @throws Exception\MissingDependencyModuleException
	 */
	public function moduleDependencyChecker(ModuleEvent $e) {
		$module = $e->getModule ();

		if ($module instanceof DependencyIndicatorInterface || method_exists ( $module, 'getModuleDependencies' )) {
			$dependencies = $module->getModuleDependencies ();

			$moduleList = array_keys(\Yii::$app->getModules());

			foreach ( $dependencies as $dependencyModule ) {
				if (! isset ($moduleList [$dependencyModule] )) {
					throw new ErrorException("$module->id依赖模块$dependencyModule没有被加载");
				}
			}
		}
	}

	/**
	 * 执行初始化方法..执行在init后面
	 * @param ModuleEvent $e
	 */
	public function initTrigger(ModuleEvent $e) {
		$module = $e->getModule ();
		if (! $module instanceof InitProviderInterface && ! method_exists ( $module, 'initialize' )) {
			return;
		}

		$module->initialize ( $e->getTarget () );
	}
	/**
	 * 向app注册引导事件,在请求前,由于默认模块和组件可能会重名所以定义这个事件
	 * @param ModuleEvent $e
	 */
	public function onBootstrap(ModuleEvent $e) {
		$module = $e->getModule ();
		if (! $module instanceof BootstrapListenerInterface && ! method_exists ( $module, 'onBootstrap' )) {
			return;
		}
		$moduleManager = $e->getTarget ();
		Event::on ( $this->appClass, $this->appBootstarpEventName, array (
				$module,
				'onBootstrap'
		) );
	}
}

?>