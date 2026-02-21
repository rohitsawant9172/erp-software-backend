const http = require('http');

const PORT = 9000;

const server = http.createServer((req, res) => {
    let body = '';

    req.on('data', chunk => {
        body += chunk.toString();
    });

    req.on('end', () => {
        console.log(`\n\x1b[36m[TALLY MOCK SERVER] Received ${req.method} Request:\x1b[0m`);
        console.log(`\x1b[33mHEADERS:\x1b[0m`, req.headers);
        console.log(`\x1b[32mBODY:\x1b[0m\n${body}`);

        const responseXml = `<?xml version="1.0"?><RESPONSE><CREATED>1</CREATED><ALTERED>0</ALTERED><DELETED>0</DELETED><LASTVCHID>1001</LASTVCHID><LASTMID>0</LASTMID><COMBINED>1</COMBINED><ERRORS>0</ERRORS></RESPONSE>`;

        res.writeHead(200, {
            'Content-Type': 'text/xml',
            'Connection': 'close'
        });

        res.end(responseXml);
        console.log('\x1b[34m[TALLY MOCK SERVER] Responded with Success XML (Created 1).\x1b[0m\n');
    });
});

server.listen(PORT, '127.0.0.1', () => {
    console.log(`🚀 Tally ERP Mock Server is listening tightly on http://127.0.0.1:${PORT}`);
    console.log('Waiting for backend sync commands...');
});
