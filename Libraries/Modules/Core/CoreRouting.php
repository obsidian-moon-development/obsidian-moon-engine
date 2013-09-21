<?php
/**
 * Obsidian Moon Engine by Dark Prospect Games
 *
 * An Open Source, Lightweight and 100% Modular Framework in PHP
 *
 * PHP version 5
 *
 * @category  obsidian-moon-engine-core
 * @package   obsidian-moon-engine-core
 * @author    Alfonso E Martinez, III <admin@darkprospect.net>
 * @copyright 2011-2013 Dark Prospect Games, LLC
 * @license   BSD https://darkprospect.net/BSD-License.txt
 * @link       https://github.com/DarkProspectGames/obsidian-moon-engine-core
 */
/**
 * Module ObsidianMoonCore\CoreRouting
 *
 * This module will handle the routing for the application.
 *
 * @category  obsidian-moon-engine-core
 * @package   CoreRouting
 * @author    Alfonso E Martinez, III <admin@darkprospect.net>
 * @copyright 2011-2013 Dark Prospect Games, LLC
 * @license   BSD https://darkprospect.net/BSD-License.txt
 * @link       https://github.com/DarkProspectGames/obsidian-moon-engine-core
 */
class CoreRouting extends Module
{

    /**
     * @var mixed
     */
    protected $primary;

    /**
     * @var mixed
     */
    protected $secondary;

    /**
     * @var mixed
     */
    protected $params = array();

    /**
     * @var mixed
     */
    protected $control;

    /**
     * Constructor class for a standard module.
     *
     * This will load the routing information, it will load the primary route first
     * and then the secondary if set. Below are examples of what is called:
     *
     * - `/main/` loads `libraries/Controls/main.php` with class and method `control_main::index()`
     * - `/main/about/` loads `libraries/Controls/main.php` with class and method `control_main::about()`
     *
     * @param Core  $core    The reference to the Core Class.
     * @param mixed $configs The configurations being passed to the module.
     */
    public function __construct(Core $core, $configs = null)
    {
        parent::__construct($core, $configs);

        // Get the URI from the system and process it into $this->primary and $this->params.
        $filter = array('/\?.*$/i');
        if (isset($this->core->conf_subdir)) {
            $filter[] = "/{$this->core->conf_subdir}\//i";
        }

        $uri           = explode('/', trim(preg_replace($filter, '', $_SERVER['REQUEST_URI']), '/'));
        $this->primary = ucfirst($uri[0]);
        $this->params  = array_slice($uri, 1);

        // If there is a second parameter we want to pull that and use it.
        if (isset($this->params[0])) {
            $this->secondary = $this->params[0];
            $this->params    = array_slice($this->params, 1);
        }
    }

    /**
     * Runs the Control that we need
     *
     * This will create and run all of the methods for a Control
     *
     * @return void
     */
    public function start()
    {
        if (file_exists("{$this->core->conf_libs}/Controls/{$this->primary}.php")) {
            include "{$this->core->conf_libs}/Controls/{$this->primary}.php";
        } else if ($this->primary == '') {
            include "{$this->core->conf_libs}/Controls/{$this->core->conf_defcon}.php";
            $this->primary = $this->core->conf_defcon;
        } else {
            include "{$this->core->conf_libs}/Controls/Error404.php";
            $this->primary = 'Error404';
        }

        $control_name = "Control{$this->primary}";
        if (class_exists($control_name)) {
            // If the control exists we pass core and params to it.
            $this->control = new $control_name($this->core, $this->params);
        }

        if (method_exists($this->control, 'start')) {
            $this->control->start();
        }

        if (isset($this->secondary) && method_exists($this->control, $this->secondary)) {
            call_user_func_array(array($this->control, $this->secondary), $this->params);
        } else {
            $this->control->index();
        }
    }

}