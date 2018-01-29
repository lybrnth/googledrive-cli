<?php
namespace GoogleDriveCLI;
class Format {

  public static function normalize_key($string) {
    $normalized = preg_replace('/\([^\)]*\)/i','',$string);
    $normalized = preg_replace('/[^a-z0-9]/i','',$normalized);
    $normalized = strtolower($normalized);
    return $normalized;
  }
  public static function simplify_mimetype($string) {
    $simplified = strtr( $string, array(
      'application/vnd.google-apps.'=>'',
      'application/'=>'',
    ));
    return $simplified;
  }
  public static function normalize_date($string) {
    $normalized = explode('T',$string);
    $normalized = current($normalized);
    return $normalized;
  }

}
