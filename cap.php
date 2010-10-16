<?php

// Requires Jeremy Lindblom's SuperClosure library
// http://github.com/jeremeamia/super_closure
require 'super_closure/SerializableClosure.php';

class CAP {
    static function register(Closure $closure) {
        $sc = new SerializableClosure($closure);
        $id = uniqid();
        $_SESSION[$id] = array('type' => 'closure', 'function' => $sc->serialize());
        return $id;
    }
}

session_start();

foreach ($_REQUEST as $id => $value) {
    if (array_key_exists($id, $_SESSION) && $_SESSION[$id]['type'] == 'closure') {
        $b = new SerializableClosure(function() {});
        try {
            $b->unserialize($_SESSION[$id]['function']);
            print $b($value);
        } catch (Exception $e) {
        }
        unset($_SESSION[$id]);
        unset($_REQUEST[$id]);
    }
}

?>