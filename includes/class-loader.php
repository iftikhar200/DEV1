<?php
/**
 * Register all actions and filters for the plugin
 *
 * @link       https://github.com/quiz-master-json
 * @since      1.0.0
 *
 * @package    Quiz_Master_JSON
 * @subpackage Quiz_Master_JSON/includes
 */

/**
 * Register all actions and filters for the plugin.
 *
 * Maintain a list of all hooks that are registered throughout
 * the plugin, and register them with the WordPress API. Call the
 * run function to execute the list of actions and filters.
 *
 * @package    Quiz_Master_JSON
 * @subpackage Quiz_Master_JSON/includes
 * @author     Quiz Master JSON Team
 */
class Quiz_Master_JSON_Loader {

    /**
     * The array of actions registered with WordPress.
     *
     * @since    1.0.0
     * @access   protected
     * @var      array    $actions    The actions registered with WordPress to fire when the plugin loads.
     */
    protected $actions;

    /**
     * The array of filters registered with WordPress.
     *
     * @since    1.0.0
     * @access   protected
     * @var      array    $filters    The filters registered with WordPress to fire when the plugin loads.
     */
    protected $filters;

    /**
     * Initialize the collections used to maintain the actions and filters.
     *
     * @since    1.0.0
     */
    public function __construct() {
        $this->actions = array();
        $this->filters = array();
    }

    /**
     * Add a new action to the collection to be registered with WordPress.
     *
     * @since    1.0.0
     * @param    string               $hook             The name of the WordPress action that is being registered.
     * @param    object               $component        A reference to the instance of the object on which the action is defined.
     * @param    string               $callback         The name of the function definition on the $component.
     * @param    int                  $priority         Optional. The priority at which the function should be fired. Default is 10.
     * @param    int                  $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1.
     */
    public function add_action($hook, $component, $callback, $priority = 10, $accepted_args = 1) {
        $this->actions = $this->add($this->actions, $hook, $component, $callback, $priority, $accepted_args);
    }

    /**
     * Add a new filter to the collection to be registered with WordPress.
     *
     * @since    1.0.0
     * @param    string               $hook             The name of the WordPress filter that is being registered.
     * @param    object               $component        A reference to the instance of the object on which the filter is defined.
     * @param    string               $callback         The name of the function definition on the $component.
     * @param    int                  $priority         Optional. The priority at which the function should be fired. Default is 10.
     * @param    int                  $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1
     */
    public function add_filter($hook, $component, $callback, $priority = 10, $accepted_args = 1) {
        $this->filters = $this->add($this->filters, $hook, $component, $callback, $priority, $accepted_args);
    }

    /**
     * A utility function that is used to register the actions and hooks into a single
     * collection.
     *
     * @since    1.0.0
     * @access   private
     * @param    array                $hooks            The collection of hooks that is being registered (that is, actions or filters).
     * @param    string               $hook             The name of the WordPress filter that is being registered.
     * @param    object               $component        A reference to the instance of the object on which the filter is defined.
     * @param    string               $callback         The name of the function definition on the $component.
     * @param    int                  $priority         The priority at which the function should be fired.
     * @param    int                  $accepted_args    The number of arguments that should be passed to the $callback.
     * @return   array                                  The collection of actions and filters registered with WordPress.
     */
    private function add($hooks, $hook, $component, $callback, $priority, $accepted_args) {
        $hooks[] = array(
            'hook'          => $hook,
            'component'     => $component,
            'callback'      => $callback,
            'priority'      => $priority,
            'accepted_args' => $accepted_args
        );

        return $hooks;
    }

    /**
     * Register the filters and actions with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        foreach ($this->filters as $hook) {
            add_filter($hook['hook'], array($hook['component'], $hook['callback']), $hook['priority'], $hook['accepted_args']);
        }

        foreach ($this->actions as $hook) {
            add_action($hook['hook'], array($hook['component'], $hook['callback']), $hook['priority'], $hook['accepted_args']);
        }
    }

    /**
     * Get all registered actions
     *
     * @since    1.0.0
     * @return   array
     */
    public function get_actions() {
        return $this->actions;
    }

    /**
     * Get all registered filters
     *
     * @since    1.0.0
     * @return   array
     */
    public function get_filters() {
        return $this->filters;
    }

    /**
     * Remove an action from the collection
     *
     * @since    1.0.0
     * @param    string    $hook        The hook name
     * @param    object    $component   The component object
     * @param    string    $callback    The callback function name
     * @return   bool                   True if removed, false otherwise
     */
    public function remove_action($hook, $component, $callback) {
        foreach ($this->actions as $key => $action) {
            if ($action['hook'] === $hook && 
                $action['component'] === $component && 
                $action['callback'] === $callback) {
                unset($this->actions[$key]);
                return true;
            }
        }
        return false;
    }

    /**
     * Remove a filter from the collection
     *
     * @since    1.0.0
     * @param    string    $hook        The hook name
     * @param    object    $component   The component object
     * @param    string    $callback    The callback function name
     * @return   bool                   True if removed, false otherwise
     */
    public function remove_filter($hook, $component, $callback) {
        foreach ($this->filters as $key => $filter) {
            if ($filter['hook'] === $hook && 
                $filter['component'] === $component && 
                $filter['callback'] === $callback) {
                unset($this->filters[$key]);
                return true;
            }
        }
        return false;
    }

    /**
     * Check if an action is registered
     *
     * @since    1.0.0
     * @param    string    $hook        The hook name
     * @param    object    $component   The component object
     * @param    string    $callback    The callback function name
     * @return   bool                   True if registered, false otherwise
     */
    public function has_action($hook, $component, $callback) {
        foreach ($this->actions as $action) {
            if ($action['hook'] === $hook && 
                $action['component'] === $component && 
                $action['callback'] === $callback) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if a filter is registered
     *
     * @since    1.0.0
     * @param    string    $hook        The hook name
     * @param    object    $component   The component object
     * @param    string    $callback    The callback function name
     * @return   bool                   True if registered, false otherwise
     */
    public function has_filter($hook, $component, $callback) {
        foreach ($this->filters as $filter) {
            if ($filter['hook'] === $hook && 
                $filter['component'] === $component && 
                $filter['callback'] === $callback) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get hooks count
     *
     * @since    1.0.0
     * @return   array
     */
    public function get_hooks_count() {
        return array(
            'actions' => count($this->actions),
            'filters' => count($this->filters),
            'total' => count($this->actions) + count($this->filters)
        );
    }

    /**
     * Debug function to list all registered hooks
     *
     * @since    1.0.0
     * @return   array
     */
    public function debug_hooks() {
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            return array();
        }

        $debug_info = array(
            'actions' => array(),
            'filters' => array()
        );

        foreach ($this->actions as $action) {
            $debug_info['actions'][] = array(
                'hook' => $action['hook'],
                'callback' => get_class($action['component']) . '::' . $action['callback'],
                'priority' => $action['priority'],
                'args' => $action['accepted_args']
            );
        }

        foreach ($this->filters as $filter) {
            $debug_info['filters'][] = array(
                'hook' => $filter['hook'],
                'callback' => get_class($filter['component']) . '::' . $filter['callback'],
                'priority' => $filter['priority'],
                'args' => $filter['accepted_args']
            );
        }

        return $debug_info;
    }
}