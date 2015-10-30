<?php

namespace RabbitmqTest;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Received extends Command
{
    protected function configure()
    {
        $this
            ->setName('task:receive')
            ->setDescription('Attrape une tache')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Connection a RabbitMQ
        $connection = new AMQPStreamConnection('rabbitmq', 5672, 'guest', 'guest');
        $channel = $connection->channel();
        $channel->basic_qos(null, 1, null); // On ne traite pas plus de 1 message à la fois

        // Déclaration de la queue
        $channel->queue_declare(
            $queue = 'tasks', // Nom de la queue 
            $passive = false, // Peut être utilisé pour vérifier si une file d'attente existe sans réellement la créer
            $durable = true, // La queue survi au redémarage du serveur
            $exclusive = false, // il file d'attente est pas exclusif à la connexion
            $auto_delete = false // la file d'attente ne sera pas automatique supprimé
        );

        $output->writeln("<fg=black;bg=cyan>####################################################</>");
        $output->writeln("<fg=black;bg=cyan>#### Waiting for messages. To exit press CTRL+C ####</>");
        $output->writeln("<fg=black;bg=cyan>####################################################</>");

        $callback = function($msg) use($output) {
            $output->writeln("");
            $output->writeln("--------------------------");
            $output->writeln("<comment>Data reçu : </comment>");
            
            $task = json_decode($msg->body, true);
            
            $output->writeln("<info>".json_encode($task, JSON_PRETTY_PRINT)."</info>");
            $output->writeln("");

            $output->writeln("<comment>Build de l'objet ".$task['class']." avec l'id ".$task['id']."</comment>");
            $object = New $task['class']($task['id']);
            $output->writeln("<comment>Execution de la méthode</comment>");
            $object->$task['methode']();
            $output->writeln("");
            $output->writeln("<comment>Fin de l'execution de la méthode</comment>");

            $output->writeln("<comment>Envoi a RabbitMQ que la message à était traité</comment>");
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };

        $channel->basic_consume(
            $queue = 'tasks', // Nom de la queue 
            $consumer_tag = '', 
            $no_local = false, 
            $no_ack = false, // Désactive la confirmation que le traitement c'est bien passé
            $exclusive = false, 
            $nowait = false, 
            $callback
        );

        while(count($channel->callbacks)) {
            $channel->wait();
        }
    }
}
