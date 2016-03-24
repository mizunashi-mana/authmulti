<?php
/** must be run within Dokuwiki */
if (!defined('DOKU_INC')) {
  die();
}

require_once(DOKU_PLUGIN.'authmulti/util.php');

/**
 * Multiple authentication backend
 *
 * @author     Mizunashi Mana <mizunashi_mana@mma.club.uec.ac.jp>
 */
class auth_plugin_authmulti extends DokuWiki_Auth_Plugin {
  /** @var array auth plugins cache */
  protected $auth_plugins = array();

  /** @var bool is auth plain enabled */
  protected $use_authplain = null;

  /**
   * parse authentications string to array
   *
   * @param string $auths
   * @return array
   */
  public static function parseAuths($auths) {
    $result = array_map('trim', explode(';', $auths));
    return $result;
  }

  /**
   * get use authentications from config
   *
   * @return array authentications
   */
  protected function getUseAuths() {
    return $this::parseAuths($this->getConf('auths'));
  }

  /**
   * Constructor
   */
  public function __construct() {
    parent::__construct();

    // parse authentications configure
    $_plugins = $this->getUseAuths();
    if (!isAuthPluginsEnabled($_plugins)) {
      $this->success = false;
      return;
    }
    $this->auth_plugins = array_map('loadAuthPlugin', $_plugins);

    foreach ($this->auth_plugins as $auth_plugin) {
      if(!$auth_plugin || $auth_plugin->success == false) {
        $this->success = false;
        return;
      }
    }
    $this->success = true;

    $this->cando['getUsers']     = true;
    $this->cando['getUserCount'] = true;
    $this->cando['delUser']      = true;
    $this->cando['modLogin']     = true;
    $this->cando['modPass']      = true;
    $this->cando['modName']      = true;
    $this->cando['modMail']      = true;
    $this->cando['modGroups']    = true;
    $authplain_key = array_search('authplain', $_plugins);
    if ($authplain_key != false) {
      $this->use_authplain = $this->auth_plugins[$authplain_key];
      $this->cando['addUser'] = $this->use_authplain->cando['addUser'];
    }

  }

  /**
   * Check user+password
   *
   * @param string $user
   * @param string $pass
   * @return bool
   */
  public function checkPass($user, $pass) {
    foreach ($this->auth_plugins as $auth_plugin) {
      if ($auth_plugin->checkPass($user, $pass)) {
        return true;
      }
    }

    return false;
  }

  /**
   * Return user info
   *
   * @param string $user
   * @param bool   $requireGroups (optional)
   * @return array|false
   */
  public function getUserData($user, $requireGroups=true) {
    foreach ($this->auth_plugins as $auth_plugin) {
      $res = $auth_plugin->getUserData($user, $requireGroups);
      if ($res != false) {
        return $res;
      }
    }

    return false;
  }

  /**
   * Create a new User
   *
   * Returns false if the user already exists, null when an error
   * occurred and true if everything went well.
   *
   * This uses authplain's.
   *
   * @param string $user
   * @param string $pass
   * @param string $name
   * @param string $mail
   * @param array  $grps
   * @return bool|null|string
   */
  public function createUser($user, $pass, $name, $mail, $grps = null) {
    // user mustn't already exist
    if ($this->getUserData($user) !== false) {
      msg($this->getLang('userexists'), -1);
      return false;
    }

    // must enable authplain
    if ($this->use_authplain == null) {
      msg($this->getLang('authplainfail'), -1);
      return null;
    }

    return $this
      ->use_authplain
      ->createUser($user, $pass, $name, $mail, $grps)
      ;
  }

  /**
   * Modify user data
   *
   * @param string $user
   * @param array  $changes
   * @return bool
   */
  public function modifyUser($user, $changes) {
    if(!is_array($changes) || !count($changes)) return true;

    foreach ($this->auth_plugins as $auth_plugin) {
      if ($auth_plugin->getUserData($user) != false) {
        return $auth_plugin->modifyUser($user, $changes);
      }
    }

    msg($this->getLang('usernotexists'), -1);
    return false;
  }

  /**
   * Remove one or more users from the list of registered users
   *
   * @param array $users
   * @return int
   */
  public function deleteUsers($users) {
    if(!is_array($users) || empty($users)) return 0;

    $count = 0;
    $tusers = $users;

    foreach ($this->auth_plugins as $auth_plugin) {
      $targets = array();
      foreach ($tusers as $tuser) {
        if ($auth_plugin->getUserData($tuser) != false) {
          $targets[] = $tuser;
        }
      }
      $tusers = array_diff($tusers, $targets);

      if ($this->cando['delUser']) {
        $count += $auth_plugin->deleteUsers($targets);
      } else {
        msg($this->getLang('notsupport'), -1);
      }
    }

    return $count;
  }

}
