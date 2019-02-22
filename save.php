<?php
/**
 * Sanitizes and saves the JSON data posted from the Perspective Taking/Spatial Orientation Test.
 */
$response = isset($_POST['response']) ? $_POST['response'] : NULL;
if ($response === NULL) die();

// Sanitize
$response = json_decode($response, TRUE);
$response['partnum'] = intval($response['partnum']);
$response['angles'] = array_map(intval, $response['angles']);
$response['avgerr'] = round($response['avgerr'] * 100, 0) / 100;
$response['submitted'] = filter_var($response['submitted'], FILTER_SANITIZE_STRING);
$response['complete'] = intval($response['complete']);

// Save
$fp = fopen("responses.dat", "a");
fwrite($fp, json_encode($response) . "\n");
fclose($fp);
