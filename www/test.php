<?php
/*require_once(dirname(dirname(__FILE__)).'/vendor/swiftmailer/swiftmailer/lib/swift_required.php');*/
/*require_once('test_creds.php');

$transport = Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, "ssl")
  ->setUsername($unami)
  ->setPassword($pwadi);

$mailer = Swift_Mailer::newInstance($transport);

$message = Swift_Message::newInstance('Test Subject (3) - should work')
  ->setFrom(array('abc@example.com' => 'ABC'))
  ->setTo(array('me@aaronmartins.com'))
  //->setBcc(array('aarontylermartins@gmail.com'))
  ->setBody('<b>This</b> is a test mail. Sould go to me@aaronmartins.com and should', 'text/html');

$result = $mailer->send($message);*/
print_r($result);
echo 'fin';
