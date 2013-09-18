<?php
require_once("vendor/autoload.php");
use Symfony\Component\Process\Process;

class Clean
{
    private $error;
    private $opt;
    private $test;
    private $dirPath;

    public function __construct($dirPath, $test = true)
    {
        $this->dirPath = $dirPath;
        $this->test = $test;
        $this->error = array();
        $this->opt = array();

        $cmd[] = "cd ".$dirPath;
        $cmd[] = "git status";
        $process = new Process(implode(";",$cmd));
        $process->run(function ($type, $buffer) {
            if ('err' === $type) {
                $this->error[] = $buffer;
            } else {
                $this->opt[] = $buffer;
            }
        });

        foreach($this->error as $error) {
            echo "\n".$error;
        }
        $unTrackedIndex = false;

        $optLines = explode("\n",$this->opt[0]);
        foreach($optLines as $index => $opt) {
            if (strpos($opt,"Untracked files") !== false) {
                $unTrackedIndex = $index + 3;
                break;
            }
        }

        if ($unTrackedIndex) {
            $i = $unTrackedIndex;
            while($i<count($optLines)) {
                if (strpos($optLines[$i],"#") !== false) {
                    $filePath = trim(str_replace("#", "", $optLines[$i]));
                    $this->action($filePath);
                }

                $i++;
            }
        }
    }
    public function action($filePath) {
        $cmd = "rm -rf ".$this->dirPath."/".$filePath;

        if ($this->test) {
            echo "\n".$cmd;
        }
        else {
            $process = new Process($cmd);
            echo "\nProcessing ".$cmd;
            $process->run(function ($type, $buffer) {
                echo "\n".$buffer;
            });
        }

    }

}
$run = new Clean("./");
