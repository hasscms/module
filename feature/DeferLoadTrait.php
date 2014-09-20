<?php
namespace hasscms\module\feature;

/**
 *只是标示这个模块延迟加载
 * @author zhepama
 *
 */
class DeferLoadTrait
{
	protected $defer = false;

	/**
	 * Determine if the provider is deferred.
	 *
	 * @return bool
	 */
	public function isDeferred()
	{
		return $this->defer;
	}
}

?>