<?php
/**
 * @file
 * Contains \Drupal\trezor_connect\ChallengeResponse\ChallengeResponseManagerInterface.
 */

namespace Drupal\trezor_connect\ChallengeResponse;

use Drupal\trezor_connect\Challenge\ChallengeInterface;
use Drupal\trezor_connect\Challenge\ChallengeManagerInterface;
use Drupal\trezor_connect\Mapping\MappingManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;

interface ChallengeResponseManagerInterface {

  /**
   * Sets the session service.
   *
   * @param $session
   *
   * @return mixed
   */
  public function setSession(SessionInterface $session);

  /**
   * Returns the session service.
   *
   * @return
   */
  public function getSession();

  /**
   * Sets the current request.
   *
   * @param $request
   *
   * @return mixed
   */
  public function setRequest(Request $request);

  /**
   * Returns the current request.
   *
   * @return
   */
  public function getRequest();

  /**
   * Sets the backend.
   *
   * @param \Drupal\trezor_connect\ChallengeResponse\ChallengeResponseBackendInterface $backend
   *
   * @return mixed
   */
  public function setBackend(ChallengeResponseBackendInterface $backend);

  /**
   * Returns the challenge response backend.
   *
   * @return \Drupal\trezor_connect\ChallengeResponse\ChallengeResponseBackendInterface
   */
  public function getBackend();

  /**
   * Sets the challenge response.
   *
   * @param \Drupal\trezor_connect\ChallengeResponse\ChallengeResponseInterface $challenge
   *
   * @return mixed
   */
  public function setChallengeResponse(ChallengeResponseInterface $challenge_response);

  /**
   * Returns the challenge response.
   *
   * @return \Drupal\trezor_connect\ChallengeResponse\ChallengeResponseInterface
   */
  public function getChallengeResponse();

  /**
   * Sets the challenge manager service.
   *
   * @param \Drupal\trezor_connect\Challenge\ChallengeManagerInterface $challenge_manager
   *
   * @return mixed
   */
  public function setChallengeManager(ChallengeManagerInterface $challenge_manager);

  /**
   * Returns the challenge manager service.
   *
   * @return \Drupal\trezor_connect\Challenge\ChallengeManagerInterface
   */
  public function getChallengeManager();


  /**
   * Sets the challenge response offset.
   *
   * @param int $challenge_response_offset
   */
  public function setChallengeResponseOffset($challenge_response_offset);

  /**
   * Returns the challenge response offset.
   */
  public function getChallengeResponseOffset();

  /**
   * Returns the challenge responses associated with an id.
   *
   * @param int|array|NULL $id
   *   The challenge response id to retrieve.  If null, the current request will
   * be checked for a challenge, otherwise a new challenge will be generated and
   * returned.
   *
   * @param array $conditions
   *   An array of conditions.  The array should contain the following keys:
   *
   *     field - A string containing the name of the field.
   *     value - A string containing the value for the condition.
   *     operator - A string containing the condition operator.
   *
   * @return array
   *   An array of ChallengeResponse objects.
   *
   * @see \Drupal\trezor_connect\ChallengeResponse\ChallengeResponseBackendInterface::getMultiple()
   */
  public function get($id = NULL, array $conditions = NULL);

  /**
   * Returns a challenge response associated with the current request.
   *
   * @return ChallengeResponse|false
   *   The challenge response object or FALSE.
   */
  public function getRequestChallengeResponse();

  /**
   * Returns a challenge response associated with the session.
   *
   * @return ChallengeResponse|false
   *   The challenge response object or FALSE.
   */
  public function getSessionChallengeResponse();

  /**
   * Returns the challenges responses associated with an array of ids.
   *
   * @param array $ids
   *   The challenge response ids to retrieve.
   *
   * @param array $conditions
   *   An array of conditions.  The array should contain the following keys:
   *
   *     field - A string containing the name of the field.
   *     value - A string containing the value for the condition.
   *     operator - A string containing the condition operator.
   *
   * @return array
   *   An array of ChallengeResponse objects.
   *
   * @see \Drupal\trezor_connect\ChallengeResponse\ChallengeResponseBackendInterface::get()
   */
  public function getMultiple(array $ids, array $conditions = NULL);

  /**
   * Returns the challenge response associated with the public key.
   *
   * @param $public_key
   *
   * @return mixed
   */
  public function getPublicKey($public_key);

  /**
   * Returns the challenge responses associated with the public keys.
   *
   * @param $public_keys
   *
   * @return mixed
   */
  public function getMultipleFromPublicKey($public_keys);

  /**
   * Stores a challenge response.
   *
   * @param ChallengeResponse $challenge_response
   *   The challenge response object to store.
   *
   * @param boolean $session
   *   Determines whether to store the challenge response on the session.
   */
  public function set(ChallengeResponseInterface $challenge_response, $session = TRUE);

  /**
   * Stores the active challenge response on the session.
   *
   * @return mixed
   */
  public function setSessionChallengeResponse(ChallengeResponseInterface $challenge_response);

  /**
   * Deletes a challenge response.
   *
   * @param integer $id
   *   The challenge response id to be deleted.
   *
   * @see \Drupal\trezor_connect\ChallengeResponse\ChallengeResponseBackendInterface::deleteMultiplel()
   */
  public function delete($id);

  /**
   * Deletes a challenge response.
   *
   * @param array $ids
   *   The challenge response ids to be deleted.
   *
   * @see \Drupal\trezor_connect\ChallengeResponse\ChallengeResponseBackendInterface::delete()
   */
  public function deleteMultiple(array $ids);

  /**
   * Deletes all challenge responses.
   */
  public function deleteAll();

  /**
   * Removes the active challenge response from the session.
   *
   * @return mixed
   */
  public function deleteSessionChallengeResponse();

  /**
   * Deletes any expired challenge responses.
   *
   * @param MappingManagerInterface $mapping_manager
   *   The mapping manager used to retrieve any mappings associated with the
   * challenge responses, as these cannot be deleted.
   */
  public function deleteExpired(MappingManagerInterface $mapping_manager);

}
