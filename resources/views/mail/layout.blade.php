<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            line-height: 1.5;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            font-family: 'League Spartan', sans-serif;
            font-weight: bold;
            font-size: 40px;
        }

        .header img {
            max-width: 100%;
            height: auto;
        }

        .headline {
            font-size: 24px;
            margin-bottom: 20px;
        }

        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #0A001C;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
        }

        .button:hover,
        .button:focus,
        .button:visited,
        .button:active {
            color: #ffffff !important;
        }

        .footer {
            padding: 10px;
            font-size: 12px;
        }

        .footer a {
            color: #333333;
            text-decoration: none;
        }

        .footer p {
            margin: 0px;
            padding: 0px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            musedoc.
        </div>
        <div class="body">
            @yield('content')
        </div>
        <hr>
        <div class="footer">
            <p>
                &copy; {{ date('Y') }}
                <a href="https://musedoc.app">{{ config('app.name') }}</a>
                {{ trans('common.copyright') }}
            </p>
            <p><a href="#">{{ trans('mail.unsubscribe') }}</a></p>
        </div>
    </div>
</body>

</html>