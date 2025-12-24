<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp Gateway Setup</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ $global_favicon ?? asset('favicon.ico') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
        }

        .main-container {
            background: #f8fafc;
            min-height: calc(100vh - 56px);
            padding: 20px;
        }

        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid #eee;
            font-weight: 600;
            padding: 12px 16px;
        }

        .btn {
            border-radius: 10px;
            font-weight: 500;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }

        .btn-success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border: none;
        }

        .btn-warning {
            background: linear-gradient(135deg, #f5af19 0%, #f12711 100%);
            border: none;
            color: white;
        }

        .code-block {
            background: #1a1a2e;
            color: #eee;
            padding: 15px;
            border-radius: 10px;
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
            overflow-x: auto;
        }

        .step-number {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 10px;
        }

        .check-result {
            padding: 15px;
            border-radius: 10px;
        }

        .check-success {
            background: #d1fae5;
            border: 1px solid #10b981;
        }

        .check-error {
            background: #fee2e2;
            border: 1px solid #ef4444;
        }

        .check-loading {
            background: #e0e7ff;
            border: 1px solid #6366f1;
        }
    </style>
</head>

<body>
    @include('layouts.navbar_partial')

    <div class="main-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-0"><i class="fab fa-whatsapp text-success me-2"></i>WhatsApp Gateway Setup</h4>
                <small class="text-muted">Check server requirements and configure the gateway</small>
            </div>
            <a href="{{ route('whatsapp.index') }}" class="btn btn-outline-secondary btn-sm"><i
                    class="fas fa-arrow-left me-1"></i>Back to Gateway</a>
        </div>

        <div class="row">
            <!-- LEFT: Server Check -->
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-header"><i class="fas fa-server text-primary me-2"></i>Server Requirements Check
                    </div>
                    <div class="card-body">
                        <p class="small text-muted mb-3">Click the button below to check if your server supports
                            Node.js.</p>

                        <button id="btnCheck" class="btn btn-primary w-100 mb-3" onclick="checkServer()">
                            <i class="fas fa-search me-1"></i>Check Server Support
                        </button>

                        <div id="checkResult" style="display: none;">
                            <!-- Results will be inserted here -->
                        </div>

                        <div id="actionButtons" style="display: none;">
                            <!-- Action buttons based on check result -->
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><i class="fas fa-info-circle text-info me-2"></i>System Info</div>
                    <div class="card-body">
                        <table class="table table-sm small mb-0">
                            <tr>
                                <td class="text-muted">PHP Version</td>
                                <td class="fw-bold">{{ phpversion() }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Server OS</td>
                                <td class="fw-bold">{{ PHP_OS }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Laravel</td>
                                <td class="fw-bold">{{ app()->version() }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- RIGHT: Documentation -->
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header"><i class="fas fa-book text-warning me-2"></i>Installation Guide</div>
                    <div class="card-body">

                        <!-- LOCALHOST -->
                        <h6 class="fw-bold mb-3"><i class="fas fa-laptop me-2"></i>For Localhost (Windows/XAMPP)</h6>

                        <div class="mb-3">
                            <div class="d-flex align-items-start mb-2">
                                <span class="step-number">1</span>
                                <div>
                                    <strong>Install Node.js</strong>
                                    <p class="small text-muted mb-1">Download and install Node.js from the official
                                        website.</p>
                                    <a href="https://nodejs.org/" target="_blank"
                                        class="btn btn-sm btn-outline-primary"><i
                                            class="fas fa-download me-1"></i>Download Node.js</a>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex align-items-start mb-2">
                                <span class="step-number">2</span>
                                <div>
                                    <strong>Install Gateway Dependencies</strong>
                                    <p class="small text-muted mb-1">Open Terminal/CMD in your project folder and run:
                                    </p>
                                    <div class="code-block">cd whatsapp-gateway<br>npm install</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="d-flex align-items-start mb-2">
                                <span class="step-number">3</span>
                                <div>
                                    <strong>Start the Gateway</strong>
                                    <p class="small text-muted mb-1">Use the "Start Service" button in the Gateway
                                        panel, or run manually:</p>
                                    <div class="code-block">node index.js</div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- HOSTING CPANEL -->
                        <h6 class="fw-bold mb-3"><i class="fas fa-cloud me-2"></i>For cPanel Hosting (CloudLinux)</h6>

                        <div class="alert alert-info py-2 small mb-3">
                            <i class="fas fa-info-circle me-1"></i>Most shared hosting uses <b>CloudLinux NodeJS
                                Selector</b>. package.json is now in project root.
                        </div>

                        <div class="mb-3">
                            <div class="d-flex align-items-start mb-2">
                                <span class="step-number">1</span>
                                <div>
                                    <strong>Upload entire project</strong>
                                    <p class="small text-danger mb-0"><i
                                            class="fas fa-exclamation-triangle me-1"></i><b>Important:</b> Do NOT upload
                                        the <code>node_modules</code> folder! Delete it first before uploading.</p>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex align-items-start mb-2">
                                <span class="step-number">2</span>
                                <div>
                                    <strong>Create Node.js App in cPanel</strong>
                                    <p class="small text-muted mb-1">Go to cPanel → Setup Node.js App → Create
                                        Application:</p>
                                    <ul class="small mb-0">
                                        <li>Node.js version: <b>18.x+</b></li>
                                        <li>Application mode: <b>Production</b></li>
                                        <li>Application root: <b>your_laravel_folder</b> (project root with
                                            package.json)</li>
                                        <li>Startup file: <b>whatsapp-gateway/index.js</b></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex align-items-start mb-2">
                                <span class="step-number">3</span>
                                <div>
                                    <strong>Run NPM Install in cPanel</strong>
                                    <p class="small text-muted mb-0">Click <b>"Run NPM Install"</b> button in cPanel
                                        Node.js panel.</p>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex align-items-start mb-2">
                                <span class="step-number">4</span>
                                <div>
                                    <strong>Configure .env</strong>
                                    <div class="code-block">
                                        DB_HOST=localhost<br>DB_USER=your_db_user<br>DB_PASSWORD=your_password<br>DB_NAME=your_database
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex align-items-start mb-2">
                                <span class="step-number">5</span>
                                <div>
                                    <strong>Start Application</strong>
                                    <p class="small text-muted mb-0">Click <b>"Restart"</b> or <b>"Run JS Script"</b> in
                                        cPanel.</p>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex align-items-start mb-2">
                                <span class="step-number">6</span>
                                <div>
                                    <strong>Configure Laravel .env</strong>
                                    <p class="small text-muted mb-1">Add Gateway URL to your Laravel project's
                                        <code>.env</code>:
                                    </p>
                                    <div class="code-block">WHATSAPP_GATEWAY_URL=https://your-app-url.yourdomain.com
                                    </div>
                                    <p class="small text-info mt-1 mb-0"><i class="fas fa-info-circle me-1"></i>Get this
                                        URL from "Application URL" in cPanel Node.js panel</p>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-danger py-2 small mb-3">
                            <i class="fas fa-bug me-1"></i><b>CloudLinux Error Fix:</b> If you get "node_modules
                            symlink" error, delete <code>node_modules</code> folder via File Manager, then run NPM
                            Install again.
                        </div>

                        <hr>

                        <!-- VPS -->
                        <h6 class="fw-bold mb-3"><i class="fas fa-server me-2"></i>For VPS/Dedicated (SSH)</h6>

                        <div class="code-block mb-3"># Install NVM + Node.js<br>curl -o-
                            https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash<br>source
                            ~/.bashrc<br>nvm install 18 && nvm use 18<br><br># Install & Run with PM2<br>cd
                            /path/to/whatsapp-gateway<br>npm install<br>npm install -g pm2<br>pm2 start index.js --name
                            "wa-gateway"<br>pm2 save && pm2 startup</div>

                        <div class="alert alert-warning py-2 small mb-0">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            <strong>Important:</strong> Some shared hosts kill long-running processes. Use VPS for
                            production.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script>
        function checkServer() {
            $('#btnCheck').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Checking...');
            $('#checkResult').show().html('<div class="check-loading text-center py-3"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2 mb-0">Checking server...</p></div>');
            $('#actionButtons').hide();

            $.get("{{ route('whatsapp.check-nodejs') }}", function (data) {
                if (data.installed) {
                    $('#checkResult').html(`
                        <div class="check-success">
                            <h6 class="text-success fw-bold mb-2"><i class="fas fa-check-circle me-1"></i>Node.js Installed!</h6>
                            <table class="table table-sm small mb-0">
                                <tr><td>Node Version</td><td class="fw-bold">${data.node_version}</td></tr>
                                <tr><td>NPM Version</td><td class="fw-bold">${data.npm_version}</td></tr>
                                <tr><td>Node Path</td><td class="fw-bold text-break">${data.node_path}</td></tr>
                                <tr><td>OS</td><td class="fw-bold">${data.os}</td></tr>
                                <tr><td>Dependencies</td><td class="fw-bold">${data.dependencies_installed ? '<span class="text-success">Installed</span>' : '<span class="text-warning">Not Installed</span>'}</td></tr>
                            </table>
                        </div>
                    `);

                    if (!data.dependencies_installed) {
                        $('#actionButtons').show().html(`
                            <button class="btn btn-warning w-100 mt-3" onclick="installDeps()">
                                <i class="fas fa-download me-1"></i>Install Gateway Dependencies
                            </button>
                        `);
                    } else {
                        $('#actionButtons').show().html(`
                            <a href="{{ route('whatsapp.index') }}" class="btn btn-success w-100 mt-3">
                                <i class="fas fa-play me-1"></i>Go to WhatsApp Gateway
                            </a>
                        `);
                    }
                } else {
                    // Check if it's cPanel - show different message
                    let cpanelMsg = data.is_cpanel ?
                        `<div class="alert alert-info py-2 small mt-2 mb-0">
                            <i class="fas fa-info-circle me-1"></i><b>cPanel Detected:</b> PHP cannot detect Node.js on CloudLinux. 
                            Please start the Node.js app via cPanel Node.js Selector.
                        </div>` : '';

                    let gatewayInfo = data.gateway_url ?
                        `<p class="small text-muted mt-2 mb-0">Gateway URL: <code>${data.gateway_url}</code></p>` : '';

                    $('#checkResult').html(`
                        <div class="check-error">
                            <h6 class="text-danger fw-bold mb-2"><i class="fas fa-times-circle me-1"></i>Node.js Not Detected</h6>
                            <p class="small mb-0">${data.is_cpanel ?
                            'On cPanel hosting, start the app via Node.js Selector then check again.' :
                            'Node.js is not installed. Please install it using the guide on the right.'}</p>
                            ${gatewayInfo}
                            ${cpanelMsg}
                        </div>
                    `);

                    if (data.is_cpanel) {
                        $('#actionButtons').show().html(`
                            <a href="{{ route('whatsapp.index') }}" class="btn btn-primary w-100 mt-3">
                                <i class="fas fa-play me-1"></i>Go to WhatsApp Gateway
                            </a>
                        `);
                    } else {
                        $('#actionButtons').show().html(`
                            <a href="https://nodejs.org/" target="_blank" class="btn btn-primary w-100 mt-3">
                                <i class="fas fa-download me-1"></i>Download Node.js
                            </a>
                        `);
                    }
                }
            }).fail(function () {
                $('#checkResult').html(`
                    <div class="check-error">
                        <h6 class="text-danger fw-bold mb-2"><i class="fas fa-exclamation-triangle me-1"></i>Check Failed</h6>
                        <p class="small mb-0">Could not check server. Please try again.</p>
                    </div>
                `);
            }).always(function () {
                $('#btnCheck').prop('disabled', false).html('<i class="fas fa-search me-1"></i>Check Server Support');
            });
        }

        function installDeps() {
            if (!confirm('This will run "npm install" in the whatsapp-gateway folder. Continue?')) return;

            $('#actionButtons button').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Installing...');

            $.ajax({
                url: "{{ route('whatsapp.install-deps') }}",
                type: "POST",
                data: { _token: $('meta[name="csrf-token"]').attr('content') },
                success: function (res) {
                    alert(res.message);
                    checkServer(); // Refresh status
                },
                error: function (xhr) {
                    let msg = xhr.responseJSON ? xhr.responseJSON.message : 'Installation failed';
                    alert('Error: ' + msg);
                    $('#actionButtons button').prop('disabled', false).html('<i class="fas fa-download me-1"></i>Install Gateway Dependencies');
                }
            });
        }

        // Auto-check on page load
        $(document).ready(function () {
            checkServer();
        });
    </script>
</body>

</html>