<?php
    // Se connecter à la base de données
    require_once  "Model/db_connect.php";
    $model = Model::getModel();
    

    $localItem = __DIR__."/../Content/image/item/";
    $files = scandir($localItem);
    $files = array_diff($files, array('.', '..'));
    $files = array_values($files);

    /*
    if( $handle= opendir($localItem)){
        while( false !== ($file = readdir($handle))){
            if($file != "." && $file != ".."){
                preg_match('/[0-9]+/', $file, $matches);
                $id = $matches[0];
                $fileData = file_get_contents($localItem.$file);
                $model->insert_image($id, $fileData);
            }
        }
    }*/
?>