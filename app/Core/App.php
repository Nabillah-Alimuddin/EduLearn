<?php
namespace App\Core;

class App {
    protected $controller = 'AuthController';
    protected $method = 'landing';
    protected array $params = [];

    public function __construct() {
        $url = $this->parseUrl();

        // 1. Controller Resolution
        if (isset($url[0]) && !empty($url[0])) {
            $controllerName = ucfirst($url[0]) . 'Controller';
            $controllerClass = "App\\Controllers\\" . $controllerName;
            
            if (class_exists($controllerClass)) {
                $this->controller = $controllerName;
                unset($url[0]);
            } else {
                // Render 404
                $this->render404("Controller [$controllerName] tidak ditemukan.");
                return;
            }
        }

        $fullControllerClass = "App\\Controllers\\" . $this->controller;
        $this->controller = new $fullControllerClass();

        // 2. Method Resolution
        if (isset($url[1]) && !empty($url[1])) {
            // Convert kebab-case or snake_case to camelCase if needed
            $methodName = lcfirst(str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $url[1]))));
            
            if (method_exists($this->controller, $methodName)) {
                $this->method = $methodName;
                unset($url[1]);
            } else {
                $this->render404("Method [$methodName] tidak ditemukan.");
                return;
            }
        } else {
            // Default method resolution when method is omitted from URL
            if (method_exists($this->controller, 'index')) {
                $this->method = 'index';
            } elseif (method_exists($this->controller, 'dashboard')) {
                $this->method = 'dashboard';
            } elseif (method_exists($this->controller, 'landing')) {
                $this->method = 'landing';
            }
        }

        // 3. Parameters
        $this->params = $url ? array_values($url) : [];

        // 4. Call Controller & Method
        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    private function parseUrl(): array {
        if (isset($_GET['url'])) {
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            return explode('/', $url);
        }
        return [];
    }

    private function render404(string $message = ''): void {
        http_response_code(404);
        $viewFile = __DIR__ . '/../Views/errors/404.view.php';
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            echo "<h1>404 - Halaman Tidak Ditemukan</h1><p>" . htmlspecialchars($message) . "</p>";
        }
        exit();
    }
}
