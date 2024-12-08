<?php
// Koneksi ke database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fpp";

$conn = new mysqli($servername, $username, $password, $dbname);

// Periksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Ambil semua tabel
$tablesQuery = $conn->query("SHOW TABLES");
$tablesData = [];

while ($table = $tablesQuery->fetch_array()) {
    $tableName = $table[0];
    $dataQuery = $conn->query("SELECT * FROM $tableName LIMIT 100"); // Batasi 100 baris
    $data = [];
    while ($row = $dataQuery->fetch_assoc()) {
        $data[] = $row;
    }
    $tablesData[$tableName] = $data;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dynamic Pivot Table</title>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- jQuery UI -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>

    <!-- D3.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/d3/5.16.0/d3.min.js"></script>

    <!-- C3.js -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/c3/0.7.20/c3.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/c3/0.7.20/c3.min.js"></script>

    <!-- PivotTable.js -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pivottable@2.23.0/dist/pivot.min.css">
    <script src="https://cdn.jsdelivr.net/npm/pivottable@2.23.0/dist/pivot.min.js"></script>

    <!-- PivotTable C3 Renderers -->
    <script src="https://cdn.jsdelivr.net/npm/pivottable@2.23.0/dist/c3_renderers.min.js"></script>

    <!-- Custom CSS -->
    <style>
        body {
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(to right, rgba(0, 128, 255, 1), rgba(0, 255, 255, 0.8)); /* Gradient biru segar ke aqua cerah */
        color: #fff; /* Warna teks untuk kontras yang lebih baik */
         }

         body {
            position: relative;
            overflow: auto; /* Memungkinkan scroll di halaman */
            height: 100vh; /* Pastikan halaman mengisi tinggi layar */
            margin: 0;
        }

        /* Animasi Partikel */
        @keyframes particleMovement {
            0% {
                transform: translateY(0) translateX(0);
                opacity: 1;
            }
            50% {
                transform: translateY(-50px) translateX(50px);
                opacity: 0.5;
            }
            100% {
                transform: translateY(0) translateX(0);
                opacity: 1;
            }
        }

        /* Partikel yang berada di belakang konten */
        .particle {
            position: fixed;
            width: 10px;
            height: 10px;
            background-color: rgba(255, 255, 255, 0.7);
            border-radius: 50%;
            animation: particleMovement 4s ease-in-out infinite;
            z-index: -1; /* Pastikan partikel di belakang konten */
        }

        .particle:nth-child(odd) {
            background-color: rgba(0, 255, 255, 0.7);
            animation-duration: 5s;
        }

        .particle:nth-child(even) {
            background-color: rgba(0, 128, 255, 0.7);
            animation-duration: 6s;
          }
                /* Kontainer utama */
        .container {
            max-width: 800px;  /* Sesuaikan lebar maksimal sesuai kebutuhan */
            margin: 50px auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            padding: 15px; /* Mengurangi padding untuk ruang yang lebih sempit */
        }

        /* Kontainer Pivot Table */
        #pivot-table-container {
            margin-top: 30px;
            max-height: 500px; /* Menambahkan batasan tinggi */
            overflow: auto; /* Menambahkan scroll jika konten lebih besar */
            width: 100%;
            border: 1px solid #ccc;
            padding: 10px;
            border-radius: 5px;
            background-color: #fff;
        }


        h2 {
            text-align: center;
            color: #444;
            font-weight: bold;
            margin-bottom: 30px;
        }

        label {
            font-size: 1.2em;
            font-weight: bold;
            margin-bottom: 10px;
            display: block;
        }

        select {
            width: 100%;
            padding: 10px;
            font-size: 1em;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        #pivot-table-container {
            margin-top: 30px;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 0.9em;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>📊 Dynamic Pivot Table</h2>

        <!-- Dropdown untuk memilih tabel -->
        <label for="table-selector">Pilih Tabel:</label>
        <select id="table-selector">
            <option value="">-- Pilih Tabel --</option>
            <?php foreach ($tablesData as $table => $data): ?>
                <option value="<?php echo $table; ?>"><?php echo $table; ?></option>
            <?php endforeach; ?>
        </select>

        <div id="pivot-table-container">
            <p style="text-align: center; color: #777;">Silakan pilih tabel untuk memulai.</p>
        </div>
    </div>

    <script>
// Fungsi untuk membuat partikel secara dinamis
function createParticles() {
    const numberOfParticles = 30; // Jumlah partikel yang ingin ditambahkan
    const windowHeight = window.innerHeight; // Menyimpan tinggi jendela
    const windowWidth = window.innerWidth; // Menyimpan lebar jendela

    for (let i = 0; i < numberOfParticles; i++) {
        const particle = document.createElement('div');
        particle.classList.add('particle');

        // Menentukan posisi acak untuk partikel di seluruh halaman
        const particleX = Math.random() * windowWidth;
        const particleY = Math.random() * windowHeight;

        // Menempatkan partikel pada posisi acak di jendela
        particle.style.top = `${particleY}px`;
        particle.style.left = `${particleX}px`;

        // Menambahkan partikel ke body
        document.body.appendChild(particle);
    }
} 

// Memanggil fungsi untuk membuat partikel
createParticles();
</script>

    <script>
        $(document).ready(function () {
            const tablesData = <?php echo json_encode($tablesData); ?>;

            console.log("Data yang diterima dari server:", tablesData);

            // Event listener untuk dropdown
            $("#table-selector").on("change", function () {
                const selectedTable = $(this).val();

                if (!selectedTable || !tablesData[selectedTable]) {
                    $("#pivot-table-container").html("<p style='text-align: center; color: #777;'>Tidak ada data untuk ditampilkan.</p>");
                    return;
                }

                // Bersihkan container sebelum render ulang
                $("#pivot-table-container").empty();

                // Render PivotTable
                try {
                    $("#pivot-table-container").pivotUI(tablesData[selectedTable], {
                        renderers: $.extend(
                            $.pivotUtilities.renderers,
                            $.pivotUtilities.c3_renderers
                        ),
                        rendererName: "Table", // Default renderer
                        rows: [], // Default rows
                        cols: [], // Default cols
                        aggregatorName: "Count" // Default aggregator
                    });
                } catch (error) {
                    console.error("Error saat merender PivotTable:", error);
                    $("#pivot-table-container").html("<p style='text-align: center; color: red;'>Terjadi kesalahan dalam memuat data.</p>");
                }
            });
        });
    </script>
</body>
</html>
