<?php

    define ('STCOM_TOKEN_LIFETIME', 25920000);
    
    require_once('../libs/reflect/php/http.php');
    require_once( '../libs/sms.ru/lib/Zelenin/smsru.php' );

    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS')
        return;

    $private=simplexml_load_file('../secure.xml');
    
    $headers = array();
    
    $headers['Authorization'] = file_get_contents ('../settings/rest-auth-code.txt');
    $headers['User-Agent-Original'] = $_SERVER['HTTP_USER_AGENT'];

    @$smsToken = file_get_contents ('../settings/sms-auth-code.txt');
    $restUrl = (string) $private->pha[0];
    
    //header('content-type: text/xml');
    
    $service = false;
    $type = false;
    
    if (isset($_REQUEST['smsCode']) && isset($_REQUEST['ID'])) {
        $service = 'pha.token/@id=' . $_REQUEST['ID'] . '&@password=' . $_REQUEST['smsCode'];
        $type = 'login';
    } elseif (isset($_REQUEST['mobileNumber'])) {
        $service = 'pha.auth/@phone=' . $_REQUEST['mobileNumber'];
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
                    
                    case 'name':
                        $result['name'] = (string) $elem;
                    break;
                    
                    case 'id':
                        $result['ID'] = (string) $elem;
                    break;
                    
                    case 'password':
                        if ($type == 'register' && $smsToken) {
                            $sms = new \Zelenin\smsru ($smsToken);
                            $sms -> sms_send($_REQUEST['mobileNumber'], 'Код авторизации: ' . (string) $elem, null, null, false);
                        }
                    break;
                    
                    case 'redirect_uri':
                        $result['redirectUri'] = (string) $elem;
                    break;
                    
                    case 'token':
                        
                        $result['accessToken'] = (string) $elem;
                        
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
