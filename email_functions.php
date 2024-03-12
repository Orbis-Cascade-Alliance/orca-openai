<?php

// Get the text body of the original message
function get_body($parent, $type) {
  // For plain-text emails, the body is at the top level
  if ($body = check_part($parent, $type)) {
    return $body;
  }
  // For multi-part emails with attachments, 
  // the body might be a few levels deep in the "parts"
  else if ($parts = $parent->getParts()) {
    foreach ($parts as $part) {
      if ($part_body = check_part($part, $type)) {
        return $part_body;
      }
      else if ($subparts = $part->getParts()) {
        foreach ($subparts as $subpart) {
          if ($subpart_body = check_part($subpart, $type)) {
            return $subpart_body;
          }
          else if ($subsubparts = $subpart->getParts()) {
            foreach ($subsubparts as $subsubpart) {
              if ($subsubpart_body = check_part($subsubpart, $type)) {
                return $subsubpart_body;
              }
            }
          }
        }
      }
    }
  }
  return false;
}

// Check the MimeType and data element of a body part
function check_part($part, $type) {
  if ($part->mimeType == $type && $part->getBody()->getData()) {
    return decode_body($part->getBody()->getData());
  }
  return false;
}

// Base64url decode a body part
function decode_body($body) {
  $rawData = $body;
  $sanitizedData = strtr($rawData,'-_', '+/');
  $decodedMessage = base64_decode($sanitizedData);
  if(!$decodedMessage){
      $decodedMessage = FALSE;
  }
  return $decodedMessage;
}

// Base64url encode a message
function urlsafe_b64encode($string) {
    $data = base64_encode($string);
    $data = str_replace(array('+','/','='),array('-','_',''),$data);
    return $data;
}

?>
