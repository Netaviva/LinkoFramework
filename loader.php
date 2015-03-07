<?php
 
define('LINKO', true);

defined('DS') or define('DS', DIRECTORY_SEPARATOR);

defined('APP_PATH') or define('APP_PATH', getcwd() . DS);

defined('MODE') or define('MODE', 'dev');

define('LINKOBASE', dirname(__FILE__) . DS);

define('LINKO_LOG_ERRORS', false);

set_include_path(dirname(__FILE__) . DS . PATH_SEPARATOR . get_include_path());

// Require Classes/Object Manager
require 'library/linko/object.class.php';

// Require Error Handler
require 'library/error/handler/error.class.php';

// Require Exception Handler
require 'library/error/handler/exception.class.php';

set_error_handler(array(new Linko_Error_Handler, 'handle'));

/**
 * @todo V2: Implement PSR-0 Standards For This
 */

// set_exception_handler(array(new Linko_Exception_Handler, 'handle'));

/* Add classes for autoloading */
Linko_Object::map(array(

		/**
		*	Core Classes
		**/
		
		'Linko' => LINKOBASE.'library/linko/linko.class.php',		
		'Linko_Application' => LINKOBASE.'library/linko/application/application.class.php',
		'Linko_Application_Abstract' => LINKOBASE.'library/linko/application/abstract.class.php',
		'Linko_Error' => LINKOBASE.'library/error/error.class.php',
		'Linko_Error_Handler' => LINKOBASE.'library/error/handler/error.class.php',
		'Linko_Cache' => LINKOBASE.'library/cache/cache.class.php',
		'Linko_Cache_Interface' => LINKOBASE.'library/cache/interface.class.php',
		'Linko_Config' => LINKOBASE.'library/config/config.class.php',
		'Linko_Core' => LINKOBASE.'library/linko/core.class.php',
		'Linko_Route' => LINKOBASE.'library/router/route.class.php',
		'Linko_Router' => LINKOBASE.'library/router/router.class.php',

        'Linko_Template' => LINKOBASE.'library/template/template.class.php',
		'Linko_Template_Abstract' => LINKOBASE.'library/template/abstract.class.php',
		'Linko_Template_Interface' => LINKOBASE.'library/template/interface.class.php',
		'Linko_Url' => LINKOBASE.'library/url/url.class.php',

		'Linko_Request' => LINKOBASE.'library/http/request.class.php',
		'Linko_Response' => LINKOBASE.'library/http/response.class.php',
        
		'Linko_Database' => LINKOBASE.'library/database/database.class.php',
		'Linko_Database_Abstract' => LINKOBASE.'library/database/abstract.class.php',
        'Linko_Database_Interface' => LINKOBASE.'library/database/interface.class.php',
		'Linko_Query_Builder' => LINKOBASE.'library/database/builder.class.php',
        'Linko_Database_Sql_Query_Builder' => LINKOBASE.'library/database/builder/sql/builder.class.php',
        'Linko_Database_Driver_Mysqli_Schema' => LINKOBASE.'library/database/driver/mysqli/schema.class.php',
        'Linko_Database_Driver_Mysqli_Export' => LINKOBASE.'library/database/driver/mysqli/export.class.php',
        'Linko_Database_Mongo_Query_Builder' => LINKOBASE.'library/database/builder/mongo/builder.class.php',
        'Linko_Database_Driver_Mongo_Schema' => LINKOBASE.'library/database/driver/mongo/schema.class.php',
        'Linko_Database_Driver_Sqlite_Schema' => LINKOBASE.'library/database/driver/sqlite/schema.class.php',

		'Linko_Log' => LINKOBASE.'library/log/log.class.php',
		'Linko_Log_Interface' => LINKOBASE.'library/log/interface.class.php',
		'Linko_Log_Writer_Interface' => LINKOBASE.'library/log/writer/interface.class.php',
		'Linko_Log_Writer_File' => LINKOBASE.'library/log/writer/file.class.php',

        'Linko_Cookie' => LINKOBASE.'library/cookie/cookie.class.php',
		'Linko_Session' => LINKOBASE.'library/session/session.class.php',
		'Linko_Session_Abstract' => LINKOBASE.'library/session/abstract.class.php',
		'Linko_Session_Interface' => LINKOBASE.'library/session/interface.class.php',
		
        'Linko_Locale' => LINKOBASE.'library/localization/locale.class.php',
        'Linko_Locale_Date' => LINKOBASE.'library/localization/date/date.class.php', // Locale date support
        'Linko_Language' => LINKOBASE.'library/language/language.class.php',
        'Linko_Language_Abstract' => LINKOBASE.'library/language/abstract.class.php',
		/**
		*	Exceptions
		**/
		
		'Linko_Exception' => LINKOBASE.'library/error/exception.class.php',
		'MethodNotImplementedException' => LINKOBASE.'library/error/exception/MethodNotImplemented.class.php',
		'FileNotFoundException' => LINKOBASE.'library/error/exception/FileNotFound.class.php',
		
		/**
		*	Utils 
		**/
		
		'Linko_Pager' => LINKOBASE.'library/util/pager/pager.class.php',
		'Linko_Xml' => LINKOBASE.'library/util/xml/xml.class.php',
		'Linko_Form' => LINKOBASE.'library/util/form/form.class.php',
		'Linko_Validate' => LINKOBASE.'library/util/validate/validate.class.php',
		'Linko_Crypt' => LINKOBASE.'library/util/crypt/crypt.class.php',
		'Linko_Hasher' => LINKOBASE.'library/util/hasher/hasher.class.php',
		'Linko_Profiler' => LINKOBASE.'library/util/profiler/profiler.class.php',
		'Linko_Json' => LINKOBASE.'library/util/json/json.class.php',
		'Linko_Upload' => LINKOBASE.'library/util/upload/upload.class.php',
        'Linko_Image' => LINKOBASE.'library/util/image/image.class.php',
		'Linko_Flash' => LINKOBASE.'library/util/flash/flash.class.php',
		'Linko_Shortcode' => LINKOBASE.'library/util/shortcode/shortcode.class.php',
		
		/**
		* 	Helpers	
		**/
		
		'Arr' => LINKOBASE.'library/helper/arr.class.php',
        'Date' => LINKOBASE.'library/helper/date.class.php',
        'Dir' => LINKOBASE.'library/helper/dir.class.php',
        'Event' => LINKOBASE.'library/helper/event.class.php',		
		'File' => LINKOBASE.'library/helper/file.class.php',
		'FileSystem' => LINKOBASE.'library/helper/filesystem.class.php',
        'Html' => LINKOBASE.'library/helper/html.class.php',
		'Inflector' => LINKOBASE.'library/helper/inflector.class.php',
		'Input' => LINKOBASE.'library/helper/input.class.php',
        'Lang' => LINKOBASE.'library/helper/lang.class.php',
        'Number' => LINKOBASE.'library/helper/number.class.php',
		'Str' => LINKOBASE.'library/helper/str.class.php',
	)		
);

error_reporting(MODE === 'dev' ? E_ALL | E_STRICT : 0);

spl_autoload_register(array('Linko_Object', 'autoload'), true, true);

/* 
	Create shortcut for classes
	
	eg for cache
    use Linko::cache() instead of Linko_Object::get('Linko_Cache'); 
*/
Linko::extend(array(
		/*
			Core
		*/
		'application' => 'Linko_Application',
		'error' => 		'Linko_Error',
		'config' => 	'Linko_Config',
		'cache' => 		'Linko_Cache',
		'database' => 	'Linko_Database',
		'template' => 	'Linko_Template',
		'router' => 	'Linko_Router',
		'route' => 		'Linko_Route',
		'session' => 	'Linko_Session',
        'cookie' =>     'Linko_Cookie',
		'url' => 		'Linko_Url',
		'request' => 	'Linko_Request',		
		'response' => 	'Linko_Response',
        'language' => 	'Linko_Language',
        'date' =>       'Linko_Date',
					
		/*
			Utils
		*/
		'validate' => 'Linko_Validate',
		'crypt' => 	'Linko_Crypt',
		'json' => 	'Linko_Json',
		'xml' => 	'Linko_Xml',
		'hasher' => 'Linko_Hasher',
		'form' => 	'Linko_Form',
		'flash' => 'Linko_Flash',
		'pager' => 	'Linko_Pager',
		'profiler' => 'Linko_Profiler',
		'shortcode' => 	'Linko_Shortcode',
		'upload' => 'Linko_Upload',
        'image' => 'Linko_Image'
	)
);

Linko::extend('locale', function($sSupport = null)
{
    return Linko_Object::get('Linko_Locale', $sSupport);
});

Linko::Config()->init();