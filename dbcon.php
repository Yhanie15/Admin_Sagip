<?php

require __DIR__.'/vendor/autoload.php';

use Kreait\Firebase\Factory;

$factory = (new Factory)
    ->withServiceAccount('capstone-sagip-siklab-firebase-adminsdk-mo00s-bc1514721b.json')
    ->withDatabaseUri('https://capstone-sagip-siklab-default-rtdb.firebaseio.com/');

$database = $factory->createDatabase();

?>