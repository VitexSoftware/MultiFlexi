# MultiFlexi Documentation Restructuring Plan

**Date:** 2026-01-21  
**Status:** Proposal - Ready for Implementation  
**Author:** Technical Documentation Architecture Review

## Executive Summary

This document outlines a comprehensive restructuring of MultiFlexi documentation based on analysis of industry-leading open-source projects (Scrapy, Weblate, Wagtail) and Read the Docs best practices.

**Key Problems Identified:**
- No clear entry point for first-time users
- Concept-task confusion throughout existing docs
- Incomplete documentation stubs (e.g., `application.rst`)
- Reference material mixed with learning content
- Missing onboarding flow from installation to first successful job

**Proposed Solution:**
- Implement proven information architecture: Tutorials → Concepts → How-To → Reference
- Create clear audience segmentation (Users, Admins, Developers)
- Add comprehensive quickstart and first-job tutorial
- Separate learning-oriented from task-oriented content

## Benchmark Analysis Summary

### Scrapy Documentation Patterns
- **Structure:** First Steps → Basic Concepts → Solving Specific Problems → Reference
- **Tutorial approach:** Executable, produces tangible results
- **"Scrapy at a glance"** provides 5-minute overview before deep dive
- Clear "What's next?" navigation throughout

### Weblate Documentation Patterns
- **Audience-based organization:** User → Admin → Developer documentation
- Prevents cognitive overload through role separation
- Quick Setup Guide for each audience

### Common Patterns Across Benchmarks
1. **Inverted Pyramid:** Quickest path to success first
2. **Single Responsibility:** Each page answers ONE question
3. **Explicit Wayfinding:** "See Also" sections guide users
4. **Executable Examples:** All tutorials produce working artifacts
5. **Reference Isolation:** API/CLI docs separated from learning

## Current Documentation Problems

### Critical Issues

1. **No Clear Entry Point**
   - `firstrun.rst` contains only 2 screenshots, minimal explanation
   - No step-by-step from installation to first job
   - Assumes knowledge of RunTemplates without introduction

2. **Concept-Task Confusion**
   - `usage.rst` mixes high-level concepts with specific tasks
   - No distinction between "understanding" and "doing"

3. **Incomplete Stubs**
   - `application.rst`: "Content goes here"
   - Critical abstractions unexplained

4. **Reference Mixed with Learning**
   - `architecture.rst` contains deep implementation details alongside concepts
   - New users can't extract "what I need to know"

5. **Fragmented Core Concepts**
   - Credential management scattered across multiple files
   - Job lifecycle split between `job.rst` and `architecture.rst`
   - No single page explaining Application→Company→RunTemplate→Job

## Proposed Documentation Structure

```
MultiFlexi Documentation
========================

## Getting Started
├── Quickstart (5-Minute Setup)
├── Installation Guide
└── Your First Automated Job (Tutorial)

## Core Concepts
├── Understanding MultiFlexi
│   ├── System Overview
│   ├── Data Model
│   └── Execution Architecture
├── Credential Management
│   ├── Credential System Overview
│   ├── Credential Types
│   └── Credential Security
└── Job Lifecycle
    ├── Scheduling Process
    ├── Execution Phases
    └── Artifact Preservation

## How-To Guides
├── Basic Operations
│   ├── Adding a Company
│   ├── Installing Applications
│   ├── Creating Run Templates
│   └── Scheduling Jobs
├── Credential Management
│   ├── Creating Custom Credential Types
│   ├── Assigning Credentials
│   └── Rotating Credentials
├── Monitoring & Troubleshooting
│   ├── Viewing Job History
│   ├── Debugging Failed Jobs
│   └── Analyzing Artifacts
└── Advanced Workflows
    ├── Bulk RunTemplate Operations
    ├── Multi-Environment Execution
    └── Custom Actions

## Integration Guides
├── Zabbix Monitoring
├── OpenTelemetry
├── AbraFlexi
├── Pohoda
└── Ansible Deployment

## Reference
├── API Reference
│   ├── Authentication
│   ├── Endpoint Catalog
│   └── Data Schemas
├── CLI Reference
│   ├── Command Index
│   ├── Job Commands
│   ├── Application Commands
│   └── User Commands
├── Configuration Reference
│   ├── Environment Variables
│   ├── Database Configuration
│   └── Daemon Configuration
├── Application Development
│   ├── JSON Schema
│   ├── Environment Field Types
│   ├── Executor Types
│   └── Testing Applications
└── GDPR Compliance

## System Administration
├── Docker Deployment
├── Database Maintenance
├── Systemd Service Management
├── Backup & Recovery
└── Upgrading MultiFlexi

## Development & Contributing
├── Project Architecture
├── Component Relationships
├── Development Setup
├── Testing Strategy
└── Code Quality Standards
```

## Implementation Plan

### Phase 1: Foundation (Weeks 1-4)
- [ ] Implement new directory structure
- [ ] Create Quickstart guide
- [ ] Write "Your First Automated Job" tutorial
- [ ] Write Core Concepts: Data Model
- [ ] Write Core Concepts: Job Lifecycle
- [ ] Migrate existing content to correct categories

### Phase 2: Coverage (Months 2-3)
- [ ] Write 3-5 How-To guides per week
- [ ] Complete Reference section (API, CLI, Config)
- [ ] Complete Integration guides

### Phase 3: Quality (Months 4-6)
- [ ] Add diagrams to all Concepts pages
- [ ] Create video tutorials
- [ ] Implement search analytics
- [ ] Internationalization planning

### Phase 4: Maintenance (Ongoing)
- [ ] Enable GitHub Discussions
- [ ] Monthly content review
- [ ] Quarterly contributor sprints
- [ ] CI/CD validation automation

## Documentation Writing Guidelines

### Audience-First Principle
- Identify target audience before writing
- Use appropriate technical depth
- Example: User docs avoid PHP internals

### Single Responsibility
- Each page answers ONE question
- Maximum 300 lines per page (split if longer)

### Mandatory Page Structure
```rst
Page Title
==========

[One-sentence purpose]

**Target Audience:** [Users | Admins | Developers]
**Difficulty:** [Beginner | Intermediate | Advanced]
**Prerequisites:** [List with links]

.. contents::
   :local:
```

### Code Block Requirements
- Include language identifier
- Show expected output
- Commands must be copy-paste executable

## Decision Matrix: Content Categories

| Criterion | Tutorial | How-To | Reference | Concepts |
|-----------|----------|--------|-----------|----------|
| Goal | Teach by doing | Solve problem | Lookup | Mental model |
| Intent | Learn X | Accomplish Y | Syntax? | Why? |
| Prerequisites | None | Existing knowledge | N/A | Basic familiarity |
| Executable? | REQUIRED | REQUIRED | Optional | Optional |

## Key Metrics for Success

### User Behavior
- Time to first successful job: <15 minutes (target)
- Support ticket frequency (should decrease)
- Search terms with no results (identify gaps)

### Content Health
- Broken links: 0 tolerance
- Outdated screenshots: quarterly review
- Orphaned pages: 0 (all reachable from TOC)

### Contributor Engagement
- Documentation PRs per month
- Average review time
- Community-contributed pages

## Next Steps

1. **Review this proposal** with team
2. **Create GitHub issues** for Phase 1 tasks
3. **Set up documentation CI/CD** (link checking, spell check)
4. **Begin implementation** starting with Quickstart
5. **Iterate based on user feedback**

## References

- Scrapy Docs: https://docs.scrapy.org/
- Weblate Docs: https://docs.weblate.org/
- Wagtail Docs: https://guide.wagtail.org/
- Read the Docs Best Practices: https://docs.readthedocs.io/
- Diátaxis Framework: https://diataxis.fr/
