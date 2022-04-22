<?php
namespace Froiden\SqlGenerator\Console;
use Froiden\SqlGenerator\SqlFormatter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
class SqlCommand extends Command {
    protected $name = 'sql:generate';
    protected $description = 'convert Laravel migrations to raw SQL scripts';
    public function __construct()
    {
        parent::__construct();
    }
    public function handle()
    {
        // Create object of migration
        $migrator = app('migrator');
        // Now that we have the connections we can resolve it and pretend to run the
        // queries against the database returning the array of raw SQL statements
        // that would get fired against the database system for this migration.
        $db = $migrator->resolveConnection(null);

        $sourceDirectory = database_path(
            Str::of('migrations')
                ->append('/')
                ->append(config('sql_generator.sourceDirectory'))
                ->replace('//', '/')
        );

        $migrator->requireFiles($migrations = $migrator->getMigrationFiles($sourceDirectory));
        //
        $sql = "-- convert Laravel migrations to raw SQL scripts --\n";
        foreach($migrations as $migration) {
            // should we ignore this
            if (!empty(config('sql_generator.ignore'))) {
                $filename = last(explode('/', $migration));

                if (in_array($filename, config('sql_generator.ignore'))) {
                    continue;
                }
            }

            // First we will resolve a "real" instance of the migration class from this
            // migration file name. Once we have the instances we can run the actual
            // command such as "up" or "down", or we can just simulate the action.
            $migration_name = $migrator->getMigrationName($migration);
            $migration = $migrator->resolve($migration_name);
            $name = "";
            foreach($db->pretend(function() use ($migration) { $migration->up(); }) as $query){
                if($name != $migration_name){
                    $name = $migration_name;
                    $sql .="\n-- migration:".$name." --\n";
                }
                if(substr( $query['query'], 0, 11 ) === "insert into"){
                    $sql .= "-- insert data -- \n";
                    foreach ($query['bindings'] as $item) {
                        $query['query'] = str_replace_first("?", "'".$item."'" ,$query['query']);
                    }
                }
                $query['query'] = SqlFormatter::format($query['query'],false);
                $sql .= $query['query'].";\n";
            }
        }
        $dir =  Config::get('sql_generator.defaultDirectory');
        //Check directory exit or not
        if( is_dir($dir) === false )
        {
            // Make directory in database folder
            mkdir($dir);
        }
        // Pull query in sql file
        File::put($dir.'/database.sql', $sql);
        $this->comment("Sql script create successfully");
    }
}
