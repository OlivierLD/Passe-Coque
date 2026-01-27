<?php
// A Test
try {
    $db = new SQLite3('./sql/weather.db');

    $sql = 'SELECT COUNT(*) FROM WEATHER_DATA';

    if (true) {
        $results = $db->query($sql);
        var_dump($results);
    } else {
        $stmt = $db->prepare($sql);
        var_dump($stmt);
    // $stmt->bindValue(':station_name', $fullName, SQLITE3_TEXT);
        $results = $stmt->execute();
    }

    var_dump($results);
    echo ("<br/>" . PHP_EOL);

    while ($row = $results->fetchArray()) {
        var_dump($row);
        echo ("<br/>" . PHP_EOL);
        echo ("We have " . $row[0] . " entries in the WEATHER_DATA table (" . count($row) . ")<br/>" . PHP_EOL);
    }
    echo "Done.<br/>" . PHP_EOL;
    $db->close();
    echo "And disconnected.<br/>" . PHP_EOL;

} catch (Throwable $bam) {
    echo "[Captured Throwable for db.connect.php : " . $bam . "] " . PHP_EOL;
}
?>
