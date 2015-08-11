<?php

/*
Plugin Name: Photo Gallery for Lightroom
Description: Photo Gallery Extension for WP/LR Sync.
Version: 0.1.0
Author: Jordy Meow
Author URI: http://www.meow.fr
*/

class WPLR_Extension_PhotoGallery {

  public function __construct() {

    // Init
    add_filter( 'wplr_extensions', array( $this, 'extensions' ), 10, 1 );

    // Folder
    add_action( 'wplr_create_folder', array( $this, 'create_folder' ), 10, 3 );
    add_action( 'wplr_update_folder', array( $this, 'update_folder' ), 10, 2 );
    add_action( "wplr_move_folder", array( $this, 'move_folder' ), 10, 3 );
    add_action( "wplr_remove_folder", array( $this, 'remove_folder' ), 10, 1 );

    // Collection
    add_action( 'wplr_create_collection', array( $this, 'create_collection' ), 10, 3 );
    add_action( 'wplr_update_collection', array( $this, 'update_collection' ), 10, 2 );
    add_action( "wplr_move_collection", array( $this, 'move_collection' ), 10, 3 );
    add_action( "wplr_remove_collection", array( $this, 'remove_collection' ), 10, 1 );

    // Media
    add_action( "wplr_add_media_to_collection", array( $this, 'add_media_to_collection' ), 10, 2 );
    add_action( "wplr_remove_media_from_collection", array( $this, 'remove_media_from_collection' ), 10, 2 );
    add_action( "wplr_remove_media", array( $this, 'remove_media' ), 10, 1 );

    // Not used
    //add_action( 'wplr_reset', array( $this, 'reset' ), 10, 0 );
  }

  function extensions( $extensions ) {
    array_push( $extensions, 'Photo Gallery' );
    return $extensions;
  }

  // Created a new collection (ID $collectionId).
  // Placed in the folder $inFolderId, or in the root if empty.
  function create_collection( $collectionId, $inFolderId, $collection ) {
    global $wpdb, $wplr;

    // Exists already?
    if ( $wplr->get_meta( 'bwg_gallery_id', $collectionId ) )
      return;

    $bwg_gallery = $wpdb->prefix . "bwg_gallery";
    $wpdb->insert( $bwg_gallery,
      array(
        'name' => $collection['name'],
        'slug' => sanitize_title( $collection['name'] ),
        'author' => get_current_user_id(),
        'published' => 1
      )
    );
    $galleryId = $wpdb->insert_id;
    $wplr->set_meta( 'bwg_gallery_id', $collectionId, $galleryId );
    if ( !empty( $inFolderId ) ) {
      $inAlbumId = $wplr->get_meta( 'bwg_album_id', $inFolderId );
      $bwg_album_gallery = $wpdb->prefix . "bwg_album_gallery";
      $wpdb->insert( $bwg_album_gallery,
        array(
          'album_id' => $inAlbumId,
          'is_album' => 0,
          'alb_gal_id' => $galleryId,
          'order' => 1
        )
      );
    }
  }

  // Created a new folder (ID $folderId).
  // Placed in the folder $inFolderId, or in the root if empty.
  function create_folder( $folderId, $inFolderId, $folder ) {
    global $wpdb, $wplr;

    // Exists already?
    if ( $wplr->get_meta( 'bwg_album_id', $folderId ) )
      return;

    $bwg_album = $wpdb->prefix . "bwg_album";
    $wpdb->insert( $bwg_album,
      array(
        'name' => $folder['name'],
        'slug' => sanitize_title( $folder['name'] ),
        'author' => get_current_user_id(),
        'published' => 1
      )
    );
    $albumId = $wpdb->insert_id;
    $wplr->set_meta( 'bwg_album_id', $folderId, $albumId );
    if ( !empty( $inFolderId ) ) {
      $inAlbumId = $wplr->get_meta( 'bwg_album_id', $inFolderId );
      $bwg_album_gallery = $wpdb->prefix . "bwg_album_gallery";
      $wpdb->insert( $bwg_album_gallery,
        array(
          'album_id' => $albumId,
          'is_album' => 1,
          'alb_gal_id' => $inAlbumId,
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

  // The folder was deleted.
  function remove_collection( $collectionId ) {
    global $wpdb, $wplr;
    $tbl_gallery = $wpdb->prefix . 'bwg_gallery';
    $tbl_album_gallery = $wpdb->prefix . 'bwg_album_gallery';
    $galleryId = $wplr->get_meta( 'bwg_gallery_id', $collectionId );
    $wpdb->delete( $tbl_gallery, array( 'id' => $galleryId ) );
    $wpdb->delete( $tbl_album_gallery, array( 'alb_gal_id' => $galleryId ) );
    $wplr->delete_meta( 'bwg_gallery_id', $collectionId );
  }

  // The collection was deleted.
  function remove_folder( $folderId ) {
    global $wpdb, $wplr;
    $tbl_album = $wpdb->prefix . 'bwg_album';
    $tbl_album_gallery = $wpdb->prefix . 'bwg_album_gallery';
    $albumId = $wplr->get_meta( 'bwg_album_id', $folderId );
    $wpdb->delete( $tbl_album, array( 'id' => $albumId ) );
    $wpdb->delete( $tbl_album_gallery, array( 'album_id' => $albumId ) );
    $wplr->delete_meta( 'bwg_album_id', $folderId );
  }

}

new WPLR_Extension_PhotoGallery;

?>
