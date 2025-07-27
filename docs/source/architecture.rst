MultiFlexi Architecture
=======================

The MultiFlexi ecosystem is composed of several interrelated projects, each fulfilling a specific role in the overall architecture. Below is an overview of the main member projects and their relationships:

Core Components
---------------

- **php-vitexsoftware-multiflexi-core**
  (https://github.com/VitexSoftware/php-vitexsoftware-multiflexi-core)

  The core PHP library providing the main business logic, data models, and shared utilities for the MultiFlexi platform. All other components depend on this package for core functionality.

- **multiflexi-database**
  (https://github.com/VitexSoftware/multiflexi-database)

  Contains database schema definitions and migration scripts for MultiFlexi. Used by the server and other components to initialize and update the database structure.

- **multiflexi-cli**
  (https://github.com/VitexSoftware/multiflexi-cli)

  Command-line interface for managing MultiFlexi resources (applications, companies, users, jobs, etc.). Relies on the core library and interacts with the database.

- **multiflexi-server**
  (https://github.com/VitexSoftware/multiflexi-server)

  The main backend server providing REST API and web UI for MultiFlexi. It orchestrates job scheduling, user management, and integrates with the core library and database.

- **multiflexi-executor**
  (https://github.com/VitexSoftware/multiflexi-executor)

  A dedicated service or agent responsible for executing jobs and tasks as scheduled by the server. Communicates with the server and may run in isolated environments.

- **multiflexi-ansible-collection**
  (https://github.com/VitexSoftware/multiflexi-ansible-collection)

  An Ansible collection providing playbooks and roles for deploying and managing MultiFlexi components in various environments.

- **multiflexi-all**
  (https://github.com/VitexSoftware/multiflexi-all)

  A meta-repository that aggregates all the above projects, providing a unified source for development, deployment, and integration.

Project Relationships
---------------------

- The **core** library is a dependency for the CLI, server, and executor.
- The **database** project provides schema and migrations for all components that require persistent storage.
- The **CLI** and **server** both interact with the database and core library, but serve different user interfaces (command-line vs. web/API).
- The **executor** is managed by the server and is responsible for running jobs in a secure and isolated manner.
- The **ansible-collection** is used to automate deployment and configuration of all components.
- The **all** meta-repo is used for orchestration, CI/CD, and as a reference for the complete MultiFlexi stack.

This modular architecture allows for flexible deployment, scaling, and maintenance of the MultiFlexi platform.

.. raw:: html

   <div style="text-align:center; margin-top:2em;">
   <svg width="800" height="420" xmlns="http://www.w3.org/2000/svg">
     <!-- Core -->
     <rect x="340" y="20" width="120" height="40" rx="8" fill="#f9f9f9" stroke="#333"/>
     <text x="400" y="45" font-size="14" text-anchor="middle" fill="#222">core</text>
     <text x="400" y="60" font-size="10" text-anchor="middle" fill="#666">php-vitexsoftware-multiflexi-core</text>

     <!-- Database -->
     <rect x="340" y="80" width="120" height="40" rx="8" fill="#f9f9f9" stroke="#333"/>
     <text x="400" y="105" font-size="14" text-anchor="middle" fill="#222">database</text>
     <text x="400" y="120" font-size="10" text-anchor="middle" fill="#666">multiflexi-database</text>

     <!-- CLI -->
     <rect x="80" y="160" width="160" height="40" rx="8" fill="#e3f2fd" stroke="#333"/>
     <text x="160" y="185" font-size="14" text-anchor="middle" fill="#222">CLI</text>
     <text x="160" y="200" font-size="10" text-anchor="middle" fill="#666">multiflexi-cli</text>

     <!-- Server -->
     <rect x="340" y="160" width="120" height="40" rx="8" fill="#ffe0b2" stroke="#333"/>
     <text x="400" y="185" font-size="14" text-anchor="middle" fill="#222">server</text>
     <text x="400" y="200" font-size="10" text-anchor="middle" fill="#666">multiflexi-server</text>

     <!-- Executor -->
     <rect x="600" y="160" width="160" height="40" rx="8" fill="#c8e6c9" stroke="#333"/>
     <text x="680" y="185" font-size="14" text-anchor="middle" fill="#222">executor</text>
     <text x="680" y="200" font-size="10" text-anchor="middle" fill="#666">multiflexi-executor</text>

     <!-- Ansible Collection -->
     <rect x="80" y="320" width="220" height="40" rx="8" fill="#f3e5f5" stroke="#333"/>
     <text x="190" y="345" font-size="14" text-anchor="middle" fill="#222">ansible-collection</text>
     <text x="190" y="360" font-size="10" text-anchor="middle" fill="#666">multiflexi-ansible-collection</text>

     <!-- All Meta -->
     <rect x="500" y="320" width="220" height="40" rx="8" fill="#fff9c4" stroke="#333"/>
     <text x="610" y="345" font-size="14" text-anchor="middle" fill="#222">all (meta-repo)</text>
     <text x="610" y="360" font-size="10" text-anchor="middle" fill="#666">multiflexi-all</text>

     <!-- Arrows -->
     <!-- core to cli -->
     <line x1="400" y1="60" x2="160" y2="160" stroke="#333" stroke-width="2" marker-end="url(#arrow)"/>
     <!-- core to server -->
     <line x1="400" y1="60" x2="400" y2="160" stroke="#333" stroke-width="2" marker-end="url(#arrow)"/>
     <!-- core to executor -->
     <line x1="400" y1="60" x2="680" y2="160" stroke="#333" stroke-width="2" marker-end="url(#arrow)"/>
     <!-- database to cli -->
     <line x1="400" y1="120" x2="160" y2="160" stroke="#333" stroke-width="2" marker-end="url(#arrow)"/>
     <!-- database to server -->
     <line x1="400" y1="120" x2="400" y2="160" stroke="#333" stroke-width="2" marker-end="url(#arrow)"/>
     <!-- database to executor -->
     <line x1="400" y1="120" x2="680" y2="160" stroke="#333" stroke-width="2" marker-end="url(#arrow)"/>
     <!-- server to executor -->
     <line x1="460" y1="180" x2="600" y2="180" stroke="#333" stroke-width="2" marker-end="url(#arrow)"/>
     <!-- ansible-collection to all -->
     <line x1="300" y1="340" x2="500" y2="340" stroke="#333" stroke-width="2" marker-end="url(#arrow)"/>
     <!-- all to all components -->
     <line x1="610" y1="320" x2="400" y2="60" stroke="#aaa" stroke-width="1" marker-end="url(#arrow)"/>
     <line x1="610" y1="320" x2="400" y2="120" stroke="#aaa" stroke-width="1" marker-end="url(#arrow)"/>
     <line x1="610" y1="320" x2="160" y2="160" stroke="#aaa" stroke-width="1" marker-end="url(#arrow)"/>
     <line x1="610" y1="320" x2="400" y2="160" stroke="#aaa" stroke-width="1" marker-end="url(#arrow)"/>
     <line x1="610" y1="320" x2="680" y2="160" stroke="#aaa" stroke-width="1" marker-end="url(#arrow)"/>

     <defs>
       <marker id="arrow" markerWidth="10" markerHeight="10" refX="10" refY="5" orient="auto" markerUnits="strokeWidth">
         <path d="M0,0 L10,5 L0,10 L2,5 Z" fill="#333" />
       </marker>
     </defs>
   </svg>
   <div style="color:#888; font-size:12px; margin-top:0.5em;">MultiFlexi project relationships schema</div>
   </div>


