<?php
/**
 * RESTful web service for a round-trip.
 * @api
 * @example http://.../roundtrip.php?from=YUL&to=YVR&time=0700
 * @return string JSON
 * @todo
 */

function __autoload($class_name)
{ require str_replace('\\', '/', $class_name) . '.php'; }