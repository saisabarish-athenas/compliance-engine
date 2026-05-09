<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Compliance Platform</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        body {
            font-family: Arial, sans-serif;
            background:#f5f6fa;
            margin:0;
            padding:20px;
        }

        .container{
            max-width:1200px;
            margin:auto;
            background:white;
            padding:20px;
            border-radius:8px;
            box-shadow:0 2px 10px rgba(0,0,0,0.1);
        }

        .header{
            margin-bottom:20px;
        }
    </style>
</head>
<body>

<div class="container">

    <div class="header">
        <h2>Labour Compliance Platform</h2>
    </div>

    @yield('content')

</div>

</body>
</html>
