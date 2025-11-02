<?php

declare(strict_types=1);

/**
 * Session Investigation Test Page.
 *
 * This page displays the contents of the current PHP session
 * to help debug serialization issues.
 */

require_once __DIR__.'/init.php';

// Start or resume session
if (session_status() === \PHP_SESSION_NONE) {
    session_start();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Investigation - MultiFlexi</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
        body { padding: 20px; }
        .debug-section { margin-bottom: 30px; }
        pre { background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .property-table td { font-family: monospace; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <h1>Session Investigation Tool</h1>
        <p class="text-muted">Session ID: <code><?php echo session_id(); ?></code></p>

        <div class="debug-section">
            <h2>Session Variables</h2>
            <pre><?php print_r($_SESSION); ?></pre>
        </div>

        <?php if (isset($_SESSION['user']) && \is_object($_SESSION['user'])) { ?>
        <div class="debug-section">
            <h2>User Object Analysis</h2>
            <div class="card">
                <div class="card-header">
                    <strong>Class:</strong> <?php echo \get_class($_SESSION['user']); ?>
                </div>
                <div class="card-body">
                    <h5>Declared Properties</h5>
                    <?php
                    $userClass = new ReflectionClass($_SESSION['user']);
            $properties = $userClass->getProperties();
            ?>
                    <table class="table table-sm property-table">
                        <thead>
                            <tr>
                                <th>Property Name</th>
                                <th>Type</th>
                                <th>Visibility</th>
                                <th>Has Value</th>
                                <th>Value Preview</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($properties as $prop) { ?>
                            <tr>
                                <td><?php echo $prop->getName(); ?></td>
                                <td><?php echo $prop->hasType() ? $prop->getType() : 'mixed'; ?></td>
                                <td>
                                    <?php
                            if ($prop->isPublic()) {
                                echo '<span class="badge badge-success">public</span>';
                            }

                            if ($prop->isProtected()) {
                                echo '<span class="badge badge-warning">protected</span>';
                            }

                            if ($prop->isPrivate()) {
                                echo '<span class="badge badge-danger">private</span>';
                            }

                            ?>
                                </td>
                                <td>
                                    <?php
                            $prop->setAccessible(true);
                            $hasValue = $prop->isInitialized($_SESSION['user']);
                            echo $hasValue ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>';
                            ?>
                                </td>
                                <td>
                                    <?php
                            if ($hasValue) {
                                $value = $prop->getValue($_SESSION['user']);

                                if (\is_object($value)) {
                                    echo '<em>Object: '.$value::class.'</em>';
                                } elseif (\is_array($value)) {
                                    echo '<em>Array['.\count($value).']</em>';
                                } else {
                                    echo htmlspecialchars(substr(var_export($value, true), 0, 50));
                                }
                            }

                            ?>
                                </td>
                            </tr>
                        <?php }

 ?>
                        </tbody>
                    </table>

                    <?php if (method_exists($_SESSION['user'], '__sleep')) { ?>
                    <div class="alert alert-info">
                        <h5>__sleep() Method Found</h5>
                        <p>This object has a custom __sleep() method. Properties returned by __sleep():</p>
                        <?php
                        $reflection = new ReflectionMethod(\get_class($_SESSION['user']), '__sleep');
                        $reflection->setAccessible(true);
                        $sleepProps = $reflection->invoke($_SESSION['user']);
                        ?>
                        <pre><?php print_r($sleepProps); ?></pre>

                        <h6 class="mt-3">Validation Check:</h6>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Property in __sleep()</th>
                                    <th>Declared?</th>
                                    <th>Initialized?</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($sleepProps as $propName) { ?>
                                <?php
                                try {
                                    $prop = $userClass->getProperty($propName);
                                    $prop->setAccessible(true);
                                    $declared = true;
                                    $initialized = $prop->isInitialized($_SESSION['user']);
                                    $status = $initialized ? 'OK' : 'WARNING: Not initialized';
                                    $statusClass = $initialized ? 'success' : 'warning';
                                } catch (ReflectionException $e) {
                                    $declared = false;
                                    $initialized = false;
                                    $status = 'ERROR: Property not declared!';
                                    $statusClass = 'danger';
                                }

                                ?>
                                <tr>
                                    <td><code><?php echo $propName; ?></code></td>
                                    <td><?php echo $declared ? '✓' : '✗'; ?></td>
                                    <td><?php echo $initialized ? '✓' : '✗'; ?></td>
                                    <td><span class="badge badge-<?php echo $statusClass; ?>"><?php echo $status; ?></span></td>
                                </tr>
                            <?php }

 ?>
                            </tbody>
                        </table>
                    </div>
                    <?php }

 ?>
                </div>
            </div>
        </div>
        <?php }

 ?>

        <?php
        // Check all session objects
        $sessionObjects = [];

foreach ($_SESSION as $key => $value) {
    if (\is_object($value)) {
        $sessionObjects[$key] = $value;
    }
}

?>

        <?php if (!empty($sessionObjects)) { ?>
        <div class="debug-section">
            <h2>All Session Objects</h2>
            <div class="accordion" id="objectAccordion">
                <?php $index = 0;

            foreach ($sessionObjects as $key => $object) {
                ++$index; ?>
                <div class="card">
                    <div class="card-header" id="heading<?php echo $index; ?>">
                        <h5 class="mb-0">
                            <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse<?php echo $index; ?>">
                                <strong><?php echo htmlspecialchars($key); ?></strong> - <?php echo $object::class; ?>
                            </button>
                        </h5>
                    </div>
                    <div id="collapse<?php echo $index; ?>" class="collapse" data-parent="#objectAccordion">
                        <div class="card-body">
                            <?php if (method_exists($object, '__sleep')) { ?>
                                <?php
                                $objClass = new ReflectionClass($object);
                                $reflection = new ReflectionMethod($object::class, '__sleep');
                                $reflection->setAccessible(true);
                                $sleepProps = $reflection->invoke($object);
                                ?>
                                <h6>__sleep() returns:</h6>
                                <pre><?php print_r($sleepProps); ?></pre>

                                <h6>Property Check:</h6>
                                <ul class="list-group">
                                <?php foreach ($sleepProps as $propName) { ?>
                                    <?php
                                    try {
                                        $prop = $objClass->getProperty($propName);
                                        $prop->setAccessible(true);
                                        $initialized = $prop->isInitialized($object);
                                        $statusIcon = $initialized ? '✓' : '⚠';
                                        $statusClass = $initialized ? 'success' : 'warning';
                                    } catch (ReflectionException $e) {
                                        $statusIcon = '✗';
                                        $statusClass = 'danger';
                                        $initialized = false;
                                    }

                                    ?>
                                    <li class="list-group-item list-group-item-<?php echo $statusClass; ?>">
                                        <?php echo $statusIcon; ?> <code><?php echo $propName; ?></code>
                                        <?php if (!$initialized) { ?>
                                            - Not declared or not initialized!
                                        <?php }

 ?>
                                    </li>
                                <?php }

 ?>
                                </ul>
                            <?php } else { ?>
                                <p class="text-muted">No custom __sleep() method</p>
                            <?php }

 ?>
                        </div>
                    </div>
                </div>
                <?php }

 ?>
            </div>
        </div>
        <?php }

 ?>

        <div class="debug-section">
            <h2>Session File Content (Raw)</h2>
            <?php
            $sessionPath = session_save_path() ?: '/var/lib/php/sessions';
$sessionFile = $sessionPath.'/sess_'.session_id();

if (file_exists($sessionFile) && is_readable($sessionFile)) {
    $content = file_get_contents($sessionFile);
    echo '<pre>'.htmlspecialchars($content).'</pre>';
} else {
    echo '<p class="text-danger">Session file not accessible: '.htmlspecialchars($sessionFile).'</p>';
}

?>
        </div>

        <div class="debug-section">
            <h2>Actions</h2>
            <a href="logout.php" class="btn btn-warning">Destroy Session & Logout</a>
            <a href="login.php" class="btn btn-primary">Go to Login</a>
            <a href="main.php" class="btn btn-secondary">Go to Main Page</a>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
