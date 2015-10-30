<?php

namespace RabbitmqTest;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Send extends Command
{
    protected function configure()
    {
        $this
            ->setName('task:send')
            ->setDescription('Envoi une tache')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Connection a RabbitMQ
        $connection = new AMQPStreamConnection('rabbitmq', 5672, 'guest', 'guest');
        $channel = $connection->channel();

        // Déclaration de la queue
        $channel->queue_declare(
            $queue = 'tasks', // Nom de la queue 
            $passive = false, // Peut être utilisé pour vérifier si une file d'attente existe sans réellement la créer
            $durable = true, // La queue survi au redémarage du serveur
            $exclusive = false, // il file d'attente est pas exclusif à la connexion
            $auto_delete = false // la file d'attente ne sera pas automatique supprimé
        );

        // Préparation des datas
        $data = [
            'class' => 'RabbitmqTest\Object',
            'id' => rand(1, 100),
            'methode' => 'genCacheMembers',
        ];

        // On json le tout
        $json = json_encode($data);

        // On Envoi le message dans la queu tasks
        $msg = new AMQPMessage(
            $json, 
            ['delivery_mode' => 2] // Persistence
        );
        $channel->basic_publish($msg, '', 'tasks');

        // Infos
        $output->writeln("<comment>#################################################</comment>");
        $output->writeln("<comment>Envoi du json : </comment>");
        $output->writeln("<info>".json_encode($data, JSON_PRETTY_PRINT)."</info>");
        $output->writeln("<comment>#################################################</comment>");

        // On ferme tous
        $channel->close();
        $connection->close();
    }
}
