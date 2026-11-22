<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Something went wrong') - Laptop Store</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 1.5rem; background: #f3f4f6; color: #111827; font: 16px/1.5 system-ui, sans-serif; }
        main { width: min(100%, 34rem); padding: 2rem; border: 1px solid #e5e7eb; border-radius: 1rem; background: #fff; box-shadow: 0 10px 24px #11182712; text-align: center; }
        .code { margin: 0; color: #4f46e5; font-size: 3rem; font-weight: 800; }
        h1 { margin: .25rem 0 1rem; font-size: 1.5rem; }
        p { color: #4b5563; }
        a { display: inline-block; margin-top: 1rem; padding: .65rem 1rem; border-radius: .5rem; background: #4f46e5; color: white; text-decoration: none; }
    </style>
</head>
<body>
    <main>
        <p class="code">@yield('code')</p>
        <h1>@yield('title')</h1>
        <p>@yield('message')</p>
        <a href="{{ url('/') }}">Return to marketplace</a>
    </main>
</body>
</html>
