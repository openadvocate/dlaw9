<?php

namespace Drupal\dlaw_dashboard\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\File\Exception\FileException;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Path\PathValidatorInterface;
use Drupal\Core\Routing\RequestContext;
use Drupal\Core\StreamWrapper\PublicStream;
use Drupal\Core\StreamWrapper\StreamWrapperManager;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\path_alias\AliasManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Mime\MimeTypeGuesserInterface;

/**
 * Class SiteInfoForm.
 *
 * Most elements are a merge of SiteInformationForm and ThemeSettingsForm
 * from /admin/config/system/site-information and /admin/appearance/settings
 */
class SiteInfoForm extends ConfigFormBase {

  /**
   * The path alias manager.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * The path validator.
   *
   * @var \Drupal\Core\Path\PathValidatorInterface
   */
  protected $pathValidator;

  /**
   * The request context.
   *
   * @var \Drupal\Core\Routing\RequestContext
   */
  protected $requestContext;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The theme handler.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * The MIME type guesser.
   *
   * @var \Symfony\Component\Mime\MimeTypeGuesserInterface
   */
  protected $mimeTypeGuesser;

  /**
   * The theme manager.
   *
   * @var \Drupal\Core\Theme\ThemeManagerInterface
   */
  protected $themeManager;

  /**
   * The file system.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * Constructs a SiteInfoForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\path_alias\AliasManagerInterface $alias_manager
   *   The path alias manager.
   * @param \Drupal\Core\Path\PathValidatorInterface $path_validator
   *   The path validator.
   * @param \Drupal\Core\Routing\RequestContext $request_context
   *   The request context.
   */
  public function __construct(ConfigFactoryInterface $config_factory, AliasManagerInterface $alias_manager, PathValidatorInterface $path_validator, RequestContext $request_context,
    ModuleHandlerInterface $module_handler, ThemeHandlerInterface $theme_handler, $mime_type_guesser, ThemeManagerInterface $theme_manager, FileSystemInterface $file_system) {
    parent::__construct($config_factory);
    $this->aliasManager = $alias_manager;
    $this->pathValidator = $path_validator;
    $this->requestContext = $request_context;

    $this->moduleHandler = $module_handler;
    $this->themeHandler = $theme_handler;
    $this->mimeTypeGuesser = $mime_type_guesser;
    $this->themeManager = $theme_manager;
    $this->fileSystem = $file_system;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('path_alias.manager'),
      $container->get('path.validator'),
      $container->get('router.request_context'),

      $container->get('module_handler'),
      $container->get('theme_handler'),
      $container->get('file.mime_type.guesser'),
      $container->get('theme.manager'),
      $container->get('file_system')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dlaw_site_info';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['system.site', 'system.theme.global'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $site_config = $this->config('system.site');
    $site_mail = $site_config->get('mail');
    if (empty($site_mail)) {
      $site_mail = ini_get('sendmail_from');
    }

    $form['site_information'] = [
      '#type' => 'details',
      '#title' => $this->t('Site details'),
      '#open' => TRUE,
    ];
    $form['site_information']['site_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Site name'),
      '#default_value' => $site_config->get('name'),
      '#required' => TRUE,
    ];
    $form['site_information']['site_slogan'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Slogan'),
      '#default_value' => $site_config->get('slogan'),
      '#description' => $this->t("How this is used depends on your site's theme."),
      '#maxlength' => 255,
    ];
    $form['site_information']['site_mail'] = [
      '#type' => 'email',
      '#title' => $this->t('Email address'),
      '#default_value' => $site_mail,
      '#description' => $this->t("The <em>From</em> address in automated emails sent during registration and new password requests, and other notifications. (Use an address ending in your site's domain to help prevent this email being flagged as spam.)"),
      '#required' => TRUE,
    ];
    $form['front_page'] = [
      '#type' => 'details',
      '#title' => $this->t('Front page'),
      '#open' => TRUE,
    ];
    $front_page = $site_config->get('page.front') != '/user/login' ? $this->aliasManager->getAliasByPath($site_config->get('page.front')) : '';
    $form['front_page']['site_frontpage'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default front page'),
      '#default_value' => $front_page,
      '#size' => 40,
      '#description' => $this->t('Optionally, specify a relative URL to display as the front page. Leave blank to display the default front page.'),
      '#field_prefix' => $this->requestContext->getCompleteBaseUrl(),
    ];

    // Display settings.
    $form['display_details'] = [
      '#type' => 'details',
      '#title' => $this->t('Display'),
      '#open' => TRUE,
    ];
    $form['display_details']['display_updated'] = [
      '#type' => 'checkbox',
      '#title' => 'Display updated date at the bottom of Pages',
      '#default_value' => \Drupal::state()->get('dlaw.site_info.display_updated', FALSE),
    ];

    // Logo type settings.
    $form['logotype_details'] = [
      '#type' => 'details',
      '#title' => $this->t('Logotype'),
      '#description' => $this->t("The organization's acronym, maximum 10 characters. Displayed as a logotype if website does not have a logo."),
      '#open' => TRUE,
    ];
    $form['logotype_details']['logotype'] = [
      '#type' => 'textfield',
      '#maxlength' => 10,
      '#default_value' => \Drupal::state()->get('dlaw.site_info.logotype', ''),
    ];

    // Copyright settings.
    $form['copyright_details'] = [
      '#type' => 'details',
      '#title' => $this->t('Copyright information'),
      '#description' => $this->t("Use [year] to print the current year and &amp;copy; for © symbol."),
      '#open' => TRUE,
    ];
    $form['copyright_details']['copyright'] = [
      '#type' => 'textfield',
      '#default_value' => \Drupal::state()->get('dlaw.site_info.copyright', ''),
      '#placeholder' => '&copy; [year] Acme Legal Services',
    ];

    // Logo settings.
    $form['logo'] = [
      '#type' => 'details',
      '#title' => $this->t('Logo image'),
      '#open' => TRUE,
    ];
    $form['logo']['default_logo'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use the logo supplied by the theme'),
      '#default_value' => theme_get_setting('logo.use_default', ''),
      '#tree' => FALSE,
    ];
    $form['logo']['settings'] = [
      '#type' => 'container',
      '#states' => [
        // Hide the logo settings when using the default logo.
        'invisible' => [
          'input[name="default_logo"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['logo']['settings']['logo_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Custom logo'),
      '#default_value' => theme_get_setting('logo.path', ''),
      '#attributes' => ['disabled' => FALSE],
    ];
    $form['logo']['settings']['logo_upload'] = [
      '#type' => 'file',
      '#title' => $this->t('Upload logo image'),
      '#maxlength' => 40,
      '#upload_validators' => [
        'file_validate_is_image' => [],
        'file_validate_image_resolution' => ['800x200'],
      ],
    ];

    // Favicon settings.
    $form['favicon'] = [
      '#type' => 'details',
      '#title' => $this->t('Favicon'),
      '#open' => TRUE,
      '#description' => $this->t("Your shortcut icon, or favicon, is displayed in the address bar and bookmarks of most browsers."),
    ];
    $form['favicon']['default_favicon'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use the favicon supplied by the theme'),
      '#default_value' => theme_get_setting('favicon.use_default', ''),
    ];
    $form['favicon']['settings'] = [
      '#type' => 'container'
    ];
    $form['favicon']['settings']['favicon_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Custom favicon'),
      '#default_value' => theme_get_setting('favicon.path', ''),
      '#attributes' => ['disabled' => FALSE],
    ];
    $form['favicon']['settings']['favicon_upload'] = [
      '#type' => 'file',
      '#title' => $this->t('Upload favicon image'),
      '#upload_validators' => [
        'file_validate_extensions' => [
          'ico png svg',
        ],
        'file_validate_image_resolution' => ['100x100'],
      ],
    ];

    // Inject human-friendly values and form element descriptions for logo and
    // favicon.
    foreach (['logo' => 'logo.svg', 'favicon' => 'favicon.ico'] as $type => $default) {
      if (isset($form[$type]['settings'][$type . '_path'])) {
        $element = &$form[$type]['settings'][$type . '_path'];

        // If path is a public:// URI, display the path relative to the files
        // directory; stream wrappers are not end-user friendly.
        $original_path = $element['#default_value'];
        $friendly_path = NULL;

        if (StreamWrapperManager::getScheme($original_path) == 'public') {
          $friendly_path = StreamWrapperManager::getTarget($original_path);
          $element['#default_value'] = $friendly_path;
        }
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Get the normal path of the front page.
    $form_state->setValueForElement($form['front_page']['site_frontpage'], $this->aliasManager->getPathByAlias($form_state->getValue('site_frontpage')));
    // Validate front page path.
    if (($value = $form_state->getValue('site_frontpage')) && $value[0] !== '/') {
      $form_state->setErrorByName('site_frontpage', $this->t("The path '%path' has to start with a slash.", ['%path' => $form_state->getValue('site_frontpage')]));
    }
    if (!$this->pathValidator->isValid($form_state->getValue('site_frontpage'))) {
      $form_state->setErrorByName('site_frontpage', $this->t("Either the path '%path' is invalid or you do not have access to it.", ['%path' => $form_state->getValue('site_frontpage')]));
    }

    // Check for a new uploaded logo.
    if (isset($form['logo'])) {
      $file = _file_save_upload_from_form($form['logo']['settings']['logo_upload'], $form_state, 0);
      if ($file) {
        // Put the temporary file in form_values so we can save it on submit.
        $form_state->setValue('logo_upload', $file);
      }
    }

    // Check for a new uploaded favicon.
    if (isset($form['favicon'])) {
      $file = _file_save_upload_from_form($form['favicon']['settings']['favicon_upload'], $form_state, 0);
      if ($file) {
        // Put the temporary file in form_values so we can save it on submit.
        $form_state->setValue('favicon_upload', $file);
      }
    }

    // When intending to use the default logo, unset the logo_path.
    if ($form_state->getValue('default_logo')) {
      $form_state->unsetValue('logo_path');
    }

    // When intending to use the default favicon, unset the favicon_path.
    if ($form_state->getValue('default_favicon')) {
      $form_state->unsetValue('favicon_path');
    }

    // If the user provided a path for a logo or favicon file, make sure a file
    // exists at that path.
    if ($form_state->getValue('logo_path')) {
      $path = $this->validatePath($form_state->getValue('logo_path'));
      if (!$path) {
        $form_state->setErrorByName('logo_path', $this->t('The custom logo path is invalid.'));
      }
    }
    if ($form_state->getValue('favicon_path')) {
      $path = $this->validatePath($form_state->getValue('favicon_path'));
      if (!$path) {
        $form_state->setErrorByName('favicon_path', $this->t('The custom favicon path is invalid.'));
      }
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    // Site info values.
    $this->config('system.site')
      ->set('name', $form_state->getValue('site_name'))
      ->set('mail', $form_state->getValue('site_mail'))
      ->set('slogan', $form_state->getValue('site_slogan'))
      ->set('page.front', $form_state->getValue('site_frontpage'))
      ->save();

    // Theme setting values.
    $config = $this->config('system.theme.global');
    $form_state->cleanValues();
    $values = $form_state->getValues();

    // If the user uploaded a new logo or favicon, save it to a permanent location
    // and use it in place of the default theme-provided file.
    $default_scheme = $this->config('system.file')->get('default_scheme');
    try {
      if (!empty($values['logo_upload'])) {
        $filename = $this->fileSystem->copy($values['logo_upload']->getFileUri(), $default_scheme . '://');
        $values['default_logo'] = 0;
        $values['logo_path'] = $filename;
      }
    }
    catch (FileException $e) {
      // Ignore.
    }
    try {
      if (!empty($values['favicon_upload'])) {
        $filename = $this->fileSystem->copy($values['favicon_upload']->getFileUri(), $default_scheme . '://');
        $values['default_favicon'] = 0;
        $values['favicon_path'] = $filename;
        $values['toggle_favicon'] = 1;
      }
    }
    catch (FileException $e) {
      // Ignore.
    }
    unset($values['logo_upload']);
    unset($values['favicon_upload']);

    // If the user entered a path relative to the system files directory for
    // a logo or favicon, store a public:// URI so the theme system can handle it.
    if (!empty($values['logo_path'])) {
      $values['logo_path'] = $this->validatePath($values['logo_path']);
    }
    if (!empty($values['favicon_path'])) {
      $values['favicon_path'] = $this->validatePath($values['favicon_path']);
    }

    if (empty($values['default_favicon']) && !empty($values['favicon_path'])) {
      if ($this->mimeTypeGuesser instanceof MimeTypeGuesserInterface) {
        $values['favicon_mimetype'] = $this->mimeTypeGuesser->guessMimeType($values['favicon_path']);
      }
      else {
        $values['favicon_mimetype'] = $this->mimeTypeGuesser->guess($values['favicon_path']);
        @trigger_error('\Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface is deprecated in drupal:9.1.0 and is removed from drupal:10.0.0. Implement \Symfony\Component\Mime\MimeTypeGuesserInterface instead. See https://www.drupal.org/node/3133341', E_USER_DEPRECATED);
      }
    }

    theme_settings_convert_to_config($values, $config)->save();

    // Custom values
    \Drupal::state()->set('dlaw.site_info.logotype', $form_state->getValue('logotype'));
    \Drupal::state()->set('dlaw.site_info.copyright', $form_state->getValue('copyright'));
    \Drupal::state()->set('dlaw.site_info.display_updated', $form_state->getValue('display_updated'));
  }

  /**
   * Helper function for the system_theme_settings form.
   *
   * Attempts to validate normal system paths, paths relative to the public files
   * directory, or stream wrapper URIs. If the given path is any of the above,
   * returns a valid path or URI that the theme system can display.
   *
   * @param string $path
   *   A path relative to the Drupal root or to the public files directory, or
   *   a stream wrapper URI.
   *
   * @return mixed
   *   A valid path that can be displayed through the theme system, or FALSE if
   *   the path could not be validated.
   */
  protected function validatePath($path) {
    // Absolute local file paths are invalid.
    if ($this->fileSystem->realpath($path) == $path) {
      return FALSE;
    }
    // A path relative to the Drupal root or a fully qualified URI is valid.
    if (is_file($path)) {
      return $path;
    }
    // Prepend 'public://' for relative file paths within public filesystem.
    if (StreamWrapperManager::getScheme($path) === FALSE) {
      $path = 'public://' . $path;
    }
    if (is_file($path)) {
      return $path;
    }
    return FALSE;
  }

}
