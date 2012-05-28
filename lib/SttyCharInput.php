<?php

use \naf\util\ShellCmd;

class SttyCharInput
{
    /**
     * @var string $initialStatusTerm
     */
    protected $initialStatusTerm;
    /**
     * @var ShellCmd $cmd1Char
     */
    static protected $cmd1Char;
    /**
     * @var ShellCmd $cmdSave
     */
    static protected $cmdSave;

    /**
     * @throws Exception
     */
    protected function saveInitialStatus()
    {
        if ($this->initialStatusTerm) {
            throw new Exception('TTY status collision');
        }

        if (!static::$cmdSave) {
            static::$cmdSave = new ShellCmd('stty --save');
        }
        $this->initialStatusTerm = static::$cmdSave->exec();
    }

    protected function restoreInitialStatus()
    {
        if (! $this->initialStatusTerm) {
            return;
        }

        $sttyRestoreCmd = new ShellCmd('stty '.$this->initialStatusTerm);
        $sttyRestoreCmd->exec();
        $this->initialStatusTerm = null;
    }

    protected function setStatus1Char()
    {
        if (!static::$cmd1Char) {
            static::$cmd1Char = new ShellCmd('stty -icanon');
        }
        static::$cmd1Char->exec();
    }

    /**
     * @return string
     */
    public function readChar()
    {
        $this->saveInitialStatus();
        $this->setStatus1Char();
        $c = fread(STDIN, 1);
        $this->restoreInitialStatus();

        return $c;
    }

    /**
     * Waits for user input in console and returns true if user presses 'Y'
     *
     * @return bool
     */
    public function confirm()
    {
        return in_array($this->readChar(), array('y', 'Y'));
    }

    public function __destruct()
    {
        $this->restoreInitialStatus();
    }
}