const express = require('express');
const bodyParser = require('body-parser');
const { exec } = require('child_process');
const fs = require('fs');
const path = require('path');
const os = require('os');

const app = express();
const PORT = process.env.PORT || 9999;
const AUTH_TOKEN = process.env.PROXY_TOKEN;

// Middleware
app.use(bodyParser.json({ limit: '5mb' }));
app.use(bodyParser.text({ limit: '5mb' }));

// ─── Public Routes (no auth) ───

/**
 * Dashboard — shown in browser
 */
app.get('/', (req, res) => {
    res.send(`
<!DOCTYPE html>
<html><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>D Star Print Proxy</title>
<style>
  body{font-family:system-ui,sans-serif;background:#0f1535;color:#fff;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0}
  .card{background:#1a1f3d;border-radius:12px;padding:32px;text-align:center;max-width:400px}
  h1{font-size:24px;margin:0 0 8px;color:#60a5fa}
  .dot{display:inline-block;width:8px;height:8px;background:#22c55e;border-radius:50%;margin-right:8px}
  .row{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.05);font-size:14px}
  .row span:first-child{color:#9ca3af}
  pre{background:#0f1535;padding:12px;border-radius:8px;font-size:12px;text-align:left;color:#9ca3af;margin-top:16px}
</style></head><body>
<div class="card">
  <h1><span class="dot"></span>Print Proxy Running</h1>
  <div class="row"><span>Status</span><span style="color:#22c55e">Online</span></div>
  <div class="row"><span>Port</span><span>${PORT}</span></div>
  <div class="row"><span>Platform</span><span>${os.platform()}</span></div>
  <div class="row"><span>Hostname</span><span>${os.hostname()}</span></div>
  <pre>
Endpoints:
  POST /print/escpos   → ESC/POS thermal
  POST /print/windows  → Windows printer
  GET  /printers       → List printers
  GET  /health         → JSON status
  </pre>
</div></body></html>`);
});

app.get('/health', (req, res) => {
    res.json({ status: 'running', port: PORT, platform: os.platform(), timestamp: new Date().toISOString() });
});

// ─── Auth Middleware (protected routes below) ───
app.use((req, res, next) => {
    const token = req.headers['x-proxy-token'] || req.query.token;
    if (token !== AUTH_TOKEN) {
        return res.status(403).json({ error: 'Invalid proxy token' });
    }
    next();
});

/**
 * ESC/POS Print — Formats receipt text for thermal printer and prints
 * via Windows print command or direct ESC/POS commands
 */
app.post('/print/escpos', async (req, res) => {
    try {
        const { printerName, content, cutPaper = true, openDrawer = true } = req.body;

        if (!printerName) {
            return res.status(400).json({ error: 'printerName is required' });
        }

        // Build ESC/POS commands
        let commands = '';

        // Initialize printer
        commands += '\x1B\x40'; // ESC @ - Initialize

        // Content (already formatted as plain text with \n for newlines)
        const lines = (content || '').split('\n');
        for (const line of lines) {
            commands += line + '\n';
        }

        // Feed & cut
        commands += '\n\n\n\n';
        if (cutPaper) {
            commands += '\x1D\x56\x00'; // GS V 0 - Full cut
        }

        // Open cash drawer
        if (openDrawer) {
            commands += '\x1B\x70\x00\x19\xFA'; // ESC p - Open drawer
        }

        // Write to temp file and print
        const tmpFile = path.join(os.tmpdir(), `print-${Date.now()}.txt`);
        fs.writeFileSync(tmpFile, commands, 'utf-8');

        // Attempt printing via Windows print command
        const printCmd = `print /D:"${printerName}" "${tmpFile}"`;
        exec(printCmd, (err, stdout, stderr) => {
            // Clean up temp file
            setTimeout(() => {
                try { fs.unlinkSync(tmpFile); } catch (e) {}
            }, 5000);

            if (err) {
                console.error('Print error:', err.message);
                return res.status(500).json({ error: 'Print failed: ' + err.message });
            }
            res.json({ success: true, printer: printerName });
        });

    } catch (e) {
        console.error('ESC/POS print error:', e);
        res.status(500).json({ error: e.message });
    }
});

/**
 * Windows Print — Prints content to a Windows printer
 */
app.post('/print/windows', async (req, res) => {
    try {
        const { printerName, content } = req.body;

        if (!printerName) {
            return res.status(400).json({ error: 'printerName is required' });
        }

        const tmpFile = path.join(os.tmpdir(), `print-${Date.now()}.txt`);
        fs.writeFileSync(tmpFile, content || '', 'utf-8');

        const printCmd = `print /D:"${printerName}" "${tmpFile}"`;
        exec(printCmd, (err, stdout, stderr) => {
            setTimeout(() => {
                try { fs.unlinkSync(tmpFile); } catch (e) {}
            }, 5000);

            if (err) {
                console.error('Print error:', err.message);
                return res.status(500).json({ error: 'Print failed: ' + err.message });
            }
            res.json({ success: true, printer: printerName });
        });

    } catch (e) {
        console.error('Windows print error:', e);
        res.status(500).json({ error: e.message });
    }
});

/**
 * List available Windows printers
 */
app.get('/printers', (req, res) => {
    exec('wmic printer get name', (err, stdout) => {
        if (err) {
            return res.json({ printers: [] });
        }
        const lines = stdout.split('\n')
            .map(l => l.trim())
            .filter(l => l && l !== 'Name');
        res.json({ printers: lines });
    });
});

/**
 * Health check (no auth required)
 */
app.get('/health', (req, res) => {
    res.json({
        status: 'running',
        port: PORT,
        platform: os.platform(),
        hostname: os.hostname(),
        timestamp: new Date().toISOString(),
    });
});

// Start server
app.listen(PORT, () => {
    console.log(`╔══════════════════════════════════════════╗`);
    console.log(`║  D Star Company Print Proxy             ║`);
    console.log(`║  Running on http://localhost:${PORT}       ║`);
    console.log(`║  Platform: ${os.platform()}                          ║`);
    console.log(`╚══════════════════════════════════════════╝`);
    console.log('');
    console.log('Endpoints:');
    console.log(`  POST /print/escpos   - ESC/POS thermal printer`);
    console.log(`  POST /print/windows  - Windows printer`);
    console.log(`  GET  /printers       - List printers`);
    console.log(`  GET  /health         - Status check`);
});
