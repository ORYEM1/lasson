<?php
function get_dt_config($data_header,$params=array())
{
    $data_tables_config=array();
    $data_tables_config['processing']=$params['processing']??true;
    $data_tables_config['serverSide']=$params['serverSide']??true;
    $data_tables_config['bFilter']=$params['bFilter']??false;
    $data_tables_config['pageLength']=$params['pageLength']??10;
    if(isset($params['ajax']))
    {
        $data_tables_config['ajax']=$params['ajax'];
    }
    $data_tables_config['aLengthMenu']=array(array(10,15,20, 50,75,100, 500),array(10,15,20, 50,75,100,500));
    $colum_classes=array();
    $column_sortable=array();
    $col_keys=array();
    $order_cols=array();
    $invisible_cols=array();
    foreach($data_header as $key=>$col)
    {
        $col_keys[md5(strtolower($col['name']))]=$key;
        if(isset($col['sortable']) && $col['sortable'])
        {
            $column_sortable[]=null;
            if(isset($col['db_col_name'])) {
                $order_cols[$key] = $col['db_col_name'];
            }
        }
        else
        {
            $column_sortable[]=array('bSortable'=>false);
        }
        if(isset($col['visible']) && $col['visible']===false)
        {
            $invisible_cols[]=$key;
        }
        if(isset($col['class']))
        {
            $colum_classes[]=array('sClass'=>$col['class'],'aTargets'=>array($key));
        }
    }
    //Column Classes
    if(!empty($colum_classes))
    {
        $data_tables_config['aoColumnDefs']=$colum_classes;
    }
    //Column sorting
    if(!empty($column_sortable))
    {
        $data_tables_config['aoColumns']=$column_sortable;
    }
    //Invisible columns
    if(!empty($invisible_cols))
    {
        $data_tables_config['columnDefs'][]=array('targets'=>$invisible_cols,'visible'=>false);
    }
    //Set order columns
    if(!empty($order_cols))
    {
        $order_cols=json_encode($order_cols);
        $encrypted_string=base64_encode($order_cols);
        if(isset($data_tables_config['ajax']))
        {
            if (parse_url($data_tables_config['ajax'], PHP_URL_QUERY))
            {
                $data_tables_config['ajax'].='&oc='.urlencode($encrypted_string);
            }
            else
            {
                $data_tables_config['ajax'].='?oc='.urlencode($encrypted_string);
            }
        }
    }
    //Set default ordering
    if(isset($params['order_columns']))
    {
        $order_definition=array();
        foreach($params['order_columns'] as $col=>$order_type)
        {
            if(isset($col_keys[md5(strtolower($col))]))
            {
                $order_definition[]=array($col_keys[md5(strtolower($col))],$order_type);
            }
        }
        if(!empty($order_definition))
        {
            $data_tables_config['order']=$order_definition;
        }
    }
    //Set grouping
    if(isset($params['group_columns']))
    {
        $grouping=array();
        foreach($params['group_columns'] as $col)
        {
            if(isset($col_keys[md5(strtolower($col))]))
            {
                $grouping[]=array('dataSrc'=>$col_keys[md5(strtolower($col))]);
            }
        }
        if(!empty($grouping))
        {
            $data_tables_config['rowGroup']=$grouping;
        }
    }
    return json_encode($data_tables_config,JSON_UNESCAPED_SLASHES);
}

function get_new_link_button($params=array())
{
    $config['element']='link';
    $config_params['class']='button button-primary';
    $params['open_modal']=$params['open_modal']??true;
    if($params['open_modal'])
    {
        $config_params['class'].=" open_modal";
    }
    $config_params['href']=$params['url'];
    $config['params']=$config_params;
    $icon=isset($params['icon'])?"<i class='{$params['icon']}'></i>":'';
    $label=$params['label']??'Link Button';
    $config['text']=$icon.'&nbsp;'.$label;
    return $config;
}

function get_actions_field($params=array())
{
    $config['element']='select';
    $config_params['id']='selected_records_action';
    $config_params['class']='button button-primary';
    $config['options']=$params['options']??array();
    $config['params']=$config_params;
    return $config;
}
