<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitd4ced237c11fd6a56f447e7e774c4a6c
{
    public static $files = array (
        '256c1545158fc915c75e51a931bdba60' => __DIR__ . '/..' . '/lcobucci/jwt/compat/class-aliases.php',
        '0d273777b2b0d96e49fb3d800c6b0e81' => __DIR__ . '/..' . '/lcobucci/jwt/compat/json-exception-polyfill.php',
        'd6b246ac924292702635bb2349f4a64b' => __DIR__ . '/..' . '/lcobucci/jwt/compat/lcobucci-clock-polyfill.php',
    );

    public static $prefixLengthsPsr4 = array (
        'W' => 
        array (
            'Wu_Kartra_Billing\\' => 18,
        ),
        'L' => 
        array (
            'Lcobucci\\JWT\\' => 13,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Wu_Kartra_Billing\\' => 
        array (
            0 => '/',
        ),
        'Lcobucci\\JWT\\' => 
        array (
            0 => __DIR__ . '/..' . '/lcobucci/jwt/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitd4ced237c11fd6a56f447e7e774c4a6c::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitd4ced237c11fd6a56f447e7e774c4a6c::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitd4ced237c11fd6a56f447e7e774c4a6c::$classMap;

        }, null, ClassLoader::class);
    }
}
