<?php
namespace iutnc\deefy\auth;

use iutnc\deefy\auth\AuthnException;

class AuthnProvider {
    private \PDO $pdo;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function register($email, $password, $passwordConfirm) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new AuthnException("Email invalide.");
        }
    
        if ($password !== $passwordConfirm) {
            throw new AuthnException("Les mots de passe ne correspondent pas.");
        }
    
        if (strlen($password) < 10) {
            throw new AuthnException("Le mot de passe doit contenir au moins 10 caractères.");
        }
    
        $stmt = $this->pdo->prepare("SELECT * FROM User WHERE email = ?");
        $stmt->execute([$email]);
    
        if ($stmt->rowCount() > 0) {
            throw new AuthnException("Un compte avec cet email existe déjà.");
        }
    
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("INSERT INTO User (email, passwd, role) VALUES (?, ?, ?)");
        $stmt->execute([$email, $hashedPassword, 1]);
    

        $userId = $this->pdo->lastInsertId();
        $_SESSION['user'] = $email;
        $_SESSION['user_id'] = $userId;
    }

    public function signin($email, $password) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || empty($password)) {
            throw new AuthnException("Identifiants invalides.");
        }
    
        $stmt = $this->pdo->prepare("SELECT * FROM User WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
    
        if (!$user || !password_verify($password, $user['passwd'])) {
            throw new AuthnException("Identifiant ou mot de passe incorrect.");
        }
    
        $_SESSION['user'] = $user['email'];
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] =$user['role'];
    }

    public function getUserId(string $email): ?int {
        $stmt = $this->pdo->prepare("SELECT id FROM User WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $user ? (int) $user['id'] : null;
    }
    public function getUserRoleById(int $userId): ?int {
        $stmt = $this->pdo->prepare("SELECT role FROM User WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $user ? (int) $user['role'] : null;
    }
}