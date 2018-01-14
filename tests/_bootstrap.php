<?php
// Constant.
define( 'ANSPRESS_ENABLE_ADDONS', true );

function ap_screenshot_inc() {
  static $counter = 0;
  $counter++;
  return $counter . '-';
}