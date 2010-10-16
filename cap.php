<?php

require_once('BFCollections/BFArray.php');
require_once('BFCollections/Box.php');
require_once('super_closure/SerializableClosure.php');

/**
 * CAP class.
 * 
 * The core of the CAP system with various operations common across CAP apps.
 */
class CAP {
    // the current scope (i.e. form) of the CAP system
    static $scope;
    // the results of the current scope
    static $results;
    // all results
    static $__results;
    
    /**
     * register function.
     * 
     * Register the provided closure by serializing and placing it in the session.
     *
     * @static
     * @param Closure $closure
     * @param string $id (Default: NULL)
     * @return Box[string] The boxed session id of the registered closure or a Failure indicating why the closure wasn't registered.
     */
    static function register(Closure $closure, $id = NULL) {
        if (is_null(self::$scope)) {
            self::setScope(self::makeID());
        }
        $id = is_null($id) ? self::makeID() : $id;
        try {
            $sc = new SerializableClosure($closure);
            $_SESSION[self::$scope][$id] = array('type' => 'closure', 'function' => $sc->serialize());
            return new Full($id);
        } catch (InvalidArgumentException $e) {
            return new Failure('Parameter is not callable.', new Full($e));
        } catch (RuntimeException $e) {
            return new Failure('Cannot serialize closure via reflection.', new Full($e));
        }
    }
    
    /**
     * setScope function.
     * 
     * A simple helper message for setting the current scope (i.e. form). Perhaps in the future this will do more.
     *
     * @static
     * @param string $id
     * @return void
     */
    static function setScope($id) {
        self::$scope = $id;
    }
    
    /**
     * makeID function.
     * 
     * Make a unique id. Used to associate form elements with (serialized) callback functions.
     * @static
     * @return string
     */
    static function makeID() {
        return uniqid();
    }
}

// start our session and go through the data sent to us, seeing if we should call any of the registered callbacks
session_start();
CAP::$__results = new BFArray();
$k = 0;
foreach ($_REQUEST as $scope => $values) {
    CAP::$results = new BFArray();
    foreach ($values as $id => $value) {
        if (array_key_exists($id, $_SESSION[$scope]) && $_SESSION[$scope][$id]['type'] == 'closure') {
            $b = new SerializableClosure(function() {});
            try {
                $b->unserialize($_SESSION[$scope][$id]['function']);
                // call the unserialized closure with the appropriate input
                CAP::$__results[$scope][$id] = $b($value);
                CAP::$results[$id] = CAP::$__results[$scope][$id];
            } catch (Exception $e) {
            }
            // keep the session clean
            unset($_SESSION[$scope][$id]);
            $k++;
        }
    }
    // keep the session clean
    unset($_SESSION[$scope]);
}
unset($k);
CAP::$results = NULL;
CAP::$__results = NULL;

?>