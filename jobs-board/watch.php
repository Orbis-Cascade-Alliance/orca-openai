<?php
// Get Google client
require_once(__DIR__ . '/authenticate.php');

$access_token = $client->getAccessToken();
$access_token_id = $access_token['access_token'];

// Submit watch request for push notifications of new CopyJobs emails
// See https://developers.google.com/gmail/api/guides/push

$post_fields = array(
  "topicName" => "projects/copy-jobs/topics/copyjobs_received",
  "labelIds" => array("INBOX")
);

$headers = array(
  'Content-type: application/json',
  'Authorization: Bearer ' . $access_token_id
);

$ch = curl_init();
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_URL, 'https://www.googleapis.com/gmail/v1/users/me/watch');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_fields));
$watch_response = curl_exec($ch);

// Log watch response
$fh = fopen(__DIR__ . '/watch_log.txt', 'w');
fwrite($fh, "\n" . $watch_response);
fclose($fh);
?>
