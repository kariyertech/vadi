<?php
require_once 'config.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $email = sanitize($_POST['email']);
        $projectName = sanitize($_POST['project_name']);
        $projectType = sanitize($_POST['project_type']);
        $projectGoal = sanitize($_POST['project_goal']);
        $projectDesc = sanitize($_POST['project_description']);
        $projectReq = sanitize($_POST['project_requirements']);
        $keywords = array_map('trim', explode(',', sanitize($_POST['keywords'])));
        
        $filePath = '';
        if (isset($_FILES['project_file']) && $_FILES['project_file']['error'] === UPLOAD_ERR_OK) {
            $filePath = handleFileUpload($_FILES['project_file']);
            if (!$filePath) {
                throw new Exception("Invalid file type or upload failed");
            }
        }
        
        $stmt = $db->prepare("
            INSERT INTO projects (email, project_name, project_type, project_goal, project_description, 
                                project_requirements, keyword_tags, file_path)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([$email, $projectName, $projectType, $projectGoal, $projectDesc, $projectReq, 
                       '{' . implode(',', $keywords) . '}', $filePath]);
        
        $message = "Project submitted successfully!";
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Submission Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #8316b5;
        }
        .navbar, .footer {
            background-color: var(--primary-color);
        }
        .navbar-brand img {
            height: 35px;
            margin-top: -5px;
            filter: brightness(0) invert(1);
        }
        .form-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .form-control {
            max-width: 100%;
            border-radius: 5px;
        }
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        .btn-primary:hover {
            background-color: #6a1291;
            border-color: #6a1291;
        }
        .footer {
            color: white;
            padding: 1rem 0;
            margin-top: 3rem;
        }
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        main {
            flex: 1;
        }
        .page-title {
            color: var(--primary-color);
            margin-bottom: 2rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">
            <img src="kariyer-logo.png" alt="Kariyer.net Logo" class="me-2">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item" style="display: none;">
                        <a class="nav-link" href="admin.php">Admin Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container">
        <div class="form-container">
            <h2 class="text-center page-title">Fikriyatımızın Şahikası: Yenilikler Burada Yükselir</h2>            
            <?php if ($message): ?>
                <div class="alert <?php echo strpos($message, 'Error') === 0 ? 'alert-danger' : 'alert-success'; ?> mb-4">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">*E-Posta Adresiniz: </label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="project_name" class="form-label">*İsim Soyisim: </label>
                        <input type="text" class="form-control" id="project_name" name="project_name" required>
                    </div>
                </div>
                
                <div class="mb-3">
                <label for="project_type" class="form-label">
                    <em>Patent, Makale, Süreç Otomasyonu veya Proje fikriniz varsa, aşağıdaki seçeneklerden birini belirleyerek devam edebilirsiniz:</em>
                </label>
                <select class="form-select" id="project_type" name="project_type" required>
                <option value="" disabled selected style="opacity: 0.6; transform: rotate(-3deg);">
                    *Seçim Yapınız
                </option> 
                <option value="Paper">Makale</option>
                <option value="Project">Proje</option>
                <option value="Patent">Patent</option>
                <option value="ProcessAutomation">Süreç Otomasyonu</option>
                </select>
                </div>
                
                <div class="mb-3">
                    <label for="project_goal" class="form-label">*Amaç: </label>
                    <textarea class="form-control" id="project_goal" name="project_goal" rows="2" required></textarea>
                </div>
                
                <div class="mb-3">
                    <label for="project_description" class="form-label">Açıklama: </label>
                    <textarea class="form-control" id="project_description" name="project_description" rows="3"></textarea>
                </div>
                
                <div class="mb-3">
                    <label for="project_requirements" class="form-label">Gereksinimler: </label>
                    <textarea class="form-control" id="project_requirements" name="project_requirements" rows="2"></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="keywords" class="form-label">Anahtar Kelimeler: </label>
                        <input type="text" class="form-control" id="keywords" name="keywords" placeholder="Virgülle ayırabilirsiniz">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="project_file" class="form-label">Dosya: </label>
                        <input type="file" class="form-control" id="project_file" name="project_file">
                        <div class="form-text">Kabul Edilen Dosya Türleri: PDF, DOCX, JPEG, PNG, MP4, WAV, Excel</div>
                        <div class="form-text">Sadece bir dosya seçebilirsiniz, Maksimum dosya boyutu 3MB</div>

                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary px-5">Gönder</button>
                </div>
            </form>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer text-center mt-5">
        <div class="container">
            <p class="mb-0">&copy; 2025 Fikrim Var Kariyer.net DevSecOps-Arge Project. All rights reserved.</p>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>
</body>
</html>