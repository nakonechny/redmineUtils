<?php
require_once __DIR__.'/../setup.php';

use \naf\util\ShellCmd;
use \redmine\Issue;

$branchPattern = '~task-([0-9]+)(-.+)?~';

$gitWorkingCopyDir = @$_SERVER['argv'][1];

$branchCmd = new ShellCmd('cd '.$gitWorkingCopyDir .' && git branch');

if (@$_SERVER['argv'][2] == 'remote') {
    $branchCmd->addOption('--remote'); //remote branches
} else if (@$_SERVER['argv'][2] == 'all') {
    $branchCmd->addOption('--all'); //all branches
}

$output = $branchCmd->exec();
$list = explode("\n", $output);

foreach (new PregMatchIterator($branchPattern, $list) as $matches)
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