<?php
/**
 * Disposable real-HTTP security runner only.
 *
 * @package Coagmentator
 */

// Retain C01's fixed destination and CA.
require __DIR__ . '/http.php';
require dirname( __DIR__ ) . '/security/GuardHttpCase.php';
