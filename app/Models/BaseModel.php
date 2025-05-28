<?php

namespace App\Models;
use CodeIgniter\Model;
class BaseModel extends Model
{
    protected $db;
    private string $edited_data_log_table;

    public function __construct()
    {
        $this->db=db_connect();
        $this->edited_data_log_table='edited_data_log';
    }
    /*
     * Insert a single record into the database.
     * Param 1: table name
     * Param 2: Associative array of data.
     */
    public function insert_data($table,$data)
    {
        $builder=$this->db->table($table);
        if(!empty($data))
        {
            $builder->insert($data,true);
            return $this->db->insertID();
        }
        return 0;
    }

    /*
     * Insert multiple records into the database at once.
     * Param 1: table name
     * Param 2: Associative arrays of data.
     */
    public function insert_data_batch($table,$data)
    {
        $builder=$this->db->table($table);
        if(!empty($data))
        {
            $builder->insertBatch($data,true);
            return $this->db->affectedRows();
        }
        return 0;
    }

    /*
     * Insert multiple records into the database at once using a transaction query.
     * Param 1: table name
     * Param 2: Associative arrays of data.
     */
    public function insert_data_batch_transaction($table,$data)
    {
        if(!empty($data))
        {
            $builder=$this->db->table($table);
            $this->db->transStart();
            foreach ($data as $item)
            {
                $insert_query = $builder->set($item)->getCompiledInsert($table);
                $this->db->query($insert_query);
            }
            $this->db->transComplete();
            return true;
        }
        return false;
    }

    /*
     * Insert multiple records into the database at once using multiple transaction queries.
     * Param 1: table name
     * Param 2: Associative arrays of data.
     * Param 3: Batch Size (Optional)  - The number of rows per single query.
     */
    public function insert_data_batch_transaction_ext($table,$data,$batch_size=500)
    {
        if(!empty($data))
        {
            $batches=array_chunk($data,$batch_size);
            $this->db->transStart();
            foreach ($batches as $batch)
            {
                $insert_query = $this->generate_insert_query($table, $batch);
                $this->db->query($insert_query);
            }
            $this->db->transComplete();
            return true;
        }
        return false;
    }

    /*
     * Update a single record in the database.
     * Param 1: Array of query parameters. The array contains the following items
     * 1. table => table name. The table in which the data is to be updated e.g. table=>'users'
     * 2. where => array of Where condition parameters as formatted in the CI query builder e.g. where=>array('id'=>1)
     * 3. data => Associative array of the data to be updated e.g. data=>array('first_name'=>'John','last_name'=>'Smith');
     * Param 2: Keep log (Optional) - Set it to true to keep an audit log of the data being edited. Set to false otherwise. The default value is false.
     */
    public function update_data($query_parameters=array(),$keep_log=false)
    {
        if(empty($query_parameters['table'])) return false;
        if(empty($query_parameters['where'])) return false;
        if(empty($query_parameters['data'])) return false;

        //Create audit log entry
        if($keep_log&&isset($_SESSION['user_data']))
        {
            $old_data=$this->get_data(array('table'=>$query_parameters['table'],'where'=>$query_parameters['where'],'limit'=>1),true);
            $update=array();
            foreach($query_parameters['data'] as $key=>$value)
            {
                if( array_key_exists($key,$old_data)&&$old_data[$key]!=$value)
                {
                    $update[$key]=array('old_value'=>$old_data[$key],'new_value'=>$value);
                }
            }

          /*  if(!empty($update))
            {
                $edited_data_log=array('user_id'=>$_SESSION['user_data']['id'],'table'=>$query_parameters['table'],'ip_address'=>$_SERVER['REMOTE_ADDR'],'record_id'=>$old_data['id'],'update'=>json_encode($update),'date_time'=>date('Y-m-d H:i:s'));
            }*/

        }
        $builder=$this->db->table($query_parameters['table']);
        $builder->where($query_parameters['where']);
        $query=$builder->update($query_parameters['data']);
       /* if(!empty($edited_data_log))
        {
            $this->insert_data($this->edited_data_log_table,$edited_data_log);
        }*/
        return $this->db->affectedRows();

    }

    /*
     * Update multiple records in the database.
     * Param 1: table name
     * Param 2: data array.
     * param 3: id (Optional) - Name of unique id field to be used while updating the records.
     * Param 4: Keep log (Optional) - Set it to true to keep an audit log of the data being edited. Set to false otherwise. The default value is false.
     * Param 4: Use Index (Optional) - A preferred table index to be used in the query.
     */
    public function update_data_batch($table,$data,$id='id',$keep_log=false,$use_index='')
    {
        $affected=0;
        if(!empty($data))
        {
            if($keep_log && isset($_SESSION['user_data']))
            {
                $edited_data_log=array();
                $ids=array_column($data,$id);
                $existing_data=$this->get_data(array('table'=>$table,'where_in'=>array($id=>$ids)));
                if(!empty($existing_data))
                {
                    $ids=array_column($existing_data,$id);
                    $existing_data=array_combine($ids,$existing_data);
                }

                foreach($data as $record)
                {
                    $update=array();
                    if(isset($existing_data[$record['id']]))
                    {
                        $old_data=$existing_data[$record['id']];
                        foreach($record as $key=>$value)
                        {
                            if( array_key_exists($key,$old_data)&&$old_data[$key]!=$value)
                            {
                                $update[$key]=array('old_value'=>$old_data[$key],'new_value'=>$value);
                            }
                        }
                        if(!empty($update))
                        {
                            $edited_data_log[]=array('user_id'=>$_SESSION['user_data']['id'],'table'=>$table,'ip_address'=>$_SERVER['REMOTE_ADDR'],'record_id'=>$old_data['id'],'update'=>json_encode($update),'date_time'=>date('Y-m-d H:i:s'));
                        }
                    }
                }
            }
            if(empty($use_index))
            {
                $builder=$this->db->table($table);
                $builder->updateBatch($data,$id);
                $affected=$this->db->affectedRows();
            }
            else
            {
                $update_query=$this->generate_update_query($table,$data,$id,$use_index);
                $this->db->query($update_query);
                $affected=$this->db->affectedRows();
            }
            if(!empty($edited_data_log))
            {
                $this->insert_data_batch($this->edited_data_log_table,$edited_data_log);
            }
            return $affected;
        }
        return false;
    }

    /*
    * Update multiple records in the database using transaction queries.
    * Param 1: table name
    * Param 2: data array.
    * param 3: id (Optional) - Name of unique id field to be used while updating the records.
    */
    public function update_data_batch_transaction($table,$data,$id='id')
    {
        if(!empty($data))
        {
            $this->db->transStart();
            $builder=$this->db->table($table);
            foreach ($data as $item)
            {
                $builder->where($id,$item[$id]);
                $builder->set($item);
                $update_query = $builder->getCompiledUpdate(true);
                $this->db->query($update_query);
            }
            $this->db->transComplete();
            return $this->db->affectedRows();
        }
        return 0;
    }

    /*
    * Update multiple records in the database using multiple transaction queries.
    * Param 1: table name
    * Param 2: data array.
    * param 3: id (Optional) - Name of unique id field to be used while updating the records.
    * Param 4: batch size (Optional) - Set it to true to keep an audit log of the data being edited. Set to false otherwise. The default value is false.
    * Param 4: where (Optional) - A custom where string.
    */
    public function update_data_batch_transaction_ext($table,$data,$index='id',$batch_size=500,$where='',$use_index='')
    {
        if(!empty($data))
        {
            $batches=array_chunk($data,$batch_size);
            $this->db->transStart();
            foreach ($batches as $batch)
            {
                $update_query = $this->generate_update_query($table,$batch,$index,$use_index).$where;
                $this->db->query($update_query);
            }
            $this->db->transComplete();
            return $this->db->affectedRows();
        }
        return false;
    }

    /*
     * Select data from the database
     * Param 1: array of query parameters. These may include the following:
     * 1. table / from (Mandatory) - The main table from which the data is to be selected.
     * 2. distinct (Optional) - Set to true to eliminate any repeating columns from the query result e.g. distinct=> true
     * 3. join (optional) - an array of join tables e.g. join=>array(array('table'=>'table1','condition'=>'main_table.table1_id=table1.id','type'=>'left'),array(.....))
     *    OR join=>array(array('table1','main_table.table1_id=table1.id','left'),array(.....))
     *    NOTE: The join type is optional, and the INNER Join is automatically used when no join type is supplied.
     * 4. fields (optional) - The table columns to be included in the select query. NOTE: All columns that are not from the main table must be qualified with the table name.
     *    e.g. fields=array('id','first_name','last_name','user_roles.role')
     * 5. where (optional) - An array of where parameters as understood by the CI query builder.
     *    e.g. where=>array('id'=>1, 'date >='=>'1995-01-01');
     * 6. where_in (optional) - An array of where_in parameters as understood by the CI query builder.
     *    e.g. where_in=>array('id'=>array(1,2,3,4,5), 'status'=>array('SUCCESSFUL','PENDING'));
     * 7. where_like (optional) - An array of where_like parameters as understood by the CI query builder.
     *    e.g. where_like=>array('name'=>'joh')
     * 8. where_string (Optional) - A custom where string that should be added to the where condition of the query.
     *    e.g. where_string=>"id=1 AND date >= '1995-01-30'";
     * 9. limit (Optional) - The returned records limit as understood by the CI query builder e.g. limit=>100
     * 10. order (Optional) - the columns by which the query results are to be ordered as understood by the CI query builder.
     *    e.g. order=>array(id=>'asc','name'=>'desc');
     * 11. group (Optional) - An array of grouping columns
     *    e.g. group=>array('date','country')
     * 12. index (Optional) - The index to be used in the select query
     *    e.g. index=>'PRIMARY'
     * 13. return_sql (Optional) - Set to true id you want to return the generated sql statement rather than run it
     * 14. use_cache (Optional) - Set to true if you want the retrieved data to be cached.
     * 15. cache_expiry (Optional) - The expiry time in seconds for the cached data to expire.
     * 16. auto_escape (Optional) - Set it to false to disable escaping of query fields.
     * Param 2: assoc - Set to true to return a single record as an associative array.
     */
    public function get_data($query_parameters,$assoc=false)
    {
        if(isset($query_parameters['from']))
        {
            $query_parameters['table']=$query_parameters['from'];
        }
        if(!isset($query_parameters['table'])) return array();
        $builder=$this->db->table($query_parameters['table']);
        //print_r($query_parameters);exit;
        if(isset($query_parameters['distinct']) && $query_parameters['distinct'])
        {
            $builder->distinct($query_parameters['distinct']);
        }
        if(isset($query_parameters['fields']))
        {
            $fields=implode(',',qualify_columns($query_parameters['table'],$query_parameters['fields']));


            if(isset($query_parameters['auto_escape']))
            {
                if(!$query_parameters['auto_escape'])
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

        if(isset($query_parameters['join'])&& is_array($query_parameters['join']))
        {
            foreach($query_parameters['join'] as $join)
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

        if(isset($query_parameters['where']))
        {
            $query_parameters['where']=qualify_search_data($query_parameters['table'],$query_parameters['where']);
            $builder->where($query_parameters['where']);
        }
        if(isset($query_parameters['where_string']))
        {
            $builder->where($query_parameters['where_string']);
        }
        if(isset($query_parameters['where_in'])&&is_array($query_parameters['where_in']))
        {
            $query_parameters['where_in']=qualify_search_data($query_parameters['table'],$query_parameters['where_in']);
            foreach($query_parameters['where_in'] as $field=>$values)
            {
                $where=" {$field} IN ('".implode("','",$values)."') ";
                $builder->where($where);
            }
        }
        if(isset($query_parameters['where_like'])&&is_array($query_parameters['where_like']))
        {
            $query_parameters['where_like']=qualify_search_data($query_parameters['table'],$query_parameters['where_like']);
            $builder->like($query_parameters['where_like']);
        }
        if(isset($query_parameters['order'])&&isset($query_parameters['order_direction']))
        {
            $builder->orderBy($query_parameters['order'],$query_parameters['order_direction']);
        }

        else if(isset($query_parameters['order']))
        {
            if(is_array($query_parameters['order']))
            {
                foreach($query_parameters['order'] as $field=>$value)
                {
                    $builder->orderBy($field,$value);
                }
            }
            else
            {
                $builder->orderBy($query_parameters['order']);
            }
        }
        if(isset($query_parameters['group']))
        {
            if(is_array($query_parameters['group']))
            {
                foreach($query_parameters['group'] as $group)
                {
                    $builder->groupBy($group);
                }
            }
            else
            {
                $builder->groupBy($query_parameters['group']);
            }
        }


        if(isset($query_parameters['limit'])&& is_numeric($query_parameters['limit'])&&isset($query_parameters['offset'])&& is_numeric($query_parameters['offset']))
        {
            $builder->limit($query_parameters['limit'],$query_parameters['offset']);
        }
        else if(isset($query_parameters['limit'])&& is_numeric($query_parameters['limit']))
        {
            $builder->limit($query_parameters['limit']);
        }
        $sql=$builder->getCompiledSelect();
        if(isset($query_parameters['index']))
        {
            $sql=$builder->getCompiledSelect();
            $sql=str_replace("FROM `{$query_parameters['table']}`","FROM `{$query_parameters['table']}` USE INDEX({$query_parameters['index']})",$sql);
        }
        if(isset($query_parameters['return_sql'])&&$query_parameters['return_sql'])
        {
            return $sql;
        }
        if(isset($query_parameters['use_cache'])&&$query_parameters['use_cache']===true&&getenv('cache.active'))
        {
            $expiry=$query_parameters['cache_expiry']??30;
            $data=$this->get_cached_data($sql,$expiry);
        }
        else
        {
            $query=$this->db->query($sql);
            $data=$query->getResultArray();

            //print_r($data);exit;
        }
        if($assoc)
        {
            if(!empty($data))
            {
                return $data[0];
            }
        }
        return $data;
    }

    /*
     * Select data from the database for very long where_in queries
     * Param 1: array of query parameters. These may include the following:
     * 1. table / from (Mandatory) - The main table from which the data is to be selected.
     * 2. distinct (Optional) - Set to true to eliminate any repeating columns from the query result e.g. distinct=> true
     * 3. join (optional) - an array of join tables e.g. join=>array(array('table'=>'table1','condition'=>'main_table.table1_id=table1.id','type'=>'left'),array(.....))
     *    OR join=>array(array('table1','main_table.table1_id=table1.id','left'),array(.....))
     *    NOTE: The join type is optional, and the INNER Join is automatically used when no join type is supplied.
     * 4. fields (optional) - The table columns to be included in the select query. NOTE: All columns that are not from the main table must be qualified with the table name.
     *    e.g. fields=array('id','first_name','last_name','user_roles.role')
     * 5. where (optional) - An array of where parameters as understood by the CI query builder.
     *    e.g. where=>array('id'=>1, 'date >='=>'1995-01-01');
     * 6. where_in (optional) - An array of where_in parameters as understood by the CI query builder.
     *    e.g. where_in=>array('id'=>array(1,2,3,4,5), 'status'=>array('SUCCESSFUL','PENDING'));
     * 7. limit (Optional) - The returned records limit as understood by the CI query builder e.g. limit=>100
     * 8. order (Optional) - the columns by which the query results are to be ordered as understood by the CI query builder.
     *    e.g. order=>array(id=>'asc','name'=>'desc');
     * 9. group (Optional) - An array of grouping columns
     *    e.g. group=>array('date','country')
     * 10. index (Optional) - The index to be used in the select query
     *    e.g. index=>'PRIMARY'
     * 11. return_sql (Optional) - Set to true id you want to return the generated sql statement rather than run it
     * 12. use_cache (Optional) - Set to true if you want the retrieved data to be cached.
     * 13. cache_expiry (Optional) - The expiry time in seconds for the cached data to expire.
     * Param 2: assoc - Set to true to return a single record as an associative array.
     */
    public function get_data_in($query_parameters,$assoc=false,$return_count=false,$return_query=false)
    {
        if(isset($query_parameters['from']))
        {
            $query_parameters['table']=$query_parameters['from'];
        }
        if(!isset($query_parameters['table'])) return array();
        if(!isset($query_parameters['where_in'])) return array();
        $fields='*';
        if(isset($query_parameters['fields']))
        {
            $fields=implode(',',qualify_columns($query_parameters['table'],$query_parameters['fields']));
        }
        $sql="SELECT";

        if(isset($query_parameters['distinct']) && $query_parameters['distinct'])
        {
            $sql.=" DISTINCT ";
        }

        $sql.=" {$fields} FROM {$query_parameters['table']} ";

        if(isset($query_parameters['index']))
        {
            $sql.=" USE INDEX({$query_parameters['index']}) ";
        }

        if(isset($query_parameters['join'])&& is_array($query_parameters['join']))
        {
            foreach($query_parameters['join'] as $join)
            {
                if(isset($join[0]) && isset($join[1]) && isset($join[2]))
                {
                    $sql.=" {$join[2]} JOIN {$join[0]} ON({$join[1]}) ";
                }
                else if(isset($join[0]) && isset($join[1]))
                {
                    $sql.=" JOIN {$join[0]} ON({$join[1]}) ";
                }
                else if(isset($join['table']) && isset($join['condition']) && isset($join['type']))
                {
                    $sql.=" {$join['type']} JOIN {$join['table']} ON({$join['condition']}) ";
                }
                else if(isset($join['table']) && isset($join['condition']))
                {
                    $sql.=" JOIN {$join['table']} ON({$join['condition']}) ";
                }
                else if(isset($join['table']) && isset($join['on']) && isset($join['type']))
                {
                    $sql.=" {$join['type']} JOIN {$join['table']} ON({$join['on']}) ";
                }
                else if(isset($join['table']) && isset($join['on']))
                {
                    $sql.=" JOIN {$join['table']} ON({$join['on']}) ";
                }
            }
        }


        $where_added=false;

        if(isset($query_parameters['where'])&&is_array($query_parameters['where']))
        {
            $query_parameters['where']=qualify_search_data($query_parameters['table'],$query_parameters['where']);
            foreach($query_parameters['where'] as $field=>$value)
            {
                $field_parts=explode(' ',$field);
                if(count($field_parts)==2)
                {
                    if(!$where_added)
                    {
                        $sql.=" WHERE {$field_parts[0]}{$field_parts[1]}'{$value}' ";
                    }
                    else
                    {
                        $sql.=" AND {$field_parts[0]}{$field_parts[1]}'{$value}' ";
                    }
                }
                else
                {
                    if(!$where_added)
                    {
                        $sql.=" WHERE {$field}='{$value}' ";
                    }
                    else
                    {
                        $sql.=" AND {$field}='{$value}' ";
                    }
                }
                $where_added=true;
            }
        }

        if(is_array($query_parameters['where_in']))
        {
            $query_parameters['where_in']=qualify_search_data($query_parameters['table'],$query_parameters['where_in']);
            foreach($query_parameters['where_in'] as $field=>$values)
            {
                $where=" {$field} IN ('".implode("','",$values)."') ";
                if(!$where_added)
                {
                    $sql.=" WHERE ".$where;
                    $where_added=true;
                }
                else
                {
                    $sql.=" AND ".$where;
                }
            }
        }

        if(isset($query_parameters['order']))
        {
            $sql.=" ORDER BY ";
            if(is_array($query_parameters['order']))
            {
                foreach($query_parameters['order'] as $field=>$value)
                {
                    $sql.=" {$field} {$value}";
                }
            }
            else
            {
                $sql.="{$query_parameters['order']} ";
            }
        }
        if(isset($query_parameters['group']))
        {
            $sql.=" GROUP BY ";
            if(is_array($query_parameters['group']))
            {
                foreach($query_parameters['group'] as $group)
                {
                    $sql.=" {$group} ";
                }
            }
            else
            {
                $sql.=" {$query_parameters['group']} ";
            }
        }

        else if(isset($query_parameters['limit'])&& is_numeric($query_parameters['limit']))
        {
            $sql.=" LIMIT {$query_parameters['limit']} ";
        }
        if(isset($query_parameters['return_sql'])&&$query_parameters['return_sql'])
        {
            return $sql;
        }
        if(isset($query_parameters['use_cache'])&&$query_parameters['use_cache']===true&&getenv('cache.active'))
        {
            $expiry=$query_parameters['cache_expiry']??30;
            $data=$this->get_cached_data($sql,$expiry);
        }
        else
        {
            $query=$this->db->query($sql);
            $data=$query->getResultArray();
        }
        if($assoc)
        {
            if(!empty($data))
            {
                return $data[0];
            }
        }
        return $data;
    }

    /*
     * Inserts data from a file into a table.
     * Param 1: table - The name of the table to be loaded with the data.
     * Param 2: file - The path to the file containing the data.
     * below is a sample of how data may appear in the file:
     *
     * 1|John|Smith
     * 2|Bill|Clinton
     * 3|Alex|Williams
     *
     * Each of the above is a single row.
     * param 3: columns - An array of the columns in the order they appear in the source file
     * e.g. array('id','first_name','last_name') - This is based on the sample data above.
     * Param 4: column_separator (Optional) - The character separating the data of different columns in the file.
     */
    public function load_data($table,$file,$cols,$col_sep='|')
    {
        $file = str_replace('\\', '/', $file);
        $columns=implode(',',$cols);
        $sql="LOAD DATA LOCAL INFILE '{$file}' INTO TABLE {$table} FIELDS TERMINATED BY '{$col_sep}' ({$columns})";
        $this->db->query($sql);
    }

    /*
     * Updates data in the destination table with data from the source table using a join query
     * Param 1: array of query parameters. The array must contain the following:
     * 1. source_table e.g. source_table=>'source_table'
     * 2. destination_table e.g. destination_table=>'destination_table'
     * 3. joining_condition e.g. joining_condition=>'source_table.id=destination_table.source_id'
     * 4. column_matching - An array matching the columns of the source table to the columns of the destination table using source=>destination
     * e.g. column_matching=array('id'=>'id','first_name'=>'first_name','other_names'=>'last_name',.........)
     * NOTE: The column names may be different as long as they are matched properly.
     * Param 2: truncate_source - Set it to true to truncate the source table after the update.
     */
    public function update_from_table($params,$truncate_source=true)
    {
        $cols=array();
        $source_table=$params['source_table'];
        $destination_table=$params['destination_table'];
        foreach($params['column_matching'] as $source_col=>$destination_col)
        {
            $cols[] = "{$destination_table}.{$destination_col}={$source_table}.{$source_col}";
        }
        $cols=implode(',',$cols);
        $sql="UPDATE {$destination_table} JOIN {$source_table} ON ({$params['joining_condition']}) SET {$cols}";
        $this->db->query($sql);
        if($truncate_source)
        {
            $sql="TRUNCATE TABLE {$params['source_table']}";
            $this->db->query($sql);
        }
        return true;
    }

    /*
     * Updates numeric values in the database by adding or the supplied value to the existing value.
     * It is very important for updating user balances in situations where the balance might be affected by different processes almost simultaneously.
     * NOTE: Each ID must appear at most once for this method to work.
     * Param 1: table - The database table containing the values to be updated.
     * Param 2: field - The name of the column to be updated.
     * Param 3: data - An array containing the values to be updated.
     * e.g. data=array(array('id'=>1,amount=>1000),'id'=>2,'amount'=>-500)
     * Param 4: id (Optional) - Name of unique id field to be used while updating the records.
     * Param 5: Use Index (Optional) - A preferred table index to be used in the query.
     */
    public function update_values($table,$field,$data=array(),$unique_key='id',$use_index='')
    {
        if(empty($data)) return false;
        //Check if a single record has been sent in an associative array.
        if(isset($data[$unique_key]))
        {
            $data=array($data);
        }
        $unique_keys=array();
        $sql="UPDATE `{$table}` SET `{$field}` = CASE ";
        if($use_index!='')
        {
            $sql="UPDATE `{$table}` USE INDEX({$use_index}) SET `{$field}` = CASE ";
        }
        foreach($data as $row)
        {
            $sql.=" WHEN id='{$row[$unique_key]}' THEN {$field}+{$row['amount']}";
            $unique_keys[]=$row[$unique_key];
        }
        $sql.=" ELSE `{$field}` END WHERE `{$unique_key}` IN('".implode("','",$unique_keys)."')";
        $this->db->query($sql);
    }

    /*
     * Generates and returns and INSERT SQL string.
     * Param 1: table - The database table in which the data is to be inserted
     * Param 2: data - An array of data to be inserted in the database table.
     */
    private function generate_insert_query($table,$data)
    {
        if(!empty($data))
        {
            $row_count=count($data);
            $counter=1;
            $data=array_values($data);
            $row=$data[0];
            $cols=array_keys($row);
            $cols='`'.implode('`,`',$cols).'`';
            $sql="INSERT INTO {$table} ({$cols}) VALUES";
            foreach($data as $row)

            //print_r($data);exit;
            {
                $values=array_values($row);

                $values='"'.implode('","',$values).'"';
                $sql.="({$values})";
                if($counter<$row_count)
                {
                    $sql.=',';
                }
                ++$counter;
            }
            return $sql;
        }
        return false;
    }

    /*
     * Generates and returns and UPDATE SQL string.
     * Param 1: table - The database table in which the data is to be updated
     * Param 2: data - An array of data to be updated in the database table.
     * Param 4: index - Name of the unique indexed column to be used for updating records e.g. ID.
     * Param 5: Use Index (Optional) - A preferred table index to be used in the query.
     */
    public function generate_update_query($table,$data,$index,$use_index='')
    {
        if(!empty($data))
        {
            $indices=array_column($data,$index);
            $data=array_values($data);
            $row=$data[0];
            $col_count=count($row)-1;
            $counter=1;
            $sql="UPDATE `{$table}` SET ";
            if($use_index!='')
            {
                $sql="UPDATE `{$table}` USE INDEX({$use_index}) SET ";
            }
            $cols=array();
            foreach($row as $key=>$value)
            {
                $value=$this->db->escape($value);
                if($key==$index)
                {
                    continue;
                }
                $col=array_column($data,$key);
                $col=array_combine($indices,$col);
                $sql_section='';
                foreach($col as $id=>$update_value)
                {
                    if(!empty($update_value))
                    {
                        if(empty($sql_section))
                        {
                            $sql_section.="`{$key}` = (CASE `{$index}` ";
                        }
                        $sql_section.=" WHEN {$id} THEN \"{$update_value}\" ";
                    }
                }
                if(!empty($sql_section))
                {
                    $sql_section.=" END)";
                }

                $sql.=$sql_section;

                if($counter<$col_count && !empty($sql_section))
                {
                    $sql.=',';
                }
                ++$counter;
            }
            $sql=trim($sql,',');
            $sql.=" WHERE {$index} IN ('".implode("','",$indices)."') ";
            return $sql;
        }
    }

    /*
     * Deletes data from a database table.
     * Param 1: table.
     * Param 2: array params - An array of parameters to be used in the delete query. It can include the where and where_in arrays,
     *
     */
    public function delete_data($table,$params)
    {
        if(empty($params))
        {
            return false;
        }
        $builder=$this->db->table($table);
        if(!empty($params['where']))
        {
            $builder->where($params['where']);
        }
        if(isset($params['where_string']))
        {
            $builder->where($params['where_string']);
        }
        if(isset($params['where_in'])&&is_array($params['where_in']))
        {
            foreach($params['where_in'] as $field=>$values)
            {
                $where=" {$field} IN ('".implode("','",$values)."') ";
                $builder->where($where);
            }
        }
        $builder->delete();
        return $this->db->affectedRows();
    }

    /*
     * Inserts results of an SQL query into a database table.
     * Param 1: The SQL string that shall be used to select the data.
     * Param 2: The table in which the data will be inserted.
     * Param 3: The columns in which the data shall be inserted in the same order as they are returned by the select query.
     */
    public function insert_query_result($query,$table,$table_cols)
    {
        if(is_array($table_cols))
        {
            $table_cols=implode(',',$table_cols);
        }
        $sql="INSERT INTO {$table}({$table_cols}) ({$query})";
        if($this->db->query($sql))
        {
            return true;
        }
        return false;
    }

    /*
     * Gets database values to be used in a form select or checklist.
     * Param 1: Array parameters to be used by the get data function.
     * Param 2: Name of column that contains the value content for the form.
     * Param 3: Name of colum that contains the key for the value;
     */
    public function get_form_options($query_params,$value_column,$key_column)
    {
        if(!isset($query_params['fields']))
        {
            $fields=array($key_column,$value_column);
            $query_params['fields']=$fields;
        }
        $data=$this->get_data($query_params);
        $result=array();
        foreach($data as $row)
        {
            $result[$row[$key_column]]=$row[$value_column];
        }
        return $result;
    }

    /*
     * Gets database values to be used in a form datalist.
     * Param 1: Array parameters to be used by the get data function.
     * Param 2: Name of column that contains the value content for the form.
     * Param 3: Name of colum that contains the key for the value;
     */
    public function get_form_datalist($query_params,$value_column)
    {
        if(!isset($query_params['fields']))
        {
            $fields=array($value_column);
            $query_params['fields']=$fields;
        }
        $query_params['distinct']=true;
        $data=$this->get_data($query_params);
        return array_column($data,$value_column);
    }

    /*
     * Gets data from cache and also updates cached data.
     * Param 1: SQL statement that is used to get the data from the database.
     * Param 2: Expiry time in seconds for the cached data.
     */
    public function get_cached_data($sql,$expiry=10)
    {
        $hash=sha1($sql.getenv('cache.key'));
        $cache=new \App\Libraries\Cache();
        if($data=$cache->get($hash))
        {
            return json_decode($data,true);
        }
        $query=$this->db->query($sql);
        $data= $query->getResultArray();
        $cache->set($hash,json_encode($data),$expiry);
        return $data;
    }

    public function cancel_data($table,$params)
    {
        if (empty($params)) {
            return false;
        }
        $builder = $this->db->table($table);
        if (!empty($params['where'])) {
            $builder->where($params['where']);
        }
        if (isset($params['where_string'])) {
            $builder->where($params['where_string']);
        }
        if (isset($params['where_in']) && is_array($params['where_in'])) {
            foreach ($params['where_in'] as $field => $values) {
                $where = " {$field} IN ('" . implode("','", $values) . "') ";
                $builder->where($where);
            }
        }
        $update = ['status' => 'calcelled'];
        $builder->update($table, $update);
        return $this->db->affectedRows();
    }
}
