You are in the majestic Town Hall of the city of Vahth.
<br/><br/>
<?

$questInfo = explode(":",$_SESSION["userinfo"]["ahlon_quest"]);
if(count($questInfo) < 2)
{
echo "ERROR:Can't find your quest info.\n<br/>";
return false;
}
/*Quest info table:
0,0 : Haven't started the quest
0,1 : Has Accepted the Quest
0,2 : Is on the quest
0,3 : Has completed the quest
1,0 : Has received the reward.*/

if($questInfo[0] =="0" && $questInfo[1] == "0")
{

	if(isset($_POST["quest_0_1"])&&$_POST["quest_0_1"] === "yes")
	{
		$questInfo[0]=0;
		$questInfo[1]=1;
		echo " \"Thank you.\" 
		<br/>";
	}
	elseif(isset($_POST["quest_0_1"])&&$_POST["quest_0_1"]=== "no")
		echo "\" Thank you for your time, nonetheless. \"\n<br/>\n";
	else
	{
		?>
		<i>A denizen of Vahth approaches you warmly.</i>
		<p class=indent>
		"
		My name is Gal, welcome to Vahth, friend.
		I see you are an adventurer, I'm afraid we are not a warriour people.
		However, these are dark times, and even peaceful villagers need protection.
		On behalf of the villagers of Vahth, I have a request for you.
		Our grass has had an evil enchantment cast upon it, by The Witch.
		It no longer grows peacefully and pads our feet, but throws acid at us.
		We have lost many a life to this grass, and cannot grow crops safely.
		Farmers require an escort merely to plant crops.
		"
		</p>
		"
		Will you escort my friend, Ahlon to his field, and keep guard while he collects his crop?
		"
		<form action=# method=POST>
			<button name="quest_0_1" value=yes>Yes, I'll be glad to.</button>
			<button name="quest_0_1" value=no>No, this is not my problem</button>
		</form>
		<?
	}


}
if (isset($_POST["quest_0_2"])&&$questInfo[0] == "0" && $questInfo[1] == "1")
{
	if($_POST["quest_0_2"] == "yes")
	{
		$_SESSION["userinfo"]["backpack"].="14,";
		update_db("backpack");
		$questInfo[0]=0;
		$questInfo[1]=2;
		echo "\" Thank you once again for the assistance, and please take care of Ahlon. \"
		<br/>";
	}
	elseif(isset($_POST["quest_0_2"])&&$_POST["quest_0_2"] === "no")
		echo "\" Very well, please return when you are ready. \"\n<br/>\n";
	else
	{
		?>
		" Thank you again for volunteering to help Ahlon. Do you wish to take him now? "
		<form action=# method=POST>
			<button name="quest_0_2" value=yes>Yes, I will escort him now</button>
			<button name="quest_0_2" value=no>No, I am not yet prepared</button>
		</form>
		<?
	}

}
if ($questInfo[0] == "0" && $questInfo[1] == "2")
{
	if(in_array("14",explode(",",$_SESSION["userinfo"]["backpack"])))
		echo "\" Please take Ahlon to his field.\n \" <br/>\n";
	else
		echo "\" My friend! You have killed Ahlon! \" :( <br/>\n";


}
if ($questInfo[0] == "0" && $questInfo[1] == "3")
{
	$questInfo[0]=1;
	$questInfo[1]="0";

	$bp = explode(",",$_SESSION["userinfo"]["backpack"]);
	unset($bp[array_search("14",$bp)]);
	$_SESSION["userinfo"]["backpack"] = implode(",",$bp);
	update_db("backpack");

	$_SESSION["userinfo"]["exp"] += 200;
	update_db("exp");
	$_SESSION["userinfo"]["credits"] += 7;
	update_db("credits");
	?>
	"Thank you for escorting Ahlon, the Villagers will forever remember your service to them."
	<br/>
	<br/>
	You have recieved 200 exp, and 7 stat credits!

	<br/>
	<br/>

	<?
	levelup();

}


$_SESSION["userinfo"]["ahlon_quest"]=implode(":",$questInfo);
update_db("ahlon_quest");

?>


<br/><br/><br/>