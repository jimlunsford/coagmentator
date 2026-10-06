<?php
/**
 * Explicit trusted early bootstrap, installed only for the C03C disposable phase.
 *
 * @package Coagmentator
 */

// Load the already accepted fixed host transport bootstrap first.
require '/workspace/tests/fixtures/c03b-bootstrap.php';
Coagmentator\Infrastructure\Preauth_Admission::bootstrap( Coagmentator\Config\Feature_Config::load() );
