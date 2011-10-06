<?php
$curl = curl_init();
curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($curl, CURLOPT_USERPWD, "$githubUsername:$githubPassword");
curl_setopt($curl, CURLOPT_SSLVERSION, 3);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($curl, CURLOPT_HEADER, FALSE);
curl_setopt($curl, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json'));

$labels = array();

function sanitizeLabel($string) {
  return preg_replace('/[^\w\s-]+/', '-', trim($string));
}

function getLabels() {
  global $curl, $labels, $githubRepoOwner, $githubRepo;
  curl_setopt($curl, CURLOPT_URL, "https://api.github.com/repos/$githubRepoOwner/$githubRepo/labels");
  curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET'); // or POST
  curl_setopt($curl, CURLOPT_POSTFIELDS, '');
  $labelData = json_decode(curl_exec($curl));
  foreach ($labelData as $label) {
    $labels[] = $label->name;
  }
}
getLabels();

function createLabel($name) {
  global $curl, $labels, $githubRepoOwner, $githubRepo;
  curl_setopt($curl, CURLOPT_URL, "https://api.github.com/repos/$githubRepoOwner/$githubRepo/labels");
  curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST'); // or POST
  curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode( array( 'name'=> $name, 'color'=> '000000' )));
  $labels[] = $name;
  echo 'Creating label: "' . $name . "\"\n";
  return json_decode(curl_exec($curl));
}

function createIssue($array) {
  global $curl, $labels, $githubRepoOwner, $githubRepo;
  foreach ($array['labels'] as $label) {
    if (!in_array($label, $labels)) createLabel($label);
  }
  curl_setopt($curl, CURLOPT_URL, "https://api.github.com/repos/$githubRepoOwner/$githubRepo/issues");
  curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
  curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode( $array ));
  return json_decode(curl_exec($curl));
}

function closeIssue($id) {
  global $curl, $githubRepoOwner, $githubRepo;
  curl_setopt($curl, CURLOPT_URL, "https://api.github.com/repos/$githubRepoOwner/$githubRepo/issues/$id");
  curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
  curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode( array( 'state'=> 'closed' )));
  return json_decode(curl_exec($curl));
}
