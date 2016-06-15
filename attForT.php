<?php
	session_start();
	
	echo "<link rel='stylesheet' href='css/main.css'/>"; //css styleshhet
	echo "<h1>Attendance to ".$_SESSION['class']." class</h1>"; //points at current class
	
	$string = "<table id = 'forTeacher'><tr><td>Date</td>"; //?

	$con = mysqli_connect("localhost", "root", "password", "students"); //connection 
	$array = array(); //make array to push the classes and connection?
	$class = $_SESSION['class']; //subject name
	$sqlToFind = "show fields from attendance;"; //attandance files
	$resultToFind = mysqli_query($con, $sqlToFind); //connection between db and files
	while($row = mysqli_fetch_row($resultToFind)){ //checking to see that the row is the one seeked
		for ($i = 70; $i <= 90; $i++){ //int i (i is the student id?) is increented and has to be less than or = 90
			if($row[0] == "isPresent".$i){ //checks attandance?
				$sql = "select * from enrollment where studentId = '$i' and subjectName = '$class';"; //checks attandance by using student # and class name
				if (mysqli_fetch_assoc(mysqli_query($con, $sql)) != NULL){ //the connection cannot be null
					array_push($array, $i); //push the array and the #i into an array?
					
					$sqlName = "SELECT firstname, lastname FROM student where id='$i';"; //selecting
					$resultName = mysqli_query($con,$sqlName); //querry for selecting names
					$helpName = mysqli_fetch_assoc($resultName); //fetches the query with selecting names
					$string = $string."<td>"; //creates table
					$string =$string.htmlentities($helpName['firstname'])." "; //prints firstname
					$string =$string.htmlentities($helpName['lastname'])."</td>"; //prints lastname and closes table
				}
			}
		}
	}
	
	$string = $string."</tr>"; //clases table
	$sql = "SELECT * FROM attendance where subjectName = '$class' order by lesDate;"; //query displayes attandance
	$result = mysqli_query($con,$sql); //connection between db and files
	$help = mysqli_fetch_assoc($result); //fetches the connection between db and files
	
	while ($help != NULL) { //while the fetched connection between db and files is not null
			$string =$string."<tr>"; //make a table
			$string =$string."<td>".htmlentities($help['lesDate'])."</td>"; //make a row with the dates and close it
			for ($k = 0; $k < count($array); $k++){ // have variable k incrementing whicle is < or = to the array
				if(htmlentities($help['isPresent'.$array[$k]]) == "Present"){ //if conneciton is establshed then is present
					$string =$string."<td id = 'good'>".htmlentities($help['isPresent'.$array[$k]])."</td>"; //states thea a student is present?
				} else{
					$string =$string."<td id = 'bad'>".htmlentities($help['isPresent'.$array[$k]])."</td>";//states thea a student is not present?
				}
			}
			$string =$string."</tr>"; //clase table
			$help = mysqli_fetch_assoc($result); //enters result to the help
		}
	$string = $string."</table></br></br><form action = 'onLogin.php' method = 'post'>
	<input type = 'submit' name = 'backButton' value = 'Back!'>
	</form>"; //? 
	
	echo $string;
	
?>