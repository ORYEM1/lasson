<?php

namespace App\Controllers;

class DataTables extends RestrictedBaseController
{
    private \App\Models\DataModel $data_model;

    public function __construct()
    {
        $this->data_model = new \App\Models\DataModel();
    }
    public function get_data($function)
    {
        if($function=='')exit('Function not set');
        $request_data=$_GET;
        if(!isset($request_data['draw']))exit('Draw not set');
        if(!isset($request_data['columns'])||!is_array($request_data['columns']))exit('columns not set');
        if(!isset($request_data['start']))exit('start not set');
        if(!isset($request_data['length']))exit('length not set');
        if(!isset($request_data['search'])||!is_array($request_data['search']))exit('search not set');
        $query_parameters=array();
        $query_parameters['search_term']=trim($request_data['search']['value']);
        $query_parameters['order']=$request_data['order']??array();
        $query_parameters['start']=$request_data['start'];
        $query_parameters['length']=$request_data['length'];
        $query_parameters['draw']=$request_data['draw'];
        $return_data=$this->data_model->$function($query_parameters);
        echo json_encode($return_data);

    }
}
