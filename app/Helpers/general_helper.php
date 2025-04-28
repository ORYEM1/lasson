<?php
function get_uuid() 
{
        return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        // 32 bits for "time_low"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

        // 16 bits for "time_mid"
        mt_rand( 0, 0xffff ),

        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 4
        mt_rand( 0, 0x0fff ) | 0x4000,

        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        mt_rand( 0, 0x3fff ) | 0x8000,

        // 48 bits for "node"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
}
function respond($response)
{
    session_write_close();
    header("Content-Type: text/plain");
    // Buffer all upcoming output...
    //ob_start();
    // Send your response.
    echo $response;
    // Get the size of the output.
    $size = ob_get_length();

    // Disable compression (in case content length is compressed).
    header("Content-Encoding: none");
    // Set the content length of the response.
    header("Content-Length: {$size}");
    // Close the connection.
    header("Connection: close");
    // Flush all output.
    ob_end_flush();
    ob_flush();
    flush();
}

function is_valid_url($url)
{
    if (filter_var($url, FILTER_VALIDATE_URL)) 
    {
        return true;
    } 
    return false;
}

function multi_curl_request($data, $headers=array(), $params=array()) 
{
      // array of curl handles
      $curly = array();
      // data to be returned
      $result = array();
      // multi handle
      $mh = curl_multi_init();
     
      // loop through $data and create curl handles
      // then add them to the multi-handle
      foreach ($data as $id => $d) 
      {
        if(isset($d['url'])) 
        {
            $url=$d['url'];
        }
        else if(isset($params['url']))
        {
            $url=$params['url'];
        } 
        else
        {
            continue;
        }
        
        if(isset($d['headers'])) 
        {
            $headers=$d['headers'];
        }
        
        $curly[$id] = curl_init();
        
        curl_setopt($curly[$id], CURLOPT_URL,$url);
        curl_setopt($curly[$id], CURLOPT_HEADER,0);
        curl_setopt($curly[$id], CURLOPT_RETURNTRANSFER,1);
        curl_setopt($curly[$id], CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curly[$id], CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curly[$id], CURLOPT_CONNECTTIMEOUT,10);
        curl_setopt($curly[$id],CURLOPT_TIMEOUT,20);
        curl_setopt($curly[$id],CURLOPT_HTTPHEADER,$headers);
        $httpCode = curl_getinfo($curly[$id] , CURLINFO_HTTP_CODE); // this results 0 every time
        // Using POST
        if(isset($d['post_fields']))
        {
            curl_setopt($curly[$id], CURLOPT_POST, 1);
            curl_setopt($curly[$id], CURLOPT_POSTFIELDS, $d['post_fields']);
        }
        //Note: To use GET, include the fields in the URL string.
        
        // extra options?
        if (isset($params['options'])) 
        {
            curl_setopt_array($curly[$id], $params['options']);
        }
     
        curl_multi_add_handle($mh, $curly[$id]);
      }
     
      // execute the handles
      $running = null;
      do {
        curl_multi_exec($mh, $running);
      } while($running > 0);
     
     
      // get content and remove handles
      foreach($curly as $id => $c) {
        $result[$id] = curl_multi_getcontent($c);
        curl_multi_remove_handle($mh, $c);
      }
      // all done
      curl_multi_close($mh);
      //print_r($result); exit;
      return $result;
}

function redirect_user($url)
{
     header("Location:{$url}");
     exit();
}
function encrypt_string($plaintext)
{
    $key=getenv('ENCRYPTION_KEY');
    $ivlen=openssl_cipher_iv_length($cipher="AES-128-CBC");
    $iv=openssl_random_pseudo_bytes($ivlen);
    $ciphertext_raw=openssl_encrypt($plaintext,$cipher,$key,$options=OPENSSL_RAW_DATA,$iv);
    $hmac=hash_hmac('sha256',$ciphertext_raw,$key,$as_binary=true);
    $ciphertext=base64_encode($iv.$hmac.$ciphertext_raw);
    return $ciphertext;
}

function decrypt_string($ciphertext)
{
    $key=getenv('ENCRYPTION_KEY');
    $c=base64_decode($ciphertext);
    $ivlen=openssl_cipher_iv_length($cipher="AES-128-CBC");
    $iv=substr($c,0,$ivlen);
    $hmac=substr($c,$ivlen,$sha2len=32);
    $ciphertext_raw=substr($c,$ivlen+$sha2len);
    $original_plaintext=openssl_decrypt($ciphertext_raw,$cipher,$key,$options=OPENSSL_RAW_DATA,$iv);
    $calcmac=hash_hmac('sha256',$ciphertext_raw,$key,$as_binary=true);
    // timing attack safe comparison
    if(hash_equals($hmac,$calcmac))
    {
        return $original_plaintext;
    }
    return false;
}

function xml_to_array($xml)
{
     $doc = new \DOMDocument();
     libxml_use_internal_errors(true);
     $doc->loadHTML($xml);
     libxml_clear_errors();
     $xml = $doc->saveXML($doc->documentElement);
     $data=json_decode(json_encode(simplexml_load_string($xml)),true);
     return $data;
}

function force_download($file)
{
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='.basename($file));
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
    //ob_clean();
    flush();
    readfile($file);
    exit;
}

function is_internal_request()
{
    if(strpos($_SERVER['HTTP_REFERER'],base_url())!==false)
        return true;
    else
        return false;
}

function get_statuses_array($flip=false,$context=null)
{
    $array=array('Active'=>'1','Inactive'=>'0');
    if($flip)
    {
        return array_flip($array);
    }
    return $array;
}

function get_genders_array($flip=false)
{
    $array=array('Female'=>'F','Male'=>'M');
    if($flip)
    {
        return array_flip($array);
    }
    return $array;
}

function get_themes()
{
    $array=array('blue','green','purple','pink','dark');
    sort($array);
    return$array ;
}

function get_payment_method()
{
    $array=array('cash','mobile_money');
    sort($array);
    return $array;
}
function get_transaction_types()
{
    return array('Collection'=>'collection','Disbursement'=>'disbursement');
}

function get_delivery_address()
{
    $array = array(

                'Nakasero',
                'Kololo',
                'Kampala Hill',
                'Katwe',
                'Kibuye',
                'Lugogo',
                'Kawempe',
                'Kanyanya',
                'Kikaaya',
                'Kisaasi',
                'Kyebando',
                'Makindye',
                'Kansanga',
                'Kabalagala',
                'Muyenga',
                'Munyonyo',
                'Ntinda',
                'Naguru',
                'Bugolobi',
                'Mbuya',
                'Kiswa',
                'Lubaga',
                'Kasubi',
                'Lungujja',
                'Mutundwe',
                'Nateete'
            );
        sort($array);



        return $array;


}

function get_log_types()
{
    return array('Database'=>'database','File'=>'file');
}

function get_checklist_display($params)
{
    $selected_options=$params['selected_options']??array();
    $selected_options=array_combine($selected_options,$selected_options);
    $options=$params['options']??$selected_options;
    if(empty($options))
    {
        $options=$selected_options;
    }
    if(empty($options) && empty($selected_options))
        return null;
    $columns=$params['columns']??2;
    $batches=array_chunk($options,ceil(count($options)/$columns));
    $col_width=floor(100/count($batches));
    $attributes=$params['attributes']??array();
    $str="<ul class='checklist' style='list-style-type:none; padding-left: 0; margin: 0; width:100%; display: flex;'";
    foreach ($attributes as $attribute=>$value)
    {
        $str.=" $attribute='$value' ";
    }
    $str.=">\n";
    foreach ($batches as $batch)
    {
        $str.="<div style='width: {$col_width}%;'>\n";
        foreach ($batch as $item)
        {
            if(isset($selected_options[$item]))
                $icon="<i class='fa fa-check-square-o'></i>";
            else
                $icon="<i class='fa fa-square-o' ></i>";
            $str.="<li>{$icon} {$item}</li>\n";
        }
        $str.="</div>\n";
    }
    $str.="\n</ul>";
    return $str;
}

function get_gateway_functions($library)
{
    $path=APPPATH.'/Libraries/'.$library.'.php';
    if(!file_exists($path))
        return array();
    $library_path="\\App\\Libraries\\".$library;
    $lib=new $library_path();
    if(property_exists($lib,'gateway_functions'))
    {
        return $lib::$gateway_functions;
    }
    return array();
}

function get_wallet_libraries()
{
    $libraries[]='UG_Airtel_Open_API';
    $libraries[]='UG_Airtel_VPN_API';
    $libraries[]='UG_MTN_Open_API';
    $libraries[]='UG_MTN_VPN_API';
    $libraries[]='UG_Iotec_Pay_API';
    sort($libraries);
    return $libraries;
}
function get_order_status()
{
    return array('Pending'=>'pending','Cancelled'=>'cancelled','Completed'=>'completed');
}
function get_payment_status()
{
    return array('paid'=>'paid','unpaid'=>'unpaid');
}
function get_stock_type()
{
    return array('Incoming'=>'incoming','Outgoing'=>'outgoing');
}
function get_product_status($flip=false,$context=null)
{
    $array=array('Available'=>'1','unavailable'=>'0');
    if($flip)
    {
        return array_flip($array);
    }
    return $array;
}


?>
