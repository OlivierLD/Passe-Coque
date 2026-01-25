<?php
// A Test
try {
    $db = new SQLite3('./sql/weather.db');

    $results = $db->query('SELECT COUNT(*) FROM STATIONS');
    while ($row = $results->fetchArray()) {
        var_dump($row);
        echo ("<br/>" . PHP_EOL);
        echo ("We have " . $row[0] . " entries in the STATIONS table.<br/>" . PHP_EOL);
    }
    echo "Done.<br/>" . PHP_EOL;
    $db->close();
    echo "And disconnected.<br/>" . PHP_EOL;

} catch (Throwable $bam) {
    echo "[Captured Throwable (big) for db.connect.php : " . $bam . "] " . PHP_EOL;
}
?>
