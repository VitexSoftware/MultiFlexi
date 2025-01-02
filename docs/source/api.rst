API
===

.. contents::

API calls are handled by the `api/index.php` file. This file is responsible for routing the request to the appropriate controller and method. The API is designed to be flexible and easy to use. It is built on top of the `MultiFlexi` class, which provides a simple interface for creating and managing API endpoints.

The endpoints

.. _api_endpoints:

API Endpoints
=============

The following endpoints are available in the API, each supporting CRUD (Create, Read, Update, Delete) operations:

/app/
-----
This endpoint allows you to manage applications within the system. You can create new applications, retrieve details of existing applications, update application information, and delete applications that are no longer needed.

- **GET /app/{appId}.{suffix}**: Get App by ID
- **POST /app/**: Create or Update Application
- **GET /apps.{suffix}**: Show All Apps

/company/
---------
This endpoint is used for managing company-related data. You can add new companies, fetch details of existing companies, update company information, and remove companies from the system.

- **GET /company/{companyId}.{suffix}**: Get Company by ID
- **POST /company/**: Create or Update Company
- **GET /companies.{suffix}**: Show All Companies

/runtemplate/
-------------
This endpoint handles run templates, which are predefined configurations for running jobs. You can create new run templates, get information about existing templates, update template details, and delete templates that are no longer required.

- **GET /runtemplate/{runTemplateId}.{suffix}**: Get RunTemplate by ID
- **POST /runtemplate/**: Create or Update RunTemplate
- **GET /runtemplates.{suffix}**: Show All RunTemplates

/job/
-----
This endpoint is for managing jobs within the system. You can create new jobs, retrieve job details, update job information, and delete jobs that are completed or no longer needed.

- **GET /job/{jobId}.{suffix}**: Get job by ID
- **POST /job/**: Create or Update job record
- **GET /jobs.{suffix}**: Show All jobs

/user/
------
This endpoint allows you to manage user accounts. You can create new users, fetch details of existing users, update user information, and delete users from the system.

- **GET /user/{userId}.{suffix}**: Get User by ID
- **POST /user/**: Create or Update User
- **GET /users.{suffix}**: Show All Users

/credential/
------------
This endpoint allows you to manage user credentials. You can retrieve and update user credentials.

- **GET /credential/{credentialId}.{suffix}**: Get User Credentials
- **POST /credential/{credentialId}.{suffix}**: Update Credentials
- **GET /credentials.{suffix}**: Get All User Credentials

/credential_type/
-----------------
This endpoint allows you to manage credential types. You can retrieve and update credential types.

- **GET /credential_type/{credentialTypeID}.{suffix}**: Get Credential Type by ID
- **POST /credential_type/{credentialTypeID}.{suffix}**: Update Credential Type
- **GET /credential_types.{suffix}**: Get All Credential Types

/topic/
-------
This endpoint allows you to manage topics. You can retrieve and update topics.

- **GET /topic/{topicId}.{suffix}**: Get Topic by ID
- **POST /topic/{topicId}.{suffix}**: Update Topic
- **GET /topics.{suffix}**: Get All Topics

Other Endpoints
===============

/login/
-------
This endpoint is used for user authentication. You can send login and password to obtain an OAuth token.

- **GET /login.{suffix}**: Return User's token
- **POST /login.{suffix}**: Return User's token

/ping/
------
This endpoint is used for job heartbeat operation.

- **GET /ping.{suffix}**: Job heartbeat operation

/status/
--------
This endpoint is used to get the API status.

- **GET /status**: Get API status

/index/
-------
This endpoint is used to list all available endpoints.

- **GET /index.{suffix}**: Endpoints listing

API Documentation
=================

There is a dedicated project for the MultiFlexi API available at:

https://github.com/VitexSoftware/multiflexi-api/

The API documentation is available in OpenAPI format. You can view the documentation by visiting the following link:

https://github.com/VitexSoftware/multiflexi-api/blob/main/openapi-schema.yaml

.. autosummary::
   :toctree: generated
