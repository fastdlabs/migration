<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @see      https://www.github.com/janhuang
 * @see      http://www.fast-d.cn/
 */

namespace FastD\Migration;


use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Migrator
 * @package FastD\Migration
 */
class Migrator extends Application
{
    const VERSION = '0.1.0 beta';

    public function __construct()
    {
        parent::__construct('Migration', static::VERSION);

        $this->add(new Migrate());
    }

    /**
     * @param InputInterface|null $input
     * @param OutputInterface|null $output
     * @return int
     */
    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        $argv = $_SERVER['argv'];
        $script = array_shift($argv);
        array_unshift($argv, 'migrate');
        array_unshift($argv, $script);
        return parent::run(new ArgvInput($argv), $output);
    }
}