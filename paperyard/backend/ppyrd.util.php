<?php

// \brief quick n dirty cli tool to do a bit of stuff
class Util
{
    const INSERT_ERROR = 'An error occurred while writing to the database.';

    private $db_link;

    public function __construct($argc, $argv)
    {
        echo 'Paperyard CLI Utility' . PHP_EOL;

        // check if args have been passed
        if ($argc <= 1) {
            echo 'usage: paperyard <realm>:<command> [<options>]' . PHP_EOL;
            echo 'use commands:list to get a full list' . PHP_EOL;
            exit;
        }

        $this->db_link = new PDO('sqlite:/data/database/paperyard.sqlite');

        $command = $argv[1];
        $options = array_slice($argv, 2);

        if ($this->hasCommand($command)) {
            $this->{$this->methodForCommand($command)}($options);
        } else {
            echo 'command not found' . PHP_EOL;
            exit;
        }
    }

    // \brief searches for a matching function
    private function hasCommand($command)
    {
        return method_exists($this, $this->methodForCommand($command));
    }

    // \brief creates method name from command
    private function methodForCommand($command)
    {
        $lowerCased = strtolower($command);
        $camelCased = ucwords(strtolower($lowerCased), ":");
        $substituted = str_replace(":", "", $camelCased);
        $methodName = 'command' . $substituted;
        return $methodName;
    }

    // \brief quits if options count mismatch
    private function assertOptionsCount($options, $int, $helptext = null)
    {
        $count = count($options);
        if ($count != $int)
        {
            echo sprintf('Expected %d parameters. %d given.', $int, $count) . PHP_EOL;
            if (is_string($helptext)) {
                echo $helptext . PHP_EOL;
            }
            exit;
        }
    }

    // \brief quits if not answerd with yes
    private function assertConfirm($default_yes = false)
    {
        $positive = ['y', 'yes'];
        $preset = $default_yes ? '[Y/n]' : '[N/y]';

        if ($default_yes) {
            $positive[] = "";
        }

        $answer = readline('Confirm ' . $preset . ': ');
        $answer_cleaned = strtolower(trim($answer));

        if (!in_array($answer_cleaned, $positive)) {
            exit;
        }
    }

    // \brief lists all users
    private function commandUsersList()
    {
        echo "users:list =>" . PHP_EOL;

        $statement = $this->db_link->prepare('SELECT * FROM users ORDER BY id');
        $statement->execute();
        $result = $statement->fetchAll();

        foreach ($result as $user) {
            echo str_pad($user['id'], 4) . $user['user'] . PHP_EOL;
        }
    }

    // \brief creates a new user
    private function commandUsersAdd($options)
    {
        $helptext = 'usage: paperyard users:add <username> <password>';

        $this->assertOptionsCount($options, 2, $helptext);

        $user = $options[0];
        $password = $options[1];

        echo 'users:add =>';
        echo str_pad("Username", 16) . $user . PHP_EOL;
        echo str_pad("Password", 16) . $password . PHP_EOL;

        $this->assertConfirm(true);

        $hash = password_hash($password, PASSWORD_BCRYPT);

        $statement = $this->db_link->prepare('INSERT INTO users(user, hash) VALUES(:user, :hash)');
        $result = $statement->execute(array(
            'user' => $user,
            'hash' => $hash
        ));

        if ($result === false) {
            echo self::INSERT_ERROR . PHP_EOL;
        }
    }

    private function commandUsersDelete($options)
    {
        $helptext = 'usage: paperyard users:delete <username>';

        $this->assertOptionsCount($options, 1, $helptext);

        $user = $options[0];

        echo 'users:delete =>'. PHP_EOL;
        echo str_pad("Username", 16) . $user . PHP_EOL;

        $this->assertConfirm();

        $statement = $this->db_link->prepare('DELETE FROM users WHERE user = :user');
        $result = $statement->execute(array(
            'user' => $user
        ));

        if ($result === false) {
            echo self::INSERT_ERROR . PHP_EOL;
        }
    }

    // \brief returns current database version
    private function commandDatabaseVersion()
    {
        echo 'database:version =>' . PHP_EOL;

        $statement = $this->db_link->prepare('SELECT * FROM config WHERE configVariable = :configVariable');
        $statement->execute(array(
            'configVariable' => 'databaseVersion'
        ));
        $result = $statement->fetchAll();

        echo $result[0]['configValue'] . PHP_EOL;
    }

    // \brief lists all config variables and there values
    private function commandDatabaseConfig()
    {
        echo 'database:config =>' . PHP_EOL;

        $statement = $this->db_link->prepare('SELECT * FROM config ORDER BY id');
        $statement->execute();
        $result = $statement->fetchAll();

        foreach ($result as $user) {
            echo str_pad($user['configVariable'], 32) . $user['configValue'] . PHP_EOL;
        }
    }

    // \brief starts native update process
    private function commandDatabaseUpdate()
    {
        echo 'database:update =>' . PHP_EOL;

        require 'dbHandler.php';

        // calling __construct will already call the update methode
        (new dbHandler());
    }

    // \brief lists all commands
    private function commandCommandsList()
    {
        $realms = array(
            'users' => array(
                'list' => 'lists all users',
                'add <username> <password>' => 'creates a new user',
                'delete <username>' => 'removes a user'
            ),
            'database' => array(
                'version' => 'returns current database version',
                'config' => 'lists all config variables and there values',
                'update' => 'starts native update process'
            ),
            'commands' => array(
                'list' => 'lists all commands'
            )
        );

        foreach ($realms as $realm => $commands) {
            echo $realm . PHP_EOL;
            foreach ($commands as $command => $desc) {
                echo '  ' . str_pad($command,32);
                echo $desc . PHP_EOL;
            }
            echo PHP_EOL;
        }
    }
}

// start
$util = new Util($argc, $argv);
