<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit16e0718d937864a4378d0a8838d82135
{
    public static $prefixLengthsPsr4 = array (
        'W' => 
        array (
            'WooCommerceWholesaleManager\\' => 28,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'WooCommerceWholesaleManager\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
            1 => __DIR__ . '/../..' . '/lib',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'WooCommerceWholesaleManager\\Admin\\Admin' => __DIR__ . '/../..' . '/src/Admin/Admin.php',
        'WooCommerceWholesaleManager\\Admin\\Settings' => __DIR__ . '/../..' . '/src/Admin/Settings.php',
        'WooCommerceWholesaleManager\\Cache' => __DIR__ . '/../..' . '/src/Cache.php',
        'WooCommerceWholesaleManager\\Emails' => __DIR__ . '/../..' . '/src/Emails.php',
        'WooCommerceWholesaleManager\\Frontend' => __DIR__ . '/../..' . '/src/Frontend.php',
        'WooCommerceWholesaleManager\\Helper' => __DIR__ . '/../..' . '/src/Helper.php',
        'WooCommerceWholesaleManager\\Installer' => __DIR__ . '/../..' . '/src/Installer.php',
        'WooCommerceWholesaleManager\\Lib\\Container' => __DIR__ . '/../..' . '/lib/Lib/Container.php',
        'WooCommerceWholesaleManager\\Lib\\Plugin' => __DIR__ . '/../..' . '/lib/Lib/Plugin.php',
        'WooCommerceWholesaleManager\\Lib\\PluginInterface' => __DIR__ . '/../..' . '/lib/Lib/PluginInterface.php',
        'WooCommerceWholesaleManager\\Lib\\Settings' => __DIR__ . '/../..' . '/lib/Lib/Settings.php',
        'WooCommerceWholesaleManager\\Plugin' => __DIR__ . '/../..' . '/src/Plugin.php',
        'WooCommerceWholesaleManager\\Roles' => __DIR__ . '/../..' . '/src/Roles.php',
        'WooCommerceWholesaleManager\\Store' => __DIR__ . '/../..' . '/src/Store.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit16e0718d937864a4378d0a8838d82135::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit16e0718d937864a4378d0a8838d82135::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit16e0718d937864a4378d0a8838d82135::$classMap;

        }, null, ClassLoader::class);
    }
}
