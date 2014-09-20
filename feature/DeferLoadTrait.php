<?php
/**
 * HassCMS (http://www.hassium.org/)
 *
 * @link      http://github.com/hasscms for the canonical source repository
 * @copyright Copyright (c) 2014-2099  Hassium  Software LLC.
 * @license   http://www.hassium.org/license/new-bsd New BSD License
 */
namespace hasscms\module\feature;

/**
 * @author zhepama <zhepama@gmail.com>
 * @date 2014-9-20 下午11:26:02
 * @since 1.0
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