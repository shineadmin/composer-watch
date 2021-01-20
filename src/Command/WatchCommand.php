<?php

namespace ShineUnited\ComposerWatch\Command;

use ShineUnited\ComposerWatch\Watcher;
use ShineUnited\ComposerWatch\Event\WatchEvent;
use ShineUnited\ComposerWatch\Pattern\PatternFactory;

use ShineUnited\ComposerBuild\Command\TaskManagerTrait;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Composer\Command\BaseCommand;
use Composer\IO\IOInterface;

use Lurker\Event\FilesystemEvent;
use Lurker\ResourceWatcher;

class WatchCommand extends BaseCommand {
	use TaskManagerTrait;

	protected function configure() {
		$this->setName('watch');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$io = $this->getIO();

		$composer = $this->getComposer();

		$package = $composer->getPackage();
		$extra = $package->getExtra();

		$taskManager = $this->getTaskManager();

		$watcher = new Watcher(null, $composer->getEventDispatcher());

		if(isset($extra['watch']) && is_array($extra['watch'])) {
			foreach($extra['watch'] as $id => $config) {
				if(!is_array($config)) {
					continue;
				}

				$files = array();
				foreach(array('file', 'files') as $label) {
					if(isset($config[$label])) {
						if(is_array($config[$label])) {
							$files = array_merge($files, $config[$label]);
						} elseif(is_string($config[$label])) {
							$files[] = $config[$label];
						}
					}
				}

				$tasks = array();
				foreach(array('task', 'tasks') as $label) {
					if(isset($config[$label])) {
						if(is_array($config[$label])) {
							$tasks = array_merge($tasks, $config[$label]);
						} elseif(is_string($config[$label])) {
							$tasks[] = $config[$label];
						}
					}
				}

				$pattern = PatternFactory::create($files);
				$action = $taskManager->createTask('watch:' . $id, $tasks);

				$watcher->track($id, $pattern);
				$watcher->addListener($id, function(WatchEvent $event) use ($action, $output, $io) {
					$io->write('<info>' . $event->getID() . '</info> triggered, ' . count($event) . ' change(s)', true, IOInterface::NORMAL);
					foreach($event as $fileEvent) {
						$io->write('[' . $fileEvent->getTypeString() . '] ' . $fileEvent->getResource(), true, IOInterface::DEBUG);
					}

					$action->setApplication($this->getApplication());

					$input = new ArrayInput(array());

					$io->write('Running watch tasks for <info>' . $event->getID() . '</info>...', true, IOInterface::VERBOSE);
					$action->run($input, $output);
					$io->write('Watch tasks for <info>' . $event->getID() . '</info> complete', true, IOInterface::VERBOSE);

					$action->setApplication(null);
				});
			}
		}

		$io->write('<comment>Watching Project...</comment>', true, IOInterface::NORMAL);

		$watcher->start();
	}
}
