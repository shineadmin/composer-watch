<?php

namespace ShineUnited\ComposerWatch\Pattern;


class GlobPattern implements PatternInterface {
	private $pattern;
	private $flags;

	public function __construct($pattern, $flags = 0) {
		$this->pattern = $pattern;
		$this->flags = $flags;
	}

	public function matches($path) {
		$cwd = getcwd();
		$length = strlen($cwd);

		if(!substr($path, 0, $length) == $cwd) {
			return false;
		}

		$pattern = implode(DIRECTORY_SEPARATOR, array(
			rtrim($cwd, DIRECTORY_SEPARATOR),
			ltrim($this->pattern, DIRECTORY_SEPARATOR)
		));

		return fnmatch($pattern, $path, $this->flags);
	}
}
