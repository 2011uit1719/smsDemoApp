<?php
ini_set( 'session.use_only_cookies', TRUE );				
ini_set( 'session.use_trans_sid', FALSE );

if(!isset($_SESSION))
{
    session_start();
}

define('TOKENLENGTH', 128);

require_once 'autoloader.php';
require_once 'config/database.php';
require_once 'config/config.php';

$db = new Database();
$conn = $db->getDbConn();

?>
