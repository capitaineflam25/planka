<?

// cette page comble l'absence d'exhaustivité de l'API Planka (les souscriptions ne sont pas gérées)
// on aurait pu héberger cette page chez Nuxit, mais la connexion Postgres n'est pas possible sur des serveurs externes avec l'offre mutualisée, il faut l'offre VPS
// et il faut un domaine spécifique, sinon Nuxit ne considère pas la requête, pour ça il suffit de faire un virtual host basé sur le nom dans Web Station du NAS Synology

// http://192.168.1.21:8888/?secure=1&card_id=1386470117142955095&list_id=1386469521937663055
// https://plankaapi.eedomus.com/?secure=1&card_id=1386470117142955095&list_id=1386469521937663055
// dans "p:\Baptiste\Dev\Docker\planka\planka\php_api\index.php"

if ($_GET['secure'] != 1) die("security error");
if ($_GET['card_id'] == '') die("empty card_id");

error_reporting(E_ALL);
ini_set('display_errors', 1);

$cx = "host=localhost connect_timeout=10 port=6432 dbname=planka user=postgres password=";
//echo $cx . "<br>";
$db_connection = pg_connect($cx);

$query = "SELECT DISTINCT email FROM user_account
WHERE is_admin = 't'
AND email != 'baptiste.vial@gadz.org' /*super admin qui ne sert à rien*/
OR id IN (SELECT user_id FROM card_subscription WHERE card_id = '" . $_GET['card_id'] . "')";
$result = pg_query($db_connection, $query);
//var_dump($query);
$res = '';
while ($rst = pg_fetch_array($result, null, PGSQL_ASSOC)) {
  $res .= $rst['email'] . "|";
}
$res = trim($res, ',');

$res .= "\n";

$ar_card_hidden = array();
$query = "SELECT card_id FROM card_label WHERE label_id = '1395853119987909976'";
$result = pg_query($db_connection, $query);
while ($rst = pg_fetch_array($result, null, PGSQL_ASSOC)) {
  $ar_card_hidden[] = $rst['card_id'];
}

$query = "SELECT id, name FROM card WHERE list_id = '". $_GET['list_id']."' ORDER BY position ASC ";
$result = pg_query($db_connection, $query);
while ($rst = pg_fetch_array($result, null, PGSQL_ASSOC)) {
  if (!in_array($rst['id'], $ar_card_hidden))
  {
    $res .= $rst['name'] . "|";
  }
}

echo $res;
pg_free_result($result);
pg_close($db_connection);
