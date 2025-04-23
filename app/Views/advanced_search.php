<header>
    <h2>Advance Search</h2>
</header>
<main>
    <form action="" method="post">
        <table class="table table-striped">
            <?php
            if(isset($advanced_search_fields))
            {
                foreach($advanced_search_fields as $field)
                {
                    //Single text field;
                    if($field['field_type'] == 'text_field')
                    {
                        echo "\n<tr>";
                        echo "<td class='label'>";
                        echo $field['label'] ?? '';
                        echo "</td>";
                        echo "<td class='field'>";
                        if(isset($field['params']))
                        {
                            $params = $field['params'];
                            $text=get_text_field($params);
                            echo $text;
                        }
                        echo"</td>";
                        echo "</tr>\n";
                    }
                    //Search Range fields
                    else if($field['field_type'] == 'text_range')
                    {
                        echo "\n<tr>";
                        echo "<td class='label' style='vertical-align: middle'>";
                        echo $field['label'] ?? '';
                        echo "</td>";
                        echo "<td class='field'>";
                        if(isset($field['params'][0]))
                        {
                            $params = $field['params'][0];
                            $params['class']='text_range';
                            $text=get_text_field($params);
                            echo "<div>$text</div>";
                        }
                        echo "<div style='width: 100%; text-align: center'>to</div>";
                        if(isset($field['params'][1]))
                        {
                            $params = $field['params'][1];
                            $params['class']='text_range';
                            $text=get_text_field($params);
                            echo "<div>$text</div>";
                        }
                        echo"</td>";
                        echo "</tr>\n";
                    }
                    //Checklist
                    else if($field['field_type'] == 'checklist')
                    {
                        echo "\n<tr>";
                        echo "<td class='label'>";
                        echo $field['label'] ?? '';
                        echo "</td>";
                        echo "<td>";
                        if(isset($field['options']))
                        {
                            $options = $field['options'];
                            $optional_params['attributes']=$field['attributes']??array();
                            $text=get_checklist($field['name'], $options,$optional_params);
                            echo $text;
                        }
                        echo"</td>";
                        echo "</tr>\n";
                    }
                    //Select
                    else if($field['field_type'] == 'select_field')
                    {
                        echo "\n<tr>";
                        echo "<td class='label'>";
                        echo $field['label'] ?? '';
                        echo "</td>";
                        echo "<td class='field'>";
                        if(isset($field['options'])&&isset($field['params']))
                        {
                            $options = $field['options'];
                            $params = $field['params'];
                            $text=get_select_field($params, $options);
                            echo $text;
                        }
                        echo"</td>";
                        echo "</tr>\n";


                    }
                }
            }
            ?>
            <tr><td style="text-align: left;"><input type="reset" name="Reset" class="button button-secondary" value="Reset"></td>
                <td style="text-align: right;"><input type="submit" name="search" class="button button-primary" value="Search"></td></tr>
        </table>
    </form>
</main>
<footer></footer>