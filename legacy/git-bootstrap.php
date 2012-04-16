<?php

//
// Rewrite PHP include_path using this git checkout in
// preference to the normal settings.
//

$repo_dir = dirname( __DIR__ );

$include_path = explode( ':', ini_get( 'include_path' ) );
$include_path = array_flip( $include_path );

// we will manually handle these
unset( $include_path['/web/app/back-compat/legacy'] );
unset( $include_path['/web/app/back-compat/external'] );
unset( $include_path['/web/app/plymouth-webapp/legacy'] );
unset( $include_path['/web/app/plymouth-webapp/external'] );
unset( $include_path['/web/includes_psu'] );
unset( $include_path['/web/includes_external'] );

// we'll re-add this to the front
unset( $include_path['.'] );

$include_path = array_flip( $include_path );

array_unshift( $include_path, $repo_dir . '/external' );
array_unshift( $include_path, $repo_dir . '/legacy' );
array_unshift( $include_path, '.' );

$include_path = implode( ':', $include_path );

ini_set( 'include_path', $include_path );

unset( $repo_dir, $include_path );
