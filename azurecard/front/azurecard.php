<?php

define('GLPI_ROOT', '../../..');
include(GLPI_ROOT . '/inc/includes.php');

function cleantags($value, $striptags = true, $keep_bad = 2) {
  $value = Html::entity_decode_deep($value);
  $regex = "/(<[^\"'>]+?@[^>\"']+?>)/";
  $value = preg_replace_callback($regex, function($matches) {
     return substr($matches[1], 1, (strlen($matches[1]) - 2));
  }, $value);

  $value = str_replace(["<![if !supportLists]>", "<![endif]>"], '', $value);

  if ($striptags) {
     $specialfilter = ['@<div[^>]*?tooltip_picture[^>]*?>.*?</div[^>]*?>@si',
                            '@<div[^>]*?tooltip_text[^>]*?>.*?</div[^>]*?>@si',
                            '@<div[^>]*?tooltip_picture_border[^>]*?>.*?</div[^>]*?>@si',
                            '@<div[^>]*?invisible[^>]*?>.*?</div[^>]*?>@si'];
     $value         = preg_replace($specialfilter, '', $value);

     $value = preg_replace("/<(p|br|div)( [^>]*)?".">/i", "\n", $value);
     $value = preg_replace("/(&nbsp;| |\xC2\xA0)+/", " ", $value);
  }

  $search = ['@<script[^>]*?>.*?</script[^>]*?>@si', // Strip out javascript
                  '@<style[^>]*?>.*?</style[^>]*?>@si', // Strip out style
                  '@<title[^>]*?>.*?</title[^>]*?>@si', // Strip out title
                  '@<!DOCTYPE[^>]*?>@si', // Strip out !DOCTYPE
                   ];
  $value = preg_replace($search, '', $value);
  $value = preg_replace("/(<)([^>]*<)/", "&lt;$2", $value);

  $config = Toolbox::getHtmLawedSafeConfig();
  $config['keep_bad'] = $keep_bad; // 1: neutralize tag and content, 2 : remove tag and neutralize content
  if ($striptags) {
     $config['elements'] = 'none';
  }

  foreach (['png', 'gif', 'jpg', 'jpeg'] as $imgtype) {
     $value = str_replace('src="denied:data:image/'.$imgtype.';base64,',
           'src="data:image/'.$imgtype.';base64,', $value);
  }
  $value = str_replace('"', "", $value);
  $value = str_replace("'", "", $value);
  $value = preg_replace('/<(\s*)img[^<>]*>/i'," ",$value); 

  return trim($value);
}

$Ticket = new Ticket();
$Ticket->getFromDB($_POST['id']);
$name = Html::Clean($Ticket->fields['name']);
$idTicket = Html::Clean($Ticket->fields['id']);
$description = Html::Clean($Ticket->fields['content']);

//sanitizing 
$name = cleantags($name);
$description = cleantags($description);

//Requester
$sql_resquester = "SELECT glpi_tickets.id AS id, glpi_users.firstname AS name, glpi_users.realname AS sname
FROM glpi_tickets_users , glpi_tickets, glpi_users
WHERE glpi_tickets.id = glpi_tickets_users.`tickets_id`
AND glpi_tickets.id = ". $idTicket ."
AND glpi_tickets_users.`users_id` = glpi_users.id
AND glpi_tickets_users.type =1";

$array_requester = array();
$i = 0;
$result_req = $DB->query($sql_resquester);
while($row_req = $DB->fetchAssoc($result_req)){
  $array_requester[$i] = $row_req['name']." ".$row_req['sname'];
  $i++;
}

$sql_group_requester = "SELECT  glpi_groups.name AS name
FROM glpi_groups_tickets, glpi_tickets, glpi_groups
WHERE glpi_groups_tickets.`groups_id` = glpi_groups.id
AND glpi_groups_tickets.`tickets_id` = glpi_tickets.id
AND glpi_tickets.id = ". $idTicket ."
AND glpi_tickets.is_deleted = 0
AND glpi_groups.is_requester = 1
GROUP BY name;";
 
$array_group_req = array();
$i = 0;
$result_group_req = $DB->query($sql_group_requester);
while($row_group_req = $DB->fetchAssoc($result_group_req)){
   $array_group_req[$i] = $row_group_req['name'];
   $i++;
}

$array_requesters_groups = array();
$array_requesters_groups = array_merge($array_requester,$array_group_req);
$requesters_group = implode("<br>",$array_requesters_groups);

//Assigned To
$sql_assigned_tech = "SELECT verdanadesk_hcrp.glpi_tickets.id AS id, verdanadesk_hcrp.glpi_users.firstname AS name, verdanadesk_hcrp.glpi_users.realname AS sname
FROM verdanadesk_hcrp.glpi_tickets_users , verdanadesk_hcrp.glpi_tickets, verdanadesk_hcrp.glpi_users
WHERE glpi_tickets.id = glpi_tickets_users.`tickets_id`
AND glpi_tickets.id = ". $idTicket ."
AND glpi_tickets_users.`users_id` = glpi_users.id
AND glpi_tickets_users.type = 2;";

$array_assigned_tech = array();
$i = 0;
$result_as_tech = $DB->query($sql_assigned_tech);
while($row_as = $DB->fetchAssoc($result_as_tech)){
  $array_assigned_tech[$i] = $row_as['name']." ".$row_as['sname'];
  $i++;
}

$sql_assigned_group = "SELECT  glpi_groups.name AS name
FROM verdanadesk_hcrp.glpi_groups_tickets, verdanadesk_hcrp.glpi_tickets, verdanadesk_hcrp.glpi_groups
WHERE glpi_groups_tickets.`groups_id` = glpi_groups.id
AND glpi_groups_tickets.`tickets_id` = glpi_tickets.id
AND glpi_tickets.id = ". $idTicket ."
AND glpi_tickets.is_deleted = 0
AND glpi_groups.is_requester = 0
GROUP BY name;";
 
$array_assigned_group = array();
$i = 0;
$result_as_group = $DB->query($sql_assigned_group);
while($row_group_as = $DB->fetchAssoc($result_as_group)){
   $array_assigned_group[$i] = $row_group_as['name'];
   $i++;
}

$array_as_techs_groups = array();
$array_as_techs_groups = array_merge($array_assigned_tech,$array_assigned_group);
$tech_assigned_groups = implode("<br>",$array_as_techs_groups);

//Datetime
$sql_opening_date = "SELECT DATE_FORMAT(glpi_tickets.date, '%d-%m-%Y %H:%i:%s') AS Date 
FROM glpi_tickets 
WHERE glpi_tickets.id = ". $idTicket .";";

$result_datetime = $DB->query($sql_opening_date);
$opening_datetime = $DB->fetchAssoc($result_datetime);
$odatetime = $opening_datetime['Date'];

$AreaPath = "X\\\GLPi";
$URLGLPI = "http://glpi.local/front/ticket.form.php?id=".$idTicket ;
$LinkGLPI = "<a target='_blank' href='http://glpi.local/front/ticket.form.php?id=$idTicket'>Acesse Aqui!!!</a>" ;
$InitiativeTeamFeature = "AzureCard-Glpi";

 /*
 * POST via curl para criação do backlog 
 **/

$Feature =   "curl --silent --show-error --fail -u {{PAT}} ";
$Feature .=  "--location -X POST 'https://dev.azure.com/{ORG}}/{{Project}}/_apis/wit/workitems/\$feature?api-version=6.0' ";
$Feature .=  "-H 'Content-Type:application/json-patch+json' -d '[ { \"op\":\"add\", \"path\":\"/fields/System.Title\", \"value\":\"". $name  ."\" },";
$Feature .=  "{ \"op\":\"add\", \"path\":\"/fields/System.Description\", \"value\":\" ".$description." => Link Chamado no GLPI:  ".$LinkGLPI."   \" },";
$Feature .=  "{ \"op\":\"add\", \"path\":\"/fields/Initiative Team Feature\", \"value\":\"". $InitiativeTeamFeature  ."\" },";
$Feature .=  "{ \"op\":\"add\", \"path\":\"/fields/LinkGLPI\", \"value\": \"". $URLGLPI ."\" },";
$Feature .=  "{ \"op\":\"add\", \"path\":\"/fields/System.AreaPath\", \"value\": \"". $AreaPath ."\" },";
$Feature .=  "{ \"op\":\"add\", \"path\":\"/fields/Custom.Requerente\", \"value\": \"". $requesters_group ."\" },";
$Feature .=  "{ \"op\":\"add\", \"path\":\"/fields/Custom.3ee63a2e-81b0-488a-83f0-244e909d08b6\", \"value\": \"". $tech_assigned_groups ."\" },";
$Feature .=  "{ \"op\":\"add\", \"path\":\"/fields/Custom.DatadeAbertura\", \"value\": \"". $odatetime ."\" } ]'";            
     
exec($Feature,$out,$ret_var);

if($ret_var == '0') {
    Session::AddMessageAfterRedirect(__("Ticket gerado com sucesso na Azure", 'azurecardhml', true, INFO));
}
else{
    Session::AddMessageAfterRedirect(__("Houve algum problema na geração do Ticket na Azure", 'azurecardhml', true, ERROR));
}

$url = explode("?", $_SERVER['HTTP_REFERER']);
Html::redirect($url[0] . "?id=" . $_POST['id']);





