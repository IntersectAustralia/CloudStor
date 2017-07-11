#!/bin/sh
nohup java -jar bin/selenium/selenium-server-standalone-2.41.0.jar > ./selenium.log &
echo 
echo 'Selenium Server started. Remember to shutdown later!'
echo 
