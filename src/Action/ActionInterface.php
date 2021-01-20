<?php

namespace ShineUnited\ComposerWatch\ActionInterface;

use Composer\Composer;
use Composer\IO\IOInterface;


interface ActionInterface {

	public function execute(Composer $composer, IOInterface $io);
}
