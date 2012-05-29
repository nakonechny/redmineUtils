redmineUtils
============

Some handy utils to associate a git working copy with respective redmine tracker

## Pull down submodules

    git submodule update --init  #from project root

## Usage examples

    cli/cut.php --dir ~/hello_world #deletes all local branches that have "closed" status in redmine
    cli/cut.php --dir ~/hello_world --remote #deletes all remote branches (in origin) that are of status 'closed' in redmine
    cli/cut.php --dir ~/hello_world --force --before 12300 #deletes branches bound with issues having id < 12300 without asking for confirmation
    cli/list.php --dir ~/hello_world --all #lists all branches with respective statuses in redmine
    cli/sync-hamster.php --date "last friday" #posts spent time on activities named with #{issue_id} on given date to redmine
