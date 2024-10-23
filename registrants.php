<?php
session_start();
require_once 'db_config.php';

// Include PhpSpreadsheet classes
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

require 'vendor/autoload.php';

if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: login.php");
    exit();
}

// Get all events for the filter dropdown
$events_query = $pdo->query("SELECT id, title FROM events ORDER BY date_time");
$events = $events_query->fetchAll();

// Handle event filter
$event_filter = isset($_GET['event_id']) ? (int)$_GET['event_id'] : null;

// Handle the export action
if (isset($_GET['action']) && $_GET['action'] == 'export') {
    try {
        // Prepare the data for export
        $export_query = "
            SELECT 
                u.first_name,
                u.last_name,
                u.email,
                u.phone_number,
                u.country,
                e.title as event_title,
                r.registration_date
            FROM registrations r
            JOIN users u ON r.user_id = u.id
            JOIN events e ON r.event_id = e.id
        ";
        
        // Add filter if event is selected
        if ($event_filter) {
            $export_query .= " WHERE e.id = :event_id";
        }
        
        $export_query .= " ORDER BY r.registration_date DESC";
        
        $stmt = $pdo->prepare($export_query);
        if ($event_filter) {
            $stmt->bindParam(':event_id', $event_filter);
        }
        $stmt->execute();
        $registrants = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Create a new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set the header row
        $headers = ['First Name', 'Last Name', 'Email', 'Phone Number', 'Country', 'Event Title', 'Registration Date'];
        $sheet->fromArray($headers, NULL, 'A1');
        
        // Fill data
        $rowNumber = 2;
        foreach ($registrants as $registrant) {
            $sheet->setCellValue('A' . $rowNumber, $registrant['first_name']);
            $sheet->setCellValue('B' . $rowNumber, $registrant['last_name']);
            $sheet->setCellValue('C' . $rowNumber, $registrant['email']);
            $sheet->setCellValue('D' . $rowNumber, $registrant['phone_number']);
            $sheet->setCellValue('E' . $rowNumber, $registrant['country']);
            $sheet->setCellValue('F' . $rowNumber, $registrant['event_title']);
            $sheet->setCellValue('G' . $rowNumber, $registrant['registration_date']);
            $rowNumber++;
        }
        
        // Set the header to force download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="registrants.xlsx"');
        header('Cache-Control: max-age=0');
        
        // Write the file to the output
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit();
    } catch (Exception $e) {
        $error_message = "Error exporting registrants: " . $e->getMessage();
    }
}

// Updated query to include country
$query = "
    SELECT 
        u.first_name,
        u.last_name,
        u.email,
        u.phone_number,
        u.country,
        e.title as event_title,
        r.registration_date
    FROM registrations r
    JOIN users u ON r.user_id = u.id
    JOIN events e ON r.event_id = e.id
";

// Add filter if event is selected
if ($event_filter) {
    $query .= " WHERE e.id = :event_id";
}

$query .= " ORDER BY r.registration_date DESC";

try {
    $stmt = $pdo->prepare($query);
    if ($event_filter) {
        $stmt->bindParam(':event_id', $event_filter);
    }
    $stmt->execute();
    $registrants = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_message = "Error fetching registrants: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrants - Division Defence Expo 2024</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'navbar-admin.php'; ?>
    
    <div class="container">
        <h2>Event Registrants</h2>
        
        <!-- Event Filter Form -->
        <form method="get" action="" class="filter-form">
            <select name="event_id" onchange="this.form.submit()">
                <option value="">All Events</option>
                <?php foreach ($events as $event): ?>
                    <option value="<?php echo $event['id']; ?>" <?php echo $event_filter == $event['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($event['title']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <form method="get" action="">
            <input type="hidden" name="action" value="export">
            <?php if ($event_filter): ?>
            <input type="hidden" name="event_id" value="<?php echo $event_filter; ?>">
            <?php endif; ?>
        <button type="submit" class="export-button">Export to Excel</button>
        </form>

        <?php if (empty($registrants)): ?>
            <p>No registrants found.</p>
        <?php else: ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Country</th>
                            <th>Event</th>
                            <th>Registration Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($registrants as $registrant): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($registrant['first_name'] . ' ' . $registrant['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($registrant['email']); ?></td>
                                <td><?php echo htmlspecialchars($registrant['phone_number']); ?></td>
                                <td><?php echo htmlspecialchars($registrant['country']); ?></td>
                                <td><?php echo htmlspecialchars($registrant['event_title']); ?></td>
                                <td><?php echo date('Y-m-d H:i', strtotime($registrant['registration_date'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>