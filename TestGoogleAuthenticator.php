<html>
	<head>
		<title>Testing Google Authenticator</title>
	</head>
	<body>

		<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
  		username: <input type="text" name="user"><br>
  		password: <input type="text" name="password"><br>
  		<input type="submit" name="create" value="Create">
		</form>

		<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
  		username: <input type="text" name="user"><br>
  		password: <input type="text" name="password"><br>
  		google authenticator: <input type="text" name="googleauth"><br>
  		<input type="submit" name="login" value="Login">
		</form>


		<?php
		require_once 'GoogleAuthenticator.php';
		$db = new SQLite3('google_authenticator.db') or die ('cannot open the database');
		// Database: USERS col(USER, PASSWORD, 2fa)

		$ga = new PHPGangsta_GoogleAuthenticator();

		// Checks username, password, and google authenticator
		if ($_SERVER["REQUEST_METHOD"] == "POST") {

			if (isset($_POST["create"])) {
				
				// Generate google auth secret
				$secret = $ga->createSecret();
				echo "Secret is: ".$secret."<br>";

				// Generate QR Code from secret
				$qrCodeUrl = $ga->getQRCodeGoogleUrl('test', $secret);
				echo "Add authenticator: <a href=\"".$qrCodeUrl."\">QR CODE</a><br><br>";

				echo $_POST["user"];
				echo $_POST["password"];

				// Add user, password, and google auth secret to database
				$query = "INSERT INTO USERS (USER, PASSWORD, GOOGLEAUTH) VALUES ('".$_POST["user"]."','".$_POST["password"]."','".$secret."');";
				$db->query($query);
				$query = "SELECT * FROM USERS WHERE USER='".$POST_["user"]."';";
				$results = $db->query($query);
				$result = $db->querySingle($query);
				echo $result;
			}

			// Login submit form
			if (isset($_POST["login"])) {

				// Get google auth secret from database with username and password
				$query = "SELECT GOOGLEAUTH FROM USERS WHERE USER='".$_POST["user"]."' AND PASSWORD='".$_POST["password"]."';";
				$secret = $db->querySingle($query);

				// Use secret to get TOTP
				$trueCode = $ga->getCode($secret);
				$oneCode = $_POST["googleauth"];

				echo "user: ".$_POST["user"]."<br>";
				echo "password: ".$_POST["password"]."<br>";
				echo "secret: ".$secret."<br>";
				echo "TOTP input: ".$oneCode."<br>";
				echo "TOTP: ".$trueCode."<br>";

				// Verify code
				echo "Checking Code :";
				$checkResult = $ga->verifyCode($secret, $oneCode, 1);    // 2 = 2*30sec clock tolerance
				if ($checkResult) {
				    echo 'OK';
				} else {
				    echo 'FAILED';
				}
			}
		}
		?>
	</body>
</html>
