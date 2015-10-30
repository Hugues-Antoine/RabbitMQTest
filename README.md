# Test RabbitMQ avec des containers Docker

## Init

```
git clone https://github.com/Hugues-Antoine/RabbitMQTest.git
docker run --rm -it -v $(pwd):/$(pwd) -w /$(pwd)  huguesantoine/php bash -c "composer install"

```

## Commande

Démarrage du serveur RabbitMQ
```
docker run -d --hostname my-rabbit --name some-rabbit rabbitmq
```

Démarrage du premier consomateur
```
docker run --rm -it -v $(pwd):/$(pwd) -w /$(pwd) --link some-rabbit:rabbitmq --name received1 huguesantoine/php bash -c "./app.php task:receive"
```

Démarrage du deuxiéme consomateur
```
docker run --rm -it -v $(pwd):/$(pwd) -w /$(pwd) --link some-rabbit:rabbitmq --name received2 huguesantoine/php bash -c "./app.php task:receive"
```

Envoi d'un message
```
docker run --rm -it -v $(pwd):/$(pwd) -w /$(pwd) --link some-rabbit:rabbitmq  huguesantoine/php bash -c "./app.php task:send"
```
