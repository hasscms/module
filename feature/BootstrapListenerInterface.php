<?php

namespace hasscms\module\feature;



/**
 * Bootstrap listener provider interface
 */
interface BootstrapListenerInterface
{
    /**
     * Listen to the bootstrap event
     *
     * @param EventInterface $e
     * @return array
     */
    public function onBootstrap($event);
}
