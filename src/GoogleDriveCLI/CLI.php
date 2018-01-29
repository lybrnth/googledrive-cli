<?php
namespace GoogleDriveCLI;
class CLI {

  public static function cli($argv) {

    // set timezone
    date_default_timezone_set('America/Montreal');

    // process arguments
    $args = array_slice($argv,1);
    $op = array_shift($args);

    // call method for operation if there is one
    $op_method = 'op_'.$op;
    if (method_exists(get_called_class(),$op_method)) {
      call_user_func_array(get_called_class().'::'.$op_method,array($args));
      return;
    }

    // if we get this far, show usage info
    CLI::op_usage($args);

  }
  public static function op_usage($args) {

    // print usage info
    print "USAGE: worklog [op] [arg1] [arg2]\n";

  }
  public static function op_demo($args) {

    // print output from demo function
    $output = \GoogleDriveAPI\Demo::demo();
    print \GoogleDriveCLI\Output::border_box($output);

  }
  public static function op_list($args) {

    // print output from demo function
    $output = \GoogleDriveCLI\ContentsList::list_array($args);
    $output = \GoogleDriveCLI\Output::whitespace_table($output);

    $path = current($args);
    $output = $path.":\n\n".$output;
    print \GoogleDriveCLI\Output::border_box($output);

  }
  public static function op_config() {

    // ask for app details
    $worklog_dir = readline("Enter path to worklogs directory (enter to skip): ");
    $worklog_default = readline("Enter default worklog (enter to skip): ");
    $invoice_template_md = readline("Enter default invoice md template (enter to skip): ");
    $invoice_template_html = readline("Enter default invoice html template (enter to skip): ");

    // use current settings if something was left blank
    $current = \GoogleDriveCLI\JsonConfig::config_get('worklog-config');
    if (empty($worklog_dir) && !empty($current['worklog']['worklog_dir']))
      $worklog_dir = $current['worklog']['worklog_dir'];
    if (empty($worklog_default) && !empty($current['worklog']['worklog_default']))
      $worklog_default = $current['worklog']['worklog_default'];
    if (empty($invoice_template_md) && !empty($current['worklog']['invoice_template_md']))
      $invoice_template_md = $current['worklog']['invoice_template_md'];
    if (empty($invoice_template_html) && !empty($current['worklog']['invoice_template_html']))
      $invoice_template_html = $current['worklog']['invoice_template_html'];

    // save config file
    \GoogleDriveCLI\JsonConfig::config_set('worklog-config',array(
      'worklog'=>array(
        'worklog_dir'=>$worklog_dir,
        'worklog_default'=>$worklog_default,
        'invoice_template_md'=>$invoice_template_md,
        'invoice_template_html'=>$invoice_template_html,
      ),
    ));

    // display saved data
    $saved = \GoogleDriveCLI\JsonConfig::config_get('worklog-config');
    print 'worklog-config.json: ';
    print_r($saved);

  }

}
