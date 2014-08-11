<?php
if ( !isset( $_SESSION ) ) session_start();
if ( !$_POST ) exit;
if ( !defined( "PHP_EOL" ) ) define( "PHP_EOL", "\r\n" );


$to = "livnestok.21@mail.ru";
$subject = "Заказ с сайта На Складском";



foreach ($_POST as $key => $value) {
    if (ini_get('magic_quotes_gpc'))
        $_POST[$key] = stripslashes($_POST[$key]);
    $_POST[$key] = htmlspecialchars(strip_tags($_POST[$key]));
}

// Assign the input values to variables for easy reference
$name      = @$_POST["name"];
$email     = @$_POST["email"];
$phone     = @$_POST["phone"];
$message   = @$_POST["comment"];
$verify    = @$_POST["verify"];


// Test input values for errors
$errors = array();
 //php verif name
if(isset($_POST["name"])){
 
        if (!$name) {
            $errors[] = "Вы должны ввести имя.";
        } elseif(strlen($name) < 2)  {
            $errors[] = "Имя должно быть не меньше двух символов.";
        }
 
}
    //php verif email
if(isset($_POST["email"])){
    if (!$email) {
        $errors[] = "Вы должны ввести email.";
    } else if (!validEmail($email)) {
        $errors[] = "Вы должны ввести корректный email.";
    }
}
    //php verif phone
if(isset($_POST["phone"])){
    if (!$phone) {
        $errors[] = "Вы должны ввести корректный номер телефона.";
    }elseif ( !is_numeric( $phone ) ) {
        $errors[]= 'Номер телефона должен содержать только цифры.';
    }
}



//php verif comment
if(isset($_POST["comment"])){
    if (strlen($message) < 10) {
        if (!$message) {
            $errors[] = "Вы должны ввести сообщение.";
        } else {
            $errors[] = "Сообщение должно быть не менее 10 символов.";
        }
    }
}

    //php verif captcha
if(isset($_POST["verify"])){
    if (!$verify) {
        $errors[] = "Вы должны ввести защитный код.";
    } else if (md5($verify) != $_SESSION['nekoCheck']['verify']) {
        $errors[] = "Защитный код введен неверно.";
    }
}

if ($errors) {
        // Output errors and die with a failure message
    $errortext = "";
    foreach ($errors as $error) {
        $errortext .= '<li>'. $error . "</li>";
    }

    echo '<div class="alert alert-error">Произошли следующие ошибки:<br><ul>'. $errortext .'</ul></div>';

}else{



    // Send the email
    $headers  = "From: $email" . PHP_EOL;
    $headers .= "Reply-To: $email" . PHP_EOL;
    $headers .= "MIME-Version: 1.0" . PHP_EOL;
    $headers .= "Content-type: text/plain; charset=utf-8" . PHP_EOL;
    $headers .= "Content-Transfer-Encoding: quoted-printable" . PHP_EOL;

    $mailBody  = "Вам написал $name" . PHP_EOL . PHP_EOL;
    $mailBody .= (!empty($company))?'Company: '. PHP_EOL.$company. PHP_EOL . PHP_EOL:'';
    $mailBody .= (!empty($quoteType))?'project Type: '. PHP_EOL.$quoteType. PHP_EOL . PHP_EOL:''; 
    $mailBody .= "Сообщение :" . PHP_EOL;
    $mailBody .= $message . PHP_EOL . PHP_EOL;
    $mailBody .= "Вы можете связаться с $name по email, $email.";
    $mailBody .= (isset($phone) && !empty($phone))?" или по телефону $phone." . PHP_EOL . PHP_EOL:'';
    $mailBody .= "-------------------------------------------------------------------------------------------" . PHP_EOL;






    if(mail($to, $subject, $mailBody, $headers)){
        echo '<div class="alert alert-success">Успшено! Ваше сообщение было отправлено.</div>';
    }
}

// FUNCTIONS 
function validEmail($email) {
    $isValid = true;
    $atIndex = strrpos($email, "@");
    if (is_bool($atIndex) && !$atIndex) {
        $isValid = false;
    } else {
        $domain = substr($email, $atIndex + 1);
        $local = substr($email, 0, $atIndex);
        $localLen = strlen($local);
        $domainLen = strlen($domain);
        if ($localLen < 1 || $localLen > 64) {
            // local part length exceeded
            $isValid = false;
        } else if ($domainLen < 1 || $domainLen > 255) {
            // domain part length exceeded
            $isValid = false;
        } else if ($local[0] == '.' || $local[$localLen - 1] == '.') {
            // local part starts or ends with '.'
            $isValid = false;
        } else if (preg_match('/\\.\\./', $local)) {
            // local part has two consecutive dots
            $isValid = false;
        } else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
            // character not valid in domain part
            $isValid = false;
        } else if (preg_match('/\\.\\./', $domain)) {
            // domain part has two consecutive dots
            $isValid = false;
        } else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\", "", $local))) {
            // character not valid in local part unless
            // local part is quoted
            if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\", "", $local))) {
                $isValid = false;
            }
        }
        if ($isValid && !(checkdnsrr($domain, "MX") || checkdnsrr($domain, "A"))) {
            // domain not found in DNS
            $isValid = false;
        }
    }
    return $isValid;
}

?>
