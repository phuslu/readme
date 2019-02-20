<?php
if (file_exists(dirname(__FILE__) . '/guestbook-config.php')) {
  require_once(dirname(__FILE__) . '/guestbook-config.php');
} else {
  define("MAIL_TO", "webadmin@example.org");
  define("MAIL_SMTP", "smtp.gmail.com");
  define("MAIL_ACCOUNT", "yourname@gmail.com");
  define("MAIL_PASSWORD", "123456");
}

# sudo apt install libphp-phpmailer
require 'libphp-phpmailer/PHPMailerAutoload.php';

if (isset($_REQUEST['username']) && isset($_REQUEST['email']) && isset($_REQUEST['message'])) {
  $username = $_REQUEST['username'];
  $email = $_REQUEST['email'];
  $message = $_REQUEST['message'];
  $remoteip = $_SERVER['REMOTE_ADDR'];
  if (strlen($username) > 64) {
    die("Name too long!");
  }
  if (strlen($email) > 128) {
    die("Email too long!");
  }
  if (strlen($message) > 288) {
    die("Message too long!");
  }
  if (isset($_SERVER['HTTP_X_REAL_IP'])) {
    $remoteip = $_SERVER['HTTP_X_REAL_IP'];
  }

  $subject = '[guestbook] ' . preg_replace('/^.+\n/', '', $message);
  $body = implode("\n", array(
    'UserName: '. $username,
    'Email: '. $email,
    'Remote IP: ' . $remoteip,
    "----------------------------",
    $message,
  ));

  $mail = new PHPMailer(true);
  try {
    #$mail->SMTPDebug = 2;
    $mail->isSMTP();
    $mail->Host = MAIL_SMTP;
    $mail->Username = MAIL_ACCOUNT;
    $mail->Password = MAIL_PASSWORD;
    $mail->SMTPAuth = true;
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;
    $mail->CharSet = 'UTF-8';

    $mail->addAddress(MAIL_TO);
    $mail->setFrom($email, $username);
    $mail->addReplyTo($email, $username);

    $mail->Subject = '=?utf-8?B?' . base64_encode($subject) . '?=';
    $mail->Body    = htmlspecialchars($body);

    $mail->send();
    die("Message sent success.");
  } catch (Exception $e) {
    die('Message could not be sent. Mailer Error: '. $mail->ErrorInfo);
  }
}

?><!DOCTYPE HTML>
<html>
<head>
<title>Contact US</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
</head>
<body>
<div class="contact">
  <h2>Get In Touch With Us</h2>
  <form method="post">
    <h3>Your Name</h3>
    <input type="text" name="username" class="user active">
    <h3>Your Email Address</h3>
    <input type="text" name="email" class="email">
    <h3>Your Message</h3>
    <textarea class="i" name="message"></textarea>
    <input type="submit" value="Submit Your message" />
  </form>
</div>
</body>
<style type="text/css">
* {list-style:none;margin:0;padding:0;}
body {
  background-size: cover;
  padding:50px 0px 30px 0px;
  font-family: 'Armata', sans-serif;
  font-size: 100%;
  background: rgb(30,87,153); /* Old browsers */
  background: -moz-linear-gradient(top,  rgba(30,87,153,1) 0%, rgba(41,137,216,1) 37%, rgba(32,124,202,1) 61%, rgba(125,185,232,1) 100%); /* FF3.6+ */
  background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,rgba(30,87,153,1)), color-stop(37%,rgba(41,137,216,1)), color-stop(61%,rgba(32,124,202,1)), color-stop(100%,rgba(125,185,232,1))); /* Chrome,Safari4+ */
  background: -webkit-linear-gradient(top,  rgba(30,87,153,1) 0%,rgba(41,137,216,1) 37%,rgba(32,124,202,1) 61%,rgba(125,185,232,1) 100%); /* Chrome10+,Safari5.1+ */
  background: -o-linear-gradient(top,  rgba(30,87,153,1) 0%,rgba(41,137,216,1) 37%,rgba(32,124,202,1) 61%,rgba(125,185,232,1) 100%); /* Opera 11.10+ */
  background: -ms-linear-gradient(top,  rgba(30,87,153,1) 0%,rgba(41,137,216,1) 37%,rgba(32,124,202,1) 61%,rgba(125,185,232,1) 100%); /* IE10+ */
  background: linear-gradient(to bottom,  rgba(30,87,153,1) 0%,rgba(41,137,216,1) 37%,rgba(32,124,202,1) 61%,rgba(125,185,232,1) 100%); /* W3C */
  filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#1e5799', endColorstr='#7db9e8',GradientType=0 ); /* IE6-9 */
}
/*--contact start here--*/
.contact {
  background: #fff;
  width: 35%;
  margin: 0 auto;
  text-align: center;
  padding: 30px 25px 45px 25px;
  border-radius: 5px;
  box-shadow: 3px 3px 8px 0px rgba(23, 22, 22, 0.33);
}
h1 {
  font-size: 40PX;
  font-weight: 400;
  color: #fff;
  text-align: center;
  margin: 0px 0px 30px 0px;
}
.contact h2 {
  font-size: 30px;
  color: #000;
  margin: 0px 0px 8px 0px;
}
.contact p {
  font-size: 12px;
  color: #909090;
  padding: 0px 0px 15px 0px;
  border-bottom: 1px solid #E9E9E9;
}
.contact form {
  padding: 20px 0px 0px 0px;
}
.contact h3 {
  font-size: 16px;
  font-weight: 400;
  color: #818181;
  margin: 5px 0px 10px 0px;
}
.contact input[type="text"] {
  outline: none;
  font-size: 15px;
  font-weight: 400;
  color: #bcbcbc;
  padding: 12px 10px 12px 50px;
  border-radius: 5px;
  -webkit-border-radius: 3px;
  -moz-border-radius: 5px;
  -o-border-radius: 5px;
  margin: 0px 0px 10px 0px;
  width: 55%;
  -webkit-appearance: none;
  border: 1px solid;
}
.contact input[type="text"]:hover,.contact input.active {
box-shadow: 0px 0px 4px 0px #3386E1;
 transition: 0.5s all;
  -webkit-transition: 0.5s all;
  -moz-transition: 0.5s all;
  -o-transition: 0.5s all;
}
.contact input.user {
  background: url(data:image/gif;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAWCAYAAADXYyzPAAAACXBIWXMAAAsTAAALEwEAmpwYAAAABGdBTUEAALGOfPtRkwAAACBjSFJNAAB6JQAAgIMAAPn/AACA6QAAdTAAAOpgAAA6mAAAF2+SX8VGAAAB8ElEQVR42mL8//8/Ay5w7tw5XSBlDsSMQHzGyMjoPAMVACMjozFAADFisxhoIQ+QWgTEgWhS24A4CuiAjxRazAAQQEw45JZisRQEvIB4NTV8DRBAGD4G+tYKSB0loM8F6Ou9lAQ1QABh87ETEXqdKfTwWYAAwmYxKxEaWSkNaoAAwmbxHSL03abQXmOAAMJm8VkiNJ6iNKgBAgjDYmCiuQakjuDRdAKo5gKlQQ0QQLiyUxEQ/8Yi/hsqRykwBgggrBYDfXQaSMUA8Xck4R9AHA+UO04Fi88CBBAjgSJTCpRnody9QEufUqnIZAAIILwW0wqAChCAAIJbDPQdKNijgFgf6LNSJF/PB1JsQBwLFP8HFWuFZqnFQLG/5PgYIIDAFgMNEgXy1wGxDRCDDBIHGvgWKM4PZH+AqpcBBTVQjBvIfgvE7EB8EogDgOIvSLUYIICYoD5dD7UUBJiB2A1LqmdCKlLZoWxQlbkFaAYLqakaIICYoOWuNZqEJx5N6HLGQOxDaqoGCCCQxQZYJNyhIUGMxQw4zMALAAKIBZpw0IEYEFsA8UskMUGgY4SAtAIW9WykBjVAALFAExM2sB5aaCC3PnCFwj9SgxoggFjwVPpiaHxpPAYdIjWoAQIIlp1AwapHZnlwDZidjpBagAAEGADcKIBfz3+n8wAAAABJRU5ErkJggg==)no-repeat 10px 8px;
}
input.email {
  background: url(data:image/gif;base64,iVBORw0KGgoAAAANSUhEUgAAACEAAAAWCAYAAABOm/V6AAAACXBIWXMAAAsTAAALEwEAmpwYAAAABGdBTUEAALGOfPtRkwAAACBjSFJNAAB6JQAAgIMAAPn/AACA6QAAdTAAAOpgAAA6mAAAF2+SX8VGAAABb0lEQVR42mL8//8/w0ACRkZGY4AAYhwEjmAACCAmhkEAAAJoMDjCGCCAwNFx7tw5BSBHgULDHhgZGT0gJzoAAojx7NmzDiADgHg9EBuQ6YALQFwIYgAdcoBURwAEECg6HKAOCIQaRo4DQHr7oWaRHB0AAQRLE6AQOA/EiUC8gAQDNkBDgJJQPAsQQCxIHAEg3g/EjlB+AgHNIMdOhOoRoCQxAQQQeu4QgIbIQWio4AIg32+khgNA0QEQQLiy6Hwojc0hILEP0CgQoEIWPQsQQLgc8QEpkRpC+R+QouoClE8VABBATDhSuyM0tYNCJB/KB+F4qFg9BbkJIzoAAogFTeAANL7nI6X2BKSCDJYFA6BigRTmDHB0AAQQC5GpHVv+N4CqTYSGUAK5rgAIIBak1P6BjNSuAA0JR0qiAyCAYHWHATQNUAIKofUHSQkWVGwDBNCgaE8ABNCgqMoBAmhQhARAAA2KlhVAAA2K6AAIMADivlIuhGeAEgAAAABJRU5ErkJggg==)no-repeat 8px 8px;
}
.contact textarea {
  outline: none;
  font-size: 15px;
  font-weight: 400;
  color: #bcbcbc;
  padding: 12px 10px 12px 50px;
  border-radius: 5px;
  -webkit-border-radius: 3px;
  -moz-border-radius: 5px;
  -o-border-radius: 5px;
  margin: 0px 0px 10px 0px;
  width: 55%;
  -webkit-appearance: none;
  border: 1px solid;
  resize: none;
  height: 6em;
}
.contact textarea.i {
  background: url(data:image/gif;base64,iVBORw0KGgoAAAANSUhEUgAAABkAAAAWCAYAAAA1vze2AAAACXBIWXMAAAsTAAALEwEAmpwYAAAABGdBTUEAALGOfPtRkwAAACBjSFJNAAB6JQAAgIMAAPn/AACA6QAAdTAAAOpgAAA6mAAAF2+SX8VGAAABpElEQVR42mL8//8/Azo4d+5cHpAyBOK1RkZGWxgoAIyMjMYAAcSIbgnQgjog1YgkZAi06AIFljAABBATmgViaBaAgCcDhQAggJjQ+JxY1Jyl0A5jgABCt+QxEC9B4s8CBtUuCi05CxBAjDgiHhREX4AWHKY0qEBxAhBAWC2hJgClLoAAwpa6WIBUDBArAPE+oG8OUeoTgABCsQRoQQiQ6gBiZSR1YkCLXlNiCUAAsSBZADK8HE3NdyD+RWnqAgggJqgF85AseIykYDfQFx8pTV0AAcQEtGAFkJEIxJ+B2A2IHyIp2EONyAcIIFBw7QBiDSCOAOKnQGyDJL+VCnYYAwQQesQ3AalaGBcYVMbUyCcAAcSEp5zaQ628AhBATEi+kIIW7yhBBRTnozS4AAII2Se2QMwMZb8CZUKgBc5A9k0gXUJJ6gIIIBYkjjkSewPQYBdQEobyVSnxCkAAIftEH4ntiGRBI9BX6ZQEF0AAIftEE4kNc3ku0IIplGZGgABC9slpJPZzIPahggVgABBAyD7JBOJHQAwqRuYCLbhPpRRsDBBgACLZdShiWC57AAAAAElFTkSuQmCC)no-repeat 16px 8px;
}
.contact textarea.i:hover {
box-shadow: 0px 0px 4px 0px #3386E1;
 transition: 0.5s all;
  -webkit-transition: 0.5s all;
  -moz-transition: 0.5s all;
  -o-transition: 0.5s all;
}
.contact input[type="submit"] {
  background: #1A7CEA;
  color: #FFF;
  font-size: 17px;
  font-weight: 400;
  padding: 15px 7px;
    width: 66%;
  border-radius: 6px;
  -webkit-border-radius: 5px;
  -moz-border-radius: 6px;
  -o-border-radius: 6px;
  margin: 10px 0px 0px 0px;
  border-bottom: 3px solid #0E5099;
  transition: 0.5s all;
  -webkit-transition: 0.5s all;
  -moz-transition: 0.5s all;
  -o-transition: 0.5s all;
  display: inline-block;
  cursor: pointer;
  outline: none;
  border-right: none;
  border-left: none;
  border-top: none;
}
.contact input[type="submit"]:hover {
  background:#3B73B3;
   transition: 0.5s all;
  -webkit-transition: 0.5s all;
  -moz-transition: 0.5s all;
  -o-transition: 0.5s all;
}
@media(max-width:1440px){
.contact {
  width: 40%;
}
}
@media(max-width:1366px){
.contact {
  width: 43%;
}
}
@media(max-width:1280px){
.contact {
  width: 45%;
}
}
@media(max-width:1024px){
.contact {
  width: 56%;
}
.copyright {
  padding: 100px 0px 0px 0px;
}
}
@media(max-width:768px){
.contact {
  width: 73%;
}
.contact h2 {
  font-size: 26px;
}
.contact h3 {
  font-size: 15px;
}
.contact input[type="text"] {
  font-size: 13px;
}
.contact input[type="submit"] {
  font-size: 15px;
}
.contact textarea {
  font-size: 13px;
}
body {
  padding: 50px 0px 80px 0px;
}
}
@media(max-width:640px){
.contact h2 {
  font-size: 24px;
}
.contact input[type="text"] {
  font-size: 12px;
}
.contact textarea {
  font-size: 12px;
}
.contact input[type="submit"] {
  width: 69%;
  font-size: 14px;
}
body {
  padding: 50px 0px 30px 0px;
}
h1 {
  font-size: 35PX;
}
}
@media(max-width:480px){
.contact h2 {
  font-size: 22px;
}
.contact p {
  font-size: 9.5px;
}
.contact form {
  padding: 10px 0px 0px 0px;
}
.contact h3 {
  font-size: 13px;
}
.contact input[type="text"] {
  width: 61%;
}
.contact textarea {
  width: 61%;
}
.contact input[type="submit"] {
  width: 80%;
}
h1 {
  font-size: 30PX;
  margin: 0px 0px 20px 0px;
}
}
</style>
</html>

