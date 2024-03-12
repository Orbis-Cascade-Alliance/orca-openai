<?php
// Get Google client
require __DIR__ . '/google/vendor/autoload.php';
$client = new Google_Client();
$client->setApplicationName('CopyJobs');
$client->setAuthConfig(__DIR__ . '/oauth2/credentials.json');
$client->setAccessType('offline');
$client->addScope(Google_Service_Gmail::GMAIL_MODIFY);

$tokenPath = __DIR__ . '/oauth2/token.json';
if (file_exists($tokenPath)) {
  $accessToken = json_decode(file_get_contents($tokenPath), true);
  
  $client->setAccessToken($accessToken);
  if ($client->isAccessTokenExpired()) {
    if ($refreshToken = $client->getRefreshToken()) {
        $client->fetchAccessTokenWithRefreshToken($refreshToken);
        $newAccessToken = $client->getAccessToken();
        $accessToken = array_merge($accessToken, $newAccessToken);
        file_put_contents($tokenPath, json_encode($accessToken));
    }
    else {
      die('Could not refresh token.');
    }
  }
}
?>
