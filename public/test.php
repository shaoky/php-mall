<?php

  
$data = array(
    'tid' => 100, 
    'name' => '标哥的技术博客',
    'site' => 'www.huangyibiao.com');
     
   $response = array(
    'code' => 200, 
    'message' => 'success for request',
    'data' => $data,
    );
     
   echo json_encode($response);
?>