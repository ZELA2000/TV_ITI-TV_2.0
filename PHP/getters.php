<?php
function connectDatabase() {
    require ('infoAccess.php');

    $conn = new mysqli($serverConn, $usernameConn, $passwordConn, $dbnameConn);

    if ($conn->connect_error) {
        die(json_encode(array("error" => "Connessione al database fallita: " . $conn->connect_error)));
    }

    return $conn;
}

function deleteOldEntries($conn, $tag, $table) {
    $sql = "DELETE FROM $table WHERE Data_fine < CURDATE() AND tag = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $tag);
    $stmt->execute();
    $stmt->close();
}

function deleteOldEntriesComponentiAggiuntivi($conn, $table) {
    $sql = "DELETE FROM $table WHERE Data_fine < CURDATE()";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die(json_encode(array("error" => "Errore nella preparazione della query: " . $conn->error)));
    }

    $stmt->execute();
    $stmt->close();
}

function getEntriesComunicazioni($conn, $tag, $table) {
    $sql = "SELECT * FROM $table WHERE tag = ? AND (Data_inizio <= CURDATE() OR Data_inizio IS NULL)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $tag);
    $stmt->execute();
    $result = $stmt->get_result();

    $rows = array();
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }

    $stmt->close();
    return $rows;
}

function getEntriesComponentiAggiuntivi($conn, $table){
    $sql = "SELECT * FROM $table WHERE (Data_inizio <= CURDATE() OR Data_inizio IS NULL)";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();

    $rows = array();
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }

    $stmt->close();
    return $rows;
}

function getEntriesEventiGiornalieri($conn) {
    $sql = "SELECT * FROM eventi_giornalieri WHERE Mese = MONTH(CURDATE()) AND Giorno = DAY(CURDATE())";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();

    $rows = array();
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }

    $stmt->close();
    return $rows;
}

function getEntriesProgrammazione($conn){
    $sql = "SELECT * FROM `programmazione`";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();

    $rows = array();
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }

    $stmt->close();
    return $rows;
}

function getEmergenze() {
    $conn = connectDatabase();
    deleteOldEntries($conn, 'emergenza', 'comunicazione');
    $rows = getEntriesComunicazioni($conn, 'emergenza', 'comunicazione');
    $conn->close();
    header('Content-Type: application/json');
    echo json_encode($rows);
}

function getNews() {
    $conn = connectDatabase();
    deleteOldEntries($conn, 'news', 'comunicazione');
    $rows = getEntriesComunicazioni($conn, 'news', 'comunicazione');
    $conn->close();
    header('Content-Type: application/json');
    echo json_encode($rows);
}

function getComponentiAggiuntivi() {
    $conn = connectDatabase();
    deleteOldEntriesComponentiAggiuntivi($conn, 'componenti_aggiuntivi');
    $rows = getEntriesComponentiAggiuntivi($conn, 'componenti_aggiuntivi');
    $conn->close();
    header('Content-Type: application/json');
    echo json_encode($rows);
}

function getEventiGiornalieri() {
    $conn = connectDatabase();
    $rows = getEntriesEventiGiornalieri($conn);
    $conn->close();
    header('Content-Type: application/json');
    echo json_encode($rows);
}

function getComunicazioni() {
    $conn = connectDatabase();
    deleteOldEntries($conn, 'comunicazione', 'comunicazione');
    $rows = getEntriesComunicazioni($conn, 'comunicazione', 'comunicazione');
    $conn->close();
    header('Content-Type: application/json');
    echo json_encode($rows);
}

function getProgrammazione(){
    $conn = connectDatabase();
    $rows = getEntriesProgrammazione($conn);
    $conn->close();
    header('Content-Type: application/json');
    echo json_encode($rows);
}

if (isset($_GET['action'])) {
    if ($_GET['action'] == 'getEmergenze') {
        getEmergenze();
        require ('GestoreNumeroPagine.php');
    } elseif ($_GET['action'] == 'getNews') {
        getNews();
        require ('GestoreNumeroPagine.php');
    } elseif ($_GET['action'] == 'getComponentiAggiuntivi') {
        getComponentiAggiuntivi();
        require ('GestoreNumeroPagine.php');
    } elseif ($_GET['action'] == 'getEventiGiornalieri') {
        getEventiGiornalieri();
        require ('GestoreNumeroPagine.php');
    } elseif ($_GET['action'] == 'getComunicazioni') {
        getComunicazioni();
        require ('GestoreNumeroPagine.php');
    } elseif ($_GET['action'] == 'getProgrammazione') {
        require ('GestoreNumeroPagine.php');
        getProgrammazione();
    } else {
        echo json_encode(array("error" => "Azione non valida"));
    }
} else {
    echo json_encode(array("error" => "Azione non specificata"));
}