#!/usr/bin/env php
<?php
require_once __DIR__.'/../setup.php';

use \naf\util\ShellCmd;
use \redmine\Issue;
use \Zend\Console\Getopt;

$rules = array(
    'help|h' => 'Get usage message',
    'dir|d=s' => 'Path to a git working copy',
    'remote|r' => 'Consider only remote branches',
    'all|a' => 'Consider all branches, local and remote',
);

try {
    $opts = new Getopt($rules);
    $opts->parse();
} catch (\Zend\Console\Exception\ExceptionInterface $e) {
    echo $e->getMessage();
    exit(2);
}

// Help requested
if ($opts->getOption('h')) {
    echo $opts->getUsageMessage();
    exit();
}

if (!isset($opts->dir)) {
    echo "Must provide a path to git working copy via the -d or --dir option\n\n";
    echo $opts->getUsageMessage();
    exit(2);
}

$gitWorkingCopyDir = $opts->dir;
if (!is_dir($gitWorkingCopyDir)) {
    printf("Unable to read from provided directory '%s'\n\n", $gitWorkingCopyDir);
    echo $opts->getUsageMessage();
    exit(2);
}

$branchListCmd = new ShellCmd('cd '.$gitWorkingCopyDir .' && git branch');

if (isset($opts->all)) {
    $branchListCmd->addOption('--all');
} else if (isset($opts->remote)) {
    $branchListCmd->addOption('--remote');
}
$output = $branchListCmd->exec();
$list = explode("\n", $output);

foreach (new PregMatchIterator('~task-([0-9]+)(-.+)?$~', $list) as $matches)
{
    $issueId = $matches[1];
    $branchName = $matches[0];
    echo $issueId.': '.$branchName.' ';

    $issue = new Issue();
    $issue->find($issueId);
    if ($issue->count()) {
        echo '['.$issue->status['name'].'('.$issue->status['id'].')]';
    } else {
        echo 'Issue not found';
    }

    echo "\n";
}