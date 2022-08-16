<?php

define('GLPI_ROOT', '../../..');
include(GLPI_ROOT . '/inc/includes.php');

$Ticket = new Ticket();
$Ticket->getFromDB($_POST['id']);
$name = Html::Clean($Ticket->fields['name']);
$idTicket = Html::Clean($Ticket->fields['id']);

$name = str_replace('"', "", $name);
$name = str_replace("'", "", $name);
$name =  str_replace("/", "-", $name);
$description = Html::Clean($Ticket->fields['content']);
$description = str_replace("/", "-", $description);
$description = str_replace('"', "",  $description);
$description = str_replace("'", "",  $description);
$description = preg_replace('<(\s*)img[^<>]*>','-',$description);

$AreaPath = "X\\\GLPi";
$URLGLPI = "http://glpi.local/front/ticket.form.php?id=".$idTicket ;
$LinkGLPI = "<a target='_blank' href='http://glpi.local/front/ticket.form.php?id=$idTicket'>Acesse Aqui!!!</a>" ;
$CSistema = "GLPI";

 /*
 * POST via curl para criação do backlog com os campos preenchidos do System.Title e System.Description 
 **/

$UserStory = "curl --silent --show-error --fail -u {{PAT}} --location -X POST 'https://dev.azure.com/{{ORG}}/{{Project}}/_apis/wit/workitems/\$User%20Story?api-version=6.0' -H 'Content-Type:application/json-patch+json' -d '[ { \"op\":\"add\", \"path\":\"/fields/System.Title\", \"value\":\"". $name  ."\" }, { \"op\":\"add\", \"path\":\"/fields/System.Description\", \"value\":\" ".$description." => Link Chamado no GLPI:  ".$LinkGLPI."   \" }, { \"op\":\"add\", \"path\":\"/fields/Custom.Sistema\", \"value\":\"". $CSistema  ."\" } , { \"op\":\"add\", \"path\":\"/fields/LinkGLPI\", \"value\": \"". $URLGLPI ."\" }  , { \"op\":\"add\", \"path\":\"/fields/System.AreaPath\", \"value\": \"". $AreaPath ."\" }  ]'";

exec($UserStory,$out,$ret_var);


if($ret_var == '0') {
    Session::AddMessageAfterRedirect(__("Ticket cadastrado com sucesso na Azure", 'azurecard', true, INFO));
}
else{
    Session::AddMessageAfterRedirect(__("Houve algum problema na geração do Ticket na Azure", 'azurecard', true, ERROR));
}

$url = explode("?", $_SERVER['HTTP_REFERER']);
Html::redirect($url[0] . "?id=" . $_POST['id']);

