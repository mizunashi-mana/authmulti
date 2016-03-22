<?php
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

require_once(DOKU_PLUGIN.'authmulti/util.php');

/**
 * Multiple authentication backend
 *
 * @author     Mizunashi Mana <mizunashi_mana@mma.club.uec.ac.jp>
 */
class auth_plugin_authmulti extends DokuWiki_Auth_Plugin {
    /** @var array auth plugins cache */
    protected $_auth_plugins = array();
    
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
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        
        // parse authentications configure
        $__plugins = $this::parseAuths($this->getConf('auths'));
        if (!isAuthPluginsEnabled($__plugins)) {
          $this->$success = false;
          return;
        }
        $this->$_auth_plugins = array_map()
        
        $this->$success = true;
    }
    
}
