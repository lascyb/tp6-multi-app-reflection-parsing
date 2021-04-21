# tp6-multi-app-reflection-parsing
对thinkphp6 框架 多应用模式下所有的应用控制器进行注解解析

#使用方法
```php
$node=(
    new \lascyb\Tp6MultiAppReflectionParsing(
            ["admin",'home','api',"index"],
            ["node","group","param"=>"/@param[ ]*(.*)[ ]*\n/"]
        )
    )->getNodes();
```
- 第一个参数为多应用的各个应用名称默认为上面四个
- 第二个参数为要解析的节点，默认正则解析模式为 ：例： $node="index/name"，通过preg_match_all函数进行解析,可通过数组模式制定特定的解析模式
- getNodes() 函数，获取解析得到的数据， reflection 键名对应反射对象，可直接调用
- setSeparator($separator=".") 多层次控制器分隔符，默认为"."