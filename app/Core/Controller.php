<?php
namespace App\Core;

abstract class Controller {

    protected function view(string $view, array $data = []): void {
        extract($data);
        
        $viewFile = __DIR__ . '/../Views/' . $view . '.view.php';
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            die("View file [{$view}] not found at {$viewFile}");
        }
    }

    protected function model(string $model) {
        $modelClass = "App\\Models\\" . $model;
        if (class_exists($modelClass)) {
            return new $modelClass();
        }
        die("Model class [{$modelClass}] not found.");
    }

    protected function redirect(string $url): void {
        header("Location: " . $url);
        exit();
    }

    protected function json(mixed $data, int $code = 200): void {
        Middleware::jsonResponse($data, $code);
    }
}
