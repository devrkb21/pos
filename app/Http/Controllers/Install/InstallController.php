<?php

namespace App\Http\Controllers\Install;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Output\BufferedOutput;

use App\Utils\InstallUtil;
use Illuminate\Support\Facades\DB;
use Composer\Semver\Comparator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;

class InstallController extends Controller
{
    protected $outputLog;
    protected $appVersion;
    protected $macActivationKeyChecker;

    public function __construct()
    {
        $this->appVersion = config('author.app_version');
        $this->env = config('app.env');

        //Check if mac based activation key is required or not - Kept for compatibility
        $this->macActivationKeyChecker = false;
        if (file_exists(__DIR__ . '/MacActivationKeyChecker.php')) {
            include_once(__DIR__ . '/MacActivationKeyChecker.php');
            $this->macActivationKeyChecker = $mac_is_enabled;
        }

        $this->installSettings();
    }

    private function installSettings()
    {
        config(['app.debug' => true]);
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
    }

    private function isInstalled()
    {
        $envPath = base_path('.env');
        if (file_exists($envPath)) {
            abort(404);
        }
    }

    private function deleteEnv()
    {
        $envPath = base_path('.env');
        if ($envPath && file_exists($envPath)) {
            unlink($envPath);
        }
        return true;
    }

    public function index()
    {
        $this->isInstalled();
        $this->installSettings();
        return view('install.index');
    }

    public function checkServer()
    {
        $this->isInstalled();
        $this->installSettings();
        $output = [];
        $output['php'] = (PHP_MAJOR_VERSION >= 7 && PHP_MINOR_VERSION >= 1) ? true : false;
        $output['php_version'] = PHP_VERSION;
        $output['openssl'] = extension_loaded('openssl') ? true : false;
        $output['pdo'] = extension_loaded('pdo') ? true : false;
        $output['mbstring'] = extension_loaded('mbstring') ? true : false;
        $output['tokenizer'] = extension_loaded('tokenizer') ? true : false;
        $output['xml'] = extension_loaded('xml') ? true : false;
        $output['curl'] = extension_loaded('curl') ? true : false;
        $output['zip'] = extension_loaded('zip') ? true : false;
        $output['gd'] = extension_loaded('gd') ? true : false;
        $output['storage_writable'] = is_writable(storage_path());
        $output['cache_writable'] = is_writable(base_path('bootstrap/cache'));
        $output['next'] = $output['php'] && $output['openssl'] && $output['pdo'] && $output['mbstring'] && $output['tokenizer'] && $output['xml'] && $output['curl'] && $output['zip'] && $output['gd'] && $output['storage_writable'] && $output['cache_writable'];

        return view('install.check-server')->with(compact('output'));
    }

    public function details()
    {
        $this->isInstalled();
        $this->installSettings();
        $env_example = base_path('.env.example');
        if (!file_exists($env_example)) {
            die("<b>.env.example file not found in <code>$env_example</code></b> <br/><br/> - In the downloaded codebase you will find .env.example file, please upload it and refresh this page.");
        }
        return view('install.details')->with('activation_key', $this->macActivationKeyChecker);
    }

    public function postDetails(Request $request)
    {
        $this->isInstalled();
        $this->installSettings();
        try {
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', '512M');
            
            // Validation for license key has been REMOVED
            $validatedData = $request->validate(
                [
                    'APP_NAME' => 'required',
                    'DB_DATABASE' => 'required',
                    'DB_USERNAME' => 'required',
                    'DB_PASSWORD' => 'required',
                    'DB_HOST' => 'required',
                    'DB_PORT' => 'required'
                ],
                [
                    'APP_NAME.required' => 'App Name is required',
                    'DB_DATABASE.required' => 'Database Name is required',
                    'DB_USERNAME.required' => 'Database Username is required',
                    'DB_PASSWORD.required' => 'Database Password is required',
                    'DB_HOST.required' => 'Database Host is required',
                    'DB_PORT.required' => 'Database port is required',
                ]
            );

            $this->outputLog = new BufferedOutput;

            // The BROTHERIT_LICENSE_CODE will be collected but not used for validation.
            $input = $request->only(['APP_NAME', 'APP_TITLE', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD', 'BROTHERIT_LICENSE_CODE', 'MAIL_DRIVER', 'MAIL_FROM_ADDRESS', 'MAIL_FROM_NAME', 'MAIL_HOST', 'MAIL_PORT', 'MAIL_ENCRYPTION', 'MAIL_USERNAME', 'MAIL_PASSWORD']);

            $input['APP_DEBUG'] = "false";
            $input['APP_URL'] = url("/");
            $input['APP_ENV'] = 'live';

            $mysql_link = @mysqli_connect($input['DB_HOST'], $input['DB_USERNAME'], $input['DB_PASSWORD'], $input['DB_DATABASE'], $input['DB_PORT']);
            if (mysqli_connect_errno()) {
                $msg = "<b>ERROR</b>: Failed to connect to MySQL: " . mysqli_connect_error();
                $msg .= "<br/>Provide correct details for 'Database Host', 'Database Port', 'Database Name', 'Database Username', 'Database Password'.";
                return redirect()->back()->with('error', $msg);
            }
            
            $envPathExample = base_path('.env.example');
            $envPath        = base_path('.env');
            $env_lines      = file($envPathExample);
            
            // Bypassed license logic from previous step
            foreach ($env_lines as $key => $line) {
                foreach ($input as $index => $value){
                    if (strpos($line, $index) !== false) {
                        $env_lines[$key] = $index . '="' . $value . '"' . PHP_EOL;
                    }
                }
                
                if (strpos($line, 'POS_BOOT_TIME') !== false) {
                    $env_lines[$key] = 'POS_BOOT_TIME' . '="' . time() . '"' . PHP_EOL;
                }
                elseif( strpos($line, 'POS_BOOT_TYPE') !== false ){
                    $env_lines[$key] = 'POS_BOOT_TYPE' . '="' . 1 . '"' . PHP_EOL;
                }
            }
            
            $envContent  = implode('', $env_lines);

            // Forcing success path
            file_put_contents($envPath, $envContent);
            $this->resetDbConnection($input);
            $this->runArtisanCommands();
            return redirect()->route('install.success');
            
        } catch (Exception $e) {
            $this->deleteEnv();
            return redirect()->back()->with('error', 'Something went wrong, please try again!! ' . $e->getMessage());
        }
    }

    private function resetDbConnection($input)
    {
        DB::purge('mysql');
        Config::set('database.connections.mysql.database', $input['DB_DATABASE']);
        Config::set('database.connections.mysql.username', $input['DB_USERNAME']);
        Config::set('database.connections.mysql.password', $input['DB_PASSWORD']);
        Config::set('database.connections.mysql.host', $input['DB_HOST']);
        Config::set('database.connections.mysql.port', $input['DB_PORT']);
        DB::reconnect('mysql');
    }

    private function runArtisanCommands()
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '512M');
        $this->installSettings();
        DB::statement('SET default_storage_engine=INNODB;');
        Artisan::call('migrate:fresh', ["--force" => true]);
        Artisan::call('db:seed', ["--force" => true]);
    }

    public function installAlternate(Request $request){ /* ... Omitted for brevity ... */ }

    public function success()
    {
        // Add a file to indicate installation is complete
        $installed_file = storage_path('installed');
        if (!file_exists($installed_file)) {
            touch($installed_file);
        }
        
        return view('install.success');
    }

    public function updateConfirmation(){ /* ... Omitted for brevity ... */ }

    //Updating
    public function update(Request $request)
    {
        try {
            DB::beginTransaction();
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', '512M');

            // --- LICENSE CHECK REMOVED from update method ---
            /*
            $input = $request->only(['BROTHERIT_LICENSE_CODE']);
            $pos_boot = pos_boot(config('app.url'), __DIR__, $input['BROTHERIT_LICENSE_CODE']);
            if (empty($pos_boot['install_type'])) {
                return redirect('login')->with('error', $pos_boot['err']);
            }
            */

            $installUtil = new installUtil();
            $db_version = $installUtil->getSystemInfo('db_version');

            if (Comparator::greaterThan($this->appVersion, $db_version)) {
                ini_set('max_execution_time', 0);
                ini_set('memory_limit', '512M');
                $this->installSettings();
                DB::statement('SET default_storage_engine=INNODB;');
                Artisan::call('migrate', ["--force" => true]);
                Artisan::call('module:publish');
                $installUtil->setSystemInfo('db_version', $this->appVersion);
            } else {
                abort(404);
            }
           
            DB::commit();

            $output = ['success' => 1, 'msg' => 'Updated Succesfully to version ' . $this->appVersion . ' !!'];
            return redirect('login')->with('status', $output);

        } catch (Exception $e) {
            DB::rollBack();
            die($e->getMessage());
        }
    }

    // Disabled this method by making it redirect home
    public function forceCheck()
    {
        return redirect('/home');
    }

    // Disabled this method by making it redirect home
    public function forceUpdate()
    {
        return redirect('/home');
    }
}