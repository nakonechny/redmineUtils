<?php
namespace git;
use \naf\util\ShellCmd;

class Branch
{
    /**
     * @var string $dir
     * @var string $name
     * @var string $remoteName
     */
    protected $dir;
    protected $name;
    protected $remoteName;

    /**
     * @param string $dir
     * @param string $name
     */
    public function __construct($dir, $name)
    {
        $this->dir = $dir;

        $matches = array();
        if (preg_match('~(.+)/(.+)$~', $name, $matches)) {
            $this->remoteName = $matches[1];
            $this->name = $matches[2];
        } else {
            $this->name = $name;
        }
    }

    /**
     * @return bool
     */
    public function isRemote()
    {
        return !empty($this->remoteName);
    }

    public function delete()
    {
        if ($this->isRemote()) {
            $deleteCmd = new ShellCmd('cd '.$this->dir.' && git push '.$this->remoteName.' :'.$this->name);
        } else {
            $deleteCmd = new ShellCmd('cd '.$this->dir.' && git branch -D '.$this->name);
        }
        $deleteCmd->exec();
    }

    /**
     * @param string $dir
     * @param bool $remote
     * @param bool $merged
     * @return array
     */
    static public function enlist($dir, $remote = false, $merged = false)
    {
        $branchListCmd = new ShellCmd('cd '.$dir.' && git branch');

        $branchListCmd->addOption('--color=never');

        if ($remote) {
            $branchListCmd->addOption('--remote');
        }
        if ($merged) {
            $branchListCmd->addOption('--merged');
        }


        $output = $branchListCmd->exec();

        return explode("\n", $output);
    }
}
