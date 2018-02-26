#!/bin/bash

# Script Constants
welsh=false

# Initial Language Selection
while true; do
    read -p "Welsh? / Cymraeg? " yn
    case $yn in
        [Yy]* ) welsh=true; echo "Cymraeg"; break;;
        [Nn]* ) welsh=false; echo "English"; break;;
        * ) echo "Yes/No";;
    esac
done

checkForComposer


checkForComposer() {
    if hash composer 2>/dev/null; then
        if welsh; then
            echo ""
        else
            echo "Composer found in PATH. Continuing."
        fi
        return 0
    fi


    if [ -f composer.phar ]; then
        if welsh; then
            echo ""
        else
            echo "Composer found in local directory. Continuing."
        fi
        return 1
    fi

    if welsh; then
        echo ""
    else
        echo "Composer is required for deployment. Please visit getcomposer.org for instructions."
    fi

    exit 1
}