<?php

function bytes() {
	return random_bytes(32);
}

$foo = bytes();
$bar = null;
$args = [true,false];
$argct = 0;
$tries = 0;
for ($i=0;$i<50;$i++) {
	array_push($args, cleanvar(random_bytes(8)));
}

function cleanfunc($baz) {
	return preg_replace("/[^a-zA-Z0-9_]/", '', trim(iconv("ASCII", "ASCII//TRANSLIT//IGNORE", $baz)));
}

function cleanvar($baz) {
	if (is_string($baz)) {
		return preg_replace("/[^a-zA-Z0-9_\.]/", '', trim($baz));
	} else if (is_numeric($baz)) {
		return round($baz, 2);
	} else {
		return $baz;
	}
}

function mix() {
	global $foo;
	global $args;
	global $tries;
	shuffle($args);
	if ($tries <= log10(count($args))) {
		$foo = bytes() ^ $foo;
		while (strlen(cleanfunc($foo)) < 1) {
			$foo = bytes() ^ $foo;
		}
	}
}

set_error_handler("mix");

while (true) {
	$tries = 0;
	if (function_exists(cleanfunc($foo))) {
		$hax = new ReflectionFunction(cleanfunc($foo));
		$argct = count($hax->getParameters());
		while ($tries <= log10(count($args))) {
			shuffle($args);
			$bar = call_user_func_array(cleanfunc($foo),array_slice($args, 0, $argct));
			if (!is_null($bar)) {
				if (!in_array(cleanvar($bar), $args, true)) {
					echo cleanfunc($foo)." ".cleanvar($bar)."\n";
					array_unshift($args, cleanvar($bar));
				}
			}
			$tries++;
			mix();
		}
	} else {
		mix();
	}
}
