<?php
session_start();
require '../service/database.php';

// Cek jika event_id ada di URL
if (isset($_GET['event_id'])) {
    $event_id = $_GET['event_id'];

    // Ambil data event yang akan diedit
    $query = "SELECT * FROM event WHERE event_id = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, 'i', $event_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $event = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    // Jika event tidak ditemukan, redirect ke dashboard
    if (!$event) {
        header("Location: edit-dashboard.php");
        exit;
    }

    // Ambil field formulir yang ada
    $fields_query = "SELECT * FROM event_form_fields WHERE event_id = ?";
    $stmt = mysqli_prepare($db, $fields_query);
    mysqli_stmt_bind_param($stmt, 'i', $event_id);
    mysqli_stmt_execute($stmt);
    $fields_result = mysqli_stmt_get_result($stmt);
    $fields = mysqli_fetch_all($fields_result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
}

// Proses form submission untuk edit event
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_event'])) {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $rules = $_POST['rules'];
        $location = $_POST['location'];
        $event_date = $_POST['event_date'];
        $event_time = $_POST['event_time'];
        $image_url = $event['image_url'];

        // Cek jika ada gambar baru yang diupload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image_name = basename($_FILES['image']['name']);
            $target_dir = "../uploads/";
            $target_file = $target_dir . $image_name;

            // Pastikan folder upload ada
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image_url = $target_file;
            }
        }

        // Update data event ke database
        $query = "UPDATE event SET event_name = ?, event_description = ?, registration_fee = ?, rules = ?, location = ?, event_date = ?, event_time = ?, image_url = ? WHERE event_id = ?";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, 'ssssssssi', $name, $description, $price, $rules, $location, $event_date, $event_time, $image_url, $event_id);
        if (!mysqli_stmt_execute($stmt)) {
            die("Error updating event: " . mysqli_error($db));
        }
        mysqli_stmt_close($stmt);

        // Proses dan simpan field formulir baru
        if (isset($_POST['fields'])) {
            // Pertama hapus semua field formulir yang ada
            $delete_query = "DELETE FROM event_form_fields WHERE event_id = ?";
            $stmt = mysqli_prepare($db, $delete_query);
            mysqli_stmt_bind_param($stmt, 'i', $event_id);
            if (!mysqli_stmt_execute($stmt)) {
                die("Error deleting old fields: " . mysqli_error($db));
            }
            mysqli_stmt_close($stmt);

            // Insert field formulir baru ke database
            foreach ($_POST['fields'] as $field) {
                $field_name = $field['name'];
                $field_type = $field['type'];
                $is_required = isset($field['is_required']) ? 1 : 0;

                $field_query = "INSERT INTO event_form_fields (event_id, field_name, field_type, is_required, created_at) VALUES (?, ?, ?, ?, NOW())";
                $stmt = mysqli_prepare($db, $field_query);
                mysqli_stmt_bind_param($stmt, 'issi', $event_id, $field_name, $field_type, $is_required);
                if (!mysqli_stmt_execute($stmt)) {
                    die("Error inserting new field: " . mysqli_error($db));
                }
                mysqli_stmt_close($stmt);
            }
        }

        // Redirect ke halaman dashboard setelah update
        header("Location: edit-dashboard.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF- ```php
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Montserrat', sans-serif; background: linear-gradient(to right, rgba(0, 128, 255, 0.8), rgba(0, 255, 255, 0.6)); color: #333; }
        .event-image { width: 100%; height: auto; border-radius: 8px; transition: transform 0.3s ease-in-out; }
        .event-image:hover { transform: scale(1.05); }
        .card-custom { box-shadow: 0 4px 8px rgba(0,0,0,0.1); border-radius: 12px; background: white; }
        .form-label { font-weight: bold; color: #007bff; }
        .form-control { border-radius: 8px; padding: 10px; border: 1px solid #007bff; }
        .btn-custom { border-radius: 30px; padding: 12px 30px; font-weight: bold; text-transform: uppercase; background: linear-gradient(135deg, #00c6ff, #007bff); color: white; }
        .btn-custom:hover { background: linear-gradient(135deg, #007bff, #00c6ff); box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2); }
        .card-header { background: linear-gradient(135deg, #00c6ff, #007bff); color: white; padding: 15px; border-radius: 10px 10px 0 0; }
        .form-control:focus { box-shadow: 0 0 5px rgba(0, 123, 255, 0.5); border-color: #007bff; }
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
          .card-header.custom-bg {
        background-color: #004085; /* Warna biru gelap yang lebih dalam */
        color: white; /* Menjaga teks tetap putih agar kontras */
    }
    </style>
            </head>
            <body>
                <div class="container my-5">
                    <div class="row justify-content-center">
                    <div class="col-md-8">
                <div class="card card-custom">
                    <div class="card-header text-center custom-bg">
                        <h3><i class="fas fa-edit"></i> Edit Event</h3>
                    </div>
                    <div class="card-body p-5">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="name" class="form-label"><i class="fas fa-calendar-alt"></i> Nama Event</label>
                                <input type="text" name="name" id="name" class="form-control" value="<?= htmlspecialchars($event['event_name']) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label"><i class="fas fa-info-circle"></i> Deskripsi</label>
                                <textarea name="description" id="description" class="form-control" required><?= htmlspecialchars($event['event_description']) ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="price" class="form-label"><i class="fas fa-dollar-sign"></i> Harga Pendaftaran</label>
                                <input type="number" name="price" id="price" class="form-control" value="<?= htmlspecialchars($event['registration_fee']) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="rules" class="form-label"><i class="fas fa-gavel"></i> Aturan</label>
                                <textarea name="rules" id="rules" class="form-control" required><?= htmlspecialchars($event['rules']) ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="location" class="form-label"><i class="fas fa-map-marker-alt"></i> Lokasi</label>
                                <input type="text" name="location" id="location" class="form-control" value="<?= htmlspecialchars($event['location']) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="event_date" class="form-label"><i class="fas fa-calendar-day ```php
"></i> Tanggal</label>
                                <input type="date" name="event_date" id="event_date" class="form-control" value="<?= htmlspecialchars($event['event_date']) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="event_time" class="form-label"><i class="fas fa-clock"></i> Waktu</label>
                                <input type="time" name="event_time" id="event_time" class="form-control" value="<?= htmlspecialchars($event['event_time']) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="image" class="form-label"><i class="fas fa-image"></i> Foto Event</label>
                                <input type="file" name="image" id="image" class="form-control">
                                <?php if ($event['image_url']): ?>
                                    <div class="mt-2">
                                        <img src="<?= htmlspecialchars($event['image_url']) ?>" alt="Event Image" class="event-image">
                                    </div>
                                <?php endif; ?>
                            </div>

                            <h3 class="mt-4"><i class="fas fa-plus-circle"></i> Field Formulir</h3>
                            <div id="fields-container">
                                <?php foreach ($fields as $index => $field): ?>
                                    <div class="mb-3" id="field-<?= $index ?>">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label class="form-label">Field Name</label>
                                                <input type="text" name="fields[<?= $index ?>][name]" class="form-control" value="<?= htmlspecialchars($field['field_name']) ?>" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Field Type</label>
                                                <select name="fields[<?= $index ?>][type]" class="form-select" required>
                                                    <option value="text" <?= $field['field_type'] === 'text' ? 'selected' : '' ?>>Text</option>
                                                    <option value="number" <?= $field['field_type'] === 'number' ? 'selected' : '' ?>>Number</option>
                                                    <option value="email" <?= $field['field_type'] === 'email' ? 'selected' : '' ?>>Email</option>
                                                    <option value="date" <?= $field['field_type'] === 'date' ? 'selected' : '' ?>>Date</option>
                                                    <option value="file" <?= $field['field_type'] === 'file' ? 'selected' : '' ?>>File</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Is Required</label>
                                                <input type="checkbox" name="fields[<?= $index ?>][is_required]" value="1" <?= $field['is_required'] ? 'checked' : '' ?>>
                                            </div>
                                        </div>
                                        <input type="hidden" name="fields[<?= $index ?>][id]" value="<?= $field['id'] ?>">
                                        <button type="button" class="btn btn-outline-danger mt-2" onclick="removeField(<?= $index ?>, <?= $field['id'] ?>)"><i class="fas fa-trash"></i> Hapus Field</button>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <button type="button" class="btn btn-outline-primary" id="add-field-btn"><i class="fas fa-plus"></i> Tambah Field</button>
                            <button type="submit" name="update_event" class="btn btn-custom mt-4"><i class="fas fa-save"></i> Update Event</button>
                        </form>
                    </div>
                </div>
            </div>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('add-field-btn').addEventListener('click', function() {
        const fieldsContainer = document.getElementById('fields-container');
        const newIndex = fieldsContainer.children.length;
        const newField = document.createElement('div');
        newField.classList.add('mb-3');
        newField.id = 'field-' + newIndex;

        newField.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label">Field Name</label>
                    <input type="text" name="fields[${newIndex}][name]" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Field Type</label>
                    <select name="fields[${newIndex}][type]" class="form-select" required>
                        <option value="text">Text</option>
                        <option value="number">Number</option>
                        <option value="email">Email</option>
                        <option value="date">Date</option>
                        <option value="file">File</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Is Required</label>
                    <input type="checkbox" name="fields[${newIndex}][is_required]" value="1">
                </div>
            </div>
            <button type="button" class="btn btn-outline-danger mt-2" onclick="removeField(${newIndex})"><i class="fas fa-trash"></i> Hapus Field</button>
        `;

        fieldsContainer.appendChild(newField);
    });

    function removeField(index) {
        const field = document.getElementById('field-' + index);
        field.remove();

        // If you want to track which fields are removed, you can use a hidden input for deletion
        const deletedFieldsInput = document.getElementById('deleted-fields');
        if (!deletedFieldsInput) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'deleted_fields[]';
            input.id = 'deleted-fields';
            document.forms[0].appendChild(input);
        }

        const deletedFields = document.forms[0].elements['deleted_fields[]'];
        deletedFields.value += (deletedFields.value ? ',' : '') + index;
    }
</script>
</body>
</html>
