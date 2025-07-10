#!/bin/bash
set -x
# Test suite for multiflexi-cli with various commands
set -e

multiflexi-cli application list
multiflexi-cli runtemplate list

multiflexi-cli user create --login test --email test@multiflexi.eu --plaintext secret
multiflexi-cli user update --login test --email changed@multiflexi.exu --plaintext test
multiflexi-cli user list

multiflexi-cli user get --login test
multiflexi-cli user get --login test --fields login,email,company

multiflexi-cli company create --name "Test Company" --email company@multiflexi.eu --slug testco
multiflexi-cli company list

# multiflexi-cli runtemplate create --name "Test Template" --uuid 868a8085-03e5-4f9b-899d-2084e1de7d3b --company-slug testco --company-id 1
multiflexi-cli runtemplate list

multiflexi-zabbix-lld | jq
multiflexi-zabbix-lld-tasks | jq
multiflexi-zabbix-lld-company | jq
multiflexi-cli appstatus

# Run template with parameters
# multiflexi-run-template --uuid 868a8085-03e5-4f9b-899d-2084e1de7d3b --company-slug testco --company-id 1 --run-params '{"param1":"value1","param2":"value2"}'


# Delete action tests
multiflexi-cli user delete --login test --format json
multiflexi-cli user list

multiflexi-cli queue list
multiflexi-cli queue truncate
multiflexi-cli queue list



multiflexi-cli prune --logs --jobs
