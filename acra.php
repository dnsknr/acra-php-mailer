<?php
/**
 * ACRA PHP Mailer
 *
 * This PHP script receives ACRA crash reports in JSON format and forwards
 * them in a HTML formatted mail to a defined recipient address.
 *
 * @author    Denis Knauer <denis@fassor.com>
 * @link      https://github.com/fassor/acra-php-mailer
 * @copyright 2015 Denis Knauer (http://www.fassor.com)
 * @license   https://github.com/fassor/acra-php-mailer/blob/master/LICENSE The MIT License (MIT)
 */
 
// Configuration
$to = 'your@mail.com';
$from = 'your@mail.com';


// Verify that we received a POST request...
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request', true, 400);
  die('400 Bad Request: Request type not supported!');
}

// Try to parse the JSON...
$json = json_decode(file_get_contents('php://input'), true);

if ($json == null) {
  header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request', true, 400);
  die('400 Bad Request: Content is not valid JSON!');
}

// Prepare the multipart email...
$separator = md5(time());

ob_start();
?>
--<?= $separator; ?><?= "\r\n" ?>
Content-Type: text/html; charset="UTF-8"
Content-Transfer-Encoding: 7bit

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <style media="all" type="text/css">
      body {
        font-family: Helvetica, Arial, sans-serif;
        font-size: 12px;
      }
      td { padding: 5px 0; vertical-align: top; border-bottom: 1px solid #ccc; }
      pre { padding: 0; margin: 0; font-size: 13px; }
    </style>
  </head>
  <body>
    <h1>Android Crash Report</h1>
		<p>PackageName: <strong><?= $json['PACKAGE_NAME'] ?></strong></p>
    <p>App Version: <strong><?= $json['APP_VERSION_NAME'] ?></strong></p>
    <p>Android Version: <strong><?= $json['ANDROID_VERSION'] ?></strong></p>
    <table>
      <? foreach($json as $key => $value) { ?>
        <tr>
          <td><strong><?= $key ?></strong></td>
          <td>
            <pre><? print_r($value) ?></pre>
          </td>
        </tr>
      <? } ?>
    </table>
  </body>
</html>

--<?= $separator; ?><?= "\r\n" ?>
Content-Type: text/plain; name="<?= $json['APP_VERSION_NAME'] ?>_<?= $json['REPORT_ID'] ?>.txt"
Content-Transfer-Encoding: base64
Content-Disposition: attachment

<?= chunk_split(base64_encode($json['STACK_TRACE'])); ?>
--<?= $separator; ?>--
<?php
$message = ob_get_contents();
ob_end_clean();

$headers = "From: {$from}\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: multipart/mixed; boundary={$separator}\r\n";

$subject = "Crash Report: {$json['PACKAGE_NAME']} {$json['APP_VERSION_NAME']} on Android {$json['ANDROID_VERSION']} | {$json['REPORT_ID']}";

// Now send the mail!
mail($to, $subject, $message, $headers);

// Done!
header("Content-Type: text/plain");
echo "Thanks!";
