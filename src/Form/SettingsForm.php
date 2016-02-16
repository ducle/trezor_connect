<?php

/**
 * @file
 * Contains \Drupal\trezor_connect\Form\SettingsForm.
 */

namespace Drupal\trezor_connect\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\trezor_connect\TrezorConnectInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides trezor connect settings.
 */
class SettingsForm extends ConfigFormBase {
  const NS = 'trezor_connect.settings';

  /**
   * The state keyvalue collection.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  protected $challenge_backends;

  protected $challenge_backend;

  protected $challenge_response_backends;

  protected $challenge_response_backend;

  protected $mapping_backends;

  protected $mapping_backend;

  protected $trezor_connect;

  protected $date_formatter;

  /**
   * Provides the module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $module_handler;

  /**
   * Provides the theme handler service.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $theme_handler;

  /**
   * Constructs a new form.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state keyvalue collection to use.
   */
  public function __construct(ConfigFactoryInterface $config_factory, StateInterface $state, $challenge_backends, $challenge_backend, $challenge_response_backends, $challenge_response_backend, $mapping_backends, $mapping_backend, TrezorConnectInterface $trezor_connect, DateFormatterInterface $date_formatter, ModuleHandlerInterface $module_handler, ThemeHandlerInterface $theme_handler) {
    parent::__construct($config_factory);

    $this->state = $state;

    $this->challenge_backends = $challenge_backends;
    $this->challenge_backend = $challenge_backend;

    $this->challenge_response_backends = $challenge_response_backends;
    $this->challenge_response_backend = $challenge_response_backend;

    $this->mapping_backends = $mapping_backends;
    $this->mapping_backend = $mapping_backend;

    $this->trezor_connect = $trezor_connect;

    $this->date_formatter = $date_formatter;

    $this->module_handler = $module_handler;

    $this->theme_handler = $theme_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('state'),
      $container->getParameter('trezor_connect_challenge_backends'),
      $container->getParameter('trezor_connect_challenge_backend'),
      $container->getParameter('trezor_connect_challenge_response_backends'),
      $container->getParameter('trezor_connect_challenge_response_backend'),
      $container->getParameter('trezor_connect_mapping_backends'),
      $container->getParameter('trezor_connect_mapping_backend'),
      $container->get('trezor_connect'),
      $container->get('date.formatter'),
      $container->get('module_handler'),
      $container->get('theme_handler')
    );
  }
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'trezor_connect_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [self::NS];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(self::NS);

    $key = 'text';

    $description = t('Please specify the button text to display on the login page.');

    $default_value = $config->get($key);

    $form[$key] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => t('Login Display Text'),
      '#description' => $description,
      '#default_value' => $default_value,
    );

    $key = 'text_register';

    $description = t('Please specify the button text to display on the registration page.');

    $default_value = $config->get($key);

    $form[$key] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => t('Register Display Text'),
      '#description' => $description,
      '#default_value' => $default_value,
    );

    $key = 'text_manage';

    $description = t('Please specify the button text to display when a user is adding an authentication device to their account.');

    $default_value = $config->get($key);

    $form[$key] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => t('Authentication Device Display Text'),
      '#description' => $description,
      '#default_value' => $default_value,
    );

    $key = 'text_manage_admin';

    $description = t('Please specify the button text to display when an administrator is adding an authentication device to an account.');

    $default_value = $config->get($key);

    $form[$key] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => t('Administrator Authentication Device Display Text'),
      '#description' => $description,
      '#default_value' => $default_value,
    );

    $features = [];

    $theme = $this->theme_handler->getDefault();
    $info = $this->theme_handler->listInfo();

    if (isset($info[$theme])) {
      $features = $info[$theme]->info['features'];
    }

    $result = $this->module_handler->moduleExists('file');

    if ($result) {
      $key = 'icon';
      $prefix = $key;

      $form[$key] = array(
        '#type' => 'details',
        '#title' => t('Icon'),
        '#open' => TRUE,
      );

      $icon = &$form[$key];

      $key = 'source';

      $description = t('Please specify the icon source.');

      $default_value = $config->get($prefix . '.' . $key);

      $options = array();

      $options[TrezorConnectInterface::ICON_SOURCE_DEFAULT] = $this->t('Default');

      $result = in_array('logo', $features);

      if ($result) {
        $options[TrezorConnectInterface::ICON_SOURCE_THEME] = $this->t('Theme');
      }

      $options[TrezorConnectInterface::ICON_SOURCE_CUSTOM] = $this->t('Custom');

      $icon[$key] = array(
        '#type' => 'radios',
        '#title' => t('Icon Source'),
        '#description' => $description,
        '#default_value' => $default_value,
        '#options' => $options,
      );

      $key = 'settings';

      $icon[$key] = array(
        '#type' => 'container',
        '#states' => array(
          'visible' => array(
            'input[name="source"]' => array(
              'value' => 'custom',
            ),
          ),
        ),
      );

      $settings = &$icon[$key];

      $key = 'path';

      $description = t('Please specify the path to the custom icon.');

      $default_value = $config->get($prefix . '.' . $key);

      $settings[$key] = array(
        '#type' => 'textfield',
        '#title' => t('Custom Icon Path'),
        '#description' => $description,
        '#default_value' => $default_value,
      );

      $key = 'upload';

      $description = t('Please upload the custom icon.');

      $settings[$key] = array(
        '#type' => 'file',
        '#title' => t('Upload Icon'),
        '#description' => $description,
      );
    }

    $key = 'external';

    $description = t('Please specify whether to load the TREZOR connect javascript externally.');

    $options = array(
      TrezorConnectInterface::EXTERNAL_YES => t('Yes'),
      TrezorConnectInterface::EXTERNAL_NO => t('No'),
    );

    $default_value = $config->get($key);

    $form[$key] = array(
      '#type' => 'radios',
      '#title' => t('Use CDN'),
      '#description' => $description,
      '#options' => $options,
      '#default_value' => $default_value,
    );

    $key = 'url';

    $description = t('Please specify the external TREZOR connect javascript url.');
    $default_value = $config->get($key);

    $form[$key] = array(
      '#type' => 'textfield',
      '#title' => t('External TREZOR Connect Javascript URL'),
      '#description' => $description,
      '#default_value' => $default_value,
    );

    $key = 'callback';

    $description = t('Please specify the TREZOR connect callback function.');
    $default_value = $config->get($key);

    $form[$key] = array(
      '#type' => 'textfield',
      '#title' => t('TREZOR Connect Callback'),
      '#description' => $description,
      '#default_value' => $default_value,
    );

    $key = 'flood_threshold';

    $description = t('Please specify the number of password attempts a user should be allowed.');
    $default_value = $config->get($key);

    $form[$key] = array(
      '#type' => 'number',
      '#title' => t('Password Attempts'),
      '#description' => $description,
      '#default_value' => $default_value,
      '#min' => 1,
    );

    $key = 'flood_window';

    $description = t('Please specify the number of seconds before an invalid password attempt is forgotten.');

    $options = array(1800, 2700, 3600, 4500, 5400, 6300, 7200);
    $options = array_combine($options, $options);

    $options = array_map(array($this->date_formatter, 'formatInterval'), $options);

    $default_value = $config->get($key);

    $form[$key] = array(
      '#type' => 'select',
      '#title' => t('Password Attempt Interval'),
      '#description' => $description,
      '#default_value' => $default_value,
      '#options' => $options,
    );

    $key = 'challenge_offset';

    $description = t('Please specify the number of seconds a challenge should be valid for.');

    $options = array(1800, 2700, 3600, 10800, 21600, 32400, 43200, 86400);
    $options = array_combine($options, $options);

    $options = array_map(array($this->date_formatter, 'formatInterval'), $options);

    $default_value = $config->get($key);

    $form[$key] = array(
      '#type' => 'select',
      '#title' => t('Challenge Offset'),
      '#description' => $description,
      '#default_value' => $default_value,
      '#options' => $options,
    );

    $key = 'challenge_backend';

    $description = t('Please specify the challenge backend to use.');

    $options = $this->trezor_connect->challengeBackendOptions();

    $default_value = $this->challenge_backend;

    $result = $config->get($key);

    if ($result) {
      $default_value = $result;
    }

    $form[$key] = array(
      '#type' => 'radios',
      '#required' => TRUE,
      '#title' => t('Challenge Backend'),
      '#options' => $options,
      '#empty_value' => '',
      '#empty_option' => t('Select an option'),
      '#description' => $description,
      '#default_value' => $default_value,
    );

    $key = 'challenge_response_backend';

    $description = t('Please specify the challenge response backend to use.');

    $options = $this->trezor_connect->challengeResponseBackendOptions();

    $default_value = $this->challenge_response_backend;

    $result = $config->get($key);

    if ($result) {
      $default_value = $result;
    }

    $form[$key] = array(
      '#type' => 'radios',
      '#required' => TRUE,
      '#title' => t('Challenge Response Backend'),
      '#options' => $options,
      '#empty_value' => '',
      '#empty_option' => t('Select an option'),
      '#description' => $description,
      '#default_value' => $default_value,
    );

    $key = 'mapping_backend';

    $description = t('Please specify the mapping backend to use.');

    $options = $this->trezor_connect->mappingBackendOptions();

    $default_value = $this->mapping_backend;

    $result = $config->get($key);

    if ($result) {
      $default_value = $result;
    }

    $form[$key] = array(
      '#type' => 'radios',
      '#required' => TRUE,
      '#title' => t('Mapping Backend'),
      '#options' => $options,
      '#empty_value' => '',
      '#empty_option' => t('Select an option'),
      '#description' => $description,
      '#default_value' => $default_value,
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    $source = $form_state->getValue('source');

    if ($source == TrezorConnectInterface::ICON_SOURCE_CUSTOM) {
      // Validate the custom icon upload
      $result = $this->module_handler->moduleExists('file');

      if ($result) {
        $key = 'upload';

        $validators = array(
          'file_validate_is_image' => array(),
        );

        $file = file_save_upload($key, $validators, FALSE, 0);

        if (!is_null($file)) {
          if (!$file) {
            $message = $this->t('An error occurred processing your icon upload.');

            $form_state->setErrorByName($key, $message);
          }
          else {
            $form_state->setValue($key, $file);
          }
        }
      }

      // Validate the custom icon path
      $key = 'path';

      $path = $form_state->getValue($key);

      if ($path) {
        $path = $this->validatePath($path);

        if (!$path) {
          $message = $this->t('Please specify a valid icon path.');

          $form_state->setErrorByName($key, $message);
        }
      }
    }
  }

  /**
   * Validates a icon path.
   *
   * Pulled from ThemeSettingsform::validatePath
   *
   * Attempts to validate normal system paths, paths relative to the public files
   * directory, or stream wrapper URIs. If the given path is any of the above,
   * returns a valid path or URI that the theme system can display.
   *
   * @param string $path
   *   A path relative to the Drupal root or to the public files directory, or
   *   a stream wrapper URI.
   * @return mixed
   *   A valid path that can be displayed through the theme system, or FALSE if
   *   the path could not be validated.
   */
  protected function validatePath($path) {
    // Absolute local file paths are invalid.
    if (drupal_realpath($path) == $path) {
      return FALSE;
    }
    // A path relative to the Drupal root or a fully qualified URI is valid.
    if (is_file($path)) {
      return $path;
    }
    // Prepend 'public://' for relative file paths within public filesystem.
    if (file_uri_scheme($path) === FALSE) {
      $path = 'public://' . $path;
    }
    if (is_file($path)) {
      return $path;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $config = $this->config(self::NS);

    $keys = array(
      'text',
      'text_register',
      'text_manage',
      'text_manage_admin',
      'external',
      'url',
      'callback',
      'flood_threshold',
      'flood_window',
      'challenge_offset',
      'challenge_backend',
      'challenge_response_backend',
      'mapping_backend',
    );

    foreach ($keys as $key) {
      $config->set($key, $form_state->getValue($key));
    }

    $key = 'source';

    $source = $form_state->getValue($key);

    if ($source == TrezorConnectInterface::ICON_SOURCE_CUSTOM) {
      $key = 'upload';

      $upload = $form_state->getValue($key);

      if ($upload) {
        // Set the icon path to the upload path
        $uri = $upload->getFileUri();

        $path = file_unmanaged_copy($uri);
      }
      else {
        // Use the icon path
        $path = $form_state->getValue('path');
      }

      $config->set('icon.path', $path);
    }

    $config->set('icon.source', $source);

    $config->save();
  }

}
