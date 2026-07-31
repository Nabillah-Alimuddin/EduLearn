<?php
// run_migration.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(300); // 5 minutes max

echo "<h3>Memulai Migrasi Database ke Supabase...</h3>\n";

try {
    include 'db_connection.php';
    echo "Koneksi ke Supabase berhasil.<br>\n";

    // 1. Membaca dan menjalankan Schema
    $schema_file = 'database/postgresql_schema.sql';
    if (!file_exists($schema_file)) {
        throw new Exception("File schema tidak ditemukan di: " . $schema_file);
    }
    
    echo "Membaca schema dari $schema_file...<br>\n";
    $schema_sql = file_get_contents($schema_file);
    
    // Hilangkan komentar -- agar eksekusi SQL lebih bersih
    $schema_sql = preg_replace('/^\s*--.*$/m', '', $schema_sql);
    
    echo "Menjalankan DDL schema...<br>\n";
    $conn->exec($schema_sql);
    echo "<span style='color:green;'>DDL Schema berhasil dieksekusi!</span><br><br>\n";

    // 2. Membaca dan menjalankan Seed Data
    $seed_file = 'database/seed_data.sql';
    if (!file_exists($seed_file)) {
        throw new Exception("File seed data tidak ditemukan di: " . $seed_file);
    }
    
    echo "Membaca seed data dari $seed_file...<br>\n";
    $seed_sql = file_get_contents($seed_file);
    
    // Hilangkan komentar --
    $seed_sql = preg_replace('/^\s*--.*$/m', '', $seed_sql);
    
    echo "Memasukkan data seed ke Supabase...<br>\n";
    $conn->exec($seed_sql);
    echo "<span style='color:green;'>Data seed berhasil dimasukkan!</span><br><br>\n";

    echo "<h4><span style='color:green;'>MIGRASI DATABASE SELESAI DENGAN SUKSES!</span></h4>\n";

} catch (PDOException $e) {
    echo "<div style='color:red; padding:10px; border:1px solid red; background:#fff5f5;'>\n";
    echo "<strong>PDO Error:</strong> " . htmlspecialchars($e->getMessage()) . "<br>\n";
    echo "Query Error terjadi pada saat eksekusi SQL.\n";
    echo "</div>\n";
} catch (Exception $e) {
    echo "<div style='color:red; padding:10px; border:1px solid red; background:#fff5f5;'>\n";
    echo "<strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "<br>\n";
    echo "</div>\n";
}
?>
