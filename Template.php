<?php
class Template {
    private $scriptPath='./templates/';
    public $properties;
    public string $path;

    private ?string $parent;
    private array $blocks;
    private array $blockContext;

    public function setScriptPath($scriptPath){
        $this->scriptPath=$scriptPath;
    }

    public function __construct(){
        $this->properties = array();
        $this->parent = null;
        $this->blocks = [];
        $this->path = $_SERVER['REQUEST_URI'];
    }

    public function render(string $filename, array $parameters = []){
        foreach ($parameters as $key => $parameter) {
            $this->properties[$key] = $parameter;
        }

        $this->parent || ob_start();
        if(file_exists($this->scriptPath.$filename)){
            include($this->scriptPath.$filename);
        } else throw new LogicException();
        $result = ob_get_clean();

        if ($this->parent) {
            return (new Template)->render($this->parent, [...$this->blocks, ...$this->properties]);
        } else {
            return $result;
        }
    }

    public function __set($k, $v){
        $this->properties[$k] = $v;
    }

    public function __get($k){
        return array_key_exists($k, $this->properties) ? $this->properties[$k] : '';
    }

    public function extend(string $fileLocation): void
    {
        $this->parent = $fileLocation;
    }

    public function block(string $name): void
    {
        $this->blockContext[] = $name;
        ob_start();
    }

    public function endblock() {
        $this->blocks[array_pop($this->blockContext)] = ob_get_clean();
    }

    public function isPropertyExist(string $name): bool
    {
        return array_key_exists($name, $this->properties);
    }
}