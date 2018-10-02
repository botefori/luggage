<?php

$loader = require __DIR__.'/../vendor/autoload.php';

use Provider\Luggage\Application;


$app = new Application(array(
    'debug' => true
));

/*
 ************* CONTROLLERS ******************
 */

// dynamically/magically loads all of the controllers in the Controller directory
$app->mountControllers();



return $app;