<?php
require_once __DIR__.'/../setup.php';

use \naf\util\ShellCmd;
use \naf\util\ShellCmd\Fault as ShellCmdFault;
use \redmine\Issue;

$branchPattern = '~(origin/)?task-([0-9]+)(-.+)?~';
$closedStatusIds = array(
    5,//выполнен
    6,//отказ
);

$gitWorkingCopyDir = @$_SERVER['argv'][1];

$branchCmd = new ShellCmd('cd '.$gitWorkingCopyDir .' && git branch');

if (@$_SERVER['argv'][2] == 'remote') {
    $branchCmd->addOption('--remote'); //remote branches
} else if (@$_SERVER['argv'][2] == 'all') {
    $branchCmd->addOption('--all'); //all branches
}

$output = $branchCmd->exec();
$list = explode("\n", $output);

$sttyStatusCmd = new ShellCmd('stty --save');
$stty_term = $sttyStatusCmd->exec();
$sttyRestoreCmd = new ShellCmd('stty '.$stty_term);
$stty1CharCmd = new ShellCmd('stty -icanon');

$issueIdThreshold = Naf::config('issue_id_threshold');
$askBeforeCut = Naf::config('ask_before_cut');

foreach (new PregMatchIterator($branchPattern, $list) as $matches)
{
    $issueId = $matches[2];
    if ($issueId >= $issueIdThreshold) {
	    continue;
    }

    $branchName = $matches[0];
    $isRemote = ! empty($matches[1]);
    echo $issueId.': '.$branchName.' ';

    $issue = new Issue();
    $issue->find($issueId);
    if ($issue->count()) {
        echo '['.$issue->status['name'].'] ';
        if (in_array($issue->status['id'], $closedStatusIds))
        {
	    if ($askBeforeCut) {
                echo "Удалить ветку? (y/N) ";

                $stty1CharCmd->exec();
                $c = fread(STDIN, 1);
                $sttyRestoreCmd->exec();
	    } else {
	        $c = 'y';
	    }

            if (in_array($c, array('y', 'Y'))) {
                if ($isRemote) {
                    $deleteCmd = new ShellCmd('cd '.$gitWorkingCopyDir . '&& git push origin :'.str_replace('origin/', '', $branchName));
                } else {
                    $deleteCmd = new ShellCmd('cd '.$gitWorkingCopyDir . '&& git branch -d '.$branchName);
                }
		try {
                    $deleteCmd->exec();
                    echo ' Удалено';
		} catch (ShellCmdFault $e) {
			echo $e->getMessage();
		}
            }
        }
    } else {
        echo 'Issue not found';
    }

    echo "\n";
}
