<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Meu Sistema</title>
    <style>
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f3f3f3;
        }
        .login-container {
            background: white;
            padding: 2rem;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            text-align: center;
            width: 320px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .login-container img {
            width: 150px;
            margin-bottom: 1rem;
        }
        .login-container h2 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            font-weight: 400;
        }
        form {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
        }
        .input-field {
            width: 90%;
            padding: 10px;
            margin-bottom: 1rem;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
        }
        .login-btn {
            width: 90%;
            padding: 10px;
            background: #0078D4;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
        }
        .login-btn:hover {
            background: #005A9E;
        }
        .forgot-password {
            margin-top: 1rem;
            font-size: 0.9rem;
            color: #0078D4;
            text-decoration: none;
        }
    </style>
</head>
<body>


    <div class="login-container">

        <?php
        if(isset($_GET["aviso"])){
             echo $_GET["aviso"];
        }
        ?>
        <img src="https://upload.wikimedia.org/wikipedia/commons/4/44/Microsoft_logo.svg" alt="Microsoft Logo">
        <h2>Login</h2>
        <form action="conexao.php" method="POST">
            <input type="text" name="email" class="input-field" placeholder="E-mail ou telefone" required>
            <input type="password" name="password" class="input-field" placeholder="Senha" required>
            <button type="submit" class="login-btn">Entrar</button>
        </form>
        <a href="#" class="forgot-password">Esqueceu a senha?</a>
    </div>
</body>
</html>