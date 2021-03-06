<?php
	session_start();
	$host = "dijkstra.ug.bcc.bilkent.edu.tr";
	$myUser = "mehmet.turanboy";
	$myPassword = "1ky0yl0r";
	$myDB = "mehmet_turanboy";
	$connection = mysqli_connect($host, $myUser, $myPassword, $myDB);
	if ($_SESSION['loggedIn'] != true){
		header("Location: login.php");
		exit();
	}
	if (isset($_POST['logout'])){
		$_SESSION['loggedIn'] = false;
		header("Location: login.php");
		exit();
	}
	$teamID = $_SESSION['favTeamID'];
	
	$clubQuery = "SELECT * FROM Club WHERE ID = '".$teamID."'";
	$club = mysqli_query($connection, $clubQuery)->fetch_object();
	$myImage = '<img src="images/'.$club->name.'"'.'style="height:200px; width: 280px">';
	
	$playersQuery = "SELECT Player.name, Player.surname FROM Club, Plays, Player WHERE Club.ID = Plays.clubID AND Plays.playerID = Player.ID AND Club.ID = '".$club->ID."'";
	$players = mysqli_query($connection, $playersQuery);
	
	$coachQuery = "SELECT Coach.name, Coach.surname FROM Coach WHERE Coach.ClubID = '".$club->ID."'";
	$coach = mysqli_query($connection, $coachQuery)->fetch_object();
	
	$leaguesQuery = "SELECT League.name FROM League, League_Club WHERE League.ID = League_Club.leagueID AND League_Club.clubID = '".$club->ID."'";
	$leagues = mysqli_query($connection, $leaguesQuery);

	$clubsQuery = "SELECT Game.ID AS ID, Game.home_teamID AS ID1, Game.away_teamID AS ID2 
				   FROM Game 
				   WHERE Game.home_teamID = '".$club->ID."' OR Game.away_teamID = '".$club->ID."' ORDER BY Game.game_date DESC";
	$clubs = mysqli_query($connection, $clubsQuery);
	
	$names = array();
	while ($row = mysqli_fetch_assoc($clubs)){ 
		$curHomeNameQuery = "SELECT Club.name FROM Club WHERE Club.ID = '".$row['ID1']."'";
		$curHomeName = mysqli_query($connection, $curHomeNameQuery)->fetch_object();
		$curPair = $curHomeName->name;
		
		$curHomeGoalsQuery = "SELECT COUNT(*) AS goals 
							  FROM Stats, Plays 
							  WHERE Stats.gameID = '".$row['ID']."' AND Stats.playerID = Plays.playerID 
									AND Plays.clubID = '".$row['ID1']."' AND Stats.action = '0'";
		$curHomeGoals = mysqli_query($connection, $curHomeGoalsQuery)->fetch_object();
		$curPair = $curPair." ".$curHomeGoals->goals." -";
		
		$curAwayGoalsQuery = "SELECT COUNT(*) AS goals 
							  FROM Stats, Plays 
							  WHERE Stats.gameID = '".$row['ID']."' AND Stats.playerID = Plays.playerID 
									AND Plays.clubID = '".$row['ID2']."' AND Stats.action = '0'";
		$curAwayGoals = mysqli_query($connection, $curAwayGoalsQuery)->fetch_object();
		$curPair = $curPair." ".$curAwayGoals->goals;
		
		$curAwayNameQuery = "SELECT Club.name FROM Club WHERE Club.ID = '".$row['ID2']."'";
		$curAwayName = mysqli_query($connection, $curAwayNameQuery)->fetch_object();
		$curPair = $curPair." ".$curAwayName->name;
		array_push($names, $curPair);
	}
	
	
	$matchesQuery = "SELECT * FROM Game";
	$matches = mysqli_query($connection, $matchesQuery);
        
        
        if(isset($_POST['search'])){
            $searchtext = $_POST['searchtext'];
            $_SESSION['searchtext'] = $searchtext;
            header("Location: Search.php");
        }

?>
<!DOCTYPE html>
<html>
<head>
<style>
* {
    box-sizing: border-box;
}

body {
    font-family: Arial;
    padding: 10px;
    background: #f1f1f1;
}

/* Header/Blog Title */
.header {
    padding: 30px;
    text-align: center;
    background: white;
}

.header h1 {
    font-size: 50px;
}

/* Style the top navigation bar */
.topnav {
    overflow: hidden;
    background-color: #333;
}

/* Style the topnav links */
.topnav a {
    float: left;
    display: block;
    color: #f2f2f2;
    text-align: center;
    padding: 14px 16px;
    text-decoration: none;
}

/* Change color on hover */
.topnav a:hover {
    background-color: #ddd;
    color: black;
}

/* Create two unequal columns that floats next to each other */
/* Left column */
.leftcolumn {   
    float: left;
    width: 25%;
}

/* Right column */
.rightcolumn {
    float: left;
    width: 75%;
    background-color: #f1f1f1;
    padding-left: 20px;
}
.logoutbutton {
    background-color: #f44336; /* Red */
    border: none;
    color: white;
    padding: 14px 31px;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    font-size: 16px;
	float: right;
}
.searchbutton {
    background-color: #4CAF50; /* Red */
    border: none;
    color: white;
    padding: 14px 31px;
    text-align: center;
    text-decoration: none;
    margin-right: 20px;
    display: inline-block;
    font-size: 16px;
	float: right;
}
.rightcolumn2 {
    float: right;
    width: 35%;
    background-color: #f1f1f1;
    padding-left: 20px;
}

/* Fake image */
.fakeimg {
    background-color: #aaa;
    width: 100%;
    padding: 20px;
}

/* Add a card effect for articles */
.card {
    background-color: white;
    padding: 20px;
    margin-top: 20px;
}

/* Clear floats after the columns */
.row:after {
    content: "";
    display: table;
    clear: both;
}

#sideBarStyle ul {
    
    margin: 0;
    padding: 0;
    width: 200px;
    background-color: #f1f1f1;
}

#sideBarStyle li a {
    
    display: block;
    color: #000;
    padding: 8px 16px;
    text-decoration: none;
}

ul, li 
{
    list-style-type: none;
    margin: 0;
    padding: 0;
}

ul#sideBarStyle li a:hover,ul#sideBarStyle li.active a
{
   background-color: #4CAF50;
   color: white;

}


/* Responsive layout - when the screen is less than 800px wide, make the two columns stack on top of each other instead of next to each other */
@media screen and (max-width: 800px) {
    .leftcolumn, .rightcolumn {   
        width: 100%;
        padding: 0;
    }
}

/* Responsive layout - when the screen is less than 400px wide, make the navigation links stack on top of each other instead of next to each other */
@media screen and (max-width: 400px) {
.topnav a {
	float: none;
	width: 100%;
}

}
</style>
</head>
<body>

 


<div class="header">
  <h1>Football Management System</h1>
</div>

<div class="topnav">
  <a href="#">Home</a>
  <a href="EditProfile.php">Settings</a>

  <form action = "#" method = "POST">
    <input type = "submit" class="logoutbutton" value = "Logout" name = "logout" />
  </form>
  <form action = "#" method = "POST">
        <input type="submit" style="float:right" name="search" value="Search" class = "searchbutton">
        <input type ="text" name = "searchtext" placeholder="Search..." style ="float:right; width: 260px; height:30px; margin-top:8px; margin-right: 1px">
  </form>
  
</div>

<div class="row">
  
  <div class="leftcolumn">
    	 <ul id="sideBarStyle">
         <li><a class="active" href="CountriesPage.php">Countries</a></li>
         <li><a href="Leagues.php">Leagues</a></li>
         <li><a href="Clubs.php">Clubs</a></li>
         <li><a href="TransferNewsPage.php">Transfer News</a></li>
         <li><a href="Matches.php">Matches</a></li>
         <li><a href="playersPage.php">Players</a></li>
         <li><a href="Subscriptions.php">Subscriptions</a></li>
    	 </ul>
  </div>


  <div class="rightcolumn">
    
    <div class="card">

      <h2><?php echo $club->name;?></h2>
      <div class="rightcolumn2">
        <p>
				Coach:   <?php echo $coach->name." ".$coach->surname;?>
			<br>Stadium: <?php echo $club->stadium;?>
			<br>City:    <?php echo $club->city;?>
			<br>Establishment Date:    <?php echo $club->establishment_date;?>
			<br>Leagues: <?php while ($row = mysqli_fetch_assoc($leagues)){ ?>
						 <?php echo $row['name'].', '; ?>
						 <?php } ?>
			
		</p>
      </div>
      <div>
		<?php echo $myImage;?>
	  </div>
      <div class="rightcolumn2">
        <p>
			Recent Matches:<br>
							<?php
							$cnt = 0;
							foreach($names as $value){ 
								echo $value;?> <br> <?php
								$cnt++;
								if ($cnt == 5)
									break;
							} 
							?>
		</p>
      </div>
      <div class="inf" style="height:200px; width: 350px">

      <p>
	  Players: 
		<?php while ($row = mysqli_fetch_assoc($players)){ ?>
			<br> <?php echo $row['name'].' '.$row['surname']; ?>
		<?php } ?>
	  </p>

    </div>
  </div>

</div>

 

</body>
</html>
