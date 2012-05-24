<?php
require_once __DIR__.'/../setup.php';

use \hamster\Db;

var_dump(Db::selectSecondsSpentOnActivities('2012-05-17', '2012-05-24')->fetchAll());