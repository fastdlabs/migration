<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @see      https://www.github.com/janhuang
 * @see      http://www.fast-d.cn/
 */

namespace FastD\Migration;


use PDO;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\HelpCommand;
use Symfony\Component\Console\Helper\Table as SymfonyTable;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Migrate
 * @package FastD\Migration\Console
 */
class Migrate extends Command
{
    /**`
     * @var string
     */
    protected $configFile;

    public function configure()
    {
        $this
            ->setName('migrate')
            ->setDescription('Migration database to php')
            ->addArgument('behavior', InputArgument::OPTIONAL, 'Migration behavior <comment>[info|dump|run|cache-clear]</comment>', 'help')
            ->addArgument('table', InputArgument::OPTIONAL, 'Migration table name', null)
            ->addOption('conf', 'c', InputOption::VALUE_OPTIONAL, 'Config file', './migrate.yml')
            ->addOption('path', 'p', InputOption::VALUE_OPTIONAL, 'Dump or run into tables path', './migration')
            ->addOption('data', 'd', InputOption::VALUE_OPTIONAL, 'Insert dataset in to table.', './dataset')
            ->addOption('info', 'i', InputOption::VALUE_NONE, 'Show table info')
        ;
    }

    /**
     * @param array $config
     * @return \PDO
     */
    protected function createConnection(array $config = null)
    {
        if (null === $config) {
            if (!file_exists($this->configFile)) {
                throw new \RuntimeException('cannot such config file '.$this->configFile);
            }

            $config = Yaml::parse(file_get_contents($this->configFile));
        }

        return new PDO(
            sprintf('mysql:host=%s;dbname=%s', $config['host'], $config['dbname']),
            $config['user'],
            $config['pass']
        );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return array
     */
    protected function askConfig(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $host = $helper->ask($input, $output, new Question('MySQL host (<info>127.0.0.1</info>)?', '127.0.0.1'));
        $user = $helper->ask($input, $output, new Question('MySQL user (<info>root</info>)?', 'root'));
        $password = $helper->ask($input, $output, new Question('MySQL password (<info>null</info>)?', null));
        $dbname = $helper->ask($input, $output, new Question('MySQL database (<info>null</info>)?', null));
        $charset = $helper->ask($input, $output, new Question('MySQL charset (<info>utf8</info>)?', 'utf8'));
        return [
            'host' => $host,
            'user' => $user,
            'pass' => $password,
            'dbname' => $dbname,
            'charset' => $charset,
        ];
    }

    /**
     * @param $path
     */
    protected function targetDirectory($path)
    {
        if (! file_exists($path)) {
            mkdir($path, 0755, true);
        }
    }

    public function version(OutputInterface $output)
    {
        $output->writeln(sprintf('FastD <info>Migration</info> Version: <comment>%s</comment>', Migrator::VERSION) . PHP_EOL);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->version($output);

        $this->configFile = $input->getOption('conf');
        if (! file_exists($this->configFile)) {
            $config = $this->askConfig($input, $output);
            $config = Yaml::dump($config);
            file_put_contents($this->configFile, $config);
        } else {
            $config = file_get_contents($this->configFile);
        }

        switch ($input->getArgument('behavior')) {
            case 'info':
                $output->writeln($config);
                $this->info($input, $output);
                break;
            case 'cache-clear':
                $output->writeln($config);
                $this->cacheClear($input, $output);
                break;
            case 'run':
                $output->writeln($config);
                $this->move($input, $output);
                break;
            case 'dump':
                $output->writeln($config);
                $this->dump($input, $output);
                break;
            case 'help':
            default:
                $help = new HelpCommand();
                $help->setCommand($this);
                $help->run($input, $output);
        }

        return 0;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param Table $table
     * @return SymfonyTable|null
     */
    protected function renderTableInfo(InputInterface $input, OutputInterface $output, Table $table)
    {
        $output->writeln(sprintf('Table: <comment>%s</comment>', $table->getTableName()));
        if ($input->hasParameterOption(['--info', '-i']) || !empty($input->getArgument('table'))) {
            $t = new SymfonyTable($output);
            $t->setHeaders(array('Field', 'Type', 'Nullable', 'Key', 'Default', 'Extra'));
            foreach ($table->getColumns() as $column) {
                $t->addRow(
                    [
                        $column->getName(),
                        $column->getType().($column->getLength() <= 0 ? '' : '('.$column->getLength().')'),
                        $column->isNullable() ? 'YES' : 'NO',
                        null === $column->getKey() ? '' : $column->getKey()->getKey(),
                        $column->getDefault(),
                        (null == $column->getComment()) ? '' : ('comment:'. $column->getComment()),
                    ]
                );
            }

            $t->render();
        }
    }

    /**
     * @param Table $table
     * @return string
     */
    protected function classRename(Table $table)
    {
        $name = $table->getTableName();
        if (strpos($name, '_')) {
            $arr = explode('_', $name);
            $name = array_shift($arr);
            foreach ($arr as $value) {
                $name .= ucfirst($value);
            }
        }
        return ucfirst($name);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function info(InputInterface $input, OutputInterface $output)
    {
        $builder = new TableBuilder($this->createConnection());

        $tables = $builder->extract($tableName = $input->getArgument('table'));

        foreach ($tables as $table) {
            $this->renderTableInfo($input, $output, $table);
        }
    }

    /**
     * Clean table cache file
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function cacheClear(InputInterface $input, OutputInterface $output)
    {
        $cachePath = __DIR__ . '/.cache/tables';
        $output->writeln(sprintf('Cache path: <comment>%s</comment>', $cachePath));
        foreach (glob($cachePath . '/*') as $file) {
            unlink($file);
            $output->writeln(sprintf('  <info>✔</info> Table <info>"%s"</info> <comment>cache is clean</comment> <info>done.</info>', pathinfo($file, PATHINFO_FILENAME)));
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function move(InputInterface $input, OutputInterface $output)
    {
        $builder = new TableBuilder($this->createConnection());
        $path = realpath($input->getOption('--path'));

        foreach (glob($path.'/*.php') as $file) {
            $className = pathinfo($file, PATHINFO_FILENAME);
            include_once $file;
            $migration = new $className();
            if ($migration instanceof MigrationAbstract) {
                $table = $migration->setUp();
                try {
                    $this->renderTableInfo($input, $output, $table);
                    // not change
                    if ('' === $builder->update($table)->getTableInfo()) {
                        $output->writeln(sprintf('  <comment>!!</comment> Table <info>"%s"</info> <comment>no change.</comment>', $table->getTableName()));
                    } else {
                        $builder->update($table)->execute();
                        $output->writeln(sprintf('  <info>✔</info> Table <info>"%s"</info> <comment>migrating</comment> <info>done.</info>', $table->getTableName()));
                    }
                } catch (\PDOException $e) {
                    $output->writeln(sprintf("<fg=red>✗</> %s \n  File: %s\n  Line: %s\n", $e->getMessage(), $e->getFile(), $e->getLine()));
                }
            } else {
                $output->writeln(sprintf('  <comment>!!</comment> Warning: Migrate class "<comment>%s</comment>" is not implement "<comment>%s</comment>"', $className, MigrationAbstract::class));
            }
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function dump(InputInterface $input, OutputInterface $output)
    {
        $builder = new TableBuilder($this->createConnection());
        $path = $input->getOption('path');
        $this->targetDirectory($path);
        $tables = $builder->extract($tableName = $input->getArgument('table'));
        foreach ($tables as $table) {
            $this->renderTableInfo($input, $output, $table);
            $name = $this->classRename($table);
            $file = $path . '/' . $name . '.php';
            $content = $this->dumpPhpFile($table);
            $contentHash = hash('md5', $content);
            if (!file_exists($file) || (file_exists($file) && $contentHash !== hash_file('md5', $file))) {
                file_put_contents($file, $content);
                $output->writeln(sprintf('  <info>✔</info> Table <info>"%s"</info> <comment>dumping</comment> <info>done.</info>', $table->getTableName()));
            } else {
                $output->writeln(sprintf('  <comment>!!</comment> Dump table "<comment>%s</comment>" is <comment>not change</comment>', $table->getTableName()));
            }
        }
    }

    /**
     * @param Table $table
     * @return string
     */
    protected function dumpPhpFile(Table $table)
    {
        $name = $this->classRename($table);

        $code = ['$table'];
        foreach ($table->getColumns() as $column) {
            $code[] = str_repeat(' ', 12) . sprintf(
                "->addColumn('%s', '%s', %s, %s, '%s', '%s')",
                $column->getName(),
                $column->getType(),
                null === $column->getLength() ? 'null' : $column->getLength(),
                false === $column->isNullable() ? 'false' : 'true',
                $column->getDefault(),
                $column->getComment()
                );
        }
        $code[] = str_repeat(' ', 8) . ';';

        $codeString = implode(PHP_EOL, $code);

        return <<<MIGRATION
<?php

use \FastD\Migration\MigrationAbstract;
use \FastD\Migration\Column;
use \FastD\Migration\Table;


class {$name} extends MigrationAbstract
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        \$table = new Table('{$table->getTableName()}');

        {$codeString}

        return \$table;
    }

    /**
     * {@inheritdoc}
     */
    public function dataSet()
    {
        
    }
}
MIGRATION;
    }
}