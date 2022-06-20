<?php
try {
session_start();
$db_hostname = "0.0.0.0";
$db_name = "db";
$db_dns = "mysql:host={$db_hostname};dbname={$db_name}";
$db_username = "root";
$db_password = "root";
$users = "users";
$messages = "messages";
$pdo = new PDO($db_dns, $db_username, $db_password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$response = "";
if (isset($_POST["method"])) {
	if ($_POST["method"] != NULL) {
		$method = htmlspecialchars($_POST["method"], ENT_QUOTES, "UTF-8");
		if ($method == "signin") {
			if (isset($_POST["username"])) {
				if ($_POST["username"] != NULL) {
					if (isset($_POST["password"])) {
						if ($_POST["password"] != NULL) {
							$username = htmlspecialchars($_POST["username"], ENT_QUOTES, "UTF-8");
							$password = htmlspecialchars($_POST["password"], ENT_QUOTES, "UTF-8");
							$stmt = $pdo->prepare("SELECT * FROM $users WHERE username=:username;");
							$stmt->bindValue(":username",$username);
							$stmt->execute();
							if ($stmt->rowCount() > 0) {
								$response = "There is already an account with this username";
							} else {
								$stmt = $pdo->prepare("INSERT INTO $users (username, password) VALUES (:username, :password);");
								$options = [
									"cost"=> 10
								];
								$password = password_hash($password, PASSWORD_BCRYPT, $options);
								$stmt->bindValue(":username",$username);
								$stmt->bindValue(":password",$password);
								$stmt->execute();
								$response = "signin";
							}
						} else {
							$response = "Null password";
						}
					} else {
							$response = "Non-existent password";
					}
				} else {
					$response = "Null username";
				}
			} else {
					$response = "Non-existent username";
			}
		} else if ($method == "login") {
			if (isset($_POST["username"])) {
				if ($_POST["username"] != NULL) {
					if (isset($_POST["password"])) {
						if ($_POST["password"] != NULL) {
							$username = htmlspecialchars($_POST["username"], ENT_QUOTES, "UTF-8");
							$password = htmlspecialchars($_POST["password"], ENT_QUOTES, "UTF-8");
							$stmt = $pdo->prepare("SELECT * FROM $users WHERE username=:username;");
							$stmt->bindValue(":username",$username);
							$stmt->execute();
							if ($stmt->rowCount() > 0) {
								$row = $stmt->fetch(PDO::FETCH_OBJ);
								$hash = $row->password;
								if (password_verify($password, $hash)) {
									$_SESSION["username"] = $username;
									$_SESSION["password"] = $password;
									$response = "login";
								} else {
									$response = "Invalid password";
								}
							} else {
								$response = "Invalid username";
							}
						} else {
							$response = "Null password";
						}
					} else {
						$response = "Non-existent password";
					}
				} else {
					$response = "Null username";
				}
			} else {
				$response = "Non-existent username";
			}
		} else if ($method == "logout") {
			if ($_SESSION ?? NULL) {
				unset($_SESSION["username"]);
				unset($_SESSION["password"]);
				$response = "logout";
			} else {
				$response = "Null session";
			}
		} else if ($method == "send") {
			if (isset($_POST["message"])) {
				if ($_POST["message"] != NULL) {
					$message = htmlspecialchars($_POST["message"], ENT_QUOTES, "UTF-8");
					if ($_SESSION ?? NULL) {
						if (isset($_SESSION["username"])) {
							if ($_SESSION["username"] != NULL) {
								if (isset($_SESSION["password"])) {
									if ($_SESSION["password"] != NULL) {
										$username = htmlspecialchars($_SESSION["username"], ENT_QUOTES, "UTF-8");
										$password = htmlspecialchars($_SESSION["password"], ENT_QUOTES, "UTF-8");
										$stmt = $pdo->prepare("SELECT * FROM $users WHERE username=:username;");
										$stmt->bindValue(":username",$username);
										$stmt->execute();
										if ($stmt->rowCount() > 0) {
											$row = $stmt->fetch(PDO::FETCH_OBJ);
											$username = $row->username;
											$hash = $row->password;
											if (password_verify($password, $hash)) {
												$stmt = $pdo->prepare("INSERT INTO $messages (username, message) VALUES (:username, :message);");
												$stmt->bindValue(":username",$username);
												$stmt->bindValue(":message",$message);
												$stmt->execute();
												$response = "sended";
											} else {
												$response = "Invalid password";
											}
										} else {
											$response = "Invalid username";
										}
									} else {
										$response = "Null password";
									}
								} else {
									$response = "Non-existent password";
								}
							} else {
								$response = "Null username";
							}
						} else {
							$response = "Non-existent username";
						}
					} else {
						$stmt = $pdo->prepare("INSERT INTO $messages (username, message) VALUES (:username, :message);");
						$stmt->bindValue(":username",'');
						$stmt->bindValue(":message",$message);
						$stmt->execute();
						$response = "sended";
					}
				} else {
					$response = "Null message";
				}
			} else {
				$response = "Non-existent message";
			}
		} else if ($method == "receive") {
			$stmt = $pdo->prepare("SELECT * FROM $messages ORDER BY id DESC LIMIT 10;");
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				while($row = $stmt->fetch(PDO::FETCH_OBJ)) {
					if ($row->username != "") {
						$response = $row->username . ": " . $row->message . "\n" . $response;
					} else {
						$response = "Anônimo: " . $row->message . "\n" . $response;
					}
				}
			}
		} else {
			$response = "Invalid method";
		}
	} else {
		$response = "Null method";
	}
} else {
	$response = "Non-existent method";
}
$pdo = null;
echo $response;
} catch (PDOException $e) {
echo "ERROR: " . $e->getMessage();
}
?>