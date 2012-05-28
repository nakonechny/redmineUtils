<?php

$settings['autoload_map'] = array(
    'ActiveResource' => ROOT.'ext/phpactiveresource/',
    'Zend' => ROOT.'ext/zf2/',
);

$settings['redmine'] = array(
    'api_key' => 'put_your_redmine_api_key_here',
    'domain' => 'put_your_redmine_domain_here',
    'closed_issue_status_ids' => array(5, 6),
);

$settings['hamster'] = array(
    'path_to_db' => '~/.local/share/hamster-applet/hamster.db',

);