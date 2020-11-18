<?php

session_start();
//configuration 
$path = 'uploads';
$filename = (isset($_POST['file']) && $_POST['file'] != '') ? $_POST['file'] : NULL;

$message = $_POST['message'];
$from = $_POST['email'];
$subject = $_POST['subject'];
$mailto = 'your@email.com';

// check if any attachment

if ($filename) {
  $file = $path . "/" . $filename;
  $file_size = filesize($file);
  $handle = fopen($file, "r");
  $content = fread($handle, $file_size);
  fclose($handle);
  $content = chunk_split(base64_encode($content));
}

// a random hash will be necessary to send mixed content
$separator = md5(time());

// carriage return type (we use a PHP end of line constant)
$eol = PHP_EOL;

// main header (multipart mandatory)
$headers = "From: ". $_POST['fullname']." <" . $from . ">" . $eol;
$headers .= 'Reply-To: <'.$from.'>' . $eol;
$headers .= "MIME-Version: 1.0" . $eol;
$headers .= "Content-Type: multipart/mixed; boundary=\"" . $separator . "\"" . $eol . $eol;
$headers .= "Content-Transfer-Encoding: 7bit" . $eol;
$headers .= "This is a MIME encoded message." . $eol . $eol;

// message
$headers .= "--" . $separator . $eol;
$headers .= "Content-Type: text/plain; charset=\"iso-8859-1\"" . $eol;
$headers .= "Content-Transfer-Encoding: 8bit" . $eol . $eol;
$headers .= $message . $eol . $eol;

// attachment
if ($filename) {
  $headers .= "--" . $separator . $eol;
  $headers .= "Content-Type: application/octet-stream; name=\"" . $filename . "\"" . $eol;
  $headers .= "Content-Transfer-Encoding: base64" . $eol;
  $headers .= "Content-Disposition: attachment" . $eol . $eol;
  $headers .= $content . $eol . $eol;
  $headers .= "--" . $separator . "--";
}


$ress = array('error' => NULL, 'msg' => NULL);

// check captcha first;
if (isset($_SESSION['simpleCaptchaAnswer']) && $_POST['captchaSelection'] == $_SESSION['simpleCaptchaAnswer']) {
  //SEND Mail
  if (mail($mailto, $subject, "", $headers)) {
    $ress['msg'] = "Thanks, I will get back to you ASAP";
  } else {
    $ress['error'] = "error : email not sent";
  }
} else {
  $ress['error'] = "Please check your answer!";
}
//respond to json
echo json_encode($ress);
