<?php
/*
Plugin Name: Infoset
Plugin URI: https://wordpress.org/plugins/infoset
Description: Official <a href="https://infoset.app">Infoset</a> chat widget for WordPress.
Author: Infoset
Author URI: https://infoset.app
Version: 1.0
*/

class InfosetIdVerificationCalculator
{
  private $user_config = NULL;
  private $private_key = "";

  public function __construct($user_config, $private_key)
  {
    $this->user_config = $user_config;
    $this->private_key = $private_key;
  }

  public function idVerificationComponent() {
    $private_key = $this->privateKey();
    $user_config = $this->userConfig();

    if (is_null($private_key) || empty($user_config)) {
      return $this->emptyIdVerificationHashComponent();
    }

    return $this->idVerificationHashComponent();
  }

  private function emptyIdVerificationHashComponent() {
    return array();
  }

  private function idVerificationHashComponent() {
    return array("userHash" => hash_hmac("sha256", $this->userId(), $this->privateKey()));
  }

  private function privateKey() {
    return $this->private_key;
  }

  private function userId() {
    return $this->user_config["id"];
  }

  private function userConfig() {
    return $this->user_config;
  }
}

class InfosetSettingsPage 
{
	private $settings = array();
  private $styles = array();

  public function __construct($settings)
  {
    $this->settings = $settings;
    $this->styles = $this->setStyles($settings);
  }

  public function dismissibleMessage($text)
  {
    return <<<END
  <div id="message" class="updated notice is-dismissible">
    <p>$text</p>
    <button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
  </div>
END;
  }

  public function getAuthUrl() {
    return "https://dashboard.infoset.app/select-chat?service=wordpress&state=".get_site_url()."::".wp_create_nonce("infoset-auth");
  }

  public function htmlUnclosed()
  {
    $settings = $this->getSettings();
    $styles = $this->getStyles();
    $api_key = esc_attr($settings['api_key']);
    $private_key = esc_attr($settings['private_key']);
    $auth_url = $this->getAuthUrl();
    $dismissable_message = '';
    $assets_path = plugin_dir_url(__FILE__) . "assets";
    $styles_path = plugin_dir_url(__FILE__) . "styles";

    if (isset($_GET['api_key'])) {
      $api_key = sanitize_text_field($_GET['api_key']);
      $dismissable_message = $this->dismissibleMessage("We've copied your new Infoset Chat Api Key below. Click to save changes and then close this window to finish installing the chat widget.");
    }
    if (isset($_GET['saved'])) {
      $dismissable_message = $this->dismissibleMessage("Your chat api key has been successfully saved. You can now close this window to finish signing installing the chat widget.");
    }
    if (isset($_GET['authenticated'])) {
      $dismissable_message = $this->dismissibleMessage('You\'ve successfully authenticated with Infoset');
    }

    return <<<END

    
    <style>
      #wpcontent {
        background-color: #ffffff;
      }
    </style>

    <div class="wrap">
      $dismissable_message

      <section id="main_content" style="padding-top: 70px;">
        <div class="container">
          <div class="cta">

            <div class="sp__2--lg sp__2--xlg"></div>
            <div id="auth_content" style="$styles[api_key_link_style]">
              <div class="t__h1 c__red">Get started with Infoset</div>

              <div class="cta__desc">
                Chat with visitors to your website in real-time, capture them as leads, and convert them to customers. Add Infoset to your WordPress site.
              </div>

              <div id="get_infoset_btn_container" style="position:relative;margin-top:30px;">
                <a href="$auth_url">
                  <img src="$assets_path/oauth-login-20210118.png" srcset="$assets_path/oauth-login-20210118.png 1x, $assets_path/oauth-login-20210118@2x.png 2x, $assets_path/oauth-login-20210118@3x.png 3x"/>
                </a>
              </div>
            </div>

            <div class="t__h1 c__red" style="$styles[api_key_copy_title]">Infoset setup</div>
            <div class="t__h1 c__red" style="$styles[api_key_saved_title]">Infoset chat key saved</div>
            <div id="api_key_content" style="$styles[api_key_row_style]">
              <div class="t__h1 c__red" style="$styles[api_key_copy_hidden]">Infoset has been installed</div>

              <div class="cta__desc">
                <div style="$styles[api_key_copy_hidden]">
                  Infoset is ready to go. You can now chat with your existing and potential new customers, send them targeted messages, and get feedback.
                  <br/>
                  <br/>
                  <a class="c__blue" href="https://dashboard.infoset.app/chats" target="_blank">Click here to access your Infoset Chats.</a>
                  <br/>
                  <br/>
                  Need help? <a class="c__blue" href="https://infoset.app/help/en" target="_blank">Visit our blog</a> for best practices, tips, and much more.
                  <br/>
                  <br/>
                </div>

                  <form method="post" action="" name="update_settings">
                    <table class="form-table" align="center" style="margin-top: 16px; width: inherit;">
                      <tbody>
                        <tr>
                          <th scope="row" style="text-align: center; vertical-align: middle;"><label for="infoset_api_key">Chat Api Key</label></th>
                          <td>
                            <input id="infoset_api_key" $styles[api_key_state] name="api_key" type="text" value="$api_key" class="$styles[api_key_class]">
                            <button type="submit" class="btn btn__primary cta__submit" style="$styles[button_submit_style]">Save</button>
                          </td>
                        </tr>
                      </tbody>
                    </table>

END;
  }

  public function htmlClosed()
  {
    $settings = $this->getSettings();
    $styles = $this->getStyles();
    $auth_url = $this->getAuthUrl();
    $api_key = esc_attr($settings['api_key']);
    $private_key = esc_attr($settings['private_key']);
    $auth_url_identity_verification = "";
    if (!empty($api_key)) {
      $auth_url_identity_verification = $auth_url.'&identity_verification=1';
    }
    return <<<END
                  </form>
                  <div style="$styles[api_key_copy_hidden]">
                    <div style="$styles[private_key_link_style]">
                      <a class="c__blue" href="$auth_url_identity_verification">Authenticate with your Infoset application to enable Identity Verification</a>
                    </div>
                    <p style="font-size:0.86em">Identity verification helps ensure that chats between you and your users are kept private and that one person cannot impersonate another.<br/>
                    <br/>
                      <a class="c__blue" href="https://infoset.app/help/en/articles/498-enable-identity-verification-in-live-chat" target="_blank">Learn more about Identity Verification</a>
                    </p>
                    <br/>
                    <div style="font-size:0.8em">If the Infoset Chat associated with your Wordpress is incorrect, please <a class="c__blue" href="$auth_url">click here</a> to reconnect with Infoset, to choose a new application.</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
    
END;
  }

  public function html()
  {
    return $this->htmlUnclosed() . $this->htmlClosed();
  }

  public function setStyles($settings) {
    $styles = array();
    $api_key = esc_attr($settings['api_key']);
    $private_key = esc_attr($settings['private_key']);

    // Case : Identity Verification enabled : checkbox checked and disabled
    if($private_key) {
      $styles['identity_verification_state'] = 'checked disabled';
    } else {
      $styles['identity_verification_state'] = '';
    }

    // Case : api_key here but Identity Verification disabled
    if (!empty($api_key)) {
      $styles['private_key_row_style'] = 'display: none;';
      $styles['private_key_link_style'] = '';
    } else {
      $styles['private_key_row_style'] = '';
      $styles['private_key_link_style'] = 'display: none;';
    }

    // Copy apiKey from Infoset Setup Guide for validation
    if (isset($_GET['apiKey'])) {
        $api_key = sanitize_text_field($_GET['apiKey']);
        $styles['api_key_state'] = 'readonly';
        $styles['api_key_class'] = "cta__email";
        $styles['button_submit_style'] = '';
        $styles['api_key_copy_hidden'] = 'display: none;';
        $styles['api_key_copy_title'] = '';
        $styles['identity_verification_state'] = 'disabled'; # Prevent from sending POST data about identity_verification when using api_key form
    } else {
      $styles['api_key_class'] = "";
      $styles['button_submit_style'] = 'display: none;';
      $styles['api_key_copy_title'] = 'display: none;';
      $styles['api_key_state'] = 'disabled'; # Prevent from sending POST data about api_key when using identity_verification form
      $styles['api_key_copy_hidden'] = '';
    }

    // Case api_key successfully copied
    if (isset($_GET['saved'])) {
      $styles['api_key_copy_hidden'] = 'display: none;';
      $styles['api_key_saved_title'] = '';
    } else {
      $styles['api_key_saved_title'] = 'display: none;';
    }

    // Display 'reconnect with infoset' button if no api_key provided (copied from setup guide or from Oauth)
    if (empty($api_key)) {
      $styles['api_key_row_style'] = 'display: none;';
      $styles['api_key_link_style'] = '';
    } else {
      $styles['api_key_row_style'] = '';
      $styles['api_key_link_style'] = 'display: none;';
    }
    return $styles;
  }

  private function getSettings()
  {
    return $this->settings;
  }

  private function getStyles()
  {
    return $this->styles;
  }

}

class InfosetSnippet {
	private $snippet_config = "";

  public function __construct($snippet_config)
  {
    $this->snippet_config = $snippet_config;
  }
  public function html()
  {
    return $this->shutdown_on_logout() . $this->source();
  }

  private function shutdown_on_logout()
  {
    return <<<HTML
<script data-cfasync="false">
  document.onreadystatechange = function () {
    if (document.readyState == "complete") {
      var logout_link = document.querySelectorAll('a[href*="wp-login.php?action=logout"]');
      if (logout_link) {
        for(var i=0; i < logout_link.length; i++) {
          logout_link[i].addEventListener( "click", function() {
            InfosetChat('shutdown');
          });
        }
      }
    }
  };
</script>

HTML;
  }

  private function source()
  {
  	$snippet_json = $this->snippet_config->json();
    $api_key = $this->snippet_config->apiKey();

    return empty(json_decode($snippet_json))
    ? <<<HTML
      <!-- BEGIN INFOSET CHAT WIDGET -->
      <script type='text/javascript'>!function(){var t=window;if('function'!=typeof t.InfosetChat){var n=document,e=function(){e.c(arguments)};e.q=[],e.c=function(t){e.q.push(t)},t.InfosetChat=e;function a(){var t=n.createElement('script');t.type='text/javascript',t.async=!0,t.src='https://cdn.infoset.app/chat/icw.js';var e=n.getElementsByTagName('script')[0];e.parentNode.insertBefore(t,e)}t.attachEvent?t.attachEvent('onload',a):t.addEventListener('load',a,!1)}}();
      InfosetChat('boot', {
        widget: { apiKey: '$api_key' },
      });
      </script>
      <!-- END INFOSET CHAT WIDGET -->
    HTML
    : <<<HTML
    <!-- BEGIN INFOSET CHAT WIDGET -->
    <script type='text/javascript'>!function(){var t=window;if('function'!=typeof t.InfosetChat){var n=document,e=function(){e.c(arguments)};e.q=[],e.c=function(t){e.q.push(t)},t.InfosetChat=e;function a(){var t=n.createElement('script');t.type='text/javascript',t.async=!0,t.src='https://cdn.infoset.app/chat/icw.js';var e=n.getElementsByTagName('script')[0];e.parentNode.insertBefore(t,e)}t.attachEvent?t.attachEvent('onload',a):t.addEventListener('load',a,!1)}}();
    InfosetChat('boot', {
      widget: { apiKey: '$api_key' },
      visitor: $snippet_json
    });
    </script>
    <!-- END INFOSET CHAT WIDGET -->
    HTML;
  }
}

class InfosetSnippetConfig
{
	private $config;
  private $api_key = NULL;
  private $private_key = NULL;
  private $wordpress_user = NULL;

  public function __construct($api_key = NULL, $private_key = NULL, $wordpress_user = NULL) {
    $this->config = array();
    $this->api_key = $api_key;
    $this->private_key = $private_key;
    $this->validateKeys(array("api_key" => $this->api_key, "private_key" => $this->private_key));
    $this->wordpress_user = $wordpress_user;
  }

  public function json() {
    return json_encode(apply_filters("infoset_settings", $this->configData()));
  }

  public function apiKey() {
    return $this->api_key;
  }

  private function configData()
  {
    $user = new InfosetVisitor($this->wordpress_user, $this->config);
    $config = $user->buildConfig();
    $idVerificationCalculator = new InfosetIdVerificationCalculator($config, $this->private_key);
    $result = array_merge($config, $idVerificationCalculator->idVerificationComponent());
    return $result;
  }

  private function validateKeys($key_array)
  {
    if (!array_key_exists("api_key", $key_array) || !array_key_exists("private_key", $key_array)) {
      throw new Exception("api_key and private_key are required");
    }
  }
}

class InfosetVisitor
{
	private $wordpress_user = NULL;
  private $config = array();

  public function __construct($wordpress_user, $config) {
    $this->wordpress_user = $wordpress_user;
    $this->config = $config;
  }

  public function buildConfig() {
    if (empty($this->wordpress_user)) {
      return $this->config;
    }

    if (!empty($this->wordpress_user->ID)) {
      $this->config["id"] = esc_js($this->wordpress_user->ID);
    }

    if (!empty($this->wordpress_user->user_email)) {
      $this->config["email"] = esc_js($this->wordpress_user->user_email);
    }

    if(!empty($this->wordpress_user->user_firstname)) {
     $this->config["firstName"] = esc_js($this->wordpress_user->user_firstname); 

    } else if(!empty($this->wordpress_user->display_name)) {
      $this->config["firstName"] = esc_js($this->wordpress_user->display_name);
    }

    if (!empty($this->wordpress_user->user_lastname)) {
      $this->config["lastName"] = esc_js($this->wordpress_user->user_lastname);
    }

    return $this->config;
  }
}

class InfosetValidator
{
	private $inputs = array();
  private $validation;

  public function __construct($inputs, $validation) {
    $this->input = $inputs;
    $this->validation = $validation;
  }

  public function validApiKey() {
    return $this->validate($this->input["api_key"]);
  }

  public function validPrivateKey() {
    return $this->validate($this->input["private_key"]);
  }

  private function validate($x) {
    return call_user_func($this->validation, $x);
  }
}

if (!defined('ABSPATH')) exit;

function add_infoset_snippet()
{
  $options = get_option('infoset');
  $snippet_settings = new InfosetSnippetConfig(
    esc_js($options['api_key']),
    esc_js($options['private_key']),
    wp_get_current_user()
  );
  $snippet = new InfosetSnippet($snippet_settings);
  echo $snippet->html();
}

function add_infoset_settings_page()
{
  add_options_page(
    'Infoset Settings',
    'Infoset',
    'manage_options',
    'infoset',
    'render_infoset_options_page'
  );
}

function render_infoset_options_page()
{
  if (!current_user_can('manage_options')) {
    wp_die('You are not authorized to access Infoset settings');
  }

  $options = get_option('infoset');
  $settings_page = new InfosetSettingsPage(
    array("api_key" => $options['api_key'], "private_key" => $options['private_key'])
  );
  echo $settings_page->htmlUnclosed();
  wp_nonce_field('infoset-update');
  echo $settings_page->htmlClosed();
}

function infoset_settings() {
  register_setting('infoset', 'infoset');
  if (isset($_GET['state']) && wp_verify_nonce($_GET[ 'state'], "infoset-auth") && current_user_can('manage_options') && isset($_GET['api_key']) && isset($_GET['private_key'])) {
    $validator = new InfosetValidator($_GET, function($x) { return wp_kses(trim($x), array()); });
    update_option("infoset",
      array("api_key" => $validator->validApiKey(), "private_key" => $validator->validPrivateKey())
    );

    $redirect_to = 'options-general.php?page=infoset&authenticated=1';
    wp_safe_redirect(admin_url($redirect_to));
  }
  if (current_user_can('manage_options') && isset($_POST['api_key']) && isset($_POST[ '_wpnonce']) && wp_verify_nonce($_POST[ '_wpnonce'], 'infoset-update')) {
      $options = array();
      $options["api_key"] = sanitize_text_input($_POST['api_key']);
      $options["private_key"] = sanitize_text_input($_POST['private_key']);
      update_option("infoset", $options);
      wp_safe_redirect(admin_url('options-general.php?page=infoset&saved=1'));
  }
}

function infosetRequiredScriptsStyles() {
  wp_enqueue_script('jquery');
  wp_enqueue_style('styles', plugin_dir_url(__FILE__)."styles/styles.css");
}

add_action('admin_enqueue_scripts', 'infosetRequiredScriptsStyles');
add_action('wp_footer', 'add_infoset_snippet');
add_action('admin_menu', 'add_infoset_settings_page');
add_action('network_admin_menu', 'add_infoset_settings_page');
add_action('admin_init', 'infoset_settings');

