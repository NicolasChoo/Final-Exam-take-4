<?php
/**
 * Must-Use loader for the Kits for Family Times setup plugin.
 * This ensures the plugin runs automatically on every WordPress request,
 * mirroring the pattern used by sqlite-database-integration and one-click-child-theme.
 */
$kits_setup = __DIR__ . '/../plugins/kits-family-setup/kits-family-setup.php';
if ( file_exists( $kits_setup ) ) {
    require_once $kits_setup;
}
