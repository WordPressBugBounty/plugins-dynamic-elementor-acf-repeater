<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
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