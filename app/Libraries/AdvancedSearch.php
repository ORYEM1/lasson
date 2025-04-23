<?php

namespace App\Libraries;

class AdvancedSearch
{
    var $pages;
    private \App\Models\BaseModel $base_model;
    public function __construct()
    {
        $this->base_model = new \App\Models\BaseModel();
    }
    //Internal functions Section
    //==============================================================================================
    private function get_options_search_config($params)
    {
        $config['label']=$params['label'];
        $config['field_name']=$params['name'];
        if(array_is_list($params['options']))
        {
            sort($params['options']);
            $options=array_combine($params['options'], $params['options']);
        }
        else
        {
            $options=$params['options'];
        }
        $config['options']=$options;
        $config['type']=$params['type'];
        $default=array('label'=>true,'name'=>true,'options'=>true,'type'=>true);
        foreach ($params as $key => $value)
        {
            if(isset($default[$key]))
            {
                continue;
            }
            $extra_attributes[$key]=$value;
        }
        if(!empty($extra_attributes))
        {
            $config['extra_attributes']=$extra_attributes;
        }
        return $config;
    }
    private function generate_options_search_field($params=array())
    {
        $label=$params['label'];
        $field_name=$params['field_name'];
        $options=$params['options'];
        if($params['type']=='select')
        {
            $attributes=array('name'=>$field_name,'id'=>$field_name,'type'=>'select','value'=>$_POST[$field_name]??'','class'=>$params['class']??'select');
            if(isset($params['extra_attributes']))
            {
                foreach($params['extra_attributes'] as $key=>$value)
                {
                    $attributes[$key]=$value;
                }
            }
            $config= array('field_type'=>'select_field','label'=>$label,'params'=>$attributes,'options'=>$options);
        }
        else
        {
            $params['values']=$options;
            $params['field_name']=$field_name;
            $params['selected_options']=$_POST[$field_name] ?? array();
            $params['label']=$label;
            $config= get_checklist_config($params);
            if(isset($params['extra_attributes']))
            {
                $config['attributes']=$params['extra_attributes'];
            }
        }
        return $config;
    }
    public function set_query_search_data($params=array())
    {
        $table_name=$params['table_name'];
        $post_data=$params['post_data']??$_POST;
        $where=$params['where']??array();
        $where=qualify_search_data($table_name,$where);
        $like=$params['like']??array();
        $like=qualify_search_data($table_name,$like);
        $where_in=$params['where_in']??array();
        $where_in=qualify_search_data($table_name,$where_in);
        $where_search_fields=$params['where_search_fields']??array();
        $where_search_fields=qualify_search_parameters($table_name,$where_search_fields);
        $like_search_fields=$params['like_search_fields']??array();
        $like_search_fields=qualify_search_parameters($table_name,$like_search_fields);
        $search_range=$params['search_range']??array();
        $search_range=qualify_search_range_parameters($table_name,$search_range);
        $where_in_search_fields=$params['where_in_search_fields']??array();
        $where_in_search_fields=qualify_search_parameters($table_name,$where_in_search_fields);
        foreach($post_data as $key=>$value)
        {
            if($value!=0 &&empty($value))
            {
                continue;
            }
            if(isset($where_search_fields[$key]))
            {
                $where[$where_search_fields[$key]]=$value;
            }
            else if(isset($like_search_fields[$key]))
            {
                $like[$like_search_fields[$key]]=$value;
            }
            else if(isset($where_in_search_fields[$key]))
            {
                $where_in[$where_in_search_fields[$key]]=$value;
            }
            else if(isset($search_range[$key]))
            {
                $where[$search_range[$key]['column'].' '.$search_range[$key]['operator']]=$value;
            }
        }
        if(!empty($where))
        {
            $_SESSION["search_{$table_name}"]=$where;
        }
        if(!empty($like))
        {
            $_SESSION["search_{$table_name}_like"]=$like;
        }
        if(!empty($where_in))
        {
            $_SESSION["search_{$table_name}_where_in"]=$where_in;
        }
    }
    //General Search Section
    //==============================================================================================
    public function get_date_time_range($params=array())
    {
        $from_date_time=$params['from_date_time']??null;
        $to_date_time=$params['to_date_time']??null;
        $from_name=$params['from_name']??'from_date_time';
        $to_name=$params['to_name']??'to_date_time';
        $from_id=$params['from_id']??$from_name;
        $to_id=$params['to_id']??$to_name;
        $label=$params['label']??'Date and Time';
        $from_class=$params['from_class']??'text';
        $to_class=$params['to_class']??'text';
        $from_type=$params['from_type']??'datetime-local';
        $to_type=$params['to_type']??'datetime-local';
        $item=array('field_type'=>'text_range',
            'label'=>$label,
            'params'=>array(
                array('name'=>$from_name,'id'=>$from_id,'type'=>$from_type,'class'=>$from_class,'value'=>$_POST[$from_name]??$from_date_time),
                array('name'=>$to_name,'id'=>$to_id,'type'=>$to_type,'class'=>$to_class,'value'=>$_POST[$to_name]??$to_date_time),
            )
        );
        return $item;
    }
    public function get_date_range($params=array())
    {
        $from_date=$params['from_date']??null;
        $to_date=$params['to_date']??null;
        $from_name=$params['from_name']??'from_date';
        $to_name=$params['to_name']??'to_date';
        $from_id=$params['from_id']??$from_name;
        $to_id=$params['to_id']??$to_name;
        $label=$params['label']??'Date';
        $from_class=$params['from_class']??'text';
        $to_class=$params['to_class']??'text';
        $from_type=$params['from_type']??'date';
        $to_type=$params['to_type']??'date';
        $item=array('field_type'=>'text_range',
            'label'=>$label,
            'params'=>array(
                array('name'=>$from_name,'id'=>$from_id,'type'=>$from_type,'class'=>$from_class,'value'=>$_POST[$from_name]??$from_date),
                array('name'=>$to_name,'id'=>$to_id,'type'=>$to_type,'class'=>$to_class,'value'=>$_POST[$to_name]??$to_date),
            )
        );
        return $item;
    }
    public function get_amount_range($params=array())
    {
        $from_amount=$params['from_amount']??null;
        $to_amount=$params['to_amount']??null;
        $from_name=$params['from_name']??'from_amount';
        $to_name=$params['to_name']??'to_amount';
        $from_id=$params['from_id']??$from_name;
        $to_id=$params['to_id']??$to_name;
        $label=$params['label']??'Amount';
        $from_class=$params['from_class']??'text';
        $to_class=$params['to_class']??'text';
        $from_type=$params['from_type']??'number';
        $to_type=$params['to_type']??'number';
        $item=array('field_type'=>'text_range',
            'label'=>$label,
            'params'=>array(
                array('name'=>$from_name,'id'=>$from_id,'type'=>$from_type,'class'=>$from_class,'value'=>$_POST[$from_name]??$from_amount),
                array('name'=>$to_name,'id'=>$to_id,'type'=>$to_type,'class'=>$to_class,'value'=>$_POST[$to_name]??$to_amount),
            )
        );
        return $item;
    }
    public function get_text_search($params=array())
    {
        if(!isset($params['label']))
        {
            return false;
        }
        if(!isset($params['name']))
        {
            return false;
        }
        $config=array('field_type'=>'text_field','label'=>$params['label'],'params'=>array('name'=>$params['name'],'id'=>$params['id']??$params['name'],'type'=>$params['type']??'text','value'=>$_POST[$params['name']]??'','class'=>$params['class']??'text'));
        $default_params=array('label'=>true,'name'=>true,'type'=>true);
        foreach ($params as $key => $value)
        {
            if(!isset($default_params[$key]))
            {
                $config['params'][$key]=$value;
            }
        }
        return $config;
    }
    public function  get_checklist_search($params=array())
    {
        $params['type']='checklist';
        $config=$this->get_options_search_config($params);
        return $this->generate_options_search_field($config);
    }
    public function  get_select_search($params=array())
    {
        $params['type']='select';
        $config=$this->get_options_search_config($params);
        return $this->generate_options_search_field($config);
    }
    public function  get_multiple_select_search($params=array())
    {
        $params['type']='select';
        $params['name'].='[]';
        $params['multiple']='multiple';
        $config=$this->get_options_search_config($params,'select');
        return $this->generate_options_search_field($config);
    }

}