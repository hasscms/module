<?php
/**
 * HassCMS (http://www.hassium.org/)
 *
 * @link      http://github.com/hasscms for the canonical source repository
 * @copyright Copyright (c) 2014-2099  Hassium  Software LLC.
 * @license   http://www.hassium.org/license/new-bsd New BSD License
 */
namespace hasscms\module;

use yii\base\Object;
use hasscms\support\Config;

/**
 * @author zhepama <zhepama@gmail.com>
 * @date 2014-9-20 下午11:26:45
 * @since 1.0
 */
class ModuleConfig extends Object
{

	/**
	 *
	 * @var array
	 */
	protected $modulePaths = array();

	/**
	 *
	 * @var array
	 */
	protected $configGlobPaths = array();

	/**
	 *
	 * @var array
	 */
	protected $configStaticPaths = array();

	/**
	 *
	 * @var array
	 */
	protected $extraConfig = array();

	/**
	 *
	 * @var bool
	 */
	protected $configCacheEnabled = false;

	/**
	 *
	 * @var string
	 */
	protected $configCacheKey;

	/**
	 *
	 * @var string
	 */
	protected $cacheDir;

	/**
	 *
	 * @var bool
	 */
	protected $checkDependencies = true;

	/**
	 *
	 * @var string
	 */
	protected $moduleMapCacheEnabled = false;

	/**
	 *
	 * @var string
	 */
	protected $moduleMapCacheKey;


	/**
	 *
	 * @var array
	 */
	protected $mergedConfig = array();

	/**
	 *
	 * @var Config
	*/
	protected $mergedConfigObject;

	public function init()
	{}

	/**
	 * Get an array of paths where modules reside
	 *
	 * @return array
	 */
	public function getModulePaths()
	{
		return $this->modulePaths;
	}

	/**
	 * [
	 * "alias"=>Module_PATH
	 * ]
	 *
	 * @param array|Traversable $modulePaths
	 * @throws Exception\InvalidArgumentException
	 * @return ListenerOptions Provides fluent interface
	 */
	public function setModulePaths($modulePaths)
	{
		$this->modulePaths = $modulePaths;
		return $this;
	}

	/**
	 * Get the glob patterns to load additional config files
	 *
	 * @return array
	 */
	public function getConfigGlobPaths()
	{
		return $this->configGlobPaths;
	}

	/**
	 * Get the static paths to load additional config files
	 *
	 * @return array
	 */
	public function getConfigStaticPaths()
	{
		return $this->configStaticPaths;
	}

	/**
	 * Set the glob patterns to use for loading additional config files
	 *
	 * @param array|Traversable $configGlobPaths
	 * @throws Exception\InvalidArgumentException
	 * @return ListenerOptions Provides fluent interface
	 */
	public function setConfigGlobPaths($configGlobPaths)
	{
		$this->configGlobPaths = $configGlobPaths;
		return $this;
	}

	/**
	 * Set the static paths to use for loading additional config files
	 *
	 * @param array|Traversable $configStaticPaths
	 * @throws Exception\InvalidArgumentException
	 * @return ListenerOptions Provides fluent interface
	 */
	public function setConfigStaticPaths($configStaticPaths)
	{
		$this->configStaticPaths = $configStaticPaths;
		return $this;
	}

	/**
	 * Get any extra config to merge in.
	 *
	 * @return array|Traversable
	 */
	public function getExtraConfig()
	{
		return $this->extraConfig;
	}

	/**
	 * Add some extra config array to the main config.
	 * This is mainly useful
	 * for unit testing purposes.
	 *
	 * @param array|Traversable $extraConfig
	 * @throws Exception\InvalidArgumentException
	 * @return ListenerOptions Provides fluent interface
	 */
	public function setExtraConfig($extraConfig)
	{
		$this->extraConfig = $extraConfig;
		return $this;
	}

	/**
	 * Check if the config cache is enabled
	 *
	 * @return bool
	 */
	public function getConfigCacheEnabled()
	{
		return $this->configCacheEnabled;
	}

	/**
	 * Set if the config cache should be enabled or not
	 *
	 * @param bool $enabled
	 * @return ListenerOptions
	 */
	public function setConfigCacheEnabled($enabled)
	{
		$this->configCacheEnabled = (bool) $enabled;
		return $this;
	}

	/**
	 * Get key used to create the cache file name
	 *
	 * @return string
	 */
	public function getConfigCacheKey()
	{
		return (string) $this->configCacheKey;
	}

	/**
	 * Set key used to create the cache file name
	 *
	 * @param string $configCacheKey
	 *        	the value to be set
	 * @return ListenerOptions
	 */
	public function setConfigCacheKey($configCacheKey)
	{
		$this->configCacheKey = $configCacheKey;
		return $this;
	}

	/**
	 * Get the path to the config cache
	 *
	 * Should this be an option, or should the dir option include the
	 * filename, or should it simply remain hard-coded? Thoughts?
	 *
	 * @return string
	 */
	public function getConfigCacheFile()
	{
		return $this->getCacheDir() . '/module-config-cache.' . $this->getConfigCacheKey() . '.php';
	}

	/**
	 * Get the path where cache file(s) are stored
	 *
	 * @return string
	 */
	public function getCacheDir()
	{
		return $this->cacheDir;
	}

	/**
	 * Set the path where cache files can be stored
	 *
	 * @param string $cacheDir
	 *        	the value to be set
	 * @return ListenerOptions
	 */
	public function setCacheDir($cacheDir)
	{
		if (null === $cacheDir) {
			$this->cacheDir = $cacheDir;
		} else {
			$this->cacheDir = static::normalizePath($cacheDir);
		}
		return $this;
	}

	/**
	 * Check if the module class map cache is enabled
	 *
	 * @return bool
	 */
	public function getModuleMapCacheEnabled()
	{
		return $this->moduleMapCacheEnabled;
	}

	/**
	 * Set if the module class map cache should be enabled or not
	 *
	 * @param bool $enabled
	 * @return ListenerOptions
	 */
	public function setModuleMapCacheEnabled($enabled)
	{
		$this->moduleMapCacheEnabled = (bool) $enabled;
		return $this;
	}

	/**
	 * Get key used to create the cache file name
	 *
	 * @return string
	 */
	public function getModuleMapCacheKey()
	{
		return (string) $this->moduleMapCacheKey;
	}

	/**
	 * Set key used to create the cache file name
	 *
	 * @param string $moduleMapCacheKey
	 *        	the value to be set
	 * @return ListenerOptions
	 */
	public function setModuleMapCacheKey($moduleMapCacheKey)
	{
		$this->moduleMapCacheKey = $moduleMapCacheKey;
		return $this;
	}

	/**
	 * Get the path to the module class map cache
	 *
	 * @return string
	 */
	public function getModuleMapCacheFile()
	{
		return $this->getCacheDir() . '/module-classmap-cache.' . $this->getModuleMapCacheKey() . '.php';
	}

	/**
	 * Set whether to check dependencies during module loading or not
	 *
	 * @return string
	 */
	public function getCheckDependencies()
	{
		return $this->checkDependencies;
	}

	/**
	 * Set whether to check dependencies during module loading or not
	 *
	 * @param bool $checkDependencies
	 *        	the value to be set
	 *
	 * @return ListenerOptions
	 */
	public function setCheckDependencies($checkDependencies)
	{
		$this->checkDependencies = (bool) $checkDependencies;

		return $this;
	}

	/**
	 * Normalize a path for insertion in the stack
	 *
	 * @param string $path
	 * @return string
	 */
	public static function normalizePath($path)
	{
		$path = rtrim($path, '/');
		$path = rtrim($path, '\\');
		return $path;
	}

	/**
	 *
	 * @return bool
	 */
	protected function hasCachedConfig()
	{
		if ($this->getConfigCacheEnabled() && (file_exists($this->getConfigCacheFile()))) {
			return true;
		}
		return false;
	}

	/**
	 *
	 * @return mixed
	 */
	protected function getCachedConfig()
	{
		return include $this->getConfigCacheFile();
	}

	/**
	 * getMergedConfig
	 *
	 * @param bool $returnConfigAsObject
	 * @return mixed
	 */
	public function getMergedConfig($returnConfigAsObject = true)
	{
		if ($returnConfigAsObject === true) {
			if ($this->mergedConfigObject === null) {
				$this->mergedConfigObject = new Config($this->mergedConfig);
			}
			return $this->mergedConfigObject;
		}

		return $this->mergedConfig;
	}

	/**
	 * setMergedConfig
	 *
	 * @param array $config
	 * @return ConfigListener
	 */
	public function setMergedConfig(array $config)
	{
		$this->mergedConfig = $config;
		$this->mergedConfigObject = null;
		return $this;
	}

	public function setMergedConfigFromCache()
	{
		$this->setMergedConfig($this->getCachedConfig());
	}

	protected function writeMergedConfigToFile($filePath, $array)
	{
		$configFile = $this->getConfigCacheFile();
		$array = $this->getMergedConfig(false);

		$content = "<?php\nreturn " . var_export($array, 1) . ';';
		file_put_contents($filePath, $content);
		return $this;
	}
}

?>