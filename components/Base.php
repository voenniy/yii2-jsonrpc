<?php
/**
 * Created by PhpStorm.
 * User: oleg
 * Date: 11/24/15
 * Time: 2:11 PM
 */

namespace voenniy\jsonrpc\components;


use voenniy\jsonrpc\JsonRPCModule;

class Base
{
    /**
     * Получение справки по методу.
     * Например  man("Test.sum")
     * @param string $method
     * @return array
     */
    public function man($method=null){
        $return = [];
        if(!$method){
            $classes = [];
            foreach(glob(JsonRPCModule::getInstance()->apiPath . "*.php") as $file){
                $classes[] = $this->classinfo_parse_file($file);
            }
            if($classes){
                foreach ($classes as $class){
                    $classname =  key($class);
                    foreach($class[$classname]['#methods'] as $method => $info){
                        $return[] = $info['#class'] . "." . $method . "(" . $info['arguments'] . ")";
                    }
                }
            }
        } else {
            if(strpos($method, '.') !== false) {
                list($object, $method) = explode(".", $method);
                $info = $this->classinfo_parse_file(\Yii::getAlias('@frontend/APIv1/') . $object .".php");
            } else {
                $info = $this->classinfo_parse_file(__FILE__);
            }
            if($info){
                $classname = key($info);
                foreach($info[$classname]['#methods'] as $mName=>$info){
                    if($mName == $method) {
                        $return = [$mName => $info];
                        break;
                    }
                }
            }
        }
        return $return;
    }

    protected function classinfo_parse_file($filename) {
        static $cache = array();
        if (isset($cache[$filename]))
            return $cache[$filename];
        if (!file_exists($filename))
            return FALSE;
        $src = file_get_contents($filename);
        if (preg_match("/([^\/\.]+)\.[^\/]+$/", $filename, $m))
            $file_class = $m[1];
        $comments = array();
        $next_comment_id = 0;
        $src = preg_replace_callback("/
		<<< ('?) ([a-z0-9_]+) \1 \n .*? \n \2
		| \" (?: [^ \" \\\\ ] | \\\\ . )* \"
		| '  (?: [^  ' \\\\ ] | \\\\ . )*  '
		| (?: \/\/ [^\r\n]* \n   |   \/ \* .*? \* \/ \s* )+
	/xis",
            function($m) use(&$comments, &$next_comment_id) {
                $id = 'id_' . ($next_comment_id++);
                $text = $m[0];
                $comments[$id] = $text;
                return $m[0][0] == '/' ? "<<<COMMENT:$id>>>" : "<<<STRING:$id>>>";
            },
            $src
        );
        $src = preg_replace("/->[a-zA-Z0-9_]/", '', $src);
        $src = preg_replace("/\b(class[ \t\r\n]+[a-z0-9_]+)[ \t\r\n]*(extends[ \t\r\n]+([a-z0-9_]+)[ \t\r\n]*)?{/i", '\1:\3', $src);
        $src = preg_replace("/{(([^{}]*|(?R))*)}/", '', $src);
        $result = preg_match_all("/
		(?: <<<COMMENT: ([a-z0-9_]+) >>> [\h\t]* )? \b
		    (?:
		        class \s+ ( [a-z_][a-z0-9_]* :? [a-z0-9_]* ) \s+
				|
				    (?: ( protected | public ) \s+ )?
				    (?: (static) \s+ )?
				    function (?: \s* & \s* | \s+) (?: & \s+ )? ( [a-z_][a-z0-9_]* )
				    \s*
				    \( 	(?P<A>  (?: [^\(\)]*  (?: \( (?P>A) \) )?  )* )  \)
			)
	/xis", $src, $matches);
        $info = array();
        $current_class = '';
        for ($i = 0, $max = count($matches[0]); $i < $max; ++$i) {
            $m_comment_id = $matches[1][$i];
            $m_classname = $matches[2][$i];
            $m_visibility = $matches[3][$i];
            $m_static = $matches[4][$i];
            $m_funcname = $matches[5][$i];
            $m_arguments = $matches[6][$i];

            if ($m_classname) {
                $current_class = $m_classname;
                $extends = NULL;
                if (preg_match("/^([^:]+):([^:]*)$/", $current_class, $m)) {
                    $current_class = $m[1];
                    $extends = $m[2];
                }
                $info[$current_class]['#type'] = 'class';
                $info[$current_class]['#class'] = $current_class;
                if ($extends)
                    $info[$current_class]['#extends'] = $extends;
                continue;
            }
            if (!$m_funcname || (substr($m_funcname, 0, 2) == '__'))
                continue;
            $m_arguments = preg_replace_callback("/<<<(STRING|COMMENT):([a-zA-Z_0-9]+)>>>/", function($m) use(&$comments) {
                return $comments[$m[2]];
            }, $m_arguments);
            $class = $current_class;
            $function = $m_funcname;
            $rec = array(
                '#type' => 'method',
                '#class' => $class,
                'arguments' => $m_arguments,
            );
            if ($m_static)
                $rec['static'] = TRUE;
            if (strtolower($m_visibility) == 'protected')
                $rec['protected'] = TRUE;
            if ($m_comment_id) {
                $c = $comments[$m_comment_id];
                $c = preg_replace("/^[ \t]+\*/m", ' *', $c);
                $c = trim($c);
                if (!preg_match("/^function.*}$/", preg_replace("/[ \t\r\n\/\*]+/", '', $c) ))
                    $rec['comment'] = $c;
            }
            $info[$class]['#methods'][$function] = $rec;

        }
        ksort($info);
        foreach ($info as $k => & $v) {
            if (isset($v['#methods']))
                ksort($v['#methods']);
        }
        $cache[$filename] =& $info;
        return $info;
    }
}