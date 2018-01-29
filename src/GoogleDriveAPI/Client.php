<?php
namespace GoogleDriveAPI;
class Client {

  public function root() {
    $script_location = $_SERVER['SCRIPT_FILENAME'];
    $script_location = realpath($script_location);
    $script_location = dirname($script_location);
    return $script_location;
  }
  public static function get_api() {
    define('APPLICATION_NAME', 'Drive API PHP Quickstart');
    define('CREDENTIALS_PATH', Client::root().'googledrive-config.json');
    define('CLIENT_SECRET_PATH', Client::root().'/client_secret.json');
    // If modifying these scopes, delete your previously saved credentials
    // at ~/.credentials/drive-php-quickstart.json
    define('SCOPES', implode(' ', array(
      \Google_Service_Drive::DRIVE_METADATA_READONLY)
    ));
    // Get the API client and construct the service object.
    $client = Client::getClient();
    $service = new \Google_Service_Drive($client);
    return $service;
  }
  public static function listFiles($params) {

    if (!empty($params['fields']) && is_array($params['fields']))
      $params['fields'] = implode(', ',$params['fields']);
    if (!empty($params['q']) && is_array($params['q']))
      $params['q'] = implode(' and ',$params['q']);

    $api = Client::get_api();
    $results = $api->files->listFiles($params);

    $return = array();
    foreach($results->getFiles() as $file) {
      $return[] = (array) $file;
    }

    return $return;

  }
  /**
   * Returns an authorized API client.
   * @return Google_Client the authorized client object
   */
  function getClient() {
    $client = new \Google_Client();
    $client->setApplicationName(APPLICATION_NAME);
    $client->setScopes(SCOPES);
    $client->setAuthConfig(CLIENT_SECRET_PATH);
    $client->setAccessType('offline');

    // Load previously authorized credentials from a file.
    $credentialsPath = Client::expandHomeDirectory(CREDENTIALS_PATH);
    if (file_exists($credentialsPath)) {
      $accessToken = json_decode(file_get_contents($credentialsPath), true);
    } else {
      // Request authorization from the user.
      $authUrl = $client->createAuthUrl();
      printf("Open the following link in your browser:\n%s\n", $authUrl);
      print 'Enter verification code: ';
      $authCode = trim(fgets(STDIN));

      // Exchange authorization code for an access token.
      $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

      // Store the credentials to disk.
      if(!file_exists(dirname($credentialsPath))) {
        mkdir(dirname($credentialsPath), 0700, true);
      }
      file_put_contents($credentialsPath, json_encode($accessToken));
      printf("Credentials saved to %s\n", $credentialsPath);
    }
    $client->setAccessToken($accessToken);

    // Refresh the token if it's expired.
    if ($client->isAccessTokenExpired()) {
      $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
      file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
    }
    return $client;
  }

  /**
   * Expands the home directory alias '~' to the full path.
   * @param string $path the path to expand.
   * @return string the expanded path.
   */
  function expandHomeDirectory($path) {
    $homeDirectory = getenv('HOME');
    if (empty($homeDirectory)) {
      $homeDirectory = getenv('HOMEDRIVE') . getenv('HOMEPATH');
    }
    return str_replace('~', realpath($homeDirectory), $path);
  }


}
