<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @see      https://www.github.com/janhuang
 * @see      http://www.fast-d.cn/
 */

namespace FastD\Migration;


use Symfony\Component\Console\Command\Command;
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
    public function configure()
    {
        $this
            ->setName('migrate')
            ->setDescription('Migration database to php')
            ->addArgument('behavior', InputArgument::REQUIRED, 'migration behavior')
            ->addOption('path', 'p', InputOption::VALUE_OPTIONAL, 'tables path', './')
            ->addArgument('table', InputArgument::OPTIONAL, 'migration table name', null);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $file = getcwd().'/migrate.yml';
        if (! file_exists($file)) {
            $helper = $this->getHelper('question');
            $host = $helper->ask($input, $output, new Question('MySQL host (<info>127.0.0.1</info>)?', '127.0.0.1'));
            $user = $helper->ask($input, $output, new Question('MySQL user (<info>root</info>)?', 'root'));
            $password = $helper->ask($input, $output, new Question('MySQL password (<info>null</info>)?', null));
            $dbname = $helper->ask($input, $output, new Question('MySQL database (<info>null</info>)?', null));
            $charset = $helper->ask($input, $output, new Question('MySQL charset (<info>utf8</info>)?', 'utf8'));
            $content = Yaml::dump(
                [
                    'host' => $host,
                    'user' => $user,
                    'pass' => $password,
                    'dbname' => $dbname,
                    'charset' => $charset,
                ]
            );
            file_put_contents($file, $content);
        }
        $path = realpath($input->getParameterOption(['--path', '-p']));
        if (! file_exists($path)) {
            mkdir($path, 0755, true);
        }
        $tableName = $input->getArgument('table');
        $schema = new Schema();
        $config = $schema->getConfig();
        $output->writeln('<info>Config: </info>');
        $output->writeln(Yaml::dump($config));
        switch ($input->getArgument('behavior')) {
            case 'run':
                foreach (glob($path.'/*.php') as $file) {
                    $migration = pathinfo($file, PATHINFO_FILENAME);
                    include_once $file;
                    $migration = new $migration();
                    if ($migration instanceof Migration) {
                        $table = $migration->setUp();
                        if ($schema->update($table)) {
                            $output->writeln(sprintf('  == Table <info>%s:</info> <comment>migrating</comment> <info>done.</info>', $table->getTableName()));
                        } else {
                            $output->writeln(sprintf('  == Table <info>%s:</info> <comment>nothing todo.</comment>', $table->getTableName()));
                        }
                        $this->renderTableSchema($output, $table)->render();
                    } else {
                        $output->writeln(sprintf('<comment>Warning: Mission table %s</comment>', $migration));
                    }
                }
                break;
            case 'dump':
                $tables = $schema->extract($tableName);
                foreach ($tables as $table) {
                    $output->writeln(sprintf('Table: <info>%s</info>', $table->getTableName()));
                    $this->renderTableSchema($output, $table)->render();
                }
                break;
        }

        return 0;
    }

    /**
     * @param OutputInterface $output
     * @param Table $table
     * @return SymfonyTable
     */
    protected function renderTableSchema(OutputInterface $output, Table $table)
    {
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

        return $t;
    }

    protected function dump(Table $table)
    {}
}