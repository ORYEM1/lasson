<?php

namespace App\Models;
use CodeIgniter\Model;
class DataModel extends Model
{
    protected $db;
    protected $download_limit=500;
    public function __construct()
    {
        $this->db=db_connect();
    }
    private function get_data($params)
    {
        $return_data=array();
        $query_params=array();
        if(!isset($params['main_table']))
        {
            exit("Main table not set");
        }
        $table=$params['main_table'];
        if(isset($params['query_params']))
        {
            $query_params=$params['query_params'];
        }
        if(isset($query_params['draw']))
        {
            $return_data['draw']=$query_params['draw'];
        }
        $builder=$this->db->table($table);
        if(isset($params['fields']))
        {
            $fields=implode(',',qualify_columns($params['main_table'],$params['fields']));
            if(isset($params['auto_escape']))
            {
                if(!$params['auto_escape'])
                {
                    $builder->select($fields,false);
                }
                else
                {
                    $builder->select($fields);
                }
            }
            else
            {
                $builder->select($fields);
            }
        }
        if(isset($params['join'])&& is_array($params['join']))
        {
            foreach($params['join'] as $join)
            {
                if(isset($join[0]) && isset($join[1]) && isset($join[2]))
                {
                    $builder->join($join[0],$join[1],$join[2]);
                }
                else if(isset($join[0]) && isset($join[1]))
                {
                    $builder->join($join[0],$join[1]);
                }
                else if(isset($join['table']) && isset($join['condition']) && isset($join['type']))
                {
                    $builder->join($join['table'],$join['condition'],$join['type']);
                }
                else if(isset($join['table']) && isset($join['condition']))
                {
                    $builder->join($join['table'],$join['condition']);
                }
                else if(isset($join['table']) && isset($join['on']) && isset($join['type']))
                {
                    $builder->join($join['table'],$join['on'],$join['type']);
                }
                else if(isset($join['table']) && isset($join['on']))
                {
                    $builder->join($join['table'],$join['on']);
                }
            }
        }
        if(!empty($_SESSION["search_{$table}"]))
        {
            $builder->where($_SESSION["search_{$table}"]);
        }
        if(!empty($_SESSION["search_{$table}_like"]))
        {
            $builder->like($_SESSION["search_{$table}_like"]);
        }
        if(!empty($_SESSION["search_{$table}_where_in"]))
        {
            foreach($_SESSION["search_{$table}_where_in"] as $field=>$values)
            {
                if(!is_array($values))
                {
                    $values=explode(",",$values);
                }
                $builder->whereIn($field,$values);
            }
        }
        $return_data['recordsTotal']=$builder->countAllResults(false);

        if(isset($query_params['search_term'])&&$query_params['search_term']!=''&&isset($params['searchable_fields']))
        {
            if(isset($params['searchable_fields'])&&is_array($params['searchable_fields']))
            {
                foreach ($params['searchable_fields'] as $key=>$field)
                {
                    $field_parts=explode(".",$field);
                    if(count($field_parts)==1)
                    {
                        $params['searchable_fields'][$key]=$params['main_table'].'.'.$field_parts[0];
                    }
                }
            }
            $searchable_fields=$params['searchable_fields'];
            $search_term=$this->db->escapeString($query_params['search_term']);
            if(!empty($searchable_fields))
            {
                $where='(';
                $total_fields=count($searchable_fields);
                $counter=1;
                foreach($searchable_fields as $field)
                {
                    $where.=" {$field} LIKE '%{$search_term}%' ";
                    if($counter<$total_fields) $where.=" OR ";
                    ++$counter;
                }
                $where.=')';
                $builder->where($where);
            }
        }
        $return_data['recordsFiltered']=$builder->countAllResults(false);
        if(!empty($_GET['oc']))
        {
            $params['order_fields']=json_decode(base64_decode($_GET['oc']),true);
        }
        if(isset($params['order_fields'])&&is_array($params['order_fields']))
        {
            foreach ($params['order_fields'] as $key=>$field)
            {
                $field_parts=explode(".",$field);
                if(count($field_parts)==1)
                {
                    $params['order_fields'][$key]=$params['main_table'].'.'.$field_parts[0];
                }
            }
        }

        if(isset($params['group']))
        {
            if(is_array($params['group']))
            {
                foreach($params['group'] as $group)
                {
                    $builder->groupBy($group);
                }
            }
            else
            {
                $builder->groupBy($params['group']);
            }
        }

        if(isset($query_params['order'])&&isset($params['order_fields']))
        {
            $columns=$params['order_fields'];
            foreach($query_params['order'] as $order_field)
            {
                if(isset($columns[$order_field['column']]))
                {
                    $builder->orderBy($columns[$order_field['column']],$order_field['dir']);
                }
            }
        }
        if(empty($query_params)) $builder->limit($this->download_limit);
        else if(isset($query_params['limit']))
        {
            $builder->limit($query_params['limit']);
        }
        else $builder->limit($query_params['length'],$query_params['start']);
        //echo $builder->getCompiledSelect(); exit;
        $query=$builder->get();
        $data=$query->getResultArray();
        $response=array();
        $response['response_data']=$return_data;
        $response['db_data']=$data;
        return $response;
    }
    public function get_users($parameters=array())
    {
        $params=array();
        $params['query_params']=$parameters;
        $params['main_table']='users';
        $params['searchable_fields']=array('first_name','last_names','username','phone_number');
        $params['fields']=array('id','first_name','last_names','phone_number','email','username','user_roles.role','status','gender','date_time_created','created_by');
        $params['join']=array(array('table'=>'user_roles','condition'=>'users.role=user_roles.id','type'=>'left'));
        $query=$this->get_data($params);
        if(isset($parameters['download_data'])&&$parameters['download_data'])
        {
            return $query['db_data'];
        }
        $return_data=$query['response_data'];
        $return_data['data']=array();
        $data=$query['db_data'];
        $statuses=get_statuses_array(true);
        foreach($data as $record)
        {
            $row=array($record['id'],$record['first_name'],$record['last_names'],$record['role'],$record['email'],$record['username'],$record['phone_number'],$statuses[$record['status']]??$record['status'],$record['gender'],$record['date_time_created'],$record['created_by']);
            $url="/users/view_user/{$record['id']}";
            $row[]="<a href='{$url}'  title='View' class='open_modal'><i class='fa fa-eye'></i></a>";
            $url="/users/edit_user/{$record['id']}";
            $row[]="<a href='{$url}'  title='Edit' class='open_modal'><i class='fa fa-pencil'></i></a>";

            $url="/users/reset_password/{$record['id']}";
            $row[]="<a href='{$url}'  title='Reset Password' class='open_modal'><i class='fa fa-unlock'></i></a>";

            $url="/users/delete_user/{$record['id']}";
            $row[]="<a href='{$url}'  title='Delete' class='open_modal'><i class='fa fa-trash'></i></a>";
            $return_data['data'][]=$row;
        }
        return $return_data;

    }
    public function get_roles($parameters=array())
    {
        $params=array();
        $params['query_params']=$parameters;
        $params['main_table']='user_roles';
        $params['fields']=array('id','role','role_type','status');
        $params['searchable_fields']=array('role');
        $query=$this->get_data($params);
        if(isset($parameters['download_data'])&&$parameters['download_data'])
        {
            return $query['db_data'];
        }
        $return_data=$query['response_data'];
        $return_data['data']=array();
        $data=$query['db_data'];
        $statuses=get_statuses_array(true);
        foreach($data as $record)
        {
            $row=array($record['id'],$record['role'],$record['role_type'],$statuses[$record['status']]??$record['status']);
            $url="/roles/view_role/{$record['id']}";
            $row[]="<a href='{$url}'  title='View' class='open_modal'><i class='fa fa-search'></i></a>";

            $url="/roles/edit_role/{$record['id']}";
            $row[]="<a href='{$url}'  title='Edit' class='open_modal'><i class='fa fa-edit'></i></a>";

            $row[]="<input type='checkbox' class='select_record' data-id='{$record['id']}'>";
            $return_data['data'][]=$row;
        }
        return $return_data;

    }



    public function get_edited_data_log($parameters=array())
    {
        $params=array();
        $params['query_params']=$parameters;
        $params['main_table']='edited_data_log';
        $params['fields']=array('id','ip_address','users.first_name','users.other_names','date_time','table','record_id');
        $params['join']=array(array('table'=>'users','condition'=>'edited_data_log.user_id=users.id','type'=>'left'));
        $query=$this->get_data($params);
        if(isset($parameters['download_data'])&&$parameters['download_data'])
        {
            return $query['db_data'];
        }
        $return_data=$query['response_data'];
        $return_data['data']=array();
        $data=$query['db_data'];
        foreach($data as $record)
        {

            $row=array($record['id'],$record['date_time'],$record['ip_address'],$record['table'],$record['record_id'],$record['first_name'].' '.$record['other_names']);
            $url=base_url("edited_data_log/view_log/{$record['id']}");
            $row[]="<a href='{$url}'  title='View' class='open_modal'><i class='fa fa-search'></i></a>";
            $return_data['data'][]=$row;
        }
        return $return_data;
    }
    public function get_activity_log($parameters=array())
    {
        $params=array();
        $params['query_params']=$parameters;
        $params['main_table']='activity_log';
        $params['fields']=array('id','ip_address','activity','users.first_name','users.other_names','date_time');
        $params['join']=array(array('table'=>'users','condition'=>'activity_log.user_id=users.id','type'=>'left'));
        $query=$this->get_data($params);
        if(isset($parameters['download_data'])&&$parameters['download_data'])
        {
            return $query['db_data'];
        }
        $return_data=$query['response_data'];
        $return_data['data']=array();
        $data=$query['db_data'];
        foreach($data as $record)
        {

            $row=array($record['id'],$record['date_time'],$record['ip_address'],$record['first_name'].' '.$record['other_names'],$record['activity']);
            $url=base_url("activity_log/view_log/{$record['id']}");
            $row[]="<a href='{$url}'  title='View' class='open_modal'><i class='fa fa-search'></i></a>";
            $return_data['data'][]=$row;
        }
        return $return_data;
    }
    public function get_products($parameters=array())
    {
        $params=array();
        $params['query_params']=$parameters;
        $params['main_table']='products';
        $params['searchable_fields']=array('id','product_name','price','category','brand');
        $params['fields']=array('id','product_name','price','color','size','description','status','brand','category','created_at');
        //$params['join'] =(array('table' => 'categories', 'condition' => 'categories.id=products.category_id', 'type' => 'left'));

        $query=$this->get_data($params);
        if(isset($parameters['download_data'])&&$parameters['download_data'])
        {
            return $query['db_data'];
        }
        $return_data=$query['response_data'];
        $return_data['data']=array();
        $data=$query['db_data'];
        foreach($data as $record)
        {
            $url=base_url("products/view_product/{$record['id']}");
            $row=array(

                $record['id'],
                $record['product_name'],
                $record['price'],
                $record['color'],
                $record['size'],
                $record['description'],
                $record['status'],
                $record['brand'],
                $record['category'],
                $record['created_at']);





            $url="/products/view_product/{$record['id']}";
            $row[]="<a href='{$url}'  title='View' class='open_modal'><i class='fa fa-eye'></i></a>";
            $url="/products/edit_product/{$record['id']}";
            $row[]="<a href='{$url}'  title='Edit' class='open_modal'><i class='fa fa-edit'></i></a>";
            $url="/products/delete_product/{$record['id']}";
            $row[]="<a href='{$url}'  title='delete' class='open_modal'><i class='fa fa-trash'></i></a>";
            $row[]="<input type='checkbox' class='select_record' data-id='{$record['id']}'>";


            $return_data['data'][]=$row;
        }
        return $return_data;
    }

}
