<?php
function get_text_field($params): string
{
    $datalist=array();
    $str = "<input";
    foreach ($params as $key => $value)
    {
        if($key=='datalist')
        {
            $datalist=$value;
            continue;
        }
        $str.= " {$key}='{$value}' ";
    }
    $str.= "/>";
    if(!empty($datalist))
    {
        $str.="<datalist id='{$params['list']}'>";
        foreach ($datalist as $value)
        {
            $str.="<option value='{$value}'></option>";
        }
        $str.="</datalist>";
    }
    return $str;
}

function get_checklist($field_name,$options,$params=array())
{
    $class='checklist';
    if(isset($attributes['class']))
    {
        $class.=' '.$attributes['class'];
    }
    $str="<ul class='{$class}' style='list-style-type:none; padding-left: 0; margin-left: 0; width:100%; display: flex;'";
    $attributes= $params['attributes'] ?? array();
    if(!empty($attributes))
    {
        foreach ($attributes as $key => $value)
        {
            if($key=='class')
            {
                continue;
            }
            $str.= " {$key}='{$value}' ";
        }
    }
    $str.=">";
    $str.=get_checklist_items($field_name,$options);
    $str.="</ul>";
    return $str;
}
function get_checklist_items($field_name,$options,$params=array())
{
    if(empty($options))
    {
        return null;
    }
    $str='';
    $counter=0;
    $selected_options=$_POST[$field_name] ?? array();
    $batches=array_chunk($options,ceil(count($options)/2),true);
    if(count($batches)==2)
    {
        if(count($batches[0])>count($batches[1]))
        {
            $carried_key=array_key_last($batches[0]);
            $carried_element=array_pop($batches[0]);
            $batch2=array($carried_key=>$carried_element);
            foreach($batches[1] as $key=>$value)
            {
                $batch2[$key]=$value;
            }
            $batches[1]=$batch2;
        }
    }
    foreach ($batches as $batch)
    {
        $str.="\n<div class='checklist_block_{$counter}'>";
        if($counter==0)
        {
            $str.="\n<li><input class='check_all_boxes' type='checkbox' data-target_class='{$field_name}' id='sa_{$field_name}' />";
            $str.=" <label for='sa_{$field_name}'>Select All</label> </li>\n";
        }
        foreach ($batch as $label=>$option)
        {
            $str.="\n<li><input class='{$field_name}'";
            foreach ($option as $key => $value)
            {
                $str.= " {$key}='{$value}' ";
            }
            $str.= "/>";
            $str.=" <label for='{$option['id']}'>{$label}</label> </li>\n";
        }
        $str.="</div>";
        $counter++;
    }
    return $str;
}

function get_select_field($params,$options)
{

    $str="<select";
    foreach ($params as $key => $value)
    {
        if($key=='value')
        {
            continue;
        }
        $str.= " {$key}='{$value}' ";
    }
    $str.=">";
    $str.=get_select_options($params,$options);
    $str.="</select>";
    return $str;
}
function get_select_options($params,$options)
{
    $selected_options=array();
    if(isset($params['value'])&&strlen($params['value']))
    {
        if(!is_array($params['value']))
        {
            $selected_options=explode(",",$params['value']);
        }
        else
        {
            $selected_options=$params['value'];
        }
    }
    $str="<option value=''></option>";
    $sequential_options=array_is_list($options)?true:false;
    foreach ($options as $key => $value)
    {
        if($sequential_options)
        {
            $key=$value;
        }
        $str.="<option value='{$value}'";
        if(in_array($value,$selected_options))
        {
            $str.= " selected";
        }
        $str.=">{$key}</option>";
    }
    return $str;
}

function get_textarea_field($params)
{
    $str = "<textarea";
    foreach ($params as $key => $value)
    {
        if($key!='value')
        {
            $str.= " {$key}='{$value}' ";
        }
    }
    $str.= ">".$params['value']??'';
    $str.="</textarea>";
    return $str;
}

function get_form_data($config)
{
    $form_data=array();
    foreach ($config as $name => $item) {
        if ($item['field_type'] =='text_field')
        {
            $params=array('name' => $name, 'id' => $item['id'] ?? $name, 'type' => $item['type']??'text', 'value' => $item['value'] ?? '', 'class' => $item['class'] ?? 'text');
            $optional_attributes=get_optional_attributes($item);
            if(!empty($optional_attributes))
            {
                $params=array_merge($params, $optional_attributes);
            }
            $form_data[] = array('field_type' => $item['field_type'], 'label' => $item['label'] ?? '', 'params' => $params);
        }
        else if ($item['field_type'] == 'password_field')
        {
            $params=array('name' => $name, 'id' => $item['id'] ?? $name, 'type' => $item['type']??'password', 'value' => $item['value'] ?? '', 'class' => $item['class'] ?? 'password');
            $optional_attributes=get_optional_attributes($item);
            if(!empty($optional_attributes))
            {
                $params=array_merge($params, $optional_attributes);
            }
            $form_data[] = array('field_type' => $item['field_type'], 'label' => $item['label'] ?? '', 'params' => $params);
        }
        else if ($item['field_type'] == 'checklist')
        {
            $field_name = $name;
            $options_array = $item['options'] ?? array();
            if(array_is_list($options_array))
            {
                sort($options_array);
            }
            else
            {
                asort($options_array);
            }
            $is_sequencial_options_array = array_is_list($options_array) ? true : false;
            $options = array();
            $selected_options = isset($item['value']) ? explode(',', $item['value']) : array();
            foreach ($options_array as $label => $value) {
                if ($is_sequencial_options_array) {
                    $label = $value;
                }
                $option = array();
                $option['type'] = 'checkbox';
                $option['name'] = "{$field_name}[]";
                $option['id'] = md5($field_name . $value);
                $option['class'] = 'checklist_item';
                $option['value'] = $value;
                if (in_array($value, $selected_options)) {
                    $option['checked'] = 'checked';
                }
                $options[$label] = $option;
            }
            $field_data=array('field_type' => 'checklist',
                'name' => $field_name,
                'label' => $item['label'],
                'options' => $options,
                'attributes'=>$item['attributes'] ?? array()
            );
            $optional_attributes=get_optional_attributes($item);
            if(!empty($optional_attributes))
            {
                $field_data=array_merge($field_data, $optional_attributes);
            }
            $form_data[] = $field_data;
        }
        else if ($item['field_type'] == 'textarea')
        {
            $params=array('name' => $name, 'id' => $item['id'] ?? $name, 'type' => $item['type']??'textarea', 'value' => $item['value'] ?? '', 'class' => $item['class'] ?? 'textarea','cols'=>$item['cols'] ?? 10,'rows'=>$item['rows'] ?? 10);
            $optional_attributes=get_optional_attributes($item);
            if(!empty($optional_attributes))
            {
                $params=array_merge($params, $optional_attributes);
            }
            $form_data[] = array('field_type' => $item['field_type'], 'label' => $item['label'] ?? '', 'params' => $params);
        }
        else if ($item['field_type'] == 'select_field')
        {
            $params=array('name' => $name, 'id' => $item['id'] ?? $name, 'value' => $item['value'] ?? '', 'class' => $item['class'] ?? 'select');
            $optional_attributes=get_optional_attributes($item);
            if(!empty($optional_attributes))
            {
                $params=array_merge($params, $optional_attributes);
            }
            $form_data[] = array('field_type' => $item['field_type'], 'label' => $item['label'] ?? '','options'=>$item['options']??array(), 'params' => $params);
        }
    }
    return $form_data;
}
function get_optional_attributes($item)
{
    $options=array('required','min','max','minlength','maxlength','tabindex','autofocus','autocomplete','datalist','list','multiple','readonly','placeholder');
    $optional_attributes=array();
    foreach ($options as $option)
    {
        if(isset($item[$option]))
        {
            $optional_attributes[$option]=$item[$option];
        }
    }
    foreach($item as $attribute => $value)
    {
        if(substr($attribute, 0, 4) == 'data')
        {
            $optional_attributes[$attribute]=$value;
        }
    }
    return $optional_attributes;
}

function get_checklist_config($params)
{
    $options=array();
    $selected_options=$params['selected_options'] ?? array();
    $field_name=$params['field_name'];
    $values=$params['values'];
    foreach($values as $label=>$value)
    {
        $option=array();
        $option['type']='checkbox';
        $option['name']="{$field_name}[]";
        $option['id']=md5($field_name.$value);
        $option['class']='select_option';
        $option['value']=$value;
        if(in_array($value,$selected_options))
        {
            $option['checked']='checked';
        }
        $options[$label]=$option;
    }
    $config=array('field_type'=>'checklist',
        'name'=>$field_name,
        'label'=>$params['label']??'',
        'options'=>$options
    );
    return $config;
}

function open_form($form_attributes)
{
    $str="<form ";
    foreach($form_attributes as $attribute=>$value)
    {
        $str.=$attribute.'="'.$value.'" ';
    }
    $str.=">";
    return $str;
}
function close_form()
{
    return '</form>';
}

