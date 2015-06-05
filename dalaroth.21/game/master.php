<?php
header("Content-Type: text/xml");
session_start();
error_reporting(E_ALL);
ini_set('display_errors', '1');

include "mysql.php";


//pull all the information from database:

		$result = @mysql_query("SELECT * FROM players WHERE name='".$_SESSION['uname']."'",$db);
		$_SESSION["userinfo"] = mysql_fetch_assoc($result);
		$_SESSION["weaponstats"] = mysql_fetch_assoc(mysql_query("SELECT * FROM items WHERE number='".$_SESSION["userinfo"]["weapon"]."'",$db));
		$_SESSION["armorstats"] = mysql_fetch_assoc(mysql_query("SELECT * FROM items WHERE number='".$_SESSION["userinfo"]["armor"]."'",$db));

		//Now lets take the armor stats and change it to an array.
		global $armorstats;
		$armor=explode(",",$_SESSION["armorstats"]["effect"]);
		//this gives an array that looks like [0] => damage:1-3 [2] => armor:0  .... etc
		//now we take that array and turn it into an associative array using the property before the colon as the key.
		foreach($armor as $stat)
		{
			$stat = explode(":",$stat);
			$armorstats[$stat[0]]=$stat[1];
		}


		global $weaponstats;
		$weapon=explode(",",$_SESSION["weaponstats"]["effect"]);
		foreach($weapon as $stat)
		{
			$stat = explode(":",$stat);
			$weaponstats[$stat[0]]=$stat[1];
		}
		
$_SESSION["AC"]=20+$armorstats["armor"];


?>

<behin_db><!-- lazslo only understands xml input in this version (3.3)-->
<pre class="code">
&lt;view x="50" y="50" width="300" 
      height="20" 
      bgcolor="red"&gt;
    &lt;view x="50" y="50" bgcolor="blue" /&gt;
&lt;/view&gt;
</pre>


<!--
<form method=post action=#>

<input type=text style="width:65%;" name=chat><button type=submit value="Chat!">Chat</button></form>
-->





<chat>
<?php
global $action;


if(!isset($_SESSION["uname"]))
	die("You're not signed in, ".htmlspecialchars('<u><a href="').'../"'.htmlspecialchars('>')."Click here".htmlspecialchars('</a></u>'));


if(isset($_GET['action']))
	$action = $_GET['action'];


	$travelable = explode(",",$_SESSION["location"]["travel"]);





if($action == "north" && in_array("n",$travelable))
	$_SESSION["userinfo"]["y"]++;
if($action == "south" && in_array("s",$travelable))
	$_SESSION["userinfo"]["y"]--;
if($action == "east" && in_array("e",$travelable))
	$_SESSION["userinfo"]["x"]++;
if($action == "west" && in_array("w",$travelable))
	$_SESSION["userinfo"]["x"]--;
if($action == "northeast" && in_array("ne",$travelable))
{
	$_SESSION["userinfo"]["x"]++;
	$_SESSION["userinfo"]["y"]++;
}
if($action == "northwest" && in_array("nw",$travelable))
{
	$_SESSION["userinfo"]["x"]--;
	$_SESSION["userinfo"]["y"]++;
}
if($action == "southeast" && in_array("se",$travelable))
{
	$_SESSION["userinfo"]["x"]++;
	$_SESSION["userinfo"]["y"]--;
}
if($action == "southwest" && in_array("sw",$travelable))
{
	$_SESSION["userinfo"]["x"]--;
	$_SESSION["userinfo"]["y"]--;
}
if($action == "reset")
{
	$_SESSION["userinfo"]["x"]=0;
	$_SESSION["userinfo"]["y"]=0;
}



if($action == "chat")
{
	$chat = htmlspecialchars(htmlspecialchars($_GET['text'], ENT_QUOTES));
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
	echo htmlspecialchars("<i>").$chat_archive_portion["time"] . " : ".htmlspecialchars("</i><b> ") . $chat_archive_portion["user"] . htmlspecialchars(" </b>") ."\n". $chat_archive_portion["text"] . "\n";
}
?>
</chat>




<center><![CDATA[<!--this comment prevents vim from being confused by the CDATA that openlaszlo requires in order to parse html --><?php



$result = @mysql_query("SELECT * FROM locations WHERE x = '".$_SESSION['userinfo']['x']."' AND y = '".$_SESSION['userinfo']['y']."' LIMIT 1") OR die(mysql_error());



		$_SESSION["location"] = mysql_fetch_assoc($result);
	$travelable = explode(",",$_SESSION["location"]["travel"]);
if(strlen($_SESSION["location"]["file"]) < 4)
	include "locations/invalid.php";
else
	include "locations/".$_SESSION['location']['file'];
?>]]>EOF</center>
	
	<Nwest><?php

		if(in_array("nw",$travelable))
			echo "N-West";
		else
			echo htmlspecialchars("<font color='#999999'>N-West</font>");
?></Nwest>
	<North><?php

		if(in_array("n",$travelable))
			echo "North";
		else
			echo htmlspecialchars("<font color='#999999'>North</font>");
?></North>
	<Neast><?php

		if(in_array("ne",$travelable))
			echo "N-East";
		else
			echo htmlspecialchars("<font color='#999999'>N-East</font>");
?></Neast>
	<West><?php

		if(in_array("w",$travelable))
			echo "West";
		else
			echo htmlspecialchars("<font color='#999999'>West</font>");
?></West>
	<East><?php

		if(in_array("e",$travelable))
			echo "East";
		else
			echo htmlspecialchars("<font color='#999999'>East</font>");
?></East>
	<Swest><?php

		if(in_array("sw",$travelable))
			echo "S-West";
		else
			echo htmlspecialchars("<font color='#999999'>S-West</font>");
?></Swest>
	<South><?php

		if(in_array("s",$travelable))
			echo "South";
		else
			echo htmlspecialchars("<font color='#999999'>South</font>");
?></South>
	<Seast><?php

		if(in_array("se",$travelable))
			echo "S-East";
		else
			echo htmlspecialchars("<font color='#999999'>S-East</font>");
?></Seast>


		<stat1><![CDATA[<!--this comment prevents vim from being confused by the CDATA that openlaszlo requires in order to parse html --><?php

	include "xptable.php";
	$_exptable = _the_exp_table();


echo 	"Your <i>Name</i> is <b>".ucFirst($_SESSION["userinfo"]["name"])."</b>.\n".
	"Your <i>Race</i> is <b>".ucFirst($_SESSION["userinfo"]["race"])." ".ucfirst($_SESSION["userinfo"]["sex"])."</b>.\n".
	"Your <i>Number</i> is <b>".ucfirst($_SESSION['userinfo']['number'])."</b>.\n".
	"You are <i>Level</i>&nbsp;<b>".ucfirst($_SESSION["userinfo"]["level"])."</b> with  <b>".ucfirst($_SESSION["userinfo"]["exp"])."</b> of <b>" . $_exptable[$_SESSION["userinfo"]["level"]]."</b> experience until next level.\n".
	"You have <b>".$_SESSION["userinfo"]["hp"] . " </b> <i>Health</i> out of <b>" . $_SESSION["userinfo"]["maxhp"]."</b>.\n".
	"You are carrying <b>".$_SESSION["userinfo"]["gold"]."</b>&nbsp;<i>Gold</i>.\n".
	"You have <b>" . $_SESSION["userinfo"]["str"] . "</b>&nbsp;<i>Strength</i>.\n".
	"You have <b>" . $_SESSION["userinfo"]["wis"] . "</b>&nbsp;<i>Wisdom</i>.\n".
	"You have <b>" . $_SESSION["userinfo"]["intel"] . "</b>&nbsp;<i>Intelligence</i>.\n".
	"You have <b>" . $_SESSION["userinfo"]["dex"] . "</b>&nbsp;<i>Dexterity</i>.\n".
	"You have <b>" . $_SESSION["userinfo"]["con"] . "</b>&nbsp;<i>Constitution</i>.\n".
	"Sent Action is:".$action;
?>]]></stat1>


	<stat2><![CDATA[<!-- This tag lets me use html, because simply echoing stuff using php prevents the double spaces in the xml for laszlo evidently--><?php
	
	echo "You are located at:X=".$_SESSION["userinfo"]["x"].", Y=".$_SESSION["userinfo"]["y"]."!\n";
	print_r($_SESSION);		
	print_r($armorstats);
	print_r($weaponstats);
?>]]></stat2>



<x>x<?php echo $_SESSION["userinfo"]["x"]; ?>
</x>
<y>y<?php echo $_SESSION["userinfo"]["y"]; ?>
</y>

</behin_db>

<?php
//This little loop updates the player stats in the database
	foreach($_SESSION["userinfo"] as $userStat=>$userinfos)
		 mysql_query("UPDATE players SET ".$userStat." = '".$userinfos."' WHERE name = '".$_SESSION["uname"]."'",$db) or die(mysql_error());

?>
