<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fixed Header Table</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Header 1</th>
                    <th>Header 2</th>
                    <th>Header 3</th>
                    <th>Header 4</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Data 1</td>
                    <td>Data 2</td>
                    <td>Data 3</td>
                    <td>Data 4</td>
                </tr>
                <!-- Add more rows as needed -->
            </tbody>
        </table>
    </div>
</body>
</html>
/* styles.css */
body {
    font-family: Arial, sans-serif;
}

.table-container {
    width: 100%;
    height: 400px; /* Adjust the height as needed */
    overflow-y: auto;
    position: relative;
}

table {
    width: 100%;
    border-collapse: collapse;
}

thead {
    position: sticky;
    top: 0;
    background-color: #f1f1f1;
    z-index: 1;
}

th, td {
    padding: 8px 12px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

tbody tr:nth-child(even) {
    background-color: #f9f9f9;
}
