sudo ant -u jenkins ant phpunit -f plugins/Postgres/vendor/Jenkins/build.xml

wget http://localhost:8080/jnlpJars/jenkins-cli.jar
/usr/lib/java/bin/java -jar jenkins-cli.jar -s http://localhost:8080/ create-job "CakePHP 3 Plugin Postgres" < "plugins/Postgres/vendor/Jenkins/jobs/CakePHP3-Postgres-Plugin.xml"
/usr/lib/java/bin/java -jar jenkins-cli.jar -s http://localhost:8080/ create-job "CakePHP 3 Plugin Postgres Quality" < "plugins/Postgres/vendor/Jenkins/jobs/CakePHP3-Postgres-Plugin-Quality.xml"