<?php
/***************************************************************************
 * Xlrstats Webmodule
 * Webfront for XLRstats for B3 (www.bigbrotherbot.com)
 * (c) 2004-2010 www.xlr8or.com (mailto:xlr8or@xlr8or.com)
 ***************************************************************************/

/***************************************************************************
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Library General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 *
 *  http://www.gnu.org/copyleft/gpl.html
 ***************************************************************************/

// no direct access
defined( '_XLREXEC' ) or die( 'Restricted access' );

function tag_start_simple($parser, $attr, $params)
{
  if($attr == 'CLIENT')
  {
    global $clients;

    $tmp = "";
    $cli = $params['NAME'];

    if(isset($params['SCORE'])) $score = $params['SCORE'];
    else $score = 'n.a.';

    $clients[$params['CID']] = new client ($params['DBID'], $params['NAME'], $params['COLORNAME'],$params['LEVEL'], $params['CONNECTIONS'], $score, $params['CID'], $params['GUID'], $params['PBID'], $params['TEAM'], $params['STATE'], $params['IP']);
  }   
}


function tag_start($parser, $attr, $params)
{
  global $sv_privateClients;
  global $gameType;
  global $mapName;  
  global $sv_maxclients;
  global $sv_hostname;  
  global $shortversion;
  global $supportedgames;
  global $ffa_modes;
  global $pll_noteams;
  global $nameTeamRed;
  global $nameTeamBlue;
  global $nameSpectators;
	  
  if($attr == 'CLIENT')
  {
    //global $clients;
    global $clientsRed;
    global $clientsBlue;
    global $clientsSpec;  

    $tmp = "";
    $cli = $params['NAME'];

    if(isset($params['SCORE'])) $score = $params['SCORE'];
    else $score = 'n.a.';

    if (in_array($gameType, $ffa_modes) || ($pll_noteams != 0))
    {
      $clientsSpec[$params['CID']] = new client ($params['DBID'], $params['NAME'], $params['COLORNAME'],$params['LEVEL'], $params['CONNECTIONS'], $score, $params['CID'], $params['GUID'], $params['PBID'], $params['TEAM'], $params['STATE'], $params['IP']);
    }
    else
    {
      if($params['TEAM'] == 2)
        $clientsRed[$params['CID']] = new client ($params['DBID'], $params['NAME'], $params['COLORNAME'],$params['LEVEL'], $params['CONNECTIONS'], $score, $params['CID'], $params['GUID'], $params['PBID'], $params['TEAM'], $params['STATE'], $params['IP']);
  
      if($params['TEAM'] == 3)
        $clientsBlue[$params['CID']] = new client ($params['DBID'], $params['NAME'], $params['COLORNAME'],$params['LEVEL'], $params['CONNECTIONS'], $score, $params['CID'], $params['GUID'], $params['PBID'], $params['TEAM'], $params['STATE'], $params['IP']);
  
      if($params['TEAM'] == -1 || $params['TEAM'] == 1)
        $clientsSpec[$params['CID']] = new client ($params['DBID'], $params['NAME'], $params['COLORNAME'],$params['LEVEL'], $params['CONNECTIONS'], $score, $params['CID'], $params['GUID'], $params['PBID'], $params['TEAM'], $params['STATE'], $params['IP']);
    }
  }   
  else if($attr == 'DATA')
  {
    if($params['NAME'] == "sv_privateClients")
      $sv_privateClients = $params['VALUE'];

    if($params['NAME'] == "gameType")
      $gameType = $params['VALUE'];

    if($params['NAME'] == "sv_maxclients")
      $sv_maxclients = $params['VALUE'];

    if($params['NAME'] == "sv_hostname")
      $sv_hostname = removequake3color(htmlentities($params['VALUE']));

    if(in_array($params['NAME'], $supportedgames))
      $mapName = $params['MAP'];

    if($params['NAME'] == "shortversion")
      $shortversion = $params['VALUE'];
    else if($params['NAME'] == "version")
      $shortversion = $params['VALUE'];

    if($params['NAME'] == "g_teamnameblue")
      $nameTeamBlue = $params['VALUE'];

    if($params['NAME'] == "g_teamnamered")
      $nameTeamRed = $params['VALUE'];
  }
  else  if($attr == 'GAME')
  {
   if(in_array($params['NAME'], $supportedgames))
      $mapName = $params['MAP'];
  }
}

function tag_end($parser, $attr)
{
  //  empty
}

function loadSimpleData()
{
  global $b3_status_url;

  $clients = array();
  $parser = xml_parser_create();

  xml_set_element_handler($parser, 'tag_start_simple', 'tag_end');

  if(!($fp = fopen($b3_status_url, "r"))) 
  {
    die("Cannot open XML file!!!");
  }

  while($data = fread($fp, 4096)) 
  {
    $data = utf16_2_utf8($data ) ;
    if(!xml_parse($parser, $data, feof($fp)))
    {
      // empty
    }
  }
  //usort($clients "cmp");
  xml_parser_free($parser); // 7 
  //echo $b3_status_url;
}

function loadData() 
{     
  global $sv_privateClients;
  global $gameType;
  global $mapName;  
  global $sv_maxclients;
  global $sv_hostname;
  global $b3_status_url; 
  global $pll_noteams;
  global $nameTeamRed;
  global $nameTeamBlue;
  global $nameSpectators;

  $sv_privateClients = 0;
  $gameType = "";
  $mapName = "";
  $sv_maxclients = 0;
  $sv_hostname = ""; 

  global $clientsRed;
  global $clientsBlue;
  global $clientsSpec;    
  $clientsRed = 0;
  $clientsBlue = 0;
  $clientsSpec = 0;

  $clientsRed = array();
  $clientsSpec = array();
  $clientsBlue = array();

  $parser = xml_parser_create();

  xml_set_element_handler($parser, 'tag_start', 'tag_end');

  if(!($fp = fopen($b3_status_url, "r"))) 
    die("Cannot open XML file!!!");

  while($data = fread($fp, 4096)) 
  {
    $data = utf16_2_utf8($data ) ;
    if(!xml_parse($parser, $data, feof($fp)))
    {
      // empty  
    }
  }
  usort($clientsRed, "cmp");
  usort($clientsBlue, "cmp");
  usort($clientsSpec, "cmp");
  xml_parser_free($parser);
}

function currentplayers()
{
  global $currentconfig;
  global $currentconfignumber;
  include($currentconfig);

  $link = baselink();
  global $clientsRed;
  global $clientsBlue;
  global $clientsSpec;
  global $ffa_modes;
  global $gameType;
  global $text;
  global $nameTeamRed;
  global $nameTeamBlue;
  global $nameSpectators;
  
  if (!isset($pll_noteams))
    $pll_noteams = 0;

  echo "
    <table width=\"100%\" border=\"0\" cellspacing=\"1\" cellpadding=\"0\" class=\"outertable\">
    <tr><td align=\"center\">".$text["curplay"]."
  ";

  echo " (".(count($clientsBlue) + count($clientsRed) + count($clientsSpec))." ".$text["players"].")";
  if (file_exists("lib/worldmap/") && file_exists($geoip_path."GeoLiteCity.dat"))
    echo "&nbsp;&nbsp;&nbsp;<a href=\"worldmap/\" onclick=\"window.open('lib/worldmap/?config=$currentconfignumber', 'worldmap', 'width=550,height=300,scrollbars=no,toolbar=no,location=no'); return false\"><img src=\"images/ico/world_go.png\" border=\"0\" align=\"absbottom\" title=\"".$text["showmap"]."\"></a>";
  echo "
    </td></tr><tr><td>
    <table width=\"100%\" border=\"0\" cellspacing=\"1\" cellpadding=\"0\" class=\"innertable\">
    <tr class=\"outertable\">
    <td align=\"center\" width=\"40\">".$text["lp"]."</td>
    <td align=\"center\" width=\"300\">".$text["nick"]."</td>
    <td align=\"center\">".$text["score"]."</td>";

  if (file_exists($geoip_path."GeoIP.dat"))
    echo "<td align=\"center\">".$text["country"]."</td>";

  echo "    <td align=\"center\">".$text["level"]."</td>
    <!--<td align=\"center\">".$text["team"]."</td>-->
    <td align=\"center\">".$text["connections"]."</td>
    </tr>
  ";
 	
  if (in_array($gameType, $ffa_modes) || ($pll_noteams != 0))
    {
  	echo "      <tr><td colspan=6 align=\"center\" class=\"status-spectators\">Players (".count($clientsSpec).") </td></tr>";
  	addClients($clientsSpec, "white");
    }
  else
    {
    echo "      <tr><td colspan=6 align=\"center\" class=\"status-blueteam\">$nameTeamBlue (".count($clientsBlue).") </td></tr>";
  	addClients($clientsBlue, "#EFFBFB");
  	echo "      <tr><td colspan=6 align=\"center\" class=\"status-redteam\">$nameTeamRed (".count($clientsRed).") </td></tr>";
  	addClients($clientsRed, "#FBEFEF");
  	echo "      <tr><td colspan=6 align=\"center\" class=\"status-spectators\">$nameSpectators (".count($clientsSpec).") </td></tr>";
  	addClients($clientsSpec, "white");
    }

   echo " 
           </table> 
         </table>";
   flush();
}

function addClients($clients, $backgroundColor)
{           
  global $geoip_path;
  global $public_ip;
  global $currentconfignumber;

  $link = baselink();

  $x = 1;
  foreach($clients as $client)
  {       
    echo '<tr bgcolor="#cccccf" valign="middle">
    <td style="background: '.$backgroundColor.'none repeat scroll 0% 0%; -moz-background-clip: -moz-initial; -moz-background-origin: -moz-initial; -moz-background-inline-policy: -moz-initial; color: black;" align="center"><font class="fontNormal" size="2"><strong>'.$x.'</strong></font></td>';
    if($client -> levelInt == 0)
      echo '<td style="background: '.$backgroundColor.' none repeat scroll 0% 0%; -moz-background-clip: -moz-initial; -moz-background-origin: -moz-initial; -moz-background-inline-policy: -moz-initial; color: black;" align="center"><font class="fontNormal" size="2">'.htmlspecialchars(utf2iso($client -> name)).'</font></td>';
    else 
      echo '<td style="background: '.$backgroundColor.' none repeat scroll 0% 0%; -moz-background-clip: -moz-initial; -moz-background-origin: -moz-initial; -moz-background-inline-policy: -moz-initial; color: black;" align="center"><font class="fontNormal" size="2"><a href='.$link.'?func=player&playerdbid='.($client -> dbid).'&config=' .$currentconfignumber .'><strong>'.htmlspecialchars(utf2iso($client -> name)).'</strong></a></font></td>';
    echo '<td style="background: '.$backgroundColor.' none repeat scroll 0% 0%; -moz-background-clip: -moz-initial; -moz-background-origin: -moz-initial; -moz-background-inline-policy: -moz-initial; color: black;" align="center"><font class="fontNormal" size="2">'.$client -> score.'</font></td>';

    if (file_exists($geoip_path."GeoIP.dat"))
    {
      if ($client -> level == "BOT")
      {
        $tip = explode(":", $public_ip);
        $ip = $tip[0];
      }
      else
        $ip = $client -> ip;
      $geocountry = $geoip_path."GeoIP.dat";
      $gi = geoip_open($geocountry,GEOIP_STANDARD);
      $countryid = strtolower (geoip_country_code_by_addr($gi, $ip));
      $country = geoip_country_name_by_addr($gi, $ip);
      if ( !is_null($countryid) and $countryid != "") 
        $flag = "<img src=\"images/flags/".$countryid.".gif\" title=\"".$country."\" alt=\"".$country."\">";
      else 
        $flag = "<img width=\"16\" height=\"11\" src=\"images/spacer.gif\" title=\"".$country."\" alt=\"".$country."\">"; 

      geoip_close($gi);
      echo '<td style="background: '.$backgroundColor.' none repeat scroll 0% 0%; -moz-background-clip: -moz-initial; -moz-background-origin: -moz-initial; -moz-background-inline-policy: -moz-initial; color: black;" align="center"><font class="fontNormal" size="2">'.$flag.'</font></td>';
    }

    echo '<td style="background: '.$backgroundColor.' none repeat scroll 0% 0%; -moz-background-clip: -moz-initial; -moz-background-origin: -moz-initial; -moz-background-inline-policy: -moz-initial; color: black;" align="center"><font class="fontNormal" size="2">'.$client -> level.'</font></td>';
    echo '<td style="background: '.$backgroundColor.' none repeat scroll 0% 0%; -moz-background-clip: -moz-initial; -moz-background-origin: -moz-initial; -moz-background-inline-policy: -moz-initial; color: black;" align="center"><font class="fontNormal" size="2">'.$client -> connections.'</font></td>
    </tr>';
    $x++;
  }
}

//---------------------------------------------------------------------------------------------------
class client
{
  var $dbid;
  var $name;
  var $colorname;
  var $connections;
  var $score;
  var $cid;
  var $guid;
  var $pbid;
  var $team;
  var $level;
  var $levelInt;
  var $state;
  var $stateInt;
  var $ip;

  function client($dbid, $name, $colorname, $level, $connections, $score, $cid, $guid, $pbid, $team, $state, $ip)
  {
    global $team1;
    global $team2;
    global $spectators;

    $this -> dbid = $dbid;
    $this -> name = $name;
    $this -> colorname = $colorname;
    $this -> connections = $connections;
    $this -> score = $score;
    $this -> cid = $cid;
    $this -> guid = $guid;
    $this -> pbid = $pbid;

    if($team == -1 || $team == 1)
    $this -> team = $spectators;
    elseif($team == 2)
    $this -> team =  $team1;     
    elseif($team == 3)
    $this -> team =  $team2; 
    else 
    $this -> team = $team;

    if($ip == "0.0.0.0")
      $this -> level = "BOT";
    elseif($level == 0)
      $this -> level = "Not registered";
    elseif($level == 1)
      $this -> level = "<span style=\"color: #04B404;\">Registered</span>";
    elseif($level == 2)
      $this -> level = "<span style=\"color: #4B8A08;\">Registered+</span>";  //04B404//4B8A08// 868A08 // 6268F7 // 0174DF // DF0101 // B404AE
    elseif($level == 20)
      $this -> level = "<span style=\"text-decoration: underline; color: #868A08;\">Moderator</span>";           		
    elseif($level == 40)
      $this -> level = "<span style=\"text-decoration: underline; color: #6268F7;\">Admin</span>";        
    elseif($level == 60)
      $this -> level = "<span style=\"text-decoration: underline; font-weight: bold; color: #0174DF;\">Full Admin</span>"; 
    elseif($level == 80)
      $this -> level = "<span style=\"text-decoration: underline; font-weight: bold; color: #DF0101;\">Senior Admin</span>";        
    elseif($level == 100)
      $this -> level = "<span style=\"text-decoration: underline; font-weight: bold; color: #B404AE;\">GOD</span>"; 
    else         		    
      $this -> level = $level;

    $this -> levelInt = $level;

    if($state == 1)
      $this -> state = "<span style=\"font-weight: bold; color: #DF0101;\">Dead</span>";
    elseif ($state == 2)
      $this -> state = "<span style=\"color: #0174DF;\"></span>";//alive
    else 
      $this -> state = "<span style=\"color: #0174DF;\"></span>";//unknown

    $this -> stateInt = $state;
    $this -> ip = $ip;
  }
}
//---------------------------------------------------------------------------------------------------
?>
