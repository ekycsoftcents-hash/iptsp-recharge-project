<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>IPTSP Recharge SaaS</title>
    <style>
        :root { color-scheme: dark; font-family: Inter, ui-sans-serif, system-ui, sans-serif; }
        body { margin: 0; background: #0b1220; color: #e5e7eb; }
        .wrap { max-width: 1180px; margin: 0 auto; padding: 42px 22px; }
        .top { display: flex; justify-content: space-between; align-items: center; gap: 20px; margin-bottom: 34px; }
        .brand { font-size: 24px; font-weight: 800; letter-spacing: -.03em; }
        .muted { color: #94a3b8; }
        .badge { color: #86efac; border: 1px solid #166534; background: #052e16; border-radius: 999px; padding: 7px 12px; font-size: 13px; }
        .hero { border: 1px solid #1e293b; border-radius: 20px; padding: 30px; background: linear-gradient(135deg, #111c34, #0f172a); margin-bottom: 22px; }
        h1 { margin: 0 0 10px; font-size: clamp(30px, 5vw, 52px); letter-spacing: -.05em; }
        .hero p { max-width: 720px; line-height: 1.7; color: #b6c2d3; }
        .grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; }
        .card { border: 1px solid #1e293b; border-radius: 16px; padding: 20px; background: #111827; }
        .label { color: #94a3b8; font-size: 13px; }
        .value { display: block; margin-top: 10px; font-size: 26px; font-weight: 750; }
        .section { margin-top: 22px; display: grid; grid-template-columns: 1.2fr .8fr; gap: 16px; }
        ul { padding-left: 20px; line-height: 2; color: #cbd5e1; }
        .warning { border-left: 4px solid #f59e0b; background: #422006; padding: 16px 18px; border-radius: 10px; line-height: 1.6; color: #fde68a; }
        @media (max-width: 800px) { .grid, .section { grid-template-columns: 1fr 1fr; } .top { align-items: flex-start; flex-direction: column; } }
        @media (max-width: 520px) { .grid, .section { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<div class="wrap">
    <div class="top">
        <div>
            <div class="brand">IPTSP Recharge SaaS</div>
            <div class="muted">Multi-tenant reseller recharge platform</div>
        </div>
        <div class="badge">Server online</div>
    </div>

    <section class="hero">
        <h1>Welcome to your IPTSP panel</h1>
        <p>This basic dashboard confirms that Laravel, PHP, database connectivity, HTTPS, and the aaPanel document root are working. Tenant login, provider API execution, and PipraPay checkout are the next implementation layer.</p>
    </section>

    <section class="grid">
        <div class="card"><span class="label">Wallet balance</span><strong class="value">৳ 0.00</strong></div>
        <div class="card"><span class="label">Customers</span><strong class="value">0</strong></div>
        <div class="card"><span class="label">Today’s recharge</span><strong class="value">0</strong></div>
        <div class="card"><span class="label">Subscription</span><strong class="value">Pending</strong></div>
    </section>

    <section class="section">
        <div class="card">
            <h2>Enabled provider adapters</h2>
            <ul><li>iTalk adapter boundary</li><li>Ranksitt adapter boundary</li><li>WebVoice adapter boundary</li></ul>
        </div>
        <div class="card">
            <h2>Next steps</h2>
            <p class="muted">Create the first tenant, configure provider credentials, connect PipraPay, and enable authenticated recharge workflows.</p>
        </div>
    </section>

    <div class="warning" style="margin-top: 22px;">Demo status: this page is an operational starter dashboard. Do not use it for real recharge or payment collection until authentication, authorization, provider API calls, webhook verification, and end-to-end tests are completed.</div>
</div>
</body>
</html>
