<?php

namespace App\Commands;

use App\Models\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateUserCommand extends Command
{

    protected static $defaultName = 'app:create-user';

    protected function configure()
    {
        $this->addArgument('email', InputArgument::REQUIRED, 'The email of the user')
            ->addArgument('pass', InputArgument::OPTIONAL, 'The password of the user');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $email = $input->getArgument('email');
        $pass = $input->getArgument('pass') ?? '123456';
        $output->writeln([
            'User Creator',
            '============',
            '']);
        $user = new User();
        $user->email = $email;
        $user->password = password_hash($pass, PASSWORD_DEFAULT);
        $user->save();
        $output->writeln('Done.');

        return 0;
    }
}