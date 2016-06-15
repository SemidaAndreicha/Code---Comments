<?php
	session_start();
	echo "<link rel='stylesheet' href='css/main.css'/>";
?>
<body>
	<div class='separator x8'></div>
	<h2>Are you sure that you want to unenroll <?php echo $_POST['nametoDelete']; echo " "; echo $_POST['surnametoDelete']?> from <?php echo $_SESSION['class'];?> class?</h2>
	<h3 id = 'warning'>Once you do this, all information about the student`s attendance to this class will be lost.</h3>
	<div class='announcementForm form'>
	<div class='commandLine'>
	<form action ='onLogin.php'>
		<input type='submit' id='rollbackButton' value='Go back' class='commandButton'/>
	</form>
	</div>
	</div>

	<div class='separator x8'></div>
	<h2>Please confirm the name of the student you wish to delete one more time</h2>
	<div class='announcementForm form'>
	<form action ='onLogin.php' method = 'post'>
		<div class='textInput'>
			<div class='label'>First Name</div><div class='textInput'><input type = 'text' name='nametoDelete'  size = '20' maxlength = '20'></div>
			<div class='label'>Last Name</div><div class='textInput'><input type = 'text' name='surnametoDelete' size = '20' maxlength = '20'></div>
		</div>
			<div class='commandLine'>
				<input type='submit' id='unenrollButton' value='Confirm' class='commandButton'/>
			</div>
	</form>
	</div>
</body>
