<?php

namespace ShineUnited\ComposerWatch\ActionInterface;


class TaskAction extends ActionInterface {

	public function __construct(Application $application, Task $task) {

	}

	public function execute();
}
