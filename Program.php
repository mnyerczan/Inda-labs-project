<?php


use App\Controller;



require "src/BashHandler.php";
require "src/Journalist.php";
require "src/JsonJournalistMigrationHandler.php";
require "src/Controller.php";



(new Controller())->index();