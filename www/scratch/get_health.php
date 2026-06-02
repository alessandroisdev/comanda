<?php

$ctx = stream_context_create(['http' => ['ignore_errors' => true]]);
echo file_get_contents('http://nginx/api/health/full', false, $ctx)."\n";
