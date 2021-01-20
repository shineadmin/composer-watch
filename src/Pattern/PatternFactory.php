<?php

namespace ShineUnited\ComposerWatch\Pattern;


class PatternFactory {

	static public function create($input) {
		if($input instanceof PatternInterface) {
			return $input;
		}

		if(is_array($input)) {
			$patterns = array();
			foreach($input as $item) {
				$patterns[] = self::create($item);
			}

			return new PatternSet($patterns);
		}

		if(is_string($input)) {
			if(self::isRegex($input)) {
				return new RegexPattern($input);
			} else {
				return new GlobPattern($input);
			}
		}

		throw new \Exception('Unexpected pattern type "' . gettype($input) . '"');
	}

	static protected function isRegex($string) {
		if(preg_match('/^(.{3,}?)[imsxuADU]*$/', $string, $matches)) {
			$start = substr($matches[1], 0, 1);
			$end = substr($matches[1], -1);

			if($start === $end) {
				return !preg_match('/[*?[:alnum:] \\\\]/', $start);
			}

			$delimiterPairs = array(
				array('{', '}'),
				array('(', ')'),
				array('[', ']'),
				array('<', '>')
			);

			foreach($delimiterPairs as $delimiters) {
				if ($start === $delimiters[0] && $end === $delimiters[1]) {
					return true;
				}
			}
		}

		return false;
	}
}
