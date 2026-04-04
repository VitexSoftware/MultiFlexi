.. _kubernetes-integration:

Kubernetes Integration
======================

.. contents::
   :local:
   :depth: 2

MultiFlexi can execute jobs inside Kubernetes pods using the **Kubernetes executor**.
When a runtemplate is configured with the Kubernetes executor, the
``multiflexi-executor`` daemon deploys the application's Helm chart into the
cluster (if not already present) and launches a one-shot pod via
``kubectl run --attach``.  The pod's standard output is captured and stored in
the database as the job's ``stdout`` value.

Prerequisites
-------------

The following must be available on the machine that runs the
``multiflexi-executor`` daemon:

- **kubectl** – Kubernetes command-line tool, accessible in ``$PATH``
- **helm** (v3+) – Helm package manager, accessible in ``$PATH``
- **kubeconfig** – A valid kubeconfig file at ``~/.kube/config`` (relative to
  the daemon user's ``$HOME``) or referenced via the ``KUBECONFIG`` environment
  variable

The daemon typically runs as the ``multiflexi`` system user whose home
directory is ``/var/lib/multiflexi/``.  Make sure the kubeconfig is placed at
``/var/lib/multiflexi/.kube/config`` with owner ``multiflexi`` and permissions
``0600``:

.. code-block:: bash

   sudo mkdir -p /var/lib/multiflexi/.kube
   sudo cp /path/to/admin.kubeconfig /var/lib/multiflexi/.kube/config
   sudo chown -R multiflexi:multiflexi /var/lib/multiflexi/.kube
   sudo chmod 600 /var/lib/multiflexi/.kube/config

A Kubernetes namespace (default ``multiflexi``) must exist in the cluster:

.. code-block:: bash

   kubectl create namespace multiflexi

Application Configuration
-------------------------

Helm Chart Reference
~~~~~~~~~~~~~~~~~~~

Each application that supports Kubernetes execution must declare a Helm chart.
This is stored in the ``helmchart`` field of the application record and can be
set in two ways:

1. **Via the application JSON definition** – The ``kubernetes.helm.chart``
   field in the ``*.multiflexi.app.json`` file is mapped to the database
   ``helmchart`` column when imported:

   .. code-block:: json

      {
        "kubernetes": {
          "helm": {
            "enabled": true,
            "releaseName": "my-app",
            "namespace": "multiflexi",
            "chart": "/path/to/chart-dir",
            "upgradeInstall": true,
            "timeoutSeconds": 300,
            "atomic": false,
            "wait": true
          },
          "artifacts": {
            "enabled": true,
            "outputPath": "result.json",
            "keepPodOnFailure": false
          }
        }
      }

   Import with:

   .. code-block:: bash

      multiflexi-cli application import-json --file=multiflexi/myapp.multiflexi.app.json

2. **Direct database update** – Useful when the chart path needs to differ from
   the JSON definition (e.g. local path vs. OCI reference):

   .. code-block:: sql

      UPDATE apps SET helmchart='/opt/helm-charts/my-app' WHERE id=23;

The ``helmchart`` value can be:

- A local filesystem path (e.g. ``/opt/helm-charts/my-app``)
- An OCI registry reference (e.g. ``oci://ghcr.io/org/charts/my-app``)
- A Helm repository chart name (e.g. ``myrepo/my-app``)

OCI Image
~~~~~~~~

The application must also have an ``ociimage`` field set (e.g.
``docker.io/vitexsoftware/multiflexi-probe``).  This image is used by
``kubectl run`` to create the one-shot job pod.

Helm Chart Structure
~~~~~~~~~~~~~~~~~~~

A minimal Helm chart for a MultiFlexi application should include:

- **ConfigMap** – For non-secret environment variables
- **Secret** – For sensitive environment variables (passwords, tokens)
- **Deployment** – Running the application container with ``envFrom``
  referencing the ConfigMap and Secret
- **ServiceAccount** – Optional, for cluster permissions

See the ``multiflexi-probe`` project's ``helm/`` directory for a reference
implementation.

Configuring a RunTemplate
-------------------------

Using the CLI
~~~~~~~~~~~~

Set the executor on an existing runtemplate:

.. code-block:: bash

   multiflexi-cli runtemplate update --id=158 --executor=Kubernetes

Or create a new runtemplate with the Kubernetes executor:

.. code-block:: bash

   multiflexi-cli runtemplate create \
     --app_id=23 \
     --company_id=3 \
     --name="Probe via K8s" \
     --executor=Kubernetes \
     --interv=d \
     --cron="0 6 * * *" \
     --active=1

Scheduling Immediate Execution
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: bash

   multiflexi-cli runtemplate schedule --id=158 --schedule_time=now

When ``--executor`` is not provided on the ``schedule`` command, the executor
is read from the runtemplate record.  The above command will use the
``Kubernetes`` executor that was configured on the runtemplate.

Execution Flow
--------------

When the ``multiflexi-executor`` daemon picks up a job with the Kubernetes
executor, the following steps occur:

1. **Helm status check** – Runs ``helm status <releaseName> --namespace <ns>``
   to determine whether the application's Helm release is already deployed in
   the cluster.

2. **Helm pre-deployment** (if needed) – If the release is not found, runs
   ``helm upgrade --install`` using the chart path from the ``helmchart``
   database field with ``--create-namespace``, ``--wait``, and the configured
   timeout.

3. **Pod creation** – Runs ``kubectl run`` with ``--restart=Never --attach``
   to create a one-shot pod using the application's OCI image.  Environment
   variables from the runtemplate configuration are passed via ``--env`` flags.
   The command executed inside the pod is the application's ``executable`` with
   its ``cmdparams``.

4. **Output capture** – The pod's stdout and stderr are streamed back through
   the ``kubectl --attach`` connection and captured by the executor.

5. **Artifact collection** (if configured) – If ``artifacts.enabled`` is true
   in the kubernetes config, ``kubectl cp`` is used to copy the output file
   from the pod to the local filesystem and store it in the MultiFlexi file
   store.

6. **Pod cleanup** – The pod is deleted unless ``keepPodOnFailure`` is true and
   the job failed (non-zero exit code).

7. **Database storage** – The captured stdout, stderr, exit code, and command
   line are saved to the job record.  The ``job.stdout`` column contains the
   full standard output from the pod execution.

Verifying Execution
-------------------

Check the job result:

.. code-block:: bash

   multiflexi-cli job get --id=159907 --format=json

Key fields to verify:

- ``executor`` – Should be ``Kubernetes``
- ``exitcode`` – ``0`` for success
- ``stdout`` – Contains the captured pod output
- ``command`` – Shows the ``kubectl run`` command that was executed

Check pods in the cluster:

.. code-block:: bash

   kubectl -n multiflexi get pods
   helm -n multiflexi list

Troubleshooting
---------------

Helm Pre-deployment Fails
~~~~~~~~~~~~~~~~~~~~~~~~~

- **"path not found"** – The ``helmchart`` value points to a path the daemon
  user cannot access.  Use a path readable by the ``multiflexi`` user or an OCI
  chart reference.
- **OCI registry 404/403** – The OCI chart doesn't exist or requires
  authentication.  Run ``helm registry login`` as the multiflexi user, or use a
  local chart path.
- **"cluster unreachable"** – The kubeconfig is missing or has wrong
  permissions.  Verify ``/var/lib/multiflexi/.kube/config`` exists and is owned
  by ``multiflexi:multiflexi``.

ImagePullBackOff
~~~~~~~~~~~~~~~

The container image tag in ``values.yaml`` doesn't exist on the registry.
Override the tag during Helm install:

.. code-block:: bash

   helm upgrade --install my-app ./helm --set image.tag=latest -n multiflexi

Pod Fails to Start
~~~~~~~~~~~~~~~~~

Check pod events:

.. code-block:: bash

   kubectl -n multiflexi describe pod <pod-name>
   kubectl -n multiflexi get events --sort-by=.lastTimestamp

Empty stdout in Job Record
~~~~~~~~~~~~~~~~~~~~~~~~~

If the job completes but ``stdout`` is empty:

1. Verify the application writes output to stdout (not just to files)
2. Check the ``stderr`` field for error messages
3. Ensure the Kubernetes executor version includes the stdout capture fix
   (``jobStdout``/``jobStderr`` instance variables in ``Kubernetes.php``)

Executor Not Recognized
~~~~~~~~~~~~~~~~~~~~~~

If scheduling reports ``Executor: Native`` despite setting ``Kubernetes``:

- Verify the runtemplate was updated: ``multiflexi-cli runtemplate get --id=<ID> --format=json``
- Ensure ``Kubernetes.php`` is deployed at
  ``/usr/share/php/MultiFlexi/Executor/Kubernetes.php``
- Restart the executor daemon: ``sudo systemctl restart multiflexi-executor``
