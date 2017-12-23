<?php
function ap_screenshot_inc() {
  static $counter = 0;
  $counter++;
  return $counter . '-';
}