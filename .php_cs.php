<?php

// Create a new CS Fixer Finder instance
$finder = PhpCsFixer\Finder::create()->in(__DIR__);

return Cartalyst\PhpCsFixer\Config::create()
    ->setFinder($finder);
