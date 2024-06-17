<?php 

namespace Console;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Process\Process;

class InstallCommand extends Command
{
    protected $config;
    
	protected function configure() {
		$this
			->setName('app:install')
			->setDescription('Prepare environment file')
            
            ->addOption('db-name', null, InputOption::VALUE_REQUIRED, 'The real name of the database')
			->addOption('db-user', null, InputOption::VALUE_REQUIRED, 'The user key for the database')
			->addOption('db-password', null, InputOption::VALUE_REQUIRED, 'The password for accessing to the database')
			->addOption('db-host', null, InputOption::VALUE_REQUIRED, 'The host key for the database')
			->addOption('db-prefix', null, InputOption::VALUE_REQUIRED, 'The prefix for tables in the database')
            
            ->addOption('wp-env', null, InputOption::VALUE_REQUIRED, 'Define the environment (development, staging, production')
			->addOption('wp-home', null, InputOption::VALUE_REQUIRED, 'Define the root dns of the website')
			->addOption('wp-title', null, InputOption::VALUE_REQUIRED, 'Define the title')
			->addOption('wp-admin-user', null, InputOption::VALUE_REQUIRED, 'Define the username for admin')
			->addOption('wp-admin-password', null, InputOption::VALUE_REQUIRED, 'Define the default password for admin')
			->addOption('wp-admin-email', null, InputOption::VALUE_REQUIRED, 'Define the email for admin')
            
			->addOption('env-local-uri', null, InputOption::VALUE_REQUIRED, 'Set the local uri')
			->addOption('env-live-uri', null, InputOption::VALUE_REQUIRED, 'Set the live uri')
			->addOption('env-staging-uri', null, InputOption::VALUE_REQUIRED, 'Set the staging uri')
			->addOption('env-current', null, InputOption::VALUE_REQUIRED, 'Set the current uri (LOCAL, LIVE, STAGING)')
			->addOption('env-is-multisite', null, InputOption::VALUE_REQUIRED, 'Define if the website is a multisite environment')
			->addOption('domain-current-site', null, InputOption::VALUE_REQUIRED, 'Set the current domain for multisite')
			->addOption('env-site-install', null, InputOption::VALUE_REQUIRED, 'Set the current domain for multisite')
			->addOption('use-subdomain', null, InputOption::VALUE_REQUIRED, 'Define if multisite use subdomain or subfolder')
			->setHelp(<<<EOF
The <info>%command.name%</info> command creates the config file for the wordpress
EOF
			);
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$questionHelper = $this->getHelper('question');
		$this->checkAndCreateConfigFile($input, $output);
	}

	protected function checkAndCreateConfigFile(InputInterface $input, OutputInterface $output) {
        if (!\file_exists(__DIR__ . "/../../../.env")) {
            $output->writeln("<error>.env not found.</error>");
            $output->writeln("<info>Initialize creation .env</info>");
            $this->config = \file_get_contents(__DIR__ . "/../../../.env.example");

            $this->configDatabase($input,$output);
            $this->configWordpress($input,$output);
            $this->configEnvironment($input,$output);
            // $this->configAdmin($input,$output);

            \file_put_contents(__DIR__ . "/../../../.env", $this->config);
        } else {
            $output->writeln("<success>.env found.</success>");
        }
	}

	protected function configAdmin(InputInterface $input, OutputInterface $output) {
		$questionHelper = $this->getHelper('question');
        foreach ([
            "admin-user" => "ffaltin",
            "admin-password" => false,
            "admin-email" => "ffaltin@alpaga.agency",
        ] as $key => $value) {
            $question = new Question(sprintf('<question>Database %s</question>: ', $key." ".($value!=false?"({$value})":"")), $value);
            if ($key == "admin-password") {
                $question->setHidden(true);
                $question->setHiddenFallback(false);
            }
            $answer = $questionHelper->ask(
                $input, 
                $output,
                $question
            );
            $input->setOption("wp-".$key,$answer);
            $this->config = str_replace("__".str_replace("-","_", "wp-".$key), $input->getOption("wp-".$key), $this->config);
        }

		return true;
    }
    
    protected function configDatabase(InputInterface $input, OutputInterface $output) {
		$questionHelper = $this->getHelper('question');
        foreach ([
            "name" => false,
            "user" => false,
            "password" => false,
            "host" => "localhost",
            "prefix" => "lmb_",
        ] as $key => $value) {
            $question = new Question(sprintf('<question>Database %s</question>: ', $key." ".($value!=false?"({$value})":"")), $value);
            if ($key == "password") {
                $question->setHidden(true);
                $question->setHiddenFallback(false);
            }
            $answer = $questionHelper->ask(
                $input, 
                $output,
                $question
            );
            $input->setOption("db-".$key,$answer);
            if ($key == "name" && !$answer) {
                $output->writeln(sprintf("<error>No argument found for '%s'</error>", "db-" . $key));
                die();
            }

            $this->config = str_replace("__".str_replace("-","_", "db-".$key), $input->getOption("db-".$key), $this->config);
        }

		return true;
    }
    
	protected function configEnvironment(InputInterface $input, OutputInterface $output) {
        $questionHelper = $this->getHelper('question');
        $raw = ["domain-current-site", "use-subdomain"];
        foreach ([
            "current" => "LOCAL",
            "live-uri" => false,
            "staging-uri" => false,
            "is-multisite" => false,
            "site-install" => false,
            "use-subdomain" => true,
        ] as $key => $value) {
            
            if (in_array($key, ["site-install", "use-subdomain" ]) && $input->getOption("env-is-multisite") == "false") {
                $this->config = str_replace([
                    "__use_subdomain",
                    "__env_site_install",
                ], [
                    "false",
                    "wp/install/singlesite",
                ], $this->config);
                continue;
            } else {
                $input->setOption("env-site-install","wp/install/multisite");
            }

            if ($key != "site-install") {
                switch ($key) {
                    case "current":
                        $question = new ChoiceQuestion(
                            '<question>Environment: Please select the environment for the website</question>',
                            ["LOCAL", "STAGING", "LIVE"]
                        );
                        $question->setErrorMessage('The environment %s is invalid.');
                        $answer = $questionHelper->ask($input, $output, $question);
                    break;
                    case "is-multisite":
                    case "use-subdomain":
                        $question = new ChoiceQuestion(
                            sprintf('<question>Environment: Please select the value for %s</question>', $key),
                            ["true", "false", ]
                        );
                        $question->setErrorMessage('The value %s is invalid.');
                        $answer = $questionHelper->ask($input, $output, $question);
                    break;
                    default:
                        $answer = $questionHelper->ask(
                            $input, 
                            $output,
                            new Question(sprintf('<question>Environment %s</question>: ', $key." ".($value!=false?"({$value})":"")), $value)
                        );
                    break;
                }
                $input->setOption((in_array($key, $raw) ? "" : "env-").$key,$answer);
            }
            $this->config = str_replace("__".str_replace("-","_", (in_array($key, $raw) ? "" : "env-").$key), $input->getOption((in_array($key, $raw) ? "" : "env-").$key), $this->config);
        }
        $input->setOption("env-local-uri", sprintf("%s.local",$input->getOption("env-live-uri")));
        $input->setOption("domain-current-site", $input->getOption(sprintf("env-%s-uri", strtolower($input->getOption("env-current")))));
        $this->config = str_replace("__env_local_uri", $input->getOption("env-local-uri"), $this->config);
        $this->config = str_replace("__domain_current_site", $input->getOption(sprintf("env-%s-uri", strtolower($input->getOption("env-current")))), $this->config);
        //
        $question = new ChoiceQuestion(
            '<question>Please select the Scheme for the website</question>',
            ["http", "https"]
        );
        $question->setErrorMessage('The scheme %s is invalid.');
        $scheme = $questionHelper->ask($input, $output, $question);

        $this->config = str_replace(
            "__wp_home", 
            sprintf("%s://%s", 
                $scheme, 
                $input->getOption(sprintf("env-%s-uri", strtolower($input->getOption("env-current"))))
            ), 
            $this->config
        );

		return true;
    }
    
	protected function configWordpress(InputInterface $input, OutputInterface $output) {
		$questionHelper = $this->getHelper('question');

        foreach ([
            "env" => "development",
            "title" => "Lumber",
        ] as $key => $value) {
            if ($key == "env") {
                $question = new ChoiceQuestion(
                    '<question>Wordpress: Please select the environment for the website</question>',
                    ["development", "staging", "production"]
                );
                $question->setErrorMessage('The environment %s is invalid.');
                $answer = $questionHelper->ask($input, $output, $question);
            } else {
                $answer = $questionHelper->ask(
                    $input, 
                    $output,
                    new Question(sprintf('<question>Wordpress %s</question>: ', $key." ".($value!=false?"({$value})":"")), $value)
                );
            }

            $input->setOption("wp-".$key,$answer);
            $this->config = str_replace("__".str_replace("-","_", "wp-".$key), $input->getOption("wp-".$key), $this->config);
        }

		return true;
    }
    
}
