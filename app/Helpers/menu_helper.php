
<?php
$viable_item=false;
$top_level_items=array();
function open_sub_menu($item_name,$params)
{
    global $top_level_items;
    if(isset($top_level_items[$item_name]))
    {
        $arrow_class="fa fa-chevron-down";
    }
    else
    {
        $arrow_class="fa fa-chevron-right";
    }
    $icon=isset($params['icon_class'])?"<i class='{$params['icon_class']}'></i>":'';
    $str="\n<li><a";
    if(isset($params['class']))
    {
        $str.=" class='{$params['class']}'";
    }
    if(isset($params['id']))
    {
        $str.=" id='{$params['id']}'";
    }
    $str.=" href='#'><span>{$icon} {$item_name} <i class='{$arrow_class}'></i></span></a>\n<ul>";
    return $str;
}
function close_sub_menu()
{
    return "</ul></li>";
}
function get_menu_item($item_name,$params)
{
    $default_controller='collection_transactions';
    $default_method='index';
    global $viable_item;
    $uri_string=$params['href'];
    $uri_string=trim($uri_string,'/');
    $user_has_access=false;
    if(empty($uri_string))
    {
        $controller=$default_controller;
        $method=$default_method;
        $user_has_access=user_has_access($controller,$method);
    }
    else if($uri_string=='#')
    {
        $user_has_access=true;
    }
    else
    {
        $url_parts=explode('/',$uri_string);
        if(count($url_parts)==1)
        {
            $controller=$url_parts[0];
            $method='index';
        }
        else
        {
            $controller=$url_parts[0];
            $method=$url_parts[1];
        }
        $user_has_access=user_has_access($controller,$method);
    }
    if(!$user_has_access)
    {
        return null;
    }
    $viable_item=true;
    $icon=isset($params['icon_class'])?"<i class='{$params['icon_class']}'></i>&nbsp;":'';
    $href=isset($params['href'])?$params['href']:'#';
    $str="\n<li><a";
    if(isset($params['class']))
    {
        $str.=" class='{$params['class']}'";
    }
    if(isset($params['id']))
    {
        $str.=" id='{$params['id']}'";
    }
    $str.=" href='{$href}'>{$icon}{$item_name}</a></li>\n";
    return $str;
}
function generate_menu($components)
{
    global $viable_item;
    global $top_level_items;
    if(empty($top_level_items))
    {
        get_top_level_items($components);
    }
    $str='';
    foreach ($components as $key => $params)
    {
        if(isset($params['submenu']))
        {
            $sub_str='';
            if(isset($top_level_items[$key]))
            {
                $viable_item=false;
            }
            $opening_str=open_sub_menu($key,$params);
            $menu_str=generate_menu($params['submenu']);
            $closing_str=close_sub_menu();;
            if(!empty($menu_str))
            {
                $sub_str.=$opening_str.$menu_str.$closing_str;
            }
        }
        else
        {
            if(isset($top_level_items[$key]))
            {
                $viable_item=false;
            }
            $sub_str=get_menu_item($key,$params);
        }
        if($viable_item)
        {
            $str.=$sub_str;
        }
    }
    return $str;
}

function get_top_level_items($menu_items)
{
    global $top_level_items;
    foreach ($menu_items as $key => $item)
    {
        $top_level_items[$key]=$key;
    }
}
?>
