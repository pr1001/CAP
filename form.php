<?php

require_once('BFCollections/BFArray.php');
require_once('cap.php');

/**
 * Form class.
 * 
 * A class used to construct forms that can have callbacks tied to their fields.
 */
class Form {
    protected $elements;
    protected $allowedMethods;
    
    /**
     * __construct function.
     * 
     * Create a new form.
     *
     * @param string $action
     * @param Closure $closure. (default: NULL)
     * @param string $method. (default: 'POST')
     * @return void
     */
    function __construct($action, Closure $closure = NULL, $method = 'POST') {
        $this->elements = new BFArray();
        $this->allowedMethods = new BFArray('DELETE', 'GET', 'POST', 'PUT');
        // register the basics
        $this->action = $action;
        $this->method = $this->allowedMethods->contains(strtoupper($method)) ? $method : 'POST';
        $this->name = CAP::makeID();
        // set our form scope
        CAP::setScope($this->name);
        // register the form closure
        $this->makeElement('hidden', $this->name, $closure, $this->name);
    }
    
    /**
     * text function.
     * 
     * A text input field.
     *
     * @param string $value. (default: NULL)
     * @param Closure $closure. (default: NULL)
     * @return string The field id, useful for instance for looking up the closure's result after processing.
     */
    function text($value = NULL, Closure $closure = NULL) {
        return $this->makeElement('text', $value, $closure);
    }
    
    /**
     * submit function.
     * 
     * A submit input field.
     *
     * @param string $value. (default: NULL)
     * @param Closure $closure. (default: NULL)
     * @return string The field id, useful for instance for looking up the closure's result after processing.
     */
    function submit($value = NULL, Closure $closure = NULL) {
        return $this->makeElement('submit', $value, $closure);
    }
    
    /**
     * toHTML function.
     * 
     * Convert the form instance into an HTML string with all registered input fields.
     *
     * @return string
     */
    function toHTML() {
        $this->setCallbacks();
        $s = sprintf('<form name="%s" action="%s" method="%s">', $this->name, $this->action, $this->method) . "\n";
        $form_id = $this->name;
        $s .= $this->elements->reduceLeft('', function($a, $b) use ($form_id) {
            return $a . sprintf('  <input name="%s[%s]" type="%s" value="%s" />', $form_id, $b['name'], $b['type'], $b['value']) . "\n";
        });
        $s .= "</form>\n";
        return $s;
    }
    
    /**
     * makeElement function.
     * 
     * Take the provided element information and register it in the form's element list.
     *
     * @access protected
     * @param string $type
     * @param string $value. (default: NULL)
     * @param Closure $closure. (default: NULL)
     * @param string $name. (default: NULL)
     * @return string The field id, useful for instance for looking up the closure's result after processing.
     */
    protected function makeElement($type, $value = NULL, Closure $closure = NULL, $name = NULL) {
        $name = is_null($name) ? CAP::makeID() : $name;
        $this->elements[$name] = new BFArray(array('type' => $type, 'value' => $value, 'closure' => $closure, 'name' => $name));
        return $name;
    }

    /**
     * setCallbacks function.
     * 
     * Loop through all the elements and register all provided closures with the underlying CAP system.
     *
     * @access protected
     * @return void
     */
    protected function setCallbacks() {
        foreach ($this->elements as $element) {
            if (array_key_exists('closure', $element->toArray()) && !is_null($element['closure'])) {
                CAP::register($element['closure'], $element['name']);
            }
        }
    }
}

?>