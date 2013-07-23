<?php

    require_once('../ud/php/http.php');
    
    $headers = array();
    
    $headers['Authorization'] = file_get_contents ('../rest-auth-code.txt');
    
    //header('content-type: text/xml');
    
    $service = false;
    
    if (isset($_GET['smsCode']) && isset($_GET['ID'])) {
        $service = 'bs.Agent/' . $_GET['ID'] . '?password=' . $_GET['smsCode'];
    } elseif (isset($_GET['mobileNumber'])) {
        $service = 'pha.auth/@phone=' . $_GET['mobileNumber'];
    }
    
    if ($service) {
        $response = simplexml_load_string ( httpPost (
            'https://asa1.sistemium.com/demo/rest/get/',
            false,
            $service,
            $headers
        ));
        
        $result = array();
        
        if ($response -> d) {
            
            foreach ($response -> d -> children() as $elem) {
                
                if ($elem['name']=='id')
                    $result['ID'] = (string) $elem;
            }
            
            print json_encode ($result);
            
        } else {
            
            header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found', 404);
            
        }
    }

?>