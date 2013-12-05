<?php
/**
 * Стартовый файл тестов
 *
 * @package ORM
 * @subpackage Tests
 */

/**
 * Путь к папке исходные кодов
 */
define('TESTS_SRC_DIR', realpath(__DIR__ . '/../../src'));

spl_autoload_register(
    function ($class)
    {
        if ('ORM' == $class)
        {
            require TESTS_SRC_DIR . '/orm.php';
        }
        elseif (substr($class, 0, 4) == 'ORM_')
        {
            $path = TESTS_SRC_DIR . '/orm/classes/' . str_replace('_', '/', substr($class, 4))
                . '.php';
            if (file_exists($path))
            {
                require $path;
            }
        }
    }
);

require_once 'stubs.php';

