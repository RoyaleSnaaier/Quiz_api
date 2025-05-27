<?php
// API Documentation generator
class ApiDocGenerator {
    private $endpoints = [];
    
    public function addEndpoint($path, $method, $description, $parameters = [], $responses = []) {
        if (!isset($this->endpoints[$path])) {
            $this->endpoints[$path] = [];
        }
        
        $this->endpoints[$path][$method] = [
            'description' => $description,
            'parameters' => $parameters,
            'responses' => $responses
        ];
    }
    
    public function generateSwaggerSpec() {
        $swagger = [
            'openapi' => '3.0.0',
            'info' => [
                'title' => Config::get('api')['name'],
                'version' => Config::get('api')['version'],
                'description' => 'Complete Quiz Management API with CRUD operations'
            ],
            'servers' => [
                ['url' => Config::get('api')['base_url']]
            ],
            'paths' => []
        ];
        
        foreach ($this->endpoints as $path => $methods) {
            $swagger['paths'][$path] = [];
            
            foreach ($methods as $method => $details) {
                $swagger['paths'][$path][strtolower($method)] = [
                    'summary' => $details['description'],
                    'parameters' => $details['parameters'],
                    'responses' => $details['responses']
                ];
            }
        }
        
        return $swagger;
    }
    
    public function generateHtmlDoc() {
        $html = $this->getHtmlTemplate();
        $endpointsHtml = '';
        
        foreach ($this->endpoints as $path => $methods) {
            foreach ($methods as $method => $details) {
                $endpointsHtml .= $this->generateEndpointHtml($path, $method, $details);
            }
        }
        
        return str_replace('{{ENDPOINTS}}', $endpointsHtml, $html);
    }
    
    private function generateEndpointHtml($path, $method, $details) {
        $methodClass = strtolower($method);
        
        $html = "<div class='endpoint {$methodClass}'>";
        $html .= "<h3><span class='method {$methodClass}'>{$method}</span> {$path}</h3>";
        $html .= "<p>{$details['description']}</p>";
        
        if (!empty($details['parameters'])) {
            $html .= "<h4>Parameters:</h4><ul>";
            foreach ($details['parameters'] as $param) {
                $html .= "<li><strong>{$param['name']}</strong> ({$param['type']}): {$param['description']}</li>";
            }
            $html .= "</ul>";
        }
        
        $html .= "</div>";
        
        return $html;
    }
    
    private function getHtmlTemplate() {
        return '<!DOCTYPE html>
<html>
<head>
    <title>Quiz API Documentation</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .endpoint { margin: 20px 0; padding: 15px; border-left: 4px solid #ccc; }
        .method { padding: 4px 8px; color: white; border-radius: 3px; font-weight: bold; }
        .get { border-left-color: #61affe; } .get .method { background-color: #61affe; }
        .post { border-left-color: #49cc90; } .post .method { background-color: #49cc90; }
        .put { border-left-color: #fca130; } .put .method { background-color: #fca130; }
        .delete { border-left-color: #f93e3e; } .delete .method { background-color: #f93e3e; }
    </style>
</head>
<body>
    <h1>Quiz API Documentation</h1>
    {{ENDPOINTS}}
</body>
</html>';
    }
}
