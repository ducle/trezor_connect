<?php
/**
 * @file
 * Contains \Drupal\trezor_connect\Mapping\MappingManagerInterface.
 */

namespace Drupal\trezor_connect\Mapping;

use Drupal\trezor_connect\Mapping\MappingBackendInterface;

interface MappingManagerInterface {

  /**
   * Sets the backend.
   *
   * @param \Drupal\trezor_connect\Mapping\MappingBackendInterface $backend
   *
   * @return mixed
   */
  public function setBackend(MappingBackendInterface $backend);

  /**
   * Returns the challenge backend.
   *
   * @return \Drupal\trezor_connect\Mapping\MappingBackendInterface
   */
  public function getBackend();

  /**
   * Returns a mapping associated with a public key.
   *
   * @param string $public_key
   *   The public key of the mapping to retrieve.
   *
   * @return Mapping|false
   *   The mapping object or FALSE.
   *
   * @see \Drupal\trezor_connect\MappingBackendInterface::getMultiple()
   */
  public function get($public_key);

  /**
   * Returns a mapping associated with an account uid.
   *
   * @param $uid
   *
   * @return mixed
   */
  public function getFromUid(integer $uid);

  /**
   * Returns the mappings associated with an array of public keys.
   *
   * @param array $public_keys
   *   An array of mapping public keys.
   *
   * @return array
   *   An array of Mapping objects.
   *
   * @see \Drupal\trezor_connect\MappingBackendInterface::get()
   */
  public function getMultiple(array $public_keys);

  /**
   * Stores a mapping.
   *
   * @param Mapping $mapping
   *   The mapping object to store.
   */
  public function set(Mapping $mapping);

  /**
   * Store multiple mappings.
   *
   * @param array $mappings
   *   An array of Mapping objects.
   */
  public function setMultiple(array $mappings);

  /**
   * Deletes a mapping.
   *
   * @param integer $uid
   *   The account id whose mappings should be deleted.
   *
   * @see \Drupal\trezor_connect\MappingBackendInterface::deleteMultiple()
   * @see \Drupal\trezor_connect\MappingBackendInterface::deleteAll()
   */
  public function delete($uid);

}
