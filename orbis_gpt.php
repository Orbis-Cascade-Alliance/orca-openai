<?php
class OrbisGPT {
  
  function __construct() {
    // Do nothing
  }
  
  // Send prompt to the GPT API
  function get_result($prompt) {
    $header = array();
    $header[] = 'Content-type: application/json';
    $header[] = "Authorization: Bearer " . OPENAI_SECRET;
    $data = '{
      "model": "gpt-4-0125-preview",
      "response_format": { "type": "json_object" },
      "messages": [
        {
          "role": "system",
          "content": "' . urlencode($prompt) . '"
        }
      ]
    }';
    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    return $result;
  }
  
  // Return the JSON contents of an API result
  function get_json($result) {
    if ($json = json_decode($result)) {
      if (isset($json->error) && $json->error->type == 'invalid_request_error') {
        throw new Exception($json->error->message);
      }
      else if (isset($json->choices[0])) {
        return json_decode($json->choices[0]->message->content);
      }
      else {
       throw new Exception('JSON not as expected.');
      }
    }
    throw new Exception('Result is not JSON.');
    return false;
  }

}
?>
