#!/usr/bin/env php
<?php
require_once __DIR__.'/../setup.php';

use \hamster\Db;
use \Zend\Console\Getopt;
use \redmine\TimeEntry;

$rules = array(
    'help|h' => 'Get usage message',
    'date|d=s' => 'Date to sync records for',
);

try {
    $opts = new Getopt($rules);
    $opts->parse();
} catch (\Zend\Console\Exception\ExceptionInterface $e) {
    echo $e->getMessage();
    exit(2);
}

if ($opts->getOption('h')) {
    echo $opts->getUsageMessage();
    exit();
}

if (! isset($opts->date)) {
    echo "Must provide a date via the -d or --date option\n\n";
    echo $opts->getUsageMessage();
    exit(2);
}

$ts = strtotime($opts->date);
if ($ts) {
    $date = date('Y-m-d', $ts);
} else {
    echo "Cannot recognize date format\n\n";
    exit(2);
}

$redmineTagId = Db::assertTagId(Naf::config('hamster.tag_text'));

foreach (Db::selectFactsByDate($date)->fetchAll() as $fact) {
    echo $fact['name'].' ..';

    $matches = null;
    if (! preg_match('~#([0-9]+)~', $fact['name'], $matches)) {
        echo " skipping\n";
        continue;
    }
    $issue_id = $matches[1];

    if (Db::isFactTaggedBy($fact['id'], $redmineTagId)) {
        echo " allready logged\n";
        continue;
    }

    $hours = round($fact['hours'], 1);
    $entry = new TimeEntry(array(
        'issue_id' => $issue_id,
        'spent_on' => $date,
        'hours' => (string)$hours,
        'comments' => '',
        'activity_id' => (string)Naf::config('redmine.activity.development'),
    ));
    $entry->save();
    if (! $entry->id) {
        echo " error\n";
        continue;
    }

    echo ' '.$hours.'H';

    Db::tagFactBy($fact['id'], $redmineTagId);
    echo " ok\n";
}