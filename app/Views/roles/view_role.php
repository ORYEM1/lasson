<?php
echo view('page_heading');
?>
    <table class="table table-striped table-hover">
        <tr><td>ID</td><td><?php echo $record['id'] ?></td></tr>
        <tr><td>Role</td><td><?php echo $record['role'] ?></td></tr>
        <tr><td>Role Type</td><td><?php echo $record['role_type'] ?></td></tr>
        <tr><td>Created by</td><td><?php echo $record['first_name'].' '.$record['last_names'] ?></td></tr>
        <tr><td>Status</td><td><?php echo $record['status'] ?></td></tr>
        <tr><td>Comment</td><td><?php echo $record['comment'] ?></td></tr>
        <tr>
            <td colspan="2">
                <?php
                    $selected_options=(!empty($record['rights']))?explode(',',$record['rights']):array();
                    $options=get_permissions();
                    sort($options);
                    $list=get_checklist_display(array('options'=>$options,'selected_options'=>$selected_options,'columns'=>3));
                ?>
                <fieldset>
                    <legend>Rights</legend>
                    <?php echo $list ?>
                </fieldset>
            </td>
        </tr>
</table>
