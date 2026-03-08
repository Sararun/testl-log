#!/bin/sh
# =============================================================================
# OpenSearch ISM Policies Init
# =============================================================================
# Применяет политики retention при старте стека.
# Запускается как отдельный init-контейнер после того, как OpenSearch готов.
#
# Политики:
#   - logs-retention:   удалять индексы logs-* после 45 дней
#   - jaeger-retention: удалять индексы jaeger-* после 7 дней
# =============================================================================

set -e

OPENSEARCH_URL="${OPENSEARCH_URL:-http://opensearch:9200}"

echo "Waiting for OpenSearch at ${OPENSEARCH_URL}..."
until curl -sf "${OPENSEARCH_URL}/_cluster/health" > /dev/null; do
    sleep 5
done
echo "OpenSearch is ready"

# -----------------------------------------------------------------------------
# Logs retention: 45 дней
# -----------------------------------------------------------------------------
curl -sf -X PUT "${OPENSEARCH_URL}/_plugins/_ism/policies/logs-retention" \
    -H 'Content-Type: application/json' \
    -d '{
        "policy": {
            "description": "Delete application logs after 45 days",
            "default_state": "hot",
            "states": [
                {
                    "name": "hot",
                    "actions": [],
                    "transitions": [
                        {
                            "state_name": "delete",
                            "conditions": { "min_index_age": "45d" }
                        }
                    ]
                },
                {
                    "name": "delete",
                    "actions": [{ "delete": {} }],
                    "transitions": []
                }
            ],
            "ism_template": [
                {
                    "index_patterns": ["logs-*"],
                    "priority": 100
                }
            ]
        }
    }'
echo "logs-retention policy applied"

# -----------------------------------------------------------------------------
# Jaeger traces retention: 7 дней
# -----------------------------------------------------------------------------
curl -sf -X PUT "${OPENSEARCH_URL}/_plugins/_ism/policies/jaeger-retention" \
    -H 'Content-Type: application/json' \
    -d '{
        "policy": {
            "description": "Delete Jaeger traces after 7 days",
            "default_state": "hot",
            "states": [
                {
                    "name": "hot",
                    "actions": [],
                    "transitions": [
                        {
                            "state_name": "delete",
                            "conditions": { "min_index_age": "7d" }
                        }
                    ]
                },
                {
                    "name": "delete",
                    "actions": [{ "delete": {} }],
                    "transitions": []
                }
            ],
            "ism_template": [
                {
                    "index_patterns": ["jaeger-*"],
                    "priority": 100
                }
            ]
        }
    }'
echo "jaeger-retention policy applied"

echo "ISM initialization complete"