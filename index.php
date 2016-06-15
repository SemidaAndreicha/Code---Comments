<?php
	session_start();
?>

<html>
    <head>
        <link rel="stylesheet" href="css/main.css"/>
        <script language='JavaScript' src='lib/const.js'></script>
        <script language='JavaScript' src='lib/md5.js'></script>
        <script language='JavaScript' src='lib/ucconnect.min.js'></script>
        <script language='JavaScript' src='lib/api.js'></script>
        <script language='JavaScript' src='js/util.js'></script>
        <script language='JavaScript' src='js/main.js'></script>
        <script language='JavaScript' src='js/requirejs.js'></script>
	</head>

<?php
	$con = mysqli_connect("localhost", "root", "password", "students");
	
	if(isset($_POST["username"]) && isset($_POST["password"])){ //makes a session
		if (isset($_SESSION['username'])){
			session_destroy(); 
			session_start();
		}
		$_SESSION['username'] = $_POST["username"];
		$password = $_POST["password"];
		$db = "teacher";
		if($_SESSION['username'] <= 90){
			$db = "student";
		}
		$sql = "SELECT password FROM $db where id = ".$_SESSION['username'].";";
		$result = mysqli_fetch_assoc(mysqli_query($con,$sql));
		$pas = htmlentities($result['password']);
		if($pas != $password){ //takes the pw inserted by student & the one from db and compares
			die("Password is incorrect");
		}
	}
	
	if($_SESSION['username'] <= 90){
		$sql = "SELECT * FROM enrollment where studentId = ".$_SESSION['username'].";";
	}
	else{
		$sql = "SELECT subjectName FROM subject where teacherId = ".$_SESSION['username'].";";
	}
	$result = mysqli_query($con,$sql);
	$help = mysqli_fetch_assoc($result);
	$array = array();
	while ($help != NULL) { //while the connection is not null?
		array_push($array, htmlentities($help['subjectName'])); //?
		$help = mysqli_fetch_assoc($result); //?
	}
	$_SESSION['allClasses'] = serialize($array);
	
	$sql = "SELECT VWpassword FROM $db where id = ".$_SESSION['username'].";"; //sql statement asks for pw
	$result = mysqli_fetch_assoc(mysqli_query($con,$sql));
	$pas = htmlentities($result['VWpassword']);
		
	echo "<body onload='onLoad(".$_SESSION['username'].", $pas)'>"; //concatinates the id, connection, password
?>
	<div id='screen' class='mainScreen'>
	
<?php
	if (isset($_POST['selectclassButton']) || isset($_SESSION['class'])){ //? what does the if do?
		echo "<div class='separator x8'></div><h2>";
		if (isset($_POST['selectclassButton'])){
			$_SESSION['class'] = $_POST["classes"];
		}
	}
	//Annoucment section display
	else if($_SESSION['username'] <= 90){
		echo "<div class='announcements'>";
		echo "<h2> Announcements </h2>";
			$sql = "SELECT a.subjectName, a.text, a.date FROM announcement a INNER JOIN enrollment e on a.subjectName = e.subjectName where e.studentId = ".$_SESSION['username']." order by a.date desc;";
			$result = mysqli_query($con,$sql);
			$help = mysqli_fetch_assoc($result);
			while ($help != NULL) {
				echo "<h4>";
				echo htmlentities($help['subjectName']);
				echo "</h4>";
				echo htmlentities($help['text']);
				echo "<p id = 'separatorAnnouncements'>";
				echo htmlentities($help['date'])."</p>";
				$help = mysqli_fetch_assoc($result);
			}
		echo "</div>";
		
	//Schedule section display
		echo "<div class='schedule'>";
		echo "<h2> Schedule </h2>";
			$sql = "SELECT a.subjectName, a.lesTime FROM attendance a INNER JOIN enrollment e on a.subjectName = e.subjectName where a.lesDate = curdate() and e.studentId = ".$_SESSION['username']." order by a.lesTime desc;";
			$result = mysqli_query($con,$sql);
			$help = mysqli_fetch_assoc($result);
			while ($help != NULL) {
				echo "<h4>";
				echo htmlentities($help['subjectName']);
				echo "</h4>";
				echo "<p id = 'separatorAnnouncements'>";
				echo htmlentities($help['lesTime'])."</p>";
				$help = mysqli_fetch_assoc($result);
			}
		echo "</div>";
	}
	
	
	//changing passwords
	if((isset($_POST['oldPas']) && isset($_POST['newPas']) && isset($_POST['repNewPas'])) && $_POST['repNewPas'] == $_POST['newPas']){
		$oldPas = $_POST['oldPas'];
		$newPas = $_POST['newPas'];
		$id = $_SESSION['username'];
		$sql = "SELECT password FROM student WHERE id='$id';";
		$result = mysqli_fetch_assoc(mysqli_query($con,$sql));
		$pas = htmlentities($result['password']);
		if ($pas == $oldPas){
			$sql = "UPDATE student SET password = '$newPas' where id = '$id';";
			$result = mysqli_query($con,$sql);
		}
	}
	
?>

	<!-- Logout Section -->
	<div class='separator x8'></div>
	<h2>Logout</h2>
		<div class='labelLine'></div>
	<div class='logoutForm form'>
		<form action = 'index.html'>
			<div class='commandLine'>
				<input type='submit' id='logoutButton' value='Logout' class='commandButton'/>
			</div>
		</form>
	</div>
   

	<!-- Status Section -->
	<div class='separator x8'></div>
		<h2>Status</h2>
		<div class='separator'></div>
			<div class='statusForm form'>
				<div class='labelLine'>
					<div class='label'>Set status</div><div class='textInput'>
					<select id='status' >
						<option value='online' selected>Online</option>
						<option value='xa'>Bussy</option>
						<option value='away'>Away</option>
					</select>
				</div>
				</div>
				<div class='commandLine'>
					<input type='button' id='statusButton' value='Set status' class='commandButton'/>
				</div>
			</div>
			
		<!-- Choose Class Section-->
	<div class='separator x8'></div>
		<h2>Choose <?php if(isset($_SESSION['class'])){echo"Another";} ?> Class</h2>
		<div class='classForm form'>
			<form action = '' method = 'post'>
			<div class='textInput'>
				<select name='classes'>
					<?php
						$array = unserialize($_SESSION['allClasses']);
						
						for ($x = 0; $x < count($array); $x++){
							echo("<option value=".$array[$x].">");
							echo $array[$x];
							echo("</option>");
						}
					?>
				</select>
			</div>
				<div class='commandLine'>
					<input type='submit' name='selectclassButton' value='Select Class' class='commandButton'/>
				</div>
			</form>
			</div>
			
		
		<!-- Change Password Section-->
		<div class='separator x8'></div>
			<h2>Change Password</h2>
			<div class='announcementForm form'>
				<form action = <?php $_SERVER['PHP_SELF']?> method = 'post'>
				<div class='textInput'>
					<div class='label'>Old Password</div><div class='textInput'><input type = 'text' name='oldPas'  size = '20' maxlength = '10'></div>
					<div class='label'>New Password</div><div class='textInput'><input type = 'text' name='newPas' size = '20' maxlength = '10'></div>
					<div class='label'>Confirm New Password</div><div class='textInput'><input type = 'text' name='repNewPas' size = '20' maxlength = '10'></div>
				</div>
					<div class='commandLine'>
						<input type='submit' id='passwordButton' value='Change' class='commandButton'/>
					</div>
				</form>
		</div>
		
		<?php
			
			if (isset($_SESSION['class'])){
				echo "<iframe src='onLogin.php' id='frame'></iframe>";
			}
		 ?>
		</div>
    </body> 
</html>

			
