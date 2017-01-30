<?php

// Add here your Host (lists.domain.tdl)
$host    = '';

//Add here your Lists
$lists = [
	"listone" => "Liste 1",
	"listtwo" => "Liste 2",
];

if (!empty($_POST)) {
    
    $path    = "/mailman/admin/" . $_POST['listname'] . "/";
    $adminpw = $_POST['password'];

    //Ein- und Austragenachricht an Mitglied/Listen Besitzer, ja = 1, Nein = 0     
    $member = "1";
    $owner  = "1";
    
    // Fehler: 
    error_reporting(E_ALL);
    $all = '';
    if (!isset($_POST['email']))
        echo ('<div class="alert alert-success">Fehler! Keine Email Adresse angegeben</div>');
    if (!isset($_POST['listname']))
        echo ('<div class="alert alert-success">Fehler! Keine Liste ausgewählt</div>');
    if (!isset($_POST['password']))
        echo ('<div class="alert alert-success">Fehler! Kein Passwort angegeben</div>');
    if (!isset($_POST['action']))
        echo ('<div class="alert alert-success">Fehler! Keine Aktion</div>');
    if (isset($_POST['email']))
        echo ('<div class="alert alert-success">Bitte schreib deine Emailadresse in das Formular!</div>');
    
    
    $fp = fsockopen($host, 80, $errno, $error, 15) OR die($error . "(" . $errno . ")");
    if ($_POST['action'] == "add") {
        $string = "adminpw=" . $adminpw . "&send_welcome_msg_to_this_batch=" . $member . "&send_notifications_to_list_owner=" . $owner . "&subscribees=" . $_POST['email'];
    } elseif ($_POST['action'] == "remove") {
        $string = "send_unsub_notifications_to_list_owner=" . $owner . "&send_unsub_ack_to_this_batch=" . $member . "&adminpw=" . $adminpw . "&unsubscribees=" . $_POST['email'];
    } else
        echo ("Fehler! Ung&uuml;ltige Aktion");
    
    fwrite($fp, "POST " . $path . " HTTP/1.1 \r\n");
    fwrite($fp, "Host: " . $host . "\r\n");
    fwrite($fp, "Content-type: application/x-www-form-urlencoded\r\n");
    fwrite($fp, "Content-length: " . strlen($string) . "\r\n");
    fwrite($fp, "Connection: close\r\n\r\n"); // Request beenden 
    fwrite($fp, $string . "\r\n");
    while (!feof($fp)) {
        $all = $all . fread($fp, 2025);
    }
    fclose($fp); // Verbindung beenden 
	
	unset($_POST);
}
?> 
<!DOCTYPE html>
<html lang="de">
<head>
    <title>Mailinglisten</title>
    <meta charset="utf-8">  
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u"
        crossorigin="anonymous">
    <style>
        body {
            padding-top: 40px;
            padding-bottom: 40px;
            background-color: #eee;
        }
        
        .form-signin {
            max-width: 330px;
            padding: 15px;
            margin: 0 auto;
        }
    </style>
</head>

<body>
    <div class="container">
		<h2 class="margin-bottom text-center">EC Zeltlager Mailinglisten</h2>
		<?php if (!empty($_POST)) { ?>
			<div class="alert alert-success"> 
				<?php 
					if (preg_match("/<h5>Fehler beim Abonnieren:<\/h5>/", $all))
						echo "Fehler beim Abonnieren. Die Emailadresse " . $_POST['email'] . " ist bereits eingetragen.";
					elseif (preg_match("/<h5>Erfolgreich eingetragen: <\/h5>/", $all))
						echo "Die Emailadresse " . $_POST['email'] . " wurde erfolgreich eingetragen. Du erh&auml;ltst in wenigen Sekunden eine Best&auml;tigungsemail.";
					elseif (preg_match("/<h5>Erfolgreich beendete Abonnements:<\/h5>/", $all))
						echo "Die Emailadresse " . $_POST['email'] . " wurde erfolgreich ausgetragen. Du erh&auml;ltst in wenigen Sekunden eine Best&auml;tigungsemail";
					elseif (preg_match("/Nichtmitglieder k.nnen nicht aus der Mailingliste ausgetragen werden:/", $all))
						echo "Fehler beim Austragen. Die Emailadresse " . $_POST['email'] . " ist nicht angemeldet.";
					else {
						echo "Allgemeiner Fehler.";
					}
				?>
			</div> 
		<?php } ?>
		
        <form action="" method="post" class="form-signin">
            <div class="form-group">
                <select name="listname" class="form-control">
				<option value="">-----------------</option>
                <?php
                    foreach($lists as $key => $value){
                        echo '<option value="'.$key.'">'.$value.'</option>';
                    };
                ?>
			  </select>
            </div>
            <div class="form-group">
                <input name="password" type="text" size="30" maxlength="50" placeholder="Passwort" onclick="this.value=''" class="form-control">
            </div>
            <hr>
            <div class="form-group">
                <select name="action" class="form-control">
				<option value="add">Eintragen</option>
				<option value="remove">Austragen</option>
			  </select>
            </div>
            <div class="form-group">
                <input name="email" type="text" size="30" maxlength="50" placeholder="E-Mail Adresse" onclick="this.value=''" class="form-control">
            </div>
            <button class="btn btn-lg btn-primary btn-block" type="submit">Senden</button>
        </form>
    </div>
    <!-- /container -->
	
</body>

</html>
