<?php

namespace ShineUnited\ComposerWatch\Pattern;


class RegexPattern implements PatternInterface {
	private $pattern;

	public function __construct($pattern) {
		$this->pattern = $pattern;
	}

	public function matches($path) {
		$cwd = getcwd();
		$length = strlen($cwd);

		if(!substr($path, 0, $length) == $cwd) {
			return false;
		}

		$path = ltrim(substr($path, $length), '/');

		return preg_match($this->pattern, $path);
	}
}
