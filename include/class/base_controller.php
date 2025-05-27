<?php
// Base controller for common CRUD operations
abstract class BaseController {
    protected $table;
    protected $modelClass;
    protected $modelPath;
    
    public function __construct(string $table, string $modelClass, string $modelPath) {
        $this->table = $table;
        $this->modelClass = $modelClass;
        $this->modelPath = $modelPath;
        require_once $this->modelPath;
    }
      public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $hasId = isset($_GET['id']);
        
        try {
            switch ($method) {
                case 'GET':
                    $hasId ? $this->getById() : $this->getAll();
                    break;
                case 'POST':
                    $hasId ? $this->methodNotAllowed() : $this->create();
                    break;
                case 'PUT':
                    $hasId ? $this->update() : $this->methodNotAllowed();
                    break;
                case 'DELETE':
                    $hasId ? $this->delete() : $this->methodNotAllowed();
                    break;
                default:
                    $this->methodNotAllowed();
            }
        } catch (Exception $e) {
            new Response('Internal Server Error', $e->getMessage(), 500);
        }
    }
    
    protected function getJsonInput() {
        $rawData = file_get_contents("php://input");
        $jsonData = json_decode($rawData);
        
        if (!$jsonData) {
            new Response('Invalid JSON data', null, 400);
            exit;
        }
        
        return $jsonData;
    }
    
    protected function methodNotAllowed() {
        new Response('Method not allowed', null, 405);
    }
    
    // Abstract methods to be implemented by child classes
    abstract protected function getAll();
    abstract protected function getById();
    abstract protected function create();
    abstract protected function update();
    abstract protected function delete();
}
