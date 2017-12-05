<?php

namespace RachidLaasri\LaravelInstaller\Helpers;

use Exception;
use Illuminate\Database\SQLiteConnection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Output\BufferedOutput;

class DatabaseManager
{
    /**
     * Migrate and seed the database.
     *
     * @return array
     */
    public function install()
    {
        $outputLog = new BufferedOutput;

        $this->sqlite($outputLog);
        $this->migrate($outputLog);

        return $this->migrate($outputLog);
    }

    /**
     * Run the migration and call the seeder.
     *
     * @param BufferedOutput $outputLog
     * @return array
     */
    private function migrate($outputLog)
    {
        try{
            Artisan::call('migrate', ["--force"=> true], $outputLog);
        }
        catch(Exception $e){
            return $this->response($e->getMessage(), $outputLog);
        }

        return $this->response(trans('installer_messages.final.finished'), $outputLog, 'success');
    }

    /**
     * Return a formatted error messages.
     *
     * @param $message
     * @param string $status
     * @param BufferedOutput $outputLog
     * @return array
     */
    private function response($message, $outputLog, $status = 'danger')
    {
        return [
            'status' => $status,
            'message' => $message,
            'dbOutputLog' => $outputLog->fetch()
        ];
    }

    /**
     * check database type. If SQLite, then create the database file.
     *
     * @param BufferedOutput $outputLog
     */
    private function sqlite($outputLog)
    {
        if(DB::connection() instanceof SQLiteConnection) {
            $database = DB::connection()->getDatabaseName();
            if(!file_exists($database)) {
                touch($database);
                DB::reconnect(Config::get('database.default'));
            }
            $outputLog->write('Using SqlLite database: ' . $database, 1);
        }
    }
}
