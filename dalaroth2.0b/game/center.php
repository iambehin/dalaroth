<?php
header("Content-Type: text/xml");
session_start();
error_reporting(E_ALL);
ini_set('display_errors', '1');

include "mysql.php";

		$result = @mysql_query("SELECT * FROM players WHERE name='".$_SESSION['uname']."'",$db);
		$_SESSION["userinfo"] = mysql_fetch_assoc($result);
		$_SESSION["weaponstats"] = mysql_fetch_array(mysql_query("SELECT * FROM items WHERE number='".$_SESSION["userinfo"]["weapon"]."'",$db));
		$_SESSION["armorstats"] = mysql_fetch_array(mysql_query("SELECT * FROM items WHERE number='".$_SESSION["userinfo"]["armor"]."'",$db));
		

?>

<behin_db><!-- lazslo only understands xml input in this version (3.3)-->

<!--
<form method=post action=#>

<input type=text style="width:65%;" name=chat><button type=submit value="Chat!">Chat</button></form>
-->

<?php

if(!isset($_SESSION["uname"]))
	die("You're not signed in");



if($_GET['action'] == "chat")
{
	$chat = htmlspecialchars($_GET['text'], ENT_QUOTES);
	if(strlen($chat) > 0)
		$chat_result = @mysql_query("INSERT INTO chat(time,text,user) VALUES ('" . date('D, M d G:i:s') . "','$chat','" . ucfirst($_SESSION['uname']) . "')",$db) or die(mysql_error());
}
$chat_result = @mysql_query("SELECT * FROM chat ORDER BY number DESC LIMIT 0,15",$db);
$chat_archive = array();
if(mysql_num_rows($chat_result) > 50)
	$chat_num_rows = 25;
else
	$chat_num_rows = mysql_num_rows($chat_result) or die(mysql_error());
for($chat_pointer = 0;$chat_pointer < $chat_num_rows;$chat_pointer++)
{
	$chats = @mysql_fetch_assoc($chat_result);
	array_push($chat_archive,$chats);
}

foreach($chat_archive as $chat_archive_portion)
{
	echo "<chat><time>".$chat_archive_portion["time"] . "</time> <user>" . $chat_archive_portion["user"] . "</user> <text>" .  $chat_archive_portion["text"] . "</text></chat>\n";
}


?>

//lazslo only accepts xml data, so we're outputting it in that format
echo "

<behin_db>

	<center>".
	"Your name is ".$_SESSION['uname'];
	print_r($_SESSION);		//we end the echo and use print_r to print the array, then resume echoing
echo	".</center>





";//this ends the echo



</behin_db>

?>
