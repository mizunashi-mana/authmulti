<?php

/**
 * is array contained
 *
 * @param array $tarr target array
 * @param array ... compare array
 * @return array
 */
function array_contains($tarr, ...$carrs) {
  foreach ($carrs as $carr) {
    $_carr = array_intersect($tarr, $carr);

    if (count($carr) != count($_carr)) {
      return false;
    }
  }

  return true;
}

/**
 * get list of auth plugins
 *
 * @return array
 */
function getAuthPlugins() {
  global $plugin_controller;
  return $plugin_controller->getList('auth');
}

/**
 * check enabled plugins
 *
 * @param array $plugins
 * @return array
 */
function isAuthPluginsEnabled($plugins) {
  $enabled_plugins = getAuthPlugins();
  return array_contains($enabled_plugins, $plugins);
}

/**
 * load auth plugin from name string
 *
 * @param string $plugin
 * @return DokuWiki_Auth_Plugin
 */
function loadAuthPlugin($plugin) {
  global $plugin_controller;
  return $plugin_controller->load('auth', $plugin);
}

/**
 * map field value to access string
 *
 * @param string $field
 * @return string
 */
function getAccessString($field) {
  $maps = [
    'user' => 'modLogin',
    'pass' => 'modPass',
    'name' => 'modName',
    'mail' => 'modMail',
    'grps' => 'modGroups',
  ];
  return $maps[$field];
}