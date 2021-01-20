<?php

namespace ShineUnited\ComposerWatch\Pattern;


class PatternSet implements PatternInterface, \ArrayAccess, \IteratorAggregate, \Countable {
	private $patterns;

	public function __construct(array $patterns = array()) {
		$this->patterns = array();

		foreach($patterns as $key => $pattern) {
			$this->offsetSet($key, $pattern);
		}
	}

	public function matches($path) {
		foreach($this->patterns as $key => $pattern) {
			if($pattern->matches($path)) {
				return true;
			}
		}

		return false;
	}

	public function offsetExists($offset) {
		if(isset($this->patterns[$offset])) {
			return true;
		}

		return false;
	}

	public function offsetGet($offset) {
		if(!$this->offsetExists($offset)) {
			throw new \Exception('Unknown offset "' . $offset . '"');
		}

		return $this->patterns[$offset];
	}

	public function offsetSet($offset, $value) {
		if(!$value instanceof PatternInterface) {
			throw new \Exception('Pattern must be instance of PatternInterface');
		}

		$this->patterns[$offset] = $value;
	}

	public function offsetUnset($offset) {
		if($this->offsetExists($offset)) {
			unset($this->patterns[$offset]);
		}
	}

	public function getIterator() {
		return new \ArrayIterator($this->patterns);
	}

	public function count() {
		return count($this->patterns);
	}
}
