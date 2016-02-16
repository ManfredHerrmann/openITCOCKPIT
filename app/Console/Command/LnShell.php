<?php

/**
 * Create symlink plugins and themes webroot to APP/webroot/
 *
 * updated for CakePHP 2.x by sams
 * PHP versions 5
 *
 * Copyright 2011, nojimage (http://php-tips.com/)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @version   1.0
 * @author    nojimage <nojimage at gmail.com>
 * @copyright 2011 nojimage (http://php-tips.com/)
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link    　http://php-tips.com/
 *
 * =====
 * Usage:
 *
 * in console
 *
 *  cake ln all
 *
 *  or
 *
 *  cake ln search
 *
 *  or
 *
 *  cake ln plugin {plugin_name}
 *
 *  or
 *
 *  cake ln theme {theme_name}
 *
 */

App::uses('Folder', 'Utility');
class LnShell extends AppShell {

    public $uses = array();

    public function main() {
        $this->help();
    }

    public function help() {
        $head = "-----------------------------------------------\n";
        $head .= "Usage: cake ln <command> <params1> <params2>...\n";
        $head .= "-----------------------------------------------\n";
        $head .= "Commands:\n";

        $commands = array(
            'all' => "all\n" .
            "\t" . "create symlink app plugins and themes webroot to APP/webroot/.\n",
            'plugin' => "plugin <plugin_name>\n" .
            "\t" . "create symlink plugin webroot to APP/webroot/{plugin_name}.\n",
            'theme' => "theme <theme_name>\n" .
            "\t" . "create symlink theme webroot to APP/webroot/theme/{theme_name}.\n",
            'search' => "search\n" .
            "\t" . "list up plugins and themes webroot.\n",
            'help' => "help [<command>]\n" .
            "\t" . "Displays this help message, or a message on a specific command.",
        );

        $this->out($head);

        if (!isset($this->args[0])) {

            foreach ($commands as $cmd) {
                $this->out("{$cmd}\n\n");
            }
        } elseif (isset($commands[strtolower($this->args[0])])) {

            $this->out($commands[strtolower($this->args[0])] . "\n\n");
        } else {

            $this->out(sprintf(__("Command '%s' not found", true), $this->args[0]));
        }
    }

    /**
     *
     */
    public function all() {
        foreach ($this->_searchPlugin() as $pluginName) {
            $this->out('plugin: ' . $pluginName . ' has webroot directory.');
            $this->_pluginSymlink($pluginName);
        }
        foreach ($this->_searchTheme() as $themeName) {
            $this->out('theme: ' . $themeName . ' has webroot directory.');
            $this->_themeSymlink($themeName);
        }
    }

    /**
     * 
     */
    public function search() {
        foreach ($this->_searchPlugin() as $pluginName) {
            $this->out('plugin: ' . $pluginName . ' has webroot directory.');
        }
        foreach ($this->_searchTheme() as $themeName) {
            $this->out('theme: ' . $themeName . ' has webroot directory.');
        }
    }

    /**
     * 
     */
    public function plugin() {

        if (empty($this->args)) {
            $this->out('please input plugin name!');
            return;
        }

        $pluginName = Inflector::camilize($this->args[0]);

        $this->_pluginSymlink($pluginName);
    }

    /**
     *
     */
    public function theme() {

        if (empty($this->args)) {
            $this->out('please input theme name!');
            return;
        }

        $themeName = Inflector::camilize($this->args[0]);

        $this->_themeSymlink($themeName);
    }

    /**
     * create pluin symlink
     *
     * @param string $pluginName
     */
    protected function _pluginSymlink($pluginName) {
        // dist path check
        $link = WWW_ROOT . Inflector::underscore($pluginName);

        if (file_exists($link)) {
            $this->out('distination already exists: ' . $link);
            return;
        }

        $target = App::pluginPath($pluginName) . WEBROOT_DIR;
        $this->_symlink($target, $link);
    }

    /**
     * create theme symlink
     *
     * @param string $themeName
     */
    protected function _themeSymlink($themeName) {
        // dist path check
        $link = WWW_ROOT . 'theme' . DS . Inflector::underscore($themeName);

        if (file_exists($link)) {
            $this->out('distination already exists: ' . $link);
            return;
        }

        $target = App::themePath($themeName) . WEBROOT_DIR;
        $this->_symlink($target, $link);
    }

    /**
     * create symlink
     *
     * @param string $target
     * @param string $link
     */
    protected function _symlink($target, $link) {

        if (!file_exists($target) || file_exists($link)) {
            return;
        }

        $input = $this->in(sprintf('ln -s %s %s ?', $target, $link), array('y', 'n'), 'n');
        if (strtolower($input) !== 'y') {
            return;
        }
        $parent = str_replace(basename($link), '', $link);
        if (!file_exists($parent)) {
            $Folder = new Folder();
            $Folder->create($parent);
            $this->out('<info>making parent ' . "\n" .$parent.'</info>');
        }

        // create symlink
        if (symlink($target, $link)) {
            $this->out('<success>SUCCESS!</success>');
        } else {
            $this->out('<warning>FAILURE! target: ' .$target. ' link: ' .$link. '</warning>');
        }
    }

    /**
     * search all plugins
     *
     * @return array
     */
    protected function _searchPlugin() {
        $pluginPaths = App::path('Plugin');
        $Folder = new Folder();
        $plugins = array();
        foreach ($pluginPaths as $path) {
            $Folder->path = $path;
            list($pluginNames, $files) = $Folder->read();
            foreach ($pluginNames as $pluginName) {
                if (is_dir($path . $pluginName . DS . WEBROOT_DIR)) {
                    $plugins[] = $pluginName;
                }
            }
        }
        return $plugins;
    }

    /**
     * search all themes
     *
     * @return array
     */
    protected function _searchTheme() {
        $viewPaths = App::path('views');
        $Folder = new Folder();
        $themes = array();
        foreach ($viewPaths as $path) {
            $Folder->path = $path . 'Themed' . DS;
            list($themeNames, $files) = $Folder->read();
            foreach ($themeNames as $themeName) {
                if (is_dir($Folder->path . $themeName . DS . WEBROOT_DIR)) {
                    $themes[] = $themeName;
                }
            }
        }
        return $themes;
    }

}
