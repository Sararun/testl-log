#!/bin/bash

set -e

psql -v ON_ERROR_STOP=1 --username "postgres" --dbname "pim" <<-EOSQL
    CREATE EXTENSION IF NOT EXISTS pg_trgm;
EOSQL