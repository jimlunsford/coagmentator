<?php
/**
 * Explicit runtime includes, without Composer, routes or credential issuance.
 *
 * @package Coagmentator
 */

// Load the fixed foundation definitions.
require_once __DIR__ . '/Config/class-identity-values.php';
require_once __DIR__ . '/Config/class-credential-window.php';
require_once __DIR__ . '/Config/class-operator-path.php';
require_once __DIR__ . '/Config/class-transport-policy.php';
require_once __DIR__ . '/Config/class-feature-config.php';
require_once __DIR__ . '/Auth/class-transport-evidence.php';
require_once __DIR__ . '/Auth/class-authentication-evidence.php';
require_once __DIR__ . '/Auth/class-bridge-identity.php';
require_once __DIR__ . '/Infrastructure/class-operational-failure.php';
require_once __DIR__ . '/Infrastructure/class-server-clock.php';
require_once __DIR__ . '/Infrastructure/class-read-deadline.php';
require_once __DIR__ . '/Infrastructure/class-local-store.php';
require_once __DIR__ . '/Infrastructure/class-rate-admission.php';
require_once __DIR__ . '/Infrastructure/class-read-slot.php';
require_once __DIR__ . '/Infrastructure/class-read-audit.php';
require_once __DIR__ . '/Infrastructure/class-preauth-admission.php';
require_once __DIR__ . '/Infrastructure/class-read-operation.php';
