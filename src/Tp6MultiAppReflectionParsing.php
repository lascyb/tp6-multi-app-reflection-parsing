<?php


namespace lascyb;
/**
 * 对thinkphp6 框架 多应用模式下所有的应用控制器进行注解解析
 */
class Tp6MultiAppReflectionParsing
{
    /**
     * @var string $separator 多层次控制器分隔符
     */
    private $separator=".";
    /**
     * @var array 解析出的应用-控制器-方法节点
     */
    private $nodes=[];
    /**
     * @var array|string[] 解析的节点名
     */
    private $nodeNames;
    /**
     * @var string 注解变量值解析默认正则
     */
    private $preg="[ |\r|\t]*=[ |\r|\t]*\"(.*)\"";

    /**
     * Base constructor.
     * @param string[] $apps 应用列表
     * @param string[] $nodeNames 节点注解信息列表 节点注解示例 @group="分组"
     */
    public function __construct(array $apps=["admin",'home','api',"index"], array $nodeNames=["node","group","param"=>"/@param[ ]*(.*)[ ]*\n/"])
    {
        $this->nodeNames=$nodeNames;
        foreach (scandir(base_path()) as $dir){
            if ($dir==="."||$dir==="..") continue;
            if (in_array($dir,$apps)&&is_dir(base_path($dir))){
                $this->nodes[$dir]=$this->subDir($dir,base_path("$dir/controller"), "app\\$dir\\controller");
            }
        }
    }

    private function subDir($appDir,$dirPath, $namespace,&$nodes=[],$path=""): array
    {
        $dirFiles = scandir($dirPath);
        foreach ($dirFiles as $item) {
            if ($item == "." || $item == "..") continue;
            if (is_dir($dirPath . "/" . $item)) {
                $this->subDir($appDir,$dirPath . "/" . $item, $namespace . "\\" . $item,$nodes,$path.$item.$this->separator);
            } elseif (file_exists($dirPath . "/" . $item)) {
                $fileInfo = pathinfo($dirPath . "/" . $item);
                if ($fileInfo['extension'] === "php") {
                    $nodes[$path.$fileInfo['filename']]=$this->getMethods($namespace."\\".$fileInfo['filename'],$appDir,$path.$fileInfo['filename']);;
                }
            }
        }
        return $nodes;
    }

    private function getMethods($className,$appName,$classUrl): array
    {
        $reflection=new \ReflectionClass($className);
        $node['reflection']=$reflection;
        $node['class']=$reflection->getName();
        $methods=$reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method){
            if($method->isConstructor()||$method->isAbstract()||$method->isDestructor()||$method->isStatic())continue;
            $tag=false;
            foreach ($this->nodeNames as $nodeName=>$preg){
                if (is_int($nodeName)){
                    $nodeName=$preg;
                    $pattern="/@{$preg}{$this->preg}/";
                }else{
                    $pattern=$preg;
                }
                ${$nodeName."Preg"}=preg_match_all($pattern,$method->getDocComment(), ${$nodeName."Temp"},PREG_SET_ORDER);
                if (${$nodeName."Preg"}>=1){
                    $node["methods"][$method->getName()]["ref"][$nodeName]=${$nodeName."Temp"};
                    $tag=true;
                }
            }
            if ($tag) {
                $node["methods"][$method->getName()]["doc"]=$method->getDocComment();
                $node["methods"][$method->getName()]["reflection"]=$method;
                $node["methods"][$method->getName()]['url'] = $appName . "/" . $classUrl . "/" . ($method->getName());
            }
        }
        return $node;
    }

    /**
     * @return array
     */
    public function getNodes(): array
    {
        return $this->nodes;
    }

    /**
     * @param string $separator
     */
    public function setSeparator(string $separator="."):self
    {
        $this->separator = $separator;
        return $this;
    }
}