<?php
namespace Folders\Rest;

defined( 'ABSPATH' ) || exit;

class Routes {
    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'restApiInit' ] );
    }

    public function restApiInit() {
        $controllers = [
            'ImportController',
            'HelperController',
            'ConfigController',
            'FoldersController',
            'FolderTypesController',
            'SecurityProfilesController'
        ];

        foreach ( $controllers as $controller ) {
            $class = __NAMESPACE__ . "\\Controllers\\{$controller}";
            $obj = new $class();
            $obj->registerRestRoutes();
        }
    }
}
