<?php

namespace ShineUnited\ComposerWatch;

use ShineUnited\ComposerWatch\Pattern\PatternInterface;
use ShineUnited\ComposerWatch\Event\WatchEvent;

use Lurker\Tracker\InotifyTracker;
use Lurker\Tracker\RecursiveIteratorTracker;
use Lurker\Tracker\TrackerInterface;
use Lurker\Resource\DirectoryResource;
use Lurker\Resource\TrackedResource;
use Composer\EventDispatcher\EventDispatcher;


class Watcher {
	private $tracker;
	private $dispatcher;
	private $watching;
	private $patterns;

	public function __construct(TrackerInterface $tracker = null, EventDispatcher $dispatcher = null) {
		// tracker
		if(is_null($tracker)) {
			if(function_exists('inotify_init')) {
				$tracker = new InotifyTracker();
			} else {
				$tracker = new RecursiveIteratorTracker();
			}
		}

		$this->tracker = $tracker;

		$resource = new DirectoryResource(getcwd());
		$trackedResource = new TrackedResource('watch', $resource);
		$this->tracker->track($trackedResource);

		// event dispatcher
		if (is_null($dispatcher)) {
			$dispatcher = new EventDispatcher();
		}

		$this->dispatcher = $dispatcher;


		$this->watching = false;
		$this->patterns = array();
	}

	public function getTracker() {
		return $this->tracker;
	}

	public function getEventDispatcher() {
		return $this->dispatcher;
	}

	public function track($id, PatternInterface $pattern) {
		$this->patterns[$id] = $pattern;
	}

	public function addListener($id, $callback) {
		if(!is_callable($callback)) {
			throw new \InvalidArgumentException('Second argument to listen() should be callable, but got ' . gettype($callback));
		}

		$this->getEventDispatcher()->addListener('composer_watch.' . $id, $callback);
	}

	public function trackByListener(PatternInterface $pattern, $callback) {
		$id = uniqid();
		$this->track($id, $pattern);
		$this->addListener($id, $callback);
	}

	public function isWatching() {
		return $this->watching;
	}

	public function start($checkInterval = 1000000, $timeLimit = null) {
		$totalTime = 0;
		$this->watching = true;

		while($this->watching) {
			usleep($checkInterval);
			$totalTime += $checkInterval;

			if(null !== $timeLimit && $totalTime > $timeLimit) {
				break;
			}

			$events = array();
			foreach($this->getTracker()->getEvents() as $event) {
				$trackedResource = $event->getTrackedResource();
				$resource = $event->getResource();

				foreach($this->patterns as $id => $pattern) {
					if($pattern->matches($event->getResource())) {


						if(!isset($events[$id])) {
							$events[$id] = new WatchEvent($id, $pattern);
						}

						$events[$id]->addEvent($event);
					}
				}
			}

			$dispatcher = $this->getEventDispatcher();
			foreach($events as $id => $event) {
				$this->getEventDispatcher()->dispatch(
					$event->getName(),
					$event
				);
			}
		}

		$this->watching = false;
	}

	public function stop() {
		$this->watching = false;
	}
}
