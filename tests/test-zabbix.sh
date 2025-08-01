#!/bin/bash
set -x
# Test suite for multiflexi-cli with various commands
set -e

multiflexi-zabbix-lld | jq
multiflexi-zabbix-lld-tasks | jq
multiflexi-zabbix-lld-company | jq

