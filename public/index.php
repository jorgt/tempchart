<?php
ini_set('display_errors', true);
/*
  // include the base
  include '../Core/Core.php';

  //instantiate a new application
  $app = new \Core\app('/v8');

  $app->registerDatabase('production', true);

  $app->registerModule('creator');
  $app->registerModule('page');
  $app->registerModule('authenticate');

  $app->execute();

 */

require_once '../Core/Application.php';

$app = new \Core\Application();

$app->router()->registerModule('temp');

$app->go();