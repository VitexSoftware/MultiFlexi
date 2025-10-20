# 🗺️ MultiFlexi GDPR Phase 1: Data Flow and Third-Party Integration Mapping

> **Status**: Complete  
> **Date**: October 20, 2025  
> **Version**: 1.0  
> **GitHub Issue**: [#55](https://github.com/VitexSoftware/MultiFlexi/issues/55)

## 📊 Executive Summary

This document maps comprehensive data flows and third-party integrations within the MultiFlexi ecosystem. It identifies all external data transfers, international data movements, and inter-component communications for GDPR compliance assessment.

**Key Findings:**
- **53+ third-party applications** with external API connections
- **15+ external service integrations** (banking, accounting, cloud services)
- **Multi-component architecture** with complex data flows
- **International data transfers** to EU/US/cloud providers
- **No explicit data transfer agreements** currently in place

## 🏗️ MultiFlexi Component Architecture

### Core System Components
```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│  MultiFlexi     │    │ MultiFlexi-CLI  │    │MultiFlexi-Server│
│  (Web Interface)│◄──►│ (Management)    │◄──►│  (REST API)     │
└─────────┬───────┘    └─────────┬───────┘    └─────────┬───────┘
          │                      │                      │
          ▼                      ▼                      ▼
┌───────────────────────────────────────────────────────────────┐
│           MultiFlexi-Database (MySQL/PostgreSQL)              │
└─────────┬───────────────────────────────────────────────┬─────┘
          │                                               │
          ▼                                               ▼
┌─────────────────┐                            ┌────────────────┐
│ MultiFlexi-     │                            │ Application    │
│ Executor        │                            │ Registry       │
│ (Job Execution) │                            │ (53+ Apps)     │
└─────────────────┘                            └────────────────┘
```

### Inter-Component Data Flows

#### 1. Web Interface → Database
**Personal Data Transmitted:**
- User authentication credentials
- Company information and associations
- Application configurations containing credentials
- Job execution parameters and logs

**Data Flow Process:**
```
User Input → Form Processing → Database Storage
├─── User Registration (email, name, login)
├─── Company Creation (name, email, business data)
├─── Credential Management (API keys, passwords)
└─── Job Configuration (execution parameters)
```

#### 2. CLI → Database
**Personal Data Transmitted:**
- Administrative operations on user accounts
- Company and application management
- Bulk import/export operations

**Key Commands with Personal Data:**
- `multiflexi-cli company create --name "Company" --email "user@example.com"`
- `multiflexi-cli application import-json` (may contain credentials)
- `multiflexi-cli user create` (personal information)

#### 3. Executor → External APIs
**Critical Data Transfer Point:**
- Executes applications that connect to third-party services
- Injects environment variables containing credentials
- Logs execution results including potential personal data

#### 4. REST API → External Clients
**Data Exposure:**
- User authentication via HTTP Basic Auth
- Company and application data via JSON APIs
- Job execution status and logs

## 🌐 Third-Party Integration Analysis

### Banking and Financial Services

#### 1. Česká Spořitelna (CSAS) Integration
**Applications:** `csas-sharepoint.app.json`, `cs_statement_downloader.multiflexi.app.json`

**Personal/Business Data Transferred:**
- Bank account numbers (IBAN)
- Transaction details and amounts
- Company financial data
- Account holder information

**API Endpoints:**
- CSAS Open Banking API
- Sandbox/Production environments

**GDPR Risks:**
- Financial data transfer to third parties
- Account information storage
- Cross-border data transfer (CSAS → SharePoint)

#### 2. FIO Bank Integration
**Applications:** `fiobank_statement_downloader.multiflexi.app.json`

**Data Transferred:**
- FIO Bank API tokens
- Account numbers and statements
- Transaction history
- Customer financial data

#### 3. Raiffeisenbank Integration
**Applications:** `abraflexi_raiffeisenbank_*.multiflexi.app.json`

**Data Transferred:**
- Bank credentials and tokens
- Account transaction data
- Payment processing information

### Accounting System Integrations

#### 1. AbraFlexi Ecosystem (15+ Applications)
**Key Applications:**
- `abraflexi_copy.multiflexi.app.json`
- `abraflexi-cashier-withdrawal.multiflexi.app.json`
- `issued_invoices_matcher.multiflexi.app.json`
- `abraflexi_send.multiflexi.app.json`

**Personal/Business Data Flow:**
```
MultiFlexi → AbraFlexi Server
├─── Company data (names, addresses, tax IDs)
├─── Customer information (contacts, payment details)
├─── Invoice data (customer details, amounts)
├─── Employee data (for payroll applications)
└─── Financial records (accounting entries)
```

**Environment Variables Injected:**
- `ABRAFLEXI_URL`: Server endpoint
- `ABRAFLEXI_LOGIN`: Username (potentially personal)
- `ABRAFLEXI_PASSWORD`: Authentication credentials
- `ABRAFLEXI_COMPANY`: Company identifier

**Data Transfer Locations:**
- Czech Republic (primary AbraFlexi servers)
- Cloud deployments (potential international transfer)

#### 2. Pohoda Integration
**Data Environment Variables:**
- `POHODA_ICO`: Company registration number
- `POHODA_URL`: Server location
- `POHODA_USERNAME`: User credentials
- `POHODA_PASSWORD`: Authentication data

### Cloud Service Integrations

#### 1. Microsoft Office 365/SharePoint
**Application:** `csas-sharepoint.app.json`

**Personal Data Transferred:**
- Office 365 credentials (username/password)
- Tenant and site information
- Document uploads containing business/personal data
- Bank statements and financial documents

**International Transfer:**
- Czech bank data → Microsoft cloud infrastructure
- Potential US/EU data residency issues
- No explicit data processing agreement documented

**Environment Variables:**
- `OFFICE365_USERNAME`: Personal email address
- `OFFICE365_PASSWORD`: Authentication credentials
- `OFFICE365_TENANT`: Organization identifier
- `OFFICE365_SITE`: SharePoint site data

#### 2. Azure Cloud Services
**Data Processing:**
- Container execution in Azure environments
- Personal data in containerized applications
- Potential cross-border data transfer

### Monitoring and Logging Services

#### 1. Zabbix Integration
**Data Collected:**
- System performance metrics
- Application execution logs (may contain personal data)
- Company-specific monitoring data
- User activity patterns

**Personal Data Exposure:**
- User IDs in log entries
- Company names and identifiers
- Application execution details
- Performance data linked to users

**Configuration:**
- `ZABBIX_SERVER`: External monitoring server
- `ZABBIX_HOST`: Host identification
- Company-specific Zabbix keys

## 📍 International Data Transfer Analysis

### Identified Cross-Border Transfers

#### 1. AbraFlexi Cloud Deployments
**Transfer:** Czech Republic → EU Cloud Providers
- **Legal Basis**: Contract with AbraFlexi
- **Data Types**: Accounting, customer, financial data
- **Safeguards**: EU adequacy (intra-EU transfer)

#### 2. Microsoft Office 365 Integration
**Transfer:** Czech Republic → Microsoft Global Infrastructure
- **Legal Basis**: Microsoft Customer Agreement
- **Data Types**: Business documents, bank statements, credentials
- **Safeguards**: Microsoft Data Protection Addendum
- **Risk Level**: HIGH - Financial data to US entity

#### 3. Azure Container Execution
**Transfer:** Local data → Azure cloud regions
- **Legal Basis**: Azure subscription terms
- **Data Types**: Application data, execution logs, credentials
- **Safeguards**: Azure Data Processing Terms

#### 4. Docker Hub / Container Registries
**Transfer:** Application images and potentially embedded data
- **Legal Basis**: Docker Hub Terms of Service
- **Data Types**: Container images, build metadata
- **Risk Level**: MEDIUM

### Required GDPR Transfer Mechanisms

#### Immediate Requirements:
1. **Standard Contractual Clauses (SCCs)** for Microsoft transfers
2. **Data Processing Agreements** with all third-party services
3. **Transfer Impact Assessments** for high-risk transfers
4. **Data Mapping** documentation for audit compliance

## 🔄 Application Execution Data Flows

### Job Execution Pipeline
```
1. User Creates Job Template
   ├─── Personal data: User ID, company association
   ├─── Credential data: API keys, passwords
   └─── Configuration: Application parameters

2. Scheduler Triggers Execution
   ├─── Environment injection (credentials exposed)
   ├─── External API calls (data transmitted)
   └─── Result logging (personal data captured)

3. External Service Communication
   ├─── Banking APIs (financial data)
   ├─── Accounting systems (business data)
   ├─── Cloud services (document transfers)
   └─── Monitoring systems (activity logs)

4. Result Processing
   ├─── Log storage (user activity tracking)
   ├─── Artifact generation (may contain personal data)
   └─── Notification dispatch (email with data)
```

### Environment Variable Injection Risks
MultiFlexi automatically injects sensitive environment variables:

```bash
# Example injected environment for AbraFlexi application
ABRAFLEXI_URL=https://demo.flexibee.eu:5434
ABRAFLEXI_LOGIN=user@company.com      # Personal email
ABRAFLEXI_PASSWORD=secretpassword     # Credential
ABRAFLEXI_COMPANY=company_name        # Business identifier
DB_HOST=localhost                     # System information
DB_USERNAME=multiflexi                # System credential
```

**GDPR Risks:**
- Personal email addresses in environment variables
- Credentials transmitted to third-party applications
- No encryption of injected variables
- Logging may capture environment data

## 📊 Third-Party Application Registry

### Banking Applications (7 identified)
1. **FIO Bank** - Statement downloads, transaction processing
2. **Česká Spořitelna** - Account integration, SharePoint uploads  
3. **Raiffeisenbank** - Transaction sync, statement processing
4. **Bank Statement Tools** - Multi-bank integration

### Accounting Applications (15+ identified)
1. **AbraFlexi Copy** - Data synchronization between servers
2. **AbraFlexi Matcher** - Invoice and payment matching
3. **AbraFlexi Mailer** - Document distribution with customer data
4. **AbraFlexi Cashier** - Payment processing
5. **AbraFlexi Reminder** - Customer communication

### Cloud Service Applications (5+ identified)
1. **Office 365 Integration** - SharePoint document management
2. **Azure Services** - Cloud execution environments
3. **Email Services** - Customer communication

### Development/Testing Applications (8+ identified)
1. **MultiFlexi Probe** - System testing with credentials
2. **Benchmark Tools** - Performance testing
3. **Import/Export Tools** - Data migration utilities

## 🚨 Critical GDPR Compliance Gaps

### Data Transfer Issues
1. **No Transfer Impact Assessments** for international transfers
2. **Missing Data Processing Agreements** with third-party services
3. **Inadequate Documentation** of data flows
4. **No Consent Mechanism** for third-party data sharing

### Technical Security Issues
1. **Unencrypted Environment Variables** containing personal data
2. **Plain Text Credential Storage** in configurations
3. **Inadequate Access Controls** for sensitive operations
4. **No Data Retention** policies for external transfers

### Legal Compliance Issues
1. **No Privacy Impact Assessments** for high-risk processing
2. **Missing Legal Basis** documentation for transfers
3. **No User Notification** about third-party data sharing
4. **Inadequate Vendor Management** procedures

## 📋 Compliance Recommendations

### Immediate Actions (Phase 2)
1. **Create Data Processing Inventory** for all third-party integrations
2. **Implement Transfer Impact Assessments** for international transfers
3. **Establish Standard Contractual Clauses** with non-EU vendors
4. **Document Legal Basis** for all third-party data sharing

### Technical Improvements (Phase 3)
1. **Implement Environment Variable Encryption**
2. **Add Data Transfer Consent Management**
3. **Create Audit Logging** for all external data transfers
4. **Implement Data Retention** policies for transferred data

### Legal Framework (Phase 2)
1. **Privacy Policy Updates** documenting third-party integrations
2. **User Consent Interface** for optional third-party sharing
3. **Vendor Data Processing Agreements**
4. **Transfer Mechanism Documentation**

## 🔍 Data Flow Verification Checklist

### ✅ Completed Analysis
- [x] Inter-component data flows mapped
- [x] Third-party integrations identified (53+ applications)
- [x] International transfer destinations documented
- [x] Personal data types in transfers classified
- [x] Environment variable injection analyzed

### ⏳ Pending Verification
- [ ] Actual data volumes in transfers
- [ ] Transfer frequency and schedules  
- [ ] Existing vendor agreements review
- [ ] Data retention periods by service
- [ ] User consent status verification

## 📚 Appendices

### A. Application Integration Summary
| Service Type | Count | Personal Data | International Transfer |
|--------------|-------|---------------|----------------------|
| Banking APIs | 7 | High | EU → Cloud |
| Accounting | 15+ | High | Czech → EU |
| Cloud Services | 5+ | High | EU → US |
| Monitoring | 3 | Medium | Local → Cloud |
| Development | 8+ | Low-Medium | Various |

### B. Environment Variable Patterns
```bash
# Banking credentials
{BANK}_API_KEY, {BANK}_TOKEN, {BANK}_ACCOUNT_*

# Accounting systems  
ABRAFLEXI_*, POHODA_*

# Cloud services
OFFICE365_*, AZURE_*, SHAREPOINT_*

# Monitoring
ZABBIX_*, MONITORING_*
```

### C. Critical Transfer Destinations
1. **Microsoft Office 365** (US/EU) - Financial documents
2. **AbraFlexi Cloud** (EU) - Accounting data
3. **Zabbix Servers** (Various) - Activity monitoring
4. **Docker Registry** (US/EU) - Application containers

---

**Document Version**: 1.0  
**Last Updated**: October 20, 2025  
**Next Review**: Phase 2 completion (February 2025)  
**GitHub Issue**: [#55](https://github.com/VitexSoftware/MultiFlexi/issues/55)