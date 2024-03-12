<?php
// Load WordPress core to access Gravity Forms API
require_once("/home/wp/public_html/wp-load.php");

// Require constant definitions above the public folder
require_once('/home/openai/definitions.php');

// Authenticate to Gmail API
require_once(__DIR__ . '/authenticate.php');

// Include OrbisGPT class and functions to get an email subject & body
include('../orbis_gpt.php');
include('../email_functions.php');

// Define prompt for GPT
$prompt = 'For the following email, determine if it contains a job announcement. If it does contain a job announcement, identify the job title, institution, link to apply, and closing date. If it does not contain a job announcement, return null for all values. Format responses in json with the following keys and formats: title as string or null, institution as string or null, link as URL or null, closing as YYYY-MM-DD or null.';

// Get Gmail service
$user = 'me';
$service = new Google_Service_Gmail($client);

// Get messages in the CopyJobs inbox
$messages = $service->users_messages;
$message_list = $messages->listUsersMessages($user, array('q'=>'in:inbox'));
foreach ($message_list->messages as $message) {
  $message_id = $message->id;
  $message = $messages->get($user, $message_id, array('format'=>'full'));
  $payload = $message->getPayload();
  
  // Get subject from payload
  $subject = null;
  $headers = $payload->getHeaders();
  foreach ($headers as $header) {
    if ($header->getName() == 'Subject') {
      $subject = $header->getValue();
    }
  }
  
  // Get body from payload
  $body = get_body($payload, 'text/html');
  if (!$body) {
    $body = get_body($payload, 'text/plain');
  }
  
  // Use GPT API to check if the email contains a job announcement
  if ($subject && $body) {
    // Strip tags to reduce tokens, but retain anchors for links
    $stripped_body = strip_tags($body, '<a>');
    $email = $subject . ' ' . $stripped_body;
    $orbis_gpt = new OrbisGPT();
    try {
      $result = $orbis_gpt->get_result($prompt . ' ' . $email);
      $json = $orbis_gpt->get_json($result);
      // Double-check that the returned title is in the original message
      // if not, the message was not a job posting or GPT hallucinated
      if (stristr($email, $json->title)) {
        // Create new Gravity Forms entry
        $form_id = JOBS_FORM;
        $user_id = JOBS_USER;
        
        // If no closing date found, set to two months in the future
        $closing = $json->closing;
        if ($closing == null) {
          $closing = date('Y-m-d H:i:s', strtotime('today + 2 months'));
        }
        
        // Strip unwanted tags, signature, and style attributes
        $to_replace = array(
          '@<(script|style)[^>]*?>.*?</\\1>@si',
          '/\-\-[\S\s]+This list is an Announcement list only[\S\s]+/',
          '/(<[^>]+) style=".*?"/i'
        );
        $replacements = array('', '', '$1');
        $modified_body = preg_replace($to_replace, $replacements, $body);
        
        // Define the new entry
        $gf_entry = array(
          "1" => $json->institution,
          "3" => $json->title,
          "4" => $modified_body,
          "5" => $json->link,
          "6" => $closing,
          "form_id" => $form_id,
          "date_created" => date('Y-m-d H:i:s'),
          "is_starred" => 0,
          "is_read" => 0,
          "ip" => "35.82.168.182",
          "source_url" => "https://www.orbiscascade.org/jobs-board/submission/",
          "post_id" => null,
          "currency" => "USD",
          "payment_status" => null,
          "payment_date" => null,
          "transaction_id" => null,
          "payment_amount" => null,
          "payment_method" => null,
          "is_fulfilled" => null,
          "created_by" => $user_id,
          "transaction_type" => null,
          "user_agent" => "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:120.0) Gecko/20100101 Firefox/120.0",
          "status" => "active"
        );
        if (!$result = GFAPI::add_entry( $gf_entry )) {
          throw Exception('Could not add entry to Gravity Forms.');
        }
      }
    }
    catch (Exception $e) {
      echo $e->getMessage();
    }
    
    // Mark message as read and remove from inbox
    $modify_request = new Google_Service_Gmail_ModifyMessageRequest();
    $modify_request->setRemoveLabelIds(array("INBOX", "UNREAD"));
    $messages->modify($user, $message_id, $modify_request);
  }
}

// Log time
date_default_timezone_set('America/Los_Angeles');
$fh = fopen('log.txt', 'w');
fwrite($fh, date('Y-m-d H:i:s'));
fclose($fh);
?>
