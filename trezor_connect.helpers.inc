<?php
/**
 * @file
 * Provides helper functions.
 */

/**
 * Provides a string containing the administration menu prefix.
 */
define('TREZOR_CONNECT_PREFIX', 'admin/config/trezor-connect');

/**
 * Provides a string containing the administration permission.
 */
define('TREZOR_CONNECT_PERMISSION', 'administer TREZOR connect');

/**
 * Provides a string containing the use permission.
 */
define('TREZOR_CONNECT_PERMISSION_USE', 'use TREZOR connect');

/**
 * Provides a boolean indicating if the TREZOR connect javascript should be
 * loaded externally.
 */
define('TREZOR_CONNECT_EXTERNAL', TRUE);

/**
 * Provides a string containing the TREZOR connect external javascript url.
 */
define('TREZOR_CONNECT_URL', 'https://trezor.github.io/connect/login.js');

/**
 * Provides a string containing the TREZOR connect callback function.
 */
define('TREZOR_CONNECT_CALLBACK', 'trezorLogin');

/**
 * Provides a string containing the TREZOR connect login callback url.
 */
define('TREZOR_CONNECT_URL_LOGIN', 'user/login/trezor');

/**
 * Provides a string containing the TREZOR connect register callback url.
 */
define('TREZOR_CONNECT_URL_REGISTER', 'user/register/trezor');

/**
 * Provides a string containing the TREZOR connect manage url.
 */
define('TREZOR_CONNECT_URL_MANAGE', 'user/%user/trezor-connect');

/**
 * Provides a string containing the TREZOR connect manage callback url.
 */
define('TREZOR_CONNECT_URL_MANAGE_JS', TREZOR_CONNECT_URL_MANAGE . '/js');

/**
 * Provides an integer containing the challenge expiration offset.
 */
define('TREZOR_CONNECT_OFFSET', 60 * 60);

/**
 * Provides an integer representing challenge response version 1.
 */
define('TREZOR_CONNECT_VERSION_1', 1);

/**
 * Provides an integer representing challenge response version 2.
 */
define('TREZOR_CONNECT_VERSION_2', 2);

/**
 * Returns a boolean indicating if the TREZOR connect javascript should be
 * loaded externally.
 */
function trezor_connect_external() {
  $output = variable_get('trezor_connect_external', TREZOR_CONNECT_EXTERNAL);

  return $output;
}

/**
 * Returns a string containing the TREZOR connect external javascript url.
 */
function trezor_connect_url() {
  $output = variable_get('trezor_connect_url', TREZOR_CONNECT_URL);

  return $output;
}

/**
 * Returns a string containing the TREZOR connect callback function.
 */
function trezor_connect_callback() {
  $output = variable_get('trezor_connect_callback', TREZOR_CONNECT_CALLBACK);

  return $output;
}

/**
 * Returns an integer containing the challenge expiration offset.
 */
function trezor_connect_offset() {
  $output = variable_get('trezor_connect_offset', TREZOR_CONNECT_OFFSET);

  return $output;
}

/**
 * Responsible for generating a challenge array.
 *
 * @return array
 */
function trezor_connect_challenge() {
  $output = array();

  $challenge_hidden = trezor_connect_random(64);
  $challenge_hidden = implode('', $challenge_hidden);

  $output['challenge_hidden'] = $challenge_hidden;

  $challenge_visual = date('Y-m-d H:i:s');

  $output['challenge_visual'] = $challenge_visual;

  return $output;
}

/**
 * Responsible for returning random data.
 *
 * @param $length
 *
 * @return array
 */
function trezor_connect_random($length) {
  $output = array();

  if ($length<2) {
    $length = 2;
  }

  $exists = function_exists('openssl_random_pseudo_bytes');

  if (!$exists) {
    $message = <<<EOF
A cryptographically secure random number can not be generated without the PHP
5.3.0 and the OpenSSL extensions openssl_random_pseudo_bytes function.
EOF;

    $message = t($message);

    throw new Exception($message);
  }
  else {
    $length = $length / 2;

    $crypto_strong = FALSE;

    while (!$crypto_strong) {
      $random = openssl_random_pseudo_bytes($length, $crypto_strong);
    }

    $random = bin2hex($random);

    $output = str_split($random);
  }

  return $output;
}

/**
 * Responsible for adding a challenge to the database.
 *
 * @param $record
 *
 * @return bool|int
 */
function trezor_connect_write_challenge($record) {
  if (is_object($record)) {
    $record = (array)$record;
  }

  if (!isset($record['created'])) {
    $record['created'] = time();
  }

  $output = drupal_write_record('trezor_connect_challenges', $record);

  return $output;
}


/**
 * Returns a boolean indicating if a TREZOR response signature is valid.
 *
 * @param $response
 *
 * @param $challenge
 *
 * @return string
 */
function trezor_connect_response_valid($response, $challenge) {
  $output = FALSE;

  if (is_array($response)) {
    $version = TREZOR_CONNECT_VERSION_1;

    if (isset($response['version'])) {
      $version = $response['version'];
    }

    $challenge_hidden = NULL;

    if (isset($challenge['challenge_hidden'])) {
      $challenge_hidden = $challenge['challenge_hidden'];
    }

    $challenge_visual = NULL;

    if (isset($challenge['challenge_visual'])) {
      $challenge_visual = $challenge['challenge_visual'];
    }

    if ($version == TREZOR_CONNECT_VERSION_1) {
      $challenge_hidden = hex2bin($challenge_hidden);
    }
    else if ($version == TREZOR_CONNECT_VERSION_2) {
      $challenge_hidden = hex2bin($challenge_hidden);
      $challenge_hidden = hash('sha256', $challenge_hidden, TRUE);

      $challenge_visual = hash('sha256', $challenge_visual, TRUE);
    }
    else {
      throw new LogicException('The challenge response version is unknown.');
    }

    $message = $challenge_hidden . $challenge_visual;

    $address = NULL;

    if (isset($response['address'])) {
      $address = $response['address'];
    }

    $public_key = NULL;

    if (isset($response['public_key'])) {
      $public_key = $response['public_key'];
    }

    $signature = NULL;

    if (isset($response['signature'])) {
      $signature = $response['signature'];
    }

    $R = substr($signature, 2, 64);
    $S = substr($signature, 66, 64);

    $ecdsa = new \BitcoinPHP\BitcoinECDSA\BitcoinECDSA();

    $prefix = "\x18Bitcoin Signed Message:\n";

    $len = strlen($message);
    $len = $ecdsa->numToVarIntString($len);

    $data = $prefix . $len . $message;

    $hash = $ecdsa->hash256($data);

    $result = $ecdsa->checkSignaturePoints($public_key, $R, $S, $hash);

    if($result) {
      /*
      $result = $ecdsa->getAddress($public_key);

      if ($result == $address) {
        $output = TRUE;
      }
      */

      $output = TRUE;
    }
  }

  return $output;
}

/**
 * Responsible for writing a TREZOR connect account mapping to the database.
 *
 * @param $record
 *
 * @return bool|int
 */
function trezor_connect_write_map($record) {
  if (is_object($record)) {
    $record = (array)$record;
  }

  if (!isset($record['created'])) {
    $record['created'] = time();
  }

  $output = drupal_write_record('trezor_connect_map', $record);

  return $output;
}

/**
 * Returns a TREZOR connect mapping.
 *
 * @param $response
 */
function trezor_connect_mapping($response) {
  $query = db_select('trezor_connect_map', 'm');

  $query->fields('m');
  $query->condition('public_key', $response['public_key']);

  $output = $query->execute()->fetchAssoc();

  return $output;
}

/**
 * Deletes any TREZOR connect mappings associated with an account.
 *
 * @param $account
 */
function trezor_connect_delete_mapping($account) {
  $query = db_delete('trezor_connect_map');

  $query->condition('uid', $account->uid);

  $query->execute();
}

/**
 * Returns a TREZOR connect mapping associated with an account.
 *
 * @param $response
 */
function trezor_connect_account_mapping($account) {
  $query = db_select('trezor_connect_map', 'm');

  $query->fields('m');
  $query->condition('uid', $account->uid);

  $output = $query->execute()->fetchAssoc();

  return $output;
}
