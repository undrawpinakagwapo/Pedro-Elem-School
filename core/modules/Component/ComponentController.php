<?php 

class ComponentController {

    protected $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function index($component,$controller, $method) {

        // Format controller name
        $controllerClass = formatControllerName($controller) . 'Controller';

        $controllerPath = realpath(__DIR__ . "/../../") . "/components/$controllerClass/$controllerClass.php";

        // Input validation
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $controller) || !preg_match('/^[a-zA-Z0-9_]+$/', $method)) {
            http_response_code(400);
            echo 'Invalid controller or method.';
            return;
        }

        // Check if the controller file exists
        if (!file_exists($controllerPath)) {
            http_response_code(404);
            echo "Controller file not found: $controllerPath";
            return;
        }

        // Include and validate the controller class
        require_once $controllerPath;

        if (!class_exists($controllerClass)) {
            http_response_code(404);
            echo "Controller class not found: $controllerClass";
            return;
        }

        $instance = new $controllerClass($this->db);

        // Validate and call the method
        if (!method_exists($instance, $method)) {
            http_response_code(404);
            echo "Method not found in $controllerClass: $method";
            return;
        }

        $res = $instance->{$method}();

        $js = [];
        if (method_exists($instance, "js")) {
           $js = $instance->js();
        }

        $css = [];
        if (method_exists($instance, "css")) {
           $css = $instance->css();
        }

        if($method == "index") {
            $data = [
                "header" =>  isset($res['header']) ? $res['header'] : '',
                'content' => isset($res['content']) ? $res['content'] : '',
                'js' => $js,
                'css' => $css,
            ];

            if($component == "component") {
                echo loadView('views/backoffice/index', $data);
            } else {
                echo loadView('views/customer_template/index', $data);
            }
            return;
        }

        return $res;
    }
}
