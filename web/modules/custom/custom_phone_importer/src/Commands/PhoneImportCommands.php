<?php

namespace Drupal\custom_phone_importer\Commands;

use Drush\Commands\DrushCommands;
use Drupal\custom_phone_importer\Service\PhoneImporter;

class PhoneImportCommands extends DrushCommands {

  protected PhoneImporter $importer;

  public function __construct(PhoneImporter $importer) {
    $this->importer = $importer;
  }

  /**
   * Run phone import process from uploaded file.
   *
   * @command phone-import:run
   * @aliases pir
   */
  public function run() {
    $result = $this->importer->run();
    $this->output()->writeln($result);
  }
}
