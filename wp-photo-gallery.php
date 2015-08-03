<?php

/*
Plugin Name: Photo Gallery for Lightroom (WP/LR Extension)
Description: Photo Gallery Extension for WP/LR Sync.
Version: 0.1.0
Author: Jordy Meow
Author URI: http://www.meow.fr
*/

class WPLR_Extension_PhotoGallery {

  public function __construct() {

    // Reset
    add_action( 'wplr_reset', array( $this, 'reset' ), 10, 0 );

    // Create / Update
    add_action( 'wplr_create_folder', array( $this, 'create_folder' ), 10, 3 );
    add_action( 'wplr_update_folder', array( $this, 'update_folder' ), 10, 2 );
    add_action( 'wplr_create_collection', array( $this, 'create_collection' ), 10, 3 );
    add_action( 'wplr_update_collection', array( $this, 'update_collection' ), 10, 2 );

    // Move
    add_action( "wplr_move_folder", array( $this, 'move_collection' ), 10, 3 );
    add_action( "wplr_move_collection", array( $this, 'move_collection' ), 10, 3 );

    // Media
    add_action( "wplr_add_media_to_collection", array( $this, 'add_media_to_collection' ), 10, 2 );
    add_action( "wplr_remove_media_from_collection", array( $this, 'remove_media_from_collection' ), 10, 2 );
    add_action( "wplr_remove_media", array( $this, 'remove_media' ), 10, 1 );
    add_action( "wplr_remove_collection", array( $this, 'remove_collection' ), 10, 1 );
  }

  // Plugins are asked to reset their support of folders/collections and the media in them.
  // This is triggered by the Reset button in the Settings -> Media -> WP/LR Sync -> Maintenance.
  function reset() {
    global $wpdb;
    $bwg_gallery = $wpdb->prefix . "bwg_gallery";
    $bwg_album = $wpdb->prefix . "bwg_album";
    $bwg_album_gallery = $wpdb->prefix . "bwg_album_gallery";
    $bwg_image = $wpdb->prefix . "bwg_image";
    $wpdb->query( "TRUNCATE $bwg_gallery" );
    $wpdb->query( "TRUNCATE $bwg_album" );
    $wpdb->query( "TRUNCATE $bwg_album_gallery" );
    $wpdb->query( "TRUNCATE $bwg_image" );
  }

  // Created a new collection (ID $collectionId).
  // Placed in the folder $inFolderId, or in the root if empty.
  function create_collection( $collectionId, $inFolderId, $collection ) {
    global $wpdb;
    $bwg_gallery = $wpdb->prefix . "bwg_gallery";
    $wpdb->insert( $bwg_gallery,
      array(
        'id' => $collectionId,
        'name' => $collection['name'],
        'slug' => sanitize_title( $collection['name'] ),
        'author' => get_current_user_id(),
        'published' => 1
      )
    );
    if ( !empty( $inFolderId ) ) {
      $bwg_album_gallery = $wpdb->prefix . "bwg_album_gallery";
      $wpdb->insert( $bwg_album_gallery,
        array(
          'album_id' => $inFolderId,
          'is_album' => 0,
          'alb_gal_id' => $collectionId,
          'order' => 1
        )
      );
    }
  }

  // Created a new folder (ID $folderId).
  // Placed in the folder $inFolderId, or in the root if empty.
  function create_folder( $folderId, $inFolderId, $folder ) {
    global $wpdb;
    $bwg_album = $wpdb->prefix . "bwg_album";
    $wpdb->insert( $bwg_album,
      array(
        'id' => $folderId,
        'name' => $folder['name'],
        'slug' => sanitize_title( $folder['name'] ),
        'author' => get_current_user_id(),
        'published' => 1
      )
    );
    if ( !empty( $inFolderId ) ) {
      $bwg_album_gallery = $wpdb->prefix . "bwg_album_gallery";
      $wpdb->insert( $bwg_album_gallery,
        array(
          'album_id' => $folderId,
          'is_album' => 1,
          'alb_gal_id' => $inFolderId,
          'order' => 1
        )
      );
    }
  }

  // Updated the collection with new information.
  // Currently, that would be only its name.
  function update_collection( $collectionId, $collection ) {
  }

  // Updated the folder with new information.
  // Currently, that would be only its name.
  function update_folder( $folderId, $folder ) {
  }

  // Moved the collection under another folder.
  // If the folder is empty, then it is the root.
  function move_collection( $collectionId, $folderId, $previousFolderId ) {
  }

  // Added meta to a collection.
  // The $mediaId is actually the WordPress Post/Attachment ID.
  function add_media_to_collection( $mediaId, $collectionId ) {
    // $post = get_post( $mediaId );
    // global $wpdb;
    // $bwg_image = $wpdb->prefix . "bwg_image";
    // $wpdb->insert( $bwg_image,
    //   array(
    //     'gallery_id' => $collectionId,
    //     'author' => get_current_user_id()
    //   )
    // );
  }

  // Remove media from the collection.
  function remove_media_from_collection( $mediaId, $collectionId ) {
  }

  // The media was physically deleted.
  function remove_media( $mediaId ) {
  }

  // The collection was deleted.
  function remove_collection( $collectionId ) {
    global $wpdb;
    $bwg_album = $wpdb->prefix . "bwg_album";
    $bwg_album_gallery = $wpdb->prefix . "bwg_album_gallery";
    $wpdb->delete( $bwg_album, array( 'id' => $collectionId ) );
    $wpdb->delete( $bwg_album_gallery, array( 'album_id' => $collectionId ) );
    $wpdb->delete( $bwg_album_gallery, array( 'alb_gal_id' => $collectionId ) );
  }

}

new WPLR_Extension_PhotoGallery;

?>
