<?php
echo view('page_heading');
?>


<div id="form_content">
    <?php
    if(!isset($form_type)) $form_type='ajax_form';
    $form_attributes=array('method'=>'post','action'=>$submit_url??'','class'=>'form');
    if($form_type=="ajax_form") {
        $form_attributes['class'] .= " ajax_form";
        $form_attributes['data-action'] = $submit_url ?? '';
    }
    else if($form_type=="upload")
    {
        $form_attributes['enctype'] = "multipart/form-data";
    }
    ?>
    <?php echo open_form($form_attributes)?>

    <table style="width: 99%; margin: auto" class="table table-striped">

        <?php
        $tabindex=1;
        $field_class='field';


        foreach ($form_data as $field)
        {
            //Password Field
            if($field['field_type'] == 'password_field')
            {
                if(isset($field['params']))
                {
                    $params = $field['params'];
                    $id= $params['id'] ?? '';
                    $params['tabindex'] = $tabindex;
                    $text=get_text_field($params);
                    echo "\n<tr>";
                    echo "<td class='label'>";
                    if(isset($params['required']))
                    {
                        echo "<span class='required'>*</span>";
                    }
                    echo $field['label'] ?? '';
                    echo "</td>";
                    echo "<td class='{$field_class}'>";
                    echo $text ?? '';
                    echo"</td>";
                    echo "<td class='password_toggle'><button type='button' class='toggle_password' data-field_id='$id'><i class='fa fa-eye-slash'></i></button></td>";
                    echo "</tr>\n";
                }
            }
            else
            {
                //Text Field
                if($field['field_type'] == 'text_field')
                {
                    if(isset($field['params']))
                    {
                        $params = $field['params'];
                        $params['tabindex'] = $tabindex;
                        $text=get_text_field($params);
                    }
                }

                //Checklist
                else if($field['field_type'] == 'checklist')
                {
                    $field_class='checklist';
                    if(isset($field['options']))
                    {
                        $options = $field['options'];
                        foreach($options as $key=>$option)
                        {
                            $option['tabindex'] = $tabindex;
                            ++$tabindex;
                            $options[$key]=$option;
                        }
                        $optional_params=array();
                        $optional_params['attributes']=$field['attributes']??array();
                        $text=get_checklist($field['name'], $options,$optional_params);
                    }
                    $params=array();
                }

                //Select
                else if($field['field_type'] == 'select_field')
                {
                    if(isset($field['options'])&&isset($field['params']))
                    {
                        $options = $field['options'];
                        $params = $field['params'];
                        $params['tabindex'] = $tabindex;
                        $text=get_select_field($params, $options);
                    }

                }

                //Textarea
                else if($field['field_type'] == 'textarea')
                {
                    if(isset($field['params']))
                    {
                        $params = $field['params'];
                        $params['tabindex'] = $tabindex;
                        $text=get_textarea_field($params);
                    }
                }

                echo "\n<tr>";
                echo "<td class='label'>";
                if(isset($params['required']))
                {
                    echo "<span class='required'>*</span>";
                }
                echo $field['label'] ?? '';
                echo "</td>";
                echo "<td class='{$field_class}' colspan='2'>";
                echo $text ?? '';
                echo"</td>";
                echo "</tr>\n";
            }
            ++$tabindex;
        }
        ?>


    </table>

    <table style="width: 100%;">
        <tr><td style="text-align: left;background-color: lightseagreen;width: 20px"><input type="reset" name="clear" class="button" value="clear"></td>
            <td style="text-align: right;"><input type="submit" name="Add To Order" value="add_to_order" tabindex="<?php echo $tabindex?>" class="button button-primary" /></td></tr>
    </table>
    <?php echo close_form()?>
    <?php if(!empty($error)) echo"<div class='alert alert-danger'>".$error."</div>"?>
    <?php if(!empty($message)) echo"<div class='alert alert-success'>".$message."</div>"?>
</div>
<script>
    function calculateTotalPrice() {
        let quantity = parseFloat(document.getElementById('quantity')?.value || 0);
        let price = parseFloat(document.getElementById('price')?.value || 0);
        let total = quantity * price;
        if (document.getElementById('total_price')) {
            document.getElementById('total_price').value = total.toFixed(2);
        }
    }
</script>



