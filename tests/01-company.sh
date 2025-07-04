#!/bin/bash
# Test suite for multiflexi-cli company CRUD with all OpenAPI fields

set -e

# Generate a random IC number for repeatable tests
IC_NUM=$((RANDOM % 90000000 + 10000000))

# Test: Get non-existent company by IC, expect JSON not found response
NOTFOUND_JSON=$(multiflexi-cli company get --ic "$IC_NUM" --verbose --format json)
echo "$NOTFOUND_JSON" | jq -e '.status == "not found" and .message == "No company found with given IC"' > /dev/null || {
  echo "Test failed: Expected not found JSON response for non-existent company" >&2
  exit 1
}
echo "Not found test passed."

# Create a company with all fields
multiflexi-cli company create \
  --name="TestCo" \
  --enabled=true \
  --settings='{"theme":"blue"}' \
  --logo="logo.png" \
  --server=1 \
  --ic="$IC_NUM" \
  --company="TESTCO" \
  --rw=true \
  --setup=false \
  --webhook=true \
  --DatCreate="2025-06-17T12:00:00Z" \
  --DatUpdate="2025-06-17T12:00:00Z" \
  --customer=42 \
  --email="testco@example.com" \
  --code="TST01" \
  --format=json > company_create.json

COMPANY_ID=$(jq -r '.company_id' company_create.json)
echo "Created company with ID: $COMPANY_ID"

# Get the company
multiflexi-cli company get --id="$COMPANY_ID" --format=json

# Update the company
multiflexi-cli company update --id="$COMPANY_ID" --name="TestCo Updated" --enabled=false --format=json

# Get the updated company
multiflexi-cli company get --id="$COMPANY_ID" --format=json

# Remove the company
multiflexi-cli company remove --id="$COMPANY_ID" --format=json

echo "Company CRUD test completed."
