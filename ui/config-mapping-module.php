<?php
/**
 * @todo change dependency on the filter-config-example.php to this file.
 *       The original idea is to provide a template for how to use the filters in the mapping module.
 *       Instead of doing this in two places in development. This below currently just loads the example file.
 */
require_once ( plugin_dir_path(__DIR__) . '/mapping-module/custom-config-module-example.php' );
DT_Mapping_Module_Example_Filters::instance();