<?php

//File to use constants for the db connection and to load the db class


define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'videouploader');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHAR', 'utf8mb4');


require_once 'DB.php';