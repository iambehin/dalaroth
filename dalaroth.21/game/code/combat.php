<?php


docombat();

global $combat;
$_SESSION["userinfo"]["combat"] = implode("|",$combat);
unset($combat);


function treasure($_cr)
{
	echo "Your treasure roll: ";
	$goldroll = roll("1d100",true);
   //these arrays deal with the treasure:
   $goldtier1 = array(".5" => "1d3"," ","1d6","1d10","2d10","3d10");
   $goldtier2 = array(".5" => "1d4"," ","1d8","2d10","4d8","4d12");
   $goldtier3 = array(".5" => "1d8"," ","2d8","4d10","10d4","10d6");
   $goldtier4 = array(".5" => "5d2"," ","10d4","20d8","10d10","10d8");
   if($goldroll < 15)
		return 0;
   elseif($goldroll < 25)
		return roll($goldtier1[$_cr],true);
   elseif($goldroll < 50)
		return roll($goldtier2[$_cr],true);
   elseif($goldroll < 90)
   	return roll($goldtier3[$_cr],true);
   elseif($goldroll < 101)
   	return roll($goldtier4[$_cr],true);
   else
   	return "hate";

}//end treasure()


function encounter()
{
	global $db;
	if(isset($_POST["set_difficulty"])&& $_POST["set_difficulty"] <= 5 && $_POST["set_difficulty"] >= 0)
	{
		$_SESSION["userinfo"]["difficulty"] = $_POST["set_difficulty"];
		update_db("difficulty");
	}

						 	

	$combat_result = @mysql_query("SELECT * FROM monsters WHERE x_max > ".$_SESSION["userinfo"]["x"]." AND y_max > ".$_SESSION["userinfo"]["y"]." AND x_min < ".$_SESSION["userinfo"]["x"]." AND y_min < ".$_SESSION["userinfo"]["y"]) or die(mysql_error());
	$combat_monsters = array();
	$num_mobs = $_SESSION["userinfo"]["difficulty"]+0;
	while($num_mobs>0)
	{
		$combat_monsters[] = "-1";
		$num_mobs--;
	}


// if a non array is selected, then no encounter.  Thus controlling encounter rate


	for($combat_pointer = 0;$combat_pointer < mysql_num_rows($combat_result);$combat_pointer++)
		array_push($combat_monsters, mysql_fetch_assoc($combat_result));
	$combat_monster = $combat_monsters[mt_rand(0, count($combat_monsters)-1)];
	global $combat;
	if(is_array($combat_monster))
		$combat[0]="yes";
	return $combat_monster;
}//end encounter





function docombat()
{
	global $db;
	global $display_Travel;
	global $combat;

	$display_Travel = false;//This tells CenterPane not to display the "You may travel using blah blah" message, since we will if its applicable.

	$combat = explode("|",$_SESSION["userinfo"]["combat"]);
	if(isset($_GET['action']))
		$combat[0] = $_GET['action'];



	if(isset($_POST["combat"])) 
		$combat[0] = $_POST["combat"];

	if(count($combat) > 1 && $combat[0] !== "no" && strlen($combat[0])>0)
	{
		$combat_monster = mysql_fetch_assoc(mysql_query("SELECT * FROM monsters WHERE number='". $combat[1] . "'",$db));
		$combat_monster["hp"]=$combat[2];
	}
	else
		$combat_monster = encounter();
	if(is_array($combat_monster)) //check if a monster was encountered
		echo do_encounter($combat_monster);
	else //if no encountered monster
	{
		echo "You hear the sounds of monsters, but nothing attacks you.<br/>\n";
		echo "You may travel using the travel buttons on the right.\n<br/>";
	}


}//end docombat()



function print_attack_form($combat_monster)
{
	global $can_I_Travel;
	$can_I_Travel = false;
	echo "You may attack the monster.\n".
	"You may not flee this beast though.\n<br/>\n";

}//end print_attack_form()


function monster_turn($combat_monster)
{
	global $combat;
	$monster_attackroll = mt_rand(0,20)+$combat_monster["tohit"];
	if($monster_attackroll < $_SESSION["AC"])
	{
		$monster_hit=false;
		$monster_damage_done = 0;
		echo "<font color='#AA66AA'>The ".$combat_monster["name"]." failed to hit you.</font>\n<br/>";
	}
	else 
	{
		$monster_hit=true;
		$monster_damage_done = roll($combat_monster["damage"],false);
		echo "<font color='#FFAA33'>".$combat_monster["name"] . " " . $combat_monster["attack"] . " for $monster_damage_done damage.</font>\n<br/>";
		$_SESSION["userinfo"]["hp"] -=$monster_damage_done;
	}
	$traveling = "combat";
	if($_SESSION["userinfo"]["hp"] < 1)
	{
		$_SESSION["userinfo"]["hp"] = 1;
		$_SESSION["userinfo"]["x"] = 0;
		$_SESSION["userinfo"]["y"] = 0;
		$_SESSION["userinfo"]["travelable"] = " ";
		$combat[0]="no";
		echo "<font size='20' color='#99FF33'>You have died.</font>\n<br/>Bad things happen!\n<br/>You've been teleported to the vahth plaza.  <br/><br/<br/>";
		include "locations\vahth\main.php";
		return 5;
	}

}//end monster_turn()

function player_turn($combat_monster)
{
	global $combat;
	global $weaponstats;
	$weapondamage=$weaponstats;
	$attackroll = roll("1d20",true);
	if($attackroll >= $weapondamage["critrange"])
		$_crit=true;
	else
		$_crit=false;
	$attackroll+=floor($_SESSION["userinfo"]["str"]/2 - 5);
	if($attackroll < $combat_monster["AC"])
		echo "<font color='#BB9977'>Your Modified Attack Roll of $attackroll failed to hit the ".$combat_monster["name"].".</font>\n<br/>";
	else
	{
		echo "<font color='#44FF88'>Your Modified Attack Roll of $attackroll succeeded to hit the ".$combat_monster["name"].".</font>\n";
		$damage_done = roll($weapondamage["damage"],true) + floor($_SESSION["userinfo"]["str"]/2-5);
		if($_crit)
		{
			echo "<font color='#00FF44'>You made a critical hit!</font>\n<br/>";
			$damage_done*=$weapondamage["crit"];
		}
		echo "<font color='#00FF88'>You deal ".$combat_monster['name']." $damage_done damage.</font><br/>\n";
		$combat_monster["hp"]-=$damage_done;
	}
	if($combat_monster["hp"] > 0)
		echo "The Monster has <font color='#993311' size='21'> " . $combat_monster["hp"] . " health</font>.\n<br/><br/>";
	else
		echo "<font color='#445533'>You did " . -$combat_monster["hp"] . " more damage than " . $combat_monster["name"] . " had health left.</font>\n<br/><br/>";
	if($combat_monster["hp"] < 1)
		$_SESSION["userinfo"]["combat"] = "Victory";
	$combat[2] = $combat_monster["hp"]; 
	if($_SESSION["userinfo"]["combat"] === "Victory")
	{
		echo "You defeat the monster!\n<br/>";
		$_exp = $combat_monster["CR"]*50*($combat_monster["CR"]/$_SESSION["userinfo"]["level"]);
		$expgain = mt_rand($_exp/5,$_exp);
		$_SESSION["userinfo"]["exp"]+=$expgain;
		levelup();
		echo "You gain $expgain exp.\n<br/><br/>";
           echo "<font color='#1188FF'><b><u>Treasure:</u></b></font>\n";
		$goldgain = treasure($combat_monster["CR"]);
		$_SESSION["userinfo"]["gold"]+=$goldgain;
           $combat[0] = "no";

		echo "You find $goldgain gold pieces.\n<br/>";
		echo "You may travel.\n<br/>";
	}
	else
		print_attack_form($combat_monster);

   return $combat_monster;
}//end player_turn()


function do_encounter($combat_monster)
{

	global $combat;
	$combat[1]=$combat_monster["number"];
	$combat[2]=$combat_monster["hp"];

	if($combat[0] !== "Attack")
	{
		$combat[0] == "yes";

		echo "You see a " . $combat_monster["name"] . ".\n<br/><img src='".$combat_monster["image"]."' />\n";
		print_attack_form($combat_monster);
	}
	elseif($combat[0] === "Attack")
	{
           //if(monster_turn($combat_monster) == 5);
           //echo "null";
           $combat_monster=player_turn($combat_monster);
	}
	elseif($_SESSION["userinfo"]["combat"] === "Heal")
	{

	}
   

}//end do_encounter()



function roll($_dice,$show)
{
echo "You have rolled a $_dice:";
	$returnthis = 0;
	foreach(explode(",",$_dice) as $_dice)
	{
		$dice = explode("d",$_dice);
		$multiplier = $dice[0];
		$dice = explode("+",$dice[1]);
		while($multiplier > 0)
		{
			$_roll = mt_rand(1,$dice[0]+0);
			if($show)
				echo " <font color='#99FF99' size='52'>$_roll  </font> ";
			$returnthis += $_roll;
			$multiplier--;
		}
		if($show)
           	echo "\n<br/>";
		if(isset($dice[1]))
			$returnthis += $dice[1];
	}
	return $returnthis;
}//end roll(


function levelup()
{
	include "code/xptable.php";
	$_exptable = _the_exp_table();
	while($_SESSION["userinfo"]["exp"] > $_exptable[$_SESSION["userinfo"]["level"]])
	{
		$_SESSION["userinfo"]["level"]++;
		$gainedhp = mt_rand($_SESSION["userinfo"]["maxhp"]*.1,floatval($_SESSION["userinfo"]["maxhp"])*.2);
		$gainedcredits = mt_rand(2,5);
		echo "You have leveled up!\n<br/>";
		echo "You gain $gainedhp Hit Points.\n<br/>";
		echo "You are now level ".$_SESSION["userinfo"]["level"].".\n<br/><br/>\n";
		$_SESSION["userinfo"]["maxhp"] +=$gainedhp;
		$_SESSION["userinfo"]["hp"] = $_SESSION["userinfo"]["maxhp"];
	}
	return false;
}//end levelup(

?>
