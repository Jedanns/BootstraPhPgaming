<?php
namespace App\Models;

use PDO;

/**
 * Modèle de base dont tous les modèles héritent
 */
class BaseModel
{
    protected $db;
    protected $table;
    
    /**
     * Constructeur
     * 
     * @param string $table Nom de la table
     */
    public function __construct($table)
    {
        $this->db = Database::getInstance()->getConnection();
        $this->table = $table;
    }
    
    /**
     * Récupère tous les enregistrements
     * 
     * @return array
     */
    public function getAll()
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table}");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupère un enregistrement par son ID
     * 
     * @param int $id Identifiant
     * @return array|false
     */
    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Crée un nouvel enregistrement
     * 
     * @param array $data Données à insérer
     * @return int|false ID de l'enregistrement créé ou false en cas d'échec
     */
    public function create($data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $stmt = $this->db->prepare("INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})");
        
        foreach ($data as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        
        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Met à jour un enregistrement
     * 
     * @param int $id Identifiant
     * @param array $data Données à mettre à jour
     * @return bool
     */
    public function update($id, $data)
    {
        $setClause = [];
        
        foreach (array_keys($data) as $key) {
            $setClause[] = "{$key} = :{$key}";
        }
        
        $setClause = implode(', ', $setClause);
        
        $stmt = $this->db->prepare("UPDATE {$this->table} SET {$setClause} WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        foreach ($data as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        
        return $stmt->execute();
    }
    
    /**
     * Supprime un enregistrement
     * 
     * @param int $id Identifiant
     * @return bool
     */
    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}