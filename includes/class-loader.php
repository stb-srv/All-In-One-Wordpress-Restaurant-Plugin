<?php
namespace AIO_Restaurant_Plugin;

/**
 * Simple autoloader for plugin classes.
 */
class Loader {
    /**
     * Register the autoloader.
     */
    public static function register(): void {
        spl_autoload_register( array( __CLASS__, 'autoload' ) );
    }

    /**
     * Load class files based on class name.
     */
    private static function autoload( string $class ): void {
        if ( 0 !== strpos( $class, __NAMESPACE__ ) ) {
            return;
        }
        $relative = strtolower( str_replace( __NAMESPACE__ . '\\', '', $class ) );
        $relative = 'class-' . str_replace( '_', '-', $relative ) . '.php';
        $file     = __DIR__ . '/' . $relative;
        if ( file_exists( $file ) ) {
            require_once $file;
        }
    }
}
