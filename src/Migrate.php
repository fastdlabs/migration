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
use FastD\QueryBuilder\MySqlBuilder;
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
    /**
     * The database config info.
     *
     * @var array
     */
    protected $config = [];

    /**
     * @var string
     */
    protected $dataSetPath = '';

    /**
     * @var string
     */
    protected $seedPath = '';

    /**
     * @var PDO
     */
    protected $connection;

    public function __construct(array $config = [])
    {
        $this->config = $config;
        if (isset($this->config['seed_path'])) {
            $this->seedPath = $this->config['seed_path'];
            unset($this->config['seed_path']);
        }
        if (isset($this->config['data_set_path'])) {
            $this->dataSetPath = $this->config['data_set_path'];
            unset($this->config['data_set_path']);
        }
        parent::__construct('migrate');
    }

    public function configure()
    {
        $this->setDescription('Migration database to php')
            ->addArgument(
                'behavior',
                InputArgument::OPTIONAL,
                'Migration behavior <comment>[info|seed|dump|run|cache]</comment>',
                'help'
            )
            ->addArgument('table', InputArgument::OPTIONAL, 'Migration table name', null)
            ->addOption('conf', 'c', InputOption::VALUE_OPTIONAL, 'Config file', './migrate.yml')
            ->addOption('seed', 's', InputOption::VALUE_OPTIONAL, 'Dump or run into tables path', './seed')
            ->addOption('data', 'd', InputOption::VALUE_OPTIONAL, 'Insert dataset in to table.', './dataset')
            ->addOption('clear', null, InputOption::VALUE_NONE, 'Clear cache')
            ->addOption('info', 'i', InputOption::VALUE_NONE, 'Show table info');
    }

    /**
     * @return \PDO
     */
    protected function createConnection()
    {
        if (null === $this->connection) {
            $this->connection = new PDO(
                sprintf('mysql:host=%s;dbname=%s', $this->config['host'], $this->config['dbname']),
                $this->config['user'],
                $this->config['pass']
            );
        }

        return $this->connection;
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
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }
    }

    public function version(OutputInterface $output)
    {
        $output->writeln(
            sprintf(
                'FastD <info>Migration</info> Version: <comment>%s</comment>',
                Migrator::VERSION
            ) . PHP_EOL
        );
    }

    public function verbosity(OutputInterface $output, Table $table)
    {
        if ($output->getVerbosity() == OutputInterface::VERBOSITY_DEBUG) {
            $builder = new TableBuilder($this->createConnection());
            $output->writeln(sprintf("SQL: \n%s", $builder->update($table)->getTableInfo()));
        }
    }

    /**
     * @param OutputInterface $output
     */
    protected function renderConfig(OutputInterface $output)
    {
        $config = $this->config;
        $config['seed'] = $this->seedPath;
        $config['dataset'] = $this->dataSetPath;
        $output->writeln(Yaml::dump($config));
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null
     * @throws \Exception
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->version($output);

        if (null === $this->connection && empty($this->config)) {
            $file = $input->getOption('conf');
            if (!file_exists($file)) {
                $config = $this->askConfig($input, $output);
                file_put_contents($file, Yaml::dump($config));
            } else {
                $config = load($file);
            }
            $this->config = $config;
        }

        if ($input->hasParameterOption(['--seed', '-s'])) {
            $this->seedPath = $input->getOption('seed');
        }

        if ($input->hasParameterOption(['--data', '-d'])) {
            $this->dataSetPath = $input->getOption('data');
        }

        switch ($input->getArgument('behavior')) {
            case 'seed':
                $this->renderConfig($output);
                $this->seed($input, $output);
                break;
            case 'info':
                $this->renderConfig($output);
                $this->info($input, $output);
                break;
            case 'cache':
                $this->renderConfig($output);
                $this->cacheClear($input, $output);
                break;
            case 'run':
                $this->renderConfig($output);
                $this->move($input, $output);
                break;
            case 'dump':
                $this->renderConfig($output);
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
     */
    protected function renderTableInfo(InputInterface $input, OutputInterface $output, Table $table)
    {
        if ($input->hasParameterOption(['--info', '-i']) || !empty($input->getArgument('table'))) {
            $t = new SymfonyTable($output);
            $t->setHeaders(array('Field', 'Type', 'Nullable', 'Key', 'Default', 'Comment', 'Extra'));
            foreach ($table->getColumns() as $column) {
                $t->addRow(
                    [
                        $column->getName(),
                        $column->getType() .
                        ($column->getLength() === null ? '' : '(' . $column->getLength() . ')') .
                        ($column->isUnsigned() ? '' : ' unsigned'),
                        $column->isNullable() ? 'YES' : 'NO',
                        null === $column->getKey() ? '' : $column->getKey()->getKey(),
                        $column->getDefault(),
                        $column->getComment() ? $column->getComment() : '',
                        $column->isIncrement() ? 'auto_increment' : ''
                    ]
                );
            }

            $t->render();
        }
    }

    /**
     * @param $name
     * @return string
     */
    protected function classRename($name)
    {
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
    public function seed(InputInterface $input, OutputInterface $output)
    {
        $tableName = $input->getArgument('table');
        if (empty($tableName)) {
            throw new \RuntimeException('Table name is empty.');
        }
        $table = new Table($tableName);
        $table
            ->addColumn('id', 'int', null, false, 0, '')
            ->addColumn('created_at', 'datetime', null, false, 'CURRENT_TIMESTAMP', '')
            ->addColumn('updated_at', 'datetime', null, false, 'CURRENT_TIMESTAMP', '');
        $content = $this->dumpPhpFile($table);
        $path = $this->seedPath;
        $this->targetDirectory($path);
        $name = $this->classRename($table->getTableName());
        $file = $path . '/' . $name . '.php';
        if (!file_exists($file)) {
            file_put_contents($file, $content);
            $output->writeln(sprintf(
                '  <info>✔</info> Table <info>"%s"</info> <comment>dumping</comment> <info>done.</info>',
                $table->getTableName()
            ));
        } else {
            $output->writeln(sprintf(
                '  <fg=red>✗</> Dump file <comment>"%s"</comment> exists',
                $table->getTableName()
            ));
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function info(InputInterface $input, OutputInterface $output)
    {
        $builder = new TableBuilder($this->createConnection());

        $tableName = $input->getArgument('table');

        $tables = $builder->extract($tableName);

        if (!empty($tables)) {
            foreach ($tables as $table) {
                $output->writeln(sprintf('Table: <comment>%s</comment>', $table->getTableName()));
                $this->renderTableInfo($input, $output, $table);
            }
        } else {
            $output->writeln(sprintf('  <comment>!!</comment> Table <comment>"%s"</comment> is not exists.', $tableName));
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
        $output->writeln(sprintf('Cache path: <info>%s</info>', __DIR__ . '/.cache'));
        $files = glob(__DIR__ . '/.cache/tables' . '/*');
        if (!empty($files)) {
            foreach ($files as $file) {
                $output->writeln('  <info>-></info> ' . $file);
                if ($input->hasParameterOption(['--clear'])) {
                    unlink($file);
                    $output->writeln(sprintf(
                        '    <info>✔</info> Table <info>"%s"</info> cache is clean <info>done.</info>',
                        pathinfo($file, PATHINFO_FILENAME)
                    ));
                }
            }
        } else {
            $output->writeln(sprintf('  <comment>!!</comment> Empty cache.'));
        }

        $files = glob(__DIR__ . '/.cache/dataset' . '/*');
        if (!empty($files)) {
            foreach ($files as $file) {
                $output->writeln('  <info>-></info> ' . $file);
                if ($input->hasParameterOption(['--clear'])) {
                    unlink($file);
                    $output->writeln(sprintf(
                        '    <info>✔</info> Table <info>"%s"</info> dataset is clean <info>done.</info>',
                        pathinfo($file, PATHINFO_FILENAME)
                    ));
                }
            }
        } else {
            $output->writeln(sprintf('  <comment>!!</comment> Empty dataset.'));
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function move(InputInterface $input, OutputInterface $output)
    {
        $builder = new TableBuilder($this->createConnection());
        $path = $this->seedPath;
        $table = $this->classRename($input->getArgument('table'));

        $move = function ($file) use ($input, $output, $builder) {
            $className = pathinfo($file, PATHINFO_FILENAME);
            include_once $file;
            $migration = new $className();
            if ($migration instanceof MigrationAbstract) {
                $table = $migration->setUp();
                try {
                    if ('' === ($sql = $builder->update($table)->getTableInfo())) {
                        $output->writeln(sprintf(
                            '  <comment>!!</comment> Table <info>"%s"</info> <comment>no change.</comment>',
                            $table->getTableName()
                        ));
                    } else {
                        $builder->update($table)->execute();
                        $output->writeln(sprintf(
                            '  <info>✔</info> Table <info>"%s"</info>' .
                            ' <comment>migrating</comment> <info>done.</info>',
                            $table->getTableName()
                        ));
                    }
                    if (!empty($this->dataSetPath)) {
                        $cachePath = __DIR__ . '/.cache/dataset';
                        $this->targetDirectory($cachePath);
                        $tableName = $table->getTableName();
                        $dataFile = $this->dataSetPath . '/' . $tableName . '.yml';
                        $rowsCount = 0;
                        if (file_exists($dataFile) && !file_exists($cachePath . '/' . $tableName)) {
                            $dataset = Yaml::parse(file_get_contents($dataFile));
                            foreach ($dataset as $row) {
                                $sql = (new MySqlBuilder($tableName))->insert($row);
                                if ($this->connection->exec($sql) > 0) {
                                    $rowsCount++;
                                }
                            }
                            $output->writeln(sprintf(
                                '      <info>-></info> Table <info>"%s"</info> insert data: <info>%s</info>',
                                $tableName,
                                $rowsCount
                            ));

                            file_put_contents($cachePath . '/' . $tableName, 1);
                        }
                    }
                    $this->renderTableInfo($input, $output, $table);
                } catch (\PDOException $e) {
                    $output->writeln(sprintf(
                        "<fg=red>✗</> %s \n  File: %s\n  Line: %s\n",
                        $e->getMessage(),
                        $e->getFile(),
                        $e->getLine()
                    ));
                }
                $this->verbosity($output, $table);
            } else {
                $output->writeln(
                    sprintf(
                        '  <comment>!!</comment>' .
                        ' Warning: Migrate class "<comment>%s</comment>" is not implement "<comment>%s</comment>"',
                        $className,
                        MigrationAbstract::class
                    )
                );
            }
        };

        if (file_exists($path . '/' . $table . '.php')) {
            $move($path . '/' . $table . '.php');
        } else {
            foreach (glob($path . '/*.php') as $file) {
                $move($file);
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
        $path = $this->seedPath;
        $this->targetDirectory($path);
        $tables = $builder->extract($tableName = $input->getArgument('table'));
        foreach ($tables as $table) {
            $name = $this->classRename($table->getTableName());
            $file = $path . '/' . $name . '.php';
            $content = $this->dumpPhpFile($table);
            $contentHash = hash('md5', $content);
            if (!file_exists($file) || (file_exists($file) && $contentHash !== hash_file('md5', $file))) {
                file_put_contents($file, $content);
                $output->writeln(sprintf(
                    '  <info>✔</info> Table <info>"%s"</info> <comment>dumping</comment> <info>done.</info>',
                    $table->getTableName()
                ));
            } else {
                $output->writeln(sprintf(
                    '  <comment>!!</comment> Dump table "<comment>%s</comment>" is <comment>not change</comment>',
                    $table->getTableName()
                ));
            }
            $this->renderTableInfo($input, $output, $table);
        }
    }

    /**
     * @param Table $table
     * @return string
     */
    protected function dumpPhpFile(Table $table)
    {
        $name = $this->classRename($table->getTableName());

        $code = ['$table'];
        $index = [];
        foreach ($table->getColumns() as $column) {

            $length = null === $column->getLength() ? 'null' : $column->getLength();
            if (false !== strpos($length, ',')) {
                $length = '[' . $length . ']';
            }

            $code[] = str_repeat(' ', 12) .
                sprintf(
                    "->addColumn('%s', '%s', %s, %s, '%s', '%s')",
                    $column->getName(),
                    $column->getType(),
                    $length,
                    false === $column->isNullable() ? 'false' : 'true',
                    $column->getDefault(),
                    $column->getComment()
                );
            if ($column->isIncrement()) {
                $code[] = str_repeat(' ', 12) . '->withIncrement()';
            }
            if ($column->isUnsigned()) {
                $code[] = str_repeat(' ', 12) . '->withUnsigned()';
            }
            if ($column->isIndex()) {
                $index[] = str_repeat(' ', 12) .
                    sprintf("->addIndex('%s', Key::INDEX)", $column->getName());
            }
            if ($column->isPrimary()) {
                $index[] = str_repeat(' ', 12) .
                    sprintf("->addIndex('%s', Key::PRIMARY)", $column->getName());
            }
            if ($column->isUnique()) {
                $index[] = str_repeat(' ', 12) .
                    sprintf("->addIndex('%s', Key::UNIQUE)", $column->getName());
            }
        }

        if (!empty($index)) {
            $codeString = implode(PHP_EOL, $code);
            $indexString = implode(PHP_EOL, $index) . ';';
        } else {
            $codeString = implode(PHP_EOL, $code) . ';';
            $indexString = '';
        }

        $table = strtolower($table->getTableName());

        return <<<MIGRATION
<?php

use \FastD\Migration\MigrationAbstract;
use \FastD\Migration\Table;
use \FastD\Migration\Key;


class {$name} extends MigrationAbstract
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        \$table = new Table('{$table}');

        {$codeString}\n{$indexString}
        
        return \$table;
    }
}
MIGRATION;
    }
}
