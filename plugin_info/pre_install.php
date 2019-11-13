<?php
require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
function Freebox_OS_pre_update() {
  exec("sudo rm -R ". dirname(__FILE__)."/../core/template/");
}
?>
