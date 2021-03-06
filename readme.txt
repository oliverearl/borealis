Borealis Application - Major Project CS39440
***********************
Oliver Earl
ole4@aber.ac.uk

Just want to use a live version of the program and save the hassle of deploying? A live instance of the program is
running on my Central webspace:
http://users.aber.ac.uk/ole4/magno

***********************
Deployment Instructions
***********************
If you are marking this work and have received this archive from TurnItIn/Blackboard, all files necessary for running
the application are in the directory, including 3rd-party libraries and compiled phpDocumentor documentation.

If you are installing this after pulling the Git repository, you will need to run 'composer install' in order to
fetch and install all necessary dependencies and to generate an autoload file that the program depends upon. If
you do not have Composer, please follow the instructions on https://getcomposer.org/ Please note that I have not
had any success in getting Composer to run on Central.

Please begin by entering the requirement information on the 'settings_blank.php' file located in ./storage/settings
and then rename it to 'settings.php'.

When uploading it to a PHP server, please ensure that you're using at least PHP 5.6 and all required file permissions
are set. Running 'fixwebperms' on Central can quickly fix this, or you can do it yourself.

*** THE PROGRAM ABSOLUTELY MUST RUN ON THE UNIVERSITY NETWORK TO ENSURE CONNECTIVITY WITH THE MAGNETOMETER ***

If you want access to all previous data from the magnetometer, you should open 'seed.php' in a text editor, fill in the
$username and $password variables with your Aber username and password, and run the script at the command prompt.
You can either do this by using the PHP prompt directly, or using Composer and typing 'composer seed'. This will go
ahead and download all data available and store it into your database. IT IS ESSENTIAL THAT YOU HAVE CONFIGURED
YOUR 'settings.php' file or you will encounter an unrecoverable PDOException.

Once seeding is complete, you will have a full set of data and the program and API are ready to use.

***********************
Directory Run-Down
***********************
assets(fonts, images, scripts, styles) - Static resources used by the program.
doc - Documentation generated by phpDocumentor.
locale - JSON files containing locale data, English and Welsh, used by the program
src - Source code
storage - Subdirectories used for temporarily storing processed CSV files, error logfiles and a retrieval log file, as
well as the settings file
templates - Source code
tests - PHPUnit tests
vendor - 3rd-party libraries, tools provided by Composer

Do not worry about the blank index.html files - they are there to prevent directory navigation.
***********************
Running Tests
***********************
You will need Composer - run the command 'composer test' to run all PHPUnit tests.
You can also run 'composer test-verbose' for more detailed verbose output.
***********************
Running Linter
***********************
You will need Composer - run the command 'composer lint' to run PHPMD.
It will output to stdout so output can be piped.
***********************
Viewing phpDocumentor Documentation
***********************
Navigate to the docs folder, and open index.html in your web browser.
If the folder is missing, download the phpDocumentor PHAR from http://docs.phpdoc.org/getting-started/installing.html
to generate documentation.
***********************
Troubleshooting
***********************
The Git repository for the application is https://bitbucket.org/oliverearl/mmp/. It will remain private until
marking is complete, but if access is required, please email me.

If you are having further trouble getting the program working, please get in touch and I will try my best to help.
