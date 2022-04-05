<?php

require_once '../vendor/autoload.php';
require_once '../config/const.php';
require_once '../config/env.php';
require_once '../helpers/functions.php';

date_default_timezone_set('Asia/Tokyo');
(new DotEnv('../.env'))->load();