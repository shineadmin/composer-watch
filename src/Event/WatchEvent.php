<?php

namespace ShineUnited\ComposerWatch\Event;

use ShineUnited\ComposerWatch\Pattern\PatternInterface;

use Composer\EventDispatcher\Event;
use Lurker\Event\FilesystemEvent;


class WatchEvent extends Event implements \IteratorAggregate, \Countable {
	private $id;
	private $pattern;
	private $events;

	public function __construct($id, PatternInterface $pattern, array $events = array()) {
		$this->id = $id;
		$this->pattern = $pattern;
		$this->events = array();
		foreach($events as $event) {
			$this->addEvent($event);
		}

		parent::__construct('composer_watch.' . $id);
	}

	public function addEvent(FilesystemEvent $event) {
		$this->events[] = $event;
	}

	public function getID() {
		return $this->id;
	}

	public function getPattern() {
		return $this->pattern;
	}

	public function getEvents() {
		return $this->events;
	}

	public function getIterator() {
		return new \ArrayIterator($this->events);
	}

	public function count() {
		return count($this->events);
	}
}
