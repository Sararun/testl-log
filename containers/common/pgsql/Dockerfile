FROM postgres:16.0
USER postgres
RUN whoami

ADD ./containers/common/pgsql/scripts/01-init.sh /docker-entrypoint-initdb.d/

ENTRYPOINT ["docker-entrypoint.sh"]
EXPOSE 5432
CMD ["postgres"]
