<?php
namespace GoogleDriveCLI;
class ContentsList {

  public static function list_array($args) {

    $path = current($args);
    $path_parts = explode('/',$path);
    $walked_parts = array();
    $current_parent = 'root';
    foreach($path_parts as $part) {
      $walked_parts[] = $part;
      $partkey =  \GoogleDriveCLI\Format::normalize_key($part);
      $subdirs = ContentsList::get_subdir_list($current_parent);
      $current_parent = $subdirs[$partkey]['id'];
      if (empty($current_parent)) return array("No such path: ".implode('/',$walked_parts));
    }

    $q = array();
    $q[] = "parents = '$current_parent'";
    $fields = 'files(name, mimeType, modifiedTime)';

    $list = \GoogleDriveAPI\Client::listFiles(array(
      'q'=>$q,
      'fields'=>$fields,
    ));

    $return = array();
    foreach($list as $item) {
      $sortkey = \GoogleDriveCLI\Format::normalize_key($item['name']);
      $kind = \GoogleDriveCLI\Format::simplify_mimetype($item['mimeType']);
      $modified = \GoogleDriveCLI\Format::normalize_date($item['modifiedTime']);
      $return[$sortkey] = array(
        'name' => $item['name'],
        'kind' => strtoupper($kind),
        'modified' => $modified,
      );
    }
    ksort($return);
    return array_values($return);

  }
  public static function get_subdir_list($parent='root') {

    $q = array();
    $q[] = "parents = '$parent'";
    $q[] = "mimeType = 'application/vnd.google-apps.folder'";
    $fields = 'files(name, id)';

    $list = \GoogleDriveAPI\Client::listFiles(array(
      'q'=>$q,
      'fields'=>$fields,
    ));

    $return = array();
    foreach($list as $item) {
      $sortkey = \GoogleDriveCLI\Format::normalize_key($item['name']);
      $return[$sortkey] = array(
        'name' => $item['name'],
        'id' => $item['id'],
      );
    }
    ksort($return);
    return $return;
  }
}
