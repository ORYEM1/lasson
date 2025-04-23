<?php
if(isset($form_title))
{
    $page_heading=$form_title;
}
$modal_title=$page_heading??'';
echo "<p id='modal_title' style='display: none'>{$modal_title}</p>";
?>