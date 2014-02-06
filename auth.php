<?php

    define ('STCOM_TOKEN_LIFETIME', 25920000);
    
    require_once('../libs/reflect/php/http.php');
    require_once( '../libs/sms.ru/lib/Zelenin/smsru.php' );
    
    $private=simplexml_load_file('../secure.xml');
    
    $headers = array();
    
    $headers['Authorization'] = file_get_contents ('../settings/rest-auth-code.txt');
    @$smsToken = file_get_contents ('../settings/sms-auth-code.txt');
    $restUrl = (string) $private->pha[0];
    
    //header('content-type: text/xml');
    
    $service = false;
    $type = false;
    
    if (isset($_GET['smsCode']) && isset($_GET['ID'])) {
        $service = 'pha.token/@id=' . $_GET['ID'] . '&@password=' . $_GET['smsCode'];
        $type = 'login';
    } elseif (isset($_GET['mobileNumber'])) {
        $service = 'pha.auth/@phone=' . $_GET['mobileNumber'];
        $type = 'register';
    }
    
    if ($service) {
        
        $response = simplexml_load_string ( httpPost (
            $restUrl . '/rest/get/',
            false,
            $service,
            $headers
        ));
        
        $result = array();
        
        if ($response -> d) {
            
            foreach ($response -> d -> children() as $elem) {
                
                switch ($elem['name']) {
                    
                    case 'id':
                        $result['ID'] = (string) $elem;
                    break;
                    
                    case 'password':
                        if ($type == 'register' && $smsToken) {
                            $sms = new \Zelenin\smsru ($smsToken);
                            $sms -> sms_send($_GET['mobileNumber'], 'Код авторизации: ' . (string) $elem, null, null, false);
                        }
                    break;
                    
                    case 'redirect_uri':
                        $result['redirectUri'] = (string) $elem;
                    break;
                    
                    case 'token':
                        
                        setcookie ( 'auth_token'
                            , (string) $elem
                            , time() + STCOM_TOKEN_LIFETIME
                            , '/'
                            , false
                            , $_SERVER['SERVER_PORT'] == '443'
                        );
                        
                    break;
                }
            }
            
            print json_encode ($result);
            
        } else {
            
            header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found', 404);
            
        }
    }

?>
