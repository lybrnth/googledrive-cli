<?php
namespace GoogleDriveAPI;
class Demo {

  public function root() {
    $script_location = $_SERVER['SCRIPT_FILENAME'];
    $script_location = realpath($script_location);
    $script_location = dirname($script_location);
    return $script_location;
  }
  public static function demo() {

    define('APPLICATION_NAME', 'Drive API PHP Quickstart');
    define('CREDENTIALS_PATH', Demo::root().'/googledrive-config.json');
    define('CLIENT_SECRET_PATH', Demo::root().'/client_secret.json');
    // If modifying these scopes, delete your previously saved credentials
    // at ~/.credentials/drive-php-quickstart.json
    define('SCOPES', implode(' ', array(
      \Google_Service_Drive::DRIVE_METADATA_READONLY)
    ));

    if (php_sapi_name() != 'cli') {
      throw new \Exception('This application must be run on the command line.');
    }

    // Get the API client and construct the service object.
    $client = Demo::getClient();
    $service = new \Google_Service_Drive($client);

    // Print the names and IDs for up to 10 files.
    $optParams = array();
    $optParams['pageSize'] = 10;
    $optParams['fields'] = 'nextPageToken, files(id, name)';

    // Optional query params
    $q = array();
    //$q[] = "name = 'Tools'";
    //$q[] = "mimeType = 'application/vnd.google-apps.folder'";
    //$q[] = "parents = 'root'"; //'1RQJkG6emZZ_q19__4waLN7rXuNYTQkkT'";
    $optParams['q'] = implode(' and ',$q);
    $results = $service->files->listFiles($optParams);

    if (count($results->getFiles()) == 0) {
      $output = "No files found.\n";
    } else {
      $output = "Files:\n";
      foreach ($results->getFiles() as $file) {
        $output .= '- '.sprintf("%s (%s)\n", $file->getName(), $file->getId());
      }
    }

    return $output;

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
    $credentialsPath = Demo::expandHomeDirectory(CREDENTIALS_PATH);
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
