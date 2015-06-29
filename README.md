# arbeider
Simple PHP driven Auto-deployer. This was designed for the use of codeship's webhooks. It consits of a webhook index.php file and a rx.php that you run on your servers. It requires [RabbitMQ](http://rabbitmq.com). 

### How it works
It works by codeship on a Successful build triggering a webhook url http://myhost/webhook/?apikey=myawesomesupersecretkey . When a request is passed, the index.php will check the apikey, environment variable for codeships project id, if all matches and the message from codeship contains status for successful, then we notify the consumers using a rabbitmq exchange. Once they get the notification, they run a update.sh script.

### what you need to bring
Your own update.sh script. This only needs to run where ever you have a rx.php running. 
