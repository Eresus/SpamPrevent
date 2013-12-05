<?php
/**
 * Заглушки встроенных классов Eresus
 *
 * @package Eresus
 * @subpackage Tests
 */

use Mekras\TestDoubles\UniversalStub;
use Mekras\TestDoubles\MockFacade;

require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * Заглушка для класса Plugin
 *
 * @package Eresus
 * @subpackage Tests
 */
class Plugin extends UniversalStub
{
}

/**
 * Заглушка для класса TPlugin
 *
 * @package Eresus
 * @subpackage Tests
 */
class TPlugin extends UniversalStub
{
}

/**
 * Заглушка для класса Eresus_Kernel
 *
 * @package Eresus
 * @subpackage Tests
 */
class Eresus_Kernel extends MockFacade
{
}

/**
 * Заглушка для класса Eresus_CMS
 *
 * @package Eresus
 * @subpackage Tests
 */
class Eresus_CMS extends MockFacade
{
}

/**
 * Заглушка для класса DB
 *
 * @package Eresus
 * @subpackage Tests
 */
class DB extends MockFacade
{
    private static $handler;

    public static function setHandler($handler)
    {
        self::$handler = $handler;
    }

    public static function getHandler()
    {
        return self::$handler;
    }
}

/**
 * Заглушка для класса ezcQuery
 *
 * @package Eresus
 * @subpackage Tests
 */
class ezcQuery extends UniversalStub
{
}

/**
 * Заглушка для класса ezcQuerySelect
 *
 * @package Eresus
 * @subpackage Tests
 */
class ezcQuerySelect extends ezcQuery
{
    const ASC = 'ASC';
    const DESC = 'DESC';
}

/**
 * Заглушка для класса ezcQueryInsert
 *
 * @package Eresus
 * @subpackage Tests
 */
class ezcQueryInsert extends ezcQuery
{
}

/**
 * Заглушка для класса ezcQueryUpdate
 *
 * @package Eresus
 * @subpackage Tests
 */
class ezcQueryUpdate extends ezcQuery
{
}

class EresusRuntimeException extends Exception
{
}

/**
 * Заглушка для интерфейса UI_List_DataProvider_Interface
 *
 * @package ORM
 * @subpackage Tests
 */
interface UI_List_DataProvider_Interface
{
}

/**
* Заглушка для интерфейса UI_List_Item_Interface
*
* @package ORM
* @subpackage Tests
*/
interface UI_List_Item_Interface
{
}
