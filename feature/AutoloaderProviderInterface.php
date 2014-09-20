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
 * @date 2014-9-20 下午11:24:16
 * @since 1.0
 */
interface AutoloaderProviderInterface {
    public function getAutoloaderConfig();
}

?>