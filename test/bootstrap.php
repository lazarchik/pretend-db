<?php

$objComposerAutoloader = require __DIR__.'/../vendor/autoload.php';

/* @var $objComposerAutoloader \Composer\Autoload\ClassLoader */

$objComposerAutoloader->addPsr4("Entities\\", __DIR__."/Entities");

/* @var $classLoader \Composer\Autoload\ClassLoader */

require_once(__DIR__."/../vendor/doctrine/dbal/tests/Doctrine/Tests/TestInit.php");
