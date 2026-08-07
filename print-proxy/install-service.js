const { execSync } = require('child_process');
const path = require('path');

const installDir = __dirname;
const nodeExe = process.execPath;
const serverJs = path.join(installDir, 'server.js');

console.log('Installing D Star Company Print Proxy as Windows Service...');
console.log(`Node: ${nodeExe}`);
console.log(`Script: ${serverJs}`);
console.log('');

try {
    const cmd = `sc create "DStarPrintProxy" binPath= "\\"${nodeExe}\\" \\"${serverJs}\\"" start= auto DisplayName= "D Star Company Print Proxy"`;
    console.log(`Running: ${cmd}`);
    execSync(cmd, { stdio: 'inherit' });
    console.log('');
    console.log('Service created. To start:');
    console.log('  sc start DStarPrintProxy');
    console.log('To stop:');
    console.log('  sc stop DStarPrintProxy');
    console.log('To remove:');
    console.log('  sc delete DStarPrintProxy');
} catch (e) {
    console.log('Service install failed. Run manually:');
    console.log(`  node "${serverJs}"`);
}
