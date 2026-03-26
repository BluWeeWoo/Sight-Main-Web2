<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lumi - Eye Health Management</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            background: white;
            border-radius: 20px;
            padding: 60px 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 400px;
            width: 100%;
        }

        h1 {
            font-size: 32px;
            color: #333;
            margin-bottom: 40px;
            font-weight: 600;
        }

        .button-group {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-doctor {
            background-color: #1abc9c;
            color: white;
        }

        .btn-doctor:hover {
            background-color: #16a085;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(26, 188, 156, 0.3);
        }

        .btn-admin {
            background-color: #2c3e50;
            color: white;
        }

        .btn-admin:hover {
            background-color: #1a252f;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(44, 62, 80, 0.3);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome</h1>
        <div class="button-group">
            <a href="{{ route('doctor.login') }}" class="btn btn-doctor">Doctor Login</a>
            <a href="{{ route('admin.login') }}" class="btn btn-admin">Admin Login</a>
        </div>
    </div>
</body>
</html>
