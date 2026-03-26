<?php
// filepath: c:\xampp1\htdocs\ACE\admin\debug_queries.php
require_once __DIR__ . '/_inc.php';
require_admin();

echo "<h3>Database Connection Test</h3>";
echo "Connected: " . ($conn ? "Yes" : "No") . "<br>";

echo "<h3>Table Structure</h3>";
$result = $conn->query("DESCRIBE queries");
echo "<table border='1'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$row['Field']}</td>";
    echo "<td>{$row['Type']}</td>";
    echo "<td>{$row['Null']}</td>";
    echo "<td>{$row['Key']}</td>";
    echo "<td>{$row['Default']}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h3>Total Queries Count</h3>";
$count = $conn->query("SELECT COUNT(*) as total FROM queries")->fetch_assoc();
echo "Total records: " . $count['total'] . "<br>";

echo "<h3>Sample Data (Raw)</h3>";
$result = $conn->query("SELECT * FROM queries LIMIT 5");
echo "<pre>";
while ($row = $result->fetch_assoc()) {
    print_r($row);
}
echo "</pre>";

echo "<h3>Check for missing columns</h3>";
$result = $conn->query("SELECT * FROM queries LIMIT 1");
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo "Columns in first row: " . implode(", ", array_keys($row)) . "<br>";
    
    $required = ['query_id', 'full_name', 'email', 'message', 'submitted_at', 'status'];
    echo "<br>Checking required columns:<br>";
    foreach ($required as $col) {
        echo "$col: " . (array_key_exists($col, $row) ? "✅ EXISTS" : "❌ MISSING") . "<br>";
    }
}
?>