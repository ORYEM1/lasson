<?php
echo view('page_heading');
?>
<table class="table table-striped table-hover">
    <?php echo $record[]=''?>;
    <tr><td>ID</td><td><?php echo $record['id'] ?></td></tr>
    <tr><td>First Name</td><td><?php echo $record['first_name'] ?></td></tr>
    <tr><td>Last Names</td><td><?php echo $record['last_names'] ?></td></tr>
    <tr><td>Gender</td><td><?php echo $record['gender'] ?></td></tr>
    <tr><td>Phone Number</td><td><?php echo $record['phone_number'] ?></td></tr>
    <tr><td>Email</td><td><?php echo $record['email'] ?></td></tr>
    <tr><td>Username</td><td><?php echo $record['username'] ?></td></tr>
    <tr><td>Role</td><td><?php echo $record['role'] ?></td></tr>
    <tr><td>Status</td><td><?php echo $statuses[$record['status']]??$record['status'] ?></td></tr>
    <tr><td>Date Registered</td><td><?php echo date('D, d M Y H:i:s T',strtotime($record['date_time_created'])) ?></td></tr>
    <tr><td>Created By</td><td><?php echo $record['created_by'] ?></td></tr>

</table>



