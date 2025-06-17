#!/bin/bash
# Test suite for multiflexi-cli company CRUD with all OpenAPI fields

set -e

# Create a company with all fields
multiflexi-cli company create \
  --name="TestCo" \
  --enabled=true \
  --settings='{"theme":"blue"}' \
  --logo="logo.png" \
  --server=1 \
  --ic="12345678" \
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
