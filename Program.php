<?php


use App\Controller;


ini_set("error_reporting", "0");


require "src/BashHandler.php";
require "src/Journalist.php";
require "src/JsonJournalistMigrationHandler.php";
require "src/Controller.php";



(new Controller())->index();