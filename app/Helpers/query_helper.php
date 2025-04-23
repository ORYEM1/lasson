<?php
function qualify_columns($main_table, $columns)
{
    $qualify_columns=array();
    foreach($columns as $column)
    {
        $parts=explode(".",$column);
        if(count($parts)==1)
        {
            $qualify_columns[]=$main_table.'.'.$parts[0];
        }
        else
        {
            $qualify_columns[]=$column;
        }
    }
    return$qualify_columns;
}
function qualify_search_parameters($main_table,$params)
{
    $qualified_params=array();
    foreach($params as $key=>$column)
    {
        $parts=explode(".",$column);
        if(count($parts)==1)
        {
            $column=$main_table.'.'.$parts[0];
        }
        $qualified_params[$key]=$column;
    }
    return $qualified_params;
}
function qualify_search_range_parameters($main_table,$params)
{
    foreach($params as $key=>$array)
    {
        $parts=explode(".",$array['column']);
        if(count($parts)==1)
        {
            $params[$key]['column']=$main_table.'.'.$parts[0];
        }
    }
    return $params;
}
function qualify_search_data($main_table,$params)
{
    $qualified_fields=array();
    foreach($params as $column=>$value)
    {
        $parts=explode(".",$column);
        if(count($parts)==1)
        {
            $column=$main_table.'.'.$parts[0];
        }
        $qualified_fields[$column]=$value;
    }
    return $qualified_fields;
}