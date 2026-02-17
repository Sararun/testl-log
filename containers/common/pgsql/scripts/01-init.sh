#!/bin/bash
set -e

psql -v ON_ERROR_STOP=1 --username "postgres" --dbname "pgdb" <<-EOSQL
    CREATE DATABASE pim;
    GRANT ALL PRIVILEGES ON DATABASE pim TO postgres;
EOSQL