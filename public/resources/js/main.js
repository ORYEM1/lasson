$(document).keydown(function(e){
   var code = e.keyCode ? e.keyCode : e.which;
   if(code==27)
   {
      $("#processing").hide();
   }
   });
$(document).on('click','.open_modal',function(event){
      var url=$(this).attr('href');
      event.preventDefault();
      this.blur();
      open_modal(url);
  })
$(document).on('change','.linked_input',switch_select_options);
$(document).on('change','.linked_checklist',switch_checklist_options);
$(document).on('change','#attachment',upload_file);
$(document).on('submit','#upload_transactions',function(event){
    event.preventDefault();
    submit_transactions();
})
$(document).keydown(function(e){
    let code = e.keyCode ? e.keyCode : e.which;
    if(code==27)
    {
        $("#processing").hide();
    }
});
function open_modal(url)
{
      $('#processing').show();
      $.ajax({
            url:url,
            type:'get',
            timeout:15000,
            success:function(html)
            {
                $('#processing').hide();
                $('#jquery_modal main').html(html);
                let title=$('#modal_title').text();
                if(typeof(title)!="undefined")
                {
                    $('#jquery_modal header').html('<h3>'+title+'</h3>');
                }
                $("#jquery_modal footer").html('');
                $('#jquery_modal').modal();

            },
            error:function(x,t,m)
            {
                $('#processing').hide();
                show_ajax_error(t);
            }
        })
}
$(document).on('click','.check_all_boxes',function(event){
    check_all_boxes(this);
});

$(document).on('keyup','.number',function(){
    $(this).val(number_format(($(this).val()).replace(/\,/g,"")));
    var amount=$(this).val();
    amount=amount.replace(/\,/g,"");
    if(isNaN(amount)&&amount.length!=0)
    {
        $(this).val('');
        return false;
    }
})

$(document).on('submit','.ajax_form',function(event){
        event.preventDefault();
        submit_ajax_form(this);
    })

function is_json(str)
{
        try
        {
            JSON.parse(str);
        }
        catch (e)
        {
            return false;
        }
        return true;
}
function number_format(num,decimal_places)
{
    return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
}

function submit_ajax_form(ajax_form)
{
    var data=$(ajax_form).serialize();
    if(data.length>0)
    {
        data+='&submit=submit';
    }
    else
    {
        data+='submit=submit';
    }
    var url=$(ajax_form).data('action');
    $('#processing').show();
    $.ajax({
            url:url,
            data:data,
            type:'post',
            timeout:30000,
            success:function(data)
            {
                //alert(data);
                $('#processing').hide();
                if(is_json(data))
                {
                    data=$.parseJSON(data);

                    if(data.status==1)
                    {
                        show_info(data.msg,'Successful');
                        var table = $('#data').DataTable();
                        table.draw();
                    }
                    else
                    {
                        show_error(data.msg);
                    }
                }
                else
                {
                    show_error("An internal system error occurred while processing the request");
                }
            },
            error:function(x,t,m)
            {
                $('#processing').hide();
                show_ajax_error(t);
            }
        })
}

function process_selected_records(handler)
{
    var record_ids=[];
   $('.select_record:checked').each(function(){
        record_ids.push($(this).data('id'));
   })
   if(record_ids.length==0)
   {
       show_error('You have not selected any records');
       return false;
   }
    record_ids=JSON.stringify(record_ids);
    data={record_ids:record_ids}
    url="/rpc/"+handler;
    $('#processing').show();
    $.ajax({
    url:url,
    data:data,
    type:'post',
    timeout:120000,
    success:function(data)
    {
        $('#processing').hide();
        if(is_json(data))
        {
            data=$.parseJSON(data);
            if(data.status==1)
            {
                show_info(data.msg,'Successful');
                var table = $('#data').DataTable();
                table.draw();
                return false;
            }
            else
            {
                show_error(data.msg);
                return false;
            }
        }
        else
        {
            show_error('Internal System Error');
            return false;
        }
    },
    error:function(x,t,m)
    {
        $('#processing').hide();
        show_ajax_error(x,t,m)
        return false;
    }
 });

}

function show_ajax_error(t)
{
    let title='Ajax Error';
    if(t==='error') show_error('Server error.',title);
    else if(t==='timeout') show_error('Connection timed out',title);
    else if(t==='persererror') show_error('Ajax parse error.',title);
    else show_error('Unknown error',title);
    return false;
}

function show_error(text,title='Error')
{
   let str='<div class="error">'+text+'</div>';
   if($('div.current .modal').length)
   {
       $('div.current .modal footer').html(str);
   }
   else
   {

       $('#jquery_modal header').html('<h3>'+title+'</h3>');
       $('#jquery_modal main').html(str);
       $("#jquery_modal footer").html('');
       $('#jquery_modal').modal();
   }
}

function show_info(text,title='Info')
{
    let str='<div class="info">'+text+'</div>';
    if($('div.current .modal').length)
    {
        $('div.current .modal header').html('<h3>'+title+'</h3>');
        $('div.current .modal main').html(str);
        $('div.current .modal footer').html('');
    }
    else
    {

        $('#jquery_modal header').html('<h3>'+title+'</h3>');
        $('#jquery_modal main').html(str);
        $("#jquery_modal footer").html('');
        $('#jquery_modal').modal();
    }
}

function check_all_boxes(element)
{
    var target_class=$(element).data('target_class');
    if(typeof(target_class)=='undefined')
    {
        return false;
    }
    var selector='.'+target_class;
    if(element.checked) { // check select status
        $(selector).each(function() { //loop through each checkbox
            this.checked = true;  //select all checkboxes with class "checkbox1"
        });
    }
    else{
        $(selector).each(function() { //loop through each checkbox
            this.checked = false; //deselect all checkboxes with class "checkbox1"
        });
    }
}

function show_confirm(msg,handler,param='')
{
    let str="<div id='confirm'><p>"+msg+"</p>"+"<P style='margin-top: 20px; padding: 5px;'><button class='button button-primary' data-action='yes' data-handler='"+handler+"'"+" data-param='"+param+"'"+" style='float: right; width: 40px;'>Yes</button>"+"<a class='button button-secondary' rel='modal:close' href='#' style='float: left; width: 40px;'>No</a>"+"</P>"
    $('#jquery_modal header').html('<h3>Please Confirm</h3>');
    $('#jquery_modal main').html(str);
    $("#jquery_modal footer").html('');
    $('#jquery_modal').modal();
}

$(document).on('click','#confirm .button',function(){
    var action=$(this).data('action');
    $('#modal').modal('toggle');
    if(action==='yes')
    {
        var handler=$(this).data('handler');
        var param=$(this).data('param');
        if(param==='')
        {
            window[handler]();
        }
        else
        {
            window[handler](param);
        }
    }
    return false;
})

function toggle_password(element)
{
    var field_id=$(element).data('field_id');
    var x = document.getElementById(field_id);
    if (x.type === "password")
    {
        x.type = "text";
        $(element).html('<i class="fa fa-eye">');
    } else
    {
        x.type = "password";
        $(element).html('<i class="fa fa-eye-slash">');
    }
}
$(document).on('click','.toggle_password',function(){
    toggle_password(this);
})

$(document).on('change','#selected_records_action',function(){
    var param=$(this).val();
    if(param=='')
    {
        return false;
    }
    var action = param.replace(/_/g, " ");
    var msg="Do you want to <u><strong>"+action+"</strong></u>?";
    show_confirm(msg,'process_selected_records',param);
})
function switch_select_options()
{
    let current_value=$(this).val();
    let linked_id = $(this).data('linked_id');
    if(typeof(linked_id)=='undefined')
    {
        return false;
    }
    linked_id='#'+linked_id;
    let source = $(linked_id).data('source');
    if(typeof(source)=='undefined')
    {
        return false;
    }
    let url =source+'?current_value='+current_value;
    let initial_value=$(linked_id).data('initial_value');
    if(typeof(initial_value)!='undefined')
    {
        url+='&initial_value='+initial_value;
    }
    $(linked_id).load(url);
}

function switch_checklist_options()
{
    let current_value=$(this).val();
    let linked_id = $(this).data('linked_id');
    if(typeof(linked_id)=='undefined')
    {
        return false;
    }
    linked_id='#'+linked_id;
    let name=$(linked_id).data('name');
    if(typeof(name)=='undefined')
    {
        return false;
    }
    let source = $(linked_id).data('source');
    if(typeof(source)=='undefined')
    {
        return false;
    }
    source+="?name="+name;
    if(current_value.length>0)
    {
        source+="&value="+current_value;
    }
    let initial_value=$(linked_id).data('initial_value');
    if(typeof(initial_value)!='undefined')
    {
        source+='&initial_value='+initial_value;
    }
    $(linked_id).load(source);
}

function upload_file(event)
{
    let files = event.target.files;
    let data = new FormData();
    $.each(files, function(key, value)
    {
        var ext=value.name.split('.').pop();
        if(!is_allowed_extension(ext))
        {
            show_error("The uploaded file format is not supported");
            exit();
        }
        if(value.size>52428800)
        {
            show_error("The selected file has exceeded the maximum allowed size of 50MB");
            exit();
        }
        data.append(key, value);
    });
    var url='/collection_requests/upload_file';
    $('#uploading').show();
    $.ajax({
        url: url,
        type: 'post',
        data: data,
        cache: false,
        dataType: 'json',
        processData: false, // Don't process the files
        contentType: false, // Set content type to false as jQuery will tell the server its a query string request
        success: function(data)
        {
            if(data.status==1)
            {
                $('#uploading').hide();
                $('#file_name').val(data.file_name);
                $('#num_rows').val(data.num_rows);
                $('#rows_processed').val('0');
                $('#rows_remaining').val(data.num_rows);
                $('#batches_processed').val('0');
                $('#skip_rows').val('0');
                $('#process_file').removeClass('disabled');
                $('#process_file').removeAttr('disabled');
                $('#process_file').show();
            }
            else
            {
                show_error(data.msg);
                $('#uploading').hide();
            }
        },
        error:function(x,t,m)
        {
            $('#uploading').hide();
            show_ajax_error(t);
        }
    });
}

function is_allowed_extension(ext)
{
    var allowed=['csv'];
    var i;
    for (i = 0; i < allowed.length; i++) {
        if(ext==allowed[i]) return true
    }
    return false;
}

function submit_transactions() {
    var data = $('#upload_transactions').serialize();
    if (data.length > 0) {
        data += '&submit=submit';
    } else {
        data += 'submit=submit';
    }
    var url = $('#upload_transactions').data('action');
    $('#processing').show();
    $.ajax({
        url: url,
        data: data,
        type: 'post',
        timeout: 30000,
        success: function (data) {
            //alert(data);
            $('#processing').hide();
            if (is_json(data)) {
                data = $.parseJSON(data);
                $('#processing').hide();
                if (data.status == 1) {
                    show_info(data.msg, 'Successful');
                    $('#rows_processed').val(data.rows_processed);
                    $('#rows_remaining').val(data.rows_remaining);
                    $('#batches_processed').val(data.batches_processed);
                    $('#skip_rows').val(data.skip_rows);
                } else {
                    show_error(data.msg);
                }
            } else {
                show_error("An internal system error occured while processing the request");
            }
        },
        error: function (x, t, m) {
            $('#processing').hide();
            show_ajax_error(t);
        }
    });

}
$(document).ready(function() {
        $('#data').on('click', '.clickable-row', function() {
            const url = $(this).data('href');
            if (url) {
                window.location.href = url;
            }
        });
    });

    $(document).ready(function()
    {

        updateUnitPrice();
        calculateTotals();
        $('#addRow').click(function()
        {
            var row = $('#productTable tbody tr:first').clone();
            row.find('input').val('');
            $('#productTable tbody').append(row);
            updateUnitPrice();
            calculateTotals();
        });
        $(document).on('click','.removeRow', function()
        {
            if($('#productTable tbody tr').length>1)
            {
                $(this).closest('tr').remove();
                calculateTotals();
            }
        });

        $(document).on('input','.quantity',function()
        {
            calculateTotals();
        });
        function updateUnitPrice()
        {
            $('#productTable tbody tr').each(function()
            {
                var selected = $(this).find('.product-select option:selected');
                var price = parseFloat(selected.date('price')) || 0;
                $(this).find('.unit-price').val(price);
            });
        }

        function calculateTotals()
        {
            var grandTotal = 0;
            $('#productTable tbody tr').each(function()
            {
                var quantity = parseFloat($(this).find('.quantity').val())|| 0;
                var price = parseFloat($(this).find('.unit-price').val())|| 0;
                var total = qty*price;
                $(this).find('.item-total').val(total.toFixed(2));
                grandTotal+=total;
            });
            $('#grandTotal').val(grandTotal.toFixed(2));
        }
    });

