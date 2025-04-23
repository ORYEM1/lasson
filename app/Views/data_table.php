<?php
$request = \Config\Services::request();
?>

<div id="page_data">
    <div id="page_heading">
        <?php
        if(isset($page_heading))
        {
            echo "<h1>".$page_heading."</h1>";
        }
        ?>
    </div>
    <div id="data_table">
        <?php
        if(isset($data_header))
        {
            ?>
            <table id="data" class="display data" style="width:100%">
                <thead>
                <tr>
                    <?php
                    $data_def=array();
                    foreach ($data_header as $header)
                    {
                        echo "<th>".$header['name']."</th>";
                        if(isset($header['data_key']))
                        {
                            $def=array('data_key'=>$header['data_key']);
                            if(isset($header['data_type']))
                            {
                                $def['data_type']=$header['data_type'];
                            }
                            if(isset($header['data_precision']))
                            {
                                $def['data_precision']=$header['data_precision'];
                            }
                            $data_def[]=$def;
                        }
                    }
                    ?>
                </tr>
                </thead>
                <tbody>
                <?php
                if(isset($table_data))
                {
                    foreach($table_data as $row)
                    {
                        echo"<tr>";
                        foreach ($data_def as $cell)
                        {
                            echo"<td>";
                            if(isset($cell['data_type']) && $cell['data_type']=='number' && isset($cell['data_precision']))
                            {
                                echo number_format($row[$cell['data_key']], $cell['data_precision']);
                            }
                            else if(isset($cell['data_type']) && $cell['data_type']=='number')
                            {
                                echo number_format($row[$cell['data_key']]);
                            }
                            else
                            {
                                echo $row[$cell['data_key']];
                            }
                            echo"</td>";
                        }
                        echo"</tr>";
                    }
                }
                ?>
                </tbody>

            </table>
            <?php
        }
        ?>
        <?php
        if(isset($data_footer))
        {
            ?>
            <ul id="data_footer">
                <?php
                foreach ($data_footer as $item)
                {
                    $params=array();
                    if(isset($item['params']))
                    {
                        foreach($item['params'] as $param=>$value)
                        {
                            $params[]="{$param}='{$value}'";
                        }

                    }
                    $params=implode(' ',$params);
                    echo "\n<li>";
                    if($item['element']=='button')
                    {
                        echo "<button ".$params.">".$item['text']."</button>";
                    }
                    else if($item['element']=='link')
                    {
                        echo "<a ".$params.">".$item['text']."</a>";
                    }
                    else if($item['element']=='select')
                    {
                        echo" <select ".$params.">";
                        echo "<option value=''>With Selected Items</option>";
                        foreach($item['options'] as $option=>$value)
                        {
                            echo "<option value='{$value}'>".$option."</option>";
                        }
                        echo "</select>";
                    }
                    echo "</li>\n";
                }

                ?>
            </ul>

            <?php
        }
        ?>
    </div>
</div>

