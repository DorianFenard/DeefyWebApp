<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInita8568dabdb1761605cf3080dd449d1be
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        spl_autoload_register(array('ComposerAutoloaderInita8568dabdb1761605cf3080dd449d1be', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInita8568dabdb1761605cf3080dd449d1be', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        call_user_func(\Composer\Autoload\ComposerStaticInita8568dabdb1761605cf3080dd449d1be::getInitializer($loader));

        $loader->register(true);

        return $loader;
    }
}
