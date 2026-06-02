const http = require('http');

const clientBaseUrl = 'http://localhost:8000';
const managerBaseUrl = 'http://localhost:8080';

// UUIDs válidos obtidos no banco do ambiente local
const TABLE_UUID = '019e860f-7602-7137-8291-e4613b24537e';
const PRODUCT_UUID = '669008bf-3107-40c6-9575-a71a07057987';
const LICENSE_UUID = '019e862f-506c-726e-82a7-8797171e6be1';

function makeRequest(url, method, data) {
    return new Promise((resolve) => {
        const u = new URL(url);
        const postData = JSON.stringify(data);
        const options = {
            hostname: u.hostname,
            port: u.port,
            path: u.pathname + u.search,
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Content-Length': Buffer.byteLength(postData)
            }
        };

        const req = http.request(options, (res) => {
            let body = '';
            res.setEncoding('utf8');
            res.on('data', (chunk) => body += chunk);
            res.on('end', () => {
                resolve({
                    status: res.statusCode,
                    body: body
                });
            });
        });

        req.on('error', (e) => {
            resolve({
                status: 500,
                error: e.message
            });
        });

        req.write(postData);
        req.end();
    });
}

async function runConcurrencyTests() {
    console.log('=== TESTES DE CONCORRÊNCIA (FASE 7) ===\n');

    // 1. Concorrência nas Mesas (Abrir comanda / lançar itens simultaneamente)
    console.log('1. Disparando 10 pedidos simultâneos no Tablet Mesa...');
    const tabletPromises = [];
    for (let i = 0; i < 10; i++) {
        tabletPromises.push(makeRequest(`${clientBaseUrl}/api/v1/tablet/order`, 'POST', {
            table_uuid: TABLE_UUID,
            items: [
                { uuid: PRODUCT_UUID, quantity: 1 }
            ]
        }));
    }
    const tabletResults = await Promise.all(tabletPromises);
    const tabletSuccess = tabletResults.filter(r => r.status === 200).length;
    console.log(`Resultado Mesa: ${tabletSuccess} com sucesso, ${10 - tabletSuccess} com falha.`);
    console.log('Amostra de resposta:', tabletResults[0]);

    // 2. Concorrência no Delivery (Checkout concorrente de pedidos)
    console.log('\n2. Disparando 10 checkouts de delivery simultâneos...');
    const deliveryPromises = [];
    for (let i = 0; i < 10; i++) {
        const randomSuffix = Math.floor(Math.random() * 1000000);
        const randomCpf = Math.floor(10000000000 + Math.random() * 90000000000).toString();
        deliveryPromises.push(makeRequest(`${clientBaseUrl}/api/v1/delivery/checkout`, 'POST', {
            items: [
                { uuid: PRODUCT_UUID, quantity: 1 }
            ],
            customer_name: `Concur Customer ${i}`,
            customer_phone: '11999999999',
            customer_email: `concur${i}_${randomSuffix}@test.com`,
            customer_cpf: randomCpf, // Usar CPF randômico para evitar conflito de chave única se for o caso
            street: 'Rua das Laranjeiras',
            number: '123',
            complement: 'Apt 42',
            neighborhood: 'Centro',
            city: 'São Paulo',
            state: 'SP',
            zip_code: '01310-100',
            delivery_fee: 10.00,
            payment_method: 'pix',
            gateway: 'asaas',
            lgpd_consent: true
        }));
    }
    const deliveryResults = await Promise.all(deliveryPromises);
    const deliverySuccess = deliveryResults.filter(r => r.status === 200 && !r.body.includes('"exception"')).length;
    console.log(`Resultado Delivery: ${deliverySuccess} com sucesso, ${10 - deliverySuccess} com falha.`);
    const firstDeliveryError = deliveryResults.find(r => r.status !== 200 || r.body.includes('"exception"'));
    if (firstDeliveryError) {
        try {
            const errObj = JSON.parse(firstDeliveryError.body);
            console.log('Mensagem de erro do Delivery:', errObj.message || errObj);
        } catch(e) {
            console.log('Erro bruto do Delivery (primeiros 200 caracteres):', firstDeliveryError.body.substring(0, 200));
        }
    } else {
        console.log('Amostra de resposta do Delivery:', deliveryResults[0]);
    }

    // 3. Concorrência no Licenciamento (Múltiplas ativações simultâneas)
    console.log('\n3. Disparando 10 ativações de licença simultâneas no Manager...');
    const licensePromises = [];
    for (let i = 0; i < 10; i++) {
        const randomInstUuid = '019e861d-' + Math.floor(1000 + Math.random() * 9000) + '-7137-8291-e4613b24537f';
        licensePromises.push(makeRequest(`${managerBaseUrl}/api/licenses/activate`, 'POST', {
            license_uuid: LICENSE_UUID,
            installation_uuid: randomInstUuid,
            hostname: `concur-host-${i}`,
            domain: `concur-${i}.com`,
            ip_address: `192.168.1.${i}`,
            fingerprint: `fingerprint-concur-${i}`
        }));
    }
    const licenseResults = await Promise.all(licensePromises);
    const licenseSuccess = licenseResults.filter(r => r.status === 200).length;
    console.log(`Resultado Licenciamento: ${licenseSuccess} com sucesso, ${10 - licenseSuccess} com falha.`);
    console.log('Amostra de resposta:', licenseResults[0]);
}

runConcurrencyTests().catch(console.error);
