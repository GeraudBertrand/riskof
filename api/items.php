<?php
    // Se connecter à la base de données
    require_once  "Model/db_connect.php";
    $model = Model::getModel();
    
    // Récupérer la méthode de la requête
    $request_method = $_SERVER["REQUEST_METHOD"];

    switch($request_method)
    {
        case 'GET':
            if(!empty($_GET["classification"])){
                if($_GET["classification"] == "all"){
                    if(!empty($_GET["id"]))
                    {
                        // Récupérer un seul items
                        $id = intval($_GET["id"]);
                        $data = $model->get_item($id);
                        echo json_encode($data, JSON_PRETTY_PRINT);
                    }
                    else
                    {
                        // Récupérer tous les items
                        $data = $model->get_items();
                        echo json_encode($data, JSON_PRETTY_PRINT);
                    }
                }
                else if($_GET["classification"] == "rarity"){
                    $data = $model->get_items_rarity(intval($_GET["rarityLevel"]));
                    echo json_encode($data, JSON_PRETTY_PRINT);
                }
                else if($_GET["classification"] == "tag"){
                    $data = $model->get_item_with_tag(intval($_GET["rarityLevel"]));
                    echo json_encode($data, JSON_PRETTY_PRINT);

                }
            }
            else{
                if(!empty($_GET["id"]))
                {
                    // Récupérer un seul items
                    $id = intval($_GET["id"]);
                    $data = $model->get_item($id);
                    echo json_encode($data, JSON_PRETTY_PRINT);
                }
                else
                {
                    // Récupérer tous les items
                    $data = $model->get_items();
                    echo json_encode($data, JSON_PRETTY_PRINT);
                }
            }
        break;
        default:
            // Requête invalide
            header("HTTP/1.0 405 Method Not Allowed");
        break;
    }
?>