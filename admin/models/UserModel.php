    <?php
    class UserModel {
        private PDO $pdo;

        public function __construct(PDO $pdo) {
            $this->pdo = $pdo;
        }

     


        public function getAllUsers()
    {
        $stmt = $this->pdo->prepare("
            SELECT  users.user_id,users.user_name, users.email,users.profile,roles.role_name,users.created_at
            FROM users
            LEFT JOIN roles ON users.role_id = roles.role_id
            ORDER BY users.user_id DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
        public function getUserByUsername(string $username): ?array {
            $stmt = $this->pdo->prepare("SELECT * FROM Users WHERE user_name = :user_name");
            $stmt->bindParam(':user_name', $username, PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            return $user ?: null;
        }
   


    public function registerUser(string $username, string $email, string $password, int $role, string $profileImage = 'uploads/users/default.png'): bool {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->pdo->prepare("INSERT INTO Users (user_name, email, password, role_id, profile_image) VALUES (:user_name, :email, :password, :role, :profile_image)");
        $stmt->bindParam(':user_name', $username, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
        $stmt->bindParam(':role', $role, PDO::PARAM_INT);
        $stmt->bindParam(':profile_image', $profileImage, PDO::PARAM_STR);
        return $stmt->execute();
    }

     public function updateUser(int $user_id, string $username, string $email, int $role_id, ?string $profile): bool {
        $stmt = $this->pdo->prepare("
            UPDATE users SET 
                user_name = :user_name,
                email = :email,
                role_id = :role_id,
                profile = :profile
            WHERE user_id = :user_id
        ");
        return $stmt->execute([
            ':user_id'   => $user_id,
            ':user_name' => $username,
            ':email'     => $email,
            ':role_id'   => $role_id,
            ':profile'   => $profile
        ]);
    }





        public function deleteUser($user_id)
    {
        $sql = "DELETE FROM Users WHERE user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([':user_id' => $user_id]);



        }
        public function deleteUserById(int $userId): bool
{
    $pdo = "DELETE FROM users WHERE user_id = :user_id";
    $stmt = $this->pdo->prepare($pdo);

    return $stmt->execute([
        ':user_id' => $userId
    ]);
}

        public function updatePassword(string $username, string $password,){
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT); // encrypyt the password
            $stmt = $this->pdo->prepare("UPDATE users SET password = :password WHERE user_name = :user_name");
            $stmt->bindParam(':user_name', $username, PDO::PARAM_STR);
            $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
            return $stmt->execute();
        }

        public function getRoles() {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM Roles");
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                // Handle error
                echo "Error fetching roles: " . $e->getMessage();
                return [];
            }
        }
        public function updateRole(string $username, string $role){
            $stmt = $this->pdo->prepare("UPDATE Users SET role = :role WHERE user_name = :user_name");
            $stmt->bindParam(':user_name', $username, PDO::PARAM_STR);
            $stmt->bindParam(':role', $role, PDO::PARAM_STR);
            return $stmt->execute();
        }   

        public function deleteRole(string $username, string $role){
            $stmt = $this->pdo->prepare("DELETE FROM Users WHERE user_name = :user_name AND role_id = :role_id");
            $stmt->bindParam(':user_name', $username, PDO::PARAM_STR);
            $stmt->bindParam(':role', $role, PDO::PARAM_STR);
            return $stmt->execute();
        }

        public function deleteRoleByName(string $username, string $role){
            $stmt = $this->pdo->prepare("DELETE FROM Users WHERE user_name = :user_name AND role = :role");
            $stmt->bindParam(':user_name', $username, PDO::PARAM_STR);
            $stmt->bindParam(':role', $role, PDO::PARAM_STR);
            return $stmt->execute();

        }
   
        

    public function updateUserPassword(string $username, string $password)
    {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT); // encrypt the password

        $stmt = $this->pdo->prepare("UPDATE Users SET password = :password WHERE user_name = :user_name");

        $stmt->bindParam(':user_name', $username, PDO::PARAM_STR);
        $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);

        return $stmt->execute();

    }

    public function updateProfileImage(int $user_id, ?string $profileImage): bool
        {
            if (!$profileImage) {
                throw new Exception("Profile image is not provided.");
            }

            $stmt = $this->pdo->prepare(
                "UPDATE Users SET profile = :profile WHERE user_id = :user_id"
            );

            $stmt->execute([
                ':profile' => $profileImage,
                ':user_id' => $user_id
            ]);

            return $stmt->rowCount() > 0; 
        }
    

    public function getUserByEmail(string $email): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
  
    public function emailExists($email) {
    $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetchColumn() > 0;
}
   

    public function getUserById($id)
{
    $stmt = $this->pdo->prepare("
        SELECT users.user_id, users.user_name, users.email, users.profile, roles.role_name, users.created_at
        FROM users 
        LEFT JOIN roles  ON users.role_id = roles.role_id
        WHERE users.user_id = ?
    ");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

 public function userExists(string $username, string $email): bool {

        $sql = "SELECT COUNT(*) FROM users 
                WHERE user_name = :username OR email = :email";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':username' => $username,
            ':email'    => $email
        ]);

        return $stmt->fetchColumn() > 0;
    }


    public function createUser(
        string $username,
        string $email,
        string $password,
        int $role_id,
        ?string $profile
    ): int {

        $sql = "INSERT INTO users 
                    (user_name, email, password, role_id, profile, created_at)
                VALUES 
                    (:username, :email, :password, :role_id, :profile, NOW())";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':username' => $username,
            ':email'    => $email,
            ':password' => $password,
            ':role_id'  => $role_id,
            ':profile'  => $profile
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function getRandomAgent()
{
    $pdo = "SELECT user_id, user_name, email 
            FROM users 
            WHERE role_id = 2"; // Only agents

    $stmt = $this->pdo->prepare($pdo);
    $stmt->execute();
    $agents = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($agents)) {
        return null;
    }

    // Pick one randomly
    return $agents[array_rand($agents)];
}

}




