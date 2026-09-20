<?php
/**
 * テーマを読み込んで init まで走らせる共通処理。
 */
require_once get_template_directory() . '/functions.php';
do_action('after_setup_theme');
do_action('init');
