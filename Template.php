<?php
class Template {
    private $scriptPath='./templates/';
    public $properties;
    private string $parent;
    private array $blocks;
    private array $blockContext;

    public function setScriptPath($scriptPath){
        $this->scriptPath=$scriptPath;
    }

    public function __construct(){
        $this->properties = array();
    }

    public function render(string $filename, array $parameters = []){
        foreach ($parameters as $key => $parameter) {
            $this->properties[$key] = $parameter;
        }

        ob_start();
        if(file_exists($this->scriptPath.$filename)){
            include($this->scriptPath.$filename);

            if ($this->parent) {
                (new Template)->render($this->parent, $this->blocks);
            }
        } else throw new LogicException();
        return ob_get_clean();
    }

    public function __set($k, $v){
        $this->properties[$k] = $v;
    }

    public function __get($k){
        return $this->properties[$k];
    }

    public function extend(string $fileLocation) {
        $this->parent = $fileLocation;
    }

    public function block(string $name) {
        $this->blockContext[] = $name;
        ob_start();
    }

    public function endblock() {
        $this->blocks[array_pop($this->blockContext)] = ob_get_clean();
    }

}

function BaseTemplate(string $content) {
    return (new Template)->render('base.phtml', ['content' =>
        $content
    ]);
}