API
===


.. toctree::
   :maxdepth: 2

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

/company/
---------
This endpoint is used for managing company-related data. You can add new companies, fetch details of existing companies, update company information, and remove companies from the system.

/runtemplate/
-------------
This endpoint handles run templates, which are predefined configurations for running jobs. You can create new run templates, get information about existing templates, update template details, and delete templates that are no longer required.

/job/
-----
This endpoint is for managing jobs within the system. You can create new jobs, retrieve job details, update job information, and delete jobs that are completed or no longer needed.

/user/
------
This endpoint allows you to manage user accounts. You can create new users, fetch details of existing users, update user information, and delete users from the system.


API Documentation
=================

The API documentation is available in OpenAPI format. You can view the documentation by visiting the following link:

https://github.com/VitexSoftware/MultiFlexi/blob/main/OpenAPI/openapi.yaml

.. autosummary::
   :toctree: generated

