#!/bin/bash

function load_configuration()
{
    configuration_file='config/paperyard'

    if [ -r ${configuration_file} ]; then
        while IFS='=' read lhs rhs
        do
            if [[ ! $lhs =~ ^\ *# && -n $lhs ]]; then
                rhs="${rhs%%\#*}"    # Del in line right comments
                rhs="${rhs%%*( )}"   # Del trailing spaces
                rhs="${rhs%\"*}"     # Del opening string quotes
                rhs="${rhs#\"*}"     # Del closing string quotes
                eval "${lhs}='${rhs}'"
            fi
        done < <(envsubst < $configuration_file)
    else
        echo 'No configuration found. Will load defaults.'
    fi
}

function load_defaults()
{
    : ${paperyard_root:=$HOME/Paperyard/}
    : ${paperyard_scan:=scan/}
    : ${paperyard_inbox:=inbox/}
    : ${paperyard_outbox:=outbox/}
    : ${paperyard_sort:=sort/}
    : ${paperyard_database:=data/database/}
    : ${paperyard_port:=80}
    : ${paperyard_development:=false}
}

function start_development()
{
    docker run --name ppyrd --rm \
          -v "$(pwd)/paperyard:/var/www/html/" \
          -v "${paperyard_root}/${paperyard_database}:/data/database" \
          -v "${paperyard_root}/${paperyard_scan}:/data/scan" \
          -v "${paperyard_root}/${paperyard_inbox}:/data/inbox" \
          -v "${paperyard_root}/${paperyard_outbox}:/data/outbox" \
          -v "${paperyard_root}/${paperyard_sort}:/data/sort" \
          -p "${paperyard_port}:80" \
          -e COMMIT_COUNT=$(git rev-list --count MASTER) \
          -i -t ppyrd_image
}

function start_production()
{
    docker run --name ppyrd --rm \
          -v "${paperyard_root}/${paperyard_database}:/data/database" \
          -v "${paperyard_root}/${paperyard_scan}:/data/scan" \
          -v "${paperyard_root}/${paperyard_inbox}:/data/inbox" \
          -v "${paperyard_root}/${paperyard_outbox}:/data/outbox" \
          -v "${paperyard_root}/${paperyard_sort}:/data/sort" \
          -p "${paperyard_port}:80" \
          -i -t ppyrd_image
}

function splash()
{
    echo 'Paperyard'

    if ${paperyard_development}; then
        echo 'Environment: development'
    else
        echo 'Environment: production'
    fi

    echo "Port: ${paperyard_port}"
}

function main()
{
    load_configuration

    load_defaults

    splash

    if ${paperyard_development}; then
        start_development
    else
        start_production
    fi
}

main