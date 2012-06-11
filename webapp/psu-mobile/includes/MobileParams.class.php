<?php

/*
 * A parameters class for namespacing parameters and saving them to a session
 * Thanks @abackstrom
 */

class MobileParams implements ArrayAccess {
     const SESSION_KEY = 'webapp-psu-mobile';

     public function offsetExists( $key ) { 
          return isset( $_SESSION[ self::SESSION_KEY ][ $this->key($key) ] );
     }   

     public function offsetGet( $key ) { 
          return $_SESSION[ self::SESSION_KEY ][ $this->key($key) ];
     }   

     public function offsetSet( $key, $value ) { 
          if( !isset( $_SESSION[ self::SESSION_KEY ] ) ) { 
               $_SESSION[ self::SESSION_KEY ] = array();
          }   

          $_SESSION[ self::SESSION_KEY ][ $this->key($key) ] = $value;
     }   

     public function offsetUnset( $key ) { 
          unset( $_SESSION[ self::SESSION_KEY ][ $this->key($key) ] );
     }   

     public function key( $key ) { 
          return $key;
     }   
}
