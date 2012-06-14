#!/usr/bin/env php
<?php
require_once __DIR__.'/../setup.php';

use \naf\util\ShellCmd;
use \redmine\Issue;
use \git\Branch;
use \Zend\Console\Getopt;

$rules = array(
    'help|h' => 'Get usage message',
    'dir|d=s' => 'Path to a git working copy',
    'remote|r' => 'Consider only remote branches',
    'merged|m' => 'Consider only fully merged branches',
    'force|f' => 'Do not ask for confirmation before deleting every branch',
    'before|b=i' => 'Consider only branches that bound to issue_id lesser than given value',
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

$list = Branch::enlist($gitWorkingCopyDir, isset($opts->remote), isset($opts->merged));

$charInput = new SttyCharInput();
$confirmed = true;

foreach (new PregMatchIterator('~^(.*)/task-([0-9]+)(-.+)?$~', $list) as $matches)
{
    $issueId = $matches[2];
    if (isset($opts->before) && $issueId >= $opts->before) {
	    continue;
    }

    $branchFullName = $matches[0];
    echo $issueId.': '.$branchFullName.' ';

    $branch = new Branch($gitWorkingCopyDir, $branchFullName);

    $issue = new Issue();
    $issue->find($issueId);
    if ($issue->count()) {
        echo '['.$issue->status['name'].'] ';
        if (in_array($issue->status['id'], Naf::config('redmine.closed_issue_status_ids')))
        {
            if (!$opts->force) {
                echo 'Delete '.($branch->isRemote()?'remote':'local').' branch? (y/N) ';
                $confirmed = $charInput->confirm();
            }

            if ($confirmed) {
                try {
                    $branch->delete();
                    echo ' Deleted';
                } catch (ShellCmd\Fault $e) {
                    echo $e->getMessage();
                }
            }
        }
    } else {
        echo 'Issue not found';
    }

    echo "\n";
}