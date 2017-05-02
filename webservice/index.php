<?php
require_once __DIR__ . '/vendor/autoload.php';
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Silex\Application;

header("Access-Control-Allow-Headers: Origin, Content-Type, Accept, authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Origin: *");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
  header("Access-Control-Allow-Headers: Origin, Content-Type, Accept, authorization, S-Token");

 exit;
}


function guidv4($data)
{
    assert(strlen($data) == 16);

    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

function generate_token() {
    $d = time();
    return $d."-".sha1("".$d)."-".guidv4(openssl_random_pseudo_bytes(16));
}

function isTokenExpired($app, $token) {
    if(strlen($token) < 10) return true;

    $d = (explode("-", $token));
    $d = $d[0];
    $delta = abs(time() - (int)$d);
    if($delta < (7 * 24 * 3600))
    return false;

    return true;
}

function isTokenValid($app, $token) {
    if(isTokenExpired($app, $token)) return false;

    $pdo = $app["PDO"];
    foreach ($pdo->query("SELECT * FROM p_users  WHERE token_user LIKE '$token'") as $row) {
        return true;
    }

    return false;
}

function getUserFromToken($app, $token) {
    $pdo = $app["PDO"];
    foreach ($pdo->query("SELECT * FROM p_users  WHERE token_user LIKE '$token'") as $row) {
        return $row['userid'];
    }

    return -1;
}

function error401() {
    $response = new Response();
    $response->headers->set('WWW-Authenticate', sprintf('Basic realm="%s"', 'WS2016'));
    $response->setStatusCode(401, 'Please sign in.');
    return $response;
}

function error409() {
    $response = new Response();
    $response->setStatusCode(409, 'Conflict');
    return $response;
}

function error404() {
    $response = new Response();
    $response->setStatusCode(404, 'Not found');
    return $response;
}

function error500() {
    $response = new Response();
    $response->setStatusCode(500, 'Internal error');
    return $response;
}

$app = new Silex\Application();

/**
*********************************************************************
*********************************************************************
* LOGIN
*
* POST
*
***** Body *****
*
* @param login_user
* @param password_user
*********************************************************************
*********************************************************************
*
*/
$app->POST('/login', function(Application $app, Request $request) {

    $data = json_decode($request->getContent(), true);
    $request->request->replace($data);

    $login_user = $request->request->get('username');
    $password_user = $request->request->get('password');

    $pdo = $app["PDO"];

    foreach( $pdo->query("SELECT * FROM p_users WHERE login_user = '$login_user'") as $row) {
        if($row['password_user'] === $password_user) {

                if($row['token_user'] != "")
                {
                    $token = $row['token_user'];

                    if (isTokenExpired($app, $token)) {
                    //return false;
                        echo "token expired";
                        $token = generate_token();

                        $pdo->exec("UPDATE p_users SET token_user = '$token' Where id_user = {$row['id_user']}") or die("DB Error");
                        return $token;

                    }
                    else {
                        $token = $row['token_user'];
                        return $token;
                    }

                }
                else{
                    $token = generate_token();
                    $pdo->exec("UPDATE p_users SET token_user = '$token' Where id_user = {$row['id_user']}") or die("DB Error");

                    return $token;
                }
        }
    }
});


//*****************
// PRONTO
//*****************


//*******************************
// PRODUITS
//*******************************


/**
*********************************************************************
*********************************************************************
* INSERT NEW PRODUCT AND SELECT PRODUCT INSERED
*
* POST
*
***** Body *****
* @param S-Token
* @param code_barre_produit
* @param nom_produit
* @param quantite_produit
* @param poids_produit
* @param prix_produit
* @param fournisseur_produit
* @param peremption_produit
*********************************************************************
*********************************************************************
*
*/

$app->POST('/api/product/new_product', function(Application $app, Request $request) {

    $data = json_decode($request->getContent(), true);
    $request->request->replace($data);

    if (null === $token = $request->headers->get('S-Token')) {
        return error401();
    }

    //le token est expiré
    if (isTokenExpired($app, $token)) {
        return false;
    } else {

        //parameters
        $code_barre_produit = $request->request->get('barre');
        $nom_produit = $request->request->get('produit');
        $quantite_produit = $request->request->get('quantite');
        $poids_produit = $request->request->get('poids');
        $prix_produit = $request->request->get('prix');
        $fournisseur_produit = $request->request->get('fournisseur');
        $peremption_produit = $request->request->get('peremption');


        $pdo = $app["PDO"];

        //insert query
        $pdo->exec("INSERT INTO `p_produits_flux`( `code_barre_produit`, `nom_produit`, `quantite_produit`, `poids_produit`, `prix_produit`, `fournisseur_produit`, `peremption_produit`)
                    VALUES ('$code_barre_produit','$nom_produit','$quantite_produit','$poids_produit', '$prix_produit' ,'$fournisseur_produit' , '$peremption_produit')")
                    or die("DB Error");


        //select product insered
        foreach( $pdo->query("SELECT * FROM p_produits_flux WHERE code_barre_produit LIKE '$code_barre_produit'") as $row) {

            $d = $row;

            return new Response(json_encode($d), 200, array('Content-Type' => 'application/json'));
        }
    }
});



$app->POST('/api/command/new_command', function(Application $app, Request $request) {

    $data = json_decode($request->getContent(), true);
    $request->request->replace($data);

    if (null === $token = $request->headers->get('S-Token')) {
        return error401();
    }

    //le token est expiré
    if (isTokenExpired($app, $token)) {
        return false;
    } else {

        //parameters
        $fournisseur = $request->request->get('fournisseur');

        $quantite = $request->request->get('prod');


        $pdo = $app["PDO"];
        foreach ($quantite as $key => $value) {

            $produit_id = $value["produit_id"];
            $quantity = $value["quantite"];
            $poids = $value["poids"];

        //insert query
        $pdo->exec("INSERT INTO `p_commandes_fournisseurs` ( `id_produit`, `quantite_produit`, `poids_produit`, `fournisseur_produit`, `status_commande`)
                    VALUES ('$produit_id','$quantity','$poids', '$fournisseur' ,'1')")
                    or die("DB Error");


        }
            return new Response(json_encode(true), 200, array('Content-Type' => 'application/json'));

    }
});




/**
*********************************************************************
*********************************************************************
* SELECT LAST PRODUCT INSERED
*
* POST
*
***** Body *****
* @param S-Token
*
*********************************************************************
*********************************************************************
*
*/

$app->GET('/api/product/last_product', function(Application $app, Request $request) {

    if (null === $token = $request->headers->get('S-Token')) {
        return error401();
    }

    //le token est expiré
    if (isTokenExpired($app, $token)) {
        return false;
    } else {

        $pdo = $app["PDO"];

        foreach( $pdo->query("SELECT * from p_produits_flux, p_produits, p_fournisseurs where p_produits_flux.`fournisseur_produit` = p_fournisseurs.`id_fournisseur` AND p_produits_flux.`nom_produit` = p_produits.`id_produit` AND p_produits_flux.`id_produit` = (SELECT max(`id_produit`) FROM p_produits_flux )") as $row) {

            $d[] = $row;

            return new Response(json_encode($d), 200, array('Content-Type' => 'application/json'));
        }
    }
});



$app->GET('/api/product/last_vin', function(Application $app, Request $request) {

    if (null === $token = $request->headers->get('S-Token')) {
        return error401();
    }

    //le token est expiré
    if (isTokenExpired($app, $token)) {
        return false;
    } else {

        $pdo = $app["PDO"];

        foreach( $pdo->query("SELECT * from p_vins_flux where p_vins_flux.`id_vin` = (SELECT max(`id_vin`) FROM p_vins_flux )") as $row) {

            $d[] = $row;

            return new Response(json_encode($d), 200, array('Content-Type' => 'application/json'));
        }
    }
});



$app->POST('/api/product/detail', function(Application $app, Request $request) {
  $data = json_decode($request->getContent(), true);
  $request->request->replace($data);
    if (null === $token = $request->headers->get('S-Token')) {
        return error401();
    }

    //le token est expiré
    if (isTokenExpired($app, $token)) {
        return false;
    } else {

        $pdo = $app["PDO"];

        $id = $request->request->get('prodId');

        foreach( $pdo->query("SELECT * FROM p_produits_flux LEFT JOIN p_produits on p_produits.id_produit = p_produits_flux.nom_produit LEFT JOIN p_fournisseurs on p_fournisseurs.id_fournisseur = p_produits_flux.fournisseur_produit where p_produits_flux.id_produit = '$id' order by entree_produit desc") as $row) {
            $d[] = $row;

        }
        return new Response(json_encode($d), 200, array('Content-Type' => 'application/json'));

    }
});



$app->POST('/api/command/detail', function(Application $app, Request $request) {
  $data = json_decode($request->getContent(), true);
  $request->request->replace($data);
    if (null === $token = $request->headers->get('S-Token')) {
        return error401();
    }

    //le token est expiré
    if (isTokenExpired($app, $token)) {
        return false;
    } else {

        $pdo = $app["PDO"];

        $da = $request->request->get('comId');
        foreach( $pdo->query("SELECT * from p_produits, p_produits_flux, p_fournisseurs where p_produits_flux.`id_produit` = '$da' AND p_produits.`id_produit`= p_produits_flux.`nom_produit` AND p_produits_flux.`fournisseur_produit`= p_fournisseurs.`id_fournisseur`") as $row) {
            $d[] = $row;
            return new Response(json_encode($d), 200, array('Content-Type' => 'application/json'));

        }

    }
});



/**
*************************************************************
*************************************************************
* INSERT PRODUCT BY CATÉGORY
*
* POST
*
***** Body *****
* @param S-Token
* @param categorie_produit
*
*************************************************************
*************************************************************
*/

$app->POST('/api/product/product_by_category/{idproduit}', function(Application $app, Request $request, $idproduit) {

    $data = json_decode($request->getContent(), true);
    $request->request->replace($data);

    if (null === $token = $request->request->get('S-Token')) {
        return error401();
    }

    //le token est expiré
    if (isTokenExpired($app, $token)) {
        return false;
    } else {
        //parameters
        $idproduit = filter_var($idproduit, FILTER_SANITIZE_NUMBER_INT);
        $categorie_produit = $request->request->get('categorie_produit');


        $pdo = $app["PDO"];

        //insert query
        $query = $pdo->exec("INSERT INTO `p_produits`(`id_produit`, `categorie_produit`) VALUES ('$idproduit','$categorie_produit')")
                    or die("DB Error");

        if($query){
            return true;
        } else {
            return false;
        }

    }
});

/**
*************************************************************
*************************************************************
* DELETE PRODUCT
*
* POST
*
***** Body *****
* @param S-Token
*
*************************************************************
*************************************************************
*/

$app->POST('/api/product/delete_flux/{idproduit}', function(Application $app, Request $request, $idproduit) {

    $data = json_decode($request->getContent(), true);
    $request->request->replace($data);

    if (null === $token = $request->request->get('S-Token')) {
        return error401();
    }

    if (isTokenExpired($app, $token)) {
        return false;
    } else {

        $idproduit = filter_var($idproduit, FILTER_SANITIZE_NUMBER_INT);

        $pdo = $app["PDO"];

        $query = $pdo->exec("DELETE FROM `p_produits_flux` WHERE `id_produit` = $idproduit") or die("DB Error");



        if($query){
            $pdo->exec("DELETE FROM `p_produits` WHERE `id_produit` = $idproduit");
            return true;
        } else {
            return false;
        }
    }
});

/**
*************************************************************
*************************************************************
* LIST PRODUCT
*
* POST
*
***** Body *****
* @param S-Token
*
*************************************************************
*************************************************************
*/


$app->GET('/api/product/list', function(Application $app, Request $request) {
    if (null === $token = $request->headers->get('S-Token')) {
        return error401();
    }

    if (isTokenExpired($app, $token)) {
        return false;
    } else {

        $pdo = $app["PDO"];

        foreach( $pdo->query("SELECT * FROM p_produits_flux order by id_produit desc") as $row) {

            $d[] = $row;
        }


        return new Response(json_encode($d), 200, array('Content-Type' => 'application/json'));


    }
});

$app->GET('/api/vin/list', function(Application $app, Request $request) {


    if (null === $token = $request->headers->get('S-Token')) {
        return error401();
    }

    if (isTokenExpired($app, $token)) {
        return false;
    } else {

        $pdo = $app["PDO"];

        foreach( $pdo->query("  SELECT * FROM p_vins_flux LEFT JOIN p_vins on p_vins.categorie_vin = p_vins_flux.id_categorie_vin LEFT JOIN p_categories_vins on p_categories_vins.id_categorie = p_vins_flux.id_categorie_vin order by p_vins_flux.id_vin desc") as $row) {

            $d[] = $row;
        }

        return new Response(json_encode(utf8_converter($d)), 200, array('Content-Type' => 'application/json'));

    }
});

$app->GET('/api/vinCat/list', function(Application $app, Request $request) {


    if (null === $token = $request->headers->get('S-Token')) {
        return error401();
    }

    if (isTokenExpired($app, $token)) {
        return false;
    } else {

        $pdo = $app["PDO"];

        foreach( $pdo->query("SELECT * FROM p_categories_vins
                              order by p_categories_vins.id_categorie desc") as $row) {

            $d[] = $row;
        }
        return new Response(json_encode(utf8_converter($d)), 200, array('Content-Type' => 'application/json'));

    }
});

$app->GET('/api/productCat/list', function(Application $app, Request $request) {


    if (null === $token = $request->headers->get('S-Token')) {
        return error401();
    }

    if (isTokenExpired($app, $token)) {
        return false;
    } else {

        $pdo = $app["PDO"];

        foreach( $pdo->query("SELECT p_produits.id_produit, p_produits.image_produit, p_produits.nom_produit, sum(quantite_produit) as quantite, sum(poids_produit) as poids, sum(prix_produit) as prix FROM `p_produits` INNER JOIN p_produits_flux ON p_produits.id_produit = p_produits_flux.nom_produit group by p_produits.id_produit  order by id_produit desc") as $row) {

            $d[] = $row;
        }

        return new Response(json_encode($d), 200, array('Content-Type' => 'application/json'));

    }
});

$app->GET('/api/productFour/list', function(Application $app, Request $request) {


    if (null === $token = $request->headers->get('S-Token')) {
        return error401();
    }

    if (isTokenExpired($app, $token)) {
        return false;
    } else {

        $pdo = $app["PDO"];

        foreach( $pdo->query("SELECT * FROM p_fournisseurs order by id_fournisseur desc") as $row) {

            $d[] = $row;
        }
        return new Response(json_encode($d), 200, array('Content-Type' => 'application/json'));

    }
});


$app->GET('/api/product/count', function(Application $app, Request $request) {


    if (null === $token = $request->headers->get('S-Token')) {
        return error401();
    }

    if (isTokenExpired($app, $token)) {
        return false;
    } else {

        $pdo = $app["PDO"];

        foreach( $pdo->query("SELECT COUNT(*) FROM p_fournisseurs") as $row) {

            $d[] = $row;
        }
        return new Response(json_encode($d), 200, array('Content-Type' => 'application/json'));

    }
});

$app->POST('/api/product/listing', function(Application $app, Request $request) {

  $data = json_decode($request->getContent(), true);
  $request->request->replace($data);
    if (null === $token = $request->headers->get('S-Token')) {
        return error401();
    }

    if (isTokenExpired($app, $token)) {
        return false;
    } else {
      $id = $request->request->get('prodId');
        $pdo = $app["PDO"];

        foreach( $pdo->query("SELECT * FROM p_produits_flux LEFT JOIN p_produits on p_produits.id_produit = p_produits_flux.nom_produit LEFT JOIN p_fournisseurs on p_fournisseurs.id_fournisseur = p_produits_flux.fournisseur_produit where p_produits_flux.nom_produit = '$id' order by entree_produit desc") as $row) {

            $d[] = $row;
        }

        return new Response(json_encode($d), 200, array('Content-Type' => 'application/json'));


    }
});
/**
*************************************************************
*************************************************************
* SELECT DATE PEREMPTION PRODUIT
*
* POST
*
***** Body *****
* @param S-Token
*
*************************************************************
*************************************************************
*/

$app->POST('/api/product/date_peremption', function(Application $app, Request $request) {

    $data = json_decode($request->getContent(), true);
    $request->request->replace($data);

    if (null === $token = $request->request->get('S-Token')) {
        return error401();
    }

    if (isTokenExpired($app, $token)) {
        return false;
    } else {

        $pdo = $app["PDO"];

        foreach( $pdo->query("SELECT * FROM `p_produits_flux`
                                INNER JOIN p_produits ON p_produits.id_produit = p_produits_flux.nom_produit
                                WHERE (now() + interval 7 day) <= peremption_produit") as $row) {

            $d[] = $row;
        }
        return new Response(json_encode($d), 200, array('Content-Type' => 'application/json'));
    }
});


//*******************************
// VINS
//*******************************

/**
*********************************************************************
*********************************************************************
* INSERT NEW PRODUCT AND SELECT PRODUCT INSERED
*
* POST
*
***** Body *****
* @param S-Token
* @param code_barre_vin
* @param nom_vin
* @param quantite_vin
* @param prix_vin
* @param fournisseur_vin
* @param peremption_vin
*********************************************************************
*********************************************************************
*
*/

$app->POST('/api/product/new_vin', function(Application $app, Request $request) {

    $data = json_decode($request->getContent(), true);
    $request->request->replace($data);

    if (null === $token = $request->headers->get('S-Token')) {
        return error401();
    }

    //le token est expiré
    if (isTokenExpired($app, $token)) {
        return false;
    } else {
        //parameters
        $code_barre_vin = $request->request->get('barre');
        $nom_vin = $request->request->get('nom');
        $quantite_vin = $request->request->get('quantite');
        $prix_vin = $request->request->get('prix');
        $fournisseur_vin = $request->request->get('fournisseur');
        $categorie_vin = $request->request->get('type');
        $annee_vin = $request->request->get('annee');

        $pdo = $app["PDO"];

        //insert query
        $pdo->exec("INSERT INTO `p_vins_flux`( `code_barre_vin`, `nom_vin`, `quantite_vin`, `prix_vin`, `fournisseur_vin`, `id_categorie_vin`, `annee_vin`)
                    VALUES ('$code_barre_vin','$nom_vin','$quantite_vin', '$prix_vin' ,'$fournisseur_vin', '$categorie_vin', '$annee_vin' )")
                    or die("DB Error");

        //select product insered
        foreach( $pdo->query("SELECT * FROM p_vins_flux WHERE code_barre_vin LIKE '$code_barre_vin'") as $row) {

            $d = $row;
            return new Response(json_encode($d), 200, array('Content-Type' => 'application/json'));
        }
    }
});



/**
*************************************************************
*************************************************************
* INSERT VIN BY CATÉGORY
*
* POST
*
***** Body *****
* @param S-Token
* @param categorie_vin
*
*************************************************************
*************************************************************
*/

$app->POST('/api/product/vin_by_category/{idvin}', function(Application $app, Request $request, $idvin) {

    $data = json_decode($request->getContent(), true);
    $request->request->replace($data);

    if (null === $token = $request->request->get('S-Token')) {
        return error401();
    }

    //le token est expiré
    if (isTokenExpired($app, $token)) {
        return false;
    } else {
        //parameters
        $idvin = filter_var($idvin, FILTER_SANITIZE_NUMBER_INT);
        $categorie_vin = $request->request->get('categorie_vin');


        $pdo = $app["PDO"];

        //insert query
        $query = $pdo->exec("INSERT INTO `p_vins`(`id_vin`, `categorie_vin`) VALUES ('$idvin','$categorie_vin')")
                    or die("DB Error");


        if($query){
            return true;
        } else {
            return false;
        }
    }
});


/**
*************************************************************
*************************************************************
* DELETE VIN
*
* POST
*
***** Body *****
* @param S-Token
*
*************************************************************
*************************************************************
*/

$app->POST('/api/product/delete_vin/{idvin}', function(Application $app, Request $request, $idvin) {

    $data = json_decode($request->getContent(), true);
    $request->request->replace($data);

    if (null === $token = $request->request->get('S-Token')) {
        return error401();
    }

    if (isTokenExpired($app, $token)) {
        return false;
    } else {

        $idvin = filter_var($idvin, FILTER_SANITIZE_NUMBER_INT);

        $pdo = $app["PDO"];

        $query = $pdo->exec("DELETE FROM `p_vins_flux` WHERE `id_vin` = $idvin") or die("DB Error");

        if($query){
            $pdo->exec("DELETE FROM `p_vins` WHERE `id_vin` = $idvin");
            return true;
        } else {
            return false;
        }
    }
});


/**
*************************************************************
*************************************************************
* LIST VINS
*
* POST
*
***** Body *****
* @param S-Token
*
*************************************************************
*************************************************************
*/

$app->GET('/api/vin/last', function(Application $app, Request $request) {


    if (null === $token = $request->headers->get('S-Token')) {
        return error401();
    }

    if (isTokenExpired($app, $token)) {
        return false;
    } else {

        $pdo = $app["PDO"];

        foreach( $pdo->query("SELECT * FROM p_vins_flux
                                INNER JOIN p_vins on p_vins.categorie_vin = p_vins_flux.id_categorie_vin
                                INNER JOIN p_categories_vins on p_categories_vins.id_categorie = p_vins_flux.id_categorie_vin
                                order by p_vins_flux.id_vin desc
                                limit 1") as $row) {

            $d[] = $row;
        }
        return new Response(json_encode($d), 200, array('Content-Type' => 'application/json'));

    }
});


//*******************************
// VENTES
//*******************************

/**
*********************************************************************
*********************************************************************
* INSERT NEW VENTE
*
* POST
*
***** Body *****
* @param S-Token
* @param recette_vente
*
*********************************************************************
*********************************************************************
*
*/

$app->POST('/api/recette/new_recette', function(Application $app, Request $request) {
  $data = json_decode($request->getContent(), true);
  $request->request->replace($data);
    // echo $request->get('S-Token');
    if (null === $token = $request->headers->get('S-Token')) {
        return error401();
    }

    //le token est expiré
    if (isTokenExpired($app, $token)) {
        return false;
    } else {
        //parameters
        $nom_recette = $request->request->get('nom');
        $descr_recette = $request->request->get('descr');
        $duree_recette = $request->request->get('duree');
        $auteur_recette = $request->request->get('auteur');
        $url_photo = $request->request->get('url_photo');


        $produits_quantites = $request->request->get('prod');

        $pdo = $app["PDO"];



        //insert query
        try{
           $pdo->exec("INSERT INTO `p_recettes`(`nom_recette`, `descr_recette`, `duree_recette`, `auteur_recette`, `date_recette`)
                    VALUES ('$nom_recette','$descr_recette','$duree_recette',1, NOW())");
            $lastRecetteId = $pdo->lastInsertId();

            if ($lastRecetteId) {
                echo $lastRecetteId;

                $photos = $pdo->exec("INSERT INTO `p_photos_recettes`(`id_recette`, `url_photo`) VALUES ('$lastRecetteId','$url_photo')");

                foreach ($produits_quantites as $key => $value) {
                    $produit_id = $value["produit_id"];
                    $quantity = $value["quantite"];


                    $pdo->exec("INSERT INTO `p_produits_recettes`(`id_produit`, `id_recette`, `quantite_produit`)
                                VALUES ('$produit_id', '$lastRecetteId','$quantity')");
                }

                // $produits = $pdo->exec("INSERT INTO `p_produits_recettes`(`id_recette`, `quantite_produit`) VALUES ('$lastRecetteId','$quantite_produit')");


                if ($photos) {
                    echo $photos;
                    return $photos;
                } else {
                    return false;
                }
                //  if ($produits) {
                //     echo $produits;
                //     return $produits;
                // } else {
                //     return false;
                // }
            }
            else{
                echo "il manque l'id de la recette";;
            }
        } catch(\Exception $e){
            die($e->getMessage());
        }
    }
});

// $app->GET('/api/recette/list_recette', function(Application $app, Request $request) {
//
//
//     if (null === $token = $request->headers->get('S-Token')) {
//         return error401();
//     }
//
//     if (isTokenExpired($app, $token)) {
//         return false;
//     } else {
//
//         $pdo = $app["PDO"];
//
//         foreach( $pdo->query("SELECT * FROM p_recettes order by id_recette desc") as $row) {
//
//             $d[] = $row;
//         }
//         return new Response(json_encode($d), 200, array('Content-Type' => 'application/json'));
//
//     }
// });

$app->GET('/api/recette/list_recette', function(Application $app, Request $request) {


    if (null === $token = $request->headers->get('S-Token')) {
        return error401();
    }

    if (isTokenExpired($app, $token)) {
        return false;
    } else {

        $pdo = $app["PDO"];

        foreach( $pdo->query("SELECT * FROM p_recettes LEFT JOIN p_photos_recettes on p_photos_recettes.id_recette = p_recettes.id_recette order by p_recettes.id_recette desc") as $row) {

            $d[] = $row;
        }


        return new Response(json_encode(utf8_converter($d)), 200, array('Content-Type' => 'application/json'));


    }
});


require "lib/mail.class.php";

$app->POST('/api/vin/new_command', function(Application $app, Request $request) {

    $data = json_decode($request->getContent(), true);
    $request->request->replace($data);

    if (null === $token = $request->headers->get('S-Token')) {
        return error401();
    }

    //le token est expiré
    if (isTokenExpired($app, $token)) {
        return false;
    } else {
        //parameters
        $produit_id = $request->request->get('nom');
        $quantite_produit = $request->request->get('quantite');
        $fournisseur_produit = $request->request->get('fournisseur');
        $status_commande = 1;
        $pdo = $app["PDO"];
        $params = [$produit_id, $quantite_produit, $fournisseur_produit, $status_commande];

              //insert query
              foreach( $pdo->query("INSERT INTO `p_commandes_vin`( `nom_produit`, `quantite_produit`, `fournisseur_produit`, `status_commande`)
                                  VALUES ('$produit_id','$quantite_produit','$fournisseur_produit','$status_commande')") as $row) {

                  $d[] = $row;

              }



    }
});



$app->POST('/api/recette/detail_recette', function(Application $app, Request $request) {


    $data = json_decode($request->getContent(), true);
    $request->request->replace($data);
    if (null === $token = $request->headers->get('S-Token')) {
        return error401();
    }

    if (isTokenExpired($app, $token)) {
        return false;
    } else {
    $idrecette = $request->request->get('recId');
        $pdo = $app["PDO"];
        foreach( $pdo->query("  SELECT * FROM p_recettes
                                LEFT join p_produits_recettes on p_produits_recettes.id_recette = p_recettes.id_recette
                                LEFT join p_photos_recettes on p_photos_recettes.id_recette = p_recettes.id_recette
                                LEFT JOIN p_produits on p_produits.id_produit = p_produits_recettes.id_produit
                                where p_recettes.id_recette = $idrecette") as $row) {

            $d[] = $row;
        }
        return new Response(json_encode($d), 200, array('Content-Type' => 'application/json'));
    }
});




$app->POST('/api/vin/detail', function(Application $app, Request $request) {


    $data = json_decode($request->getContent(), true);
    $request->request->replace($data);
    if (null === $token = $request->headers->get('S-Token')) {
        return error401();
    }

    if (isTokenExpired($app, $token)) {
        return false;
    } else {
    $id_vin = $request->request->get('vinId');
        $pdo = $app["PDO"];
        foreach( $pdo->query("SELECT * FROM p_vins_flux LEFT JOIN p_vins on p_vins.id_vin = p_vins_flux.id_vin LEFT JOIN p_categories_vins on p_categories_vins.id_categorie = p_vins_flux.id_categorie_vin LEFT JOIN p_fournisseurs on p_fournisseurs.id_fournisseur = p_vins_flux.fournisseur_vin where p_vins_flux.id_vin = '$id_vin'") as $row) {

            $d[] = $row;

        }
        return new Response(json_encode($d), 200, array('Content-Type' => 'application/json'));

    }
});

$app->POST('/api/vente/new_vente/', function(Application $app, Request $request) {

    $data = json_decode($request->getContent(), true);
    $request->request->replace($data);

    if (null === $token = $request->headers->get('S-Token')) {
        return error401();
    }

    //le token est expiré
    if (isTokenExpired($app, $token)) {
        return false;
    } else {
        //parameters
        $recette_vente = $request->request->get('nom');
        $quantite_vente = $request->request->get('quantite');

        $pdo = $app["PDO"];

        //insert query
        $query = $pdo->exec("INSERT INTO `p_ventes`( `recette_vente`, `quantite_vente`) VALUES ('$recette_vente', '$quantite_vente')")
                    or die("DB Error");

        if($query){
            return true;
        } else {
            return false;
        }

    }
});


/**
*************************************************************
*************************************************************
* SELECT VENTES DU JOUR
*
* POST
*
***** Body *****
* @param S-Token
*
*************************************************************
*************************************************************
*/

$app->GET('/api/vente/list_ventes/day', function(Application $app, Request $request) {

    if (null === $token = $request->headers->get('S-Token')) {
        return error401();
    }

    if (isTokenExpired($app, $token)) {
        return false;
    } else {

        $pdo = $app["PDO"];

        foreach( $pdo->query(" SELECT * FROM `p_ventes`
                                INNER JOIN p_recettes on p_recettes.id_recette = p_ventes.recette_vente
                                WHERE `date_vente` >= now() - interval 1 day") as $row) {
            $d[] = $row;
        }
        return new Response(json_encode(utf8_converter($d)), 200, array('Content-Type' => 'application/json'));
    }

});

/**
*************************************************************
*************************************************************
* SELECT VENTES DE LA SEMAINE
*
* POST
*
***** Body *****
* @param S-Token
*
*************************************************************
*************************************************************
*/

$app->GET('/api/vente/list_ventes/week', function(Application $app, Request $request) {



    if (null === $token = $request->headers->get('S-Token')) {
        return error401();
    }

    if (isTokenExpired($app, $token)) {
        return false;
    } else {

        $pdo = $app["PDO"];

        foreach( $pdo->query("  SELECT * FROM `p_ventes`
                                INNER JOIN p_recettes on p_recettes.id_recette = p_ventes.recette_vente
                                WHERE `date_vente` >= now() - interval 1 week") as $row) {
            $d[] = $row;
        }
        return new Response(json_encode(utf8_converter($d)), 200, array('Content-Type' => 'application/json'));
    }
});



/**
*************************************************************
*************************************************************
* SELECT VENTES DU MOIS
*
* POST
*
***** Body *****
* @param S-Token
*
*************************************************************
*************************************************************
*/

$app->GET('/api/vente/list_ventes/month', function(Application $app, Request $request) {


    if (null === $token = $request->headers->get('S-Token')) {
        return error401();
    }

    if (isTokenExpired($app, $token)) {
        return false;
    } else {

        $pdo = $app["PDO"];

        foreach( $pdo->query("  SELECT * FROM `p_ventes`
                                INNER JOIN p_recettes on p_recettes.id_recette = p_ventes.recette_vente
                                WHERE `date_vente` >= now() - interval 1 month") as $row) {
            $d[] = $row;
        }
        return new Response(json_encode(utf8_converter($d)), 200, array('Content-Type' => 'application/json'));
    }
});


/**
*************************************************************
*************************************************************
* SELECT VENTES DE L'ANNÉE
*
* POST
*
***** Body *****
* @param S-Token
*
*************************************************************
*************************************************************
*/

$app->GET('/api/vente/list_ventes/year', function(Application $app, Request $request) {



    if (null === $token = $request->headers->get('S-Token')) {
        return error401();
    }

    if (isTokenExpired($app, $token)) {
        return false;
    } else {

        $pdo = $app["PDO"];

        foreach( $pdo->query("  SELECT * FROM `p_ventes`
                                INNER JOIN p_recettes on p_recettes.id_recette = p_ventes.recette_vente
                                WHERE `date_vente` >= now() - interval 1 year") as $row) {
            $d[] = $row;
        }
        return new Response(json_encode(utf8_converter($d)), 200, array('Content-Type' => 'application/json'));
    }
});

function utf8_converter($array)
{
    array_walk_recursive($array, function(&$item, $key){
        if(!mb_detect_encoding($item, 'utf-8', true)){
                $item = utf8_encode($item);
        }
    });

    return $array;
}



/**
***********************************
*    BDD CONNEXION
***********************************
*/

$dbname = "aangehrn";
$dbuser = "aangehrn";
$dbpass = "H6JJbkyL3h";
$host = "localhost";
$app['PDO'] = new PDO("mysql:dbname=$dbname;host=$host", $dbuser, $dbpass);

$app->run();
