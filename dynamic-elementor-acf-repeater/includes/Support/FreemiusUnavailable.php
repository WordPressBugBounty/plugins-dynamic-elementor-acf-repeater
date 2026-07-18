<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
if ( !function_exists( 'earluna_freemius_can_use_premium_code' ) ) {
    /**
     * Fail closed when Freemius or its premium-only entitlement method is absent.
     *
     * Freemius removes methods whose names end in __premium_only from generated
     * Free builds, including the no-op fallback method below. Guarding the call
     * keeps the Free plugin usable if the bundled SDK cannot load.
     *
     * @param object|null $freemius Freemius SDK instance or fallback adapter.
     */
    function earluna_freemius_can_use_premium_code(  $freemius  ) {
        if ( !is_object( $freemius ) || !is_callable( array($freemius, 'can_use_premium_code__premium_only') ) ) {
            return false;
        }
        return (bool) $freemius->can_use_premium_code__premium_only();
    }

}
if ( !class_exists( 'Earluna_Freemius_Unavailable' ) ) {
    /**
     * No-op licensing adapter used only when the bundled SDK cannot load.
     * Premium features stay disabled and WordPress remains accessible.
     */
    final class Earluna_Freemius_Unavailable {
        public function set_basename( $basename = true, $plugin_file = '' ) {
        }

        public function add_action(
            $tag,
            $function_to_add,
            $priority = 10,
            $accepted_args = 1
        ) {
        }

        public function can_use_premium_code() {
            return false;
        }

        public function get_upgrade_url() {
            return admin_url( 'plugins.php' );
        }

        public function is_not_paying() {
            return true;
        }

        public function is_trial() {
            return false;
        }

    }

}